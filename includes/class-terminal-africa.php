<?php
//security
defined('ABSPATH') or die('No script kiddies please!');

/**
 * Terminal Africa
 * @package TerminalAfrica
 * @since 1.0.0
 * @version 1.0.0
 * @author Terminal Africa
 */

use App\Terminal\Core\TerminalSession;
use TerminalAfrica\Includes\Parts\Menus;
use TerminalAfrica\Includes\Parts\Ajax;
use TerminalAfrica\Includes\Parts\Shipping;
use TerminalAfrica\Includes\Parts\Activation;
use TerminalAfrica\Includes\Parts\Assets;
use TerminalAfrica\Includes\Parts\TerminalRESTAPI;

//class
/**
 * TerminalAfricaShippingPlugin
 * @package TerminalAfrica
 * @since 1.0.0
 * @version 1.0.0
 * @author Terminal Africa
 */
class TerminalAfricaShippingPlugin
{
    /**
     * Secret Key
     * @since 1.11.10
     * @var string
     */
    public static $skkey;

    /**
     * Public Key
     * @since 1.11.10
     * @var string
     */
    public static $public_key;

    /**
     * User ID
     * @since 1.11.10
     * @var string
     */
    public static $user_id;

    /**
     * User Payment Status
     * @since 1.11.10
     * @var string
     */
    public static $user_payment_status;

    /**
     * Instance
     * @since 1.11.10
     * @var object
     */
    public static $instance;

    /**
     * ~Deprecated~ property - please use <s>$endpoint</s> instead.
     * @since 1.10.19
     */
    public static $enpoint;

    /**
     * Payment Endpoint
     * @since 1.10.19
     * @var string
     */
    public static $payment_endpoint;

    /**
     * Get Terminal Endpoint
     * @since 1.10.19
     */
    public static $endpoint;

    /**
     * Plugin Mode
     * @since 1.10.19
     * @var string
     */
    public static $plugin_mode;

    /**
     * Request Header
     * @since 1.12.0
     * @var array
     */
    public static $request_header;

    use Menus, Ajax, Shipping, Activation, Assets, TerminalRESTAPI;

    /**
     * Constructor
     * @since 1.11.10
     * @return void
     */
    public function __construct()
    {
        /**
         * Set the request header
         * 
         * @since 1.12.0
         * @var array
         */
        self::$request_header = [
            'x-terminal-source' => 'wordpress'
        ];

        //check if terminal_africa_settings is set
        if ($settings = get_option("terminal_africa_settings")) {
            //set skkey
            self::$skkey = $settings["secret_key"];
            //set public_key
            self::$public_key = $settings["public_key"];
            //set user_id
            self::$user_id = $settings["user_id"];

            //payment status
            $payment_gateway_status = "inactive";
            //check if isset payment_gateway_status
            if (isset($settings['others']->user->payment_gateway_status)) {
                $payment_gateway_status = $settings['others']->user->payment_gateway_status;
            }

            //set user_payment_status
            self::$user_payment_status = $payment_gateway_status;

            //set endpoint
            $validate_keys = $this->checkKeys($settings["public_key"], $settings["secret_key"]);
            self::$enpoint = $validate_keys['endpoint'];
            //set the value
            self::$endpoint = $validate_keys['endpoint'];
            self::$plugin_mode = $validate_keys['mode'];
            //set payment endpoint
            self::$payment_endpoint = $validate_keys['payment_endpoint'];
        } else {
            //set the value to null
            self::$skkey = null;
            self::$public_key = null;
            self::$user_id = null;
            self::$user_payment_status = "inactive";
            self::$enpoint = TERMINAL_AFRICA_API_ENDPOINT;
            //set the value
            self::$endpoint = TERMINAL_AFRICA_API_ENDPOINT;
            self::$plugin_mode = 'test';
            //set payment endpoint
            self::$payment_endpoint = TERMINAL_AFRICA_PAYMENT_API_ENDPOINT;
        }
    }

