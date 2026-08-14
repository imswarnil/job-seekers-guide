<?php
/**
 * `wp jsl seed` — creates one learning path, three courses (one free, two
 * paid with placeholder Dodo product IDs), each with modules/lessons of
 * real job-seeker content, so the design can be previewed against
 * something other than an empty install.
 */

namespace JSL\Cli;

use JSL\Builder\Tables as Builder_Tables;
use JSL\Payments\Course_Pricing;

defined( 'ABSPATH' ) || exit;

class Seed_Command {

	public static function register() {
		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			\WP_CLI::add_command( 'jsl seed', array( __CLASS__, 'run' ) );
		}
	}

	public static function run() {
		global $wpdb;

		$path_id = wp_insert_post(
			array(
				'post_type'    => 'learning_path',
				'post_title'   => 'Land Your First Tech Job',
				'post_content' => 'A structured path from "no resume" to "signed offer letter" — resume, LinkedIn, interviews, and technical practice, in order.',
				'post_excerpt' => 'A structured path from resume to offer letter.',
				'post_status'  => 'publish',
			)
		);

		$courses = self::course_definitions();

		foreach ( $courses as $order => $course_def ) {
			$course_id = wp_insert_post(
				array(
					'post_type'    => 'course',
					'post_title'   => $course_def['title'],
					'post_excerpt' => $course_def['excerpt'],
					'post_content' => $course_def['content'],
					'post_status'  => 'publish',
					'menu_order'   => $order,
				)
			);

			update_post_meta( $course_id, 'jsl_path_id', $path_id );
			update_post_meta( $course_id, Course_Pricing::META_TYPE, $course_def['pricing_type'] );
			if ( 'paid' === $course_def['pricing_type'] ) {
				update_post_meta( $course_id, Course_Pricing::META_PRODUCT_ID, $course_def['product_id'] );
				update_post_meta( $course_id, Course_Pricing::META_PRICE_LABEL, $course_def['price_label'] );
			}

			foreach ( $course_def['modules'] as $module_order => $module_def ) {
				$wpdb->insert(
					Builder_Tables::table_name(),
					array(
						'course_id'  => $course_id,
						'title'      => $module_def['title'],
						'menu_order' => $module_order,
					),
					array( '%d', '%s', '%d' )
				);
				$module_id = (int) $wpdb->insert_id;

				foreach ( $module_def['lessons'] as $lesson_order => $lesson_def ) {
					$lesson_id = wp_insert_post(
						array(
							'post_type'    => 'lesson',
							'post_title'   => $lesson_def['title'],
							'post_content' => $lesson_def['content'],
							'post_status'  => 'publish',
						)
					);

					update_post_meta( $lesson_id, 'jsl_course_id', $course_id );
					update_post_meta( $lesson_id, 'jsl_module_id', $module_id );
					update_post_meta( $lesson_id, 'jsl_lesson_order', $lesson_order );
				}
			}

			\WP_CLI::log( "Seeded course: {$course_def['title']} (#{$course_id})" );
		}

		\WP_CLI::success( 'Demo content seeded.' );
	}

	private static function course_definitions() {
		return array(
			array(
				'title'        => 'Resume & LinkedIn Basics',
				'excerpt'      => 'Get your resume and LinkedIn profile into shape before you apply anywhere.',
				'content'      => 'Most rejections happen before a human ever reads your resume — this course covers the formatting, keywords, and LinkedIn setup that get you past the first filter.',
				'pricing_type' => 'free',
				'modules'      => array(
					array(
						'title'   => 'Resume Fundamentals',
						'lessons' => array(
							array( 'title' => 'Formatting that survives an ATS', 'content' => 'Applicant tracking systems parse your resume as plain text before a human sees it. Skip tables, columns, and headers/footers — they get mangled. Use standard section titles ("Experience", "Education") so the parser recognizes them.' ),
							array( 'title' => 'Writing bullet points that show impact', 'content' => 'Weak: "Responsible for customer support." Strong: "Resolved 40+ support tickets/week, cutting average response time from 6 hours to 90 minutes." Lead with the verb, end with the number.' ),
						),
					),
					array(
						'title'   => 'LinkedIn Setup',
						'lessons' => array(
							array( 'title' => 'Profile photo and headline', 'content' => 'Profiles with a photo get significantly more views. Your headline should say what you do and for whom — not just your job title.' ),
							array( 'title' => 'Turning on "Open to Work"', 'content' => 'The recruiter-only visibility setting lets you signal availability without your current employer seeing it in their feed.' ),
						),
					),
				),
			),
			array(
				'title'        => 'Interview Preparation',
				'excerpt'      => 'Behavioral interviews, salary negotiation, and how to research a company before you walk in.',
				'content'      => 'The interview is a two-way evaluation. This course covers how to prepare answers that are specific and memorable, and how to negotiate once an offer is on the table.',
				'pricing_type' => 'paid',
				'product_id'   => '',
				'price_label'  => '$49',
				'modules'      => array(
					array(
						'title'   => 'Behavioral Questions',
						'lessons' => array(
							array( 'title' => 'The STAR method', 'content' => 'Situation, Task, Action, Result. Most candidates skip straight to Action and never quantify the Result — that\'s the part interviewers remember.' ),
							array( 'title' => '"Tell me about a conflict" without badmouthing anyone', 'content' => 'Frame the disagreement around the work, not the person. Interviewers are listening for how you handle friction, not for gossip.' ),
						),
					),
					array(
						'title'   => 'Negotiation',
						'lessons' => array(
							array( 'title' => 'Getting the offer in writing before responding', 'content' => 'Never negotiate verbally on the spot. Ask for the offer in writing, then take at least 24 hours before countering.' ),
						),
					),
				),
			),
			array(
				'title'        => 'Technical Interview Practice',
				'excerpt'      => 'Coding rounds, system design basics, and how to think out loud without freezing up.',
				'content'      => 'Technical interviews test process as much as correctness. This course focuses on structuring your thinking so an interviewer can follow along, even when you don\'t immediately know the answer.',
				'pricing_type' => 'paid',
				'product_id'   => '',
				'price_label'  => '$79',
				'modules'      => array(
					array(
						'title'   => 'Coding Rounds',
						'lessons' => array(
							array( 'title' => 'Talking through a problem before coding', 'content' => 'State your understanding of the problem back to the interviewer, ask about edge cases, then describe your approach before writing a single line.' ),
							array( 'title' => 'What to do when you\'re stuck', 'content' => 'Narrate what you\'ve ruled out and why. A visibly-reasoned dead end is more valuable to the interviewer than silence.' ),
						),
					),
					array(
						'title'   => 'System Design Basics',
						'lessons' => array(
							array( 'title' => 'Clarifying requirements first', 'content' => 'Before drawing any boxes, nail down scale (how many users?), read/write ratio, and consistency requirements — they determine every downstream decision.' ),
						),
					),
				),
			),
		);
	}
}
