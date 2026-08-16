<?php
/**
 * Reactions and written feedback.
 *
 * One table serves two features that are the same shape underneath:
 *
 *   · Was this lesson/course useful? — up or down, plus an optional note.
 *   · Should we build this? — an upvote on a roadmap item.
 *
 * Both are "one person, one opinion, about one thing", so both get the same
 * uniqueness guarantee: a UNIQUE key on (user_id, object_type, object_id).
 * Voting twice updates your vote rather than adding one, which is the only
 * behaviour that makes a vote count mean anything.
 *
 * Anonymous reactions are deliberately not supported. A vote you cannot
 * attribute is a vote you cannot deduplicate, and a feedback box open to the
 * logged-out internet is a spam queue.
 */

namespace Guide\Community;

defined( 'ABSPATH' ) || exit;

class Feedback {

	const UP   = 'up';
	const DOWN = 'down';

	/** How long between two written notes from the same person. */
	const MESSAGE_COOLDOWN = 60;

	public static function table_name(): string {
		global $wpdb;
		return $wpdb->prefix . 'jsl_reactions';
	}

	public static function init() {
		add_action( 'rest_api_init', array( __CLASS__, 'register_routes' ) );
		add_action( 'deleted_post', array( __CLASS__, 'on_post_deleted' ), 10, 2 );
	}

	public static function create_table() {
		global $wpdb;

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$table   = self::table_name();
		$collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE {$table} (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			user_id BIGINT UNSIGNED NOT NULL,
			object_type VARCHAR(20) NOT NULL,
			object_id BIGINT UNSIGNED NOT NULL,
			sentiment VARCHAR(10) NOT NULL DEFAULT 'up',
			message TEXT NULL,
			status VARCHAR(20) NOT NULL DEFAULT 'new',
			created_at DATETIME NOT NULL,
			updated_at DATETIME NOT NULL,
			PRIMARY KEY  (id),
			UNIQUE KEY one_per_person (user_id, object_type, object_id),
			KEY object (object_type, object_id),
			KEY status (status)
		) {$collate};";

		dbDelta( $sql );
	}

	public static function on_post_deleted( $post_id, $post = null ) {
		global $wpdb;

		$wpdb->delete( self::table_name(), array( 'object_id' => (int) $post_id ) );
	}

	// -------------------------------------------------------------------------
	// Writing
	// -------------------------------------------------------------------------

	/**
	 * Record or update one person's reaction to one thing.
	 *
	 * @param string $sentiment self::UP or self::DOWN.
	 * @return true|\WP_Error
	 */
	public static function react( int $user_id, string $object_type, int $object_id, string $sentiment, string $message = '' ) {
		global $wpdb;

		if ( ! $user_id ) {
			return new \WP_Error( 'guide_signin', __( 'Sign in to leave feedback.', 'guide-lms' ) );
		}

		$sentiment   = self::DOWN === $sentiment ? self::DOWN : self::UP;
		$object_type = sanitize_key( $object_type );
		$message     = trim( wp_strip_all_tags( $message ) );
		$message     = mb_substr( $message, 0, 1000 );

		$existing = self::for_user( $user_id, $object_type, $object_id );

		// Rate limit written notes only. Flipping a vote costs nothing; a
		// stream of messages is how a feedback queue becomes unusable.
		if ( '' !== $message && $existing && $existing['message'] ) {
			$since = time() - strtotime( (string) $existing['updated_at'] . ' UTC' );

			if ( $since < self::MESSAGE_COOLDOWN ) {
				return new \WP_Error( 'guide_slow_down', __( 'Give it a moment before editing that again.', 'guide-lms' ) );
			}
		}

		$now = current_time( 'mysql', true );

		if ( $existing ) {
			$data = array(
				'sentiment'  => $sentiment,
				'updated_at' => $now,
			);

			// An empty message never wipes one already written — the up/down
			// buttons and the note field post separately.
			if ( '' !== $message ) {
				$data['message'] = $message;
				$data['status']  = 'new';
			}

			$wpdb->update( self::table_name(), $data, array( 'id' => (int) $existing['id'] ) );

			return true;
		}

		$wpdb->insert(
			self::table_name(),
			array(
				'user_id'     => $user_id,
				'object_type' => $object_type,
				'object_id'   => $object_id,
				'sentiment'   => $sentiment,
				'message'     => '' !== $message ? $message : null,
				'status'      => 'new',
				'created_at'  => $now,
				'updated_at'  => $now,
			)
		);

		return true;
	}

