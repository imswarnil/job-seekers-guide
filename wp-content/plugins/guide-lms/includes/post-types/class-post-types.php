<?php
/**
 * Registers Course, Lesson, and Learning Path post types plus the Module taxonomy.
 */

namespace Guide;

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
					'name'          => __( 'Courses', 'guide-lms' ),
					'singular_name' => __( 'Course', 'guide-lms' ),
					'add_new_item'  => __( 'Add New Course', 'guide-lms' ),
					'edit_item'     => __( 'Edit Course', 'guide-lms' ),
				),
				'public'            => true,
				'show_ui'           => true,
				'show_in_menu'      => false,
				'show_in_nav_menus' => true,
				'show_in_rest'      => true,
				'rest_base'         => 'courses',
				'has_archive'       => true,
				'rewrite'           => array( 'slug' => Permalinks::COURSE_BASE, 'with_front' => false ),
				'menu_icon'         => 'dashicons-welcome-learn-more',
				'supports'          => array( 'title', 'editor', 'excerpt', 'thumbnail', 'custom-fields' ),
				'template'          => array(),
			)
		);
	}

	/**
	 * Lessons are public but have no rewrite base of their own — their URLs
	 * are built by Guide\Permalinks as /courses/{course}/{lesson}/ so a lesson
	 * never has a second, duplicate address. show_in_menu is off because all
	 * authoring happens in the LMS console.
	 */
	private static function register_lesson() {
		register_post_type(
			'lesson',
			array(
				'labels'             => array(
					'name'          => __( 'Lessons', 'guide-lms' ),
					'singular_name' => __( 'Lesson', 'guide-lms' ),
					'add_new_item'  => __( 'Add New Lesson', 'guide-lms' ),
					'edit_item'     => __( 'Edit Lesson', 'guide-lms' ),
				),
				'public'             => true,
				'publicly_queryable' => true,
				'show_ui'            => true,
				'show_in_menu'       => false,
				'show_in_nav_menus'  => false,
				'show_in_rest'       => true,
				'rest_base'          => 'lessons',
				'has_archive'        => false,
				'rewrite'            => false,
				'menu_icon'          => 'dashicons-media-document',
				'supports'           => array( 'title', 'editor', 'custom-fields', 'thumbnail' ),
			)
		);
	}

	private static function register_learning_path() {
		register_post_type(
			'learning_path',
			array(
				'labels'       => array(
					'name'          => __( 'Learning Paths', 'guide-lms' ),
					'singular_name' => __( 'Learning Path', 'guide-lms' ),
					'add_new_item'  => __( 'Add New Learning Path', 'guide-lms' ),
					'edit_item'     => __( 'Edit Learning Path', 'guide-lms' ),
				),
				'public'            => true,
				'show_ui'           => true,
				'show_in_menu'      => false,
				'show_in_nav_menus' => true,
				'show_in_rest'      => true,
				'rest_base'         => 'learning-paths',
				'has_archive'       => true,
				'rewrite'           => array( 'slug' => 'learning-paths', 'with_front' => false ),
				'menu_icon'         => 'dashicons-networking',
				'supports'          => array( 'title', 'editor', 'excerpt', 'thumbnail', 'custom-fields' ),
			)
		);
	}

	private static function register_course_category() {
		register_taxonomy(
			'course_category',
			array( 'course' ),
			array(
				'labels'       => array(
					'name'          => __( 'Course Categories', 'guide-lms' ),
					'singular_name' => __( 'Course Category', 'guide-lms' ),
				),
				'public'            => true,
				'show_ui'           => true,
				'show_in_menu'      => false,
				'show_in_rest'      => true,
				'rest_base'         => 'course-categories',
				'hierarchical'      => true,
				'show_admin_column' => true,
				'rewrite'           => array( 'slug' => 'topics', 'with_front' => false ),
			)
		);
	}

	private static function register_module_taxonomy() {
		register_taxonomy(
			'module',
			array( 'course', 'lesson' ),
			array(
				'labels'       => array(
					'name'          => __( 'Modules', 'guide-lms' ),
					'singular_name' => __( 'Module', 'guide-lms' ),
				),
				// Modules live in wp_jsl_modules; the taxonomy is internal
				// bookkeeping only, so it gets no public archive URL.
				'public'       => false,
				'show_ui'      => false,
				'show_in_rest' => false,
				'hierarchical' => true,
				'rewrite'      => false,
			)
		);
	}
}
