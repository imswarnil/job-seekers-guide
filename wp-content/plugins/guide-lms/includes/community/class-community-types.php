<?php
/**
 * Content types the site owner manages in WordPress rather than in files:
 * the help centre, the changelog, and the roadmap.
 *
 * These deliberately use ordinary wp-admin list tables and the block editor
 * instead of being folded into the LMS console. The console is a purpose-built
 * app for the one job it does well — structuring courses. Help articles and
 * changelog entries are just posts with a couple of fields, and WordPress
 * already has an excellent editor for that. Rebuilding it inside a SPA would
 * be a lot of work to end up somewhere worse.
 *
 * They all appear under the LMS menu, so there is still one place to go.
 */

namespace Guide\Community;

defined( 'ABSPATH' ) || exit;

class Community_Types {

	const HELP     = 'help_article';
	const HELP_TAX = 'help_section';
	const CHANGELOG = 'changelog_entry';
	const ROADMAP  = 'roadmap_item';

	/** Roadmap statuses, in the order they are shown. */
	const STATUSES = array(
		'suggested'   => 'Suggested',
		'planned'     => 'Planned',
		'in_progress' => 'In progress',
		'shipped'     => 'Shipped',
		'declined'    => 'Not doing',
	);

	/** Changelog entry kinds, mirroring Keep a Changelog. */
	const KINDS = array(
		'added'      => 'Added',
		'changed'    => 'Changed',
		'fixed'      => 'Fixed',
		'removed'    => 'Removed',
		'security'   => 'Security',
	);

	public static function init() {
		add_action( 'init', array( __CLASS__, 'register' ) );
		add_action( 'init', array( __CLASS__, 'register_meta' ) );
		add_action( 'admin_menu', array( __CLASS__, 'register_menus' ), 25 );
		add_action( 'add_meta_boxes', array( __CLASS__, 'register_meta_boxes' ) );
		add_action( 'save_post', array( __CLASS__, 'save_meta' ), 10, 2 );
	}

	public static function register() {
		register_taxonomy(
			self::HELP_TAX,
			array( self::HELP ),
			array(
				'labels'            => array(
					'name'          => __( 'Help sections', 'guide-lms' ),
					'singular_name' => __( 'Help section', 'guide-lms' ),
					'add_new_item'  => __( 'Add help section', 'guide-lms' ),
				),
				'public'            => true,
				'hierarchical'      => true,
				'show_ui'           => true,
				'show_in_menu'      => false,
				'show_in_rest'      => true,
				'show_admin_column' => true,
				'rewrite'           => array( 'slug' => 'help/section', 'with_front' => false ),
			)
		);

		register_post_type(
			self::HELP,
			array(
				'labels'       => array(
					'name'          => __( 'Help articles', 'guide-lms' ),
					'singular_name' => __( 'Help article', 'guide-lms' ),
					'add_new_item'  => __( 'Add help article', 'guide-lms' ),
					'edit_item'     => __( 'Edit help article', 'guide-lms' ),
					'search_items'  => __( 'Search help articles', 'guide-lms' ),
				),
				'public'       => true,
				'show_ui'      => true,
				'show_in_menu' => false,
				'show_in_rest' => true,
				'menu_icon'    => 'dashicons-editor-help',
				'supports'     => array( 'title', 'editor', 'excerpt', 'revisions', 'page-attributes' ),
				'has_archive'  => 'help',
				'rewrite'      => array( 'slug' => 'help', 'with_front' => false ),
			)
		);

		register_post_type(
			self::CHANGELOG,
			array(
				'labels'       => array(
					'name'          => __( 'Changelog', 'guide-lms' ),
					'singular_name' => __( 'Changelog entry', 'guide-lms' ),
					'add_new_item'  => __( 'Add changelog entry', 'guide-lms' ),
					'edit_item'     => __( 'Edit changelog entry', 'guide-lms' ),
				),
				'public'       => true,
				'show_ui'      => true,
				'show_in_menu' => false,
				'show_in_rest' => true,
				'supports'     => array( 'title', 'editor', 'revisions' ),
				'has_archive'  => 'changelog',
				'rewrite'      => array( 'slug' => 'changelog', 'with_front' => false ),
			)
		);

		register_post_type(
			self::ROADMAP,
			array(
				'labels'       => array(
					'name'          => __( 'Roadmap', 'guide-lms' ),
					'singular_name' => __( 'Roadmap item', 'guide-lms' ),
					'add_new_item'  => __( 'Add roadmap item', 'guide-lms' ),
					'edit_item'     => __( 'Edit roadmap item', 'guide-lms' ),
				),
				'public'       => true,
				'show_ui'      => true,
				'show_in_menu' => false,
				'show_in_rest' => true,
				'supports'     => array( 'title', 'editor', 'author', 'revisions' ),
				'has_archive'  => 'roadmap',
				'rewrite'      => array( 'slug' => 'roadmap', 'with_front' => false ),
			)
		);
	}

