<?php
/**
 * Company guides — "how to get a job at Accenture".
 *
 * This is the question the audience actually types into a search engine, and
 * the honest answer is specific: this is how they hire, these are the rounds
 * in order, this is what they pay, this is how hard it is, and these are the
 * skills — with a link to the course that teaches each one.
 *
 * Two things are deliberate about the data model:
 *
 *   · Salary is stored as a BAND, never an individual figure, and every
 *     company carries a "last verified" date. Compensation data goes stale
 *     fast, and a confident-looking number from three years ago is worse than
 *     no number at all — someone will negotiate against it.
 *
 *   · Skills link to courses by ID rather than by name. A guide that says
 *     "you need SQL" and stops is a guide that leaves the reader exactly where
 *     they started; the point of putting this on a learning platform is that
 *     the next step is one click away.
 */

namespace Guide\Companies;

defined( 'ABSPATH' ) || exit;

class Companies {

	const POST_TYPE = 'company';
	const TAXONOMY  = 'company_type';

	/** How hard it is to get in, 1–5. */
	const DIFFICULTY = array(
		1 => 'Very accessible',
		2 => 'Accessible',
		3 => 'Competitive',
		4 => 'Hard',
		5 => 'Very hard',
	);

	const HIRING_MODES = array(
		'campus'       => 'Campus placement',
		'off_campus'   => 'Off-campus drive',
		'referral'     => 'Employee referral',
		'portal'       => 'Careers portal',
		'consultancy'  => 'Staffing consultancy',
		'hackathon'    => 'Hiring challenge',
	);

