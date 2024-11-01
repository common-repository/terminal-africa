<?php

use App\Terminal\Core\TerminalSession;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

/**
 * Main Terminal Delivery Class.
 *
 * @class  WC_Terminal_Delivery
 */
class WC_Terminal_Delivery
{
    /** @var array settings value for this plugin */
    public $settings;

    /** @var array order status value for this plugin */
    public $statuses;

    /** @var \WC_Terminal_Delivery single instance of this plugin */
    protected static $instance;

    /**
     * Loads functionality/admin classes and add auto schedule order hook.
     *
     * @since 1.0
     */
    public function __construct()
    {
        // get settings
        $this->settings = maybe_unserialize(get_option('woocommerce_Terminal_delivery_settings'));

        $this->init_plugin();

        $this->init_hooks();
    }

    /**
     * Initializes the plugin.
     *
     * @internal
     *
     * @since 2.4.0
     */
    public function init_plugin()
    {
        $this->includes();
    }

    /**
     * Includes the necessary files.
     *
     * @since 1.0.0
     */
    public function includes()
    {
        require __DIR__ . '/class-terminal-shipping-method.php';
    }

    /**
     * Initialize hooks.
     *
     * @since 1.0.0
     */
    public function init_hooks()
    {
        /**
         * Actions
         */

        // create order when \WC_Order::payment_complete() is called
        // add_action('woocommerce_thankyou', array($this, 'create_order_shipping_task'));

        add_action('woocommerce_shipping_init', array($this, 'load_shipping_method'), PHP_INT_MAX);

        add_action('woocommerce_checkout_update_order_meta', array($this, 'save_terminal_delivery_order_meta'), PHP_INT_MAX);

        //woocommerce_checkout_order_created
        add_action('woocommerce_checkout_order_created', array($this, 'save_terminal_delivery_order_meta'), PHP_INT_MAX);

        // cancel a Terminal delivery task when an order is cancelled in WC
        // add_action('woocommerce_order_status_cancelled', array($this, 'cancel_order_shipping_task'));

        // adds tracking button(s) to the View Order page
        // add_action('woocommerce_order_details_after_order_table', array($this, 'add_view_order_tracking'));

        //order edit page actions
        add_action('woocommerce_admin_order_data_after_shipping_address', array($this, 'add_order_meta_box'), PHP_INT_MAX);

        /**
         * Filters
         */
        // Add shipping icon to the shipping label
        add_filter('woocommerce_cart_shipping_method_full_label', array($this, 'add_shipping_icon'), PHP_INT_MAX, 2);

        add_filter('woocommerce_checkout_fields', array($this, 'remove_address_2_checkout_fields'), PHP_INT_MAX, 1);

        add_filter('woocommerce_shipping_methods', array($this, 'add_shipping_method'), PHP_INT_MAX);

        add_filter('woocommerce_shipping_calculator_enable_city', '__return_true');
        // add_filter('woocommerce_shipping_calculator_enable_postcode', '__return_true');
        //woocommerce_checkout_update_order_review
        add_filter('woocommerce_checkout_update_order_review', array($this, 'update_order_review'), PHP_INT_MAX, 1);

        //init
        add_action('wp', array($this, 'init'), PHP_INT_MAX);
    }

