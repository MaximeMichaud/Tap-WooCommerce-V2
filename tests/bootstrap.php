<?php
/**
 * PHPUnit bootstrap file with WordPress/WooCommerce mocks.
 *
 * @package TapWooCommerce
 */

// Mock WordPress constants.
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', dirname( __DIR__ ) . '/' );
}
if ( ! defined( 'WP_CONTENT_DIR' ) ) {
	define( 'WP_CONTENT_DIR', ABSPATH . 'wp-content' );
}
if ( ! defined( 'WP_PLUGIN_DIR' ) ) {
	define( 'WP_PLUGIN_DIR', WP_CONTENT_DIR . '/plugins' );
}
if ( ! defined( 'WP_PLUGIN_URL' ) ) {
	define( 'WP_PLUGIN_URL', 'https://example.com/wp-content/plugins' );
}
if ( ! defined( 'WPINC' ) ) {
	define( 'WPINC', 'wp-includes' );
}
if ( ! defined( 'TAPWC_IMGDIR' ) ) {
	define( 'TAPWC_IMGDIR', 'https://example.com/wp-content/plugins/tap-woocommerce/assets/img/' );
}

// =============================================================================
// MOCK WORDPRESS FILESYSTEM CLASSES (required by tap.php)
// =============================================================================

if ( ! class_exists( 'WP_Filesystem_Base' ) ) {
	class WP_Filesystem_Base {
		public function __construct() {}
	}
}

if ( ! class_exists( 'WP_Filesystem_Direct' ) ) {
	class WP_Filesystem_Direct extends WP_Filesystem_Base {
		public function __construct() {
			parent::__construct();
		}
	}
}

// =============================================================================
// GLOBAL STATE FOR TESTING
// =============================================================================

global $wp_scripts_enqueued, $wp_styles_enqueued, $wp_scripts_registered, $wp_styles_registered;
global $wp_options, $wp_post_meta, $wp_actions, $wp_filters;
global $wc_orders, $wc_products, $woocommerce;

$wp_scripts_enqueued    = array();
$wp_styles_enqueued     = array();
$wp_scripts_registered  = array();
$wp_styles_registered   = array();
$wp_options             = array();
$wp_post_meta           = array();
$wp_actions             = array();
$wp_filters             = array();
$wc_orders              = array();
$wc_products            = array();

// =============================================================================
// WOOCOMMERCE CLASSES
// =============================================================================

/**
 * Mock WC_Payment_Gateway class - base class for payment gateways.
 * Note: Properties are untyped to match WooCommerce's actual implementation
 * and allow child classes to redeclare them without type conflicts.
 */
if ( ! class_exists( 'WC_Payment_Gateway' ) ) {
	class WC_Payment_Gateway {
		public $id = '';
		public $icon = '';
		public $has_fields = false;
		public $method_title = '';
		public $method_description = '';
		public $supports = array( 'products' );
		public $title = '';
		public $description = '';
		public $enabled = 'no';
		public $settings = array();
		public $form_fields = array();

		public function __construct() {}

		public function init_form_fields() {}

		public function init_settings() {
			$this->settings = get_option( 'woocommerce_' . $this->id . '_settings', array() );
		}

		public function get_option( $key, $default = '' ) {
			return $this->settings[ $key ] ?? $default;
		}

		public function process_admin_options() {
			return true;
		}

		public function get_return_url( $order = null ) {
			return 'https://example.com/checkout/order-received/';
		}

		public function supports( $feature ) {
			return in_array( $feature, $this->supports, true );
		}
	}
}

/**
 * Mock WC_Order class.
 */
