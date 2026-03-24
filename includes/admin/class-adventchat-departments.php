<?php
/**
 * Departments admin page — CRUD for chat departments.
 *
 * WP-49: Manages departments stored as a WP option and synced to Firestore.
 *
 * @package AdventChat
 */

defined( 'ABSPATH' ) || exit;

class AdventChat_Departments {

	const OPTION_KEY = 'adventchat_departments';

	/**
	 * Register hooks.
	 */
	public static function init(): void {
		add_action( 'admin_menu', array( __CLASS__, 'add_submenu' ) );
		add_action( 'admin_post_adventchat_save_department', array( __CLASS__, 'handle_save' ) );
		add_action( 'admin_post_adventchat_delete_department', array( __CLASS__, 'handle_delete' ) );
	}

	/**
	 * Add the Departments submenu page under the main AdventChat menu.
	 */
	public static function add_submenu(): void {
		add_submenu_page(
			'adventchat',
			__( 'Departments', 'adventchat' ),
			__( 'Departments', 'adventchat' ),
			'manage_options',
			'adventchat-departments',
			array( __CLASS__, 'render_page' )
		);
	}

	/**
	 * Get all departments.
	 *
	 * @return array<int, array{id: string, name: string, agents: int[]}>
	 */
	public static function get_all(): array {
		return get_option( self::OPTION_KEY, array() );
	}

	/**
	 * Render the admin page.
	 */
	public static function render_page(): void {
		$departments = self::get_all();
		$editing     = null;

		if ( isset( $_GET['edit'] ) && isset( $_GET['_wpnonce'] ) ) {
			if ( wp_verify_nonce( sanitize_key( $_GET['_wpnonce'] ), 'adventchat_edit_department' ) ) {
				$edit_id = sanitize_text_field( wp_unslash( $_GET['edit'] ) );
				foreach ( $departments as $dept ) {
					if ( $dept['id'] === $edit_id ) {
						$editing = $dept;
						break;
					}
				}
			}
		}

		$operators = get_users( array(
			'role__in' => array( 'administrator', 'adventchat_operator' ),
			'fields'   => array( 'ID', 'display_name' ),
		) );

		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Departments', 'adventchat' ); ?></h1>

			<!-- Add / Edit form -->
			<h2><?php echo $editing ? esc_html__( 'Edit Department', 'adventchat' ) : esc_html__( 'Add Department', 'adventchat' ); ?></h2>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<?php wp_nonce_field( 'adventchat_save_department', '_adventchat_nonce' ); ?>
				<input type="hidden" name="action" value="adventchat_save_department" />
				<?php if ( $editing ) : ?>
					<input type="hidden" name="department_id" value="<?php echo esc_attr( $editing['id'] ); ?>" />
				<?php endif; ?>

				<table class="form-table">
					<tr>
						<th><label for="dept_name"><?php esc_html_e( 'Name', 'adventchat' ); ?></label></th>
						<td><input type="text" id="dept_name" name="department_name" value="<?php echo esc_attr( $editing['name'] ?? '' ); ?>" class="regular-text" required /></td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'Agents', 'adventchat' ); ?></th>
						<td>
							<?php
							$selected_agents = $editing['agents'] ?? array();
							foreach ( $operators as $op ) :
								$checked = in_array( (int) $op->ID, $selected_agents, true ) ? 'checked' : '';
								?>
								<label style="display:block;margin-bottom:4px;">
									<input type="checkbox" name="department_agents[]" value="<?php echo esc_attr( $op->ID ); ?>" <?php echo $checked; ?> />
									<?php echo esc_html( $op->display_name ); ?>
								</label>
							<?php endforeach; ?>
						</td>
					</tr>
				</table>

				<?php submit_button( $editing ? __( 'Update Department', 'adventchat' ) : __( 'Add Department', 'adventchat' ) ); ?>
			</form>

			<!-- List -->
			<h2><?php esc_html_e( 'Existing Departments', 'adventchat' ); ?></h2>
			<table class="wp-list-table widefat fixed striped">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Name', 'adventchat' ); ?></th>
						<th><?php esc_html_e( 'Agents', 'adventchat' ); ?></th>
						<th style="width:180px;"><?php esc_html_e( 'Actions', 'adventchat' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php if ( empty( $departments ) ) : ?>
						<tr><td colspan="3"><?php esc_html_e( 'No departments yet.', 'adventchat' ); ?></td></tr>
					<?php else : ?>
						<?php foreach ( $departments as $dept ) : ?>
							<tr>
								<td><?php echo esc_html( $dept['name'] ); ?></td>
								<td><?php echo esc_html( count( $dept['agents'] ?? array() ) ); ?></td>
								<td>
									<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=adventchat-departments&edit=' . $dept['id'] ), 'adventchat_edit_department' ) ); ?>"><?php esc_html_e( 'Edit', 'adventchat' ); ?></a>
									|
									<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=adventchat_delete_department&department_id=' . $dept['id'] ), 'adventchat_delete_department' ) ); ?>" onclick="return confirm('<?php esc_attr_e( 'Delete this department?', 'adventchat' ); ?>');" style="color:#b32d2e;"><?php esc_html_e( 'Delete', 'adventchat' ); ?></a>
								</td>
							</tr>
						<?php endforeach; ?>
					<?php endif; ?>
				</tbody>
			</table>
		</div>
		<?php
	}

	/**
	 * Handle save (create or update).
	 */
	public static function handle_save(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Unauthorized', 'adventchat' ) );
		}

		check_admin_referer( 'adventchat_save_department', '_adventchat_nonce' );

		$departments = self::get_all();
		$name        = sanitize_text_field( wp_unslash( $_POST['department_name'] ?? '' ) );

		if ( empty( $name ) ) {
			wp_safe_redirect( admin_url( 'admin.php?page=adventchat-departments' ) );
			exit;
		}

		$agents = array_map( 'intval', (array) ( $_POST['department_agents'] ?? array() ) );

		if ( ! empty( $_POST['department_id'] ) ) {
			// Update existing.
			$id = sanitize_text_field( wp_unslash( $_POST['department_id'] ) );
			foreach ( $departments as &$dept ) {
				if ( $dept['id'] === $id ) {
					$dept['name']   = $name;
					$dept['agents'] = $agents;
					break;
				}
			}
			unset( $dept );
		} else {
			// Create new.
			$departments[] = array(
				'id'     => wp_generate_uuid4(),
				'name'   => $name,
				'agents' => $agents,
			);
		}

		update_option( self::OPTION_KEY, $departments, false );

		wp_safe_redirect( admin_url( 'admin.php?page=adventchat-departments' ) );
		exit;
	}

	/**
	 * Handle delete.
	 */
	public static function handle_delete(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Unauthorized', 'adventchat' ) );
		}

		check_admin_referer( 'adventchat_delete_department' );

		$departments = self::get_all();
		$id          = sanitize_text_field( wp_unslash( $_GET['department_id'] ?? '' ) );

		$departments = array_values(
			array_filter( $departments, static function ( $d ) use ( $id ) {
				return $d['id'] !== $id;
			} )
		);

		update_option( self::OPTION_KEY, $departments, false );

		wp_safe_redirect( admin_url( 'admin.php?page=adventchat-departments' ) );
		exit;
	}
}
