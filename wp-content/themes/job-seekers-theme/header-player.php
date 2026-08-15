<?php
/**
 * Course-player app bar.
 *
 * The marketing header sells the site; this one gets out of the way and
 * keeps the learner oriented inside a course: where they are, how far
 * through they are, and how to get back out. It derives its context from
 * the queried lesson rather than taking arguments, because it is only ever
 * used on a lesson.
 *
 * On compact windows the curriculum is a modal navigation drawer, opened
 * from the leading icon button here.
 */

defined( 'ABSPATH' ) || exit;

$jsl_p_lesson  = (int) get_queried_object_id();
$jsl_p_course  = (int) get_post_meta( $jsl_p_lesson, 'jsl_course_id', true );
$jsl_p_has_api = class_exists( 'JSL\\Course_Api' );
$jsl_p_user    = get_current_user_id();

$jsl_p_flat  = $jsl_p_course && $jsl_p_has_api ? \JSL\Course_Api::get_lessons_flat( $jsl_p_course ) : array();
$jsl_p_total = count( $jsl_p_flat );

$jsl_p_position = 0;
foreach ( $jsl_p_flat as $jsl_p_i => $jsl_p_row ) {
	if ( (int) $jsl_p_row->ID === $jsl_p_lesson ) {
		$jsl_p_position = $jsl_p_i + 1;
		break;
	}
}

$jsl_p_done = ( $jsl_p_user && $jsl_p_course && class_exists( 'JSL\\Progress\\Progress' ) )
	? count( \JSL\Progress\Progress::completed_lesson_ids( $jsl_p_user, $jsl_p_course ) )
	: 0;
$jsl_p_percent = $jsl_p_total > 0 ? (int) round( $jsl_p_done / $jsl_p_total * 100 ) : 0;
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
	<?php wp_head(); ?>
</head>
<body <?php body_class( 'font-sans bg-surface text-on-surface antialiased' ); ?>>
<?php wp_body_open(); ?>

<a class="skip-link" href="#jsl-main"><?php esc_html_e( 'Skip to lesson', 'job-seekers-theme' ); ?></a>

<header class="md-top-app-bar !min-h-[64px]">
	<div class="flex w-full items-center gap-1 px-1 md:px-3">

		<!-- Opens the curriculum drawer (compact windows only). -->
		<button type="button" class="md-icon-btn lg:hidden" data-drawer-open="curriculum"
			aria-expanded="false" aria-controls="jsl-curriculum-drawer"
			aria-label="<?php esc_attr_e( 'Course content', 'job-seekers-theme' ); ?>">
			<?php echo jsl_icon( 'list', 'w-6 h-6' ); ?>
		</button>

		<a class="md-icon-btn hidden lg:inline-grid" href="<?php echo esc_url( $jsl_p_course ? get_permalink( $jsl_p_course ) : home_url( '/' ) ); ?>"
			aria-label="<?php esc_attr_e( 'Back to course overview', 'job-seekers-theme' ); ?>">
			<?php echo jsl_icon( 'arrow-left', 'w-6 h-6' ); ?>
		</a>

		<a class="ml-1 grid h-9 w-9 shrink-0 place-items-center rounded-lg bg-primary text-on-primary no-underline" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>">
			<?php echo jsl_logo_mark( 'w-5 h-5' ); ?>
		</a>

		<!-- Course context -->
		<div class="ml-2 min-w-0 flex-1">
			<?php if ( $jsl_p_course ) : ?>
				<a class="block truncate text-sm font-semibold text-on-surface no-underline hover:text-primary" href="<?php echo esc_url( get_permalink( $jsl_p_course ) ); ?>">
					<?php echo esc_html( get_the_title( $jsl_p_course ) ); ?>
				</a>
			<?php endif; ?>
			<?php if ( $jsl_p_position && $jsl_p_total ) : ?>
				<p class="m-0 truncate text-xs text-on-surface-variant">
					<?php printf( esc_html__( 'Lesson %1$d of %2$d', 'job-seekers-theme' ), (int) $jsl_p_position, (int) $jsl_p_total ); ?>
				</p>
			<?php endif; ?>
		</div>

		<?php if ( $jsl_p_user && $jsl_p_total ) : ?>
			<div class="mr-1 hidden items-center gap-2.5 sm:flex">
				<div class="md-linear-progress w-24 lg:w-36">
					<div class="md-linear-progress__bar" data-progress-bar style="width:<?php echo esc_attr( $jsl_p_percent ); ?>%"></div>
				</div>
				<span class="font-mono text-xs font-semibold tabular-nums text-on-surface-variant" data-progress-percent><?php echo esc_html( $jsl_p_percent ); ?>%</span>
			</div>
		<?php endif; ?>

		<button type="button" class="md-icon-btn" data-theme-toggle
				data-label-auto="<?php esc_attr_e( 'Theme: follow system', 'job-seekers-theme' ); ?>"
				data-label-light="<?php esc_attr_e( 'Theme: light', 'job-seekers-theme' ); ?>"
				data-label-dark="<?php esc_attr_e( 'Theme: dark', 'job-seekers-theme' ); ?>"
				aria-label="<?php esc_attr_e( 'Change theme', 'job-seekers-theme' ); ?>">
				<span data-mode-icon="auto"><?php echo jsl_icon( 'circle-half', 'w-5 h-5' ); ?></span>
				<span data-mode-icon="light" hidden><?php echo jsl_icon( 'sun-fill', 'w-5 h-5' ); ?></span>
				<span data-mode-icon="dark" hidden><?php echo jsl_icon( 'moon-fill', 'w-5 h-5' ); ?></span>
			</button>

		<a class="md-icon-btn" href="<?php echo esc_url( is_user_logged_in() ? home_url( '/my-learning/' ) : wp_login_url( get_permalink() ) ); ?>"
			aria-label="<?php echo is_user_logged_in() ? esc_attr__( 'My Learning', 'job-seekers-theme' ) : esc_attr__( 'Sign in', 'job-seekers-theme' ); ?>">
			<?php echo jsl_icon( is_user_logged_in() ? 'user-circle' : 'sign-in', 'w-5 h-5' ); ?>
		</a>
	</div>
</header>

<main id="jsl-main">
