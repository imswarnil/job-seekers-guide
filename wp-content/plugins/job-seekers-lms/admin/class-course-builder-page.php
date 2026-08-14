<?php
/**
 * wp-admin course builder screen: drag-and-drop modules/lessons for one course.
 */

namespace JSL\Admin;

defined( 'ABSPATH' ) || exit;

class Course_Builder_Page {

	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'register_menu' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue' ) );
	}

	public static function register_menu() {
		add_submenu_page(
			'edit.php?post_type=course',
			__( 'Course Builder', 'job-seekers-lms' ),
			__( 'Course Builder', 'job-seekers-lms' ),
			'edit_posts',
			'jsl-course-builder',
			array( __CLASS__, 'render' )
		);
	}

	public static function enqueue( $hook ) {
		if ( 'course_page_jsl-course-builder' !== $hook ) {
			return;
		}

		wp_enqueue_style( 'jsl-course-builder', JSL_PLUGIN_URL . 'admin/assets/css/course-builder.css', array(), JSL_VERSION );
		wp_enqueue_script( 'jsl-course-builder', JSL_PLUGIN_URL . 'admin/assets/js/course-builder.js', array(), JSL_VERSION, true );

		$course_id = isset( $_GET['course'] ) ? (int) $_GET['course'] : 0;

		wp_localize_script(
			'jsl-course-builder',
			'jslBuilder',
			array(
				'restUrl'  => esc_url_raw( rest_url( 'jsl/v1' ) ),
				'nonce'    => wp_create_nonce( 'wp_rest' ),
				'courseId' => $course_id,
			)
		);
	}

	public static function render() {
		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_die( esc_html__( 'You do not have permission to access the course builder.', 'job-seekers-lms' ) );
		}

		$course_id = isset( $_GET['course'] ) ? (int) $_GET['course'] : 0;

		echo '<div class="wrap jsl-builder-wrap">';
		echo '<h1>' . esc_html__( 'Course Builder', 'job-seekers-lms' ) . '</h1>';

		if ( ! $course_id ) {
			self::render_course_picker();
		} else {
			self::render_builder( $course_id );
		}

		echo '</div>';
	}

	private static function render_course_picker() {
		$courses = get_posts( array( 'post_type' => 'course', 'posts_per_page' => -1 ) );

		if ( empty( $courses ) ) {
			echo '<p>' . esc_html__( 'No courses yet. Create one first, then come back here to build it.', 'job-seekers-lms' ) . '</p>';
			return;
		}

		echo '<p>' . esc_html__( 'Pick a course to build:', 'job-seekers-lms' ) . '</p><ul>';
		foreach ( $courses as $course ) {
			$url = esc_url( add_query_arg( array( 'page' => 'jsl-course-builder', 'course' => $course->ID ), admin_url( 'edit.php?post_type=course' ) ) );
			echo '<li><a href="' . $url . '">' . esc_html( get_the_title( $course ) ) . '</a></li>';
		}
		echo '</ul>';
	}

	private static function render_builder( $course_id ) {
		$course = get_post( $course_id );
		if ( ! $course || 'course' !== $course->post_type ) {
			echo '<p>' . esc_html__( 'Course not found.', 'job-seekers-lms' ) . '</p>';
			return;
		}

		echo '<h2>' . esc_html( get_the_title( $course ) ) . '</h2>';
		echo '<div id="jsl-builder-root" data-course-id="' . esc_attr( $course_id ) . '"><p>' . esc_html__( 'Loading…', 'job-seekers-lms' ) . '</p></div>';
	}
}
