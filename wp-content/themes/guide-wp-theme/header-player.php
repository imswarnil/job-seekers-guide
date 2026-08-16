<?php
/**
 * Course-player app bar.
 *
 * The marketing header sells the site; this one gets out of the way and keeps
 * the learner oriented inside a course: where they are, how far through they
 * are, and how to get back out. It derives its context from the queried lesson
 * rather than taking arguments, because it is only ever used on a lesson.
 *
 * On narrow windows the curriculum is a slide-over drawer, opened from the
 * leading icon button here.
 */

defined( 'ABSPATH' ) || exit;

$guide_p_lesson  = (int) get_queried_object_id();
$guide_p_course  = (int) get_post_meta( $guide_p_lesson, 'jsl_course_id', true );
$guide_p_has_api = class_exists( 'Guide\\Course_Api' );
$guide_p_user    = get_current_user_id();

$guide_p_flat  = $guide_p_course && $guide_p_has_api
	? \Guide\Course_Api::get_lessons_flat( $guide_p_course )
	: array();
$guide_p_total = count( $guide_p_flat );

$guide_p_position = 0;
foreach ( $guide_p_flat as $guide_p_i => $guide_p_row ) {
	if ( (int) $guide_p_row->ID === $guide_p_lesson ) {
		$guide_p_position = $guide_p_i + 1;
		break;
	}
}

$guide_p_done = ( $guide_p_user && $guide_p_course && class_exists( 'Guide\\Progress\\Progress' ) )
	? count( \Guide\Progress\Progress::completed_lesson_ids( $guide_p_user, $guide_p_course ) )
	: 0;
$guide_p_percent = $guide_p_total > 0 ? (int) round( $guide_p_done / $guide_p_total * 100 ) : 0;
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
	<?php wp_head(); ?>
</head>
<body <?php body_class( 'guide-player-body' ); ?>>
<?php wp_body_open(); ?>

<a class="skip-link" href="#guide-main"><?php esc_html_e( 'Skip to lesson', 'guide-wp-theme' ); ?></a>

<header class="guide-player__toolbar">

	<button type="button" class="guide-icon-button guide-player__drawer-toggle"
		data-drawer-toggle="guide-course-drawer"
		aria-expanded="false" aria-controls="guide-course-drawer"
		aria-label="<?php esc_attr_e( 'Course content', 'guide-wp-theme' ); ?>">
		<?php echo guide_icon( 'list' ); ?>
	</button>

	<a class="guide-icon-button" href="<?php echo esc_url( $guide_p_course ? get_permalink( $guide_p_course ) : home_url( '/' ) ); ?>"
		aria-label="<?php esc_attr_e( 'Back to course overview', 'guide-wp-theme' ); ?>">
		<?php echo guide_icon( 'arrow-left' ); ?>
	</a>

	<div style="min-width:0;flex:1">
		<?php if ( $guide_p_course ) : ?>
			<a class="guide-player__course-title" href="<?php echo esc_url( get_permalink( $guide_p_course ) ); ?>">
				<?php echo esc_html( get_the_title( $guide_p_course ) ); ?>
			</a>
		<?php endif; ?>
		<?php if ( $guide_p_position && $guide_p_total ) : ?>
			<p class="guide-player__toolbar-title">
				<?php
				printf(
					/* translators: 1: current lesson number, 2: total lessons. */
					esc_html__( 'Lesson %1$d of %2$d', 'guide-wp-theme' ),
					(int) $guide_p_position,
					(int) $guide_p_total
				);
				?>
			</p>
		<?php endif; ?>
	</div>

	<?php if ( $guide_p_user && $guide_p_total ) : ?>
		<div class="guide-player__progress">
			<span class="guide-progress" style="width:7rem">
				<span class="guide-progress__bar" data-progress-bar style="width:<?php echo esc_attr( (string) $guide_p_percent ); ?>%"></span>
			</span>
			<span class="guide-player__percent" data-progress-percent><?php echo esc_html( (string) $guide_p_percent ); ?>%</span>
		</div>
	<?php endif; ?>

	<button type="button" class="guide-icon-button" data-theme-toggle
		data-label-auto="<?php esc_attr_e( 'Theme: follow system', 'guide-wp-theme' ); ?>"
		data-label-light="<?php esc_attr_e( 'Theme: light', 'guide-wp-theme' ); ?>"
		data-label-dark="<?php esc_attr_e( 'Theme: dark', 'guide-wp-theme' ); ?>"
		aria-label="<?php esc_attr_e( 'Change theme', 'guide-wp-theme' ); ?>">
		<span data-mode-icon="auto"><?php echo guide_icon( 'circle-half' ); ?></span>
		<span data-mode-icon="light" hidden><?php echo guide_icon( 'sun-fill' ); ?></span>
		<span data-mode-icon="dark" hidden><?php echo guide_icon( 'moon-fill' ); ?></span>
	</button>

	<a class="guide-icon-button" href="<?php echo esc_url( is_user_logged_in() ? home_url( '/my-learning/' ) : wp_login_url( get_permalink() ) ); ?>"
		aria-label="<?php echo is_user_logged_in() ? esc_attr__( 'My Learning', 'guide-wp-theme' ) : esc_attr__( 'Sign in', 'guide-wp-theme' ); ?>">
		<?php echo guide_icon( is_user_logged_in() ? 'user-circle' : 'sign-in' ); ?>
	</a>
</header>

<main id="guide-main" class="guide-main">
