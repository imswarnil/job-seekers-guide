<?php
/**
 * Learner discussion.
 *
 * Comments used to be dead site-wide. They are now open on exactly the places
 * where a learner being stuck is worth answering in public — lessons, help
 * articles, company guides and stories — and closed everywhere else.
 *
 * The loneliest part of teaching yourself is being stuck at 11pm with nobody
 * to ask. That is the entire reason this exists.
 *
 * It is deliberately narrow, because a comment box is also the largest
 * user-generated-content surface on a site and therefore the largest liability:
 *
 *   · Signed-in only. An open box is a spam queue, and there is no honest way
 *     to moderate anonymous volume with one person running the site.
 *   · No HTML at all — not a filtered allow-list, none. Comments are plain
 *     text, linkified on output. A stripped tag cannot become an XSS bug.
 *   · Rate limited per person, and long enough to be a question rather than
 *     "same".
 *   · One level of replies. Deeper nesting is unreadable on a phone, which is
 *     what most of this audience is using.
 *   · Staff replies are badged, so an official answer is distinguishable from
 *     a confident guess.
 */

namespace Guide\Community;

defined( 'ABSPATH' ) || exit;

class Discussion {

	/** Where discussion is allowed. */
	const OPEN_ON = array( 'lesson', 'help_article', 'company', 'success_story' );

	const OPTION_ENABLED = 'jsl_discussion_enabled';

	/** Seconds between comments from one person. */
	const COOLDOWN = 45;

	const MIN_LENGTH = 8;
	const MAX_LENGTH = 3000;

	public static function init() {
		add_filter( 'comments_open', array( __CLASS__, 'only_where_allowed' ), 20, 2 );

		// Content rules.
		add_filter( 'preprocess_comment', array( __CLASS__, 'guard_submission' ) );
		add_filter( 'pre_comment_content', array( __CLASS__, 'strip_all_markup' ), 5 );
		add_filter( 'comment_text', array( __CLASS__, 'render_text' ), 5 );

		// Anything from someone who has never had a comment approved waits.
		add_filter( 'pre_comment_approved', array( __CLASS__, 'moderate_first_post' ), 10, 2 );

		// Comment authors are always real accounts here, so drop the fields
		// that only exist for anonymous commenting.
		add_filter( 'comment_form_default_fields', '__return_empty_array' );

		add_action( 'init', array( __CLASS__, 'support' ), 100 );
	}

	public static function is_enabled(): bool {
		return (bool) get_option( self::OPTION_ENABLED, true );
	}

	/** Add comment support to the post types that should have it, remove it elsewhere. */
	public static function support() {
		foreach ( get_post_types() as $post_type ) {
			if ( self::is_enabled() && in_array( $post_type, self::OPEN_ON, true ) ) {
				add_post_type_support( $post_type, 'comments' );
			} else {
				remove_post_type_support( $post_type, 'comments' );
			}

			remove_post_type_support( $post_type, 'trackbacks' );
		}
	}

	/**
	 * @param bool $open
	 * @param int  $post_id
	 */
	public static function only_where_allowed( $open, $post_id ) {
		if ( ! self::is_enabled() ) {
			return false;
		}

		if ( ! in_array( get_post_type( $post_id ), self::OPEN_ON, true ) ) {
			return false;
		}

		// A locked lesson's discussion is part of the lesson.
		if ( class_exists( 'Guide\\Access\\Access' ) && 'lesson' === get_post_type( $post_id ) ) {
			if ( \Guide\Access\Access::is_locked( (int) $post_id ) ) {
				return false;
			}
		}

		// Deliberately ignore the stored comment_status.
		//
		// Every post on this site was created while comments were switched off
		// site-wide, so they all carry comment_status = 'closed' — and anything
		// the console creates would inherit the same default. Honouring it would
		// mean discussion never opened on a single existing lesson, and the
		// per-post toggle is not where this policy belongs anyway: it is by post
		// type, with one global switch in LMS → Settings.
		return true;
	}

