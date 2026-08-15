<?php
/**
 * Lesson settings: video URL, duration, and free-preview flag.
 * Registered as post meta (REST-visible) + a classic metabox for editing.
 */

namespace JSL;

defined( 'ABSPATH' ) || exit;

class Lesson_Meta {

	const FIELDS = array(
		'jsl_lesson_type'      => 'string',  // article | video | quiz
		'jsl_video_url'        => 'string',
		'jsl_video_start'      => 'integer', // seconds; 0 = from beginning
		'jsl_video_end'        => 'integer', // seconds; 0 = to the end
		'jsl_duration_minutes' => 'integer',
		'jsl_is_preview'       => 'boolean',
	);

	public static function init() {
		add_action( 'init', array( __CLASS__, 'register_meta' ) );
		add_action( 'add_meta_boxes', array( __CLASS__, 'add_metabox' ) );
		add_action( 'save_post_lesson', array( __CLASS__, 'save' ) );
	}

	public static function register_meta() {
		foreach ( self::FIELDS as $key => $type ) {
			register_post_meta(
				'lesson',
				$key,
				array(
					'type'              => $type,
					'single'            => true,
					'show_in_rest'      => true,
					'sanitize_callback' => 'jsl_video_url' === $key ? 'esc_url_raw' : ( 'string' === $type ? 'sanitize_text_field' : null ),
					'auth_callback'     => function () {
						return current_user_can( 'edit_posts' );
					},
				)
			);
		}
	}

	public static function add_metabox() {
		add_meta_box(
			'jsl-lesson-settings',
			__( 'Lesson Settings', 'job-seekers-lms' ),
			array( __CLASS__, 'render_metabox' ),
			'lesson',
			'side'
		);
	}

	public static function render_metabox( $post ) {
		wp_nonce_field( 'jsl_lesson_meta', 'jsl_lesson_meta_nonce' );

		$video    = get_post_meta( $post->ID, 'jsl_video_url', true );
		$duration = (int) get_post_meta( $post->ID, 'jsl_duration_minutes', true );
		$preview  = (bool) get_post_meta( $post->ID, 'jsl_is_preview', true );
		?>
		<p>
			<label for="jsl_video_url"><strong><?php esc_html_e( 'Video URL', 'job-seekers-lms' ); ?></strong></label><br>
			<input type="url" id="jsl_video_url" name="jsl_video_url" value="<?php echo esc_attr( $video ); ?>" class="widefat" placeholder="https://youtube.com/watch?v=…">
			<span class="description"><?php esc_html_e( 'YouTube, Vimeo, or direct .mp4 link.', 'job-seekers-lms' ); ?></span>
		</p>
		<p>
			<label for="jsl_duration_minutes"><strong><?php esc_html_e( 'Duration (minutes)', 'job-seekers-lms' ); ?></strong></label><br>
			<input type="number" min="0" id="jsl_duration_minutes" name="jsl_duration_minutes" value="<?php echo esc_attr( $duration ?: '' ); ?>" class="widefat">
		</p>
		<p>
			<label>
				<input type="checkbox" name="jsl_is_preview" value="1" <?php checked( $preview ); ?>>
				<?php esc_html_e( 'Free preview (visible without enrolling)', 'job-seekers-lms' ); ?>
			</label>
		</p>
		<?php
	}

	public static function save( $post_id ) {
		if (
			! isset( $_POST['jsl_lesson_meta_nonce'] ) ||
			! wp_verify_nonce( sanitize_key( $_POST['jsl_lesson_meta_nonce'] ), 'jsl_lesson_meta' ) ||
			defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ||
			! current_user_can( 'edit_post', $post_id )
		) {
			return;
		}

		update_post_meta( $post_id, 'jsl_video_url', esc_url_raw( wp_unslash( $_POST['jsl_video_url'] ?? '' ) ) );
		update_post_meta( $post_id, 'jsl_duration_minutes', (int) ( $_POST['jsl_duration_minutes'] ?? 0 ) );
		update_post_meta( $post_id, 'jsl_is_preview', isset( $_POST['jsl_is_preview'] ) ? 1 : 0 );
	}

	/**
	 * Turn a video URL into an embeddable iframe/video src, honoring the
	 * lesson's start/end clip range. YouTube goes through the privacy
	 * domain with minimal branding; the theme wraps everything in its own
	 * click-to-play facade player.
	 *
	 * @return array{type:string, src:string, poster:?string, start:int, end:int}|null
	 */
	public static function embed_info( string $url, int $start = 0, int $end = 0 ): ?array {
		if ( ! $url ) {
			return null;
		}

		if ( preg_match( '~(?:youtube\.com/(?:watch\?v=|embed/|shorts/)|youtu\.be/)([\w-]{6,})~', $url, $m ) ) {
			$args = array(
				'rel'            => 0,
				'modestbranding' => 1,
				'iv_load_policy' => 3,
				'playsinline'    => 1,
				'autoplay'       => 1,
			);
			if ( $start > 0 ) {
				$args['start'] = $start;
			}
			if ( $end > $start ) {
				$args['end'] = $end;
			}
			return array(
				'type'   => 'iframe',
				'src'    => add_query_arg( $args, 'https://www.youtube-nocookie.com/embed/' . $m[1] ),
				'poster' => 'https://i.ytimg.com/vi/' . $m[1] . '/maxresdefault.jpg',
				'start'  => $start,
				'end'    => $end,
			);
		}

		if ( preg_match( '~vimeo\.com/(?:video/)?(\d+)~', $url, $m ) ) {
			$src = add_query_arg( array( 'autoplay' => 1, 'dnt' => 1 ), 'https://player.vimeo.com/video/' . $m[1] );
			if ( $start > 0 ) {
				$src .= '#t=' . $start . 's';
			}
			return array( 'type' => 'iframe', 'src' => $src, 'poster' => null, 'start' => $start, 'end' => $end );
		}

		if ( preg_match( '~\.(mp4|webm|ogv)(\?|$)~', $url ) ) {
			return array( 'type' => 'video', 'src' => $url, 'poster' => null, 'start' => $start, 'end' => $end );
		}

		return array( 'type' => 'iframe', 'src' => $url, 'poster' => null, 'start' => $start, 'end' => $end );
	}
}
