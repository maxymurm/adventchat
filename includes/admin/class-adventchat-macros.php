<?php
/**
 * Macros admin page — WP_List_Table CRUD for canned responses.
 *
 * WP-52: Stores macros in wp_options, syncs to Firestore.
 *
 * @package AdventChat
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * Macros list table.
 */
class AdventChat_Macros_Table extends WP_List_Table {

	const OPTION_KEY = 'adventchat_macros';

	public function __construct() {
		parent::__construct( array(
			'singular' => 'macro',
			'plural'   => 'macros',
			'ajax'     => false,
		) );
	}

	/**
	 * Get all macros.
	 *
	 * @return array<int, array{id: string, shortcut: string, title: string, text: string}>
	 */
	public static function get_all(): array {
		return get_option( self::OPTION_KEY, array() );
	}

	public function get_columns(): array {
		return array(
			'shortcut' => __( 'Shortcut', 'adventchat' ),
			'title'    => __( 'Title', 'adventchat' ),
			'text'     => __( 'Text', 'adventchat' ),
			'actions'  => __( 'Actions', 'adventchat' ),
		);
	}

	public function prepare_items(): void {
		$this->_column_headers = array( $this->get_columns(), array(), array() );
		$this->items           = self::get_all();
	}

	protected function column_default( $item, $column_name ): string {
		return esc_html( $item[ $column_name ] ?? '' );
	}

	protected function column_text( $item ): string {
		return esc_html( wp_trim_words( $item['text'] ?? '', 15, '…' ) );
	}

	protected function column_actions( $item ): string {
		$edit_url   = wp_nonce_url(
			admin_url( 'admin.php?page=adventchat-macros&edit=' . $item['id'] ),
			'adventchat_edit_macro'
		);
		$delete_url = wp_nonce_url(
			admin_url( 'admin-post.php?action=adventchat_delete_macro&macro_id=' . $item['id'] ),
			'adventchat_delete_macro'
		);

		return sprintf(
			'<a href="%s">%s</a> | <a href="%s" style="color:#b32d2e;" onclick="return confirm(\'%s\');">%s</a>',
			esc_url( $edit_url ),
			esc_html__( 'Edit', 'adventchat' ),
			esc_url( $delete_url ),
			esc_attr__( 'Delete this macro?', 'adventchat' ),
			esc_html__( 'Delete', 'adventchat' )
		);
	}
}

/**
 * Macros admin page controller.
 */
class AdventChat_Macros {

	public static function init(): void {
		add_action( 'admin_menu', array( __CLASS__, 'add_submenu' ) );
		add_action( 'admin_post_adventchat_save_macro', array( __CLASS__, 'handle_save' ) );
		add_action( 'admin_post_adventchat_delete_macro', array( __CLASS__, 'handle_delete' ) );
	}

	public static function add_submenu(): void {
		add_submenu_page(
			'adventchat',
			__( 'Macros', 'adventchat' ),
			__( 'Macros', 'adventchat' ),
			'manage_options',
			'adventchat-macros',
			array( __CLASS__, 'render_page' )
		);
	}

