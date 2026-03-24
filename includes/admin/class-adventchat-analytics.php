<?php
/**
 * Analytics dashboard widget.
 *
 * WP-84: WP Dashboard widget showing today's chats, avg rating, response time,
 * online agents. Includes a 7-day Chart.js chart. Data from Firestore, 5min cache.
 *
 * @package AdventChat
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class AdventChat_Analytics
 */
class AdventChat_Analytics {

	/**
	 * Cache key prefix.
	 *
	 * @var string
	 */
	private const CACHE_KEY = 'adventchat_analytics';

	/**
	 * Cache duration in seconds (5 minutes).
	 *
	 * @var int
	 */
	private const CACHE_TTL = 300;

	/**
	 * Initialize hooks.
	 */
	public static function init(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		add_action( 'wp_dashboard_setup', array( __CLASS__, 'register_widget' ) );
	}

	/**
	 * Register the dashboard widget.
	 */
	public static function register_widget(): void {
		wp_add_dashboard_widget(
			'adventchat_analytics',
			__( 'AdventChat Analytics', 'adventchat' ),
			array( __CLASS__, 'render_widget' )
		);
	}

	/**
	 * Render the dashboard widget.
	 */
	public static function render_widget(): void {
		$stats = self::get_stats();

		echo '<div class="adventchat-analytics">';

		// Quick stats boxes.
		echo '<div style="display:grid;grid-template-columns:repeat(2,1fr);gap:12px;margin-bottom:16px;">';
		self::render_stat_box( __( "Today's Chats", 'adventchat' ), $stats['today_chats'] );
		self::render_stat_box( __( 'Avg Rating', 'adventchat' ), $stats['avg_rating'] . ' ★' );
		self::render_stat_box( __( 'Avg Response', 'adventchat' ), $stats['avg_response_time'] );
		self::render_stat_box( __( 'Online Agents', 'adventchat' ), $stats['online_agents'] );
		echo '</div>';

		// 7-day chart.
		echo '<canvas id="adventchat-chart" height="150"></canvas>';

		// Enqueue Chart.js from CDN.
		$chart_data = wp_json_encode( $stats['daily'] );
		echo '<script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>';
		echo '<script>
			document.addEventListener("DOMContentLoaded", function() {
				var data = ' . $chart_data . ';
				var labels = data.map(function(d){ return d.date; });
				var values = data.map(function(d){ return d.chats; });
				new Chart(document.getElementById("adventchat-chart"), {
					type: "bar",
					data: {
						labels: labels,
						datasets: [{
							label: "Chats",
							data: values,
							backgroundColor: "rgba(0, 102, 255, 0.7)",
							borderRadius: 4
						}]
					},
					options: {
						responsive: true,
						plugins: { legend: { display: false } },
						scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
					}
				});
			});
		</script>';

		echo '</div>';
	}

	/**
	 * Render a single stat box.
	 *
	 * @param string $label Label.
	 * @param string $value Value.
	 */
	private static function render_stat_box( string $label, string $value ): void {
		printf(
			'<div style="background:#f8fafc;border:1px solid #e5e7eb;border-radius:8px;padding:12px;text-align:center;"><div style="font-size:20px;font-weight:700;color:#1a1a1a;">%s</div><div style="font-size:11px;color:#6b7280;margin-top:4px;">%s</div></div>',
			esc_html( $value ),
			esc_html( $label )
		);
	}

	/**
	 * Get analytics stats (cached).
	 *
	 * @return array
	 */
	private static function get_stats(): array {
		$cached = get_transient( self::CACHE_KEY );
		if ( is_array( $cached ) ) {
			return $cached;
		}

		global $wpdb;
		$table = $wpdb->prefix . 'adventchat_chat_logs';

		// Today's chats.
		$today = gmdate( 'Y-m-d' );
		$today_chats = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$table} WHERE DATE(started_at) = %s",
				$today
			)
		);

		// Average rating (non-zero) — no user input in query; table name from $wpdb->prefix.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$avg_rating = (float) $wpdb->get_var(
			"SELECT AVG(rating) FROM `{$table}` WHERE rating > 0"
		);

		// Average response time — no user input; table name from $wpdb->prefix.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$avg_duration = (int) $wpdb->get_var(
			"SELECT AVG(duration_seconds) FROM `{$table}` WHERE duration_seconds > 0"
		);

		// Format response time.
		if ( $avg_duration > 60 ) {
			$avg_response_time = round( $avg_duration / 60 ) . 'm';
		} else {
			$avg_response_time = $avg_duration . 's';
		}

		// Online agents count (from Firestore - use transient to cache).
		$online_agents = self::count_online_agents();

		// 7-day chart data.
		$daily = array();
		for ( $i = 6; $i >= 0; $i-- ) {
			$date  = gmdate( 'Y-m-d', strtotime( "-{$i} days" ) );
			$label = gmdate( 'M j', strtotime( "-{$i} days" ) );
			$count = (int) $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*) FROM {$table} WHERE DATE(started_at) = %s",
					$date
				)
			);
			$daily[] = array( 'date' => $label, 'chats' => $count );
		}

		$stats = array(
			'today_chats'       => (string) $today_chats,
			'avg_rating'        => number_format( $avg_rating, 1 ),
			'avg_response_time' => $avg_response_time ?: '0s',
			'online_agents'     => (string) $online_agents,
			'daily'             => $daily,
		);

		set_transient( self::CACHE_KEY, $stats, self::CACHE_TTL );

		return $stats;
	}

	/**
	 * Count online agents from the WP user meta or operator status.
	 *
	 * @return int
	 */
	private static function count_online_agents(): int {
		// Query users with the adventchat_operator role and online status transient.
		$agents = get_users( array(
			'role__in' => array( 'adventchat_operator', 'adventchat_supervisor', 'administrator' ),
			'fields'   => 'ID',
		) );

		$online = 0;
		foreach ( $agents as $id ) {
			$status = get_transient( 'adventchat_agent_status_' . $id );
			if ( 'online' === $status ) {
				$online++;
			}
		}

		return $online;
	}
}