    /**
     * instance
     * @since 1.11.10
     * @return object|TerminalAfricaShippingPlugin
     */
    public static function instance()
    {
        //check if instance is set
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Init the plugin
     * @since 1.0.0
     * @return void
     */
    public function init()
    {
        try {
            //add settings page
            add_action('admin_menu', array(self::class, 'add_settings_page'), PHP_INT_MAX);
            //woocommerce_countries
            add_filter('woocommerce_countries', array(self::class, 'woocommerce_countries'), PHP_INT_MAX);
            //woocommerce_states
            add_filter('woocommerce_states', array(self::class, 'woocommerce_states'), PHP_INT_MAX);
            //plugin loaded
            add_action('plugins_loaded', array(self::class, 'activate'), PHP_INT_MAX);
            //enqueue scripts
            add_action('admin_enqueue_scripts', array(self::class, 'enqueue_scripts'), PHP_INT_MAX);
            //enqueue scripts
            add_action('wp_enqueue_scripts', array(self::class, 'enqueue_frontend_script'), 1);
            //fluid_checkout_override_style
            add_action('wp_head', array(self::class, 'fluid_checkout_override_style'), PHP_INT_MAX);
            //wp head
            add_action('wp_head', array(self::class, 'wp_head_checkout'), PHP_INT_MAX);
            add_action('woocommerce_checkout_update_order_review', array($this, 'checkout_update_refresh_shipping_methods'), PHP_INT_MAX, 1);
            // add_action('woocommerce_add_to_cart', array($this, 'remove_wc_session_on_cart_action'), 10, 6);
            //listen to add to cart
            add_action('woocommerce_add_to_cart', array($this, 'add_to_cart_event'), 10, 6);
            //listen to update cart
            // add_action('woocommerce_after_cart_item_quantity_update', array($this, 'update_cart_event'), 10, 3);
            //listen to remove cart
            add_action('woocommerce_cart_item_removed', array($this, 'remove_cart_event'), 10, 2);
            //add new column to shop order page
            add_filter('manage_edit-shop_order_columns', array($this, 'terminal_add_new_order_admin_list_column'), 20);
            //add new column to shop order page
            add_action('manage_shop_order_posts_custom_column', array($this, 'terminal_add_new_order_admin_list_column_content'), 20, 2);
            //filter woocommerce_' . $this->order_type . '_list_table_columns
            add_filter('woocommerce_shop_order_list_table_columns', array($this, 'terminal_add_new_order_admin_list_column'), 10);
            //woocommerce_' . $this->order_type . '_list_table_custom_column
            add_action('woocommerce_shop_order_list_table_custom_column', array($this, 'terminal_add_new_order_admin_list_column_content'), 10, 2);
            //init ajax
            $this->init_ajax();
            //initAPI
            $this->initAPI();
            //initPaymentGateway
            $this->initPaymentGateway();
            //activate terminal
            $this->activate_terminal_init();
        } catch (\Exception $e) {
            logTerminalError($e, 'terminal_init_issue');
        }
    }

    /**
     * Init Payment Gateway
     * 
     * Terminal provide merchants with the tools and services needed to accept online payments from local and international customers using Mastercard, Visa, Verve Cards
     * @link https://www.terminal.africa/integrations?utm_source=web
     * @since 1.11.10
     * @author Adeleye Ayodeji
     * 
     * @return void
     */
    public function initPaymentGateway()
    {
        //check if user_payment_status is active
        if (self::$user_payment_status == "active") {
            //init payment gateway
            require_once TERMINAL_AFRICA_PLUGIN_PATH . '/includes/payment-gateway/class-terminal-payment.php';
        }
    }

    /**
     * Add new column to shop order page
     * @param $columns
     * @since 1.10.5
     * @return void
     */
    public function terminal_add_new_order_admin_list_column($columns)
    {
        $filter = '<span>
        <img src="' . TERMINAL_AFRICA_PLUGIN_ASSETS_URL . '/img/logo.svg" style="margin-right: 5px;" alt="" align="left"> Terminal Africa
        </span>';

        //add column to the second position
        $columns = array_slice($columns, 0, 2, true) +
            ['terminal_shipment_status' => $filter] +
            array_slice($columns, 2, count($columns) - 2, true);

        return $columns;
    }

    /**
     * Add new column to shop order page
     * @param $column
     * @since 1.10.5
     * @return void
     */
    public function terminal_add_new_order_admin_list_column_content($column, $order)
    {
        global $terminal_allowed_order_statuses;
        //remove all "wc" in terminal_allowed_order_statuses array
        $terminal_allowed_order_statuses = array_map(function ($status) {
            return str_replace('wc-', '', $status);
        }, $terminal_allowed_order_statuses);

        //check if order is an instance of WC_Order
        if ($order && $order instanceof WC_Order) {
            //get order id
            $post_id = $order->get_id();
        } else {
            //get order id
            $post_id = $order;
        }

        if ('terminal_shipment_status' === $column) {
            //check if the order status is in processing, on-hold, completed, pending
            $order = wc_get_order($post_id);
            //get order status
            $order_status = $order->get_status();
            //check if order status is processing, on-hold, completed, pending
            $default_status = ['processing', 'on-hold', 'completed', 'pending'];
            //append terminal_allowed_order_statuses to default_status
            $default_status = $default_status + $terminal_allowed_order_statuses;
            if (in_array($order_status, $default_status)) {
                //get terminal shipment status
                $terminal_shipment_id = get_post_meta($post_id, 'Terminal_africa_shipment_id', true);
                //rate id
                $rate_id = get_post_meta($post_id, 'Terminal_africa_rateid', true);
                //get Terminal_africa_carrierlogo
                $carrirer_logo = get_post_meta($post_id, 'Terminal_africa_carrierlogo', true) ?: TERMINAL_AFRICA_PLUGIN_ASSETS_URL . '/img/logo.svg';
                //check if terminal_shipment_id is set
                if (!empty($terminal_shipment_id)) {
                    //echo terminal_shipment_id
                    //plugin url
                    $plugin_url = admin_url('admin.php?page=terminal-africa');
                    //arg
                    $arg = array(
                        'page' => 'terminal-africa',
                        'action' => 'edit',
                        'id' => esc_html($terminal_shipment_id),
                        'order_id' => esc_html($post_id),
                        'rate_id' => esc_html($rate_id),
                        'nonce' => wp_create_nonce('terminal_africa_edit_shipment')
                    );
                    $plugin_url = add_query_arg($arg, $plugin_url);
                    //echo woocommerce status button 
                    echo "<a href='{$plugin_url}' class='button' title='Manage Terminal Shipment' style='font-size: 11px;min-height: 25px;'>
                    <span style='margin-right: 5px;'>
                       <img src='" . esc_attr($carrirer_logo) . "' style='height:10px;' />
                    </span> 
                    <span>
                       Manage Shipment
                    </span>
                    </a>";
                } else {
                    echo "N/A";
                }
            } else {
                //do nothing
                echo "N/A";
            }
        }
    }

    /**
     * Added to cart event
     * @param $cart_item_key
     * @param $product_id
     * @param $quantity
     * @param $variation_id
     * @param $variation
     * @param $cart_item_data
     * @since 1.10.5
     * @return void
     */
    public function add_to_cart_event($cart_item_key, $product_id, $quantity, $variation_id, $variation, $cart_item_data)
    {
        //create or update terminal parcel
        $this->terminal_africa_save_cart_item_event();
    }

    /**
     * Update cart event
     * @param $cart_item_key
     * @param $quantity
     * @param $old_quantity
     * @since 1.10.5
     * @return void
     */
    public function update_cart_event($cart_item_key, $quantity, $old_quantity)
    {
        //create or update terminal parcel
        $this->terminal_africa_save_cart_item_event();
    }

    /**
     * Remove cart event
     * @param $cart_item_key
     * @param $cart
     * @since 1.10.5
     * @return void
     */
    public function remove_cart_event($cart_item_key, $cart)
    {
        //create or update terminal parcel
        $this->terminal_africa_save_cart_item_event("removed");
    }

    public function checkout_update_refresh_shipping_methods($post_data)
    {
        //update shipping pricing realtime
        $packages = WC()->cart->get_shipping_packages();
        foreach ($packages as $package_key => $package) {
            WC()->session->set('shipping_for_package_' . $package_key, false); // Or true
        }
    }

    /**
     * Process terminal parcel on cart event
     * @since 1.10.5
     * @return void
     */
    public function terminal_africa_save_cart_item_event($type = null)
    {
        try {
            //terminal_check_checkout_product_for_shipping_support
            $check_shipping_support = terminal_check_checkout_product_for_shipping_support();
            ///check if check_shipping_support is "false"
            if ($check_shipping_support === "false") {
                //check if type is remove 
                if (!empty($type) && $type == "removed") {
                    //do nothing
                } else {
                    //return
                    return;
                }
            }

            //recaculate cart total
            WC()->cart->calculate_totals();

            //get cart item
            $cart_item = WC()->cart->get_cart();
            //check if type is remove 
            if (!empty($type) && $type == "removed") {
                //do nothing
            } else {
                //check if cart item is empty
                if (empty($cart_item)) {
                    //do nothing
                    return;
                }
            }

            $data_items = [];
            //loop through cart items
            foreach ($cart_item as $item) {
                $data_items[] = [
                    'name' => $item['data']->get_name(),
                    'quantity' => $item['quantity'],
                    'value' => $item['line_total'],
                    'description' => "{$item['quantity']} of {$item['data']->get_name()} at {$item['data']->get_price()} each for a total of {$item['line_total']}",
                    'type' => 'parcel',
                    'currency' => get_woocommerce_currency(),
                    'weight' => (float)$item['data']->get_weight() ?: 0.1,
                ];
            }
            //check if terminal_default_packaging_id is set
            $packaging_id = get_option('terminal_default_packaging_id');
            //verify packaging id
            $verifyDefaultPackaging = verifyDefaultPackaging($packaging_id);
            //check if verifyDefaultPackaging is 200
            if ($verifyDefaultPackaging['code'] != 200) {
                //do nothing
                return;
            }
            //get new packaging id
            $packaging_id = $verifyDefaultPackaging['packaging_id'];
            //arrange parcel
            $parcel = [
                'packaging' => $packaging_id,
                'weight_unit' => 'kg',
                'items' => $data_items,
                'description' => 'Order from ' . get_bloginfo('name'),
            ];
            //terminal session
            $terminalSession = TerminalSession::instance();
            //check if terminal_africa_parcel_id is set
            $parcel_id = $terminalSession->get('terminal_africa_parcel_id');
            if (!empty($parcel_id)) {
                //update parcel
                $response = updateTerminalParcel($parcel_id, $parcel);
                //check if response is 200
                if ($response['code'] == 200) {
                    //do nothing
                    return;
                } else {
                    //do nothing
                    return;
                }
            }
            //post request
            $response = createTerminalParcel($parcel);
            //check if response is 200
            if ($response['code'] == 200) {
                //save parcel wc session
                $terminalSession->set('terminal_africa_parcel_id', $response['data']->parcel_id);
                //packaging wc session
                WC()->session->set('terminal_africa_packaging_id', $response['data']->packaging);
                //do nothing
                return;
            } else {
                //do nothing
                return;
            }
        } catch (\Exception $e) {
            logTerminalError($e, 'terminal_cart_event');
        }
    }

    /**
     * Remove WC Session on Cart Action
     * @param $cart_item_key
     * @param $product_id
     * @param $quantity
     * @param $variation_id
     * @param $variation
     * @param $cart_item_data
     * @since 1.11.10
     * @return void
     */
    public function remove_wc_session_on_cart_action($cart_item_key, $product_id, $quantity, $variation_id, $variation, $cart_item_data)
    {
        //check if WC is available
        if (function_exists('WC')) {
            //get all sessions
            $sessions = WC()->session->get_session_data();
            //loop through sessions
            foreach ($sessions as $key => $value) {
                //check if session is terminal_africa
                if (strpos($key, 'terminal_africa') !== false) {
                    //remove session
                    WC()->session->__unset($key);
                }
            }
        }
    }
}

/**
 * Init Terminal Africa
 * @since 1.0.0
 * @return void
 */
$TerminalAfricaShippingPlugin = TerminalAfricaShippingPlugin::instance();
//init
$TerminalAfricaShippingPlugin->init();
