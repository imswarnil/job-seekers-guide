<?php
/**
 * `wp jsl seed [--fresh]` — seeds a realistic job-seeker curriculum:
 * two learning paths, four coded courses across categories, modules with
 * article/video/quiz lessons (durations, previews, video clip ranges,
 * server-graded quizzes). --fresh deletes existing LMS content first.
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

	public static function run( $args, $assoc_args ) {
		global $wpdb;

		if ( ! empty( $assoc_args['fresh'] ) ) {
			self::wipe();
			\WP_CLI::log( 'Existing LMS content removed.' );
		}

		$categories = array();
		foreach ( array( 'Career Foundations', 'Interviewing', 'Technical Prep' ) as $name ) {
			$term = term_exists( $name, 'course_category' ) ?: wp_insert_term( $name, 'course_category' );
			$categories[ $name ] = (int) ( is_array( $term ) ? $term['term_id'] : $term );
		}

		$path_ids = array();
		foreach ( self::paths() as $slug => $path ) {
			$path_ids[ $slug ] = wp_insert_post(
				array(
					'post_type'    => 'learning_path',
					'post_title'   => $path['title'],
					'post_content' => $path['content'],
					'post_excerpt' => $path['excerpt'],
					'post_status'  => 'publish',
					'menu_order'   => $path['order'],
				)
			);
		}

		foreach ( self::courses() as $order => $def ) {
			$course_id = wp_insert_post(
				array(
					'post_type'    => 'course',
					'post_title'   => $def['title'],
					'post_excerpt' => $def['excerpt'],
					'post_content' => $def['content'],
					'post_status'  => 'publish',
					'menu_order'   => $order,
				)
			);

			update_post_meta( $course_id, 'jsl_path_id', $path_ids[ $def['path'] ] );
			update_post_meta( $course_id, 'jsl_course_code', $def['code'] );
			wp_set_post_terms( $course_id, array( $categories[ $def['category'] ] ), 'course_category' );

			update_post_meta( $course_id, Course_Pricing::META_TYPE, $def['pricing'] );
			if ( 'paid' === $def['pricing'] ) {
				update_post_meta( $course_id, Course_Pricing::META_PRODUCT_ID, $def['product_id'] );
				update_post_meta( $course_id, Course_Pricing::META_PRICE_LABEL, $def['price'] );
			}

			foreach ( $def['modules'] as $module_order => $module ) {
				$wpdb->insert(
					Builder_Tables::table_name(),
					array( 'course_id' => $course_id, 'title' => $module['title'], 'menu_order' => $module_order ),
					array( '%d', '%s', '%d' )
				);
				$module_id = (int) $wpdb->insert_id;

				foreach ( $module['lessons'] as $lesson_order => $lesson ) {
					$lesson_id = wp_insert_post(
						array(
							'post_type'    => 'lesson',
							'post_title'   => $lesson['title'],
							'post_content' => $lesson['content'] ?? self::article_body( $lesson['title'] ),
							'post_status'  => 'publish',
						)
					);

					update_post_meta( $lesson_id, 'jsl_course_id', $course_id );
					update_post_meta( $lesson_id, 'jsl_module_id', $module_id );
					update_post_meta( $lesson_id, 'jsl_lesson_order', $lesson_order );
					update_post_meta( $lesson_id, 'jsl_lesson_type', $lesson['type'] ?? 'article' );
					update_post_meta( $lesson_id, 'jsl_duration_minutes', $lesson['minutes'] ?? 0 );
					update_post_meta( $lesson_id, 'jsl_is_preview', empty( $lesson['preview'] ) ? 0 : 1 );

					if ( ! empty( $lesson['video'] ) ) {
						update_post_meta( $lesson_id, 'jsl_video_url', $lesson['video'] );
						update_post_meta( $lesson_id, 'jsl_video_start', $lesson['start'] ?? 0 );
						update_post_meta( $lesson_id, 'jsl_video_end', $lesson['end'] ?? 0 );
					}

					if ( ! empty( $lesson['quiz'] ) ) {
						update_post_meta( $lesson_id, 'jsl_quiz', wp_slash( wp_json_encode( $lesson['quiz'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) ) );
					}
				}
			}

			\WP_CLI::log( sprintf( 'Seeded %s — %s', $def['code'], $def['title'] ) );
		}

		\WP_CLI::success( 'Curriculum seeded.' );
	}

	private static function wipe() {
		global $wpdb;

		foreach ( array( 'course', 'lesson', 'learning_path' ) as $type ) {
			foreach ( get_posts( array( 'post_type' => $type, 'posts_per_page' => -1, 'post_status' => 'any', 'fields' => 'ids' ) ) as $post_id ) {
				wp_delete_post( $post_id, true );
			}
		}
		$wpdb->query( 'TRUNCATE TABLE ' . Builder_Tables::table_name() );
	}

	/**
	 * Generic but plausible article body used when a lesson has no
	 * hand-written content.
	 */
	private static function article_body( string $title ): string {
		return '<p>' . esc_html( $title ) . ' is one of those steps most candidates skip — and it shows. In this lesson you\'ll learn the exact approach hiring managers respond to, why the common advice fails, and how to practice it in under twenty minutes.</p>'
			. '<h2>What you\'ll do</h2>'
			. '<ul><li>See the mistake most candidates make and why it costs interviews.</li>'
			. '<li>Walk through a worked example, line by line.</li>'
			. '<li>Apply the checklist to your own materials before moving on.</li></ul>'
			. '<p>Take your time here — the lessons that follow build directly on this one. When you\'re done, mark the lesson complete so your progress stays accurate.</p>';
	}

	private static function paths(): array {
		return array(
			'first-job' => array(
				'order'   => 0,
				'title'   => 'Land Your First Tech Job',
				'excerpt' => 'From "no resume" to "signed offer": resume, LinkedIn, interviews, and technical practice — in order.',
				'content' => 'Work through the four stages every successful job search moves through. Each course unlocks the next skill: getting seen, telling your story, proving your skills, and closing the deal.',
			),
			'negotiate' => array(
				'order'   => 1,
				'title'   => 'Negotiate Like a Pro',
				'excerpt' => 'Most candidates leave 10–20% on the table. This path is how you don\'t.',
				'content' => 'Short and surgical: market research, the rules of the money conversation, and word-for-word scripts for the offer stage.',
			),
		);
	}

	private static function courses(): array {
		return array(
			array(
				'path'     => 'first-job',
				'code'     => 'JSG-101',
				'category' => 'Career Foundations',
				'title'    => 'Resume & LinkedIn Mastery',
				'excerpt'  => 'Get past the six-second scan and the ATS — and make recruiters come to you.',
				'content'  => '<p>Most rejections happen before a human ever reads your resume. This course fixes the two documents that decide whether you get a first conversation: your resume and your LinkedIn profile.</p>',
				'pricing'  => 'free',
				'modules'  => array(
					array(
						'title'   => 'Resume Foundations',
						'lessons' => array(
							array( 'title' => 'The six-second recruiter scan', 'minutes' => 7, 'preview' => true ),
							array( 'title' => 'Formatting that survives an ATS', 'minutes' => 9 ),
							array( 'title' => 'Bullet points with the XYZ formula', 'minutes' => 12 ),
							array(
								'title' => 'Checkpoint: resume fundamentals',
								'type'  => 'quiz',
								'minutes' => 5,
								'quiz'  => array(
									'pass'      => 70,
									'questions' => array(
										array(
											'q'       => 'How long does a recruiter typically spend on a first resume scan?',
											'options' => array( 'About 6 seconds', 'About 2 minutes', 'About 30 seconds', 'Until they finish reading it' ),
											'correct' => 0,
											'explain' => 'Eye-tracking studies put the first scan at 6–8 seconds — your top third has to land.',
										),
										array(
											'q'       => 'Which format is safest for automated (ATS) parsing?',
											'options' => array( 'Two-column with graphics', 'Single-column with standard headings', 'A designed PDF from Canva', 'A table-based layout' ),
											'correct' => 1,
											'explain' => 'Parsers read top-to-bottom; columns and tables scramble the order.',
										),
										array(
											'q'       => 'The XYZ formula for bullet points is…',
											'options' => array( 'Accomplished X as measured by Y by doing Z', 'eXplain, Yield, Zoom', 'A LaTeX template', 'A LinkedIn feature' ),
											'correct' => 0,
											'explain' => 'Result first, measurement second, method third — that\'s what interviewers remember.',
										),
									),
								),
							),
						),
					),
					array(
						'title'   => 'LinkedIn That Gets Found',
						'lessons' => array(
							array( 'title' => 'Headline and photo that pass the filter', 'minutes' => 8 ),
							array( 'title' => 'Turning on Open to Work the right way', 'minutes' => 6 ),
							array( 'title' => 'A keyword-rich About section', 'minutes' => 10 ),
						),
					),
				),
			),
			array(
				'path'       => 'first-job',
				'code'       => 'JSG-201',
				'category'   => 'Interviewing',
				'title'      => 'Behavioral Interview Prep',
				'excerpt'    => 'A story bank, the STAR method, and calm answers to the hard questions.',
				'content'    => '<p>Behavioral rounds are won before the interview: you build eight strong stories once, then adapt them to almost any question. This course is the system for doing that.</p>',
				'pricing'    => 'paid',
				'product_id' => 'prod_behavioral_prep',
				'price'      => '$29',
				'modules'    => array(
					array(
						'title'   => 'The Storytelling System',
						'lessons' => array(
							array(
								'title'   => 'The STAR method, demonstrated',
								'type'    => 'video',
								'minutes' => 8,
								'preview' => true,
								'video'   => 'https://www.youtube.com/watch?v=WFcYF_pxLgA',
								'start'   => 35,
								'end'     => 320,
								'content' => '<p>Watch the clipped demo above — we jump you straight to the worked example. Situation, Task, Action, Result: most candidates skip to Action and never quantify the Result, which is the part interviewers actually remember.</p><h2>After watching</h2><ul><li>Pick one real project and write it as four STAR sentences.</li><li>Say it out loud in under 90 seconds.</li></ul>',
							),
							array( 'title' => 'Building your story bank (8 stories)', 'minutes' => 15 ),
							array( 'title' => '"Tell me about yourself" in 90 seconds', 'minutes' => 10 ),
						),
					),
					array(
						'title'   => 'The Hard Questions',
						'lessons' => array(
							array( 'title' => '"Tell me about a conflict" without badmouthing anyone', 'minutes' => 9 ),
							array( 'title' => '"Why should we hire you?"', 'minutes' => 7 ),
							array(
								'title' => 'Checkpoint: behavioral answers',
								'type'  => 'quiz',
								'minutes' => 5,
								'quiz'  => array(
									'pass'      => 70,
									'questions' => array(
										array(
											'q'       => 'In STAR, which part do most candidates under-deliver?',
											'options' => array( 'Situation', 'Task', 'Action', 'Result' ),
											'correct' => 3,
											'explain' => 'Quantified results ("cut load time 40%") are rare — and memorable.',
										),
										array(
											'q'       => 'When asked about a conflict, you should never…',
											'options' => array( 'Describe the disagreement', 'Badmouth the other person', 'Explain the resolution', 'Mention what you learned' ),
											'correct' => 1,
											'explain' => 'Interviewers assume how you talk about past colleagues is how you\'ll talk about them.',
										),
									),
								),
							),
						),
					),
				),
			),
			array(
				'path'       => 'first-job',
				'code'       => 'JSG-301',
				'category'   => 'Technical Prep',
				'title'      => 'Technical Interview Practice',
				'excerpt'    => 'Think out loud, get unstuck gracefully, and handle system design basics.',
				'content'    => '<p>Technical rounds test how you think, not just what you know. This course drills the communication patterns that make interviewers root for you.</p>',
				'pricing'    => 'paid',
				'product_id' => 'prod_technical_prep',
				'price'      => '$49',
				'modules'    => array(
					array(
						'title'   => 'Coding Rounds',
						'lessons' => array(
							array( 'title' => 'Talking through a problem before coding', 'minutes' => 11, 'preview' => true ),
							array( 'title' => 'What to do when you\'re stuck', 'minutes' => 8 ),
							array( 'title' => 'Big-O in plain English', 'minutes' => 14 ),
						),
					),
					array(
						'title'   => 'System Design Basics',
						'lessons' => array(
							array( 'title' => 'Clarifying requirements first', 'minutes' => 9 ),
							array( 'title' => 'Load balancers, caches, and queues', 'minutes' => 16 ),
							array(
								'title' => 'Checkpoint: technical readiness',
								'type'  => 'quiz',
								'minutes' => 6,
								'quiz'  => array(
									'pass'      => 70,
									'questions' => array(
										array(
											'q'       => 'What should you do first in a system design interview?',
											'options' => array( 'Draw boxes and arrows', 'Clarify requirements and scale', 'Pick a database', 'Estimate salaries' ),
											'correct' => 1,
											'explain' => 'Designing before scoping is the most common failure mode.',
										),
										array(
											'q'       => 'You\'re stuck mid-problem. Best move?',
											'options' => array( 'Go silent and think', 'State what you know, what you\'ve ruled out, and your next test', 'Start over', 'Ask for the answer' ),
											'correct' => 1,
											'explain' => 'Narrating your debugging process is itself the skill being assessed.',
										),
									),
								),
							),
						),
					),
				),
			),
			array(
				'path'     => 'negotiate',
				'code'     => 'JSG-401',
				'category' => 'Career Foundations',
				'title'    => 'Salary Negotiation Essentials',
				'excerpt'  => 'Research your number, hold the line, and counter with confidence.',
				'content'  => '<p>Negotiation is a 20-minute conversation worth thousands. Learn the rules before you\'re in it.</p>',
				'pricing'  => 'free',
				'modules'  => array(
					array(
						'title'   => 'Before the Offer',
						'lessons' => array(
							array( 'title' => 'Researching your market rate', 'minutes' => 10, 'preview' => true ),
							array( 'title' => 'The "no first number" rule', 'minutes' => 8 ),
						),
					),
					array(
						'title'   => 'After the Offer',
						'lessons' => array(
							array( 'title' => 'Getting the offer in writing before responding', 'minutes' => 7 ),
							array( 'title' => 'Counter-offer scripts that work', 'minutes' => 12 ),
							array(
								'title' => 'Checkpoint: negotiation',
								'type'  => 'quiz',
								'minutes' => 4,
								'quiz'  => array(
									'pass'      => 70,
									'questions' => array(
										array(
											'q'       => 'A recruiter asks your salary expectations in the first call. You…',
											'options' => array( 'Give your current salary', 'Give a specific number', 'Deflect politely and ask for their range', 'Refuse to discuss money' ),
											'correct' => 2,
											'explain' => 'Whoever names the first number anchors the negotiation — make it them.',
										),
										array(
											'q'       => 'You get a verbal offer. First step?',
											'options' => array( 'Accept immediately', 'Counter immediately', 'Ask for it in writing and time to review', 'Ghost them for a week' ),
											'correct' => 2,
											'explain' => 'Written offers are real offers; time pressure is a tactic, not a rule.',
										),
									),
								),
							),
						),
					),
				),
			),
		);
	}
}