	public static function init() {
		add_action( 'init', array( __CLASS__, 'register' ) );
		add_action( 'init', array( __CLASS__, 'register_meta' ) );
		add_action( 'admin_menu', array( __CLASS__, 'register_menu' ), 26 );
		add_action( 'add_meta_boxes', array( __CLASS__, 'register_meta_boxes' ) );
		add_action( 'save_post_' . self::POST_TYPE, array( __CLASS__, 'save' ), 10, 2 );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'admin_assets' ) );
	}

	public static function register() {
		register_taxonomy(
			self::TAXONOMY,
			array( self::POST_TYPE ),
			array(
				'labels'            => array(
					'name'          => __( 'Company types', 'guide-lms' ),
					'singular_name' => __( 'Company type', 'guide-lms' ),
				),
				'public'            => true,
				'hierarchical'      => false,
				'show_ui'           => true,
				'show_in_menu'      => false,
				'show_in_rest'      => true,
				'show_admin_column' => true,
				'rewrite'           => array( 'slug' => 'companies/type', 'with_front' => false ),
			)
		);

		register_post_type(
			self::POST_TYPE,
			array(
				'labels'       => array(
					'name'          => __( 'Companies', 'guide-lms' ),
					'singular_name' => __( 'Company', 'guide-lms' ),
					'add_new_item'  => __( 'Add company guide', 'guide-lms' ),
					'edit_item'     => __( 'Edit company guide', 'guide-lms' ),
					'search_items'  => __( 'Search companies', 'guide-lms' ),
				),
				'public'       => true,
				'show_ui'      => true,
				'show_in_menu' => false,
				'show_in_rest' => true,
				'supports'     => array( 'title', 'editor', 'excerpt', 'thumbnail', 'revisions' ),
				'has_archive'  => 'companies',
				'rewrite'      => array( 'slug' => 'companies', 'with_front' => false ),
			)
		);
	}

	public static function register_meta() {
		$can_edit = function () {
			return current_user_can( 'edit_posts' );
		};

		$scalars = array(
			'jsl_company_website'    => 'string',
			'jsl_company_hq'         => 'string',
			'jsl_company_locations'  => 'string',
			'jsl_company_headcount'  => 'string',
			'jsl_company_window'     => 'string',
			'jsl_company_verified'   => 'string',
			'jsl_company_difficulty' => 'integer',
		);

		foreach ( $scalars as $key => $type ) {
			register_post_meta(
				self::POST_TYPE,
				$key,
				array(
					'type'              => $type,
					'single'            => true,
					'show_in_rest'      => true,
					'sanitize_callback' => 'integer' === $type ? 'absint' : 'sanitize_text_field',
					'auth_callback'     => $can_edit,
				)
			);
		}

		// Repeatable structures. Stored as arrays of associative arrays and
		// kept out of REST — they are edited through the metabox, and exposing
		// a free-form array to REST is a sanitising problem with no upside.
		foreach ( array( 'jsl_company_salary', 'jsl_company_process', 'jsl_company_skills', 'jsl_company_modes' ) as $key ) {
			register_post_meta(
				self::POST_TYPE,
				$key,
				array(
					'type'          => 'array',
					'single'        => true,
					'default'       => array(),
					'show_in_rest'  => false,
					'auth_callback' => $can_edit,
				)
			);
		}
	}

	public static function register_menu() {
		add_submenu_page(
			'guide-lms',
			__( 'Companies', 'guide-lms' ),
			__( 'Companies', 'guide-lms' ),
			'edit_posts',
			'edit.php?post_type=' . self::POST_TYPE
		);
	}

	// -------------------------------------------------------------------------
	// Readers
	// -------------------------------------------------------------------------

	public static function difficulty( int $post_id ): int {
		$value = (int) get_post_meta( $post_id, 'jsl_company_difficulty', true );
		return $value >= 1 && $value <= 5 ? $value : 3;
	}

	public static function difficulty_label( int $post_id ): string {
		return self::DIFFICULTY[ self::difficulty( $post_id ) ];
	}

	/** @return array<int, array{role:string, min:float, max:float, level:string}> */
	public static function salary_bands( int $post_id ): array {
		$rows = get_post_meta( $post_id, 'jsl_company_salary', true );
		return is_array( $rows ) ? $rows : array();
	}

	/** @return array<int, array{title:string, detail:string}> */
	public static function process( int $post_id ): array {
		$rows = get_post_meta( $post_id, 'jsl_company_process', true );
		return is_array( $rows ) ? $rows : array();
	}

	/** @return array<int, array{name:string, course:int}> */
	public static function skills( int $post_id ): array {
		$rows = get_post_meta( $post_id, 'jsl_company_skills', true );
		return is_array( $rows ) ? $rows : array();
	}

	/** @return string[] */
	public static function modes( int $post_id ): array {
		$rows = get_post_meta( $post_id, 'jsl_company_modes', true );
		return is_array( $rows ) ? $rows : array();
	}

	/**
	 * The overall fresher band across every role, for the card.
	 *
	 * @return array{min:float, max:float}|null
	 */
	public static function fresher_band( int $post_id ) {
		$min = null;
		$max = null;

		foreach ( self::salary_bands( $post_id ) as $row ) {
			if ( 'fresher' !== ( $row['level'] ?? 'fresher' ) ) {
				continue;
			}

			$row_min = (float) ( $row['min'] ?? 0 );
			$row_max = (float) ( $row['max'] ?? 0 );

			if ( $row_min > 0 && ( null === $min || $row_min < $min ) ) {
				$min = $row_min;
			}
			if ( $row_max > 0 && ( null === $max || $row_max > $max ) ) {
				$max = $row_max;
			}
		}

		if ( null === $min && null === $max ) {
			return null;
		}

		return array(
			'min' => (float) $min,
			'max' => (float) $max,
		);
	}

	/** "3.5 – 6.5 LPA", or "from 3.5 LPA" when only one end is known. */
	public static function format_band( $min, $max ): string {
		$min = (float) $min;
		$max = (float) $max;

		$fmt = static function ( $n ) {
			return rtrim( rtrim( number_format( (float) $n, 2, '.', '' ), '0' ), '.' );
		};

		if ( $min > 0 && $max > 0 ) {
			return sprintf( '₹%s – %s LPA', $fmt( $min ), $fmt( $max ) );
		}

		if ( $min > 0 ) {
			/* translators: %s: a salary figure in lakhs per annum. */
			return sprintf( __( 'from ₹%s LPA', 'guide-lms' ), $fmt( $min ) );
		}

		if ( $max > 0 ) {
			/* translators: %s: a salary figure in lakhs per annum. */
			return sprintf( __( 'up to ₹%s LPA', 'guide-lms' ), $fmt( $max ) );
		}

		return '';
	}

	// -------------------------------------------------------------------------
	// Admin
	// -------------------------------------------------------------------------

	public static function admin_assets( $hook ) {
		$screen = get_current_screen();

		if ( ! $screen || self::POST_TYPE !== $screen->post_type ) {
			return;
		}

		wp_enqueue_script(
			'guide-company-admin',
			GUIDE_PLUGIN_URL . 'admin/assets/js/company-admin.js',
			array(),
			guide_plugin_asset_version( 'admin/assets/js/company-admin.js' ),
			true
		);
	}

	public static function register_meta_boxes() {
		add_meta_box( 'guide-company-facts', __( 'At a glance', 'guide-lms' ), array( __CLASS__, 'box_facts' ), self::POST_TYPE, 'normal', 'high' );
		add_meta_box( 'guide-company-salary', __( 'Salary bands', 'guide-lms' ), array( __CLASS__, 'box_salary' ), self::POST_TYPE, 'normal' );
		add_meta_box( 'guide-company-process', __( 'Selection process', 'guide-lms' ), array( __CLASS__, 'box_process' ), self::POST_TYPE, 'normal' );
		add_meta_box( 'guide-company-skills', __( 'Skills they test', 'guide-lms' ), array( __CLASS__, 'box_skills' ), self::POST_TYPE, 'normal' );
	}

	public static function box_facts( \WP_Post $post ) {
		wp_nonce_field( 'guide_company_meta', 'guide_company_nonce' );

		$difficulty = self::difficulty( $post->ID );
		$modes      = self::modes( $post->ID );
		?>
		<style>
			.guide-cf{display:grid;grid-template-columns:repeat(auto-fit,minmax(15rem,1fr));gap:12px 20px}
			.guide-cf label{display:block;font-weight:600;margin-bottom:3px}
			.guide-cf input,.guide-cf select{width:100%}
			.guide-rows{display:flex;flex-direction:column;gap:8px;margin-top:8px}
			.guide-row{display:grid;gap:8px;align-items:start;background:#fff;border:1px solid #e2e3ee;border-radius:6px;padding:10px}
			.guide-row input,.guide-row select,.guide-row textarea{width:100%}
			.guide-row__rm{color:#d0413c;cursor:pointer;background:none;border:0;font-size:18px;line-height:1}
		</style>

		<div class="guide-cf">
			<div>
				<label for="jsl_company_website"><?php esc_html_e( 'Careers page', 'guide-lms' ); ?></label>
				<input type="url" id="jsl_company_website" name="jsl_company_website" value="<?php echo esc_attr( (string) get_post_meta( $post->ID, 'jsl_company_website', true ) ); ?>" placeholder="https://">
			</div>
			<div>
				<label for="jsl_company_hq"><?php esc_html_e( 'Headquarters', 'guide-lms' ); ?></label>
				<input type="text" id="jsl_company_hq" name="jsl_company_hq" value="<?php echo esc_attr( (string) get_post_meta( $post->ID, 'jsl_company_hq', true ) ); ?>">
			</div>
			<div>
				<label for="jsl_company_locations"><?php esc_html_e( 'Hiring locations', 'guide-lms' ); ?></label>
				<input type="text" id="jsl_company_locations" name="jsl_company_locations" value="<?php echo esc_attr( (string) get_post_meta( $post->ID, 'jsl_company_locations', true ) ); ?>" placeholder="Bengaluru, Pune, Hyderabad">
			</div>
			<div>
				<label for="jsl_company_headcount"><?php esc_html_e( 'Rough size', 'guide-lms' ); ?></label>
				<input type="text" id="jsl_company_headcount" name="jsl_company_headcount" value="<?php echo esc_attr( (string) get_post_meta( $post->ID, 'jsl_company_headcount', true ) ); ?>" placeholder="700,000+">
			</div>
			<div>
				<label for="jsl_company_window"><?php esc_html_e( 'When they hire', 'guide-lms' ); ?></label>
				<input type="text" id="jsl_company_window" name="jsl_company_window" value="<?php echo esc_attr( (string) get_post_meta( $post->ID, 'jsl_company_window', true ) ); ?>" placeholder="Rolling, peaks Jul–Nov">
			</div>
			<div>
				<label for="jsl_company_difficulty"><?php esc_html_e( 'How hard to get in', 'guide-lms' ); ?></label>
				<select id="jsl_company_difficulty" name="jsl_company_difficulty">
					<?php foreach ( self::DIFFICULTY as $value => $label ) : ?>
						<option value="<?php echo esc_attr( (string) $value ); ?>" <?php selected( $difficulty, $value ); ?>>
							<?php echo esc_html( $value . ' — ' . $label ); ?>
						</option>
					<?php endforeach; ?>
				</select>
			</div>
			<div>
				<label for="jsl_company_verified"><?php esc_html_e( 'Figures last checked', 'guide-lms' ); ?></label>
				<input type="date" id="jsl_company_verified" name="jsl_company_verified" value="<?php echo esc_attr( (string) get_post_meta( $post->ID, 'jsl_company_verified', true ) ); ?>">
				<p class="description"><?php esc_html_e( 'Shown publicly. Salary data goes stale fast and an unmarked old number is worse than none.', 'guide-lms' ); ?></p>
			</div>
		</div>

		<p style="margin-top:14px"><strong><?php esc_html_e( 'How they hire', 'guide-lms' ); ?></strong></p>
		<?php foreach ( self::HIRING_MODES as $key => $label ) : ?>
			<label style="display:inline-block;margin:0 16px 6px 0">
				<input type="checkbox" name="jsl_company_modes[]" value="<?php echo esc_attr( $key ); ?>" <?php checked( in_array( $key, $modes, true ) ); ?>>
				<?php echo esc_html( $label ); ?>
			</label>
		<?php endforeach; ?>
		<?php
	}

	public static function box_salary( \WP_Post $post ) {
		$rows = self::salary_bands( $post->ID );
		?>
		<p class="description">
			<?php esc_html_e( 'Bands, not individual packages — a named person’s salary next to their employer identifies them. Use lakhs per annum.', 'guide-lms' ); ?>
		</p>

		<div class="guide-rows" data-repeat="salary" data-cols="2fr 1fr 1fr 1fr auto">
			<?php foreach ( $rows as $row ) : ?>
				<?php self::salary_row( $row ); ?>
			<?php endforeach; ?>
		</div>
		<button type="button" class="button" data-add="salary"><?php esc_html_e( '+ Add a band', 'guide-lms' ); ?></button>

		<template data-template="salary"><?php self::salary_row( array() ); ?></template>
		<?php
	}

	private static function salary_row( array $row ) {
		?>
		<div class="guide-row" style="grid-template-columns:2fr 1fr 1fr 1fr auto">
			<input type="text" name="jsl_company_salary[role][]" placeholder="<?php esc_attr_e( 'Role, e.g. Associate Software Engineer', 'guide-lms' ); ?>" value="<?php echo esc_attr( (string) ( $row['role'] ?? '' ) ); ?>">
			<input type="number" step="0.1" min="0" name="jsl_company_salary[min][]" placeholder="<?php esc_attr_e( 'Min LPA', 'guide-lms' ); ?>" value="<?php echo esc_attr( (string) ( $row['min'] ?? '' ) ); ?>">
			<input type="number" step="0.1" min="0" name="jsl_company_salary[max][]" placeholder="<?php esc_attr_e( 'Max LPA', 'guide-lms' ); ?>" value="<?php echo esc_attr( (string) ( $row['max'] ?? '' ) ); ?>">
			<select name="jsl_company_salary[level][]">
				<option value="fresher" <?php selected( $row['level'] ?? 'fresher', 'fresher' ); ?>><?php esc_html_e( 'Fresher', 'guide-lms' ); ?></option>
				<option value="experienced" <?php selected( $row['level'] ?? '', 'experienced' ); ?>><?php esc_html_e( 'Experienced', 'guide-lms' ); ?></option>
			</select>
			<button type="button" class="guide-row__rm" data-remove aria-label="<?php esc_attr_e( 'Remove', 'guide-lms' ); ?>">&times;</button>
		</div>
		<?php
	}

	public static function box_process( \WP_Post $post ) {
		$rows = self::process( $post->ID );
		?>
		<p class="description"><?php esc_html_e( 'The rounds, in the order a candidate meets them. What actually happens in each one is more useful than its name.', 'guide-lms' ); ?></p>

		<div class="guide-rows" data-repeat="process">
			<?php foreach ( $rows as $row ) : ?>
				<?php self::process_row( $row ); ?>
			<?php endforeach; ?>
		</div>
		<button type="button" class="button" data-add="process"><?php esc_html_e( '+ Add a round', 'guide-lms' ); ?></button>

		<template data-template="process"><?php self::process_row( array() ); ?></template>
		<?php
	}

	private static function process_row( array $row ) {
		?>
		<div class="guide-row" style="grid-template-columns:1fr auto">
			<div>
				<input type="text" name="jsl_company_process[title][]" placeholder="<?php esc_attr_e( 'Round, e.g. Online assessment', 'guide-lms' ); ?>" value="<?php echo esc_attr( (string) ( $row['title'] ?? '' ) ); ?>">
				<textarea name="jsl_company_process[detail][]" rows="2" style="margin-top:6px" placeholder="<?php esc_attr_e( 'What happens, how long, what they are looking for…', 'guide-lms' ); ?>"><?php echo esc_textarea( (string) ( $row['detail'] ?? '' ) ); ?></textarea>
			</div>
			<button type="button" class="guide-row__rm" data-remove aria-label="<?php esc_attr_e( 'Remove', 'guide-lms' ); ?>">&times;</button>
		</div>
		<?php
	}

	public static function box_skills( \WP_Post $post ) {
		$rows    = self::skills( $post->ID );
		$courses = get_posts(
			array(
				'post_type'      => 'course',
				'posts_per_page' => 200,
				'orderby'        => 'title',
				'order'          => 'ASC',
				'post_status'    => array( 'publish', 'draft' ),
			)
		);
		?>
		<p class="description">
			<?php esc_html_e( 'Link each skill to the course that teaches it. A guide that says “you need SQL” and stops leaves the reader where they started.', 'guide-lms' ); ?>
		</p>

		<div class="guide-rows" data-repeat="skills">
			<?php foreach ( $rows as $row ) : ?>
				<?php self::skill_row( $row, $courses ); ?>
			<?php endforeach; ?>
		</div>
		<button type="button" class="button" data-add="skills"><?php esc_html_e( '+ Add a skill', 'guide-lms' ); ?></button>

		<template data-template="skills"><?php self::skill_row( array(), $courses ); ?></template>
		<?php
	}

	private static function skill_row( array $row, array $courses ) {
		$selected = (int) ( $row['course'] ?? 0 );
		?>
		<div class="guide-row" style="grid-template-columns:1fr 1fr auto">
			<input type="text" name="jsl_company_skills[name][]" placeholder="<?php esc_attr_e( 'Skill, e.g. SQL joins', 'guide-lms' ); ?>" value="<?php echo esc_attr( (string) ( $row['name'] ?? '' ) ); ?>">
			<select name="jsl_company_skills[course][]">
				<option value="0"><?php esc_html_e( '— no course yet —', 'guide-lms' ); ?></option>
				<?php foreach ( $courses as $course ) : ?>
					<option value="<?php echo esc_attr( (string) $course->ID ); ?>" <?php selected( $selected, $course->ID ); ?>>
						<?php echo esc_html( get_the_title( $course ) ); ?>
					</option>
				<?php endforeach; ?>
			</select>
			<button type="button" class="guide-row__rm" data-remove aria-label="<?php esc_attr_e( 'Remove', 'guide-lms' ); ?>">&times;</button>
		</div>
		<?php
	}

	// -------------------------------------------------------------------------
	// Saving
	// -------------------------------------------------------------------------

	public static function save( $post_id, $post ) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( ! isset( $_POST['guide_company_nonce'] )
			|| ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['guide_company_nonce'] ) ), 'guide_company_meta' ) ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		// Scalars.
		update_post_meta( $post_id, 'jsl_company_website', esc_url_raw( wp_unslash( $_POST['jsl_company_website'] ?? '' ) ) );

		foreach ( array( 'jsl_company_hq', 'jsl_company_locations', 'jsl_company_headcount', 'jsl_company_window', 'jsl_company_verified' ) as $key ) {
			update_post_meta( $post_id, $key, sanitize_text_field( wp_unslash( $_POST[ $key ] ?? '' ) ) );
		}

		$difficulty = isset( $_POST['jsl_company_difficulty'] ) ? absint( $_POST['jsl_company_difficulty'] ) : 3;
		update_post_meta( $post_id, 'jsl_company_difficulty', min( 5, max( 1, $difficulty ) ) );

		// Hiring modes.
		$modes     = isset( $_POST['jsl_company_modes'] ) ? (array) wp_unslash( $_POST['jsl_company_modes'] ) : array();
		$modes     = array_values( array_intersect( array_map( 'sanitize_key', $modes ), array_keys( self::HIRING_MODES ) ) );
		update_post_meta( $post_id, 'jsl_company_modes', $modes );

		// Salary bands. A row with no role is an empty row the author left
		// behind, not data.
		$salary = array();
		$roles  = isset( $_POST['jsl_company_salary']['role'] ) ? (array) wp_unslash( $_POST['jsl_company_salary']['role'] ) : array();

		foreach ( $roles as $i => $role ) {
			$role = sanitize_text_field( $role );

			if ( '' === trim( $role ) ) {
				continue;
			}

			$salary[] = array(
				'role'  => $role,
				'min'   => (float) ( $_POST['jsl_company_salary']['min'][ $i ] ?? 0 ),
				'max'   => (float) ( $_POST['jsl_company_salary']['max'][ $i ] ?? 0 ),
				'level' => 'experienced' === ( $_POST['jsl_company_salary']['level'][ $i ] ?? '' ) ? 'experienced' : 'fresher',
			);
		}

		update_post_meta( $post_id, 'jsl_company_salary', $salary );

		// Selection process.
		$process = array();
		$titles  = isset( $_POST['jsl_company_process']['title'] ) ? (array) wp_unslash( $_POST['jsl_company_process']['title'] ) : array();

		foreach ( $titles as $i => $title ) {
			$title = sanitize_text_field( $title );

			if ( '' === trim( $title ) ) {
				continue;
			}

			$process[] = array(
				'title'  => $title,
				'detail' => sanitize_textarea_field( wp_unslash( $_POST['jsl_company_process']['detail'][ $i ] ?? '' ) ),
			);
		}

		update_post_meta( $post_id, 'jsl_company_process', $process );

		// Skills.
		$skills = array();
		$names  = isset( $_POST['jsl_company_skills']['name'] ) ? (array) wp_unslash( $_POST['jsl_company_skills']['name'] ) : array();

		foreach ( $names as $i => $name ) {
			$name = sanitize_text_field( $name );

			if ( '' === trim( $name ) ) {
				continue;
			}

			$course = (int) ( $_POST['jsl_company_skills']['course'][ $i ] ?? 0 );

			// Only link to something that is actually a course.
			if ( $course && 'course' !== get_post_type( $course ) ) {
				$course = 0;
			}

			$skills[] = array(
				'name'   => $name,
				'course' => $course,
			);
		}

		update_post_meta( $post_id, 'jsl_company_skills', $skills );
	}
}
