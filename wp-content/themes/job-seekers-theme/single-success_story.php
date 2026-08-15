<?php
/**
 * A single success story — a long-form reading layout.
 *
 * Wide, quiet, and typographically generous: this is the one place on the
 * site someone reads several hundred words end to end.
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	$story_id = get_the_ID();
	$d        = class_exists( 'JSL\\Success\\Success_Stories' ) ? \JSL\Success\Success_Stories::details( $story_id ) : array(
		'company'  => '',
		'role'     => '',
		'previous' => '',
		'weeks'    => 0,
		'linkedin' => '',
	);
	$author_id = (int) get_the_author_meta( 'ID' );
	?>

	<article>
		<!-- Header -->
		<header class="relative overflow-hidden bg-hero text-on-hero">
			<div class="pointer-events-none absolute inset-0" aria-hidden="true"
				style="background: radial-gradient(45rem 28rem at 20% -20%, color-mix(in srgb, var(--md-tertiary-40) 50%, transparent), transparent 70%);"></div>

			<div class="jsl-container relative py-14 md:py-18">
				<nav class="text-sm text-hero-muted" aria-label="<?php esc_attr_e( 'Breadcrumb', 'job-seekers-theme' ); ?>">
					<a class="text-hero-muted hover:text-on-hero" href="<?php echo esc_url( get_post_type_archive_link( 'success_story' ) ); ?>">
						<?php esc_html_e( 'Wall of Success', 'job-seekers-theme' ); ?>
					</a>
				</nav>

				<h1 class="mt-5 max-w-[22ch] text-balance font-display text-[clamp(2rem,1.4rem+2.2vw,2.9rem)] font-extrabold leading-[1.1] tracking-[-0.03em]">
					<?php the_title(); ?>
				</h1>

				<div class="mt-8 flex flex-wrap items-center gap-x-6 gap-y-4">
					<div class="flex items-center gap-3">
						<?php echo jsl_avatar( $author_id, 52, 'ring-2 ring-white/20' ); ?>
						<div>
							<p class="m-0 font-bold"><?php the_author(); ?></p>
							<p class="m-0 text-sm text-hero-muted"><?php echo esc_html( get_the_date() ); ?></p>
						</div>
					</div>

					<?php if ( $d['linkedin'] ) : ?>
						<a class="jsl-btn jsl-btn--hero-ghost jsl-btn--sm" href="<?php echo esc_url( $d['linkedin'] ); ?>" target="_blank" rel="noopener nofollow">
							<?php echo jsl_icon( 'link-simple', 'w-4 h-4' ); ?>
							<?php esc_html_e( 'LinkedIn', 'job-seekers-theme' ); ?>
						</a>
					<?php endif; ?>
				</div>
			</div>
		</header>

		<div class="jsl-container py-12">
			<div class="mx-auto grid max-w-5xl gap-12 lg:grid-cols-[minmax(0,1fr)_18rem] lg:items-start">

				<!-- The story -->
				<div class="jsl-prose max-w-[68ch] text-[1.075rem]">
					<?php the_content(); ?>
				</div>

				<!-- The facts, pulled out so they're scannable -->
				<aside class="md-card md-card--filled p-6 lg:sticky lg:top-24">
					<h2 class="m-0 text-[0.65rem] font-bold uppercase tracking-widest text-on-surface-variant">
						<?php esc_html_e( 'The move', 'job-seekers-theme' ); ?>
					</h2>

					<dl class="m-0 mt-4 flex flex-col gap-4">
						<?php if ( $d['previous'] ) : ?>
							<div>
								<dt class="text-xs font-semibold text-on-surface-variant"><?php esc_html_e( 'From', 'job-seekers-theme' ); ?></dt>
								<dd class="m-0 mt-0.5 font-semibold text-on-surface"><?php echo esc_html( $d['previous'] ); ?></dd>
							</div>
						<?php endif; ?>

						<?php if ( $d['role'] ) : ?>
							<div>
								<dt class="text-xs font-semibold text-on-surface-variant"><?php esc_html_e( 'To', 'job-seekers-theme' ); ?></dt>
								<dd class="m-0 mt-0.5 font-semibold text-on-surface"><?php echo esc_html( $d['role'] ); ?></dd>
							</div>
						<?php endif; ?>

						<?php if ( $d['company'] ) : ?>
							<div>
								<dt class="text-xs font-semibold text-on-surface-variant"><?php esc_html_e( 'Company', 'job-seekers-theme' ); ?></dt>
								<dd class="m-0 mt-0.5 font-semibold text-on-surface"><?php echo esc_html( $d['company'] ); ?></dd>
							</div>
						<?php endif; ?>

						<?php if ( $d['weeks'] ) : ?>
							<div>
								<dt class="text-xs font-semibold text-on-surface-variant"><?php esc_html_e( 'Time searching', 'job-seekers-theme' ); ?></dt>
								<dd class="m-0 mt-0.5 font-semibold text-on-surface">
									<?php printf( esc_html( _n( '%d week', '%d weeks', $d['weeks'], 'job-seekers-theme' ) ), (int) $d['weeks'] ); ?>
								</dd>
							</div>
						<?php endif; ?>
					</dl>

					<a class="jsl-btn jsl-btn--tonal jsl-btn--block mt-6" href="<?php echo esc_url( get_post_type_archive_link( 'success_story' ) ); ?>">
						<?php esc_html_e( 'More stories', 'job-seekers-theme' ); ?>
					</a>
				</aside>
			</div>

			<!-- Closing CTA -->
			<section class="mx-auto mt-16 max-w-3xl">
				<div class="relative overflow-hidden rounded-3xl bg-hero px-8 py-12 text-center text-on-hero">
					<div class="pointer-events-none absolute inset-0" aria-hidden="true"
						style="background: radial-gradient(30rem 18rem at 50% 0%, color-mix(in srgb, var(--md-primary-40) 60%, transparent), transparent 70%);"></div>
					<div class="relative">
						<h2 class="m-0 font-display text-2xl font-extrabold tracking-tight"><?php esc_html_e( 'Your story could be next', 'job-seekers-theme' ); ?></h2>
						<p class="mx-auto mt-3 max-w-sm text-hero-muted"><?php esc_html_e( 'Pick a path and start working through it — the same way they did.', 'job-seekers-theme' ); ?></p>
						<a class="jsl-btn jsl-btn--primary mt-7" href="<?php echo esc_url( get_post_type_archive_link( 'learning_path' ) ); ?>">
							<?php esc_html_e( 'Browse learning paths', 'job-seekers-theme' ); ?>
						</a>
					</div>
				</div>
			</section>
		</div>
	</article>

	<?php
endwhile;

get_footer();
