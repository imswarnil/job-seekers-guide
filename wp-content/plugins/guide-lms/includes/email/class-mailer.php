<?php
/**
 * Branded email.
 *
 * Every message this platform sends goes through here, so they all look like
 * the same product and none of them look like a default WordPress
 * notification — which, to somebody who has been receiving rejection emails
 * for six months, reads as one more machine that does not care.
 *
 * Constraints that shape everything below, none of them negotiable:
 *
 *   · Email clients are stuck around 2005. Tables for layout, inline styles,
 *     no flexbox, no grid, no custom properties, no external stylesheet.
 *     Gmail strips <style> blocks in several contexts, so nothing may depend
 *     on one.
 *   · Colours are hardcoded hex values copied from the theme tokens, because
 *     var(--guide-primary) resolves to nothing in a mail client.
 *   · Every message ships a plain-text alternative. Some people read mail in
 *     a text client, some corporate gateways strip HTML outright, and a
 *     message that arrives as a wall of markup is worse than no message.
 *   · Dark mode is left to the client. Trying to force it produces black text
 *     on a black background in exactly the clients that need it most.
 */

namespace Guide\Email;

defined( 'ABSPATH' ) || exit;

class Mailer {

	// The theme palette, hardcoded — see the note above.
	const INK        = '#101340';
	const INK_SOFT   = '#4a4f6e';
	const PRIMARY    = '#414ba0';
	const SPARK      = '#e9a92a';
	const PAPER      = '#ffffff';
	const CANVAS     = '#f2f3fd';
	const BORDER     = '#e0e2ff';

	/**
	 * Send one branded message.
	 *
	 * @param string               $to      Recipient address.
	 * @param string               $subject Subject line, plain text.
	 * @param array<string, mixed> $parts   heading, body (array of paragraphs
	 *                                      or raw HTML strings), cta, cta_url,
	 *                                      footnote, preheader.
	 */
	public static function send( string $to, string $subject, array $parts ): bool {
		if ( ! is_email( $to ) ) {
			return false;
		}

		$html = self::render( $subject, $parts );
		$text = self::render_text( $parts );

		// Set the HTML content type for this send only. Leaving the filter in
		// place would turn every other plugin's mail — password resets
		// included — into raw markup.
		$as_html      = static function () {
			return 'text/html';
		};
		$alt_body     = static function ( $phpmailer ) use ( $text ) {
			$phpmailer->AltBody = $text; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.NotSnakeCase
		};

		add_filter( 'wp_mail_content_type', $as_html );
		add_action( 'phpmailer_init', $alt_body );

		$sent = wp_mail( $to, $subject, $html, self::headers() );

		remove_filter( 'wp_mail_content_type', $as_html );
		remove_action( 'phpmailer_init', $alt_body );

		return (bool) $sent;
	}

	/** @return string[] */
	private static function headers(): array {
		$headers = array();

		/**
		 * Extra headers on every branded message.
		 *
		 * @param string[] $headers
		 */
		return apply_filters( 'guide_email_headers', $headers );
	}

	// -------------------------------------------------------------------------
	// Rendering
	// -------------------------------------------------------------------------

