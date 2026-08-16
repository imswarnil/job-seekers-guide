<?php
/**
 * Success stories — the "Wall of Success".
 *
 * A learner who lands a job can write up how it went. Stories are submitted
 * from the front end and land as *pending*: nothing a learner writes appears
 * publicly until someone with edit rights approves it. That matters more here
 * than for most user-generated content, because these are claims about real
 * employers attached to real names.
 *
 * Stories are authored by the learner (post_author), so a person can edit
 * their own draft, and the moderation queue lives in the LMS console.
 */

namespace Guide\Success;

defined( 'ABSPATH' ) || exit;

class Success_Stories {

	const POST_TYPE = 'success_story';

	const OPTION_ENABLED = 'jsl_stories_enabled';

	/** Meta keys, all plain scalars. */
	const META_COMPANY  = 'jsl_story_company';
	const META_ROLE     = 'jsl_story_role';
	const META_PREVIOUS = 'jsl_story_previous';
	const META_WEEKS    = 'jsl_story_weeks';
	const META_LINKEDIN = 'jsl_story_linkedin';
	const META_SALARY   = 'jsl_story_salary';

	/** Archive filter parameters. See filter_archive() for why they are prefixed. */
	const QUERY_COMPANY = 'story_company';
	const QUERY_ROLE    = 'story_role';
	const QUERY_BAND    = 'story_band';

	/**
	 * Salary bands, in lakhs per annum.
	 *
	 * Bands rather than exact figures, and optional. Three reasons, in order of
	 * how much they matter:
	 *
	 *   · A named person, their employer and their exact salary together are
	 *     enough to identify and embarrass someone. A band is not.
	 *   · Nobody at the bottom of the range wants to publish the number, and
	 *     those are exactly the stories this site needs most. The founder's own
	 *     first job was ₹1.8 LPA; a wall where everybody posts 12 LPA would
	 *     have made him feel worse, not better.
	 *   · A range is more honest anyway. "₹4.2 LPA" implies a precision that
	 *     variable pay and joining bonuses do not survive.
	 *
	 * Narrow at the bottom because that is where this audience actually lands
	 * and a lakh is a big difference there; wider at the top where it is not.
	 */
	const SALARY_BANDS = array(
		'1-2'   => '₹1–2 LPA',
		'2-3'   => '₹2–3 LPA',
		'3-4'   => '₹3–4 LPA',
		'4-5'   => '₹4–5 LPA',
		'5-6'   => '₹5–6 LPA',
		'6-8'   => '₹6–8 LPA',
		'8-10'  => '₹8–10 LPA',
		'10-12' => '₹10–12 LPA',
		'12-15' => '₹12–15 LPA',
		'15-20' => '₹15–20 LPA',
		'20-25' => '₹20–25 LPA',
		'25+'   => '₹25 LPA and above',
	);

	public static function init() {
		add_action( 'init', array( __CLASS__, 'register' ) );
		add_action( 'rest_api_init', array( __CLASS__, 'register_routes' ) );
		add_action( 'pre_get_posts', array( __CLASS__, 'filter_archive' ) );
	}

	public static function is_enabled(): bool {
		return (bool) get_option( self::OPTION_ENABLED, true );
	}

	public static function archive_url(): string {
		return (string) get_post_type_archive_link( self::POST_TYPE );
	}

	/* ---------------------------------------------------------------
	 * Post type + meta
	 * --------------------------------------------------------------- */

	public static function register() {
		register_post_type(
			self::POST_TYPE,
			array(
				'labels'             => array(
					'name'          => __( 'Success Stories', 'guide-lms' ),
					'singular_name' => __( 'Success Story', 'guide-lms' ),
				),
				'public'             => true,
				'publicly_queryable' => true,
				'show_ui'            => true,
				'show_in_menu'       => false,
				'show_in_nav_menus'  => true,
				'show_in_rest'       => true,
				'rest_base'          => 'success-stories',
				'has_archive'        => true,
				'rewrite'            => array( 'slug' => 'success', 'with_front' => false ),
				'menu_icon'          => 'dashicons-awards',
				'supports'           => array( 'title', 'editor', 'excerpt', 'thumbnail', 'author', 'custom-fields' ),
			)
		);

		$auth = function () {
			return current_user_can( 'edit_posts' );
		};

		$fields = array(
			self::META_COMPANY  => 'sanitize_text_field',
			self::META_ROLE     => 'sanitize_text_field',
			self::META_PREVIOUS => 'sanitize_text_field',
			self::META_LINKEDIN => 'esc_url_raw',
		);

		foreach ( $fields as $key => $sanitizer ) {
			register_post_meta(
				self::POST_TYPE,
				$key,
				array(
					'type'              => 'string',
					'single'            => true,
					'show_in_rest'      => true,
					'sanitize_callback' => $sanitizer,
					'auth_callback'     => $auth,
				)
			);
		}

		register_post_meta(
			self::POST_TYPE,
			self::META_WEEKS,
			array(
				'type'              => 'integer',
				'single'            => true,
				'show_in_rest'      => true,
				'sanitize_callback' => 'absint',
				'auth_callback'     => $auth,
			)
		);
	}

