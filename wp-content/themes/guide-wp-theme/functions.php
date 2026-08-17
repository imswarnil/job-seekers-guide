<?php
/**
 * Guide WP Theme bootstrap. Classic (non-block) theme.
 *
 * Styling is Bulma, compiled from src/scss/app.scss into
 * assets/css/app.min.css (committed, so production needs no Node). The design
 * tokens live in src/scss/_tokens.scss and are fed into Bulma's configuration.
 * Rebuild with `npm run build`.
 */

defined( 'ABSPATH' ) || exit;

define( 'GUIDE_THEME_VERSION', '0.7.0' );
define( 'GUIDE_THEME_DIR', get_template_directory() );
define( 'GUIDE_THEME_URI', get_template_directory_uri() );

require_once GUIDE_THEME_DIR . '/inc/icons.php';
require_once GUIDE_THEME_DIR . '/inc/dark-mode.php';
require_once GUIDE_THEME_DIR . '/inc/course-filters.php';
require_once GUIDE_THEME_DIR . '/inc/customizer.php';

add_action( 'after_setup_theme', 'guide_theme_setup' );

function guide_theme_setup() {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ) );
	load_theme_textdomain( 'guide-wp-theme', GUIDE_THEME_DIR . '/languages' );
}

add_action( 'wp_enqueue_scripts', 'guide_theme_assets' );

// SVG favicon (assets/img/favicon.svg).
add_action( 'wp_head', 'guide_theme_favicon', 5 );
function guide_theme_favicon() {
	echo '<link rel="icon" type="image/svg+xml" href="' . esc_url( GUIDE_THEME_URI . '/assets/img/favicon.svg' ) . '">' . "\n";
}

function guide_theme_assets() {
	// Plus Jakarta Sans (display) + Inter (body/UI) + JetBrains Mono (code).
	wp_enqueue_style(
		'guide-fonts',
		'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@600;700;800&family=JetBrains+Mono:wght@400;600&display=swap',
		array(),
		null
	);
	wp_enqueue_style( 'guide-app', GUIDE_THEME_URI . '/assets/css/app.min.css', array( 'guide-fonts' ), guide_asset_version( '/assets/css/app.min.css' ) );
	wp_enqueue_script( 'guide-ui', GUIDE_THEME_URI . '/assets/js/ui.js', array(), guide_asset_version( '/assets/js/ui.js' ), true );
	wp_enqueue_script( 'guide-theme', GUIDE_THEME_URI . '/assets/js/theme.js', array( 'guide-ui' ), guide_asset_version( '/assets/js/theme.js' ), true );
}

/**
 * Cache-busting version for a theme asset: the file's own modification time.
 *
 * The theme version alone is not enough — CSS and JS change far more often
 * than the version constant gets bumped, and a stale cached stylesheet after
 * a deploy looks exactly like a broken design.
 *
 * @param string $relative_path Path from the theme root, with a leading slash.
 */
function guide_asset_version( $relative_path ) {
	$file = GUIDE_THEME_DIR . $relative_path;
	$time = file_exists( $file ) ? filemtime( $file ) : 0;

	return $time ? GUIDE_THEME_VERSION . '.' . $time : GUIDE_THEME_VERSION;
}

/**
 * Drop the "Archives:" / "Category:" prefix from archive headings.
 *
 * The prefix reads like a database listing rather than a page title, and
 * the surrounding template already says what kind of page this is.
 */
add_filter( 'get_the_archive_title_prefix', '__return_empty_string' );

/**
 * The app's primary destinations.
 *
 * One definition drives both navigation components — the inline destinations
 * in the header and the bottom navigation bar — so they can never disagree
 * about what exists or which one is current.
 *
 * @param bool $with_home Include Home. The bottom navigation bar needs it
 *                        (there is no logo to tap); the top app bar does not,
 *                        because the logo is the home affordance.
 * @return array<int, array{url:string, label:string, icon:string, here:bool}>
 */
