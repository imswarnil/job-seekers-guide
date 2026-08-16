<?php
/**
 * The messages this platform actually sends.
 *
 * The rule for what earns an email: it must tell somebody something they
 * cannot see by opening the site, or ask them to do something. "You completed
 * a lesson" fails that test — they were there, they know. "Your story is now
 * public" passes, because they submitted it days ago and have no reason to
 * keep checking.
 *
 * The audience matters here more than usual. A lot of these people are in the
 * middle of a job hunt, which means their inbox is currently the most
 * stressful object in their life. Every message that is not useful spends
 * goodwill that the useful ones need.
 *
 * So: no digests, no streak nagging, no "we miss you". Eight messages, each
 * of which someone would be annoyed to have missed.
 */

namespace Guide\Email;

use Guide\Account\Account;
use Guide\Success\Success_Stories;

defined( 'ABSPATH' ) || exit;

class Notifications {

	const OPTION_ENABLED = 'jsl_emails_enabled';

	public static function init() {
		if ( ! self::is_enabled() ) {
			return;
		}

		// Learner journey.
		add_action( 'user_register', array( __CLASS__, 'welcome' ) );
		add_action( 'jsl_subscription_activated', array( __CLASS__, 'subscription_started' ), 10, 2 );
		add_action( 'jsl_subscription_ended', array( __CLASS__, 'subscription_ended' ), 10, 2 );
		add_action( 'jsl_payment_confirmed', array( __CLASS__, 'payment_receipt' ), 10, 2 );

		// Stories.
		add_action( 'jsl_story_submitted', array( __CLASS__, 'story_received' ), 10, 2 );
		add_action( 'transition_post_status', array( __CLASS__, 'story_published' ), 10, 3 );

		// Sponsorship.
		add_action( 'guide_sponsorship_submitted', array( __CLASS__, 'sponsorship_submitted' ), 10, 2 );
		add_action( 'guide_sponsorship_approved', array( __CLASS__, 'sponsorship_approved' ), 10, 2 );
		add_action( 'guide_sponsorship_rejected', array( __CLASS__, 'sponsorship_rejected' ), 10, 2 );
	}

	public static function is_enabled(): bool {
		return (bool) get_option( self::OPTION_ENABLED, true );
	}

	// -------------------------------------------------------------------------
	// Learner journey
	// -------------------------------------------------------------------------

	/**
	 * The one email that sets the tone for everything after it.
	 *
	 * No feature tour, no "5 tips to get started". One instruction — start at
	 * the beginning — because the entire premise of this platform is that
	 * being handed a sequence is the thing that was missing.
	 */
	public static function welcome( $user_id ) {
		$user = get_userdata( (int) $user_id );

		if ( ! $user ) {
			return;
		}

		$name  = self::first_name( $user );
		$paths = get_posts(
			array(
				'post_type'      => 'learning_path',
				'post_status'    => 'publish',
				'posts_per_page' => 1,
				'orderby'        => 'menu_order date',
				'order'          => 'ASC',
			)
		);

		$start = $paths ? get_permalink( $paths[0] ) : home_url( '/' );

		Mailer::send(
			$user->user_email,
			/* translators: %s: site name. */
			sprintf( __( 'Welcome to %s', 'guide-lms' ), get_bloginfo( 'name' ) ),
			array(
				'heading'   => $name
					/* translators: %s: first name. */
					? sprintf( __( 'You are in, %s', 'guide-lms' ), $name )
					: __( 'You are in', 'guide-lms' ),
				'preheader' => __( 'Start at the beginning — the order is the point.', 'guide-lms' ),
				'body'      => array(
					__( 'Everything here is free, and the order it is in is the part that matters. Most people who stall are not short of material — they are short of a sequence, and a hundred open tabs is not one.', 'guide-lms' ),
					__( 'So start at the beginning, even if some of it looks basic. Especially if some of it looks basic.', 'guide-lms' ),
				),
				'cta'       => __( 'Start the first path', 'guide-lms' ),
				'cta_url'   => $start,
				'footnote'  => __( 'Stuck on something? Every lesson has a discussion under it, and a real person reads them.', 'guide-lms' ),
			)
		);
	}

