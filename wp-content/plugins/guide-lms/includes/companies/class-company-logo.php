<?php
/**
 * Derive a company's logo from its website, so writing a company guide is
 * "paste the URL" rather than "go and find a logo, check you are allowed to
 * use it, crop it, upload it".
 *
 * That is the simplification: the fastest way to make somebody maintain a
 * catalogue of fifty companies is to remove the step that makes each one feel
 * like a chore.
 *
 * ---------------------------------------------------------------------------
 * This class makes the server fetch a URL somebody typed in, which is the
 * classic setup for server-side request forgery: the server sits inside the
 * network, so "fetch this URL for me" can reach a database admin panel, a
 * cloud metadata endpoint, or anything else that trusts local traffic.
 *
 * The defences, in order:
 *
 *   1. Only users who can edit companies can trigger it, and only over a
 *      nonce-checked request. It is never reachable by a visitor.
 *   2. Every request goes through wp_safe_remote_get(), which rejects private
 *      and loopback addresses, so http://169.254.169.254/ and
 *      http://192.168.1.1/ are refused before a socket is opened.
 *   3. Only http and https. No file://, no gopher://, no redirects to them.
 *   4. Responses are size-capped and must decode as a real image.
 *   5. Nothing from the remote host is ever echoed back — not the body, not a
 *      header, not the error text. A fetcher that reports what it saw is an
 *      SSRF oracle even when it refuses to store the result.
 */

namespace Guide\Companies;

defined( 'ABSPATH' ) || exit;

class Company_Logo {

	/** Give up on a slow host rather than holding the editor hostage. */
	const TIMEOUT = 8;

	/** Nothing plausible as a logo is larger than this. */
	const MAX_BYTES = 1048576; // 1 MB

	/** Smallest useful edge — a 16px favicon looks like dirt on a card. */
	const MIN_EDGE = 48;

	/** Big enough to stop looking and take it. */
	const GOOD_EDGE = 96;

	/** How far from square a logo may be before it is treated as a banner. */
	const MAX_RATIO = 1.6;

	public static function init() {
		add_action( 'wp_ajax_guide_fetch_company_logo', array( __CLASS__, 'ajax_fetch' ) );
	}

	// -------------------------------------------------------------------------
	// Admin entry point
	// -------------------------------------------------------------------------

	public static function ajax_fetch() {
		check_ajax_referer( 'guide_company_logo' );

		$post_id = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;
		$website = isset( $_POST['website'] ) ? esc_url_raw( wp_unslash( $_POST['website'] ) ) : '';

		if ( ! $post_id || ! current_user_can( 'edit_post', $post_id ) ) {
			wp_send_json_error( array( 'message' => __( 'You cannot edit this company.', 'guide-lms' ) ), 403 );
		}

		if ( '' === $website ) {
			wp_send_json_error( array( 'message' => __( 'Add the company website first.', 'guide-lms' ) ), 400 );
		}

		$attachment_id = self::fetch_for( $post_id, $website );

		if ( is_wp_error( $attachment_id ) ) {
			// Deliberately our own message, never the remote host's.
			wp_send_json_error( array( 'message' => $attachment_id->get_error_message() ), 400 );
		}

		set_post_thumbnail( $post_id, $attachment_id );

		wp_send_json_success(
			array(
				'id'      => $attachment_id,
				'url'     => wp_get_attachment_image_url( $attachment_id, 'medium' ),
				'message' => __( 'Logo saved as the featured image.', 'guide-lms' ),
			)
		);
	}

	// -------------------------------------------------------------------------
	// The fetch
	// -------------------------------------------------------------------------