function guide_primary_destinations( $with_home = false ) {
	$items = array();

	if ( $with_home ) {
		$items[] = array(
			'url'   => home_url( '/' ),
			'label' => __( 'Home', 'guide-wp-theme' ),
			'icon'  => 'house',
			'here'  => is_front_page(),
		);
	}

	$items[] = array(
		'url'   => (string) get_post_type_archive_link( 'learning_path' ),
		'label' => __( 'Paths', 'guide-wp-theme' ),
		'icon'  => 'path',
		'here'  => is_post_type_archive( 'learning_path' ) || is_singular( 'learning_path' ),
	);

	$items[] = array(
		'url'   => (string) get_post_type_archive_link( 'course' ),
		'label' => __( 'Courses', 'guide-wp-theme' ),
		'icon'  => 'graduation-cap',
		'here'  => is_post_type_archive( 'course' ) || is_singular( 'course' ),
	);

	if ( post_type_exists( 'company' ) ) {
		$items[] = array(
			'url'   => (string) get_post_type_archive_link( 'company' ),
			'label' => __( 'Companies', 'guide-wp-theme' ),
			'icon'  => 'buildings',
			'here'  => is_post_type_archive( 'company' ) || is_singular( 'company' ),
		);
	}

	if ( class_exists( 'Guide\\Success\\Success_Stories' ) && \Guide\Success\Success_Stories::is_enabled() ) {
		$items[] = array(
			'url'   => (string) get_post_type_archive_link( 'success_story' ),
			'label' => __( 'Stories', 'guide-wp-theme' ),
			'icon'  => 'trophy',
			'here'  => is_post_type_archive( 'success_story' ) || is_singular( 'success_story' ),
		);
	}

	// Flagged as an account destination: the bottom navigation bar needs it
	// (there is no header button down there), the desktop bar does not,
	// because the account menu and Sign in button already cover it.
	$items[] = is_user_logged_in()
		? array(
			'url'     => home_url( '/my-learning/' ),
			'label'   => __( 'Learning', 'guide-wp-theme' ),
			'icon'    => 'stack',
			'here'    => is_page( 'my-learning' ),
			'account' => true,
		)
		: array(
			'url'     => wp_login_url(),
			'label'   => __( 'Sign in', 'guide-wp-theme' ),
			'icon'    => 'user-circle',
			'here'    => false,
			'account' => true,
		);

	return $items;
}

/**
 * Secondary destinations — real pages, but not frequent enough to earn a
 * slot in the navigation bar. They live in the account menu and footer.
 *
 * @return array<int, array{url:string, label:string, icon:string}>
 */
function guide_secondary_destinations() {
	$items = array();

	if ( class_exists( 'Guide\\Leaderboard\\Leaderboard' ) && \Guide\Leaderboard\Leaderboard::is_enabled() ) {
		$items[] = array(
			'url'   => \Guide\Leaderboard\Leaderboard::url(),
			'label' => __( 'Leaderboard', 'guide-wp-theme' ),
			'icon'  => 'medal',
		);
	}

	if ( class_exists( 'Guide\\Success\\Success_Stories' ) && \Guide\Success\Success_Stories::is_enabled() ) {
		$items[] = array(
			'url'   => \Guide\Success\Success_Stories::archive_url(),
			'label' => __( 'Wall of Success', 'guide-wp-theme' ),
			'icon'  => 'trophy',
		);
	}

	foreach ( array(
		'help_article'    => array( __( 'Help centre', 'guide-wp-theme' ), 'question' ),
		'roadmap_item'    => array( __( 'Roadmap', 'guide-wp-theme' ), 'map-pin' ),
		'changelog_entry' => array( __( 'Changelog', 'guide-wp-theme' ), 'list-bullets' ),
	) as $guide_type => $guide_meta ) {
		if ( ! post_type_exists( $guide_type ) ) {
			continue;
		}

		$items[] = array(
			'url'   => (string) get_post_type_archive_link( $guide_type ),
			'label' => $guide_meta[0],
			'icon'  => $guide_meta[1],
		);
	}

	return $items;
}