	/* ---------------------------------------------------------------
	 * Submission
	 * --------------------------------------------------------------- */

	public static function register_routes() {
		register_rest_route(
			'guide/v1',
			'/stories',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'submit' ),
				'permission_callback' => 'is_user_logged_in',
				'args'                => array(
					'title'   => array( 'required' => true, 'type' => 'string' ),
					'story'   => array( 'required' => true, 'type' => 'string' ),
					'company' => array( 'required' => true, 'type' => 'string' ),
					'role'    => array( 'required' => true, 'type' => 'string' ),
					'salary'  => array( 'required' => false, 'type' => 'string' ),
				),
			)
		);

		// Moderation.
		register_rest_route(
			'guide/v1',
			'/stories/(?P<id>\d+)/status',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'set_status' ),
				'permission_callback' => function ( \WP_REST_Request $request ) {
					return current_user_can( 'edit_post', (int) $request['id'] );
				},
			)
		);

		register_rest_route(
			'guide/v1',
			'/stories',
			array(
				'methods'             => 'GET',
				'callback'            => array( __CLASS__, 'list_for_console' ),
				'permission_callback' => function () {
					return current_user_can( 'edit_posts' );
				},
			)
		);
	}

	/**
	 * Accept a story from a learner. Always pending, never published —
	 * approval is a human decision.
	 */
	public static function submit( \WP_REST_Request $request ) {
		if ( ! self::is_enabled() ) {
			return new \WP_REST_Response( array( 'error' => __( 'Story submissions are closed.', 'guide-lms' ) ), 403 );
		}

		$user_id = get_current_user_id();

		// One pending story at a time, so a submit button can't be used to
		// flood the moderation queue.
		$pending = get_posts(
			array(
				'post_type'      => self::POST_TYPE,
				'post_status'    => 'pending',
				'author'         => $user_id,
				'posts_per_page' => 1,
				'fields'         => 'ids',
			)
		);

		if ( ! empty( $pending ) ) {
			return new \WP_REST_Response(
				array( 'error' => __( 'You already have a story waiting to be reviewed.', 'guide-lms' ) ),
				429
			);
		}

		$story_id = wp_insert_post(
			array(
				'post_type'    => self::POST_TYPE,
				'post_status'  => 'pending',
				'post_author'  => $user_id,
				'post_title'   => sanitize_text_field( (string) $request->get_param( 'title' ) ),
				// wp_kses_post, not raw: this is user-submitted HTML that an
				// editor will read in the admin before it is ever public.
				'post_content' => wp_kses_post( (string) $request->get_param( 'story' ) ),
			),
			true
		);

		if ( is_wp_error( $story_id ) ) {
			return new \WP_REST_Response( array( 'error' => $story_id->get_error_message() ), 400 );
		}

		update_post_meta( $story_id, self::META_COMPANY, sanitize_text_field( (string) $request->get_param( 'company' ) ) );
		update_post_meta( $story_id, self::META_ROLE, sanitize_text_field( (string) $request->get_param( 'role' ) ) );
		update_post_meta( $story_id, self::META_PREVIOUS, sanitize_text_field( (string) $request->get_param( 'previous' ) ) );
		update_post_meta( $story_id, self::META_WEEKS, absint( $request->get_param( 'weeks' ) ) );
		update_post_meta( $story_id, self::META_LINKEDIN, esc_url_raw( (string) $request->get_param( 'linkedin' ) ) );
		update_post_meta( $story_id, self::META_SALARY, self::sanitize_band( (string) $request->get_param( 'salary' ) ) );

		do_action( 'jsl_story_submitted', $story_id, $user_id );

		return new \WP_REST_Response( array( 'submitted' => true, 'id' => (int) $story_id ), 201 );
	}

	public static function set_status( \WP_REST_Request $request ) {
		$story_id = (int) $request['id'];
		$status   = (string) $request->get_param( 'status' );

		if ( self::POST_TYPE !== get_post_type( $story_id ) ) {
			return new \WP_REST_Response( array( 'error' => 'Not a story.' ), 404 );
		}

		if ( ! in_array( $status, array( 'publish', 'pending', 'draft', 'trash' ), true ) ) {
			return new \WP_REST_Response( array( 'error' => 'Unknown status.' ), 400 );
		}

		if ( 'trash' === $status ) {
			wp_trash_post( $story_id );
		} else {
			wp_update_post( array( 'ID' => $story_id, 'post_status' => $status ) );
		}

		return new \WP_REST_Response( array( 'ok' => true, 'status' => $status ), 200 );
	}

	/**
	 * Stories for the console's moderation queue, pending first.
	 */
	public static function list_for_console( \WP_REST_Request $request ) {
		$stories = get_posts(
			array(
				'post_type'      => self::POST_TYPE,
				'post_status'    => array( 'pending', 'publish', 'draft' ),
				'posts_per_page' => 100,
				'orderby'        => 'date',
				'order'          => 'DESC',
			)
		);

		$out = array();

		foreach ( $stories as $story ) {
			$author = get_userdata( (int) $story->post_author );

			$out[] = array(
				'id'        => (int) $story->ID,
				'title'     => $story->post_title,
				'status'    => $story->post_status,
				'author'    => $author ? $author->display_name : __( 'Unknown', 'guide-lms' ),
				'company'   => (string) get_post_meta( $story->ID, self::META_COMPANY, true ),
				'role'      => (string) get_post_meta( $story->ID, self::META_ROLE, true ),
				'excerpt'   => self::plain_excerpt( $story->post_content, 40 ),
				'permalink' => (string) get_permalink( $story ),
				'date'      => get_the_date( 'M j, Y', $story ),
			);
		}

		// Pending first — the queue is the point of this screen.
		usort(
			$out,
			function ( $a, $b ) {
				$rank = array( 'pending' => 0, 'draft' => 1, 'publish' => 2 );
				return ( $rank[ $a['status'] ] ?? 3 ) <=> ( $rank[ $b['status'] ] ?? 3 );
			}
		);

		return new \WP_REST_Response( array( 'stories' => $out ), 200 );
	}

	/**
	 * A plain-text preview of a story.
	 *
	 * Two details matter: block tags become spaces first, or "…applications."
	 * and "What actually…" collide into one word; and the ellipsis is the
	 * literal character rather than &hellip;, because this string is JSON and
	 * the client escapes it again.
	 */
	private static function plain_excerpt( string $content, int $words ): string {
		$spaced = preg_replace( '#</(p|div|li|h[1-6]|blockquote)>#i', ' ', $content );

		return wp_trim_words( wp_strip_all_tags( (string) $spaced ), $words, '…' );
	}

	/* ---------------------------------------------------------------
	 * Read helpers for templates
	 * --------------------------------------------------------------- */

	/**
	 * @return array{company:string, role:string, previous:string, weeks:int, linkedin:string}
	 */
	public static function details( int $story_id ): array {
		return array(
			'company'  => (string) get_post_meta( $story_id, self::META_COMPANY, true ),
			'role'     => (string) get_post_meta( $story_id, self::META_ROLE, true ),
			'previous' => (string) get_post_meta( $story_id, self::META_PREVIOUS, true ),
			'weeks'    => (int) get_post_meta( $story_id, self::META_WEEKS, true ),
			'linkedin' => (string) get_post_meta( $story_id, self::META_LINKEDIN, true ),
			'salary'   => self::salary_band( $story_id ),
			'salary_label' => self::salary_label( $story_id ),
		);
	}

	public static function published_count(): int {
		$counts = wp_count_posts( self::POST_TYPE );
		return (int) ( $counts->publish ?? 0 );
	}

	/**
	 * Whether this user already has a story in the system, and its state —
	 * so the front end can show "waiting for review" instead of the form.
	 */
	public static function user_story_status( int $user_id ): string {
		if ( ! $user_id ) {
			return '';
		}

		$existing = get_posts(
			array(
				'post_type'      => self::POST_TYPE,
				'post_status'    => array( 'pending', 'publish', 'draft' ),
				'author'         => $user_id,
				'posts_per_page' => 1,
			)
		);

		return $existing ? $existing[0]->post_status : '';
	}

	/**
	 * Keep only a band we actually offer.
	 *
	 * Anything else becomes an empty string, which reads as "preferred not to
	 * say" everywhere it is displayed — the safe default for a field nobody is
	 * obliged to answer.
	 */
	public static function sanitize_band( string $value ): string {
		$value = trim( $value );

		return isset( self::SALARY_BANDS[ $value ] ) ? $value : '';
	}

	public static function salary_band( int $story_id ): string {
		return self::sanitize_band( (string) get_post_meta( $story_id, self::META_SALARY, true ) );
	}

	public static function salary_label( int $story_id ): string {
		$band = self::salary_band( $story_id );

		return $band ? self::SALARY_BANDS[ $band ] : '';
	}

	/**
	 * The distinct companies and roles that published stories mention, for the
	 * archive filters.
	 *
	 * Built from the stories themselves rather than a taxonomy: these are free
	 * text typed by learners, and a filter listing options that match nothing
	 * is worse than no filter.
	 *
	 * @return array{companies: string[], roles: string[], bands: string[]}
	 */
	public static function filter_options(): array {
		global $wpdb;

		$out = array(
			'companies' => array(),
			'roles'     => array(),
			'bands'     => array(),
		);

		$rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT m.meta_key, m.meta_value
				   FROM {$wpdb->postmeta} m
				   INNER JOIN {$wpdb->posts} p ON p.ID = m.post_id
				  WHERE p.post_type = %s
				    AND p.post_status = 'publish'
				    AND m.meta_key IN ( %s, %s, %s )
				    AND m.meta_value <> ''",
				self::POST_TYPE,
				self::META_COMPANY,
				self::META_ROLE,
				self::META_SALARY
			)
		);

		foreach ( (array) $rows as $row ) {
			if ( self::META_COMPANY === $row->meta_key ) {
				$out['companies'][] = $row->meta_value;
			} elseif ( self::META_ROLE === $row->meta_key ) {
				$out['roles'][] = $row->meta_value;
			} else {
				$out['bands'][] = $row->meta_value;
			}
		}

		$out['companies'] = array_values( array_unique( $out['companies'] ) );
		$out['roles']     = array_values( array_unique( $out['roles'] ) );
		sort( $out['companies'] );
		sort( $out['roles'] );

		// Bands are ordered by the constant, not alphabetically — "10-12"
		// sorting before "2-3" would be nonsense.
		$out['bands'] = array_values(
			array_intersect( array_keys( self::SALARY_BANDS ), array_unique( $out['bands'] ) )
		);

		return $out;
	}

	/**
	 * Apply the archive filters to the main query.
	 *
	 * Every value is matched against the known set or an exact meta compare —
	 * never interpolated — so a crafted query string cannot widen the result
	 * set or reach another post type.
	 */
	public static function filter_archive( $query ) {
		if ( is_admin() || ! $query->is_main_query() || ! $query->is_post_type_archive( self::POST_TYPE ) ) {
			return;
		}

		$meta = array();

		// The parameters are namespaced deliberately. A bare ?company= collides
		// with the query var WordPress registers for the `company` post type,
		// which quietly turns this request into a single-company lookup — the
		// story archive stops running and the filter silently returns nothing.
		// phpcs:disable WordPress.Security.NonceVerification.Recommended
		if ( ! empty( $_GET[ self::QUERY_COMPANY ] ) ) {
			$meta[] = array(
				'key'     => self::META_COMPANY,
				'value'   => sanitize_text_field( wp_unslash( $_GET[ self::QUERY_COMPANY ] ) ),
				'compare' => '=',
			);
		}

		if ( ! empty( $_GET[ self::QUERY_ROLE ] ) ) {
			$meta[] = array(
				'key'     => self::META_ROLE,
				'value'   => sanitize_text_field( wp_unslash( $_GET[ self::QUERY_ROLE ] ) ),
				'compare' => '=',
			);
		}

		if ( ! empty( $_GET[ self::QUERY_BAND ] ) ) {
			$band = self::sanitize_band( sanitize_text_field( wp_unslash( $_GET[ self::QUERY_BAND ] ) ) );

			if ( $band ) {
				$meta[] = array(
					'key'     => self::META_SALARY,
					'value'   => $band,
					'compare' => '=',
				);
			}
		}
		// phpcs:enable WordPress.Security.NonceVerification.Recommended

		if ( $meta ) {
			$meta['relation'] = 'AND';
			$query->set( 'meta_query', $meta );
		}
	}
}
