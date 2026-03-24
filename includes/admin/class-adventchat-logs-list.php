<?php
/**
 * Chat logs admin list — WP_List_Table.
 *
 * WP-59: Filterable chat log with date/agent/dept/rating filters and CSV export.
 *
 * @package AdventChat
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class AdventChat_Logs_List extends WP_List_Table {

	public function __construct() {
		parent::__construct( array(
			'singular' => 'chat_log',
			'plural'   => 'chat_logs',
			'ajax'     => false,
		) );
	}

	public static function init(): void {
		add_action( 'admin_menu', array( __CLASS__, 'add_submenu' ) );
		add_action( 'admin_post_adventchat_export_logs', array( __CLASS__, 'export_csv' ) );
	}

	public static function add_submenu(): void {
		add_submenu_page(
			'adventchat',
			__( 'Chat Logs', 'adventchat' ),
			__( 'Chat Logs', 'adventchat' ),
			'manage_options',
			'adventchat-logs',
			array( __CLASS__, 'render_page' )
		);
	}

	public function get_columns(): array {
		return array(
			'visitor_name'  => __( 'Visitor', 'adventchat' ),
			'agent_name'    => __( 'Agent', 'adventchat' ),
			'department'    => __( 'Department', 'adventchat' ),
			'message_count' => __( 'Messages', 'adventchat' ),
			'rating'        => __( 'Rating', 'adventchat' ),
			'duration_seconds' => __( 'Duration', 'adventchat' ),
			'created_at'    => __( 'Date', 'adventchat' ),
		);
	}

	public function get_sortable_columns(): array {
		return array(
			'created_at' => array( 'created_at', true ),
			'rating'     => array( 'rating', false ),
		);
	}

	protected function column_default( $item, $column_name ): string {
		return esc_html( $item[ $column_name ] ?? '—' );
	}

	protected function column_rating( $item ): string {
		$r = (int) ( $item['rating'] ?? 0 );
		return $r > 0 ? str_repeat( '★', $r ) . str_repeat( '☆', 5 - $r ) : '—';
	}

	protected function column_duration_seconds( $item ): string {
		$seconds = (int) ( $item['duration_seconds'] ?? 0 );
		if ( $seconds < 60 ) {
			return $seconds . 's';
		}
		return floor( $seconds / 60 ) . 'm ' . ( $seconds % 60 ) . 's';
	}

	protected function column_created_at( $item ): string {
		if ( empty( $item['created_at'] ) ) {
			return '—';
		}
		return esc_html( wp_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $item['created_at'] ) ) );
	}

	/**
	 * Extra controls above the table (date + filters).
	 */
	protected function extra_tablenav( $which ): void {
		if ( 'top' !== $which ) {
			return;
		}
		$agent = sanitize_text_field( wp_unslash( $_GET['filter_agent'] ?? '' ) );
		$dept  = sanitize_text_field( wp_unslash( $_GET['filter_dept'] ?? '' ) );
		$from  = sanitize_text_field( wp_unslash( $_GET['filter_from'] ?? '' ) );
		$to    = sanitize_text_field( wp_unslash( $_GET['filter_to'] ?? '' ) );
		?>
		<div class="alignleft actions">
			<input type="date" name="filter_from" value="<?php echo esc_attr( $from ); ?>" placeholder="<?php esc_attr_e( 'From', 'adventchat' ); ?>" />
			<input type="date" name="filter_to"   value="<?php echo esc_attr( $to ); ?>"   placeholder="<?php esc_attr_e( 'To', 'adventchat' ); ?>" />
			<input type="text" name="filter_agent" value="<?php echo esc_attr( $agent ); ?>" placeholder="<?php esc_attr_e( 'Agent', 'adventchat' ); ?>" />
			<input type="text" name="filter_dept"  value="<?php echo esc_attr( $dept ); ?>"  placeholder="<?php esc_attr_e( 'Department', 'adventchat' ); ?>" />
			<?php submit_button( __( 'Filter', 'adventchat' ), '', 'filter_action', false ); ?>
		</div>
		<div class="alignright">
			<a class="button" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=adventchat_export_logs' ), 'adventchat_export_logs' ) ); ?>"><?php esc_html_e( 'Export CSV', 'adventchat' ); ?></a>
		</div>
		<?php
	}

	public function prepare_items(): void {
		global $wpdb;
		$table = $wpdb->prefix . 'adventchat_chat_logs';

		$per_page = 20;
		$current  = $this->get_pagenum();
		$offset   = ( $current - 1 ) * $per_page;

		$where = array( '1=1' );
		$args  = array();

		if ( ! empty( $_REQUEST['filter_agent'] ) ) {
			$where[] = 'agent_name LIKE %s';
			$args[]  = '%' . $wpdb->esc_like( sanitize_text_field( wp_unslash( $_REQUEST['filter_agent'] ) ) ) . '%';
		}
		if ( ! empty( $_REQUEST['filter_dept'] ) ) {
			$where[] = 'department LIKE %s';
			$args[]  = '%' . $wpdb->esc_like( sanitize_text_field( wp_unslash( $_REQUEST['filter_dept'] ) ) ) . '%';
		}
		if ( ! empty( $_REQUEST['filter_from'] ) ) {
			$where[] = 'created_at >= %s';
			$args[]  = sanitize_text_field( wp_unslash( $_REQUEST['filter_from'] ) ) . ' 00:00:00';
		}
		if ( ! empty( $_REQUEST['filter_to'] ) ) {
			$where[] = 'created_at <= %s';
			$args[]  = sanitize_text_field( wp_unslash( $_REQUEST['filter_to'] ) ) . ' 23:59:59';
		}

		$where_sql = implode( ' AND ', $where );
		$orderby   = isset( $_REQUEST['orderby'] ) && in_array( $_REQUEST['orderby'], array( 'created_at', 'rating' ), true )
			? sanitize_sql_orderby( $_REQUEST['orderby'] )
			: 'created_at';
		$order = isset( $_REQUEST['order'] ) && 'asc' === strtolower( $_REQUEST['order'] ) ? 'ASC' : 'DESC';

		$count_args = $args;
		$total = (int) $wpdb->get_var(
			empty( $args )
				? "SELECT COUNT(*) FROM {$table} WHERE {$where_sql}" // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				: $wpdb->prepare( "SELECT COUNT(*) FROM {$table} WHERE {$where_sql}", ...$count_args ) // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		);

		$args[] = $per_page;
		$args[] = $offset;

		$sql = "SELECT * FROM {$table} WHERE {$where_sql} ORDER BY {$orderby} {$order} LIMIT %d OFFSET %d"; // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared

		$this->items = $wpdb->get_results( $wpdb->prepare( $sql, ...$args ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

		$this->set_pagination_args( array(
			'total_items' => $total,
			'per_page'    => $per_page,
			'total_pages' => ceil( $total / $per_page ),
		) );

		$this->_column_headers = array( $this->get_columns(), array(), $this->get_sortable_columns() );
	}

	/**
	 * CSV export.
	 */
	public static function export_csv(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Unauthorized', 'adventchat' ) );
		}
		check_admin_referer( 'adventchat_export_logs' );

		global $wpdb;
		$table = $wpdb->prefix . 'adventchat_chat_logs';
		$rows  = $wpdb->get_results( "SELECT * FROM {$table} ORDER BY created_at DESC", ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared

		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename=adventchat-logs-' . gmdate( 'Y-m-d' ) . '.csv' );

		$output = fopen( 'php://output', 'w' );
		if ( ! empty( $rows ) ) {
			fputcsv( $output, array_keys( $rows[0] ) );
			foreach ( $rows as $row ) {
				fputcsv( $output, $row );
			}
		}
		fclose( $output );
		exit;
	}

	public static function render_page(): void {
		$table = new self();
		$table->prepare_items();
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Chat Logs', 'adventchat' ); ?></h1>
			<form method="get">
				<input type="hidden" name="page" value="adventchat-logs" />
				<?php $table->display(); ?>
			</form>
		</div>
		<?php
	}
}
