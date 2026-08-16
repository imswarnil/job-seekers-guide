<?php
/**
 * Course catalogue filtering.
 *
 * Filters are GET parameters applied to the main query, not JavaScript over a
 * pre-rendered list. That means every filtered view has its own shareable,
 * bookmarkable, crawlable URL, it paginates correctly, and it works before
 * (and without) JavaScript — which matters for an audience often on a slow
 * connection and a cheap phone.
 *
 * The rail is a progressive disclosure rather than one flat wall of
 * checkboxes: pick a top-level category and only then are its children
 * revealed. Choosing "Programming Languages" lists the languages; choosing
 * "Computer Science" lists the subjects. Showing all of them at once is
 * exactly the paralysis this platform exists to prevent.
 */

defined( 'ABSPATH' ) || exit;

/**
 * The filter state for the current request, sanitized.
 *
 * @return array{topics:int[], level:string, price:string, q:string}
 */
function guide_course_filter_state() {
	static $state = null;

	if ( null !== $state ) {
		return $state;
	}

	// phpcs:disable WordPress.Security.NonceVerification.Recommended -- read-only, public catalogue filtering.
	$topics = array();
	if ( isset( $_GET['topic'] ) ) {
		$raw = is_array( $_GET['topic'] ) ? $_GET['topic'] : explode( ',', (string) $_GET['topic'] );
		foreach ( $raw as $id ) {
			$id = (int) $id;
			if ( $id > 0 ) {
				$topics[] = $id;
			}
		}
	}

	$level = isset( $_GET['level'] ) ? sanitize_key( (string) $_GET['level'] ) : '';
	if ( ! in_array( $level, array( 'beginner', 'intermediate', 'advanced' ), true ) ) {
		$level = '';
	}

	$price = isset( $_GET['price'] ) ? sanitize_key( (string) $_GET['price'] ) : '';
	if ( ! in_array( $price, array( 'free', 'paid' ), true ) ) {
		$price = '';
	}

	$q = isset( $_GET['q'] ) ? sanitize_text_field( wp_unslash( (string) $_GET['q'] ) ) : '';
	$q = mb_substr( $q, 0, 100 );
	// phpcs:enable WordPress.Security.NonceVerification.Recommended

	$state = array(
		'topics' => array_slice( array_unique( $topics ), 0, 20 ),
		'level'  => $level,
		'price'  => $price,
		'q'      => $q,
	);

	return $state;
}

/**
 * Apply the filter state to the course archive's main query.
 */
add_action( 'pre_get_posts', 'guide_filter_course_archive' );

function guide_filter_course_archive( $query ) {
	if ( is_admin() || ! $query->is_main_query() ) {
		return;
	}

	if ( ! $query->is_post_type_archive( 'course' ) && ! $query->is_tax( 'course_category' ) ) {
		return;
	}

	$state = guide_course_filter_state();

	if ( $state['topics'] ) {
		$tax_query = (array) $query->get( 'tax_query' );
		$tax_query[] = array(
			'taxonomy'         => 'course_category',
			'field'            => 'term_id',
			'terms'            => $state['topics'],
			'include_children' => true,
			'operator'         => 'IN',
		);
		$query->set( 'tax_query', $tax_query );
	}

	$meta_query = (array) $query->get( 'meta_query' );

	if ( $state['level'] ) {
		$meta_query[] = array(
			'key'   => 'jsl_course_level',
			'value' => $state['level'],
		);
	}

	if ( 'paid' === $state['price'] ) {
		$meta_query[] = array(
			'key'   => 'jsl_pricing_type',
			'value' => 'paid',
		);
	} elseif ( 'free' === $state['price'] ) {
		// Free is the default, so a course with no pricing meta at all is
		// free. Matching only on value 'free' would silently hide every
		// course an author never opened the pricing panel for.
		$meta_query[] = array(
			'relation' => 'OR',
			array(
				'key'   => 'jsl_pricing_type',
				'value' => 'free',
			),
			array(
				'key'     => 'jsl_pricing_type',
				'compare' => 'NOT EXISTS',
			),
		);
	}

	if ( $meta_query ) {
		$query->set( 'meta_query', $meta_query );
	}

	if ( $state['q'] ) {
		$query->set( 's', $state['q'] );
	}
}

/**
 * Build a catalogue URL with one filter value changed.
 *
 * @param string     $key   topic|level|price|q
 * @param string|int $value Value to set, or to toggle off if already active.
 * @return string
 */
function guide_course_filter_url( $key, $value ) {
	$state = guide_course_filter_state();
	$base  = get_post_type_archive_link( 'course' );
	$args  = array();

	if ( $state['level'] ) {
		$args['level'] = $state['level'];
	}
	if ( $state['price'] ) {
		$args['price'] = $state['price'];
	}
	if ( $state['q'] ) {
		$args['q'] = $state['q'];
	}
	if ( $state['topics'] ) {
		$args['topic'] = implode( ',', $state['topics'] );
	}

	if ( 'topic' === $key ) {
		$id      = (int) $value;
		$topics  = $state['topics'];
		$index   = array_search( $id, $topics, true );
		if ( false !== $index ) {
			unset( $topics[ $index ] );
		} else {
			$topics[] = $id;
		}
		$topics = array_values( $topics );

		if ( $topics ) {
			$args['topic'] = implode( ',', $topics );
		} else {
			unset( $args['topic'] );
		}
	} else {
		// Selecting the value that is already active clears it, so every
		// filter control is its own undo.
		if ( (string) $state[ $key ] === (string) $value || '' === $value ) {
			unset( $args[ $key ] );
		} else {
			$args[ $key ] = $value;
		}
	}

	return $args ? add_query_arg( $args, $base ) : $base;
}

/** True when any filter is active. */
function guide_course_filters_active() {
	$state = guide_course_filter_state();
	return (bool) ( $state['topics'] || $state['level'] || $state['price'] || $state['q'] );
}

/**
 * Course categories as a two-level tree, counting only categories in use.
 *
 * @return array<int, array{term:WP_Term, children:WP_Term[]}>
 */
function guide_course_category_tree() {
	$terms = get_terms(
		array(
			'taxonomy'   => 'course_category',
			'hide_empty' => true,
		)
	);

	if ( is_wp_error( $terms ) || ! $terms ) {
		return array();
	}

	$by_parent = array();
	foreach ( $terms as $term ) {
		$by_parent[ (int) $term->parent ][] = $term;
	}

	$tree = array();
	foreach ( $by_parent[0] ?? array() as $top ) {
		$tree[] = array(
			'term'     => $top,
			'children' => $by_parent[ (int) $top->term_id ] ?? array(),
		);
	}

	return $tree;
}