	/**
	 * @param int $user_id
	 * @param string $expires_at
	 */
	public static function subscription_started( $user_id, $expires_at = '' ) {
		$user = get_userdata( (int) $user_id );

		if ( ! $user ) {
			return;
		}

		$body = array(
			__( 'Your subscription is active, and every course on the platform is now open to you. Nothing else to buy — there is only ever one thing to buy here.', 'guide-lms' ),
			__( 'Ads are switched off for you from now on, everywhere on the site.', 'guide-lms' ),
		);

		if ( $expires_at ) {
			$body[] = sprintf(
				/* translators: %s: renewal date. */
				__( 'It renews on %s. You can cancel any time from your account, and access runs to the end of the period you have paid for.', 'guide-lms' ),
				date_i18n( get_option( 'date_format' ), strtotime( (string) $expires_at ) )
			);
		}

		Mailer::send(
			$user->user_email,
			__( 'Your subscription is active', 'guide-lms' ),
			array(
				'heading'   => __( 'Everything is unlocked', 'guide-lms' ),
				'preheader' => __( 'Every course is now open, and ads are off.', 'guide-lms' ),
				'body'      => $body,
				'cta'       => __( 'Go to your account', 'guide-lms' ),
				'cta_url'   => home_url( '/account/' ),
				'footnote'  => __( 'Thank you — subscriptions are what keep the core path free for people who cannot pay for it.', 'guide-lms' ),
			)
		);
	}

	/** @param int $user_id */
	public static function subscription_ended( $user_id, $reason = '' ) {
		$user = get_userdata( (int) $user_id );

		if ( ! $user ) {
			return;
		}

		Mailer::send(
			$user->user_email,
			__( 'Your subscription has ended', 'guide-lms' ),
			array(
				'heading'   => __( 'Your subscription has ended', 'guide-lms' ),
				'preheader' => __( 'The free path is still yours, and so is your progress.', 'guide-lms' ),
				'body'      => array(
					__( 'Members-only courses are closed again, but nothing else has changed: the whole core path — orientation, foundations, one language, projects and the job-search module — is still free and still yours.', 'guide-lms' ),
					__( 'Your progress is kept exactly where it was. If you come back, you carry on rather than starting over.', 'guide-lms' ),
				),
				'cta'       => __( 'Keep learning', 'guide-lms' ),
				'cta_url'   => home_url( '/my-learning/' ),
			)
		);
	}

	/**
	 * @param int $user_id
	 * @param int $payment_id
	 */
	public static function payment_receipt( $user_id, $payment_id = 0 ) {
		$user = get_userdata( (int) $user_id );

		if ( ! $user ) {
			return;
		}

		$url = class_exists( 'Guide\\Account\\Account' ) && $payment_id
			? Account::url( 'receipt/' . (int) $payment_id )
			: home_url( '/account/' );

		Mailer::send(
			$user->user_email,
			__( 'Your receipt', 'guide-lms' ),
			array(
				'heading'   => __( 'Payment received', 'guide-lms' ),
				'preheader' => __( 'A printable receipt is in your account.', 'guide-lms' ),
				'body'      => array(
					__( 'Thank you — your payment has gone through. Your receipt is below and stays available in your account for as long as you have one.', 'guide-lms' ),
				),
				'cta'       => __( 'View receipt', 'guide-lms' ),
				'cta_url'   => $url,
				'footnote'  => __( 'This receipt is our own record. Your card statement and the payment provider’s invoice are the formal documents, and the receipt carries the provider’s reference so anything can be traced.', 'guide-lms' ),
			)
		);
	}

	// -------------------------------------------------------------------------
	// Stories
	// -------------------------------------------------------------------------

	/**
	 * @param int $story_id
	 * @param int $user_id
	 */
	public static function story_received( $story_id, $user_id ) {
		$user = get_userdata( (int) $user_id );

		if ( $user ) {
			Mailer::send(
				$user->user_email,
				__( 'We have your story', 'guide-lms' ),
				array(
					'heading'   => __( 'Thank you — we have your story', 'guide-lms' ),
					'preheader' => __( 'A person reads every one before it goes up.', 'guide-lms' ),
					'body'      => array(
						__( 'A person reads every story before it is published, so this takes a few days rather than a few seconds. You will get one more email when it is live.', 'guide-lms' ),
						__( 'For what it is worth: the stories that help most are the ones that include the rejections. Somebody at number twelve is going to read yours.', 'guide-lms' ),
					),
				)
			);
		}

		// And tell whoever runs the site that the queue has something in it.
		Mailer::send(
			Mailer::operator_address(),
			__( 'A story is waiting for review', 'guide-lms' ),
			array(
				'heading' => __( 'A story is waiting for review', 'guide-lms' ),
				'body'    => array(
					sprintf(
						/* translators: %s: story title. */
						__( '“%s” has been submitted and is pending.', 'guide-lms' ),
						get_the_title( (int) $story_id )
					),
				),
				'cta'     => __( 'Review it', 'guide-lms' ),
				'cta_url' => admin_url( 'post.php?post=' . (int) $story_id . '&action=edit' ),
			)
		);
	}

