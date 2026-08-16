<?php
/**
 * A single success story — a long-form reading layout.
 *
 * Quiet and typographically generous: this is the one place on the site
 * someone reads several hundred words end to end, and the reason they are
 * reading it is to believe it could be them.
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	$guide_story_id = get_the_ID();
	$guide_d        = class_exists( 'Guide\\Success\\Success_Stories' )
		? \Guide\Success\Success_Stories::details( $guide_story_id )
		: array(
			'company'  => '',
			'role'     => '',
			'previous' => '',
			'weeks'    => 0,
			'linkedin' => '',
		);
	$guide_author_id = (int) get_the_author_meta( 'ID' );
	$guide_can_edit  = current_user_can( 'edit_post', $guide_story_id );
	?>

	<article>
		<header class="guide-story-hero">
			<div class="guide-shell guide-story-hero__inner">
				<nav class="guide-breadcrumb" aria-label="<?php esc_attr_e( 'Breadcrumb', 'guide-wp-theme' ); ?>">
					<a href="<?php echo esc_url( get_post_type_archive_link( 'success_story' ) ); ?>">
						<?php esc_html_e( 'Wall of Success', 'guide-wp-theme' ); ?>
					</a>
				</nav>

				<h1 class="guide-display mt-4"><?php the_title(); ?></h1>

				<div class="is-flex is-align-items-center mt-5" style="gap:1rem;flex-wrap:wrap">
					<?php echo guide_avatar( $guide_author_id, 48 ); ?>
					<div>
						<p class="has-text-weight-bold"><?php the_author(); ?></p>
						<p class="is-size-7" style="opacity:.75"><?php echo esc_html( get_the_date() ); ?></p>
					</div>

					<?php if ( $guide_d['linkedin'] ) : ?>
						<a class="button is-small" href="<?php echo esc_url( $guide_d['linkedin'] ); ?>" target="_blank" rel="noopener nofollow">
							<?php esc_html_e( 'LinkedIn', 'guide-wp-theme' ); ?>
						</a>
					<?php endif; ?>

					<?php if ( $guide_can_edit ) : ?>
						<?php // Storytellers can revise their own story — a first draft written the week you got hired is rarely the one you want up forever. ?>
						<a class="button is-small" href="<?php echo esc_url( home_url( '/share-your-story/?edit=' . $guide_story_id ) ); ?>">
							<?php esc_html_e( 'Edit your story', 'guide-wp-theme' ); ?>
						</a>
					<?php endif; ?>
				</div>
			</div>
		</header>

		<div class="guide-shell guide-section">
			<div class="guide-story-layout">
				<div class="guide-prose"><?php the_content(); ?></div>

				<aside class="guide-facts">
					<h2 class="guide-filter-group__label"><?php esc_html_e( 'The move', 'guide-wp-theme' ); ?></h2>

					<dl class="mt-3">
						<?php if ( $guide_d['previous'] ) : ?>
							<dt><?php esc_html_e( 'From', 'guide-wp-theme' ); ?></dt>
							<dd><?php echo esc_html( $guide_d['previous'] ); ?></dd>
						<?php endif; ?>

						<?php if ( $guide_d['role'] ) : ?>
							<dt><?php esc_html_e( 'To', 'guide-wp-theme' ); ?></dt>
							<dd><?php echo esc_html( $guide_d['role'] ); ?></dd>
						<?php endif; ?>

						<?php if ( $guide_d['company'] ) : ?>
							<dt><?php esc_html_e( 'Company', 'guide-wp-theme' ); ?></dt>
							<dd><?php echo esc_html( $guide_d['company'] ); ?></dd>
						<?php endif; ?>

						<?php if ( $guide_d['weeks'] ) : ?>
							<dt><?php esc_html_e( 'Time searching', 'guide-wp-theme' ); ?></dt>
							<dd>
								<?php
								printf(
									/* translators: %d: number of weeks spent job searching. */
									esc_html( _n( '%d week', '%d weeks', (int) $guide_d['weeks'], 'guide-wp-theme' ) ),
									(int) $guide_d['weeks']
								);
								?>
							</dd>
						<?php endif; ?>
					</dl>

					<a class="button is-fullwidth mt-5" href="<?php echo esc_url( get_post_type_archive_link( 'success_story' ) ); ?>">
						<?php esc_html_e( 'More stories', 'guide-wp-theme' ); ?>
					</a>
				</aside>
			</div>

			<section class="guide-cta" style="max-width:48rem;margin-inline:auto">
				<div class="guide-cta__inner">
					<h2 class="title is-3"><?php esc_html_e( 'Your story could be next', 'guide-wp-theme' ); ?></h2>
					<p class="mt-3"><?php esc_html_e( 'They started where you are, with the same fog and the same doubt. Pick a path and work through it.', 'guide-wp-theme' ); ?></p>
					<div class="guide-hero__actions" style="justify-content:center">
						<a class="button is-primary is-medium" href="<?php echo esc_url( get_post_type_archive_link( 'learning_path' ) ); ?>">
							<?php esc_html_e( 'Browse learning paths', 'guide-wp-theme' ); ?>
						</a>
					</div>
				</div>
			</section>
		</div>
	</article>

	<?php
endwhile;

get_footer();