	/** Withdraw a reaction entirely. */
	public static function withdraw( int $user_id, string $object_type, int $object_id ): bool {
		global $wpdb;

		return false !== $wpdb->delete(
			self::table_name(),
			array(
				'user_id'     => $user_id,
				'object_type' => sanitize_key( $object_type ),
				'object_id'   => $object_id,
			)
		);
	}

	// -------------------------------------------------------------------------
	// Reading
	// -------------------------------------------------------------------------

	/** @return array<string,mixed>|null */
	public static function for_user( int $user_id, string $object_type, int $object_id ) {
		global $wpdb;

		if ( ! $user_id ) {
			return null;
		}

		$row = $wpdb->get_row(
			$wpdb->prepare(
				'SELECT * FROM ' . self::table_name() . ' WHERE user_id = %d AND object_type = %s AND object_id = %d',
				$user_id,
				sanitize_key( $object_type ),
				$object_id
			),
			ARRAY_A
		);

		return $row ? $row : null;
	}

	/**
	 * @return array{up:int, down:int}
	 */
	public static function tally( string $object_type, int $object_id ): array {
		global $wpdb;

		$rows = $wpdb->get_results(
			$wpdb->prepare(
				'SELECT sentiment, COUNT(*) AS total FROM ' . self::table_name() . '
				  WHERE object_type = %s AND object_id = %d GROUP BY sentiment',
				sanitize_key( $object_type ),
				$object_id
			),
			ARRAY_A
		);

		$out = array(
			'up'   => 0,
			'down' => 0,
		);

		foreach ( (array) $rows as $row ) {
			$out[ $row['sentiment'] ] = (int) $row['total'];
		}

		return $out;
	}

