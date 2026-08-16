<?php
/**
 * The learner's own profile: a picture, a short bio, a few links, and the
 * ability to wipe progress and start a course again.
 *
 * Why a profile exists at all on a course platform: the wall of success only
 * works if the people on it look like people. A story signed by a name and a
 * face from a tier-three college in Indore is evidence; the same story signed
 * by "user_4417" is marketing copy.
 *
 * The security posture here is deliberately paranoid, because this is the one
 * place where an ordinary subscriber writes a file to disk:
 *
 *   · Subscribers do not have `upload_files`, and they are not given it. The
 *     upload is handled here, under rules narrower than the media library's.
 *   · The file must decode as a real JPEG, PNG or WebP. An extension and a
 *     Content-Type header are both attacker-controlled; getimagesize() reading
 *     actual pixel dimensions is not.
 *   · SVG is refused. An SVG is a script container, and one served from our own
 *     origin is stored XSS.
 *   · One avatar per person. Uploading a new one deletes the old file, so this
 *     cannot be used as free unbounded storage.
 */

namespace Guide\Account;

use Guide\Enrollment\Tables;

defined( 'ABSPATH' ) || exit;

class Profile {

	/** Attachment ID of the learner's picture. */
	const META_AVATAR = 'jsl_avatar_id';

	/** The links a learner may show. Keys are meta suffixes. */
	const LINKS = array(
		'linkedin'  => 'LinkedIn',
		'github'    => 'GitHub',
		'instagram' => 'Instagram',
		'x'         => 'X',
		'website'   => 'Website',
	);

	/** Hosts each link field is allowed to point at. */
	const LINK_HOSTS = array(
		'linkedin'  => array( 'linkedin.com' ),
		'github'    => array( 'github.com' ),
		'instagram' => array( 'instagram.com' ),
		'x'         => array( 'x.com', 'twitter.com' ),
	);

	const MAX_AVATAR_BYTES = 2097152; // 2 MB
	const AVATAR_EDGE      = 512;     // Stored square, at a sane size.

	public static function init() {
		add_action( 'rest_api_init', array( __CLASS__, 'register_routes' ) );

		// Show the uploaded picture wherever WordPress asks for an avatar —
		// comments, the wall of success, the console learner list.
		add_filter( 'get_avatar_data', array( __CLASS__, 'use_uploaded_avatar' ), 10, 2 );

		// Learners edit their profile at /account/, never in wp-admin.
		add_filter( 'user_contactmethods', array( __CLASS__, 'contact_methods' ) );
	}

	// -------------------------------------------------------------------------
	// Reading
	// -------------------------------------------------------------------------

	public static function meta_key( string $link ): string {
		return 'jsl_link_' . $link;
	}

	/**
	 * A learner's public links, empty ones dropped.
	 *
	 * @return array<string, array{label:string, url:string}>
	 */
	public static function links( int $user_id ): array {
		$out = array();

		foreach ( self::LINKS as $key => $label ) {
			$url = (string) get_user_meta( $user_id, self::meta_key( $key ), true );

			if ( '' === $url ) {
				continue;
			}

			$out[ $key ] = array(
				'label' => $label,
				'url'   => $url,
			);
		}

		return $out;
	}

	public static function avatar_id( int $user_id ): int {
		return (int) get_user_meta( $user_id, self::META_AVATAR, true );
	}

	public static function avatar_url( int $user_id, string $size = 'thumbnail' ): string {
		$id = self::avatar_id( $user_id );

		if ( ! $id ) {
			return '';
		}

		return (string) wp_get_attachment_image_url( $id, $size );
	}

	/**
	 * Swap in the uploaded picture for Gravatar.
	 *
	 * @param array<string,mixed> $args
	 * @param mixed               $id_or_email
	 * @return array<string,mixed>
	 */
	public static function use_uploaded_avatar( $args, $id_or_email ) {
		$user_id = self::resolve_user_id( $id_or_email );

		if ( ! $user_id ) {
			return $args;
		}

		$url = self::avatar_url( $user_id, 'thumbnail' );

		if ( '' === $url ) {
			return $args;
		}

		$args['url']          = $url;
		$args['found_avatar'] = true;

		return $args;
	}