	/**
	 * Tell the author when their story goes public.
	 *
	 * Hooked to the status transition rather than a custom action so it fires
	 * however the story was approved — the console, the classic editor, or
	 * wp-cli.
	 *
	 * @param string   $new
	 * @param string   $old
	 * @param \WP_Post $post
	 */
	public static function story_published( $new, $old, $post ) {
		if ( ! $post instanceof \WP_Post || Success_Stories::POST_TYPE !== $post->post_type ) {
			return;
		}

		if ( 'publish' !== $new || 'publish' === $old ) {
			return;
		}

		$user = get_userdata( (int) $post->post_author );

		if ( ! $user ) {
			return;
		}

		Mailer::send(
			$user->user_email,
			__( 'Your story is live', 'guide-lms' ),
			array(
				'heading'   => __( 'Your story is on the wall', 'guide-lms' ),
				'preheader' => __( 'Somebody who needs it is going to read it today.', 'guide-lms' ),
				'body'      => array(
					__( 'It is published, and somebody in the middle of the worst part of their search is going to read it today and keep going. That is the entire point of the wall.', 'guide-lms' ),
				),
				'cta'       => __( 'Read it on the site', 'guide-lms' ),
				'cta_url'   => get_permalink( $post ),
			)
		);
	}

	// -------------------------------------------------------------------------
	// Sponsorship
	// -------------------------------------------------------------------------

	/** @param int $campaign_id @param int $user_id */
	public static function sponsorship_submitted( $campaign_id, $user_id = 0 ) {
		$user = get_userdata( (int) $user_id );

		if ( $user ) {
			Mailer::send(
				$user->user_email,
				__( 'We have your sponsorship request', 'guide-lms' ),
				array(
					'heading' => __( 'Your sponsorship is with us', 'guide-lms' ),
					'body'    => array(
						__( 'We review every campaign by hand before it goes live. You will hear back shortly, and you can still edit the creative until it is approved.', 'guide-lms' ),
					),
					'cta'     => __( 'View your campaign', 'guide-lms' ),
					'cta_url' => home_url( '/sponsor/' ),
				)
			);
		}

		Mailer::send(
			Mailer::operator_address(),
			__( 'A sponsorship needs review', 'guide-lms' ),
			array(
				'heading' => __( 'A sponsorship needs review', 'guide-lms' ),
				'body'    => array(
					sprintf(
						/* translators: %s: campaign title. */
						__( '“%s” has been submitted.', 'guide-lms' ),
						get_the_title( (int) $campaign_id )
					),
				),
				'cta'     => __( 'Review it', 'guide-lms' ),
				'cta_url' => admin_url( 'post.php?post=' . (int) $campaign_id . '&action=edit' ),
			)
		);
	}

	/** @param int $campaign_id @param int $user_id */
	public static function sponsorship_approved( $campaign_id, $user_id = 0 ) {
		$user = get_userdata( (int) $user_id );

		if ( ! $user ) {
			return;
		}

		Mailer::send(
			$user->user_email,
			__( 'Your sponsorship is approved', 'guide-lms' ),
			array(
				'heading'  => __( 'Approved — one step left', 'guide-lms' ),
				'body'     => array(
					__( 'Your campaign has been approved. The creative is now locked, which is deliberate: what we reviewed is what runs.', 'guide-lms' ),
					__( 'Complete the payment and it goes live for the months you chose.', 'guide-lms' ),
				),
				'cta'      => __( 'Complete payment', 'guide-lms' ),
				'cta_url'  => home_url( '/sponsor/' ),
				'footnote' => __( 'Need a change after this? Reply to this email and we will sort it out.', 'guide-lms' ),
			)
		);
	}

	/** @param int $campaign_id @param int $user_id */
	public static function sponsorship_rejected( $campaign_id, $user_id = 0 ) {
		$user = get_userdata( (int) $user_id );

		if ( ! $user ) {
			return;
		}

		Mailer::send(
			$user->user_email,
			__( 'About your sponsorship request', 'guide-lms' ),
			array(
				'heading' => __( 'We cannot run this one', 'guide-lms' ),
				'body'    => array(
					__( 'We have not approved this campaign. You have not been charged, and nothing has run.', 'guide-lms' ),
					__( 'This is usually about fit rather than anything being wrong — the audience here is people looking for their first job, and we turn down anything that sells them a shortcut. You are welcome to submit a different creative.', 'guide-lms' ),
				),
				'cta'     => __( 'Submit another', 'guide-lms' ),
				'cta_url' => home_url( '/sponsor/' ),
			)
		);
	}

	// -------------------------------------------------------------------------

	private static function first_name( \WP_User $user ): string {
		$name = trim( (string) $user->first_name );

		if ( '' === $name ) {
			$name = trim( (string) $user->display_name );
		}

		// Only the first word: "Hi Swarnil" reads like a person, "Hi Swarnil
		// Singhai" reads like a database.
		$parts = preg_split( '/\s+/', $name );

		return $parts ? sanitize_text_field( (string) $parts[0] ) : '';
	}
}