	/**
	 * Find a logo for a company and store it in the media library.
	 *
	 * @return int|\WP_Error Attachment ID.
	 */
	public static function fetch_for( int $post_id, string $website ) {
		// wp_tempnam(), wp_handle_sideload() and the attachment metadata
		// helpers all live in wp-admin includes, which are not loaded on every
		// request that can reach this code.
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';

		$base = self::normalise( $website );

		if ( is_wp_error( $base ) ) {
			return $base;
		}

		$candidates = self::candidates( $base );

		if ( empty( $candidates ) ) {
			return new \WP_Error( 'guide_no_logo', __( 'No usable image found on that site.', 'guide-lms' ) );
		}

		$best      = null;
		$best_edge = 0;

		// Take the first candidate that is big enough, not the biggest one.
		//
		// The candidate list is in order of how likely each source is to be an
		// actual logo, and "largest wins" throws that ordering away: a site's
		// og:image is usually a 1200x630 marketing banner with a strapline
		// baked into it, which beats a clean 180px square icon on pixel count
		// and loses badly as a logo on a card.
		foreach ( $candidates as $url ) {
			$image = self::download_image( $url );

			if ( is_wp_error( $image ) ) {
				continue;
			}

			$edge  = min( $image['width'], $image['height'] );
			$ratio = max( $image['width'], $image['height'] ) / max( 1, $edge );

			// Big enough *and* roughly square. A 1200x630 banner is not a logo
			// no matter how many pixels it has, and it will be cropped to a
			// square on the company card anyway — losing whichever half of the
			// wordmark happens to fall outside.
			if ( $edge >= self::GOOD_EDGE && $ratio <= self::MAX_RATIO ) {
				if ( $best ) {
					wp_delete_file( $best['file'] );
				}

				$best = $image;
				break;
			}

			// Not ideal, but keep the best of the small ones in case nothing
			// better turns up.
			if ( $edge > $best_edge ) {
				if ( $best ) {
					wp_delete_file( $best['file'] );
				}

				$best      = $image;
				$best_edge = $edge;
			} else {
				wp_delete_file( $image['file'] );
			}
		}

		if ( ! $best ) {
			return new \WP_Error( 'guide_no_logo', __( 'Could not read an image from that site.', 'guide-lms' ) );
		}

		return self::store( $post_id, $best );
	}

	/**
	 * @return string|\WP_Error Base URL, scheme and host only.
	 */
	private static function normalise( string $website ) {
		// An explicit scheme is honoured or refused — never rewritten. Turning
		// "file:///etc/passwd" into "https://file:///etc/passwd" happens to
		// fail, but relying on a mangling accident to enforce a scheme rule is
		// how a later refactor quietly reopens the hole.
		if ( preg_match( '#^([a-z][a-z0-9+.-]*):#i', $website, $scheme_match ) ) {
			if ( ! in_array( strtolower( $scheme_match[1] ), array( 'http', 'https' ), true ) ) {
				return new \WP_Error( 'guide_bad_scheme', __( 'Only http and https addresses are supported.', 'guide-lms' ) );
			}
		} else {
			$website = 'https://' . ltrim( $website, '/' );
		}

		$parts = wp_parse_url( $website );

		if ( empty( $parts['host'] ) || empty( $parts['scheme'] ) ) {
			return new \WP_Error( 'guide_bad_url', __( 'That is not a valid website address.', 'guide-lms' ) );
		}

		if ( ! in_array( strtolower( $parts['scheme'] ), array( 'http', 'https' ), true ) ) {
			return new \WP_Error( 'guide_bad_scheme', __( 'Only http and https addresses are supported.', 'guide-lms' ) );
		}

		return strtolower( $parts['scheme'] ) . '://' . $parts['host'];
	}

