<?php
/**
 * Registers Course, Lesson, and Learning Path post types plus the Module taxonomy.
 */

namespace JSL;

defined( 'ABSPATH' ) || exit;

class Post_Types {

	public static function init() {
		add_action( 'init', array( __CLASS__, 'register' ) );
	}

	public static function register() {
		self::register_course_category();
		self::register_course();
		self::register_lesson();
		self::register_learning_path();
		self::register_module_taxonomy();
	}

	private static function register_course() {
		register_post_type(
			'course',
			array(
				'labels'       => array(
					'name'          => __( 'Courses', 'job-seekers-lms' ),
					'singular_name' => __( 'Course', 'job-seekers-lms' ),
					'add_new_item'  => __( 'Add New Course', 'job-seekers-lms' ),
					'edit_item'     => __( 'Edit Course', 'job-seekers-lms' ),
				),
				'public'       => true,
				'show_in_rest' => true,
				'rest_base'    => 'courses',
				'has_archive'  => true,
				'rewrite'      => array( 'slug' => 'courses' ),
				'menu_icon'    => 'dashicons-welcome-learn-more',
				'supports'     => array( 'title', 'editor', 'excerpt', 'thumbnail', 'custom-fields' ),
				'template'     => array(),
			)
		);
	}

	private static function register_lesson() {
		register_post_type(
			'lesson',
			array(
				'labels'       => array(
					'name'          => __( 'Lessons', 'job-seekers-lms' ),
					'singular_name' => __( 'Lesson', 'job-seekers-lms' ),
					'add_new_item'  => __( 'Add New Lesson', 'job-seekers-lms' ),
					'edit_item'     => __( 'Edit Lesson', 'job-seekers-lms' ),
				),
				'public'       => true,
				'show_in_rest' => true,
				'rest_base'    => 'lessons',
				'has_archive'  => false,
				'rewrite'      => array( 'slug' => 'lessons' ),
				'menu_icon'    => 'dashicons-media-document',
				'supports'     => array( 'title', 'editor', 'custom-fields' ),
			)
		);
	}

	private static function register_learning_path() {
		register_post_type(
			'learning_path',
			array(
				'labels'       => array(
					'name'          => __( 'Learning Paths', 'job-seekers-lms' ),
					'singular_name' => __( 'Learning Path', 'job-seekers-lms' ),
					'add_new_item'  => __( 'Add New Learning Path', 'job-seekers-lms' ),
					'edit_item'     => __( 'Edit Learning Path', 'job-seekers-lms' ),
				),
				'public'       => true,
				'show_in_rest' => true,
				'rest_base'    => 'learning-paths',
				'has_archive'  => true,
				'rewrite'      => array( 'slug' => 'learning-paths' ),
				'menu_icon'    => 'dashicons-networking',
				'supports'     => array( 'title', 'editor', 'excerpt', 'thumbnail', 'custom-fields' ),
			)
		);
	}

	private static function register_course_category() {
		register_taxonomy(
			'course_category',
			array( 'course' ),
			array(
				'labels'       => array(
					'name'          => __( 'Course Categories', 'job-seekers-lms' ),
					'singular_name' => __( 'Course Category', 'job-seekers-lms' ),
				),
				'public'       => true,
				'show_in_rest' => true,
				'rest_base'    => 'course-categories',
				'hierarchical' => true,
				'show_admin_column' => true,
				'rewrite'      => array( 'slug' => 'course-category' ),
			)
		);
	}

	private static function register_module_taxonomy() {
		register_taxonomy(
			'module',
			array( 'course', 'lesson' ),
			array(
				'labels'       => array(
					'name'          => __( 'Modules', 'job-seekers-lms' ),
					'singular_name' => __( 'Module', 'job-seekers-lms' ),
				),
				'public'       => true,
				'show_in_rest' => true,
				'hierarchical' => true,
				'rewrite'      => array( 'slug' => 'module' ),
			)
		);
	}
}