	/** @param mixed $id_or_email */
	private static function resolve_user_id( $id_or_email ): int {
		if ( is_numeric( $id_or_email ) ) {
			return (int) $id_or_email;
		}

		if ( $id_or_email instanceof \WP_User ) {
			return (int) $id_or_email->ID;
		}

		if ( $id_or_email instanceof \WP_Comment ) {
			return (int) $id_or_email->user_id;
		}

		if ( is_string( $id_or_email ) && is_email( $id_or_email ) ) {
			$user = get_user_by( 'email', $id_or_email );
			return $user ? (int) $user->ID : 0;
		}

		return 0;
	}

	/**
	 * Expose the links as contact methods so an administrator can see them on
	 * the standard user screen without a second UI to maintain.
	 *
	 * @param array<string,string> $methods
	 * @return array<string,string>
	 */
	public static function contact_methods( $methods ) {
		foreach ( self::LINKS as $key => $label ) {
			$methods[ self::meta_key( $key ) ] = $label;
		}

		return $methods;
	}

	// -------------------------------------------------------------------------
	// REST
	// -------------------------------------------------------------------------

	public static function register_routes() {
		register_rest_route(
			'guide/v1',
			'/account/links',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'update_links' ),
				'permission_callback' => 'is_user_logged_in',
			)
		);

		register_rest_route(
			'guide/v1',
			'/account/avatar',
			array(
				array(
					'methods'             => 'POST',
					'callback'            => array( __CLASS__, 'upload_avatar' ),
					'permission_callback' => 'is_user_logged_in',
				),
				array(
					'methods'             => 'DELETE',
					'callback'            => array( __CLASS__, 'remove_avatar' ),
					'permission_callback' => 'is_user_logged_in',
				),
			)
		);

		register_rest_route(
			'guide/v1',
			'/account/progress',
			array(
				'methods'             => 'DELETE',
				'callback'            => array( __CLASS__, 'reset_progress' ),
				'permission_callback' => 'is_user_logged_in',
				'args'                => array(
					'course_id' => array(
						'type'     => 'integer',
						'required' => false,
					),
					'lesson_id' => array(
						'type'     => 'integer',
						'required' => false,
					),
				),
			)
		);
	}

	/**
	 * Save the learner's links.
	 *
	 * Every URL is forced to https and checked against an allow-list of hosts,
	 * so the "LinkedIn" field on a public profile cannot quietly become a link
	 * to anywhere the author likes. Profiles are public; an unconstrained URL
	 * field on a public page is a spam vector with a queue of one.
	 */
	public static function update_links( \WP_REST_Request $request ) {
		$user_id = get_current_user_id();
		$saved   = array();

		foreach ( array_keys( self::LINKS ) as $key ) {
			$raw = trim( (string) $request->get_param( $key ) );

			if ( '' === $raw ) {
				delete_user_meta( $user_id, self::meta_key( $key ) );
				continue;
			}

			$url = self::clean_link( $raw, $key );

			if ( is_wp_error( $url ) ) {
				return new \WP_REST_Response(
					array(
						'error' => $url->get_error_message(),
						'field' => $key,
					),
					400
				);
			}

			update_user_meta( $user_id, self::meta_key( $key ), $url );
			$saved[ $key ] = $url;
		}

		return new \WP_REST_Response(
			array(
				'saved' => true,
				'links' => $saved,
			),
			200
		);
	}

	/**
	 * @return string|\WP_Error
	 */
	private static function clean_link( string $raw, string $field ) {
		// People paste "linkedin.com/in/name" far more often than a full URL.
		if ( ! preg_match( '#^https?://#i', $raw ) ) {
			$raw = 'https://' . ltrim( $raw, '/' );
		}

		$url = esc_url_raw( $raw, array( 'https', 'http' ) );

		if ( '' === $url || ! wp_http_validate_url( $url ) ) {
			return new \WP_Error( 'guide_bad_url', __( 'That does not look like a web address.', 'guide-lms' ) );
		}

		$host = strtolower( (string) wp_parse_url( $url, PHP_URL_HOST ) );
		$host = preg_replace( '/^www\./', '', $host );

		if ( isset( self::LINK_HOSTS[ $field ] ) ) {
			$allowed = false;

			foreach ( self::LINK_HOSTS[ $field ] as $candidate ) {
				if ( $host === $candidate || str_ends_with( $host, '.' . $candidate ) ) {
					$allowed = true;
					break;
				}
			}

			if ( ! $allowed ) {
				return new \WP_Error(
					'guide_wrong_host',
					sprintf(
						/* translators: 1: field label, 2: expected domain. */
						__( 'Your %1$s link should point at %2$s.', 'guide-lms' ),
						self::LINKS[ $field ],
						self::LINK_HOSTS[ $field ][0]
					)
				);
			}
		}

		return mb_substr( $url, 0, 300 );
	}

	/**
	 * Accept a profile picture.
	 *
	 * Validation order matters: cheapest rejections first, and the expensive
	 * image decode only once the obvious junk is gone.
	 */
	public static function upload_avatar( \WP_REST_Request $request ) {
		$user_id = get_current_user_id();
		$files   = $request->get_file_params();
		$file    = $files['file'] ?? null;

		if ( ! $file || ! isset( $file['tmp_name'] ) || UPLOAD_ERR_OK !== ( $file['error'] ?? UPLOAD_ERR_NO_FILE ) ) {
			return new \WP_REST_Response( array( 'error' => __( 'No image was received.', 'guide-lms' ) ), 400 );
		}

		if ( (int) ( $file['size'] ?? 0 ) > self::MAX_AVATAR_BYTES ) {
			return new \WP_REST_Response( array( 'error' => __( 'That image is over 2 MB. Try a smaller one.', 'guide-lms' ) ), 400 );
		}

		// The decisive check: does this actually decode as one of three image
		// formats? A .jpg that is really a PHP script fails here.
		$probe = @getimagesize( $file['tmp_name'] );

		$allowed = array(
			IMAGETYPE_JPEG => 'jpg',
			IMAGETYPE_PNG  => 'png',
			IMAGETYPE_WEBP => 'webp',
		);

		if ( ! $probe || ! isset( $allowed[ $probe[2] ] ) ) {
			return new \WP_REST_Response(
				array( 'error' => __( 'Please upload a JPEG, PNG or WebP image.', 'guide-lms' ) ),
				400
			);
		}

		$extension = $allowed[ $probe[2] ];

		// Our own filename. Whatever the browser sent is discarded — it is
		// attacker-controlled and has no business reaching the filesystem.
		$file['name'] = 'avatar-' . $user_id . '-' . wp_generate_password( 8, false ) . '.' . $extension;

		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';

		$moved = wp_handle_upload(
			$file,
			array(
				'test_form' => false,
				'mimes'     => array(
					'jpg|jpeg' => 'image/jpeg',
					'png'      => 'image/png',
					'webp'     => 'image/webp',
				),
			)
		);

		if ( ! $moved || isset( $moved['error'] ) ) {
			return new \WP_REST_Response(
				array( 'error' => $moved['error'] ?? __( 'That upload failed.', 'guide-lms' ) ),
				400
			);
		}

		$attachment_id = wp_insert_attachment(
			array(
				'post_mime_type' => $moved['type'],
				'post_title'     => sprintf(
					/* translators: %s: display name. */
					__( 'Profile picture for %s', 'guide-lms' ),
					get_the_author_meta( 'display_name', $user_id )
				),
				'post_content'   => '',
				'post_status'    => 'inherit',
				'post_author'    => $user_id,
			),
			$moved['file']
		);

		if ( is_wp_error( $attachment_id ) || ! $attachment_id ) {
			wp_delete_file( $moved['file'] );
			return new \WP_REST_Response( array( 'error' => __( 'That upload failed.', 'guide-lms' ) ), 500 );
		}

		wp_update_attachment_metadata(
			$attachment_id,
			wp_generate_attachment_metadata( $attachment_id, $moved['file'] )
		);

		self::discard_previous_avatar( $user_id );
		update_user_meta( $user_id, self::META_AVATAR, (int) $attachment_id );

		return new \WP_REST_Response(
			array(
				'saved' => true,
				'url'   => wp_get_attachment_image_url( $attachment_id, 'thumbnail' ),
			),
			200
		);
	}

	public static function remove_avatar() {
		$user_id = get_current_user_id();

		self::discard_previous_avatar( $user_id );
		delete_user_meta( $user_id, self::META_AVATAR );

		return new \WP_REST_Response( array( 'saved' => true ), 200 );
	}

	/** Delete the file behind the current avatar, if this user owns it. */
	private static function discard_previous_avatar( int $user_id ) {
		$existing = self::avatar_id( $user_id );

		if ( ! $existing ) {
			return;
		}

		$attachment = get_post( $existing );

		// Only delete something this user actually owns — an administrator who
		// set someone's picture from the media library should not have their
		// library item destroyed by the learner changing their mind.
		if ( $attachment && (int) $attachment->post_author === $user_id ) {
			wp_delete_attachment( $existing, true );
		}
	}

	// -------------------------------------------------------------------------
	// Resetting progress
	// -------------------------------------------------------------------------

	/**
	 * Clear a learner's own completion records.
	 *
	 * Worth having rather than being a curiosity: a lot of this audience will
	 * work through the foundations, get a job, and come back a year later to
	 * revise before an interview. Starting again with every tick already green
	 * makes the course useless as a revision tool.
	 *
	 * Scoped three ways — one lesson, one course, or everything — and always
	 * limited to the caller's own rows. Progress is not access: resetting it
	 * never touches enrolment, so nobody can lock themselves out with it.
	 */
	public static function reset_progress( \WP_REST_Request $request ) {
		global $wpdb;

		$user_id   = get_current_user_id();
		$table     = Tables::progress_table_name();
		$lesson_id = (int) $request->get_param( 'lesson_id' );
		$course_id = (int) $request->get_param( 'course_id' );

		if ( $lesson_id ) {
			$deleted = $wpdb->delete(
				$table,
				array(
					'user_id'   => $user_id,
					'lesson_id' => $lesson_id,
				),
				array( '%d', '%d' )
			);

			$scope = 'lesson';
		} elseif ( $course_id ) {
			$deleted = $wpdb->delete(
				$table,
				array(
					'user_id'   => $user_id,
					'course_id' => $course_id,
				),
				array( '%d', '%d' )
			);

			$scope = 'course';
		} else {
			// Everything. Explicit confirmation required, so a stray request
			// cannot wipe a year of work.
			if ( 'RESET' !== (string) $request->get_param( 'confirm' ) ) {
				return new \WP_REST_Response(
					array( 'error' => __( 'Confirmation required to reset everything.', 'guide-lms' ) ),
					400
				);
			}

			$deleted = $wpdb->delete( $table, array( 'user_id' => $user_id ), array( '%d' ) );
			$scope   = 'all';
		}

		/**
		 * Fires after a learner resets their own progress.
		 *
		 * @param int    $user_id
		 * @param string $scope   'lesson', 'course' or 'all'.
		 * @param int    $object_id
		 */
		do_action( 'guide_progress_reset', $user_id, $scope, $lesson_id ?: $course_id );

		return new \WP_REST_Response(
			array(
				'reset'   => true,
				'scope'   => $scope,
				'cleared' => (int) $deleted,
			),
			200
		);
	}

	/**
	 * Courses this learner has progress in, for the reset UI.
	 *
	 * @return array<int, array{id:int, title:string, completed:int}>
	 */
	public static function courses_with_progress( int $user_id ): array {
		global $wpdb;

		$table = Tables::progress_table_name();

		$rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT course_id, COUNT(*) AS completed
				   FROM {$table}
				  WHERE user_id = %d AND course_id > 0
			   GROUP BY course_id",
				$user_id
			)
		);

		$out = array();

		foreach ( (array) $rows as $row ) {
			$course = get_post( (int) $row->course_id );

			if ( ! $course || 'course' !== $course->post_type ) {
				continue;
			}

			$out[] = array(
				'id'        => (int) $course->ID,
				'title'     => get_the_title( $course ),
				'completed' => (int) $row->completed,
			);
		}

		return $out;
	}
}