if ( ! class_exists( 'WC_Order' ) ) {
	class WC_Order {
		private int $id;
		private string $status = 'pending';
		private array $meta_data = array();
		private array $items = array();
		private string $billing_email = 'test@example.com';
		private string $billing_first_name = 'John';
		private string $billing_last_name = 'Doe';
		private string $billing_phone = '1234567890';
		private string $billing_country = 'US';
		private string $billing_city = 'New York';
		private string $billing_address_1 = '123 Main St';
		private string $billing_address_2 = '';
		private string $currency = 'USD';
		private float $total = 100.00;
		private string $transaction_id = '';
		private array $order_notes = array();
		private array $shipping_methods = array();

		public function __construct( int $id = 0 ) {
			$this->id = $id;
		}

		public function get_id(): int {
			return $this->id;
		}

		public function get_status(): string {
			return $this->status;
		}

		public function set_status( string $status ): void {
			$this->status = str_replace( 'wc-', '', $status );
		}

		public function update_status( string $status, string $note = '' ): bool {
			$this->status = str_replace( 'wc-', '', $status );
			if ( $note ) {
				$this->add_order_note( $note );
			}
			return true;
		}

		public function get_currency(): string {
			return $this->currency;
		}

		public function get_total(): float {
			return $this->total;
		}

		public function get_billing_email(): string {
			return $this->billing_email;
		}

		public function get_billing_first_name(): string {
			return $this->billing_first_name;
		}

		public function get_billing_last_name(): string {
			return $this->billing_last_name;
		}

		public function get_billing_phone(): string {
			return $this->billing_phone;
		}

		public function get_billing_country(): string {
			return $this->billing_country;
		}

		public function get_billing_city(): string {
			return $this->billing_city;
		}

		public function get_billing_address_1(): string {
			return $this->billing_address_1;
		}

		public function get_billing_address_2(): string {
			return $this->billing_address_2;
		}

		public function get_items( string $type = 'line_item' ): array {
			return $this->items;
		}

		public function get_shipping_methods(): array {
			return $this->shipping_methods;
		}

		public function get_checkout_payment_url( bool $on_checkout = false ): string {
			return 'https://example.com/checkout/order-pay/' . $this->id . '/';
		}

		public function get_checkout_order_received_url(): string {
			return 'https://example.com/checkout/order-received/' . $this->id . '/';
		}

		public function add_order_note( string $note, int $is_customer_note = 0, bool $added_by_user = false ): int {
			$this->order_notes[] = $note;
			return count( $this->order_notes );
		}

		public function payment_complete( string $transaction_id = '' ): bool {
			$this->transaction_id = $transaction_id;
			$this->status = 'processing';
			return true;
		}

		public function set_transaction_id( string $transaction_id ): void {
			$this->transaction_id = $transaction_id;
		}

		public function get_transaction_id(): string {
			return $this->transaction_id;
		}

		// Testing helpers
		public function set_total( float $total ): void {
			$this->total = $total;
		}

		public function set_currency( string $currency ): void {
			$this->currency = $currency;
		}

		public function set_billing_country( string $country ): void {
			$this->billing_country = $country;
		}

		public function get_order_notes(): array {
			return $this->order_notes;
		}
	}
}

/**
 * Mock WC_Order_Item_Product class.
 */
if ( ! class_exists( 'WC_Order_Item_Product' ) ) {
	class WC_Order_Item_Product {
		private int $id;
		private string $name = 'Test Product';
		private int $quantity = 1;
		private int $product_id = 1;
		private int $variation_id = 0;

		public function __construct( int $id = 0 ) {
			$this->id = $id;
		}

		public function get_name(): string {
			return $this->name;
		}

		public function get_quantity(): int {
			return $this->quantity;
		}

		public function get_product_id(): int {
			return $this->product_id;
		}

		public function get_variation_id(): int {
			return $this->variation_id;
		}
	}
}

/**
 * Mock WC_Order_Item_Shipping class.
 */
if ( ! class_exists( 'WC_Order_Item_Shipping' ) ) {
	class WC_Order_Item_Shipping {
		private string $name = 'Flat Rate';
		private float $total = 10.00;

		public function get_name(): string {
			return $this->name;
		}

		public function get_total(): float {
			return $this->total;
		}
	}
}

/**
 * Mock WC_Product_Variation class.
 */
if ( ! class_exists( 'WC_Product_Variation' ) ) {
	class WC_Product_Variation {
		private int $id;

		public function __construct( int $id = 0 ) {
			$this->id = $id;
		}

		public function get_variation_attributes(): array {
			return array();
		}
	}
}

