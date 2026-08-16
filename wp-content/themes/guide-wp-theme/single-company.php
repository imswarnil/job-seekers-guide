<?php
/**
 * A single company guide.
 *
 * Ordered the way a candidate needs it: what this place is, what it pays, how
 * they hire, what they test — and only then the long-form guide. Someone
 * deciding whether to apply this week should not have to read 800 words first.
 */

defined( 'ABSPATH' ) || exit;

use Guide\Companies\Companies;

get_header();

while ( have_posts() ) :
	the_post();

	$guide_id       = get_the_ID();
	$guide_band     = Companies::fresher_band( $guide_id );
	$guide_diff     = Companies::difficulty( $guide_id );
	$guide_salary   = Companies::salary_bands( $guide_id );
	$guide_process  = Companies::process( $guide_id );
	$guide_skills   = Companies::skills( $guide_id );
	$guide_modes    = Companies::modes( $guide_id );
	$guide_verified = (string) get_post_meta( $guide_id, 'jsl_company_verified', true );
	$guide_terms    = get_the_terms( $guide_id, Companies::TAXONOMY );
	?>

	<article>
		<section class="guide-course-hero">
			<div class="guide-shell">
				<nav class="guide-breadcrumb" aria-label="<?php esc_attr_e( 'Breadcrumb', 'guide-wp-theme' ); ?>">
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'guide-wp-theme' ); ?></a>
					<span>/</span>
					<a href="<?php echo esc_url( get_post_type_archive_link( Companies::POST_TYPE ) ); ?>"><?php esc_html_e( 'Companies', 'guide-wp-theme' ); ?></a>
				</nav>

				<div class="guide-company-hero">
					<?php if ( has_post_thumbnail() ) : ?>
						<span class="guide-company-logo guide-company-logo--lg"><?php the_post_thumbnail( 'medium', array( 'alt' => '' ) ); ?></span>
					<?php else : ?>
						<span class="guide-company-logo guide-company-logo--lg guide-company-logo--letter"><?php echo esc_html( mb_substr( get_the_title(), 0, 1 ) ); ?></span>
					<?php endif; ?>

					<div>
						<h1 class="guide-display">
							<?php
							printf(
								/* translators: %s: company name. */
								esc_html__( 'How to get a job at %s', 'guide-wp-theme' ),
								esc_html( get_the_title() )
							);
							?>
						</h1>

						<?php if ( has_excerpt() ) : ?>
							<p class="guide-course-hero__lede"><?php echo esc_html( get_the_excerpt() ); ?></p>
						<?php endif; ?>

						<div class="guide-course-hero__meta">
							<?php if ( $guide_terms && ! is_wp_error( $guide_terms ) ) : ?>
								<span class="guide-chip guide-chip--spark"><?php echo esc_html( $guide_terms[0]->name ); ?></span>
							<?php endif; ?>
							<?php if ( $guide_band ) : ?>
								<span class="guide-chip guide-chip--outline">
									<?php echo esc_html( Companies::format_band( $guide_band['min'], $guide_band['max'] ) ); ?>
								</span>
							<?php endif; ?>
							<span class="guide-chip guide-chip--outline">
								<?php echo esc_html( Companies::difficulty_label( $guide_id ) ); ?>
							</span>
						</div>
					</div>
				</div>
			</div>
		</section>

		<div class="guide-shell guide-section guide-section--tight">
			<div class="guide-doc-layout">
				<div>
					<!-- At a glance --------------------------------------- -->
					<section class="guide-card" style="padding:1.5rem" aria-labelledby="guide-glance">
						<h2 class="guide-card__title" id="guide-glance"><?php esc_html_e( 'At a glance', 'guide-wp-theme' ); ?></h2>

						<dl class="guide-glance mt-3">
							<?php
							$guide_facts = array(
								'jsl_company_hq'        => __( 'Headquarters', 'guide-wp-theme' ),
								'jsl_company_locations' => __( 'Hiring in', 'guide-wp-theme' ),
								'jsl_company_headcount' => __( 'Size', 'guide-wp-theme' ),
								'jsl_company_window'    => __( 'When they hire', 'guide-wp-theme' ),
							);

							foreach ( $guide_facts as $guide_key => $guide_label ) :
								$guide_value = (string) get_post_meta( $guide_id, $guide_key, true );
								if ( ! $guide_value ) {
									continue;
								}
								?>
								<div>
									<dt><?php echo esc_html( $guide_label ); ?></dt>
									<dd><?php echo esc_html( $guide_value ); ?></dd>
								</div>
							<?php endforeach; ?>

							<div>
								<dt><?php esc_html_e( 'How hard to get in', 'guide-wp-theme' ); ?></dt>
								<dd>
									<span class="guide-difficulty">
										<?php for ( $guide_i = 1; $guide_i <= 5; $guide_i++ ) : ?>
											<span class="guide-difficulty__pip<?php echo $guide_i <= $guide_diff ? ' is-on' : ''; ?>"></span>
										<?php endfor; ?>
									</span>
									<?php echo esc_html( Companies::difficulty_label( $guide_id ) ); ?>
								</dd>
							</div>
						</dl>

						<?php if ( $guide_modes ) : ?>
							<p class="guide-filter-group__label mt-4"><?php esc_html_e( 'How they hire', 'guide-wp-theme' ); ?></p>
							<div class="is-flex mt-2" style="gap:.4rem;flex-wrap:wrap">
								<?php foreach ( $guide_modes as $guide_mode ) : ?>
									<span class="guide-chip"><?php echo esc_html( Companies::HIRING_MODES[ $guide_mode ] ?? $guide_mode ); ?></span>
								<?php endforeach; ?>
							</div>
						<?php endif; ?>

						<?php $guide_site = (string) get_post_meta( $guide_id, 'jsl_company_website', true ); ?>
						<?php if ( $guide_site ) : ?>
							<a class="button is-small mt-4" href="<?php echo esc_url( $guide_site ); ?>" target="_blank" rel="noopener nofollow">
								<?php esc_html_e( 'Their careers page', 'guide-wp-theme' ); ?>
							</a>
						<?php endif; ?>
					</section>

					<!-- Salary --------------------------------------------- -->
					<?php if ( $guide_salary ) : ?>
						<section class="mt-6" aria-labelledby="guide-pay">
							<h2 class="title is-5" id="guide-pay"><?php esc_html_e( 'What they pay', 'guide-wp-theme' ); ?></h2>

							<div class="guide-table-scroll mt-3">
								<table class="guide-billing-table">
									<thead>
										<tr>
											<th scope="col"><?php esc_html_e( 'Role', 'guide-wp-theme' ); ?></th>
											<th scope="col"><?php esc_html_e( 'Level', 'guide-wp-theme' ); ?></th>
											<th scope="col"><?php esc_html_e( 'Band', 'guide-wp-theme' ); ?></th>
										</tr>
									</thead>
									<tbody>
										<?php foreach ( $guide_salary as $guide_row ) : ?>
											<tr>
												<td><?php echo esc_html( $guide_row['role'] ); ?></td>
												<td><?php echo 'experienced' === ( $guide_row['level'] ?? '' ) ? esc_html__( 'Experienced', 'guide-wp-theme' ) : esc_html__( 'Fresher', 'guide-wp-theme' ); ?></td>
												<td><?php echo esc_html( Companies::format_band( $guide_row['min'] ?? 0, $guide_row['max'] ?? 0 ) ); ?></td>
											</tr>
										<?php endforeach; ?>
									</tbody>
								</table>
							</div>

							<p class="guide-notice guide-notice--info mt-3">
								<span>
									<?php esc_html_e( 'Bands, not promises. Compensation varies by role, location, interview performance and the year — and it moves. Treat these as a starting point for your own research, never as a number to quote back at anyone.', 'guide-wp-theme' ); ?>
									<?php if ( $guide_verified ) : ?>
										<?php
										printf(
											/* translators: %s: date the figures were last checked. */
											' ' . esc_html__( 'Last checked %s.', 'guide-wp-theme' ),
											esc_html( date_i18n( get_option( 'date_format' ), strtotime( $guide_verified ) ) )
										);
										?>
									<?php endif; ?>
								</span>
							</p>
						</section>
					<?php endif; ?>

					<!-- Selection process ---------------------------------- -->
					<?php if ( $guide_process ) : ?>
						<section class="mt-6" aria-labelledby="guide-rounds">
							<h2 class="title is-5" id="guide-rounds"><?php esc_html_e( 'The selection process', 'guide-wp-theme' ); ?></h2>
							<p class="mt-1 is-size-7" style="color:var(--bulma-text-weak)">
								<?php esc_html_e( 'In the order you meet them. Knowing which round is which is half of not being surprised by it.', 'guide-wp-theme' ); ?>
							</p>

							<ol class="guide-timeline mt-4">
								<?php foreach ( $guide_process as $guide_i => $guide_step ) : ?>
									<li class="guide-timeline__item">
										<span class="guide-timeline__marker"><?php echo esc_html( (string) ( $guide_i + 1 ) ); ?></span>
										<div>
											<h3 class="guide-timeline__title"><?php echo esc_html( $guide_step['title'] ); ?></h3>
											<?php if ( ! empty( $guide_step['detail'] ) ) : ?>
												<p class="guide-timeline__text"><?php echo esc_html( $guide_step['detail'] ); ?></p>
											<?php endif; ?>
										</div>
									</li>
								<?php endforeach; ?>
							</ol>
						</section>
					<?php endif; ?>

					<!-- Skills --------------------------------------------- -->
					<?php if ( $guide_skills ) : ?>
						<section class="mt-6" aria-labelledby="guide-skills">
							<h2 class="title is-5" id="guide-skills"><?php esc_html_e( 'What they test', 'guide-wp-theme' ); ?></h2>
							<p class="mt-1 is-size-7" style="color:var(--bulma-text-weak)">
								<?php esc_html_e( 'Where we have a course for something, it is linked. Start there rather than searching for a roadmap.', 'guide-wp-theme' ); ?>
							</p>

							<div class="guide-skill-list mt-3">
								<?php foreach ( $guide_skills as $guide_skill ) : ?>
									<?php $guide_course = (int) ( $guide_skill['course'] ?? 0 ); ?>
									<div class="guide-skill">
										<span class="guide-skill__name"><?php echo esc_html( $guide_skill['name'] ); ?></span>
										<?php if ( $guide_course && 'publish' === get_post_status( $guide_course ) ) : ?>
											<a class="button is-small" href="<?php echo esc_url( get_permalink( $guide_course ) ); ?>">
												<?php echo esc_html( get_the_title( $guide_course ) ); ?>
											</a>
										<?php else : ?>
											<span class="guide-skill__none"><?php esc_html_e( 'No course yet', 'guide-wp-theme' ); ?></span>
										<?php endif; ?>
									</div>
								<?php endforeach; ?>
							</div>
						</section>
					<?php endif; ?>

					<!-- The guide ------------------------------------------ -->
					<?php if ( trim( (string) get_the_content() ) ) : ?>
						<section class="mt-6">
							<div class="guide-prose" data-toc-source><?php the_content(); ?></div>
						</section>
					<?php endif; ?>

					<?php get_template_part( 'template-parts/feedback', null, array( 'object_type' => 'company' ) ); ?>
				</div>

				<aside class="guide-toc" data-toc aria-label="<?php esc_attr_e( 'On this page', 'guide-wp-theme' ); ?>">
					<p class="guide-filter-group__label"><?php esc_html_e( 'On this page', 'guide-wp-theme' ); ?></p>
					<nav class="guide-toc__list" data-toc-list></nav>
				</aside>
			</div>
		</div>
	</article>

	<?php
endwhile;

get_footer();
