<?php
/**
 * Plugin Name: Omise Order Handler
 * Description: An automate script to update WooCommerce Order's status from pending to cancelled after a desired-time.
 * Version:     0.1
 * Author:      Omise
 * License:     MIT
 * License URI: https://opensource.org/licenses/MIT
 */
defined( 'ABSPATH' ) or die( 'No direct script access allowed.' );

class Omise_Order_Handler {
	/**
	 * The Omise Instance.
	 * @var   \Omise_Order_Handler
	 */
	protected static $the_instance = null;

	/**
	 * @var   boolean
	 */
	protected static $can_initiate = false;

	/**
	 * @var array  of system messages.
	 */
	protected $messages = array();

	/**
	 * @var null | array
	 */
	public $settings;

	public function __construct() {
		add_action( 'plugins_loaded', array( $this, 'check_dependencies' ) );
		add_action( 'init', array( $this, 'init' ) );
    }
    
    /**
	 * The Omise_Order_Handler Instance.
	 *
	 * @see    Omise_Order_Handler()
	 * @static
	 * @return \Omise_Order_Handler - The instance.
	 */
	public static function instance() {
		if ( is_null( self::$the_instance ) ) {
			self::$the_instance = new self();
		}

		return self::$the_instance;
	}

	public static function batch_update_order_status() {
		$held_duration = get_option( 'omise_order_handler_settings' );
		if ( $held_duration < 1 ) {
			return;
		}
	
		$data_store    = WC_Data_Store::load( 'order' );
		$unpaid_orders = $data_store->get_unpaid_orders( strtotime( '-' . absint( $held_duration ) . ' MINUTES', current_time( 'timestamp' ) ) );
	
		if ( $unpaid_orders ) {
			foreach ( $unpaid_orders as $unpaid_order ) {
				$order = wc_get_order( $unpaid_order );
	
				if ( apply_filters( 'woocommerce_cancel_unpaid_order', 'checkout' === $order->get_created_via(), $order ) ) {
					$order->update_status( 'cancelled', __( 'Unpaid order cancelled - time limit reached.', 'woocommerce' ) );
				}
			}
		}
		wp_clear_scheduled_hook( 'omise_order_handler_cancel_unpaid_orders' );
		wp_schedule_single_event( time() + ( absint( $held_duration ) * 60 ), 'omise_order_handler_cancel_unpaid_orders' );
	}

	/** 
	 * Check if all dependencies are loaded properly
     * before Omise_Order_Handler plugin.
	 */
	public function check_dependencies() {
		if ( ! function_exists( 'WC' ) ) {
			return;
		}

		static::$can_initiate = true;
	}

	public function init() {
		if ( ! static::$can_initiate ) {
			add_action( 'admin_notices', array( $this, 'init_error_messages' ) );
			return;
		}

		defined( 'OMISE_ORDER_HANDLER_PLUGIN_PATH' ) || define( 'OMISE_ORDER_HANDLER_PLUGIN_PATH', __DIR__ );

		$this->register_hooks();
		$this->register_admin_menu();
		$this->settings = $this->get_settings();
	}

	/**
	 * Callback to display message about activation error
	 */
	public function init_error_messages(){
		?>
		<div class="error">
			<p><?php echo __( 'Kiss plugin requires <strong>WooCommerce</strong> to be activated.', 'omise' ); ?></p>
		</div>
		<?php
    }
    
	public function register_hooks() {
		add_action( 'omise_order_handler_cancel_unpaid_orders', 'Omise_Order_Handler::batch_update_order_status' );
	}
	
	/**
	 * Register Omise's custom menu to WordPress admin menus.
	 */
	public function register_admin_menu() {	
		add_action( 'admin_menu', array( $this, 'wordpress_hook_admin_menu' ) );
	}

	/**
	 * Callback to $this::register_admin_menu() method.
	 * Register Omise's custom menu to WordPress admin menus.
	 */
	public function wordpress_hook_admin_menu() {
		add_menu_page( 'Omise Order Handler', 'Omise Order Handler', 'manage_options', 'Omise-order-handler', array( $this, 'page_settings') );
	}

	/**
	 * Render Omise Setting page.
	 */
	public function page_settings() {
		global $title;

		// Save settings if data has been posted
		if ( ! empty( $_POST ) ) {
			$post = $_POST; unset( $_POST );
			if ( ! isset( $post['omise_order_handler_setting_page_nonce'] ) || ! wp_verify_nonce( $post['omise_order_handler_setting_page_nonce'], 'omise-order-handler-setting' ) ) {
				wp_die( __( 'You are not allowed to modify the settings from a suspicious source.', 'omise' ) );
			}

			$this->settings['schedule_time'] = sanitize_text_field( $post['schedule_time'] );
			$this->update_settings();

			if ( $this->settings['schedule_time'] > 0 ) {
				wp_clear_scheduled_hook( 'omise_order_handler_cancel_unpaid_orders' );
				wp_schedule_single_event( time() + ( absint( $this->settings['schedule_time'] ) * 60 ), 'omise_order_handler_cancel_unpaid_orders' );
			}
		}

		include_once __DIR__ . '/views/html-settings.php';
	}

	/**
	 * @return HTML
	 */
	public function display_messages() {
		if ( count( $this->messages ) > 0 ) {
			foreach ( $this->messages as $message ) {
				echo '<div class="updated"><p>' . esc_html( $message ) . '</p></div>';
			}
		}
	}

	/**
	 * @return array
	 */
	protected function get_default_settings() {
		return array( 'schedule_time' => 0 );
	}

	/**
	 * @return array
	 */
	public function get_settings() {
		if ( $options = get_option( 'omise_order_handler_settings' ) ) {
			return array_merge( $this->get_default_settings(), $options );
		}

		return $this->get_default_settings();
	}

	/**
	 * Update the plugin's settings.
	 */
	public function update_settings() {
		update_option( 'omise_order_handler_settings', $this->settings );
	}
}

function Omise_Order_Handler() {
	return Omise_Order_Handler::instance();
}

Omise_Order_Handler();