	public static function register_meta() {
		$can_edit = function () {
			return current_user_can( 'edit_posts' );
		};

		register_post_meta(
			self::ROADMAP,
			'jsl_roadmap_status',
			array(
				'type'              => 'string',
				'single'            => true,
				'default'           => 'suggested',
				'show_in_rest'      => true,
				'sanitize_callback' => array( __CLASS__, 'sanitize_status' ),
				'auth_callback'     => $can_edit,
			)
		);

		// Denormalised vote total. The reactions table is the source of truth;
		// this exists so the archive can sort by popularity without a join per
		// row, and is recomputed on every vote.
		register_post_meta(
			self::ROADMAP,
			'jsl_roadmap_votes',
			array(
				'type'          => 'integer',
				'single'        => true,
				'default'       => 0,
				'show_in_rest'  => true,
				'auth_callback' => $can_edit,
			)
		);

		// Whether the owner has prioritised this. Surfaced to voters, because
		// "we heard you and it is next" is the only response to a suggestion
		// that actually satisfies anyone.
		register_post_meta(
			self::ROADMAP,
			'jsl_roadmap_pinned',
			array(
				'type'          => 'boolean',
				'single'        => true,
				'default'       => false,
				'show_in_rest'  => true,
				'auth_callback' => $can_edit,
			)
		);

		register_post_meta(
			self::CHANGELOG,
			'jsl_changelog_version',
			array(
				'type'              => 'string',
				'single'            => true,
				'show_in_rest'      => true,
				'sanitize_callback' => 'sanitize_text_field',
				'auth_callback'     => $can_edit,
			)
		);

		register_post_meta(
			self::CHANGELOG,
			'jsl_changelog_kind',
			array(
				'type'              => 'string',
				'single'            => true,
				'default'           => 'added',
				'show_in_rest'      => true,
				'sanitize_callback' => array( __CLASS__, 'sanitize_kind' ),
				'auth_callback'     => $can_edit,
			)
		);
	}

	public static function sanitize_status( $value ): string {
		$value = sanitize_key( (string) $value );
		return isset( self::STATUSES[ $value ] ) ? $value : 'suggested';
	}

	public static function sanitize_kind( $value ): string {
		$value = sanitize_key( (string) $value );
		return isset( self::KINDS[ $value ] ) ? $value : 'added';
	}

	public static function status_label( string $status ): string {
		return self::STATUSES[ self::sanitize_status( $status ) ] ?? $status;
	}

	public static function kind_label( string $kind ): string {
		return self::KINDS[ self::sanitize_kind( $kind ) ] ?? $kind;
	}

	// -------------------------------------------------------------------------
	// Admin menu
	// -------------------------------------------------------------------------

	public static function register_menus() {
		$console = 'guide-lms';

		add_submenu_page( $console, __( 'Help articles', 'guide-lms' ), __( 'Help centre', 'guide-lms' ), 'edit_posts', 'edit.php?post_type=' . self::HELP );
		add_submenu_page( $console, __( 'Changelog', 'guide-lms' ), __( 'Changelog', 'guide-lms' ), 'edit_posts', 'edit.php?post_type=' . self::CHANGELOG );
		add_submenu_page( $console, __( 'Roadmap', 'guide-lms' ), __( 'Roadmap', 'guide-lms' ), 'edit_posts', 'edit.php?post_type=' . self::ROADMAP );
	}

	// -------------------------------------------------------------------------
	// Meta boxes
	// -------------------------------------------------------------------------