	public static function render_page(): void {
		$table   = new AdventChat_Macros_Table();
		$editing = null;

		if ( isset( $_GET['edit'] ) && isset( $_GET['_wpnonce'] ) ) {
			if ( wp_verify_nonce( sanitize_key( $_GET['_wpnonce'] ), 'adventchat_edit_macro' ) ) {
				$edit_id = sanitize_text_field( wp_unslash( $_GET['edit'] ) );
				foreach ( AdventChat_Macros_Table::get_all() as $m ) {
					if ( $m['id'] === $edit_id ) {
						$editing = $m;
						break;
					}
				}
			}
		}

		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Macros (Canned Responses)', 'adventchat' ); ?></h1>

			<h2><?php echo $editing ? esc_html__( 'Edit Macro', 'adventchat' ) : esc_html__( 'Add Macro', 'adventchat' ); ?></h2>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<?php wp_nonce_field( 'adventchat_save_macro', '_adventchat_nonce' ); ?>
				<input type="hidden" name="action" value="adventchat_save_macro" />
				<?php if ( $editing ) : ?>
					<input type="hidden" name="macro_id" value="<?php echo esc_attr( $editing['id'] ); ?>" />
				<?php endif; ?>

				<table class="form-table">
					<tr>
						<th><label for="macro_shortcut"><?php esc_html_e( 'Shortcut', 'adventchat' ); ?></label></th>
						<td>
							<input type="text" id="macro_shortcut" name="macro_shortcut" value="<?php echo esc_attr( $editing['shortcut'] ?? '' ); ?>" class="regular-text" required />
							<p class="description"><?php esc_html_e( 'Type /shortcut in chat to trigger this macro.', 'adventchat' ); ?></p>
						</td>
					</tr>
					<tr>
						<th><label for="macro_title"><?php esc_html_e( 'Title', 'adventchat' ); ?></label></th>
						<td><input type="text" id="macro_title" name="macro_title" value="<?php echo esc_attr( $editing['title'] ?? '' ); ?>" class="regular-text" required /></td>
					</tr>
					<tr>
						<th><label for="macro_text"><?php esc_html_e( 'Text', 'adventchat' ); ?></label></th>
						<td><textarea id="macro_text" name="macro_text" rows="4" class="large-text" required><?php echo esc_textarea( $editing['text'] ?? '' ); ?></textarea></td>
					</tr>
				</table>

				<?php submit_button( $editing ? __( 'Update Macro', 'adventchat' ) : __( 'Add Macro', 'adventchat' ) ); ?>
			</form>

			<hr />
			<?php
			$table->prepare_items();
			$table->display();
			?>
		</div>
		<?php
	}

	public static function handle_save(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Unauthorized', 'adventchat' ) );
		}

		check_admin_referer( 'adventchat_save_macro', '_adventchat_nonce' );

		$macros   = AdventChat_Macros_Table::get_all();
		$shortcut = sanitize_text_field( wp_unslash( $_POST['macro_shortcut'] ?? '' ) );
		$title    = sanitize_text_field( wp_unslash( $_POST['macro_title'] ?? '' ) );
		$text     = sanitize_textarea_field( wp_unslash( $_POST['macro_text'] ?? '' ) );

		if ( empty( $shortcut ) || empty( $title ) || empty( $text ) ) {
			wp_safe_redirect( admin_url( 'admin.php?page=adventchat-macros' ) );
			exit;
		}

		// Ensure shortcut starts with /
		if ( '/' !== $shortcut[0] ) {
			$shortcut = '/' . $shortcut;
		}

		if ( ! empty( $_POST['macro_id'] ) ) {
			$id = sanitize_text_field( wp_unslash( $_POST['macro_id'] ) );
			foreach ( $macros as &$m ) {
				if ( $m['id'] === $id ) {
					$m['shortcut'] = $shortcut;
					$m['title']    = $title;
					$m['text']     = $text;
					break;
				}
			}
			unset( $m );
		} else {
			$macros[] = array(
				'id'       => wp_generate_uuid4(),
				'shortcut' => $shortcut,
				'title'    => $title,
				'text'     => $text,
			);
		}

		update_option( AdventChat_Macros_Table::OPTION_KEY, $macros, false );

		wp_safe_redirect( admin_url( 'admin.php?page=adventchat-macros' ) );
		exit;
	}

	public static function handle_delete(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Unauthorized', 'adventchat' ) );
		}

		check_admin_referer( 'adventchat_delete_macro' );

		$macros = AdventChat_Macros_Table::get_all();
		$id     = sanitize_text_field( wp_unslash( $_GET['macro_id'] ?? '' ) );

		$macros = array_values(
			array_filter( $macros, static function ( $m ) use ( $id ) {
				return $m['id'] !== $id;
			} )
		);

		update_option( AdventChat_Macros_Table::OPTION_KEY, $macros, false );

		wp_safe_redirect( admin_url( 'admin.php?page=adventchat-macros' ) );
		exit;
	}
}
