<?php
/**
 * Changelog — grouped by version, newest first.
 *
 * Entries are written in the admin (LMS → Changelog), each tagged with a
 * version and a kind, and grouped here. Publishing what changed is part of
 * the honesty the rest of the site claims.
 */

defined( 'ABSPATH' ) || exit;

get_header();

$guide_entries = get_posts(
	array(
		'post_type'      => \Guide\Community\Community_Types::CHANGELOG,
		'posts_per_page' => 200,
		'orderby'        => 'date',
		'order'          => 'DESC',
	)
);

// Group by version, preserving the order versions were first seen (which,
// because entries come back newest-first, is newest version first).
$guide_versions = array();

foreach ( $guide_entries as $guide_entry ) {
	$guide_version = (string) get_post_meta( $guide_entry->ID, 'jsl_changelog_version', true );
	$guide_version = $guide_version ? $guide_version : __( 'Unversioned', 'guide-wp-theme' );

	if ( ! isset( $guide_versions[ $guide_version ] ) ) {
		$guide_versions[ $guide_version ] = array(
			'date'    => $guide_entry->post_date,
			'entries' => array(),
		);
	}

	$guide_versions[ $guide_version ]['entries'][] = $guide_entry;
}
?>

<div class="guide-shell">
	<header class="guide-page-head">
		<span class="guide-eyebrow"><?php esc_html_e( 'Changelog', 'guide-wp-theme' ); ?></span>
		<h1 class="guide-display mt-1"><?php esc_html_e( 'What we have shipped', 'guide-wp-theme' ); ?></h1>
		<p class="guide-page-head__lede">
			<?php esc_html_e( 'Everything that changed, in the order it changed. If something you asked for is on this list, thank you — it got here because somebody said so.', 'guide-wp-theme' ); ?>
		</p>
		<div class="guide-hero__actions">
			<a class="button" href="<?php echo esc_url( get_post_type_archive_link( \Guide\Community\Community_Types::ROADMAP ) ); ?>">
				<?php esc_html_e( 'See what is planned', 'guide-wp-theme' ); ?>
			</a>
		</div>
	</header>

	<div class="guide-section guide-section--tight">
		<?php if ( empty( $guide_versions ) ) : ?>
			<div class="guide-empty">
				<p class="guide-empty__title"><?php esc_html_e( 'Nothing published yet', 'guide-wp-theme' ); ?></p>
				<p class="guide-empty__text"><?php esc_html_e( 'Entries are written under LMS → Changelog.', 'guide-wp-theme' ); ?></p>
			</div>
		<?php else : ?>
			<div class="guide-changelog">
				<?php foreach ( $guide_versions as $guide_version => $guide_group ) : ?>
					<section class="guide-release">
						<header class="guide-release__head">
							<h2 class="guide-release__version"><?php echo esc_html( $guide_version ); ?></h2>
							<time class="guide-release__date" datetime="<?php echo esc_attr( mysql2date( 'c', $guide_group['date'] ) ); ?>">
								<?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $guide_group['date'] ) ) ); ?>
							</time>
						</header>

						<div class="guide-release__entries">
							<?php foreach ( $guide_group['entries'] as $guide_entry ) : ?>
								<?php $guide_kind = (string) get_post_meta( $guide_entry->ID, 'jsl_changelog_kind', true ); ?>
								<article class="guide-release__entry">
									<span class="guide-kind guide-kind--<?php echo esc_attr( $guide_kind ); ?>">
										<?php echo esc_html( \Guide\Community\Community_Types::kind_label( $guide_kind ) ); ?>
									</span>
									<div>
										<h3 class="guide-release__title"><?php echo esc_html( get_the_title( $guide_entry ) ); ?></h3>
										<?php if ( trim( $guide_entry->post_content ) ) : ?>
											<div class="guide-release__body guide-prose">
												<?php echo apply_filters( 'the_content', $guide_entry->post_content ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
											</div>
										<?php endif; ?>
									</div>
								</article>
							<?php endforeach; ?>
						</div>
					</section>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>
</div>

<?php get_footer(); ?>
