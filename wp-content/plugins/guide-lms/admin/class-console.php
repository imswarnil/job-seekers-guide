<?php
/**
 * The LMS Console: a single-page admin app (Dashboard / Courses / Course
 * editor with drag-drop builder + inline lesson writing / Learners).
 * Rendered into one full-bleed admin screen; all data over REST
 * (guide/v1 for LMS data + wp/v2 for post content).
 */

namespace Guide\Admin;

defined( 'ABSPATH' ) || exit;

class Console {

	const SLUG = 'guide-lms';

	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'register_menu' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue' ) );
	}

	public static function register_menu() {
		add_menu_page(
			__( 'LMS', 'guide-lms' ),
			__( 'LMS', 'guide-lms' ),
			'edit_posts',
			self::SLUG,
			array( __CLASS__, 'render' ),
			'data:image/svg+xml;base64,' . base64_encode( '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#a7aaad" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="m15.5 8.5-2 5-5 2 2-5 5-2Z"/></svg>' ),
			3
		);

	}

	public static function enqueue( $hook ) {
		if ( 'toplevel_page_' . self::SLUG !== $hook ) {
			return;
		}

		Admin_Theme::enqueue_console();
		wp_enqueue_media();
		wp_enqueue_script( 'guide-console', GUIDE_PLUGIN_URL . 'admin/assets/js/console.js', array( 'media-editor' ), guide_plugin_asset_version( 'admin/assets/js/console.js' ), true );

		wp_localize_script(
			'guide-console',
			'guideConsole',
			array(
				'root'     => esc_url_raw( rest_url() ),
				'nonce'    => wp_create_nonce( 'wp_rest' ),
				'adminUrl' => esc_url_raw( admin_url() ),
				'siteUrl'  => esc_url_raw( home_url( '/' ) ),
				'userName' => wp_get_current_user()->display_name,
			)
		);
	}

	public static function render() {
		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_die( esc_html__( 'You do not have permission to access the LMS console.', 'guide-lms' ) );
		}
		?>
		<?php // `guide-admin` is the scope the console stylesheet is compiled under; without it nothing in console.min.css applies. ?>
		<div class="guide-admin">
		<div class="guide-console" id="guide-console">
			<aside class="guide-console__nav">
				<div class="guide-console__brand">
					<span class="guide-console__brand-mark">
						<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="m15.5 8.5-2 5-5 2 2-5 5-2Z"/></svg>
					</span>
					<span>
						<strong><?php esc_html_e( 'LMS Console', 'guide-lms' ); ?></strong>
						<small><?php echo esc_html( get_bloginfo( 'name' ) ); ?></small>
					</span>
				</div>

				<nav class="guide-console__links" aria-label="<?php esc_attr_e( 'LMS sections', 'guide-lms' ); ?>">
					<a href="#/" data-nav="dashboard">
						<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="3" width="8" height="8" rx="2"/><rect x="13" y="3" width="8" height="5" rx="2"/><rect x="13" y="10" width="8" height="11" rx="2"/><rect x="3" y="13" width="8" height="8" rx="2"/></svg>
						<?php esc_html_e( 'Dashboard', 'guide-lms' ); ?>
					</a>
					<a href="#/courses" data-nav="courses">
						<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m12 3 9 5-9 5-9-5 9-5Z"/><path d="m3 13 9 5 9-5"/></svg>
						<?php esc_html_e( 'Courses', 'guide-lms' ); ?>
					</a>
					<a href="#/paths" data-nav="paths">
						<svg viewBox="0 0 256 256" fill="currentColor" aria-hidden="true"><path d="M216,168a32,32,0,1,0,32,32A32,32,0,0,0,216,168Zm0,48a16,16,0,1,1,16-16A16,16,0,0,1,216,216ZM40,88A32,32,0,1,0,8,56,32,32,0,0,0,40,88ZM40,40A16,16,0,1,1,24,56,16,16,0,0,1,40,40Zm128,72a40,40,0,0,1-40,40H88a24,24,0,0,0,0,48h48v16H88a40,40,0,0,1,0-80h40a24,24,0,0,0,0-48H80V72h48A40,40,0,0,1,168,112Z"/></svg>
						<?php esc_html_e( 'Learning Paths', 'guide-lms' ); ?>
					</a>
					<a href="#/stories" data-nav="stories">
						<svg viewBox="0 0 256 256" fill="currentColor" aria-hidden="true"><path d="M232,64H208V56a16,16,0,0,0-16-16H64A16,16,0,0,0,48,56v8H24A16,16,0,0,0,8,80V96a40,40,0,0,0,40,40h1.83A80.16,80.16,0,0,0,120,191.61V216H96a8,8,0,0,0,0,16h64a8,8,0,0,0,0-16H136V191.58A80.07,80.07,0,0,0,206.17,136H208a40,40,0,0,0,40-40V80A16,16,0,0,0,232,64ZM24,96V80H48v40A24,24,0,0,1,24,96ZM232,96a24,24,0,0,1-24,24V80h24Z"/></svg>
						<?php esc_html_e( 'Stories', 'guide-lms' ); ?>
					</a>
					<a href="#/learners" data-nav="learners">
						<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="9" cy="8" r="3.5"/><path d="M3 20c0-3.3 2.7-6 6-6s6 2.7 6 6"/><circle cx="17" cy="9" r="2.5"/><path d="M16.5 14.5c2.8.3 4.5 2.3 4.5 5.5"/></svg>
						<?php esc_html_e( 'Learners', 'guide-lms' ); ?>
					</a>
				</nav>

				<div class="guide-console__nav-foot">
					<a href="<?php echo esc_url( Settings_Page::url( 'payments' ) ); ?>">
						<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="3"/><path d="M19 12a7 7 0 0 0-.14-1.4l2-1.55-2-3.46-2.37.95A7 7 0 0 0 14 5.1L13.6 2.6h-4L9.2 5.1a7 7 0 0 0-2.49 1.44l-2.37-.95-2 3.46 2 1.55A7 7 0 0 0 4.2 12c0 .48.05.94.14 1.4l-2 1.55 2 3.46 2.37-.95c.73.62 1.57 1.11 2.49 1.44l.4 2.5h4l.4-2.5a7 7 0 0 0 2.49-1.44l2.37.95 2-3.46-2-1.55c.09-.46.14-.92.14-1.4Z"/></svg>
						<?php esc_html_e( 'Settings', 'guide-lms' ); ?>
					</a>
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>">
						<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 11.5 12 4l9 7.5"/><path d="M5.5 10v10h13V10"/></svg>
						<?php esc_html_e( 'View site', 'guide-lms' ); ?>
					</a>
				</div>
			</aside>

			<main class="guide-console__main" id="guide-view" tabindex="-1">
				<div class="guide-skeleton-page"><span class="guide-spinner" aria-hidden="true"></span><?php esc_html_e( 'Loading console…', 'guide-lms' ); ?></div>
			</main>
		</div>
		</div><?php // .guide-admin ?>
		<?php
	}
}
