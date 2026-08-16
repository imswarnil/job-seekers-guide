<?php
/**
 * SEO output: titles, meta descriptions, canonicals, Open Graph / Twitter
 * cards, robots directives, and BreadcrumbList structured data.
 *
 * Course / Lesson / LearningPath schema lives in Guide\Schema\Json_Ld — this
 * class handles the head tags and the breadcrumb trail, and takes over
 * canonical output from core so there is exactly one canonical per page.
 */

namespace Guide\Seo;

use Guide\Access\Access;

defined( 'ABSPATH' ) || exit;

class Seo {

	const OPTION_DESCRIPTION  = 'jsl_seo_description';
	const OPTION_SOCIAL_IMAGE = 'jsl_seo_social_image';
	const OPTION_TWITTER      = 'jsl_seo_twitter';
	const OPTION_ORG_NAME     = 'jsl_seo_org_name';

	public static function init() {
		// One canonical per page: ours.
		remove_action( 'wp_head', 'rel_canonical' );

		add_action( 'wp_head', array( __CLASS__, 'render_head' ), 2 );
		add_filter( 'document_title_parts', array( __CLASS__, 'filter_title' ) );
		add_filter( 'wp_robots', array( __CLASS__, 'filter_robots' ) );
	}

	public static function org_name(): string {
		return (string) ( get_option( self::OPTION_ORG_NAME, '' ) ?: get_bloginfo( 'name' ) );
	}

	/**
	 * Sharpen the <title> for LMS templates — a lesson title alone is
	 * meaningless in a search result without its course.
	 */
	public static function filter_title( $parts ) {
		if ( is_singular( 'lesson' ) ) {
			$course_id = (int) get_post_meta( get_queried_object_id(), 'jsl_course_id', true );
			if ( $course_id ) {
				$parts['title'] = get_the_title( get_queried_object_id() ) . ' — ' . get_the_title( $course_id );
			}
		}

		if ( is_post_type_archive( 'course' ) ) {
			$parts['title'] = __( 'All courses', 'guide-lms' );
		}

		if ( is_post_type_archive( 'learning_path' ) ) {
			$parts['title'] = __( 'Learning paths', 'guide-lms' );
		}

		return $parts;
	}

	public static function filter_robots( $robots ) {
		// Search results and paginated comment views are noise in an index.
		if ( is_search() || is_404() ) {
			$robots['noindex']  = true;
			$robots['nofollow'] = true;
			return $robots;
		}

		$robots['max-image-preview'] = 'large';
		$robots['max-snippet']       = -1;

		return $robots;
	}

	/* ---------------------------------------------------------------
	 * <head>
	 * --------------------------------------------------------------- */

	public static function render_head() {
		$canonical   = self::canonical_url();
		$description = self::description();
		$image       = self::social_image();
		$type        = is_singular( array( 'course', 'lesson', 'learning_path' ) ) ? 'article' : 'website';

		if ( $canonical ) {
			printf( "<link rel=\"canonical\" href=\"%s\">\n", esc_url( $canonical ) );
		}

		if ( $description ) {
			printf( "<meta name=\"description\" content=\"%s\">\n", esc_attr( $description ) );
		}

		printf( "<meta property=\"og:site_name\" content=\"%s\">\n", esc_attr( get_bloginfo( 'name' ) ) );
		printf( "<meta property=\"og:type\" content=\"%s\">\n", esc_attr( $type ) );
		printf( "<meta property=\"og:title\" content=\"%s\">\n", esc_attr( wp_get_document_title() ) );

		if ( $description ) {
			printf( "<meta property=\"og:description\" content=\"%s\">\n", esc_attr( $description ) );
		}

		if ( $canonical ) {
			printf( "<meta property=\"og:url\" content=\"%s\">\n", esc_url( $canonical ) );
		}

		if ( $image ) {
			printf( "<meta property=\"og:image\" content=\"%s\">\n", esc_url( $image ) );
			printf( "<meta name=\"twitter:card\" content=\"summary_large_image\">\n" );
		} else {
			printf( "<meta name=\"twitter:card\" content=\"summary\">\n" );
		}

		$twitter = (string) get_option( self::OPTION_TWITTER, '' );
		if ( $twitter ) {
			printf( "<meta name=\"twitter:site\" content=\"%s\">\n", esc_attr( '@' . ltrim( $twitter, '@' ) ) );
		}

		self::render_breadcrumbs();
	}

	/**
	 * The one true URL for this view.
	 */
	public static function canonical_url(): string {
		if ( is_front_page() ) {
			return home_url( '/' );
		}

		if ( is_singular() ) {
			return (string) get_permalink( get_queried_object_id() );
		}

		if ( is_post_type_archive() ) {
			return (string) get_post_type_archive_link( (string) get_query_var( 'post_type' ) );
		}

		if ( is_tax() || is_category() || is_tag() ) {
			$term = get_queried_object();
			return $term instanceof \WP_Term ? (string) get_term_link( $term ) : '';
		}

		return '';
	}

