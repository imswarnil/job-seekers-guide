<?php
/**
 * Generated SVG placeholder art for courses and lessons without a
 * featured image: brand gradient, dotted-path motif, course code, and
 * the title set in the display face. Returned as a data URI so templates
 * can drop it straight into <img src> with zero extra requests.
 */

namespace JSL\Media;

defined( 'ABSPATH' ) || exit;

class Placeholder {

	/**
	 * Card image for a course (16:9).
	 */
	public static function course( int $course_id ): string {
		$title = get_the_title( $course_id );
		$code  = class_exists( 'JSL\\Course_Meta' ) ? \JSL\Course_Meta::get_code( $course_id ) : '';
		$stats = class_exists( 'JSL\\Course_Api' ) ? \JSL\Course_Api::get_stats( $course_id ) : array( 'lessons' => 0 );
		$sub   = $stats['lessons'] ? sprintf( '%d lessons', $stats['lessons'] ) : 'Course';

		return self::data_uri( self::art( $title, $code ?: 'COURSE', $sub, $course_id ) );
	}

	/**
	 * Card image for a lesson (16:9).
	 */
	public static function lesson( int $lesson_id, int $position = 0 ): string {
		$title = get_the_title( $lesson_id );
		$code  = $position ? sprintf( 'LESSON %02d', $position ) : 'LESSON';
		$mins  = (int) get_post_meta( $lesson_id, 'jsl_duration_minutes', true );

		return self::data_uri( self::art( $title, $code, $mins ? $mins . ' min' : '', $lesson_id ) );
	}

	/**
	 * Build the SVG. The seed varies the gradient angle/decoration position
	 * so cards don't look copy-pasted.
	 */
	private static function art( string $title, string $eyebrow, string $sub, int $seed ): string {
		$title   = wp_specialchars_decode( $title, ENT_QUOTES );
		$lines   = self::wrap( $title, 18, 3 );
		$angle   = array( '0 0 1 1', '1 0 0 1', '0 1 1 0' )[ $seed % 3 ];
		list( $x1, $y1, $x2, $y2 ) = explode( ' ', $angle );
		$dot_x   = 470 - ( $seed % 4 ) * 30;

		$tspans = '';
		foreach ( $lines as $i => $line ) {
			$tspans .= '<tspan x="48" dy="' . ( $i ? 44 : 0 ) . '">' . esc_html( $line ) . '</tspan>';
		}

		return '<svg xmlns="http://www.w3.org/2000/svg" width="640" height="360" viewBox="0 0 640 360">'
			. '<defs><linearGradient id="g" x1="' . $x1 . '" y1="' . $y1 . '" x2="' . $x2 . '" y2="' . $y2 . '">'
			. '<stop offset="0" stop-color="#16182e"/><stop offset="1" stop-color="#3d3dab"/></linearGradient></defs>'
			. '<rect width="640" height="360" fill="url(#g)"/>'
			. '<path d="M' . $dot_x . ' 330 C ' . ( $dot_x + 60 ) . ' 250, ' . ( $dot_x - 40 ) . ' 180, ' . ( $dot_x + 70 ) . ' 90" fill="none" stroke="#6a68f2" stroke-width="3" stroke-linecap="round" stroke-dasharray="1 14" opacity="0.9"/>'
			. '<circle cx="' . $dot_x . '" cy="330" r="7" fill="#8fc514"/>'
			. '<circle cx="' . ( $dot_x + 70 ) . '" cy="90" r="9" fill="#6a68f2"/>'
			. '<text x="48" y="84" font-family="JetBrains Mono, Menlo, monospace" font-size="17" letter-spacing="3" fill="#a5a4f8">' . esc_html( strtoupper( $eyebrow ) ) . '</text>'
			. '<text x="48" y="150" font-family="Space Grotesk, Manrope, sans-serif" font-size="36" font-weight="700" fill="#f7f8fc">' . $tspans . '</text>'
			. ( $sub ? '<text x="48" y="316" font-family="Manrope, sans-serif" font-size="16" fill="#a9adc7">' . esc_html( $sub ) . '</text>' : '' )
			. '</svg>';
	}

	/**
	 * Greedy word wrap into at most $max_lines lines of ~$width chars,
	 * ellipsizing the last line if the title is longer.
	 */
	private static function wrap( string $text, int $width, int $max_lines ): array {
		$words = preg_split( '/\s+/', trim( $text ) ) ?: array();
		$lines = array();
		$line  = '';

		foreach ( $words as $word ) {
			$candidate = $line ? $line . ' ' . $word : $word;
			if ( mb_strlen( $candidate ) <= $width || '' === $line ) {
				$line = $candidate;
				continue;
			}
			$lines[] = $line;
			$line    = $word;
			if ( count( $lines ) === $max_lines ) {
				break;
			}
		}
		if ( $line && count( $lines ) < $max_lines ) {
			$lines[] = $line;
		}
		if ( count( $lines ) === $max_lines && $line && end( $lines ) !== $line ) {
			$lines[ $max_lines - 1 ] = mb_substr( $lines[ $max_lines - 1 ], 0, $width - 1 ) . '…';
		}

		return array_map( fn( $l ) => mb_strlen( $l ) > $width + 4 ? mb_substr( $l, 0, $width ) . '…' : $l, $lines );
	}

	private static function data_uri( string $svg ): string {
		return 'data:image/svg+xml;base64,' . base64_encode( $svg );
	}
}