    //init
    public function init()
    {
        //check if checkout-for-woocommerce/checkout-for-woocommerce.php is active
        if (in_array('checkout-for-woocommerce/checkout-for-woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
            //filter the postal code
            //wp head
            add_action('wp_head', array($this, 'filter_postal_code'), PHP_INT_MAX);
            //filter checkout wc
        } else if (in_array('checkoutwc-lite/checkout-for-woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
            //filter the postal code
            //wp head
            add_action('wp_head', array($this, 'filter_postal_code'), PHP_INT_MAX);
            //filter checkout wc
        }
    }

    /**
     * terminal_autoload_merchant_address
     * @return mixed
     */
    public static function terminal_autoload_merchant_address()
    {
        try {
            return [];
            //check if session is started
            // if (session_status() == PHP_SESSION_NONE) {
            //     session_start();
            // }
            // //check if merchant_address_data is set
            // if (isset($_SESSION['terminal_africa_merchant_address_data'])) {
            //     return $_SESSION['terminal_africa_merchant_address_data'];
            // }

            // //get merchant_address_id
            // $merchant_address_id = get_option('terminal_africa_merchant_address_id');
            // //check if merchant_address_id is set
            // if (!$merchant_address_id) {
            //     return [];
            // }

            // //get merchant address data
            // $merchant_address_data = getTerminalAddressById($merchant_address_id);
            // //check if code is 200
            // if ($merchant_address_data['code'] == 200) {
            //     $data = $merchant_address_data['data'];
            //     //get availables cities for the merchant address
            //     $available_cities = get_terminal_cities($data->country, $data->state_code);
            //     //mdata
            //     $mdata = [
            //         'country' => $data->country,
            //         'state' => $data->state_code,
            //         'city' => $data->city,
            //         'zip' => $data->zip,
            //         'cities' => $available_cities['data'],
            //     ];
            //     //save to session
            //     $_SESSION['terminal_africa_merchant_address_data'] = $mdata;
            //     //return merchant_address_data
            //     return $mdata;
            // }
            //return empty array if nothing found
            return [];
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * filter_postal_code
     * @return void
     */
    public function filter_postal_code()
    {
        echo '<style>';
        //#shipping_postcode_field
        echo '#shipping_postcode_field{display:block !important;}';
        //style
        echo '</style>';
    }

    public function removeShipment($order_id)
    {
        $order = wc_get_order($order_id);
        $items    = (array) $order->get_items('shipping');
        // // Loop through shipping items
        foreach ($items as $item) {
            //get shipping method id
            $shipping_method_id = $item->get_method_id();
            //if shipping method id is terminal_delivery
            if ($shipping_method_id == "terminal_delivery") {
                //remove order item shipping
                $order->remove_item($item->get_id());
            }
        }
        //calculate totals
        $order->calculate_totals();
    }

    /**
     * Add order meta box
     * @param $order 
     * @return void
     */
    public function add_order_meta_box($order)
    {
        $terminal_africa_merchant_id = sanitize_text_field(get_option('terminal_africa_merchant_id'));
        //check if mode is live or test
        $mode = 'test';
        //check if class exist TerminalAfricaShippingPlugin
        if (class_exists('TerminalAfricaShippingPlugin')) {
            $TerminalAfricaShippingPlugin = \TerminalAfricaShippingPlugin::instance();
            if ($TerminalAfricaShippingPlugin::$plugin_mode) {
                $mode = $TerminalAfricaShippingPlugin::$plugin_mode;
            }
        }
        //check if order has meta Terminal_africa_merchant_id
        $order_terminal_africa_merchant_id = get_post_meta($order->get_id(), 'Terminal_africa_merchant_id', true);
        if ($order_terminal_africa_merchant_id != $terminal_africa_merchant_id) {
            //remove order item shipping
            $this->removeShipment($order->get_id());
            return;
        }
        //check if order has meta Terminal_africa_mode
        $order_terminal_africa_mode = get_post_meta($order->get_id(), 'Terminal_africa_mode', true);
        if ($order_terminal_africa_mode != $mode) {
            //remove order item shipping
            $this->removeShipment($order->get_id());
            return;
        }
        $plugin_path = TERMINAL_AFRICA_PLUGIN_FILE;
        $order_id = $order->get_id();
        $icon_url = plugins_url('assets/img/logo.svg', $plugin_path);
        //overwirte icon url
        $icon_url = get_post_meta($order_id, 'Terminal_africa_carrierlogo', true) ?: $icon_url;
        $shipment_id = get_post_meta($order_id, 'Terminal_africa_shipment_id', true);
        $carrier_name = get_post_meta($order_id, 'Terminal_africa_carriername', true);
        $rate_id = get_post_meta($order_id, 'Terminal_africa_rateid', true);
        $delivery_time = get_post_meta($order_id, 'Terminal_africa_duration', true);
        //terminal api ping
        $terminal_africa_api_ping = get_post_meta($order_id, 'Terminal_africa_api_ping', true);
        //if shipment id is empty
        if (empty($shipment_id)) {
            return;
        }
        //check if order shipping method is terminal_delivery
        $shipping_method = $order->get_shipping_methods();
        if (empty($shipping_method)) {
            //check Terminal_africa_api_ping is yes
            if ($terminal_africa_api_ping != 'yes') {
                //return if not active
                return;
            }
        }
        //get order currency
        $order_currency = $order->get_currency();
        //loop through shipping method
        $items    = (array) $order->get_items('shipping');
        //$shipping_cost
        $shipping_cost = 0;
        //terminal method is active
        $terminal_method_is_active = false;
        // Loop through shipping items
        foreach ($items as $item) {
            //get shipping method id
            $shipping_method_id = $item->get_method_id();
            //if shipping method id is terminal_delivery
            if ($shipping_method_id == "terminal_delivery") {
                $terminal_method_is_active = true;
                //get shipping cost
                $shipping_cost = $item->get_total();
                break;
            }
        }
        //check if terminal_africa_api_ping is yes
        if ($terminal_africa_api_ping == 'yes') {
            //update the shipping cost to the api amount
            $shipping_cost = get_post_meta($order_id, "Terminal_africa_amount", true) ?: $shipping_cost;
        }

        //if terminal method is not active
        if (!$terminal_method_is_active) {
            //check Terminal_africa_api_ping is yes
            if ($terminal_africa_api_ping != 'yes') {
                //return if not active
                return;
            }
        }
        //check if terminal_africa_delivery_arranged is yes
        $terminal_africa_delivery_arranged = get_post_meta($order_id, 'terminal_africa_delivery_arranged', true);
        if ($terminal_africa_delivery_arranged == 'yes') {
            $note = "Manage Delivery &rarr;";
        } else {
            $note = "Arrange Delivery &rarr;";
        }
        //plugin url
        $plugin_url = admin_url('admin.php?page=terminal-africa');
        //arg
        $arg = array(
            'page' => 'terminal-africa',
            'action' => 'edit',
            'id' => esc_html($shipment_id),
            'order_id' => esc_html($order_id),
            'rate_id' => esc_html($rate_id),
            'nonce' => wp_create_nonce('terminal_africa_edit_shipment')
        );
        $plugin_url = add_query_arg($arg, $plugin_url);
        //guest email
        $guestEmail = get_post_meta($order_id, 'Terminal_africa_guest_email', true);
        //get order 
        echo "<h4> <img src='" . esc_url($icon_url) . "' align='left' style='margin-right: 5px;width: auto;
    height: auto;
    max-width: 20px;'/>" . esc_html($carrier_name . ' - ' . $delivery_time ?: 'Terminal Delivery') . "</h4>";
        echo "<p><strong>Delivery Carrier Name: </strong>" . esc_html($carrier_name) . "</p>";
        echo "<p><strong>Delivery Amount: </strong>" . wc_price(esc_html($shipping_cost), array('currency' => $order_currency)) . "</p>";
        echo "<p><strong>Pickup Time: </strong>" . esc_html(get_post_meta($order_id, 'Terminal_africa_pickuptime', true)) . "</p>";
        echo "<p><strong>Delivery Time: </strong>" . esc_html($delivery_time) . "</p>";
        echo "<p><strong>Delivery Rate ID: </strong>" . esc_html($rate_id) . "</p>";
        echo "<p><strong>Shipment ID: </strong>" . esc_html($shipment_id) . "</p>";
        if (!empty($guestEmail)) {
            echo "<p><strong>Delivery Guest Email: </strong>" . esc_html(get_post_meta($order_id, 'Terminal_africa_guest_email', true)) . "</p>";
        }
        //manage shipping from terminal delivery
        echo "<p><strong><a href='" . esc_url($plugin_url) . "' style='background: orange;
    text-decoration: none;
    color: white;
    padding: 8px;
    border-radius: 6px;
    outline: none;'>" . $note . "</a></strong></p>";
    }

    /**
     * shipping_icon.
     *
     * @since   1.0.0
     */
    function add_shipping_icon($label, $method)
    {
        if ($method->method_id == 'terminal_delivery') {
            $plugin_path = TERMINAL_AFRICA_PLUGIN_FILE;
            $logo_title = 'Terminal Delivery';
            $icon_url = plugins_url('assets/img/logo.svg', $plugin_path);
            $img = '<img class="Terminal-delivery-logo" align="left"' .
                ' alt="' . $logo_title . '"' .
                ' title="' . $logo_title . '"' .
                ' style="width: auto;
    height: auto;
    margin-right: 10px;
    max-width: 20px;    display: inline;"' .
                ' src="' . $icon_url . '"' .
                '>';
            $label = $img . ' ' . $label;
        }

        return $label;
    }

    //save_terminal_delivery_order_meta
    public function save_terminal_delivery_order_meta($order)
    {
        //check if order is an instance of WC_Order
        if ($order && $order instanceof WC_Order) {
            //get order id
            $order_id = $order->get_id();
        } else {
            //get order id
            $order_id = $order;
        }
        //check if session is started
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        //check if session exists
        $terminal_africa_carriername = WC()->session->get('terminal_africa_carriername');
        $terminal_africa_amount = WC()->session->get('terminal_africa_amount');
        $terminal_africa_duration = WC()->session->get('terminal_africa_duration');
        $guest_email = WC()->session->get('terminal_africa_guest_email');
        //guest email hashed
        $guest_email_hashed = md5($guest_email);
        //terminal_africa_rateid
        $terminal_africa_rateid = WC()->session->get('terminal_africa_rateid');
        $terminal_africa_pickuptime = WC()->session->get('terminal_africa_pickuptime');
        $terminal_africa_carrierlogo = WC()->session->get('terminal_africa_carrierlogo');
        $terminal_africa_merchant_id = sanitize_text_field(get_option('terminal_africa_merchant_id'));
        $merchant_address_id = get_option('terminal_africa_merchant_address_id');
        //terminal session
        $terminalSession = TerminalSession::instance();
        //check if address id is set
        $guest_address_id = $terminalSession->get('terminal_africa_guest_address_id' . $guest_email_hashed);
        //check if not empty $parcel_id 
        $parcel_id = $terminalSession->get('terminal_africa_parcel_id');
        //check if mode is live or test
        $mode = 'test';
        //check if class exist TerminalAfricaShippingPlugin
        if (class_exists('TerminalAfricaShippingPlugin')) {
            $TerminalAfricaShippingPlugin = \TerminalAfricaShippingPlugin::instance();
            if ($TerminalAfricaShippingPlugin::$plugin_mode) {
                $mode = $TerminalAfricaShippingPlugin::$plugin_mode;
            }
        }
        //if exist
        if ($merchant_address_id && $terminal_africa_carriername && $terminal_africa_amount && $terminal_africa_duration && $guest_email && $terminal_africa_rateid) {

            //create shipment
            $create_shipment = createTerminalShipment($merchant_address_id, $guest_address_id, $parcel_id, $order_id);
            //$shipment_id
            $shipment_id = null;
            //check if shipment is created
            if ($create_shipment['code'] == 200) {
                //wc session
                WC()->session->set('terminal_africa_shipment_id' . $guest_email, $create_shipment['data']->shipment_id);
                $shipment_id = $create_shipment['data']->shipment_id;
            } else {
                //order error note
                $order = wc_get_order($order_id);
                $order->add_order_note(
                    __('Terminal Africa Error: ' . $create_shipment['message'], 'terminal-africa-shipping')
                );
                //return
                return;
            }
            //check if $shipment_id
            if (!$shipment_id) {
                //order error note
                $order = wc_get_order($order_id);
                $order->add_order_note(
                    __('Terminal Africa Error: ' . $create_shipment['message'], 'terminal-africa-shipping')
                );
                //return
                return;
            }

            //check if $terminal_africa_amount is not string
            if (is_string($terminal_africa_amount)) {
                $terminal_africa_amount = floatval($terminal_africa_amount);
            }
            //sanitize data
            $terminal_africa_carriername = sanitize_text_field($terminal_africa_carriername);
            $terminal_africa_amount = sanitize_text_field($terminal_africa_amount);
            $terminal_africa_duration = sanitize_text_field($terminal_africa_duration);
            $guest_email = sanitize_text_field($guest_email);
            $terminal_africa_rateid = sanitize_text_field($terminal_africa_rateid);
            $shipment_id = sanitize_text_field($shipment_id);
            $terminal_africa_pickuptime = sanitize_text_field($terminal_africa_pickuptime);
            $terminal_africa_carrierlogo = sanitize_text_field($terminal_africa_carrierlogo);

            //save
            update_post_meta($order_id, 'Terminal_africa_carriername', $terminal_africa_carriername);
            update_post_meta($order_id, 'Terminal_africa_amount', $terminal_africa_amount);
            update_post_meta($order_id, 'Terminal_africa_duration', $terminal_africa_duration);
            update_post_meta($order_id, 'Terminal_africa_guest_email', $guest_email);
            update_post_meta($order_id, 'Terminal_africa_rateid', $terminal_africa_rateid);
            update_post_meta($order_id, 'Terminal_africa_shipment_id', $shipment_id);
            update_post_meta($order_id, 'Terminal_africa_pickuptime', $terminal_africa_pickuptime);
            update_post_meta($order_id, 'Terminal_africa_carrierlogo', $terminal_africa_carrierlogo);
            update_post_meta($order_id, 'Terminal_africa_merchant_id', $terminal_africa_merchant_id);
            update_post_meta($order_id, 'Terminal_africa_mode', $mode);

            //save to wc order meta
            if ($order && $order instanceof WC_Order) {
                //save 
                $order->update_meta_data('Terminal_africa_carriername', $terminal_africa_carriername);
                $order->update_meta_data('Terminal_africa_amount', $terminal_africa_amount);
                $order->update_meta_data('Terminal_africa_duration', $terminal_africa_duration);
                $order->update_meta_data('Terminal_africa_guest_email', $guest_email);
                $order->update_meta_data('Terminal_africa_rateid', $terminal_africa_rateid);
                $order->update_meta_data('Terminal_africa_shipment_id', $shipment_id);
                $order->update_meta_data('Terminal_africa_pickuptime', $terminal_africa_pickuptime);
                $order->update_meta_data('Terminal_africa_carrierlogo', $terminal_africa_carrierlogo);
                $order->update_meta_data('Terminal_africa_merchant_id', $terminal_africa_merchant_id);
                $order->update_meta_data('Terminal_africa_mode', $mode);
                //save order
                $order->save();
            } else {
                //get order id
                $order_id = $order;
                //get order
                $order = wc_get_order($order_id);
                //save
                $order->update_meta_data('Terminal_africa_carriername', $terminal_africa_carriername);
                $order->update_meta_data('Terminal_africa_amount', $terminal_africa_amount);
                $order->update_meta_data('Terminal_africa_duration', $terminal_africa_duration);
                $order->update_meta_data('Terminal_africa_guest_email', $guest_email);
                $order->update_meta_data('Terminal_africa_rateid', $terminal_africa_rateid);
                $order->update_meta_data('Terminal_africa_shipment_id', $shipment_id);
                $order->update_meta_data('Terminal_africa_pickuptime', $terminal_africa_pickuptime);
                $order->update_meta_data('Terminal_africa_carrierlogo', $terminal_africa_carrierlogo);
                $order->update_meta_data('Terminal_africa_merchant_id', $terminal_africa_merchant_id);
                $order->update_meta_data('Terminal_africa_mode', $mode);
                //save order
                $order->save();
            }

            //delete session
            WC()->session->__unset('terminal_africa_carriername');
            WC()->session->__unset('terminal_africa_amount');
            WC()->session->__unset('terminal_africa_duration');
            WC()->session->__unset('terminal_africa_guest_email');
            WC()->session->__unset('terminal_africa_rateid');
            WC()->session->__unset('terminal_africa_pickuptime');
            WC()->session->__unset('terminal_africa_shipment_id' . $guest_email_hashed);
            WC()->session->__unset('terminal_africa_carrierlogo');
            //delete session
            $terminalSession->destroy();
            //delete terminal_africa_parcel_id
            $terminal_africa_parcel_id = $terminalSession->get('terminal_africa_parcel_id');
            if ($terminal_africa_parcel_id) {
                $terminalSession->unset('terminal_africa_parcel_id');
            }
            $address_id = $terminalSession->get('terminal_africa_guest_address_id' . $guest_email_hashed);
            if ($address_id) {
                $terminalSession->unset('terminal_africa_guest_address_id' . $guest_email_hashed);
            }
            //clear session cache
            $terminalSession->destroy();
        } else {
            return;
        }
    }

    public function remove_address_2_checkout_fields($fields)
    {
        unset($fields['billing']['billing_address_2']);
        unset($fields['shipping']['shipping_address_2']);
        //check if logged in
        if (is_user_logged_in()) {
            $user_id = get_current_user_id();
            $billing_postcode = get_user_meta($user_id, 'billing_postcode', true);
        } else {
            $billing_postcode = '';
        }
        //enable first name
        $fields['billing']['billing_first_name'] = array(
            'label' => __('First name', 'terminal-africa'),
            'placeholder' => _x('First name', 'placeholder', 'terminal-africa'),
            'required' => true,
            'class' => array('form-row-first'),
            'clear' => true,
            'priority' => 10,
        );
        //enable last name
        $fields['billing']['billing_last_name'] = array(
            'label' => __('Last name', 'terminal-africa'),
            'placeholder' => _x('Last name', 'placeholder', 'terminal-africa'),
            'required' => true,
            'class' => array('form-row-last'),
            'clear' => true,
            'priority' => 20,
        );
        //email 
        $fields['billing']['billing_email'] = array(
            'label' => __('Email address', 'terminal-africa'),
            'placeholder' => _x('Email address', 'placeholder', 'terminal-africa'),
            'required' => true,
            'clear' => true,
            'priority' => 30,
        );
        //phone
        $fields['billing']['billing_phone'] = array(
            'label' => __('Phone', 'terminal-africa'),
            'placeholder' => _x('Phone', 'placeholder', 'terminal-africa'),
            'required' => true,
            'clear' => true,
            'priority' => 40,
        );
        //enable zip code
        $fields['billing']['billing_postcode'] = array(
            'label' => __('Postcode / ZIP', 'terminal-africa'),
            'placeholder' => _x('Postcode / ZIP', 'placeholder', 'terminal-africa'),
            'clear' => true,
            'priority' => 50,
            'required' => false,
            'default' => $billing_postcode,
        );
        //address
        $fields['billing']['billing_address_1'] = array(
            'label' => __('Address', 'terminal-africa'),
            'placeholder' => _x('Address', 'placeholder', 'terminal-africa'),
            'required' => true,
            'clear' => true,
            'priority' => 60,
        );
        //Get default merchant autoload address data
        $defaultMerchantAddress = terminal_autoload_merchant_address();
        //country
        $fields['billing']['billing_country'] = array(
            'type' => 'country',
            'label' => __('Country', 'terminal-africa'),
            'placeholder' => _x('Country', 'placeholder', 'terminal-africa'),
            'required' => true,
            'class' => array('form-row-wide', 'address-field', 'update_totals_on_change'),
            'clear' => true,
            'priority' => 70,
            'default' => isset($defaultMerchantAddress['country']) ? $defaultMerchantAddress['country'] : '',
        );
        //state
        $fields['billing']['billing_state'] = array(
            'type' => 'state',
            'label' => __('State / County', 'terminal-africa'),
            'placeholder' => _x('State / County', 'placeholder', 'terminal-africa'),
            'required' => true,
            'class' => array('form-row-wide', 'address-field'),
            'validate' => array('state'),
            'clear' => true,
            'priority' => 80,
            'default' => isset($defaultMerchantAddress['state']) ? $defaultMerchantAddress['state'] : '',
        );
        //haystack
        $haystack = apply_filters('active_plugins', get_option('active_plugins'));
        //use in array check
        if (in_array('checkout-for-woocommerce/checkout-for-woocommerce.php', $haystack)) {
            //return and stop here for checkout wc
            return $fields;
        } else if (in_array('checkoutwc-lite/checkout-for-woocommerce.php', $haystack)) {
            //return and stop here for checkout wc
            return $fields;
        } else if (in_array('fluid-checkout/fluid-checkout.php', $haystack)) {
            //return and stop here for fluid checkout
            return $fields;
        }
        //continue for woocommerce native checkout

        //Shipping
        //enable first name
        $fields['shipping']['shipping_first_name'] = array(
            'label' => __('First name', 'terminal-africa'),
            'placeholder' => _x('First name', 'placeholder', 'terminal-africa'),
            'required' => true,
            'class' => array('form-row-first'),
            'clear' => true,
            'priority' => 10,
        );
        //shipping_email
        $fields['shipping']['shipping_email'] = array(
            'label' => __('Email address', 'terminal-africa'),
            'placeholder' => _x('Email address', 'placeholder', 'terminal-africa'),
            'required' => true,
            'clear' => true,
            'priority' => 20,
        );
        //enable last name
        $fields['shipping']['shipping_last_name'] = array(
            'label' => __('Last name', 'terminal-africa'),
            'placeholder' => _x('Last name', 'placeholder', 'terminal-africa'),
            'required' => true,
            'class' => array('form-row-last'),
            'clear' => true,
            'priority' => 20,
        );
        //address
        $fields['shipping']['shipping_address_1'] = array(
            'label' => __('Address', 'terminal-africa'),
            'placeholder' => _x('Address', 'placeholder', 'terminal-africa'),
            'required' => true,
            'clear' => true,
            'priority' => 30,
        );
        //postcode
        $fields['shipping']['shipping_postcode'] = array(
            'label' => __('Postcode / ZIP', 'terminal-africa'),
            'placeholder' => _x('Postcode / ZIP', 'placeholder', 'terminal-africa'),
            'required' => true,
            'class' => array('form-row-wide', 'address-field'),
            'clear' => true,
            'priority' => 40,
        );
        //phone
        $fields['shipping']['shipping_phone'] = array(
            'label' => __('Phone', 'terminal-africa'),
            'placeholder' => _x('Phone', 'placeholder', 'terminal-africa'),
            'required' => true,
            'clear' => true,
            'priority' => 50,
        );
        //country
        $fields['shipping']['shipping_country'] = array(
            'type' => 'country',
            'label' => __('Country', 'terminal-africa'),
            'placeholder' => _x('Country', 'placeholder', 'terminal-africa'),
            'required' => true,
            'class' => array('form-row-wide', 'address-field', 'update_totals_on_change'),
            'clear' => true,
            'priority' => 60,
        );
        //state
        $fields['shipping']['shipping_state'] = array(
            'type' => 'state',
            'label' => __('State', 'terminal-africa'),
            'placeholder' => _x('County', 'placeholder', 'terminal-africa'),
            'required' => true,
            'class' => array('form-row-wide', 'address-field'),
            'validate' => array('state'),
            'clear' => true,
            'priority' => 70,
        );
        //city
        $fields['shipping']['shipping_city'] = array(
            'label' => __('City', 'terminal-africa'),
            'placeholder' => _x('City', 'placeholder', 'terminal-africa'),
            'required' => true,
            'class' => array('form-row-wide', 'address-field'),
            'clear' => true,
            'priority' => 80,
        );
        //return fields

        return $fields;
    }

    //update_order_review
    public function update_order_review($data)
    {
        try {
            //format url data 
            $formdata = array();
            parse_str($data, $formdata);
            //billing_postcode
            $billing_postcode = sanitize_text_field($formdata['billing_postcode']);
            $billing_state = sanitize_text_field($formdata['shipping_state']);
            $billing_city = sanitize_text_field($formdata['shipping_city']);
            //country
            $billing_country = sanitize_text_field($formdata['billing_country']);
            //check if city is not empty
            if (!empty($billing_city)) {
                $billing_city = sanitize_text_field($billing_city);
                //check if , is in billing city
                if (strpos($billing_city, ',') !== false) {
                    $billing_city = explode(',', $billing_city);
                    $billing_city = $billing_city[0];
                }
            }

            //save first name and last name
            $billing_first_name = sanitize_text_field($formdata['billing_first_name']);
            $billing_last_name = sanitize_text_field($formdata['billing_last_name']);
            $billing_email = sanitize_text_field($formdata['billing_email']);
            $billing_phone = sanitize_text_field($formdata['billing_phone']);
            //company name
            $billing_company = sanitize_text_field($formdata['billing_company']);

            //Street address
            $billing_address_1 = sanitize_text_field($formdata['billing_address_1']);

            //check if session is started
            if (session_status() == PHP_SESSION_NONE) {
                session_start();
            }

            //check if logged in
            if (is_user_logged_in()) {
                //update user meta
                $user_id = get_current_user_id();
                if (!empty($billing_state)) {
                    update_user_meta($user_id, 'billing_state', $billing_state);
                }
                //check if city is not empty
                if (!empty($billing_city)) {
                    update_user_meta($user_id, 'billing_city', $billing_city);
                    update_user_meta($user_id, 'shipping_city', $billing_city);
                }
                if (!empty($billing_state)) {
                    //shipping_state
                    update_user_meta($user_id, 'shipping_state', $billing_state);
                }

                //country
                if (!empty($billing_country)) {
                    update_user_meta($user_id, 'billing_country', $billing_country);
                }

                //phone
                if (!empty($billing_phone)) {
                    update_user_meta($user_id, 'billing_phone', $billing_phone);
                }
                //billing_email
                if (!empty($billing_email)) {
                    update_user_meta($user_id, 'billing_email', $billing_email);
                }
                //billing_first_name
                if (!empty($billing_first_name)) {
                    update_user_meta($user_id, 'billing_first_name', $billing_first_name);
                }
                //billing_last_name
                if (!empty($billing_last_name)) {
                    update_user_meta($user_id, 'billing_last_name', $billing_last_name);
                }
                //billing_address_1
                if (!empty($billing_address_1)) {
                    update_user_meta($user_id, 'billing_address_1', $billing_address_1);
                }
                //billing_company
                if (!empty($billing_company)) {
                    update_user_meta($user_id, 'billing_company', $billing_company);
                }
            }
            //update session
            if (!empty($billing_postcode)) {
                //get billing_postcode
                if (WC()->session->get('billing_postcode')) {
                    WC()->session->__unset('billing_postcode');
                }
                WC()->session->set('billing_postcode', $billing_postcode);

                //get shipping_postcode
                if (WC()->session->get('shipping_postcode')) {
                    WC()->session->__unset('shipping_postcode');
                }
                WC()->session->set('shipping_postcode', $billing_postcode);

                //check if logged in
                if (is_user_logged_in()) {
                    //update user meta
                    $user_id = get_current_user_id();
                    //shipping postcode
                    update_user_meta($user_id, 'shipping_postcode', $billing_postcode);
                    update_user_meta($user_id, 'billing_postcode', $billing_postcode);
                }
            }
            if (!empty($billing_state)) {
                //get billing_state
                if (WC()->session->get('billing_state')) {
                    WC()->session->__unset('billing_state');
                }
                WC()->session->set('billing_state', $billing_state);
            }
            //$billing_country
            if (!empty($billing_country)) {
                //get billing_country
                if (WC()->session->get('billing_country')) {
                    WC()->session->__unset('billing_country');
                }
                WC()->session->set('billing_country', $billing_country);
            }
            //check if city is not empty
            if (!empty($billing_city)) {
                //get billing_city
                if (WC()->session->get('billing_city')) {
                    WC()->session->__unset('billing_city');
                }
                WC()->session->set('billing_city', $billing_city);

                //get shipping_city
                if (WC()->session->get('shipping_city')) {
                    WC()->session->__unset('shipping_city');
                }
                WC()->session->set(
                    'shipping_city',
                    $billing_city
                );
            }
            //get shipping_state
            if (WC()->session->get('shipping_state')) {
                WC()->session->__unset('shipping_state');
            }
            WC()->session->set('shipping_state', $billing_state);
            return $data;
        } catch (\Exception $e) {
            error_log($e->getMessage() . ' ' . $e->getLine() . ' ' . $e->getFile());
            return $data;
        }
    }

    /**
     * Load Shipping method.
     *
     * Load the WooCommerce shipping method class.
     *
     * @since 1.0.0
     */
    public function load_shipping_method()
    {
        new WC_Terminal_Delivery_Shipping_Method;
    }

    /**
     * Add shipping method.
     *
     * Add shipping method to the list of available shipping method..
     *
     * @since 1.0.0
     */
    public function add_shipping_method($methods)
    {
        if (class_exists('WC_Terminal_Delivery_Shipping_Method')) :
            $methods['terminal_delivery'] = 'WC_Terminal_Delivery_Shipping_Method';
        endif;

        return $methods;
    }

    public function get_plugin_path()
    {
        return plugin_dir_path(TERMINAL_AFRICA_PLUGIN_FILE);
    }

    /**
     * Returns the main Terminal Delivery Instance.
     *
     * Ensures only one instance is/can be loaded.
     *
     * @since 1.0.0
     *
     * @return \WC_Terminal_Delivery
     */
    public static function instance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }
}


/**
 * Returns the One True Instance of WooCommerce TerminalDelivery.
 *
 * @since 1.0.0
 *
 * @return \WC_Terminal_Delivery
 */
function wc_Terminal_delivery()
{
    return \WC_Terminal_Delivery::instance();
}
