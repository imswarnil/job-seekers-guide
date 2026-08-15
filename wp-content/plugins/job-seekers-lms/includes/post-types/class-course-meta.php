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

	/** Difficulty levels a course can be tagged with. */
	const LEVELS = array( 'beginner', 'intermediate', 'advanced' );

	public static function register_meta() {
		$can_edit = function () {
			return current_user_can( 'edit_posts' );
		};

		register_post_meta(
			'course',
			'jsl_course_code',
			array(
				'type'              => 'string',
				'single'            => true,
				'show_in_rest'      => true,
				'sanitize_callback' => array( __CLASS__, 'sanitize_code' ),
				'auth_callback'     => $can_edit,
			)
		);

		register_post_meta(
			'course',
			'jsl_course_level',
			array(
				'type'              => 'string',
				'single'            => true,
				'show_in_rest'      => true,
				'sanitize_callback' => array( __CLASS__, 'sanitize_level' ),
				'auth_callback'     => $can_edit,
			)
		);

		// "What you'll learn" and "Requirements" are lists of short lines.
		// Stored as arrays so the console can edit them as repeatable rows
		// rather than asking authors to hand-write markup.
		foreach ( array( 'jsl_course_outcomes', 'jsl_course_requirements' ) as $key ) {
			register_post_meta(
				'course',
				$key,
				array(
					'type'              => 'array',
					'single'            => true,
					'default'           => array(),
					'show_in_rest'      => array(
						'schema' => array(
							'type'  => 'array',
							'items' => array( 'type' => 'string' ),
						),
					),
					'sanitize_callback' => array( __CLASS__, 'sanitize_lines' ),
					'auth_callback'     => $can_edit,
				)
			);
		}
	}

	public static function sanitize_level( $value ): string {
		$value = sanitize_key( (string) $value );
		return in_array( $value, self::LEVELS, true ) ? $value : '';
	}

	/**
	 * A list of plain one-line strings: no markup, no blanks, capped so a
	 * runaway client can't write unbounded meta.
	 *
	 * @param mixed $value
	 * @return string[]
	 */
	public static function sanitize_lines( $value ): array {
		if ( ! is_array( $value ) ) {
			return array();
		}

		$lines = array();

		foreach ( $value as $line ) {
			$line = trim( sanitize_text_field( (string) $line ) );
			if ( '' !== $line ) {
				$lines[] = mb_substr( $line, 0, 200 );
			}
			if ( count( $lines ) >= 20 ) {
				break;
			}
		}

		return $lines;
	}

	public static function get_level( int $course_id ): string {
		return (string) get_post_meta( $course_id, 'jsl_course_level', true );
	}

	/** @return string[] */
	public static function get_outcomes( int $course_id ): array {
		$value = get_post_meta( $course_id, 'jsl_course_outcomes', true );
		return is_array( $value ) ? $value : array();
	}

	/** @return string[] */
	public static function get_requirements( int $course_id ): array {
		$value = get_post_meta( $course_id, 'jsl_course_requirements', true );
		return is_array( $value ) ? $value : array();
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