/**
 * Mock WC_Cart class.
 */
if ( ! class_exists( 'WC_Cart' ) ) {
	class WC_Cart {
		public float $total = 0.00;
		private array $cart_contents = array();

		public function get_cart(): array {
			return $this->cart_contents;
		}

		public function empty_cart(): void {
			$this->cart_contents = array();
		}

		public function add_to_cart( $product_id, $quantity = 1, $variation_id = 0, $variation = array(), $cart_item_data = array() ): string {
			return 'cart_item_key';
		}

		public function get_cart_url(): string {
			return 'https://example.com/cart/';
		}
	}
}

/**
 * Mock WooCommerce main class.
 */
if ( ! class_exists( 'WooCommerce' ) ) {
	class WooCommerce {
		public string $version = '9.0.0';
		public ?WC_Cart $cart = null;

		private static ?WooCommerce $instance = null;

		public static function instance(): WooCommerce {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		public function __construct() {
			$this->cart = new WC_Cart();
		}
	}
}

// =============================================================================
// WOOCOMMERCE FUNCTIONS
// =============================================================================

if ( ! function_exists( 'WC' ) ) {
	function WC(): WooCommerce {
		return WooCommerce::instance();
	}
}

if ( ! function_exists( 'wc_get_order' ) ) {
	function wc_get_order( $order_id ): ?WC_Order {
		global $wc_orders;

		if ( $order_id instanceof WC_Order ) {
			return $order_id;
		}

		$order_id = absint( $order_id );

		if ( isset( $wc_orders[ $order_id ] ) ) {
			return $wc_orders[ $order_id ];
		}

		if ( $order_id > 0 ) {
			$order = new WC_Order( $order_id );
			$wc_orders[ $order_id ] = $order;
			return $order;
		}

		return null;
	}
}

if ( ! function_exists( 'wc_add_notice' ) ) {
	function wc_add_notice( string $message, string $notice_type = 'success', array $data = array() ): void {
		// Mock - just store for testing if needed.
	}
}

if ( ! function_exists( 'wc_reduce_stock_levels' ) ) {
	function wc_reduce_stock_levels( int $order_id ): void {
		// Mock implementation.
	}
}

if ( ! function_exists( 'get_woocommerce_currency' ) ) {
	function get_woocommerce_currency(): string {
		return 'USD';
	}
}

if ( ! function_exists( 'is_checkout' ) ) {
	function is_checkout(): bool {
		return false;
	}
}

// =============================================================================
// WORDPRESS FUNCTIONS
// =============================================================================

if ( ! function_exists( 'add_filter' ) ) {
	function add_filter( string $hook_name, $callback, int $priority = 10, int $accepted_args = 1 ): bool {
		global $wp_filters;
		if ( ! isset( $wp_filters[ $hook_name ] ) ) {
			$wp_filters[ $hook_name ] = array();
		}
		$wp_filters[ $hook_name ][] = array(
			'callback'      => $callback,
			'priority'      => $priority,
			'accepted_args' => $accepted_args,
		);
		return true;
	}
}

if ( ! function_exists( 'add_action' ) ) {
	function add_action( string $hook_name, $callback, int $priority = 10, int $accepted_args = 1 ): bool {
		global $wp_actions;
		if ( ! isset( $wp_actions[ $hook_name ] ) ) {
			$wp_actions[ $hook_name ] = array();
		}
		$wp_actions[ $hook_name ][] = array(
			'callback'      => $callback,
			'priority'      => $priority,
			'accepted_args' => $accepted_args,
		);
		return true;
	}
}

if ( ! function_exists( 'apply_filters' ) ) {
	function apply_filters( string $hook_name, $value, ...$args ) {
		global $wp_filters;
		if ( isset( $wp_filters[ $hook_name ] ) ) {
			foreach ( $wp_filters[ $hook_name ] as $filter ) {
				$value = call_user_func_array( $filter['callback'], array_merge( array( $value ), array_slice( $args, 0, $filter['accepted_args'] - 1 ) ) );
			}
		}
		return $value;
	}
}

if ( ! function_exists( 'do_action' ) ) {
	function do_action( string $hook_name, ...$args ): void {
		global $wp_actions;
		if ( isset( $wp_actions[ $hook_name ] ) ) {
			foreach ( $wp_actions[ $hook_name ] as $action ) {
				call_user_func_array( $action['callback'], array_slice( $args, 0, $action['accepted_args'] ) );
			}
		}
	}
}

if ( ! function_exists( 'get_option' ) ) {
	function get_option( string $option, $default = false ) {
		global $wp_options;
		return $wp_options[ $option ] ?? $default;
	}
}

if ( ! function_exists( 'update_option' ) ) {
	function update_option( string $option, $value, $autoload = null ): bool {
		global $wp_options;
		$wp_options[ $option ] = $value;
		return true;
	}
}

if ( ! function_exists( 'get_post_meta' ) ) {
	function get_post_meta( int $post_id, string $key = '', bool $single = false ) {
		global $wp_post_meta;
		if ( empty( $key ) ) {
			return $wp_post_meta[ $post_id ] ?? array();
		}
		$value = $wp_post_meta[ $post_id ][ $key ] ?? null;
		return $single ? ( $value ?? '' ) : ( $value !== null ? array( $value ) : array() );
	}
}

if ( ! function_exists( 'update_post_meta' ) ) {
	function update_post_meta( int $post_id, string $meta_key, $meta_value, $prev_value = '' ): bool {
		global $wp_post_meta;
		if ( ! isset( $wp_post_meta[ $post_id ] ) ) {
			$wp_post_meta[ $post_id ] = array();
		}
		$wp_post_meta[ $post_id ][ $meta_key ] = $meta_value;
		return true;
	}
}

if ( ! function_exists( 'get_permalink' ) ) {
	function get_permalink( $post = 0, bool $leavename = false ) {
		return 'https://example.com/page/' . absint( $post ) . '/';
	}
}

if ( ! function_exists( 'get_site_url' ) ) {
	function get_site_url( ?int $blog_id = null, string $path = '', ?string $scheme = null ): string {
		return 'https://example.com' . $path;
	}
}

if ( ! function_exists( 'get_pages' ) ) {
	function get_pages( $args = array() ) {
		return array();
	}
}

if ( ! function_exists( 'get_post' ) ) {
	function get_post( $post = null, string $output = OBJECT, string $filter = 'raw' ) {
		return null;
	}
}

if ( ! function_exists( 'wp_get_current_user' ) ) {
	function wp_get_current_user(): object {
		return (object) array(
			'ID' => 1,
			'user_firstname' => 'John',
			'user_lastname' => 'Doe',
		);
	}
}

if ( ! function_exists( 'get_current_user_id' ) ) {
	function get_current_user_id(): int {
		return 1;
	}
}

if ( ! function_exists( 'wp_redirect' ) ) {
	function wp_redirect( string $location, int $status = 302, string $x_redirect_by = 'WordPress' ): bool {
		return true;
	}
}

if ( ! function_exists( 'wp_safe_redirect' ) ) {
	function wp_safe_redirect( string $location, int $status = 302, string $x_redirect_by = 'WordPress' ): bool {
		return true;
	}
}

if ( ! function_exists( 'headers_sent' ) ) {
	// headers_sent is a PHP function, don't mock unless needed
}

if ( ! function_exists( 'wp_enqueue_script' ) ) {
	function wp_enqueue_script( string $handle, string $src = '', array $deps = array(), $ver = false, $args = false ): void {
		global $wp_scripts_enqueued;
		$wp_scripts_enqueued[ $handle ] = array(
			'handle' => $handle,
			'src'    => $src,
			'deps'   => $deps,
			'ver'    => $ver,
		);
	}
}

if ( ! function_exists( 'wp_enqueue_style' ) ) {
	function wp_enqueue_style( string $handle, string $src = '', array $deps = array(), $ver = false, string $media = 'all' ): void {
		global $wp_styles_enqueued;
		$wp_styles_enqueued[ $handle ] = array(
			'handle' => $handle,
			'src'    => $src,
			'deps'   => $deps,
			'ver'    => $ver,
		);
	}
}

if ( ! function_exists( 'wp_register_script' ) ) {
	function wp_register_script( string $handle, string $src = '', array $deps = array(), $ver = false, $args = false ): bool {
		global $wp_scripts_registered;
		$wp_scripts_registered[ $handle ] = array(
			'handle' => $handle,
			'src'    => $src,
		);
		return true;
	}
}

if ( ! function_exists( 'plugins_url' ) ) {
	function plugins_url( string $path = '', string $plugin = '' ): string {
		return 'https://example.com/wp-content/plugins/tap-woocommerce/' . $path;
	}
}

if ( ! function_exists( 'plugin_basename' ) ) {
	function plugin_basename( string $file ): string {
		return 'tap-woocommerce/' . basename( $file );
	}
}

if ( ! function_exists( '__' ) ) {
	function __( string $text, string $domain = 'default' ): string {
		return $text;
	}
}

if ( ! function_exists( 'esc_attr' ) ) {
	function esc_attr( $text ): string {
		return htmlspecialchars( (string) $text, ENT_QUOTES, 'UTF-8' );
	}
}

if ( ! function_exists( 'esc_url' ) ) {
	function esc_url( string $url ): string {
		return filter_var( $url, FILTER_SANITIZE_URL ) ?: '';
	}
}

if ( ! function_exists( 'esc_html' ) ) {
	function esc_html( $text ): string {
		return htmlspecialchars( (string) $text, ENT_QUOTES, 'UTF-8' );
	}
}

if ( ! function_exists( 'wp_kses_post' ) ) {
	function wp_kses_post( string $data ): string {
		return $data;
	}
}

if ( ! function_exists( 'wpautop' ) ) {
	function wpautop( string $text, bool $br = true ): string {
		return '<p>' . $text . '</p>';
	}
}

if ( ! function_exists( 'sanitize_text_field' ) ) {
	function sanitize_text_field( string $str ): string {
		return trim( strip_tags( $str ) );
	}
}

if ( ! function_exists( 'sanitize_email' ) ) {
	function sanitize_email( string $email ): string {
		return filter_var( $email, FILTER_SANITIZE_EMAIL ) ?: '';
	}
}

if ( ! function_exists( 'wp_unslash' ) ) {
	function wp_unslash( $value ) {
		return is_string( $value ) ? stripslashes( $value ) : $value;
	}
}

if ( ! function_exists( 'absint' ) ) {
	function absint( $maybeint ): int {
		return abs( (int) $maybeint );
	}
}

if ( ! function_exists( 'wp_json_encode' ) ) {
	function wp_json_encode( $data, int $options = 0, int $depth = 512 ) {
		return json_encode( $data, $options, $depth );
	}
}

if ( ! defined( 'OBJECT' ) ) {
	define( 'OBJECT', 'OBJECT' );
}

// =============================================================================
// TEST HELPER FUNCTIONS
// =============================================================================

/**
 * Reset all global state for clean tests.
 */
function reset_all(): void {
	global $wp_scripts_enqueued, $wp_styles_enqueued, $wp_scripts_registered, $wp_styles_registered;
	global $wp_options, $wp_post_meta, $wp_actions, $wp_filters;
	global $wc_orders, $wc_products;

	$wp_scripts_enqueued    = array();
	$wp_styles_enqueued     = array();
	$wp_scripts_registered  = array();
	$wp_styles_registered   = array();
	$wp_options             = array();
	$wp_post_meta           = array();
	$wp_actions             = array();
	$wp_filters             = array();
	$wc_orders              = array();
	$wc_products            = array();
}

/**
 * Set WordPress option for testing.
 */
function set_wp_option( string $key, $value ): void {
	global $wp_options;
	$wp_options[ $key ] = $value;
}

/**
 * Create a mock order for testing.
 */
function create_mock_order( int $id = 1 ): WC_Order {
	global $wc_orders;
	$order = new WC_Order( $id );
	$wc_orders[ $id ] = $order;
	return $order;
}

// =============================================================================
// LOAD COMPOSER AUTOLOADER
// =============================================================================

require_once dirname( __DIR__ ) . '/vendor/autoload.php';
