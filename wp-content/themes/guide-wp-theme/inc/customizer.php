<?php
/**
 * Customizer support.
 *
 * What belongs here and what does not:
 *
 *   · The Customizer is for how the site *looks and reads* — brand colour,
 *     the words in the hero, which homepage sections appear, the footer.
 *     Things somebody adjusts once while deciding whether the site feels
 *     right, with a live preview beside them.
 *
 *   · Payments, ads, sign-in and access control stay in
 *     LMS → Settings. They are operational, they need explanation and
 *     validation, and a live preview of a webhook secret is meaningless.
 *
 * The split matters because a settings screen that contains everything is a
 * settings screen nobody reads.
 *
 * Every default here is the string the theme shipped with, so an untouched
 * install renders exactly as it did before this file existed.
 */

defined( 'ABSPATH' ) || exit;

/**
 * The defaults, in one place so the templates and the controls cannot drift.
 *
 * @return array<string, string|bool>
 */
function guide_customizer_defaults() {
	return array(
		'guide_primary_color'   => '#414ba0',
		'guide_spark_color'     => '#e9a92a',

		'guide_hero_eyebrow'    => __( 'Free. Not a trial, not a teaser.', 'guide-wp-theme' ),
		'guide_hero_heading'    => __( 'You were never bad at this. Nobody gave you the order.', 'guide-wp-theme' ),
		'guide_hero_lede'       => __( 'Everything a training institute charges ₹80,000 to teach you is already free on the internet. What is not free is knowing what to learn first, what to skip, and what actually gets asked in interviews. That is what this is.', 'guide-wp-theme' ),
		'guide_hero_cta'        => __( 'Start at the beginning', 'guide-wp-theme' ),
		'guide_hero_cta_alt'    => __( 'Browse courses', 'guide-wp-theme' ),

		'guide_show_problem'    => true,
		'guide_show_how'        => true,
		'guide_show_pricing'    => true,
		'guide_show_stats'      => true,

		'guide_footer_tagline'  => __( 'Built by somebody who was rejected 33 times, for everybody who is being rejected now.', 'guide-wp-theme' ),
		'guide_github_url'      => '',
		'guide_linkedin_url'    => '',
		'guide_youtube_url'     => '',
	);
}

/**
 * One setting's value, falling back to the shipped default.
 *
 * @param string $key
 * @return string|bool
 */
function guide_option( $key ) {
	$defaults = guide_customizer_defaults();
	$default  = $defaults[ $key ] ?? '';

	return get_theme_mod( $key, $default );
}

/** Convenience for the boolean toggles. */
function guide_shows( $key ): bool {
	return (bool) guide_option( $key );
}

add_action( 'customize_register', 'guide_customize_register' );

/**
 * @param WP_Customize_Manager $wp_customize
 */