	/**
	 * Written notes, for the admin queue.
	 *
	 * @return array<int, array<string,mixed>>
	 */
	public static function messages( string $status = '', int $limit = 100 ): array {
		global $wpdb;

		$sql  = 'SELECT * FROM ' . self::table_name() . ' WHERE message IS NOT NULL AND message <> ""';
		$args = array();

		if ( $status ) {
			$sql   .= ' AND status = %s';
			$args[] = sanitize_key( $status );
		}

		$sql   .= ' ORDER BY created_at DESC LIMIT %d';
		$args[] = max( 1, min( 500, $limit ) );

		$rows = $wpdb->get_results( $wpdb->prepare( $sql, $args ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL

		return is_array( $rows ) ? $rows : array();
	}

	public static function unread_count(): int {
		global $wpdb;

		return (int) $wpdb->get_var(
			'SELECT COUNT(*) FROM ' . self::table_name() . ' WHERE status = "new" AND message IS NOT NULL AND message <> ""'
		);
	}

	public static function set_status( int $id, string $status ): bool {
		global $wpdb;

		$status = in_array( $status, array( 'new', 'read', 'actioned' ), true ) ? $status : 'read';

		return false !== $wpdb->update( self::table_name(), array( 'status' => $status ), array( 'id' => $id ) );
	}

	// -------------------------------------------------------------------------
	// REST
	// -------------------------------------------------------------------------

	public static function register_routes() {
		register_rest_route(
			'guide/v1',
			'/feedback',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'handle_feedback' ),
				'permission_callback' => 'is_user_logged_in',
				'args'                => array(
					'object_type' => array( 'required' => true, 'type' => 'string' ),
					'object_id'   => array( 'required' => true, 'type' => 'integer' ),
					'sentiment'   => array( 'type' => 'string' ),
					'message'     => array( 'type' => 'string' ),
				),
			)
		);

		register_rest_route(
			'guide/v1',
			'/roadmap/(?P<id>\d+)/vote',
			array(
				array(
					'methods'             => 'POST',
					'callback'            => array( __CLASS__, 'handle_vote' ),
					'permission_callback' => 'is_user_logged_in',
				),
				array(
					'methods'             => 'DELETE',
					'callback'            => array( __CLASS__, 'handle_unvote' ),
					'permission_callback' => 'is_user_logged_in',
				),
			)
		);

		register_rest_route(
			'guide/v1',
			'/roadmap/suggest',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'handle_suggestion' ),
				'permission_callback' => 'is_user_logged_in',
				'args'                => array(
					'title' => array( 'required' => true, 'type' => 'string' ),
				),
			)
		);
	}

	public static function handle_feedback( \WP_REST_Request $request ) {
		$object_type = sanitize_key( (string) $request->get_param( 'object_type' ) );
		$object_id   = (int) $request->get_param( 'object_id' );

		if ( ! in_array( $object_type, array( 'course', 'lesson', 'help_article', 'company' ), true ) ) {
			return new \WP_REST_Response( array( 'error' => __( 'Unknown item.', 'guide-lms' ) ), 400 );
		}

		if ( ! get_post( $object_id ) ) {
			return new \WP_REST_Response( array( 'error' => __( 'That item does not exist.', 'guide-lms' ) ), 404 );
		}

		$result = self::react(
			get_current_user_id(),
			$object_type,
			$object_id,
			(string) $request->get_param( 'sentiment' ),
			(string) $request->get_param( 'message' )
		);

		if ( is_wp_error( $result ) ) {
			return new \WP_REST_Response( array( 'error' => $result->get_error_message() ), 429 );
		}

		return new \WP_REST_Response(
			array(
				'saved' => true,
				'tally' => self::tally( $object_type, $object_id ),
			),
			200
		);
	}

	public static function handle_vote( \WP_REST_Request $request ) {
		$id = (int) $request['id'];

		if ( Community_Types::ROADMAP !== get_post_type( $id ) ) {
			return new \WP_REST_Response( array( 'error' => __( 'Not a roadmap item.', 'guide-lms' ) ), 404 );
		}

		self::react( get_current_user_id(), Community_Types::ROADMAP, $id, self::UP );

		return new \WP_REST_Response(
			array(
				'voted' => true,
				'votes' => self::sync_votes( $id ),
			),
			200
		);
	}

	public static function handle_unvote( \WP_REST_Request $request ) {
		$id = (int) $request['id'];

		self::withdraw( get_current_user_id(), Community_Types::ROADMAP, $id );

		return new \WP_REST_Response(
			array(
				'voted' => false,
				'votes' => self::sync_votes( $id ),
			),
			200
		);
	}

	/** Recompute and store the denormalised vote total. */
	public static function sync_votes( int $post_id ): int {
		$votes = self::tally( Community_Types::ROADMAP, $post_id )['up'];

		update_post_meta( $post_id, 'jsl_roadmap_votes', $votes );

		return $votes;
	}

	/**
	 * A learner suggests something.
	 *
	 * Always created as a pending draft: the roadmap is a public statement of
	 * intent, and anything anyone types should not appear on it unreviewed.
	 */
	public static function handle_suggestion( \WP_REST_Request $request ) {
		$title = trim( wp_strip_all_tags( (string) $request->get_param( 'title' ) ) );
		$body  = trim( wp_strip_all_tags( (string) $request->get_param( 'body' ) ) );

		if ( mb_strlen( $title ) < 6 ) {
			return new \WP_REST_Response( array( 'error' => __( 'Give it a slightly longer title.', 'guide-lms' ) ), 400 );
		}

		$user_id = get_current_user_id();

		// One pending suggestion at a time, so the queue cannot be flooded.
		$pending = get_posts(
			array(
				'post_type'      => Community_Types::ROADMAP,
				'post_status'    => 'pending',
				'author'         => $user_id,
				'posts_per_page' => 1,
				'fields'         => 'ids',
			)
		);

		if ( $pending ) {
			return new \WP_REST_Response(
				array( 'error' => __( 'You already have a suggestion waiting to be reviewed.', 'guide-lms' ) ),
				429
			);
		}

		$post_id = wp_insert_post(
			array(
				'post_type'    => Community_Types::ROADMAP,
				'post_status'  => 'pending',
				'post_title'   => mb_substr( $title, 0, 140 ),
				'post_content' => mb_substr( $body, 0, 2000 ),
				'post_author'  => $user_id,
			),
			true
		);

		if ( is_wp_error( $post_id ) ) {
			return new \WP_REST_Response( array( 'error' => $post_id->get_error_message() ), 400 );
		}

		update_post_meta( $post_id, 'jsl_roadmap_status', 'suggested' );

		// The suggester's own vote is implied — they clearly want it.
		self::react( $user_id, Community_Types::ROADMAP, (int) $post_id, self::UP );
		self::sync_votes( (int) $post_id );

		return new \WP_REST_Response( array( 'submitted' => true ), 201 );
	}
}
