<?php
/**
 * LMS → Feedback.
 *
 * Collecting feedback and never reading it is worse than not collecting it —
 * it costs the learner the effort of writing and gives nothing back. This is
 * the queue, plus the numbers that say which lessons are landing and which are
 * not.
 *
 * The two views answer different questions:
 *
 *   Messages   — what did somebody actually say, and have I dealt with it?
 *   By content — where is the problem, ranked by how loudly people are saying so?
 */

namespace Guide\Admin;

use Guide\Community\Feedback;

defined( 'ABSPATH' ) || exit;

class Feedback_Page {

	const SLUG = 'guide-feedback';

	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'register_menu' ), 28 );
		add_action( 'admin_post_guide_feedback_status', array( __CLASS__, 'handle_status' ) );
	}

	public static function register_menu() {
		$unread = Feedback::unread_count();

		$label = __( 'Feedback', 'guide-lms' );

		// An unread count in the menu is the difference between a queue you
		// clear and a queue you forget exists.
		if ( $unread ) {
			$label .= ' <span class="update-plugins count-' . (int) $unread . '"><span class="update-count">'
				. number_format_i18n( $unread ) . '</span></span>';
		}

		add_submenu_page(
			'guide-lms',
			__( 'Feedback', 'guide-lms' ),
			$label,
			'edit_posts',
			self::SLUG,
			array( __CLASS__, 'render' )
		);
	}

	public static function url( array $args = array() ): string {
		return add_query_arg( array_merge( array( 'page' => self::SLUG ), $args ), admin_url( 'admin.php' ) );
	}

	/**
	 * Mark one message read or actioned.
	 */
	public static function handle_status() {
		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_die( esc_html__( 'You do not have permission to do that.', 'guide-lms' ) );
		}

		$id     = isset( $_GET['id'] ) ? (int) $_GET['id'] : 0;
		$status = isset( $_GET['status'] ) ? sanitize_key( wp_unslash( $_GET['status'] ) ) : 'read';

		check_admin_referer( 'guide_feedback_' . $id );

		if ( $id ) {
			Feedback::set_status( $id, $status );
		}

		wp_safe_redirect( wp_get_referer() ? wp_get_referer() : self::url() );
		exit;
	}

	// -------------------------------------------------------------------------
	// Aggregates
	// -------------------------------------------------------------------------

	/**
	 * Reaction totals per piece of content, worst ratio first.
	 *
	 * Ranked by *negative* reactions rather than by ratio alone: a lesson with
	 * 2 down out of 3 looks terrible but says almost nothing, while 40 down out
	 * of 200 is a real signal about a real audience.
	 *
	 * @return array<int, array<string,mixed>>
	 */
	private static function by_content( int $limit = 40 ): array {
		global $wpdb;

		$table = Feedback::table_name();

		$rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT object_type, object_id,
				        SUM(sentiment = 'up')   AS ups,
				        SUM(sentiment = 'down') AS downs,
				        COUNT(*)                AS total
				   FROM {$table}
				  WHERE object_type <> 'roadmap_item'
				  GROUP BY object_type, object_id
				  ORDER BY downs DESC, total DESC
				  LIMIT %d",
				$limit
			),
			ARRAY_A
		);

		return is_array( $rows ) ? $rows : array();
	}

	/** @return array{up:int, down:int, messages:int, unread:int} */
	private static function totals(): array {
		global $wpdb;

		$table = Feedback::table_name();

		$row = $wpdb->get_row(
			"SELECT SUM(sentiment = 'up')   AS ups,
			        SUM(sentiment = 'down') AS downs,
			        SUM(message IS NOT NULL AND message <> '') AS messages
			   FROM {$table}
			  WHERE object_type <> 'roadmap_item'",
			ARRAY_A
		);

		return array(
			'up'       => (int) ( $row['ups'] ?? 0 ),
			'down'     => (int) ( $row['downs'] ?? 0 ),
			'messages' => (int) ( $row['messages'] ?? 0 ),
			'unread'   => Feedback::unread_count(),
		);
	}

	// -------------------------------------------------------------------------
	// Render
	// -------------------------------------------------------------------------

	public static function render() {
		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_die( esc_html__( 'You do not have permission to view this page.', 'guide-lms' ) );
		}

		$filter   = isset( $_GET['status'] ) ? sanitize_key( wp_unslash( $_GET['status'] ) ) : 'new'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$filter   = in_array( $filter, array( 'new', 'read', 'actioned', 'all' ), true ) ? $filter : 'new';
		$messages = Feedback::messages( 'all' === $filter ? '' : $filter, 200 );
		$totals   = self::totals();
		$content  = self::by_content();
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Feedback', 'guide-lms' ); ?></h1>
			<p class="description" style="max-width:44rem">
				<?php esc_html_e( 'What learners said about lessons, courses, company guides and help articles. A thumbs-down with a sentence attached is the most useful thing on this page — it is somebody telling you exactly where the material fails.', 'guide-lms' ); ?>
			</p>

			<!-- Totals ------------------------------------------------------- -->
			<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(10rem,1fr));gap:12px;margin:18px 0 24px">
				<?php
				$cards = array(
					array( __( 'Useful', 'guide-lms' ), $totals['up'], '#2f9e64' ),
					array( __( 'Not useful', 'guide-lms' ), $totals['down'], '#b47b11' ),
					array( __( 'Written notes', 'guide-lms' ), $totals['messages'], '' ),
					array( __( 'Unread', 'guide-lms' ), $totals['unread'], $totals['unread'] ? '#d0413c' : '' ),
				);

				foreach ( $cards as $card ) :
					?>
					<div style="background:#fff;border:1px solid #e2e3ee;border-radius:8px;padding:14px 16px">
						<div style="font-size:11px;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:#5a5d71">
							<?php echo esc_html( $card[0] ); ?>
						</div>
						<div style="font-size:26px;font-weight:800;margin-top:4px;<?php echo $card[2] ? 'color:' . esc_attr( $card[2] ) : ''; ?>">
							<?php echo esc_html( number_format_i18n( $card[1] ) ); ?>
						</div>
					</div>
				<?php endforeach; ?>
			</div>

			<!-- Where the problems are --------------------------------------- -->
			<h2><?php esc_html_e( 'Where the problems are', 'guide-lms' ); ?></h2>
			<p class="description">
				<?php esc_html_e( 'Ranked by how many people said "not useful", not by ratio — 2 out of 3 looks alarming and means little; 40 out of 200 is a real signal.', 'guide-lms' ); ?>
			</p>

			<table class="widefat striped" style="max-width:60rem;margin:10px 0 30px">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Content', 'guide-lms' ); ?></th>
						<th style="width:7rem"><?php esc_html_e( 'Type', 'guide-lms' ); ?></th>
						<th style="width:6rem"><?php esc_html_e( 'Useful', 'guide-lms' ); ?></th>
						<th style="width:8rem"><?php esc_html_e( 'Not useful', 'guide-lms' ); ?></th>
						<th style="width:10rem"><?php esc_html_e( 'Signal', 'guide-lms' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php if ( ! $content ) : ?>
						<tr><td colspan="5"><?php esc_html_e( 'No reactions yet.', 'guide-lms' ); ?></td></tr>
					<?php endif; ?>

					<?php foreach ( $content as $row ) : ?>
						<?php
						$post  = get_post( (int) $row['object_id'] );
						$ups   = (int) $row['ups'];
						$downs = (int) $row['downs'];
						$total = max( 1, (int) $row['total'] );
						$ratio = (int) round( $ups / $total * 100 );
						?>
						<tr>
							<td>
								<?php if ( $post ) : ?>
									<a href="<?php echo esc_url( (string) get_permalink( $post ) ); ?>" target="_blank" rel="noopener">
										<?php echo esc_html( get_the_title( $post ) ); ?>
									</a>
								<?php else : ?>
									<em><?php esc_html_e( 'deleted', 'guide-lms' ); ?></em>
								<?php endif; ?>
							</td>
							<td><?php echo esc_html( str_replace( '_', ' ', $row['object_type'] ) ); ?></td>
							<td><?php echo esc_html( number_format_i18n( $ups ) ); ?></td>
							<td<?php echo $downs > 0 ? ' style="font-weight:700"' : ''; ?>>
								<?php echo esc_html( number_format_i18n( $downs ) ); ?>
							</td>
							<td>
								<div style="background:#e2e3ee;border-radius:99px;height:8px;overflow:hidden">
									<div style="height:100%;width:<?php echo esc_attr( (string) $ratio ); ?>%;background:<?php echo $ratio >= 70 ? '#2f9e64' : ( $ratio >= 40 ? '#e9a92a' : '#d0413c' ); ?>"></div>
								</div>
								<small><?php echo esc_html( $ratio . '% useful' ); ?></small>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>

			<!-- Messages ----------------------------------------------------- -->
			<h2><?php esc_html_e( 'What people wrote', 'guide-lms' ); ?></h2>

			<ul class="subsubsub">
				<?php
				$filters = array(
					'new'      => __( 'Unread', 'guide-lms' ),
					'read'     => __( 'Read', 'guide-lms' ),
					'actioned' => __( 'Actioned', 'guide-lms' ),
					'all'      => __( 'All', 'guide-lms' ),
				);

				$last = array_key_last( $filters );

				foreach ( $filters as $key => $label ) :
					?>
					<li>
						<a href="<?php echo esc_url( self::url( array( 'status' => $key ) ) ); ?>"
							class="<?php echo $filter === $key ? 'current' : ''; ?>">
							<?php echo esc_html( $label ); ?>
						</a>
						<?php echo $key === $last ? '' : ' |'; ?>
					</li>
				<?php endforeach; ?>
			</ul>

			<table class="widefat striped" style="max-width:60rem;margin-top:10px">
				<thead>
					<tr>
						<th style="width:12rem"><?php esc_html_e( 'About', 'guide-lms' ); ?></th>
						<th><?php esc_html_e( 'What they said', 'guide-lms' ); ?></th>
						<th style="width:9rem"><?php esc_html_e( 'Who / when', 'guide-lms' ); ?></th>
						<th style="width:11rem"><?php esc_html_e( 'Status', 'guide-lms' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php if ( ! $messages ) : ?>
						<tr><td colspan="4"><?php esc_html_e( 'Nothing here.', 'guide-lms' ); ?></td></tr>
					<?php endif; ?>

					<?php foreach ( $messages as $message ) : ?>
						<?php
						$post = get_post( (int) $message['object_id'] );
						$user = get_userdata( (int) $message['user_id'] );
						$down = 'down' === $message['sentiment'];
						?>
						<tr>
							<td>
								<?php if ( $post ) : ?>
									<a href="<?php echo esc_url( (string) get_permalink( $post ) ); ?>" target="_blank" rel="noopener">
										<?php echo esc_html( get_the_title( $post ) ); ?>
									</a>
								<?php else : ?>
									<em><?php esc_html_e( 'deleted', 'guide-lms' ); ?></em>
								<?php endif; ?>
								<br>
								<small style="color:<?php echo $down ? '#b47b11' : '#2f9e64'; ?>">
									<?php echo $down ? esc_html__( 'Not useful', 'guide-lms' ) : esc_html__( 'Useful', 'guide-lms' ); ?>
								</small>
							</td>
							<td><?php echo esc_html( (string) $message['message'] ); ?></td>
							<td>
								<?php echo esc_html( $user ? $user->display_name : __( 'unknown', 'guide-lms' ) ); ?><br>
								<small>
									<?php
									printf(
										/* translators: %s: human-readable time difference. */
										esc_html__( '%s ago', 'guide-lms' ),
										esc_html( human_time_diff( strtotime( (string) $message['created_at'] . ' UTC' ) ) )
									);
									?>
								</small>
							</td>
							<td>
								<?php foreach ( array( 'read' => __( 'Mark read', 'guide-lms' ), 'actioned' => __( 'Actioned', 'guide-lms' ) ) as $status => $label ) : ?>
									<?php if ( $message['status'] === $status ) { continue; } ?>
									<a class="button button-small"
										href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=guide_feedback_status&id=' . (int) $message['id'] . '&status=' . $status ), 'guide_feedback_' . (int) $message['id'] ) ); ?>">
										<?php echo esc_html( $label ); ?>
									</a>
								<?php endforeach; ?>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
		<?php
	}
}