function guide_customize_register( $wp_customize ) {
	$defaults = guide_customizer_defaults();

	// Make the built-in bits live-update rather than reloading the whole page.
	$wp_customize->get_setting( 'blogname' )->transport        = 'postMessage';
	$wp_customize->get_setting( 'blogdescription' )->transport = 'postMessage';

	$wp_customize->add_panel(
		'guide_panel',
		array(
			'title'       => __( 'Guide theme', 'guide-wp-theme' ),
			'description' => __( 'How the site looks and what the homepage says. Payments, ads and sign-in live in LMS → Settings.', 'guide-wp-theme' ),
			'priority'    => 20,
		)
	);

	// -------------------------------------------------------------------------
	// Brand
	// -------------------------------------------------------------------------

	$wp_customize->add_section(
		'guide_brand',
		array(
			'title'       => __( 'Brand colours', 'guide-wp-theme' ),
			'panel'       => 'guide_panel',
			'description' => __( 'Both colours are used against white and against the dark hero, so very light choices will fail contrast in one of the two. The theme does not correct this for you.', 'guide-wp-theme' ),
		)
	);

	$colors = array(
		'guide_primary_color' => array(
			'label' => __( 'Primary', 'guide-wp-theme' ),
			'desc'  => __( 'Buttons, links, progress bars.', 'guide-wp-theme' ),
		),
		'guide_spark_color'   => array(
			'label' => __( 'Accent', 'guide-wp-theme' ),
			'desc'  => __( 'Highlights and the “free” chip.', 'guide-wp-theme' ),
		),
	);

	foreach ( $colors as $key => $meta ) {
		$wp_customize->add_setting(
			$key,
			array(
				'default'           => $defaults[ $key ],
				'sanitize_callback' => 'sanitize_hex_color',
				'transport'         => 'postMessage',
			)
		);

		$wp_customize->add_control(
			new WP_Customize_Color_Control(
				$wp_customize,
				$key,
				array(
					'label'       => $meta['label'],
					'description' => $meta['desc'],
					'section'     => 'guide_brand',
				)
			)
		);
	}

	// -------------------------------------------------------------------------
	// Hero
	// -------------------------------------------------------------------------

	$wp_customize->add_section(
		'guide_hero',
		array(
			'title'       => __( 'Homepage hero', 'guide-wp-theme' ),
			'panel'       => 'guide_panel',
			'description' => __( 'The first screen. Say what the site is and who it is for — a headline that could belong to any course platform is a wasted headline.', 'guide-wp-theme' ),
		)
	);

	$hero_fields = array(
		'guide_hero_eyebrow' => array( __( 'Eyebrow', 'guide-wp-theme' ), 'text' ),
		'guide_hero_heading' => array( __( 'Headline', 'guide-wp-theme' ), 'textarea' ),
		'guide_hero_lede'    => array( __( 'Opening paragraph', 'guide-wp-theme' ), 'textarea' ),
		'guide_hero_cta'     => array( __( 'Primary button', 'guide-wp-theme' ), 'text' ),
		'guide_hero_cta_alt' => array( __( 'Secondary button', 'guide-wp-theme' ), 'text' ),
	);

	foreach ( $hero_fields as $key => $field ) {
		$wp_customize->add_setting(
			$key,
			array(
				'default'           => $defaults[ $key ],
				'sanitize_callback' => 'wp_kses_post',
				'transport'         => 'postMessage',
			)
		);

		$wp_customize->add_control(
			$key,
			array(
				'label'   => $field[0],
				'section' => 'guide_hero',
				'type'    => $field[1],
			)
		);
	}

	// -------------------------------------------------------------------------
	// Homepage sections
	// -------------------------------------------------------------------------

	$wp_customize->add_section(
		'guide_sections',
		array(
			'title'       => __( 'Homepage sections', 'guide-wp-theme' ),
			'panel'       => 'guide_panel',
			'description' => __( 'Turn a section off if it is not earning its place. The learning paths and the closing call to action are always shown — without them the page is a brochure.', 'guide-wp-theme' ),
		)
	);

	$toggles = array(
		'guide_show_stats'   => __( 'Course and lesson counts in the hero', 'guide-wp-theme' ),
		'guide_show_problem' => __( '“Why this exists” — the story and comparison', 'guide-wp-theme' ),
		'guide_show_how'     => __( '“How it works” — the three steps', 'guide-wp-theme' ),
		'guide_show_pricing' => __( 'Subscription pricing', 'guide-wp-theme' ),
	);

	foreach ( $toggles as $key => $label ) {
		$wp_customize->add_setting(
			$key,
			array(
				'default'           => $defaults[ $key ],
				'sanitize_callback' => 'guide_sanitize_checkbox',
			)
		);

		$wp_customize->add_control(
			$key,
			array(
				'label'   => $label,
				'section' => 'guide_sections',
				'type'    => 'checkbox',
			)
		);
	}

	// -------------------------------------------------------------------------
	// Footer
	// -------------------------------------------------------------------------

	$wp_customize->add_section(
		'guide_footer',
		array(
			'title' => __( 'Footer and links', 'guide-wp-theme' ),
			'panel' => 'guide_panel',
		)
	);

	$wp_customize->add_setting(
		'guide_footer_tagline',
		array(
			'default'           => $defaults['guide_footer_tagline'],
			'sanitize_callback' => 'wp_kses_post',
			'transport'         => 'postMessage',
		)
	);

	$wp_customize->add_control(
		'guide_footer_tagline',
		array(
			'label'   => __( 'Footer line', 'guide-wp-theme' ),
			'section' => 'guide_footer',
			'type'    => 'textarea',
		)
	);

	$links = array(
		'guide_github_url'   => __( 'GitHub repository', 'guide-wp-theme' ),
		'guide_linkedin_url' => __( 'LinkedIn', 'guide-wp-theme' ),
		'guide_youtube_url'  => __( 'YouTube', 'guide-wp-theme' ),
	);

	foreach ( $links as $key => $label ) {
		$wp_customize->add_setting(
			$key,
			array(
				'default'           => '',
				'sanitize_callback' => 'esc_url_raw',
			)
		);

		$wp_customize->add_control(
			$key,
			array(
				'label'       => $label,
				'section'     => 'guide_footer',
				'type'        => 'url',
				'input_attrs' => array( 'placeholder' => 'https://' ),
			)
		);
	}

	// Selective refresh for the parts that are cheap to re-render on their own.
	if ( isset( $wp_customize->selective_refresh ) ) {
		$wp_customize->selective_refresh->add_partial(
			'guide_hero_heading',
			array(
				'selector'        => '.guide-hero .guide-display',
				'render_callback' => function () {
					return guide_option( 'guide_hero_heading' );
				},
			)
		);

		$wp_customize->selective_refresh->add_partial(
			'guide_hero_lede',
			array(
				'selector'        => '.guide-hero__lede',
				'render_callback' => function () {
					return guide_option( 'guide_hero_lede' );
				},
			)
		);
	}
}

