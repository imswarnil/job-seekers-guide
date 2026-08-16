<?php
/**
 * The catalogue filter rail.
 *
 * Every control is a link, so the whole thing works without JavaScript and
 * every filtered view has its own URL. See inc/course-filters.php.
 */

defined( 'ABSPATH' ) || exit;

$guide_state = guide_course_filter_state();
$guide_tree  = guide_course_category_tree();
$guide_base  = get_post_type_archive_link( 'course' );
?>

<button type="button" class="button is-fullwidth guide-filters-toggle" data-filters-toggle="guide-filters" aria-expanded="false" aria-controls="guide-filters">
	<?php echo guide_icon( 'funnel' ); ?>
	<span class="ml-2"><?php esc_html_e( 'Filters', 'guide-wp-theme' ); ?></span>
</button>

<aside class="guide-filters mt-3" id="guide-filters" aria-label="<?php esc_attr_e( 'Filter courses', 'guide-wp-theme' ); ?>">

	<div class="guide-filters__head">
		<h2 class="guide-filters__title"><?php esc_html_e( 'Filter', 'guide-wp-theme' ); ?></h2>
		<?php if ( guide_course_filters_active() ) : ?>
			<a class="guide-filters__reset" href="<?php echo esc_url( $guide_base ); ?>"><?php esc_html_e( 'Clear all', 'guide-wp-theme' ); ?></a>
		<?php endif; ?>
	</div>

	<form class="guide-filter-group" method="get" action="<?php echo esc_url( $guide_base ); ?>" role="search">
		<label class="guide-filter-group__label" for="guide-course-search"><?php esc_html_e( 'Search', 'guide-wp-theme' ); ?></label>
		<div class="field has-addons">
			<div class="control is-expanded">
				<input class="input is-small" type="search" id="guide-course-search" name="q"
					value="<?php echo esc_attr( $guide_state['q'] ); ?>"
					placeholder="<?php esc_attr_e( 'e.g. SQL, Java, resume', 'guide-wp-theme' ); ?>">
			</div>
			<div class="control">
				<button class="button is-small is-primary" type="submit" aria-label="<?php esc_attr_e( 'Search courses', 'guide-wp-theme' ); ?>">
					<?php echo guide_icon( 'magnifying-glass' ); ?>
				</button>
			</div>
		</div>
		<?php // Keep the other active filters when searching. ?>
		<?php if ( $guide_state['level'] ) : ?>
			<input type="hidden" name="level" value="<?php echo esc_attr( $guide_state['level'] ); ?>">
		<?php endif; ?>
		<?php if ( $guide_state['price'] ) : ?>
			<input type="hidden" name="price" value="<?php echo esc_attr( $guide_state['price'] ); ?>">
		<?php endif; ?>
		<?php if ( $guide_state['topics'] ) : ?>
			<input type="hidden" name="topic" value="<?php echo esc_attr( implode( ',', $guide_state['topics'] ) ); ?>">
		<?php endif; ?>
	</form>

	<?php if ( $guide_tree ) : ?>
		<div class="guide-filter-group">
			<span class="guide-filter-group__label"><?php esc_html_e( 'Subject', 'guide-wp-theme' ); ?></span>

			<?php foreach ( $guide_tree as $guide_branch ) : ?>
				<?php
				$guide_top      = $guide_branch['term'];
				$guide_children = $guide_branch['children'];
				$guide_top_on   = in_array( (int) $guide_top->term_id, $guide_state['topics'], true );

				// A branch opens when it is selected, or when one of its
				// children is — otherwise a filtered child would sit inside a
				// collapsed parent and look like it had been lost.
				$guide_child_on = false;
				foreach ( $guide_children as $guide_child ) {
					if ( in_array( (int) $guide_child->term_id, $guide_state['topics'], true ) ) {
						$guide_child_on = true;
						break;
					}
				}
				$guide_open = $guide_top_on || $guide_child_on;
				?>

				<a class="guide-filter-option <?php echo $guide_top_on ? 'is-active' : ''; ?>"
					href="<?php echo esc_url( guide_course_filter_url( 'topic', $guide_top->term_id ) ); ?>"
					<?php echo $guide_top_on ? 'aria-current="true"' : ''; ?>>
					<input type="checkbox" tabindex="-1" aria-hidden="true" <?php checked( $guide_top_on ); ?> readonly>
					<span><?php echo esc_html( $guide_top->name ); ?></span>
					<span class="guide-filter-option__count"><?php echo esc_html( (string) $guide_top->count ); ?></span>
				</a>

				<?php if ( $guide_children ) : ?>
					<div class="guide-filter-children <?php echo $guide_open ? 'is-open' : ''; ?>">
						<?php foreach ( $guide_children as $guide_child ) : ?>
							<?php $guide_child_active = in_array( (int) $guide_child->term_id, $guide_state['topics'], true ); ?>
							<a class="guide-filter-option <?php echo $guide_child_active ? 'is-active' : ''; ?>"
								href="<?php echo esc_url( guide_course_filter_url( 'topic', $guide_child->term_id ) ); ?>"
								<?php echo $guide_child_active ? 'aria-current="true"' : ''; ?>>
								<input type="checkbox" tabindex="-1" aria-hidden="true" <?php checked( $guide_child_active ); ?> readonly>
								<span><?php echo esc_html( $guide_child->name ); ?></span>
								<span class="guide-filter-option__count"><?php echo esc_html( (string) $guide_child->count ); ?></span>
							</a>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>

	<div class="guide-filter-group">
		<span class="guide-filter-group__label"><?php esc_html_e( 'Level', 'guide-wp-theme' ); ?></span>
		<?php
		$guide_levels = array(
			'beginner'     => __( 'Beginner — start from zero', 'guide-wp-theme' ),
			'intermediate' => __( 'Intermediate', 'guide-wp-theme' ),
			'advanced'     => __( 'Advanced', 'guide-wp-theme' ),
		);
		foreach ( $guide_levels as $guide_key => $guide_label ) :
			$guide_on = $guide_state['level'] === $guide_key;
			?>
			<a class="guide-filter-option <?php echo $guide_on ? 'is-active' : ''; ?>"
				href="<?php echo esc_url( guide_course_filter_url( 'level', $guide_key ) ); ?>">
				<input type="radio" tabindex="-1" aria-hidden="true" <?php checked( $guide_on ); ?> readonly>
				<span><?php echo esc_html( $guide_label ); ?></span>
			</a>
		<?php endforeach; ?>
	</div>

	<div class="guide-filter-group">
		<span class="guide-filter-group__label"><?php esc_html_e( 'Price', 'guide-wp-theme' ); ?></span>
		<?php
		$guide_prices = array(
			'free' => __( 'Free', 'guide-wp-theme' ),
			'paid' => __( 'Paid', 'guide-wp-theme' ),
		);
		foreach ( $guide_prices as $guide_key => $guide_label ) :
			$guide_on = $guide_state['price'] === $guide_key;
			?>
			<a class="guide-filter-option <?php echo $guide_on ? 'is-active' : ''; ?>"
				href="<?php echo esc_url( guide_course_filter_url( 'price', $guide_key ) ); ?>">
				<input type="radio" tabindex="-1" aria-hidden="true" <?php checked( $guide_on ); ?> readonly>
				<span><?php echo esc_html( $guide_label ); ?></span>
			</a>
		<?php endforeach; ?>
	</div>
</aside>
