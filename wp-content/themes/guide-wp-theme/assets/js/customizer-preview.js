/**
 * Live preview for the Customizer.
 *
 * Only the settings whose effect can be applied without re-rendering the page.
 * Anything structural — a section being switched off, for instance — falls back
 * to WordPress's normal preview refresh, which is correct: guessing at a DOM
 * change is how a preview ends up lying about what the page will look like.
 */
( function ( $ ) {
	'use strict';

	if ( ! window.wp || ! wp.customize ) {
		return;
	}

	function text( setting, selector ) {
		wp.customize( setting, function ( value ) {
			value.bind( function ( to ) {
				$( selector ).text( to );
			} );
		} );
	}

	/* ---- Site identity ---- */
	text( 'blogname', '.guide-brand__name, .site-title' );
	text( 'blogdescription', '.site-description' );

	/* ---- Hero ---- */
	text( 'guide_hero_eyebrow', '.guide-hero .guide-chip--spark' );
	text( 'guide_hero_heading', '.guide-hero .guide-display' );
	text( 'guide_hero_lede', '.guide-hero__lede' );
	text( 'guide_hero_cta', '.guide-hero__actions .button.is-primary' );
	text( 'guide_hero_cta_alt', '.guide-hero__actions .button:not(.is-primary)' );

	/* ---- Footer ---- */
	text( 'guide_footer_tagline', '.guide-footer__tagline' );

	/* ---- Colours ----
	 * Bulma 1.x stores each palette entry as hue, saturation and lightness and
	 * derives every shade from them — hover, active, light, dark, and the
	 * readable text colour on top. Overriding the hex alone changes almost
	 * nothing, because the button is built from the components. So the preview
	 * converts to HSL exactly as the PHP does, and one chosen colour
	 * regenerates the whole family in both light and dark mode.
	 */
	function hexToHsl( hex ) {
		hex = String( hex || '' ).replace( '#', '' );

		if ( hex.length === 3 ) {
			hex = hex[ 0 ] + hex[ 0 ] + hex[ 1 ] + hex[ 1 ] + hex[ 2 ] + hex[ 2 ];
		}

		if ( ! /^[0-9a-f]{6}$/i.test( hex ) ) {
			return null;
		}

		var r = parseInt( hex.slice( 0, 2 ), 16 ) / 255;
		var g = parseInt( hex.slice( 2, 4 ), 16 ) / 255;
		var b = parseInt( hex.slice( 4, 6 ), 16 ) / 255;

		var max = Math.max( r, g, b );
		var min = Math.min( r, g, b );
		var d = max - min;
		var l = ( max + min ) / 2;
		var h = 0;
		var s = 0;

		if ( d > 0 ) {
			s = d / ( 1 - Math.abs( 2 * l - 1 ) );

			if ( max === r ) {
				h = ( ( g - b ) / d ) % 6;
			} else if ( max === g ) {
				h = ( b - r ) / d + 2;
			} else {
				h = ( r - g ) / d + 4;
			}

			h *= 60;

			if ( h < 0 ) {
				h += 360;
			}
		}

		return {
			h: Math.round( h ),
			s: Math.round( s * 100 ),
			l: Math.round( l * 100 ),
		};
	}

	function brandVars( name, hex ) {
		var hsl = hexToHsl( hex );

		if ( ! hsl ) {
			return '';
		}

		return '--bulma-' + name + '-h:' + hsl.h + 'deg;' +
			'--bulma-' + name + '-s:' + hsl.s + '%;' +
			'--bulma-' + name + '-l:' + hsl.l + '%;';
	}

	function styleTag() {
		var el = document.getElementById( 'guide-customizer-live' );

		if ( ! el ) {
			el = document.createElement( 'style' );
			el.id = 'guide-customizer-live';
			document.head.appendChild( el );
		}

		return el;
	}

	var live = { primary: null, spark: null };

	function paint() {
		var rules = '';

		if ( live.primary ) {
			rules += brandVars( 'primary', live.primary );
			rules += brandVars( 'link', live.primary );
			rules += '--guide-primary:' + live.primary + ';';
		}

		if ( live.spark ) {
			rules += '--guide-spark:' + live.spark + ';';
			rules += '--guide-spark-strong:' + live.spark + ';';
		}

		styleTag().textContent = rules ? ':root{' + rules + '}' : '';
	}

	wp.customize( 'guide_primary_color', function ( value ) {
		value.bind( function ( to ) {
			live.primary = to;
			paint();
		} );
	} );

	wp.customize( 'guide_spark_color', function ( value ) {
		value.bind( function ( to ) {
			live.spark = to;
			paint();
		} );
	} );
} )( jQuery );
