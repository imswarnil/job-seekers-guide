<?php
/**
 * Course meta: the short course code (e.g. "JSG-101") shown on cards,
 * placeholders, and JSON-LD courseCode.
 */

namespace JSL;

defined( 'ABSPATH' ) || exit;

class Course_Meta {

	public static function init() {
		add_action( 'init', array( __CLASS__, 'register_meta' ) );
	}

	public static function register_meta() {
		register_post_meta(
			'course',
			'jsl_course_code',
			array(
				'type'              => 'string',
				'single'            => true,
				'show_in_rest'      => true,
				'sanitize_callback' => array( __CLASS__, 'sanitize_code' ),
				'auth_callback'     => function () {
					return current_user_can( 'edit_posts' );
				},
			)
		);
	}

	/**
	 * Codes are uppercase letters/digits/dashes, max 12 chars ("JSG-101").
	 */
	public static function sanitize_code( $value ): string {
		$value = strtoupper( preg_replace( '/[^A-Za-z0-9\-]/', '', (string) $value ) );
		return substr( $value, 0, 12 );
	}

	public static function get_code( int $course_id ): string {
		return (string) get_post_meta( $course_id, 'jsl_course_code', true );
	}
}