	/**
	 * Image URLs worth trying, best first.
	 *
	 * apple-touch-icon comes first because it is the one icon a site is
	 * obliged to supply as a clean, square, reasonably large logo. og:image is
	 * usually a banner — fine, but often has text baked into it. /favicon.ico
	 * is the last resort and is usually 32px.
	 *
	 * @return string[]
	 */
	private static function candidates( string $base ): array {
		$html = self::get_body( $base );
		$out  = array();

		if ( '' !== $html ) {
			// Only the <head>; a page's body can contain anything.
			$head = substr( $html, 0, 200000 );

			$patterns = array(
				// rel="apple-touch-icon" (with or without -precomposed): the one
				// icon a site is obliged to supply large and square.
				'#<link[^>]+rel=["\'][^"\']*apple-touch-icon[^"\']*["\'][^>]*>#i',
				// Any other rel containing "icon". Often only 32px, in which
				// case it fails the minimum size and we fall through.
				'#<link[^>]+rel=["\'][^"\']*icon[^"\']*["\'][^>]*>#i',
				// og:image last: it is a social sharing banner, not a logo, and
				// frequently has a strapline baked into the pixels.
				'#<meta[^>]+(?:property|name)=["\'](?:og:image|twitter:image)["\'][^>]*>#i',
			);

			foreach ( $patterns as $pattern ) {
				if ( ! preg_match_all( $pattern, $head, $tags ) ) {
					continue;
				}

				foreach ( $tags[0] as $tag ) {
					if ( preg_match( '#(?:href|content)=["\']([^"\']+)["\']#i', $tag, $m ) ) {
						$resolved = self::absolute( trim( $m[1] ), $base );

						if ( $resolved ) {
							$out[] = $resolved;
						}
					}
				}
			}
		}

		// Conventional paths, tried whether or not the HTML links them. Plenty
		// of large sites serve these and only reference them from a manifest
		// we are not going to parse, and a homepage <head> is increasingly
		// assembled by JavaScript we never run.
		$out[] = $base . '/apple-touch-icon.png';
		$out[] = $base . '/apple-touch-icon-precomposed.png';
		$out[] = $base . '/favicon.ico';

		return array_slice( array_unique( $out ), 0, 8 );
	}

	/** Resolve a possibly-relative URL against the site base. */
	private static function absolute( string $url, string $base ): string {
		if ( '' === $url || str_starts_with( $url, 'data:' ) ) {
			return '';
		}

		if ( str_starts_with( $url, '//' ) ) {
			$url = ( wp_parse_url( $base, PHP_URL_SCHEME ) ?: 'https' ) . ':' . $url;
		} elseif ( str_starts_with( $url, '/' ) ) {
			$url = $base . $url;
		} elseif ( ! preg_match( '#^https?://#i', $url ) ) {
			$url = $base . '/' . ltrim( $url, './' );
		}

		$scheme = strtolower( (string) wp_parse_url( $url, PHP_URL_SCHEME ) );

		return in_array( $scheme, array( 'http', 'https' ), true ) ? $url : '';
	}

	/** Fetch a page body. Empty string on any failure — never an error message. */
	private static function get_body( string $url ): string {
		$response = wp_safe_remote_get(
			$url,
			array(
				'timeout'    => self::TIMEOUT,
				'redirection' => 3,
				'user-agent' => 'Mozilla/5.0 (compatible; GuideLMS/1.0; +' . home_url( '/' ) . ')',
				'headers'    => array( 'Accept' => 'text/html' ),
			)
		);

		if ( is_wp_error( $response ) || 200 !== (int) wp_remote_retrieve_response_code( $response ) ) {
			return '';
		}

		return (string) wp_remote_retrieve_body( $response );
	}