	/**
	 * Reject anything that should never reach the database.
	 *
	 * @param array<string,mixed> $data
	 * @return array<string,mixed>
	 */
	public static function guard_submission( $data ) {
		if ( ! is_user_logged_in() ) {
			wp_die(
				esc_html__( 'Please sign in to join the discussion.', 'guide-lms' ),
				esc_html__( 'Sign in required', 'guide-lms' ),
				array( 'response' => 401, 'back_link' => true )
			);
		}

		$post_id = (int) ( $data['comment_post_ID'] ?? 0 );

		if ( ! comments_open( $post_id ) ) {
			wp_die(
				esc_html__( 'Discussion is closed here.', 'guide-lms' ),
				esc_html__( 'Closed', 'guide-lms' ),
				array( 'response' => 403, 'back_link' => true )
			);
		}

		$content = trim( wp_strip_all_tags( (string) ( $data['comment_content'] ?? '' ) ) );

		if ( mb_strlen( $content ) < self::MIN_LENGTH ) {
			wp_die(
				esc_html__( 'That is a little short — say enough that somebody can actually help.', 'guide-lms' ),
				esc_html__( 'Too short', 'guide-lms' ),
				array( 'response' => 400, 'back_link' => true )
			);
		}

		if ( mb_strlen( $content ) > self::MAX_LENGTH ) {
			$data['comment_content'] = mb_substr( $content, 0, self::MAX_LENGTH );
		}

		// One level of replies only.
		$parent = (int) ( $data['comment_parent'] ?? 0 );

		if ( $parent ) {
			$parent_comment = get_comment( $parent );

			if ( $parent_comment && (int) $parent_comment->comment_parent ) {
				$data['comment_parent'] = (int) $parent_comment->comment_parent;
			}
		}

		self::enforce_cooldown();

		return $data;
	}

	/**
	 * Stop one person posting in a burst.
	 *
	 * Checked against the database rather than a transient: a transient can be
	 * dropped by an object cache under memory pressure, and a rate limit that
	 * quietly stops applying is not a rate limit.
	 */
	private static function enforce_cooldown() {
		global $wpdb;

		$user_id = get_current_user_id();

		$last = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT comment_date_gmt FROM {$wpdb->comments}
				  WHERE user_id = %d ORDER BY comment_date_gmt DESC LIMIT 1",
				$user_id
			)
		);

		if ( ! $last ) {
			return;
		}

		if ( ( time() - strtotime( $last . ' UTC' ) ) < self::COOLDOWN ) {
			wp_die(
				esc_html__( 'You just posted — give it a moment before the next one.', 'guide-lms' ),
				esc_html__( 'Slow down', 'guide-lms' ),
				array( 'response' => 429, 'back_link' => true )
			);
		}
	}

	/**
	 * Strip every tag before storage.
	 *
	 * Not wp_kses with an allow-list — none at all. An allow-list is a standing
	 * bet that no tag or attribute combination in it can ever be abused, and
	 * nothing a learner needs to ask a question requires markup.
	 *
	 * @param string $content
	 */
	public static function strip_all_markup( $content ) {
		return wp_strip_all_tags( (string) $content, false );
	}

	/**
	 * Render stored plain text: escape, linkify, keep paragraph breaks.
	 *
	 * @param string $text
	 */
	public static function render_text( $text ) {
		// Comments elsewhere (if any legacy ones exist) are left alone.
		$comment = get_comment();

		if ( ! $comment || ! in_array( get_post_type( (int) $comment->comment_post_ID ), self::OPEN_ON, true ) ) {
			return $text;
		}

		$safe = esc_html( wp_strip_all_tags( (string) $comment->comment_content ) );
		$safe = make_clickable( $safe );

		// make_clickable builds plain <a href>; add the attributes an untrusted
		// outbound link needs.
		$safe = str_replace( '<a href=', '<a rel="nofollow ugc noopener" target="_blank" href=', $safe );

		return wpautop( $safe );
	}

	/**
	 * Hold a person's first comment for review, then trust them.
	 *
	 * The standard WordPress behaviour, kept explicitly: it stops a fresh
	 * account posting links immediately, without making every regular
	 * contributor wait forever.
	 *
	 * @param int|string          $approved
	 * @param array<string,mixed> $data
	 */
	public static function moderate_first_post( $approved, $data ) {
		if ( 'spam' === $approved || is_wp_error( $approved ) ) {
			return $approved;
		}

		$user_id = (int) ( $data['user_id'] ?? 0 );

		if ( ! $user_id ) {
			return 0;
		}

		// Staff are not moderated.
		if ( user_can( $user_id, 'edit_posts' ) ) {
			return 1;
		}

		$previous = get_comments(
			array(
				'user_id' => $user_id,
				'status'  => 'approve',
				'count'   => true,
			)
		);

		return $previous > 0 ? 1 : 0;
	}

	/** True when this comment's author is staff — badged in the template. */
	public static function is_staff_comment( $comment ): bool {
		$user_id = (int) ( is_object( $comment ) ? $comment->user_id : 0 );

		return $user_id && user_can( $user_id, 'edit_posts' );
	}
}
