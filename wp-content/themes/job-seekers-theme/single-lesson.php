<?php
/**
 * Lesson page: content + a sidebar listing the parent course's modules
 * so learners can navigate without going back to the course page.
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	$lesson_id = get_the_ID();
	$course_id = (int) get_post_meta( $lesson_id, 'jsl_course_id', true );
	$modules   = $course_id && class_exists( 'JSL\\Course_Api' ) ? \JSL\Course_Api::get_modules( $course_id ) : array();
	?>

	<div class="jsl-container" style="display:grid;grid-template-columns:1fr 20rem;gap:var(--jsl-space-6);align-items:start">
		<article>
			<?php if ( $course_id ) : ?>
				<p class="jsl-eyebrow">
					<a href="<?php echo esc_url( get_permalink( $course_id ) ); ?>">&larr; <?php echo get_the_title( $course_id ); ?></a>
				</p>
			<?php endif; ?>
			<h1><?php the_title(); ?></h1>
			<div><?php the_content(); ?></div>
		</article>

		<aside>
			<?php foreach ( $modules as $module ) : ?>
				<div class="jsl-module">
					<h3 class="jsl-module__title"><?php echo esc_html( $module['title'] ); ?></h3>
					<ol class="jsl-lesson-list">
						<?php foreach ( $module['lessons'] as $index => $mod_lesson ) : ?>
							<li class="jsl-lesson-list__item" <?php echo ( (int) $mod_lesson->ID === $lesson_id ) ? 'style="font-weight:700"' : ''; ?>>
								<span class="jsl-lesson-list__index"><?php echo esc_html( $index + 1 ); ?></span>
								<a href="<?php echo esc_url( get_permalink( $mod_lesson ) ); ?>"><?php echo get_the_title( $mod_lesson ); ?></a>
							</li>
						<?php endforeach; ?>
					</ol>
				</div>
			<?php endforeach; ?>
		</aside>
	</div>

	<?php
endwhile;

get_footer();