/**
 * Inline Phosphor icon.
 *
 * Icons are baked into inc/icons.php by `npm run icons`, so rendering one
 * costs an array lookup — no icon font, no network request, and it works
 * offline in the PWA. Append "-fill" to a name for the solid variant
 * (e.g. guide_icon( 'check-circle-fill' )).
 *
 * @param string $name  Phosphor icon name, or a legacy alias.
 * @param string $class Classes for the <svg> element.
 * @param string $title Accessible name. Empty (default) renders the icon as
 *                      decorative, which is right when adjacent text already
 *                      names the action.
 */
function guide_icon( $name, $class = '', $title = '' ) {
	static $paths = null;

	if ( null === $paths ) {
		$paths = guide_icon_paths();
	}

	// Names used by templates written before the Phosphor switch.
	$aliases = array(
		'play'    => 'play-fill',
		'arrow-r' => 'arrow-right',
		'arrow-l' => 'arrow-left',
		'layers'  => 'stack',
		'doc'     => 'article',
		'menu'    => 'list',
		'spark'   => 'sparkle',
		// Lesson types, named by what they are rather than what they look
		// like, so templates read clearly.
		'quiz'    => 'list-checks',
		'video'   => 'film-strip',
	);

	$key = $aliases[ $name ] ?? $name;

	if ( ! isset( $paths[ $key ] ) ) {
		return '';
	}

	$a11y = $title
		? 'role="img" aria-label="' . esc_attr( $title ) . '"'
		: 'aria-hidden="true" focusable="false"';

	// `guide-icon` carries the default size. Component rules that need a
	// different one override it — without this every icon renders at the
	// SVG's intrinsic size, which is enormous.
	$classes = trim( 'guide-icon ' . $class );

	return '<svg class="' . esc_attr( $classes ) . '" viewBox="0 0 256 256" fill="currentColor" ' . $a11y . '>' . $paths[ $key ] . '</svg>';
}

/**
 * Learner avatar: an initial on a colour derived from the user ID.
 *
 * Deliberately not Gravatar. A leaderboard or story wall is mostly people
 * without one, so it would render as rows of identical mystery-person
 * icons; it also sends every visitor's page to a third party and fails in
 * the offline PWA. A stable letter mark reads better and costs nothing.
 *
 * @param int    $user_id
 * @param int    $size    Pixel size.
 * @param string $class   Extra classes on the element.
 */
function guide_avatar( $user_id, $size = 40, $class = '' ) {
	$user    = get_userdata( (int) $user_id );
	$name    = $user ? $user->display_name : '';
	$initial = $name ? mb_strtoupper( mb_substr( $name, 0, 1 ) ) : '?';
	$px      = (int) $size;

	// Deterministic hue, so a person's avatar colour never changes.
	$hue = ( (int) $user_id * 47 ) % 360;

	return sprintf(
		'<span class="guide-avatar %1$s" style="width:%2$dpx;height:%2$dpx;font-size:%3$dpx;background:hsl(%4$d 62%% 88%%);color:hsl(%4$d 65%% 26%%)" aria-hidden="true">%5$s</span>',
		esc_attr( $class ),
		$px,
		max( 11, (int) round( $px * 0.42 ) ),
		(int) $hue,
		esc_html( $initial )
	);
}

/**
 * The brand mark, inline. A magnifying glass with a briefcase in the lens —
 * the job search, and the thing being searched for.
 *
 * Kept in sync with assets/img/logo.svg (that file is the standalone asset
 * for the manifest, OG images and anywhere an <img> is needed; this is the
 * inline version so it can take its colour from its container).
 *
 * @param string $class Classes for the <svg>.
 */