	/**
	 * @param array<string, mixed> $parts
	 */
	public static function render( string $subject, array $parts ): string {
		$site      = get_bloginfo( 'name' );
		$heading   = (string) ( $parts['heading'] ?? $subject );
		$preheader = (string) ( $parts['preheader'] ?? '' );
		$cta       = (string) ( $parts['cta'] ?? '' );
		$cta_url   = (string) ( $parts['cta_url'] ?? '' );
		$footnote  = (string) ( $parts['footnote'] ?? '' );

		$body = '';

		foreach ( (array) ( $parts['body'] ?? array() ) as $block ) {
			$body .= '<p style="margin:0 0 16px;font-size:15px;line-height:1.65;color:' . self::INK_SOFT . '">'
				. wp_kses_post( $block )
				. '</p>';
		}

		$button = '';

		if ( '' !== $cta && '' !== $cta_url ) {
			// A table, not a styled <a>: Outlook ignores padding on inline
			// elements, so a link-shaped button collapses to bare text there.
			$button = '<table role="presentation" cellpadding="0" cellspacing="0" border="0" style="margin:8px 0 4px">'
				. '<tr><td style="border-radius:8px;background:' . self::PRIMARY . '">'
				. '<a href="' . esc_url( $cta_url ) . '" style="display:inline-block;padding:13px 26px;'
				. 'font-family:-apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif;'
				. 'font-size:15px;font-weight:600;color:#ffffff;text-decoration:none;border-radius:8px">'
				. esc_html( $cta ) . '</a>'
				. '</td></tr></table>';
		}

		$foot = '';

		if ( '' !== $footnote ) {
			$foot = '<p style="margin:20px 0 0;padding-top:18px;border-top:1px solid ' . self::BORDER . ';'
				. 'font-size:13px;line-height:1.6;color:' . self::INK_SOFT . '">' . wp_kses_post( $footnote ) . '</p>';
		}

		return '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">'
			. '<html xmlns="http://www.w3.org/1999/xhtml"><head>'
			. '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />'
			. '<meta name="viewport" content="width=device-width, initial-scale=1" />'
			. '<title>' . esc_html( $subject ) . '</title>'
			. '</head>'
			. '<body style="margin:0;padding:0;background:' . self::CANVAS . ';'
			. '-webkit-text-size-adjust:100%;font-family:-apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif">'

			// The preheader: the grey preview line next to the subject in an
			// inbox. Hidden in the message itself. Left empty, clients fill it
			// with whatever text comes first, which is usually the logo alt.
			. '<div style="display:none;font-size:1px;color:' . self::CANVAS . ';line-height:1px;max-height:0;max-width:0;opacity:0;overflow:hidden">'
			. esc_html( $preheader ) . '</div>'

			. '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background:' . self::CANVAS . '">'
			. '<tr><td align="center" style="padding:28px 14px">'

			. '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width:560px;width:100%">'

			// Masthead.
			. '<tr><td style="padding:0 4px 16px">'
			. '<a href="' . esc_url( home_url( '/' ) ) . '" style="font-size:15px;font-weight:700;color:' . self::INK . ';text-decoration:none">'
			. esc_html( $site ) . '</a>'
			. '</td></tr>'

			// Card.
			. '<tr><td style="background:' . self::PAPER . ';border:1px solid ' . self::BORDER . ';border-radius:14px;padding:30px 28px">'
			. '<h1 style="margin:0 0 14px;font-size:21px;line-height:1.35;font-weight:700;color:' . self::INK . '">'
			. esc_html( $heading ) . '</h1>'
			. $body
			. $button
			. $foot
			. '</td></tr>'

			// Footer.
			. '<tr><td style="padding:18px 6px 0;font-size:12px;line-height:1.6;color:' . self::INK_SOFT . '">'
			. esc_html__( 'You are receiving this because you have an account on', 'guide-lms' ) . ' '
			. '<a href="' . esc_url( home_url( '/' ) ) . '" style="color:' . self::PRIMARY . '">' . esc_html( $site ) . '</a>.'
			. '<br /><a href="' . esc_url( home_url( '/account/' ) ) . '" style="color:' . self::PRIMARY . '">'
			. esc_html__( 'Manage your account', 'guide-lms' ) . '</a>'
			. '</td></tr>'

			. '</table></td></tr></table></body></html>';
	}

	/**
	 * The plain-text alternative.
	 *
	 * @param array<string, mixed> $parts
	 */
	public static function render_text( array $parts ): string {
		$lines = array();

		if ( ! empty( $parts['heading'] ) ) {
			$lines[] = (string) $parts['heading'];
			$lines[] = str_repeat( '=', min( 60, mb_strlen( (string) $parts['heading'] ) ) );
			$lines[] = '';
		}

		foreach ( (array) ( $parts['body'] ?? array() ) as $block ) {
			$lines[] = wp_strip_all_tags( (string) $block );
			$lines[] = '';
		}

		if ( ! empty( $parts['cta_url'] ) ) {
			$lines[] = trim( (string) ( $parts['cta'] ?? '' ) . ': ' . $parts['cta_url'], ': ' );
			$lines[] = '';
		}

		if ( ! empty( $parts['footnote'] ) ) {
			$lines[] = wp_strip_all_tags( (string) $parts['footnote'] );
			$lines[] = '';
		}

		$lines[] = '--';
		$lines[] = get_bloginfo( 'name' ) . ' — ' . home_url( '/' );

		return implode( "\n", $lines );
	}

	/**
	 * Where operational notifications go.
	 *
	 * Separate from the site admin email so alerts can be routed elsewhere
	 * without changing who WordPress itself emails.
	 */
	public static function operator_address(): string {
		$address = (string) get_option( 'jsl_operator_email', '' );

		if ( ! is_email( $address ) ) {
			$address = (string) get_option( 'admin_email' );
		}

		/** @param string $address */
		return (string) apply_filters( 'guide_operator_email', $address );
	}
}