	/**
	 * Download one candidate and confirm it is a usable image.
	 *
	 * @return array{file:string, width:int, height:int, ext:string, mime:string}|\WP_Error
	 */
	private static function download_image( string $url ) {
		require_once ABSPATH . 'wp-admin/includes/file.php';

		$response = wp_safe_remote_get(
			$url,
			array(
				'timeout'    => self::TIMEOUT,
				'redirection' => 3,
				'user-agent' => 'Mozilla/5.0 (compatible; GuideLMS/1.0; +' . home_url( '/' ) . ')',
			)
		);

		if ( is_wp_error( $response ) || 200 !== (int) wp_remote_retrieve_response_code( $response ) ) {
			return new \WP_Error( 'guide_fetch_failed', __( 'Could not fetch that image.', 'guide-lms' ) );
		}

		$body = (string) wp_remote_retrieve_body( $response );

		if ( '' === $body || strlen( $body ) > self::MAX_BYTES ) {
			return new \WP_Error( 'guide_bad_size', __( 'That image is empty or too large.', 'guide-lms' ) );
		}

		$temp = wp_tempnam( 'guide-logo' );

		if ( ! $temp ) {
			return new \WP_Error( 'guide_no_temp', __( 'Could not write a temporary file.', 'guide-lms' ) );
		}

		file_put_contents( $temp, $body ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents

		// The decisive test: does it decode as an image, and which kind?
		$probe = @getimagesize( $temp );

		$types = array(
			IMAGETYPE_PNG  => array( 'png', 'image/png' ),
			IMAGETYPE_JPEG => array( 'jpg', 'image/jpeg' ),
			IMAGETYPE_WEBP => array( 'webp', 'image/webp' ),
			IMAGETYPE_GIF  => array( 'gif', 'image/gif' ),
		);

		// .ico is common for favicons but is not a web image format we want to
		// store, and PHP cannot resize it. Skipped rather than special-cased —
		// a site with only a 32px .ico has no logo worth showing.
		if ( ! $probe || ! isset( $types[ $probe[2] ] ) ) {
			wp_delete_file( $temp );
			return new \WP_Error( 'guide_not_image', __( 'That file is not a usable image.', 'guide-lms' ) );
		}

		if ( min( (int) $probe[0], (int) $probe[1] ) < self::MIN_EDGE ) {
			wp_delete_file( $temp );
			return new \WP_Error( 'guide_too_small', __( 'That image is too small to use.', 'guide-lms' ) );
		}

		return array(
			'file'   => $temp,
			'width'  => (int) $probe[0],
			'height' => (int) $probe[1],
			'ext'    => $types[ $probe[2] ][0],
			'mime'   => $types[ $probe[2] ][1],
		);
	}

	/**
	 * Move a verified image into the media library.
	 *
	 * @param array{file:string, ext:string, mime:string} $image
	 * @return int|\WP_Error
	 */
	private static function store( int $post_id, array $image ) {
		$name = sanitize_title( get_the_title( $post_id ) ?: 'company' ) . '-logo.' . $image['ext'];

		// wp_handle_sideload() takes its first argument by reference, so it has
		// to be a variable rather than an inline array.
		$file = array(
			'name'     => $name,
			'type'     => $image['mime'],
			'tmp_name' => $image['file'],
			'error'    => 0,
			'size'     => (int) filesize( $image['file'] ),
		);

		$sideload = wp_handle_sideload( $file, array( 'test_form' => false ) );

		if ( isset( $sideload['error'] ) ) {
			wp_delete_file( $image['file'] );
			return new \WP_Error( 'guide_store_failed', __( 'Could not save that image.', 'guide-lms' ) );
		}

		$attachment_id = wp_insert_attachment(
			array(
				'post_mime_type' => $sideload['type'],
				'post_title'     => sprintf(
					/* translators: %s: company name. */
					__( '%s logo', 'guide-lms' ),
					get_the_title( $post_id )
				),
				'post_status'    => 'inherit',
				'post_parent'    => $post_id,
			),
			$sideload['file'],
			$post_id
		);

		if ( is_wp_error( $attachment_id ) || ! $attachment_id ) {
			wp_delete_file( $sideload['file'] );
			return new \WP_Error( 'guide_store_failed', __( 'Could not save that image.', 'guide-lms' ) );
		}

		wp_update_attachment_metadata(
			$attachment_id,
			wp_generate_attachment_metadata( $attachment_id, $sideload['file'] )
		);

		return (int) $attachment_id;
	}
}