/**
 * @param mixed $value
 */
function guide_sanitize_checkbox( $value ): bool {
	return (bool) $value;
}

/**
 * Push the chosen brand colours into the page as custom-property overrides.
 *
 * A handful of declarations rather than a regenerated stylesheet: the theme is
 * already built on custom properties, so overriding two of them re-colours
 * everything that derives from them — including dark mode, which reads the same
 * variables.
 *
 * Nothing is printed when the colours are untouched, so the default install
 * carries no extra bytes.
 */
add_action( 'wp_head', 'guide_customizer_css', 20 );

function guide_customizer_css() {
	$defaults = guide_customizer_defaults();
	$primary  = sanitize_hex_color( (string) guide_option( 'guide_primary_color' ) );
	$spark    = sanitize_hex_color( (string) guide_option( 'guide_spark_color' ) );

	$rules = '';

	if ( $primary && $primary !== $defaults['guide_primary_color'] ) {
		$rules .= guide_brand_vars( 'primary', $primary );
		$rules .= guide_brand_vars( 'link', $primary );
		$rules .= '--guide-primary:' . $primary . ';';
	}

	if ( $spark && $spark !== $defaults['guide_spark_color'] ) {
		$rules .= '--guide-spark:' . $spark . ';';
		$rules .= '--guide-spark-strong:' . $spark . ';';
	}

	if ( '' === $rules ) {
		return;
	}

	printf(
		'<style id="guide-customizer">:root{%s}</style>' . "\n",
		esc_html( $rules )
	);
}

/**
 * Bulma's colour variables for one palette entry.
 *
 * Bulma 1.x does not read a colour as a hex. It stores hue, saturation and
 * lightness separately and derives every shade from them — hover, active,
 * light, dark, the readable text colour on top. Overriding `--bulma-primary`
 * with a hex therefore changes almost nothing, because the button is built
 * from `--bulma-primary-h/s/l`, which are still the old values.
 *
 * Setting the three components instead means one chosen colour correctly
 * regenerates the whole family, in both light and dark mode.
 *
 * @param string $name Bulma palette name, e.g. "primary".
 * @param string $hex  #rrggbb.
 */
function guide_brand_vars( string $name, string $hex ): string {
	list( $h, $s, $l ) = guide_hex_to_hsl( $hex );

	return sprintf(
		'--bulma-%1$s-h:%2$sdeg;--bulma-%1$s-s:%3$s%%;--bulma-%1$s-l:%4$s%%;',
		$name,
		$h,
		$s,
		$l
	);
}

/**
 * @return array{0:int,1:int,2:int} Hue in degrees, saturation and lightness as percentages.
 */
function guide_hex_to_hsl( string $hex ): array {
	$hex = ltrim( $hex, '#' );

	if ( 3 === strlen( $hex ) ) {
		$hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
	}

	$r = hexdec( substr( $hex, 0, 2 ) ) / 255;
	$g = hexdec( substr( $hex, 2, 2 ) ) / 255;
	$b = hexdec( substr( $hex, 4, 2 ) ) / 255;

	$max   = max( $r, $g, $b );
	$min   = min( $r, $g, $b );
	$delta = $max - $min;

	$l = ( $max + $min ) / 2;
	$h = 0;
	$s = 0;

	if ( $delta > 0 ) {
		$s = $delta / ( 1 - abs( 2 * $l - 1 ) );

		if ( $max === $r ) {
			$h = fmod( ( $g - $b ) / $delta, 6 );
		} elseif ( $max === $g ) {
			$h = ( ( $b - $r ) / $delta ) + 2;
		} else {
			$h = ( ( $r - $g ) / $delta ) + 4;
		}

		$h *= 60;

		if ( $h < 0 ) {
			$h += 360;
		}
	}

	return array( (int) round( $h ), (int) round( $s * 100 ), (int) round( $l * 100 ) );
}

/**
 * The live-preview script, loaded only inside the Customizer.
 */
add_action( 'customize_preview_init', 'guide_customizer_preview_js' );

function guide_customizer_preview_js() {
	wp_enqueue_script(
		'guide-customizer-preview',
		GUIDE_THEME_URI . '/assets/js/customizer-preview.js',
		array( 'customize-preview' ),
		guide_asset_version( '/assets/js/customizer-preview.js' ),
		true
	);
}
