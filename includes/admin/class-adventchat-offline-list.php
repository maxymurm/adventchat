<?php
/**
 * Offline messages admin list — WP_List_Table.
 *
 * WP-56: Lists offline messages with bulk actions and unread badge.
 *
 * @package AdventChat
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class AdventChat_Offline_List extends WP_List_Table {

	public function __construct() {
		parent::__construct( array(
			'singular' => 'offline_message',
			'plural'   => 'offline_messages',
			'ajax'     => false,
		) );
	}

	/**
	 * Register admin page and hooks.
	 */
	public static function init(): void {
		add_action( 'admin_menu', array( __CLASS__, 'add_submenu' ) );
		add_action( 'admin_post_adventchat_mark_offline_read', array( __CLASS__, 'handle_mark_read' ) );
	}

	public static function add_submenu(): void {
		$unread = self::unread_count();
		$badge  = $unread > 0 ? ' <span class="awaiting-mod">' . absint( $unread ) . '</span>' : '';

		add_submenu_page(
			'adventchat',
			__( 'Offline Messages', 'adventchat' ),
			__( 'Offline Messages', 'adventchat' ) . $badge,
			'manage_options',
			'adventchat-offline',
			array( __CLASS__, 'render_page' )
		);
	}

	/**
	 * Count unread offline messages.
	 */
	public static function unread_count(): int {
		global $wpdb;
		$table = $wpdb->prefix . 'adventchat_offline_messages';
		return (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table} WHERE status = 'unread'" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- table name is safe
	}

	public function get_columns(): array {
		return array(
			'cb'         => '<input type="checkbox" />',
			'name'       => __( 'Name', 'adventchat' ),
			'email'      => __( 'Email', 'adventchat' ),
			'message'    => __( 'Message', 'adventchat' ),
			'department' => __( 'Department', 'adventchat' ),
			'created_at' => __( 'Date', 'adventchat' ),
			'status'     => __( 'Status', 'adventchat' ),
		);
	}

	public function get_sortable_columns(): array {
		return array(
			'created_at' => array( 'created_at', true ),
			'name'       => array( 'name', false ),
		);
	}

	protected function get_bulk_actions(): array {
		return array(
			'mark_read' => __( 'Mark as Read', 'adventchat' ),
			'delete'    => __( 'Delete', 'adventchat' ),
		);
	}

	protected function column_cb( $item ): string {
		return '<input type="checkbox" name="ids[]" value="' . absint( $item['id'] ) . '" />';
	}

	protected function column_default( $item, $column_name ): string {
		return esc_html( $item[ $column_name ] ?? '' );
	}

	protected function column_message( $item ): string {
		return esc_html( wp_trim_words( $item['message'] ?? '', 20, '…' ) );
	}

	protected function column_status( $item ): string {
		return 'read' === ( $item['status'] ?? '' ) ? __( 'Read', 'adventchat' ) : '<strong>' . __( 'Unread', 'adventchat' ) . '</strong>';
	}

	protected function column_created_at( $item ): string {
		if ( empty( $item['created_at'] ) ) {
			return '—';
		}
		return esc_html( wp_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $item['created_at'] ) ) );
	}

	public function prepare_items(): void {
		global $wpdb;
		$table = $wpdb->prefix . 'adventchat_offline_messages';

		$per_page = 20;
		$current  = $this->get_pagenum();
		$offset   = ( $current - 1 ) * $per_page;

		$orderby = isset( $_REQUEST['orderby'] ) && in_array( $_REQUEST['orderby'], array( 'created_at', 'name' ), true )
			? sanitize_sql_orderby( $_REQUEST['orderby'] )
			: 'created_at';
		$order = isset( $_REQUEST['order'] ) && 'asc' === strtolower( $_REQUEST['order'] ) ? 'ASC' : 'DESC';

		$total = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table}" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared

		$this->items = $wpdb->get_results(
			$wpdb->prepare( "SELECT * FROM {$table} ORDER BY {$orderby} {$order} LIMIT %d OFFSET %d", $per_page, $offset ), // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			ARRAY_A
		);

		$this->set_pagination_args( array(
			'total_items' => $total,
			'per_page'    => $per_page,
			'total_pages' => ceil( $total / $per_page ),
		) );

		$this->_column_headers = array( $this->get_columns(), array(), $this->get_sortable_columns() );
	}

	/**
	 * Process bulk actions.
	 */
	public function process_bulk_action(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$action = $this->current_action();
		if ( ! $action ) {
			return;
		}

		check_admin_referer( 'bulk-offline_messages' );

		$ids = array_map( 'absint', (array) ( $_REQUEST['ids'] ?? array() ) );
		if ( empty( $ids ) ) {
			return;
		}

		global $wpdb;
		$table        = $wpdb->prefix . 'adventchat_offline_messages';
		$placeholders = implode( ',', array_fill( 0, count( $ids ), '%d' ) );

		if ( 'mark_read' === $action ) {
			$wpdb->query( $wpdb->prepare( "UPDATE {$table} SET status = 'read' WHERE id IN ({$placeholders})", ...$ids ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		} elseif ( 'delete' === $action ) {
			$wpdb->query( $wpdb->prepare( "DELETE FROM {$table} WHERE id IN ({$placeholders})", ...$ids ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		}
	}

	public static function handle_mark_read(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Unauthorized', 'adventchat' ) );
		}
		check_admin_referer( 'adventchat_mark_offline_read' );
		$id = absint( $_GET['id'] ?? 0 );
		if ( $id ) {
			global $wpdb;
			$wpdb->update( $wpdb->prefix . 'adventchat_offline_messages', array( 'status' => 'read' ), array( 'id' => $id ) );
		}
		wp_safe_redirect( admin_url( 'admin.php?page=adventchat-offline' ) );
		exit;
	}

	public static function render_page(): void {
		$table = new self();
		$table->process_bulk_action();
		$table->prepare_items();
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Offline Messages', 'adventchat' ); ?></h1>
			<form method="post">
				<?php
				$table->display();
				?>
			</form>
		</div>
		<?php
	}
}