	public static function register_meta_boxes() {
		add_meta_box(
			'guide-roadmap-meta',
			__( 'Roadmap', 'guide-lms' ),
			array( __CLASS__, 'render_roadmap_box' ),
			self::ROADMAP,
			'side'
		);

		add_meta_box(
			'guide-changelog-meta',
			__( 'Release', 'guide-lms' ),
			array( __CLASS__, 'render_changelog_box' ),
			self::CHANGELOG,
			'side'
		);
	}

	public static function render_roadmap_box( \WP_Post $post ) {
		wp_nonce_field( 'guide_community_meta', 'guide_community_nonce' );

		$status = (string) get_post_meta( $post->ID, 'jsl_roadmap_status', true );
		$pinned = (bool) get_post_meta( $post->ID, 'jsl_roadmap_pinned', true );
		$votes  = (int) get_post_meta( $post->ID, 'jsl_roadmap_votes', true );
		?>
		<p>
			<label for="guide_roadmap_status"><strong><?php esc_html_e( 'Status', 'guide-lms' ); ?></strong></label><br>
			<select name="guide_roadmap_status" id="guide_roadmap_status" class="widefat">
				<?php foreach ( self::STATUSES as $key => $label ) : ?>
					<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $status, $key ); ?>>
						<?php echo esc_html( $label ); ?>
					</option>
				<?php endforeach; ?>
			</select>
		</p>

		<p>
			<label>
				<input type="checkbox" name="guide_roadmap_pinned" value="1" <?php checked( $pinned ); ?>>
				<?php esc_html_e( 'Prioritised', 'guide-lms' ); ?>
			</label>
			<span class="description"><?php esc_html_e( 'Shown to voters as "we heard you".', 'guide-lms' ); ?></span>
		</p>

		<p>
			<strong><?php echo esc_html( number_format_i18n( $votes ) ); ?></strong>
			<?php echo esc_html( _n( 'vote', 'votes', $votes, 'guide-lms' ) ); ?>
		</p>
		<?php
	}

	public static function render_changelog_box( \WP_Post $post ) {
		wp_nonce_field( 'guide_community_meta', 'guide_community_nonce' );

		$version = (string) get_post_meta( $post->ID, 'jsl_changelog_version', true );
		$kind    = (string) get_post_meta( $post->ID, 'jsl_changelog_kind', true );
		?>
		<p>
			<label for="guide_changelog_version"><strong><?php esc_html_e( 'Version', 'guide-lms' ); ?></strong></label><br>
			<input type="text" class="widefat" id="guide_changelog_version" name="guide_changelog_version"
				value="<?php echo esc_attr( $version ); ?>" placeholder="0.9.0">
			<span class="description"><?php esc_html_e( 'Entries are grouped by version on the public page.', 'guide-lms' ); ?></span>
		</p>

		<p>
			<label for="guide_changelog_kind"><strong><?php esc_html_e( 'Kind', 'guide-lms' ); ?></strong></label><br>
			<select name="guide_changelog_kind" id="guide_changelog_kind" class="widefat">
				<?php foreach ( self::KINDS as $key => $label ) : ?>
					<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $kind, $key ); ?>>
						<?php echo esc_html( $label ); ?>
					</option>
				<?php endforeach; ?>
			</select>
		</p>
		<?php
	}

	public static function save_meta( $post_id, $post ) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( ! isset( $_POST['guide_community_nonce'] )
			|| ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['guide_community_nonce'] ) ), 'guide_community_meta' ) ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		if ( self::ROADMAP === $post->post_type ) {
			update_post_meta(
				$post_id,
				'jsl_roadmap_status',
				self::sanitize_status( isset( $_POST['guide_roadmap_status'] ) ? sanitize_key( wp_unslash( $_POST['guide_roadmap_status'] ) ) : '' )
			);
			update_post_meta( $post_id, 'jsl_roadmap_pinned', isset( $_POST['guide_roadmap_pinned'] ) ? 1 : 0 );
		}

		if ( self::CHANGELOG === $post->post_type ) {
			update_post_meta(
				$post_id,
				'jsl_changelog_version',
				sanitize_text_field( wp_unslash( $_POST['guide_changelog_version'] ?? '' ) )
			);
			update_post_meta(
				$post_id,
				'jsl_changelog_kind',
				self::sanitize_kind( isset( $_POST['guide_changelog_kind'] ) ? sanitize_key( wp_unslash( $_POST['guide_changelog_kind'] ) ) : '' )
			);
		}
	}
}
