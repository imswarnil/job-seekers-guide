<?php
/**
 * Nested lesson permalinks.
 *
 * A lesson only ever has ONE canonical URL, and it lives under its course:
 *
 *     /courses/{course-slug}/{lesson-slug}/
 *
 * The old flat /lessons/{slug}/ base is gone — requests to it 301 to the
 * nested URL so nothing that was already indexed or bookmarked breaks.
 * Lessons that belong to no course (standalone articles/videos used as
 * learning-path steps) live under /library/{slug}/ instead, which keeps
 * them addressable without inventing a second URL for course lessons.
 *
 * Wrong-course URLs (/courses/other-course/lesson-slug/) 301 to the right
 * one rather than rendering, so a lesson can never be reached at two
 * different addresses.
 */

namespace JSL;

defined( 'ABSPATH' ) || exit;

class Permalinks {

	/** Rewrite base shared with the course post type. */
	const COURSE_BASE = 'courses';

	/** Rewrite base for lessons that belong to no course. */
	const ORPHAN_BASE = 'library';

	/** Bumped whenever the rules below change, to trigger a one-time flush. */
	const RULES_VERSION = '3';

	public static function init() {
		add_action( 'init', array( __CLASS__, 'add_rewrite_rules' ), 20 );
		add_action( 'init', array( __CLASS__, 'maybe_flush' ), 99 );
		add_filter( 'query_vars', array( __CLASS__, 'register_query_vars' ) );
		add_filter( 'post_type_link', array( __CLASS__, 'lesson_permalink' ), 10, 2 );
		add_action( 'template_redirect', array( __CLASS__, 'enforce_canonical_url' ), 1 );
	}

	/**
	 * The nested rule is registered 'top' so it wins over the course post
	 * type's own /courses/([^/]+)/ rule, which would otherwise swallow the
	 * two-segment request.
	 */
	public static function add_rewrite_rules() {
		add_rewrite_rule(
			'^' . self::COURSE_BASE . '/([^/]+)/([^/]+)/?$',
			'index.php?post_type=lesson&lesson=$matches[2]&jsl_course=$matches[1]',
			'top'
		);

		add_rewrite_rule(
			'^' . self::ORPHAN_BASE . '/([^/]+)/?$',
			'index.php?post_type=lesson&lesson=$matches[1]',
			'top'
		);

		// Legacy flat base — matched only so we can 301 it away.
		add_rewrite_rule(
			'^lessons/([^/]+)/?$',
			'index.php?post_type=lesson&lesson=$matches[1]&jsl_legacy=1',
			'top'
		);
	}

	public static function register_query_vars( $vars ) {
		$vars[] = 'jsl_course';
		$vars[] = 'jsl_legacy';
		return $vars;
	}

	/**
	 * Flush rewrites once per rules version, instead of on every request.
	 */
	public static function maybe_flush() {
		if ( get_option( 'jsl_rewrite_rules_version' ) === self::RULES_VERSION ) {
			return;
		}
		flush_rewrite_rules( false );
		update_option( 'jsl_rewrite_rules_version', self::RULES_VERSION, false );
	}

	/**
	 * Build the canonical lesson URL.
	 *
	 * @param string    $link Default permalink.
	 * @param \WP_Post  $post Post being linked.
	 */
	public static function lesson_permalink( $link, $post ) {
		if ( ! $post instanceof \WP_Post || 'lesson' !== $post->post_type ) {
			return $link;
		}

		// Drafts/autodrafts have no usable slug yet — leave WP's ?p= form alone.
		if ( '' === $post->post_name || in_array( $post->post_status, array( 'auto-draft', 'draft', 'pending' ), true ) ) {
			return $link;
		}

		$course_slug = self::course_slug_for_lesson( (int) $post->ID );

		$path = $course_slug
			? self::COURSE_BASE . '/' . $course_slug . '/' . $post->post_name
			: self::ORPHAN_BASE . '/' . $post->post_name;

		return user_trailingslashit( home_url( '/' . $path ) );
	}

	/**
	 * Slug of the course a lesson belongs to, or '' when it is standalone.
	 * Cached per-request because course pages resolve this once per lesson row.
	 */
	private static function course_slug_for_lesson( int $lesson_id ): string {
		static $cache = array();

		if ( isset( $cache[ $lesson_id ] ) ) {
			return $cache[ $lesson_id ];
		}

		$course_id = (int) get_post_meta( $lesson_id, 'jsl_course_id', true );
		$course    = $course_id ? get_post( $course_id ) : null;

		$cache[ $lesson_id ] = ( $course && 'course' === $course->post_type && $course->post_name )
			? $course->post_name
			: '';

		return $cache[ $lesson_id ];
	}

	/**
	 * One lesson, one URL. Anything that resolved a lesson through a legacy
	 * base, the wrong course, or the wrong base for its kind is redirected
	 * to the canonical permalink.
	 */
	public static function enforce_canonical_url() {
		if ( ! is_singular( 'lesson' ) ) {
			return;
		}

		$post = get_queried_object();
		if ( ! $post instanceof \WP_Post ) {
			return;
		}

		$canonical = get_permalink( $post );
		if ( ! $canonical ) {
			return;
		}

		$request_uri = isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
		$requested   = wp_parse_url( $request_uri, PHP_URL_PATH );
		$expected    = wp_parse_url( $canonical, PHP_URL_PATH );

		if ( untrailingslashit( (string) $requested ) === untrailingslashit( (string) $expected ) ) {
			return;
		}

		wp_safe_redirect( $canonical, 301 );
		exit;
	}
}