function guide_logo_mark( $class = '' ) {
	// Three ascending steps, the last carried through as an arrow.
	//
	// The old mark was a magnifying glass over a briefcase — "job search",
	// which is what every job board on the internet draws, and which depicts
	// the thing this platform argues is not the problem. The problem is the
	// order: people are not short of material, they are short of a sequence.
	// So the mark is a sequence, going up.
	//
	// Solid shapes rather than strokes, because the same drawing has to work
	// at 16px in a browser tab where a fine stroke turns to mud.
	return '<svg class="' . esc_attr( trim( 'guide-icon ' . $class ) ) . '" viewBox="0 0 256 256" fill="none" aria-hidden="true" focusable="false">'
		. '<rect x="26" y="150" width="50" height="76" rx="16" fill="currentColor" opacity=".45"/>'
		. '<rect x="103" y="104" width="50" height="122" rx="16" fill="currentColor" opacity=".72"/>'
		. '<rect x="180" y="58" width="50" height="168" rx="16" fill="currentColor"/>'
		. '</svg>';
}

/**
 * Escape an image src that may be a plugin-generated SVG data URI
 * (esc_url strips the data: scheme). Only base64 SVG data URIs are let
 * through verbatim; everything else goes through esc_url.
 */
function guide_img_src( $src ) {
	if ( 0 === strpos( (string) $src, 'data:image/svg+xml;base64,' ) && preg_match( '/^[A-Za-z0-9+\/=,;:\-_]+$/', substr( $src, 5 ) ) ) {
		return esc_attr( $src );
	}
	return esc_url( $src );
}

/**
 * Render an ad slot, if this request should see one.
 *
 * A thin wrapper so templates do not have to know whether the plugin is
 * active, whether ads are configured, or whether this particular visitor is a
 * subscriber — Guide\Ads\Ads owns all of that.
 *
 * @param string $which 'feed' or 'page'.
 * @param bool   $upsell Show the "subscribe to remove ads" line underneath.
 */
function guide_ad( $which = 'page', $upsell = true ) {
	if ( ! class_exists( 'Guide\\Ads\\Ads' ) ) {
		return;
	}

	$drawn = \Guide\Ads\Ads::render( $which );

	// "Subscribe to remove them" only makes sense under something a
	// subscription would actually remove — not under the administrator-only
	// outline of an empty slot.
	if ( $upsell && 'adsense' === $drawn ) {
		\Guide\Ads\Ads::render_upsell();
	}
}

/**
 * Render one comment.
 *
 * Hand-rolled rather than using WordPress's default walker output, which emits
 * markup shaped for a blog and classes this theme does not style.
 *
 * @param WP_Comment $comment
 * @param array      $args
 * @param int        $depth
 */
function guide_render_comment( $comment, $args, $depth ) {
	$is_staff = class_exists( 'Guide\\Community\\Discussion' )
		&& \Guide\Community\Discussion::is_staff_comment( $comment );
	?>
	<li id="comment-<?php comment_ID(); ?>" <?php comment_class( 'guide-comment' ); ?>>
		<?php echo guide_avatar( (int) $comment->user_id, 40 ); ?>

		<div class="guide-comment__body">
			<div class="guide-comment__head">
				<span class="guide-comment__author"><?php echo esc_html( get_comment_author( $comment ) ); ?></span>

				<?php if ( $is_staff ) : ?>
					<span class="guide-comment__badge"><?php esc_html_e( 'Staff', 'guide-wp-theme' ); ?></span>
				<?php endif; ?>

				<time class="guide-comment__date" datetime="<?php comment_time( 'c' ); ?>">
					<?php
					printf(
						/* translators: %s: human-readable time difference. */
						esc_html__( '%s ago', 'guide-wp-theme' ),
						esc_html( human_time_diff( get_comment_time( 'U' ) ) )
					);
					?>
				</time>
			</div>

			<?php if ( '0' === $comment->comment_approved ) : ?>
				<p class="guide-comment__pending"><?php esc_html_e( 'Waiting to be reviewed — only you can see this.', 'guide-wp-theme' ); ?></p>
			<?php endif; ?>

			<div class="guide-comment__text"><?php comment_text(); ?></div>

			<?php if ( is_user_logged_in() && $depth < (int) $args['max_depth'] ) : ?>
				<div class="guide-comment__actions">
					<?php
					comment_reply_link(
						array_merge(
							$args,
							array(
								'depth'      => $depth,
								'max_depth'  => $args['max_depth'],
								'reply_text' => esc_html__( 'Reply', 'guide-wp-theme' ),
								'before'     => '<span class="guide-comment__action">',
								'after'      => '</span>',
							),
							array( 'add_below' => 'comment' )
						)
					);
					?>
				</div>
			<?php endif; ?>
	<?php
}

