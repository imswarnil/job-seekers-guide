<?php
/**
 * Course and lesson settings in the standard WordPress editor.
 *
 * The console is a better way to build a course — drag lessons between
 * sections, reorder without saving, see the whole shape at once. It is also a
 * bespoke interface, and somebody who already knows WordPress should not have
 * to learn it to change a duration.
 *
 * So both work, on the same fields. Everything here reads and writes exactly
 * the meta keys the console and the templates use, which means a course can be
 * started in one and finished in the other with nothing lost. Where the two
 * would disagree — lesson order inside a section, which is a structural
 * relationship rather than a property of the lesson — this defers to the
 * console and says so, rather than offering a control that quietly does
 * something different.
 */

namespace Guide\Admin;

use Guide\Course_Meta;
use Guide\Payments\Course_Access;

defined( 'ABSPATH' ) || exit;

class Meta_Boxes {

	const NONCE = 'guide_meta_boxes';

	public static function init() {
		add_action( 'add_meta_boxes', array( __CLASS__, 'register' ) );
		add_action( 'save_post_course', array( __CLASS__, 'save_course' ), 10, 2 );
		add_action( 'save_post_lesson', array( __CLASS__, 'save_lesson' ), 10, 2 );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'styles' ) );
	}

	public static function register() {
		add_meta_box(
			'guide-course-settings',
			__( 'Course settings', 'guide-lms' ),
			array( __CLASS__, 'course_box' ),
			'course',
			'normal',
			'high'
		);

		add_meta_box(
			'guide-lesson-settings',
			__( 'Lesson settings', 'guide-lms' ),
			array( __CLASS__, 'lesson_box' ),
			'lesson',
			'normal',
			'high'
		);
	}

	public static function styles( $hook ) {
		if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
			return;
		}

		$css = '
			.guide-mb{display:grid;grid-template-columns:repeat(auto-fit,minmax(16rem,1fr));gap:14px 22px}
			.guide-mb label{display:block;font-weight:600;margin-bottom:4px}
			.guide-mb input[type=text],.guide-mb input[type=number],.guide-mb input[type=url],.guide-mb select,.guide-mb textarea{width:100%}
			.guide-mb__full{grid-column:1/-1}
			.guide-mb__note{margin:10px 0 0;color:#646970;font-size:12px;line-height:1.5}
			.guide-mb__hr{grid-column:1/-1;margin:6px 0;border:0;border-top:1px solid #dcdcde}
			.guide-mb textarea{min-height:6em}
		';

		wp_register_style( 'guide-meta-boxes', false, array(), GUIDE_VERSION );
		wp_enqueue_style( 'guide-meta-boxes' );
		wp_add_inline_style( 'guide-meta-boxes', $css );
	}

	// -------------------------------------------------------------------------
	// Course
	// -------------------------------------------------------------------------

	public static function course_box( \WP_Post $post ) {
		wp_nonce_field( self::NONCE, 'guide_meta_nonce' );

		$code   = (string) get_post_meta( $post->ID, 'jsl_course_code', true );
		$level  = Course_Meta::get_level( $post->ID );
		$header = Course_Meta::get_header( $post->ID );
		$tier   = (string) get_post_meta( $post->ID, Course_Access::META_TIER, true );

		$outcomes = (array) get_post_meta( $post->ID, 'jsl_course_outcomes', true );
		$reqs     = (array) get_post_meta( $post->ID, 'jsl_course_requirements', true );
		?>
		<div class="guide-mb">
			<div>
				<label for="jsl_course_code"><?php esc_html_e( 'Course code', 'guide-lms' ); ?></label>
				<input type="text" id="jsl_course_code" name="jsl_course_code" value="<?php echo esc_attr( $code ); ?>" placeholder="CS-000">
			</div>

			<div>
				<label for="jsl_course_level"><?php esc_html_e( 'Level', 'guide-lms' ); ?></label>
				<select id="jsl_course_level" name="jsl_course_level">
					<option value=""><?php esc_html_e( 'Not specified', 'guide-lms' ); ?></option>
					<?php foreach ( Course_Meta::LEVELS as $value ) : ?>
						<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $level, $value ); ?>>
							<?php echo esc_html( ucfirst( $value ) ); ?>
						</option>
					<?php endforeach; ?>
				</select>
			</div>

			<div>
				<label for="jsl_course_header"><?php esc_html_e( 'Header style', 'guide-lms' ); ?></label>
				<select id="jsl_course_header" name="jsl_course_header">
					<?php foreach ( Course_Meta::HEADERS as $value => $label ) : ?>
						<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $header, $value ); ?>>
							<?php echo esc_html( $label ); ?>
						</option>
					<?php endforeach; ?>
				</select>
			</div>

			<div>
				<label for="jsl_pricing_type"><?php esc_html_e( 'Access', 'guide-lms' ); ?></label>
				<select id="jsl_pricing_type" name="jsl_pricing_type">
					<option value="free" <?php selected( Course_Access::TIER_FREE, Course_Access::sanitize_tier( $tier ) ); ?>>
						<?php esc_html_e( 'Free — open to everyone', 'guide-lms' ); ?>
					</option>
					<option value="premium" <?php selected( Course_Access::TIER_PREMIUM, Course_Access::sanitize_tier( $tier ) ); ?>>
						<?php esc_html_e( 'Members — included in the subscription', 'guide-lms' ); ?>
					</option>
				</select>
			</div>

			<hr class="guide-mb__hr">

			<div class="guide-mb__full">
				<label for="jsl_course_outcomes"><?php esc_html_e( 'What you’ll learn', 'guide-lms' ); ?></label>
				<textarea id="jsl_course_outcomes" name="jsl_course_outcomes" rows="5"><?php echo esc_textarea( implode( "\n", array_map( 'strval', $outcomes ) ) ); ?></textarea>
				<p class="guide-mb__note"><?php esc_html_e( 'One per line.', 'guide-lms' ); ?></p>
			</div>

			<div class="guide-mb__full">
				<label for="jsl_course_requirements"><?php esc_html_e( 'Requirements', 'guide-lms' ); ?></label>
				<textarea id="jsl_course_requirements" name="jsl_course_requirements" rows="4"><?php echo esc_textarea( implode( "\n", array_map( 'strval', $reqs ) ) ); ?></textarea>
				<p class="guide-mb__note"><?php esc_html_e( 'One per line.', 'guide-lms' ); ?></p>
			</div>

			<p class="guide-mb__note guide-mb__full">
				<?php
				printf(
					/* translators: %s: link to the course builder. */
					esc_html__( 'Sections and the order of lessons are structure rather than settings, so they live in the builder: %s', 'guide-lms' ),
					'<a href="' . esc_url( admin_url( 'admin.php?page=guide-lms#/courses/' . $post->ID ) ) . '">' . esc_html__( 'open this course in the builder', 'guide-lms' ) . '</a>'
				);
				?>
			</p>
		</div>
		<?php
	}

	/**
	 * @param int      $post_id
	 * @param \WP_Post $post
	 */
	public static function save_course( $post_id, $post ) {
		if ( ! self::may_save( $post_id ) ) {
			return;
		}

		// phpcs:disable WordPress.Security.NonceVerification.Missing
		update_post_meta( $post_id, 'jsl_course_code', Course_Meta::sanitize_code( wp_unslash( $_POST['jsl_course_code'] ?? '' ) ) );
		update_post_meta( $post_id, 'jsl_course_level', Course_Meta::sanitize_level( wp_unslash( $_POST['jsl_course_level'] ?? '' ) ) );
		update_post_meta( $post_id, 'jsl_course_header', Course_Meta::sanitize_header( wp_unslash( $_POST['jsl_course_header'] ?? '' ) ) );
		update_post_meta( $post_id, Course_Access::META_TIER, Course_Access::sanitize_tier( wp_unslash( $_POST['jsl_pricing_type'] ?? '' ) ) );

		update_post_meta( $post_id, 'jsl_course_outcomes', self::lines( $_POST['jsl_course_outcomes'] ?? '' ) );
		update_post_meta( $post_id, 'jsl_course_requirements', self::lines( $_POST['jsl_course_requirements'] ?? '' ) );
		// phpcs:enable WordPress.Security.NonceVerification.Missing
	}

	// -------------------------------------------------------------------------
	// Lesson
	// -------------------------------------------------------------------------

	public static function lesson_box( \WP_Post $post ) {
		wp_nonce_field( self::NONCE, 'guide_meta_nonce' );

		$course_id = (int) get_post_meta( $post->ID, 'jsl_course_id', true );
		$type      = (string) get_post_meta( $post->ID, 'jsl_lesson_type', true );
		$minutes   = (int) get_post_meta( $post->ID, 'jsl_duration_minutes', true );
		$preview   = (bool) get_post_meta( $post->ID, 'jsl_is_preview', true );
		$video     = (string) get_post_meta( $post->ID, 'jsl_video_url', true );
		$start     = (int) get_post_meta( $post->ID, 'jsl_video_start', true );
		$end       = (int) get_post_meta( $post->ID, 'jsl_video_end', true );

		$courses = get_posts(
			array(
				'post_type'      => 'course',
				'post_status'    => array( 'publish', 'draft', 'pending' ),
				'posts_per_page' => 200,
				'orderby'        => 'title',
				'order'          => 'ASC',
			)
		);
		?>
		<div class="guide-mb">
			<div>
				<label for="jsl_course_id"><?php esc_html_e( 'Course', 'guide-lms' ); ?></label>
				<select id="jsl_course_id" name="jsl_course_id">
					<option value="0"><?php esc_html_e( 'Not in a course yet', 'guide-lms' ); ?></option>
					<?php foreach ( $courses as $course ) : ?>
						<option value="<?php echo esc_attr( (string) $course->ID ); ?>" <?php selected( $course_id, $course->ID ); ?>>
							<?php echo esc_html( get_the_title( $course ) ); ?>
						</option>
					<?php endforeach; ?>
				</select>
				<p class="guide-mb__note">
					<?php esc_html_e( 'The lesson’s home course: it decides the URL, who may read it, and where progress is recorded.', 'guide-lms' ); ?>
				</p>
			</div>

			<div>
				<label for="jsl_lesson_type"><?php esc_html_e( 'Type', 'guide-lms' ); ?></label>
				<select id="jsl_lesson_type" name="jsl_lesson_type">
					<?php
					$types = array(
						'article' => __( 'Article', 'guide-lms' ),
						'video'   => __( 'Video', 'guide-lms' ),
						'quiz'    => __( 'Quiz', 'guide-lms' ),
					);

					foreach ( $types as $value => $label ) :
						?>
						<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $type ? $type : 'article', $value ); ?>>
							<?php echo esc_html( $label ); ?>
						</option>
					<?php endforeach; ?>
				</select>
			</div>

			<div>
				<label for="jsl_duration_minutes"><?php esc_html_e( 'Duration (minutes)', 'guide-lms' ); ?></label>
				<input type="number" min="0" max="600" id="jsl_duration_minutes" name="jsl_duration_minutes" value="<?php echo esc_attr( (string) $minutes ); ?>">
				<p class="guide-mb__note"><?php esc_html_e( 'Used for the course totals and the sidebar.', 'guide-lms' ); ?></p>
			</div>

			<div>
				<label for="jsl_is_preview"><?php esc_html_e( 'Free preview', 'guide-lms' ); ?></label>
				<label style="font-weight:400">
					<input type="checkbox" id="jsl_is_preview" name="jsl_is_preview" value="1" <?php checked( $preview ); ?>>
					<?php esc_html_e( 'Readable without access — the sample chapter', 'guide-lms' ); ?>
				</label>
			</div>

			<hr class="guide-mb__hr">

			<div class="guide-mb__full">
				<label for="jsl_video_url"><?php esc_html_e( 'Video URL', 'guide-lms' ); ?></label>
				<input type="url" id="jsl_video_url" name="jsl_video_url" value="<?php echo esc_attr( $video ); ?>" placeholder="https://www.youtube.com/watch?v=…">
				<p class="guide-mb__note">
					<?php esc_html_e( 'YouTube, Vimeo or an mp4. Nothing loads from the video host until the learner presses play.', 'guide-lms' ); ?>
				</p>
			</div>

			<div>
				<label for="jsl_video_start"><?php esc_html_e( 'Start at (seconds)', 'guide-lms' ); ?></label>
				<input type="number" min="0" id="jsl_video_start" name="jsl_video_start" value="<?php echo esc_attr( (string) $start ); ?>">
			</div>

			<div>
				<label for="jsl_video_end"><?php esc_html_e( 'End at (seconds)', 'guide-lms' ); ?></label>
				<input type="number" min="0" id="jsl_video_end" name="jsl_video_end" value="<?php echo esc_attr( (string) $end ); ?>">
			</div>

			<?php if ( 'quiz' === $type ) : ?>
				<p class="guide-mb__note guide-mb__full">
					<?php
					printf(
						/* translators: %s: link to the builder. */
						esc_html__( 'Quiz questions are edited in the builder, where answers stay server-side: %s', 'guide-lms' ),
						'<a href="' . esc_url( admin_url( 'admin.php?page=guide-lms' ) ) . '">' . esc_html__( 'open the builder', 'guide-lms' ) . '</a>'
					);
					?>
				</p>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * @param int      $post_id
	 * @param \WP_Post $post
	 */
	public static function save_lesson( $post_id, $post ) {
		if ( ! self::may_save( $post_id ) ) {
			return;
		}

		// phpcs:disable WordPress.Security.NonceVerification.Missing
		update_post_meta( $post_id, 'jsl_course_id', absint( $_POST['jsl_course_id'] ?? 0 ) );

		$type = sanitize_key( wp_unslash( $_POST['jsl_lesson_type'] ?? 'article' ) );
		update_post_meta( $post_id, 'jsl_lesson_type', in_array( $type, array( 'article', 'video', 'quiz' ), true ) ? $type : 'article' );

		update_post_meta( $post_id, 'jsl_duration_minutes', min( 600, absint( $_POST['jsl_duration_minutes'] ?? 0 ) ) );
		update_post_meta( $post_id, 'jsl_is_preview', empty( $_POST['jsl_is_preview'] ) ? 0 : 1 );

		update_post_meta( $post_id, 'jsl_video_url', esc_url_raw( wp_unslash( $_POST['jsl_video_url'] ?? '' ) ) );
		update_post_meta( $post_id, 'jsl_video_start', absint( $_POST['jsl_video_start'] ?? 0 ) );
		update_post_meta( $post_id, 'jsl_video_end', absint( $_POST['jsl_video_end'] ?? 0 ) );
		// phpcs:enable WordPress.Security.NonceVerification.Missing
	}

	// -------------------------------------------------------------------------

	/**
	 * The usual three guards, in one place.
	 *
	 * Without the autosave check, WordPress's periodic autosave — which posts
	 * none of these fields — would blank every setting on the screen while the
	 * author was still typing the title.
	 */
	private static function may_save( int $post_id ): bool {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return false;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		$nonce = isset( $_POST['guide_meta_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['guide_meta_nonce'] ) ) : '';

		if ( ! wp_verify_nonce( $nonce, self::NONCE ) ) {
			return false;
		}

		return current_user_can( 'edit_post', $post_id );
	}

	/**
	 * A textarea of one-per-line values.
	 *
	 * @param mixed $raw
	 * @return string[]
	 */
	private static function lines( $raw ): array {
		$lines = preg_split( '/\r\n|\r|\n/', (string) wp_unslash( $raw ) );
		$out   = array();

		foreach ( (array) $lines as $line ) {
			$line = sanitize_text_field( trim( $line ) );

			if ( '' !== $line ) {
				$out[] = $line;
			}
		}

		return array_slice( $out, 0, 20 );
	}
}