	/**
	 * Meta description: the page's own excerpt if it has one, otherwise a
	 * trimmed version of its content, otherwise the site default.
	 */
	public static function description(): string {
		if ( is_singular() ) {
			$post = get_queried_object();

			if ( $post instanceof \WP_Post ) {
				if ( $post->post_excerpt ) {
					return wp_strip_all_tags( $post->post_excerpt );
				}

				// A locked lesson's body must not leak into a meta tag.
				if ( 'lesson' === $post->post_type && Access::is_locked( (int) $post->ID ) ) {
					return self::default_description();
				}

				$content = wp_strip_all_tags( strip_shortcodes( $post->post_content ) );
				if ( $content ) {
					return wp_trim_words( $content, 30, '…' );
				}
			}
		}

		if ( is_post_type_archive( 'course' ) ) {
			return __( 'Every course on the platform — interview preparation, résumés, negotiation and more.', 'guide-lms' );
		}

		if ( is_post_type_archive( 'learning_path' ) ) {
			return __( 'Structured learning paths that take you from application to offer, one course at a time.', 'guide-lms' );
		}

		return self::default_description();
	}

	private static function default_description(): string {
		return (string) ( get_option( self::OPTION_DESCRIPTION, '' ) ?: get_bloginfo( 'description' ) );
	}

	public static function social_image(): string {
		if ( is_singular() ) {
			$thumb = get_the_post_thumbnail_url( get_queried_object_id(), 'large' );
			if ( $thumb ) {
				return (string) $thumb;
			}
		}

		return (string) get_option( self::OPTION_SOCIAL_IMAGE, '' );
	}

	/* ---------------------------------------------------------------
	 * Breadcrumbs
	 * --------------------------------------------------------------- */

	/**
	 * BreadcrumbList JSON-LD reflecting the real URL hierarchy —
	 * Home › Courses › {Course} › {Lesson}.
	 */
	public static function render_breadcrumbs() {
		$trail = self::breadcrumb_trail();

		if ( count( $trail ) < 2 ) {
			return;
		}

		$items = array();

		foreach ( $trail as $i => $crumb ) {
			$items[] = array(
				'@type'    => 'ListItem',
				'position' => $i + 1,
				'name'     => $crumb['name'],
				'item'     => $crumb['url'],
			);
		}

		$graph = array(
			'@context'        => 'https://schema.org',
			'@type'           => 'BreadcrumbList',
			'itemListElement' => $items,
		);

		echo '<script type="application/ld+json">' . wp_json_encode( $graph, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . "</script>\n";
	}

	/**
	 * @return array<int, array{name:string, url:string}>
	 */
	public static function breadcrumb_trail(): array {
		$trail = array(
			array(
				'name' => __( 'Home', 'guide-lms' ),
				'url'  => home_url( '/' ),
			),
		);

		if ( is_singular( 'lesson' ) ) {
			$lesson_id = get_queried_object_id();
			$course_id = (int) get_post_meta( $lesson_id, 'jsl_course_id', true );

			$trail[] = array(
				'name' => __( 'Courses', 'guide-lms' ),
				'url'  => (string) get_post_type_archive_link( 'course' ),
			);

			if ( $course_id ) {
				$trail[] = array(
					'name' => get_the_title( $course_id ),
					'url'  => (string) get_permalink( $course_id ),
				);
			}

			$trail[] = array(
				'name' => get_the_title( $lesson_id ),
				'url'  => (string) get_permalink( $lesson_id ),
			);

			return $trail;
		}

		if ( is_singular( 'course' ) ) {
			$trail[] = array(
				'name' => __( 'Courses', 'guide-lms' ),
				'url'  => (string) get_post_type_archive_link( 'course' ),
			);
			$trail[] = array(
				'name' => get_the_title( get_queried_object_id() ),
				'url'  => (string) get_permalink( get_queried_object_id() ),
			);

			return $trail;
		}

		if ( is_singular( 'learning_path' ) ) {
			$trail[] = array(
				'name' => __( 'Learning paths', 'guide-lms' ),
				'url'  => (string) get_post_type_archive_link( 'learning_path' ),
			);
			$trail[] = array(
				'name' => get_the_title( get_queried_object_id() ),
				'url'  => (string) get_permalink( get_queried_object_id() ),
			);

			return $trail;
		}

		if ( is_post_type_archive( 'course' ) ) {
			$trail[] = array(
				'name' => __( 'Courses', 'guide-lms' ),
				'url'  => (string) get_post_type_archive_link( 'course' ),
			);
		}

		if ( is_post_type_archive( 'learning_path' ) ) {
			$trail[] = array(
				'name' => __( 'Learning paths', 'guide-lms' ),
				'url'  => (string) get_post_type_archive_link( 'learning_path' ),
			);
		}

		if ( is_page() ) {
			$trail[] = array(
				'name' => get_the_title( get_queried_object_id() ),
				'url'  => (string) get_permalink( get_queried_object_id() ),
			);
		}

		return $trail;
	}
}