/** Close the element guide_render_comment() opened. */
function guide_render_comment_end() {
	echo '</div></li>';
}

/**
 * Inline a decorative illustration from assets/svg/.
 *
 * Inlined rather than served as an <img> for three reasons: the artwork
 * inherits currentColor so it themes itself in light and dark without a second
 * file; CSS can animate its individual parts, which an <img> cannot; and it
 * costs no extra request on a connection where every request is the expensive
 * part.
 *
 * These are decorations, never information. Everything they depict is also
 * stated in text nearby, so each is marked aria-hidden and a screen reader
 * skips it entirely rather than being read a shape.
 *
 * @param string $name  File name without the extension.
 * @param string $class Extra classes for the wrapper.
 */
function guide_illustration( $name, $class = '' ) {
	static $cache = array();

	// Never let a caller reach outside the illustration directory.
	$name = preg_replace( '/[^a-z0-9-]/', '', strtolower( (string) $name ) );

	if ( '' === $name ) {
		return '';
	}

	if ( ! isset( $cache[ $name ] ) ) {
		$file = get_theme_file_path( '/assets/svg/' . $name . '.svg' );

		$cache[ $name ] = ( $file && file_exists( $file ) )
			? trim( (string) file_get_contents( $file ) ) // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
			: '';
	}

	if ( '' === $cache[ $name ] ) {
		return '';
	}

	$svg = $cache[ $name ];

	if ( '' !== $class ) {
		$svg = preg_replace( '/class="/', 'class="' . esc_attr( $class ) . ' ', $svg, 1 );
	}

	// The files are ours, committed to the repository, and never user-supplied
	// — the sanitiser exists so that stays true if that ever changes.
	return wp_kses( $svg, guide_svg_allowed_html() );
}

/**
 * The tag and attribute set our own illustrations use.
 *
 * Deliberately no <script>, no <foreignObject>, no event handlers and no
 * <use> — the elements that turn an SVG from a picture into a program.
 *
 * @return array<string, array<string, bool>>
 */
function guide_svg_allowed_html() {
	$shared = array(
		'class'            => true,
		'style'            => true,
		'fill'             => true,
		'stroke'           => true,
		'stroke-width'     => true,
		'stroke-linecap'   => true,
		'stroke-linejoin'  => true,
		'stroke-dasharray' => true,
		'opacity'          => true,
		'transform'        => true,
	);

	return array(
		'svg'      => array_merge(
			$shared,
			array(
				'viewbox'             => true,
				'xmlns'               => true,
				'width'               => true,
				'height'              => true,
				'aria-hidden'         => true,
				'focusable'           => true,
				'role'                => true,
				'preserveaspectratio' => true,
			)
		),
		'g'        => $shared,
		'path'     => array_merge( $shared, array( 'd' => true ) ),
		'circle'   => array_merge( $shared, array( 'cx' => true, 'cy' => true, 'r' => true ) ),
		'rect'     => array_merge( $shared, array( 'x' => true, 'y' => true, 'width' => true, 'height' => true, 'rx' => true, 'ry' => true ) ),
		'line'     => array_merge( $shared, array( 'x1' => true, 'y1' => true, 'x2' => true, 'y2' => true ) ),
		'polyline' => array_merge( $shared, array( 'points' => true ) ),
		'polygon'  => array_merge( $shared, array( 'points' => true ) ),
		'ellipse'  => array_merge( $shared, array( 'cx' => true, 'cy' => true, 'rx' => true, 'ry' => true ) ),
	);
}
