<?php
/**
 * LMS → Help.
 *
 * A short operator's guide inside wp-admin. Deliberately self-contained —
 * no network calls, nothing to load — because the moment you need help is
 * often the moment something is broken.
 *
 * The long version lives in docs/help-centre.md in the repository; this is the
 * part you actually need at 11pm.
 */

namespace Guide\Admin;

use Guide\Ads\Ads;
use Guide\Payments\Subscription;

defined( 'ABSPATH' ) || exit;

class Help_Page {

	const SLUG = 'guide-help';

	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'register_menu' ), 40 );
	}

	public static function register_menu() {
		add_submenu_page(
			Console::SLUG,
			__( 'Help', 'guide-lms' ),
			__( 'Help', 'guide-lms' ),
			'edit_posts',
			self::SLUG,
			array( __CLASS__, 'render' )
		);
	}

	public static function url(): string {
		return admin_url( 'admin.php?page=' . self::SLUG );
	}

	/**
	 * Things that are commonly half-configured. Showing the state beats
	 * describing the setting.
	 */
	private static function checks(): array {
		$subscription_on = class_exists( 'Guide\\Payments\\Subscription' ) && Subscription::is_enabled();
		$ads_on          = class_exists( 'Guide\\Ads\\Ads' ) && Ads::is_enabled();

		return array(
			array(
				'label' => __( 'Subscription', 'guide-lms' ),
				'ok'    => $subscription_on,
				'good'  => __( 'Enabled', 'guide-lms' ),
				'bad'   => __( 'Not set up — no one can subscribe', 'guide-lms' ),
				'url'   => Settings_Page::url( 'subscription' ),
			),
			array(
				'label' => __( 'Ads', 'guide-lms' ),
				'ok'    => $ads_on,
				'good'  => __( 'Running for non-subscribers', 'guide-lms' ),
				'bad'   => __( 'Off', 'guide-lms' ),
				'url'   => Settings_Page::url( 'ads' ),
			),
			array(
				'label' => __( 'Permalinks', 'guide-lms' ),
				'ok'    => '' !== get_option( 'permalink_structure' ),
				'good'  => __( 'Pretty permalinks on', 'guide-lms' ),
				'bad'   => __( 'Plain permalinks — course URLs will not work', 'guide-lms' ),
				'url'   => admin_url( 'options-permalink.php' ),
			),
			array(
				'label' => __( 'Site visibility', 'guide-lms' ),
				'ok'    => ! get_option( 'blog_public' ) ? false : true,
				'good'  => __( 'Visible to search engines', 'guide-lms' ),
				'bad'   => __( 'Discouraging search engines — nobody will find the site', 'guide-lms' ),
				'url'   => admin_url( 'options-reading.php' ),
			),
		);
	}

	public static function render() {
		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_die( esc_html__( 'You do not have permission to view this page.', 'guide-lms' ) );
		}
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Help', 'guide-lms' ); ?></h1>

			<h2><?php esc_html_e( 'Setup check', 'guide-lms' ); ?></h2>
			<table class="widefat striped" style="max-width:52rem">
				<tbody>
					<?php foreach ( self::checks() as $check ) : ?>
						<tr>
							<td style="width:12rem"><strong><?php echo esc_html( $check['label'] ); ?></strong></td>
							<td>
								<span style="color:<?php echo $check['ok'] ? '#2f9e64' : '#b47b11'; ?>">
									<?php echo esc_html( $check['ok'] ? $check['good'] : $check['bad'] ); ?>
								</span>
							</td>
							<td style="width:8rem;text-align:right">
								<a href="<?php echo esc_url( $check['url'] ); ?>"><?php esc_html_e( 'Open', 'guide-lms' ); ?></a>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>

			<h2><?php esc_html_e( 'How this works', 'guide-lms' ); ?></h2>

			<h3><?php esc_html_e( 'Courses', 'guide-lms' ); ?></h3>
			<p>
				<?php esc_html_e( 'Everything is authored in LMS → Courses. A course holds modules, a module holds lessons. Drag to reorder; lessons can move between modules. A course starts as a draft and is invisible until you publish it.', 'guide-lms' ); ?>
			</p>

			<h3><?php esc_html_e( 'Free or Members', 'guide-lms' ); ?></h3>
			<p>
				<?php esc_html_e( 'Each course is either Free (open to everyone) or Members (part of the subscription). There is no per-course price, on purpose — asking a learner to make a purchase decision at every step of a path is the confusion this platform exists to remove.', 'guide-lms' ); ?>
			</p>
			<p>
				<strong><?php esc_html_e( 'Keep the core path free.', 'guide-lms' ); ?></strong>
				<?php esc_html_e( 'Foundations, one language, projects and the job-search module are the promise the site makes.', 'guide-lms' ); ?>
			</p>

			<h3><?php esc_html_e( 'Lessons', 'guide-lms' ); ?></h3>
			<p>
				<?php esc_html_e( 'Article, video, or quiz. Videos can be clipped to a start and end time, and nothing loads from the video host until a learner clicks play. Quiz answers are stored server-side and never sent to the browser. Mark one lesson as a free preview to use it as the sample chapter.', 'guide-lms' ); ?>
			</p>

			<h3><?php esc_html_e( 'Ads', 'guide-lms' ); ?></h3>
			<p>
				<?php esc_html_e( 'Subscribers and staff never see an ad, and the AdSense script is not loaded for them at all. Ads never appear inside lesson content, or on the account and sign-in pages. Use test mode while checking placement — clicking your own live ads will get the account banned.', 'guide-lms' ); ?>
			</p>

			<h3><?php esc_html_e( 'Stories', 'guide-lms' ); ?></h3>
			<p>
				<?php esc_html_e( 'Learner stories arrive as pending and appear publicly only after you approve them in LMS → Stories. A story that includes the rejections is worth more than one that skips them.', 'guide-lms' ); ?>
			</p>

			<h2><?php esc_html_e( 'When something breaks', 'guide-lms' ); ?></h2>
			<table class="widefat striped" style="max-width:52rem">
				<tbody>
					<tr>
						<td style="width:18rem"><strong><?php esc_html_e( 'A URL 404s that should not', 'guide-lms' ); ?></strong></td>
						<td>
							<?php
							printf(
								/* translators: %s: link to the permalinks screen. */
								esc_html__( 'Open %s and click Save. That flushes the rewrite rules.', 'guide-lms' ),
								'<a href="' . esc_url( admin_url( 'options-permalink.php' ) ) . '">' . esc_html__( 'Settings → Permalinks', 'guide-lms' ) . '</a>'
							);
							?>
						</td>
					</tr>
					<tr>
						<td><strong><?php esc_html_e( 'A learner paid but has no access', 'guide-lms' ); ?></strong></td>
						<td><?php esc_html_e( 'Check their billing history at /account/. If the payment exists in Dodo but not there, the webhook did not arrive — check the secret and the registered URL on the Payments tab.', 'guide-lms' ); ?></td>
					</tr>
					<tr>
						<td><strong><?php esc_html_e( 'The site looks unstyled after a deploy', 'guide-lms' ); ?></strong></td>
						<td><?php esc_html_e( 'The compiled CSS is committed to the repository. If SCSS changed without running "npm run build", the change never reached the file the site serves.', 'guide-lms' ); ?></td>
					</tr>
					<tr>
						<td><strong><?php esc_html_e( 'Routine upkeep', 'guide-lms' ); ?></strong></td>
						<td><?php esc_html_e( 'Weekly: clear the story queue, install updates. Monthly: check Site Health, and look for courses nobody finishes. Back up the database before any big change.', 'guide-lms' ); ?></td>
					</tr>
				</tbody>
			</table>

			<h2><?php esc_html_e( 'More', 'guide-lms' ); ?></h2>
			<p>
				<?php esc_html_e( 'The full operator manual, the architecture notes, the changelog and the roadmap live in the docs/ folder of the repository.', 'guide-lms' ); ?>
				<a href="https://github.com/imswarnil/job-seekers-guide/tree/main/docs" target="_blank" rel="noopener">
					<?php esc_html_e( 'Open the docs', 'guide-lms' ); ?>
				</a>
			</p>
		</div>
		<?php
	}
}
