<?php
/**
 * Course landing template.
 *
 * A classic PHP template (WordPress supports mixing these into block
 * themes via the template hierarchy) because the course landing page needs
 * data from the plugin's Course_Api — not something the block editor can
 * express on its own yet. Block themes don't ship header.php/footer.php,
 * so get_header()/get_footer() would silently no-op here; we build the
 * document shell manually and render the header/footer template parts via
 * block_template_part(), which is the supported way to mix a classic PHP
 * template into a block theme.
 */

defined( 'ABSPATH' ) || exit;

?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<?php block_template_part( 'header' ); ?>

<?php
while ( have_posts() ) :
	the_post();
	$course_id = get_the_ID();
	$lessons   = class_exists( 'JSL\\Course_Api' ) ? \JSL\Course_Api::get_lessons( $course_id ) : array();
	?>

	<main class="wp-block-group" style="max-width:var(--wp--style--global--content-size);margin-inline:auto;padding-block:var(--wp--preset--spacing--6)">

		<article>
			<h1 style="font-size:var(--wp--preset--font-size--2xl)"><?php the_title(); ?></h1>

			<?php if ( has_excerpt() ) : ?>
				<p style="font-size:var(--wp--preset--font-size--md);color:var(--wp--preset--color--ink-500)">
					<?php echo esc_html( get_the_excerpt() ); ?>
				</p>
			<?php endif; ?>

			<div style="margin-block:var(--wp--preset--spacing--5)">
				<?php the_content(); ?>
			</div>

			<section aria-labelledby="jsl-course-lessons">
				<h2 id="jsl-course-lessons" style="font-size:var(--wp--preset--font-size--lg)">
					<?php esc_html_e( 'Lessons', 'job-seekers-theme' ); ?>
				</h2>

				<?php if ( empty( $lessons ) ) : ?>
					<p style="color:var(--wp--preset--color--ink-500)">
						<?php esc_html_e( 'No lessons added yet.', 'job-seekers-theme' ); ?>
					</p>
				<?php else : ?>
					<ol style="list-style:none;padding:0;display:flex;flex-direction:column;gap:var(--wp--preset--spacing--2)">
						<?php foreach ( $lessons as $index => $lesson ) : ?>
							<li style="border:1px solid var(--wp--preset--color--paper-200);border-radius:var(--wp--custom--radius--md);padding:var(--wp--preset--spacing--3) var(--wp--preset--spacing--4)">
								<a href="<?php echo esc_url( get_permalink( $lesson ) ); ?>" style="text-decoration:none">
									<span style="color:var(--wp--preset--color--ink-500)"><?php echo esc_html( $index + 1 ); ?>.</span>
									<?php echo esc_html( get_the_title( $lesson ) ); ?>
								</a>
							</li>
						<?php endforeach; ?>
					</ol>
				<?php endif; ?>
			</section>
		</article>

	</main>

	<?php
endwhile;
?>

<?php block_template_part( 'footer' ); ?>

<?php wp_footer(); ?>
</body>
</html>

