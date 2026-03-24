<?php
/**
 * WooCommerce integration — cart context, order context, visitor identity.
 *
 * WP-70: Cart items + total passed to widget config.
 * WP-71: Order context on order-received page.
 * WP-72: WC customer data auto-fills pre-chat + lifetime value in sidebar.
 *
 * @package AdventChat
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class AdventChat_WooCommerce
 */
class AdventChat_WooCommerce {

	/**
	 * Initialize hooks if WooCommerce is active.
	 */
	public static function init(): void {
		if ( ! class_exists( 'WooCommerce' ) ) {
			return;
		}

		add_filter( 'adventchat_widget_config', array( __CLASS__, 'add_cart_context' ) );
		add_filter( 'adventchat_widget_config', array( __CLASS__, 'add_order_context' ) );
		add_filter( 'adventchat_widget_config', array( __CLASS__, 'add_customer_identity' ) );
	}

	/**
	 * WP-70: Add current cart context to widget config.
	 *
	 * @param array $config Current widget config.
	 * @return array Modified config.
	 */
	public static function add_cart_context( array $config ): array {
		if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
			return $config;
		}

		$cart  = WC()->cart;
		$items = array();

		foreach ( $cart->get_cart() as $item ) {
			$product = $item['data'];
			if ( ! $product ) {
				continue;
			}
			$items[] = array(
				'name'     => $product->get_name(),
				'quantity' => $item['quantity'],
				'total'    => wc_format_decimal( $item['line_total'], 2 ),
			);
		}

		$config['woo'] = array_merge(
			$config['woo'] ?? array(),
			array(
				'cartItems' => $items,
				'cartTotal' => wc_format_decimal( $cart->get_total( 'edit' ), 2 ),
				'cartCount' => $cart->get_cart_contents_count(),
				'currency'  => get_woocommerce_currency(),
			)
		);

		return $config;
	}

	/**
	 * WP-71: Add order context on the order-received (thank-you) page.
	 *
	 * @param array $config Current widget config.
	 * @return array Modified config.
	 */
	public static function add_order_context( array $config ): array {
		if ( ! function_exists( 'is_order_received_page' ) || ! is_order_received_page() ) {
			return $config;
		}

		global $wp;
		$order_id = absint( $wp->query_vars['order-received'] ?? 0 );
		if ( ! $order_id ) {
			return $config;
		}

		$order = wc_get_order( $order_id );
		if ( ! $order ) {
			return $config;
		}

		$config['woo'] = array_merge(
			$config['woo'] ?? array(),
			array(
				'orderId'    => $order->get_id(),
				'orderTotal' => wc_format_decimal( $order->get_total(), 2 ),
				'orderUrl'   => $order->get_edit_order_url(),
			)
		);

		return $config;
	}

	/**
	 * WP-72: Add WooCommerce customer identity to widget config.
	 *
	 * @param array $config Current widget config.
	 * @return array Modified config.
	 */
	public static function add_customer_identity( array $config ): array {
		if ( ! is_user_logged_in() ) {
			return $config;
		}

		$customer = new \WC_Customer( get_current_user_id() );
		if ( ! $customer->get_id() ) {
			return $config;
		}

		$config['woo'] = array_merge(
			$config['woo'] ?? array(),
			array(
				'customerName'      => $customer->get_display_name(),
				'customerEmail'     => $customer->get_email(),
				'lifetimeValue'     => wc_format_decimal( $customer->get_total_spent(), 2 ),
				'orderCount'        => $customer->get_order_count(),
			)
		);

		// Auto-fill pre-chat form with WC customer data.
		$config['settings']['prefillName']  = $customer->get_display_name();
		$config['settings']['prefillEmail'] = $customer->get_email();

		return $config;
	}
}
