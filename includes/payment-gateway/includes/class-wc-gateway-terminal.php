<?php
//avoid direct access

use SmartyStreets\PhpSdk\Request;
use WpOrg\Requests\Requests;

if (!defined('ABSPATH')) exit("No direct script access allowed"); // Exit if accessed directly

if (class_exists("WC_Payment_Gateway")) {
    class WC_Terminal_Payment_Gateway extends WC_Payment_Gateway_CC
    {
        /**
         * testmode
         * 
         */
        public $testmode;

        /**
         * apiURL
         * 
         */
        public $apiURL;

        /**
         * Constructor
         */
        public function __construct()
        {
            $this->id = "terminal_africa_payment";
            // $this->icon = apply_filters("woocommerce_terminal_africa_payment_icon", plugins_url( 'assets/images/terminal_africa_payment.png', WC_TERMINAL_PAYMENT_MAIN_FILE ));
            $this->has_fields = true;
            $this->method_title = __("Terminal Payment", "wc-terminal_africa_payment-payment-gateway");
            $this->method_description = sprintf(__('Terminal provide merchants with the tools and services needed to accept online payments from local and international customers using Mastercard, Visa, Verve Cards <a href="%1$s" target="_blank">Learn more</a>.', 'wc-terminal_africa_payment-payment-gateway'), 'https://www.terminal.africa/integrations?utm_source=web');
            $this->supports = array(
                'products',
                'tokenization',
                'subscriptions',
                'subscription_cancellation',
                'subscription_suspension',
                'subscription_reactivation',
                'subscription_amount_changes',
                'subscription_date_changes',
                'subscription_payment_method_change',
                'subscription_payment_method_change_customer',
                'subscription_payment_method_change_admin',
                'multiple_subscriptions',
            );
            // Load the form fields
            $this->init_form_fields();
            // Load the settings
            $this->init_settings();
            //Load 
            $this->title       = $this->get_option('title');
            $this->description = $this->get_option('description');
            $this->enabled     = $this->get_option('enabled');

            //TerminalAfricaShippingPlugin
            $terminal_africa_shipping_plugin = TerminalAfricaShippingPlugin::instance();
            //get endpoint
            $endpoint = $terminal_africa_shipping_plugin::$endpoint;

            $this->testmode = $terminal_africa_shipping_plugin::$plugin_mode === 'test' ? true : false;

            //apiURL
            $this->apiURL = $terminal_africa_shipping_plugin::$payment_endpoint;

            // Hooks
            add_action('wp_enqueue_scripts', array($this, 'payment_scripts'));
            add_action('woocommerce_available_payment_gateways', array($this, 'add_gateway_to_checkout'));
            add_action('admin_enqueue_scripts', array($this, 'admin_scripts'));
            add_action('woocommerce_receipt_' . $this->id, array($this, 'receipt_page'));
            //register webhook api init
            add_action('rest_api_init', array($this, 'register_api_init'));
            add_action(
                'woocommerce_update_options_payment_gateways_' . $this->id,
                array(
                    $this,
                    'process_admin_options',
                )
            );

            //ajax terminal_africa_payment_status
            add_action('wp_ajax_terminal_africa_payment_status', array($this, 'terminal_africa_payment_status'));

            //woocommerce_thankyou
            add_action('woocommerce_thankyou_' . $this->id, array($this, 'terminal_africa_payment_thankyou'));

            //add ajax terminal_africa_payment_init
            add_action('wp_ajax_terminal_africa_payment_init', array($this, 'terminal_africa_payment_init'));
            // no priv
            add_action('wp_ajax_nopriv_terminal_africa_payment_init', array($this, 'terminal_africa_payment_init'));

            //add terminal gateway notice
            add_action('admin_notices', array($this, 'terminal_africa_payment_notice'));

            // Check if the gateway can be used.
            if (!$this->is_valid_for_use()) {
                $this->enabled = false;
            }
        }

        public function init_form_fields()
        {
            $form_fields = apply_filters(
                "woo_terminal_africa_payment_fields",
                array(
                    "enabled" => array(
                        "title" => __("Enable/Disable", "wc-terminal_africa_payment-payment-gateway"),
                        "type" => "checkbox",
                        "label" => __("Enable or Disable Terminal Africa Payment", "wc-terminal_africa_payment-payment-gateway"),
                        "default" => "no"
                    ),
                    "title" => array(
                        "title" => __("Title", "wc-terminal_africa_payment-payment-gateway"),
                        "type" => "text",
                        "description" => __("This controls the payment method title which the user sees during checkout.", "wc-terminal_africa_payment-payment-gateway"),
                        "default" => __("Terminal Africa Payment", "wc-terminal_africa_payment-payment-gateway"),
                        "desc_tip" => true,
                        //read only
                        "custom_attributes" => array(
                            "readonly" => "readonly"
                        )
                    ),
                    "description" => array(
                        "title" => __("Payment Description", "wc-terminal_africa_payment-payment-gateway"),
                        "type" => "textarea",
                        "description" => __("Add a new description", "wc-terminal_africa_payment-payment-gateway"),
                        "default" => __("Accept payments seamlessly via card, account transfers, etc. using Terminal payment gateway.", "wc-terminal_africa_payment-payment-gateway"),
                        "desc_tip" => true,
                        //read only
                        "custom_attributes" => array(
                            "readonly" => "readonly"
                        )
                    )
                )
            );

            $this->form_fields = $form_fields;
        }

        /**
         * terminal_africa_payment_notice
         * 
         * @return void
         */
        public function terminal_africa_payment_notice()
        {
            //check if user is admin
            if (!current_user_can('manage_options')) {
                return;
            }

            //get terminal_africa_notice_closed
            $terminal_africa_notice_closed = get_option('terminal_africa_notice_closed');
            //check if terminal_africa_notice_closed is empty
            if (!empty($terminal_africa_notice_closed)) {
                //convert to date
                $terminal_africa_notice_closed = date('Y-m-d', strtotime($terminal_africa_notice_closed));
                //check if terminal_africa_notice_closed is less than today
                if ($terminal_africa_notice_closed > date('Y-m-d')) {
                    return;
                }
            }

            //learn more url
            $learn_more_url = admin_url('admin.php?page=terminal-africa-get-started');

            ob_start();
?>
            <style>
                .terminal-custom-notice-wp {
                    padding: 0 !important;
                }

                .terminal-custom-notice-wp.notice-info {
                    border-left-color: #f69220 !important;
                }

                .terminal-notice {
                    display: flex !important;
                }

                .terminal-notice-logo {
                    background-color: #ff9c2c3d !important;
                    padding: 15px 10px !important;
                }

                .terminal-notice-logo img {
                    width: 25px !important;
                    height: 25px !important;
                    object-fit: contain !important;
                    object-position: center !important;
                }

                .terminal-notice-content {
                    margin-left: 20px !important;
                }
            </style>
            <div class="terminal-custom-notice-wp notice notice-info is-dismissible">
                <div class="terminal-notice">
                    <div class="terminal-notice-logo">
                        <img src="<?php echo TERMINAL_AFRICA_PLUGIN_ASSETS_URL . '/img/logo-footer.png'; ?>" alt="Terminal Africa Logo">
                    </div>
                    <div class="terminal-notice-content">
                        <h3 style="margin: 10px 0;">You Can Now Accept Payments with Terminal Africa!</h3>
                        <p>
                            Terminal Africa is the easiest way to accept payments on your WooCommerce store. With our Terminal Africa Payment Gateway, you can accept payments from Mastercard, Visa, Verve Cards and more.
                        </p>
                        <p>
                            <a href="<?php echo esc_url($learn_more_url); ?>">Learn More</a>
                        </p>
                        <button type="button" class="notice-dismiss terminal-notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button>
                    </div>
                </div>
            </div>
<?php
            echo ob_get_clean();
        }

        /**
         * terminal_africa_payment_thankyou
         * 
         * @param int $order_id
         * @return void
         */
        public function terminal_africa_payment_thankyou($order_id)
        {
            try {
                //get the payment_id from url
                $payment_id = isset($_GET['payment_id']) ? sanitize_text_field($_GET['payment_id']) : null;

                //check if payment_id is empty
                if (empty($payment_id)) {
                    //return 
                    return;
                }

                //get order
                $order = wc_get_order($order_id);

                //update order meta
                $order->update_meta_data('terminal_africa_payment_id', $payment_id);
                //save order
                $order->save();

                //get template for status
                wc_get_template(
                    'thank-you-page.php',
                    array(
                        'order' => $order
                    ),
                    'terminal-africa/',
                    WC_TERMINAL_PAYMENT_TEMPLATE
                );
            } catch (\Exception $e) {
                //log error
                error_log($e->getMessage());
                //log
                logTerminalError($e, "terminal_africa_payment_thankyou");
            }
        }

        /**
         * terminal_africa_payment_status
         * 
         * @return void
         */
        public function terminal_africa_payment_status()
        {
            try {
                //very nonce
                if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'terminal_africa_nonce')) {
                    wp_send_json_error([
                        'status' => 'pending',
                        'message' => 'Unauthorized request detected'
                    ]);
                }

                //get order id
                $order_id = isset($_POST['order_id']) ? sanitize_text_field($_POST['order_id']) : null;

                //get the order
                $order = wc_get_order($order_id);

                //check if order is valid
                if (!$order) {
                    throw new \Exception('Invalid order, please try again');
                }

                //get instance
                $terminal_africa_shipping_plugin = TerminalAfricaShippingPlugin::instance();

                //get terminal_africa_settings
                $skkey = $terminal_africa_shipping_plugin::$skkey;
                //user id
                $user_id = $terminal_africa_shipping_plugin::$user_id;

                //get payment id
                $payment_id = $order->get_meta('terminal_africa_payment_id');

                //get payment status from terminal africa
                $request = Requests::get(
                    $this->apiURL . $payment_id,
                    array(
                        'Content-Type' => 'application/json',
                        'Authorization' => 'Bearer ' . $skkey
                    ) + $terminal_africa_shipping_plugin::$request_header,
                    array(
                        'timeout' => 60
                    )
                );

                //check if response is successful
                if (!$request->success) {
                    throw new \Exception('Something went wrong: ' . $request->body);
                }

                //get response
                $response = json_decode($request->body);

                //get status
                $status = $response->data->status;

                //return status
                wp_send_json_success([
                    'status' => ucfirst($status)
                ]);
            } catch (\Exception $e) {
                //log error
                error_log($e->getMessage());
                //log
                logTerminalError($e, "terminal_africa_payment_status");
                //return error
                wp_send_json_error([
                    'status' => 'pending',
                    'message' => $e->getMessage()
                ]);
            }
        }

        /**
         * register_api_init
         * 
         */
        public function register_api_init()
        {
            //route
            register_rest_route(
                'terminal_africa_payment/v1',
                '/terminal_africa_payment_verify_payment',
                array(
                    'methods' => WP_REST_Server::ALLMETHODS,
                    'callback' => array($this, 'terminal_africa_payment_verify_payment'),
                )
            );
        }

        /**
         * terminal_africa_payment_init
         * 
         */
        public function terminal_africa_payment_init()
        {
            try {
                //verify nonce
                if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'wc_terminal_africa_payment_nonce')) {
                    throw new \Exception('Unauthorized request detected');
                }

                //get order id
                $order_id = isset($_POST['order_id']) ? sanitize_text_field($_POST['order_id']) : null;

                //get the order
                $order = wc_get_order($order_id);

                //check if order is valid
                if (!$order) {
                    throw new \Exception('Invalid order, please try again');
                }

                //check if order is already paid 
                if ($order->is_paid()) {
                    throw new \Exception('Order has already been paid for');
                }

                //get settings
                $terminal_africa_settings = get_option('terminal_africa_settings');

                //checkout success url
                $success_url = $this->get_return_url($order);

                //checkout cancel url
                $cancel_url = $order->get_cancel_order_url();

                //cart url
                $cart_url = wc_get_cart_url();

                //checkout webhook url terminal_africa_payment_verify_payment
                $webhook_url = rest_url('terminal_africa_payment/v1/terminal_africa_payment_verify_payment');

                //$order_items
                $order_items = $order->get_items();

                $data_items = [];

                //loop through cart items
                foreach ($order_items as $product_id => $item) {
                    //$product_id
                    $product_id = $item->get_product_id();

                    //get product hs code
                    $terminal_hscode = get_post_meta($product_id, 'terminal_hscode', true);

                    //get product image
                    $product_image = get_the_post_thumbnail_url($product_id);

                    //pass the data
                    $data_items[] = [
                        "product_id" => $product_id,
                        'plugin_product_id' => $product_id,
                        "name" => $item->get_name(),
                        "quantity" => intval($item->get_quantity()) ?: 1,
                        "value" => $item->get_total(),
                        "description" => "{$item->get_quantity()} of {$item->get_name()} at {$item->get_total()} each for a total of {$item->get_total()}",
                        "type" => "parcel",
                        "currency" => get_woocommerce_currency(),
                        "weight" => (float)get_post_meta($product_id, '_weight', true) ?: 0.1,
                        'image' => $product_image ? $product_image : TERMINAL_AFRICA_PLUGIN_ASSETS_URL . '/img/logo-footer.png',
                        'hs_code' => $terminal_hscode
                    ];
                }

                //check if session is started
                if (session_status() == PHP_SESSION_NONE) {
                    session_start();
                }

                //get selected carrier logo
                $carrier_logo = isset($_SESSION['terminal_africa_carrierlogo']) ? $_SESSION['terminal_africa_carrierlogo'] : null;
                //get terminal_africa_carriername
                $carrier_name = isset($_SESSION['terminal_africa_carriername']) ? $_SESSION['terminal_africa_carriername'] : null;

                //get site title
                $site_title = get_bloginfo('name');

                //site logo url
                $site_logo = get_site_icon_url();

                //default url
                $default_logo_url = WC_HTTPS::force_https_url(TERMINAL_AFRICA_PLUGIN_ASSETS_URL . '/img/logo-footer.png');

                //site url
                $site_url = site_url();
                $domain = parse_url($site_url, PHP_URL_HOST);

                //get order country code
                $order_country_code = $order->get_billing_country();

                //add country code to phone number
                $phoneNumber = terminalFormatPhoneNumber(
                    $order->get_billing_phone(),
                    $order_country_code
                );

                //get post meta Terminal_africa_shipment_id
                $shipment_id = $order->get_meta('Terminal_africa_shipment_id');

                //get terminal user details
                $payment_payload = array(
                    "amount" => $order->get_total(),
                    "currency" => $order->get_currency(),
                    "customer" => array(
                        "first_name" => $order->get_billing_first_name(),
                        "last_name" => $order->get_billing_last_name(),
                        "email" => $order->get_billing_email(),
                        "phone" => $phoneNumber,
                    ),
                    "line_items" => $data_items,
                    "metadata" => array(
                        "domain" => $domain,
                        "order_id" => $order_id,
                        "user_id" => $terminal_africa_settings['user_id'],
                        "company" => array(
                            "name" => $site_title,
                            "logo" => $site_logo ? $site_logo : $default_logo_url,
                            "support_email" => $terminal_africa_settings['others']->user->email
                        )
                    ),
                    "shipping" => array(
                        "amount" => $order->get_shipping_total(),
                        "carrier_logo" => $carrier_logo,
                        "carrier_name" => $carrier_name
                    ),
                    "return_url" => $cart_url,
                    "success_url" => $success_url,
                    "complete_url" => site_url(),
                    "webhook_url" => $webhook_url,
                    "shipment_id" => $shipment_id
                );

                //create json 
                $request_data = json_encode($payment_payload, JSON_UNESCAPED_SLASHES);

                //get instance of TerminalAfricaShippingPlugin
                $terminal_africa_shipping_plugin = TerminalAfricaShippingPlugin::instance();

                $headers = array(
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . $terminal_africa_shipping_plugin::$skkey,
                    'X-Terminal-User' => $terminal_africa_settings['user_id']
                ) + $terminal_africa_shipping_plugin::$request_header;

                //request
                $request = Requests::post($this->apiURL, $headers, $request_data, array('timeout' => 60));

                //check if request was successful
                if (!$request->success) {
                    throw new \Exception('Something went wrong: ' . $request->body);
                }

                //parse body
                $body = json_decode($request->body);

                //check if status is success
                if ($body->status) {
                    //redirect to payment url
                    wp_send_json_success([
                        'message' => 'Payment initiated successfully',
                        'redirect_url' => $body->data->payment_url
                    ]);
                } else {
                    //check if data has error_code = duplicate_shipment
                    if (isset($body->data->error_code) && $body->data->error_code == 'duplicate_shipment') {
                        //get the payment_url
                        $payment_url = $body->data->payment_url;

                        //redirect to payment url
                        wp_send_json_success([
                            'message' => 'Payment initiated successfully',
                            'redirect_url' => $payment_url
                        ]);
                    }
                    //throw error
                    throw new \Exception('Payment initiation failed: ' . $body->message);
                }
            } catch (\Exception $e) {
                logTerminalError($e, 'terminal_africa_payment_init');
                //wp json error
                wp_send_json_error([
                    'message' => $e->getMessage()
                ]);
            }
        }

        /**
         * Header Hash Generator
         * 
         * @param $request_data
         * @param $secret_key
         * @return string
         */
        public function generate_header_hash($request_data, $secret_key)
        {
            return hash_hmac('sha512', $request_data, $secret_key);
        }

        /**
         * order_received_page
         * 
         */
        public function order_received_page($order_id)
        {
            echo '<div id="terminal_africa_payment_order_received">' . __('Thank you for your order, please click the button below to pay with Terminal Africa Payment Gateway.', 'wc-terminal_africa_payment-payment-gateway') . '</div>';
        }

        /**
         * Get Paystack payment icon URL.
         */
        public function get_logo_url()
        {
            $url = WC_HTTPS::force_https_url(TERMINAL_AFRICA_PLUGIN_ASSETS_URL . '/assets/img/logo-footer.png');
            return apply_filters('woocommerce_terminal_africa_payment_icon', $url, $this->id);
        }

        /**
         * Payment form on checkout page
         */
        public function payment_fields()
        {

            if ($this->description) {
                echo wpautop(wptexturize($this->description));
            }

            if (!is_ssl()) {
                return;
            }

            if ($this->supports('tokenization') && is_checkout() && is_user_logged_in()) {
                $this->tokenization_script();
                $this->saved_payment_methods();
                $this->save_payment_method_checkbox();
            }
        }


        /**
         * Display terminal_africa_payment payment icon.
         */
        public function get_icon()
        {
            $url = WC_HTTPS::force_https_url(TERMINAL_AFRICA_PLUGIN_ASSETS_URL . '/img/logo-footer.png');
            $icon = '<img src="' . $url . '" alt="Terminal Payment Options" style="height: 20px;" />';

            return apply_filters('woocommerce_gateway_icon', $icon, $this->id);
        }

        /**
         * Displays the payment page.
         *
         * @param $order_id
         */
        public function receipt_page($order_id)
        {
            $order = wc_get_order($order_id);

            echo '<div id="yes-add">' . __('Thank you for your order, please click the button below to pay with Terminal Africa Payment Gateway.', 'wc-terminal_africa_payment-payment-gateway') . '</div>';

            echo '<div id="terminal_africa_payment_form"><form id="order_review" method="post" class="terminal_africa_payment_form_class"></form><button class="button alt" id="wc-terminal_africa_payment-payment-gateway-button">' . __('Pay Now', 'wc-terminal_africa_payment-payment-gateway') . '</button>';
        }

        /**
         * Verify Terminal payment.
         */
        public function terminal_africa_payment_verify_payment(WP_REST_Request $request)
        {
            //MARK: WEBHOOK 
            try {
                //get header
                $headerSignature = $request->get_header("X-Terminal-Signature");

                //get all params
                $params = $request->get_params();

                //TerminalAfricaShippingPlugin
                $terminal_africa_shipping_plugin = TerminalAfricaShippingPlugin::instance();

                //generate hash from request
                $hashKey = $this->generate_header_hash(
                    json_encode($params, JSON_UNESCAPED_SLASHES),
                    $terminal_africa_shipping_plugin::$skkey
                );

                //verify hashkey
                if ($hashKey !== $headerSignature) {
                    throw new \Exception('Invalid hash key, please try again');
                }

                //confirm event 
                if (!isset($params['event'])) {
                    throw new \Exception('Invalid event data, please try again');
                }

                //get the order
                $order = wc_get_order($params['data']['metadata']['order_id']);

                //check if order is valid
                if (!$order) {
                    throw new \Exception('Invalid order, please try again');
                }

                //check status
                switch ($params['event']) {
                    case 'charge.success':
                        //set wc note
                        $order->add_order_note('Payment successful with Terminal Africa Payment Gateway - ' . $params['data']['platform']);
                        //add platform_reference to order note
                        $order->add_order_note('Platform Reference: ' . $params['data']['platform_reference']);
                        //add payment_id to order note
                        $order->add_order_note('Payment ID: ' . $params['data']['payment_id']);

                        //save to order meta
                        $order->update_meta_data('terminal_africa_payment_id', $params['data']['payment_id']);

                        //save to order meta platform
                        $order->update_meta_data('terminal_africa_payment_platform', $params['data']['platform']);

                        //save to order meta platform_reference
                        $order->update_meta_data('terminal_africa_payment_platform_reference', $params['data']['platform_reference']);

                        //save order
                        $order->save();

                        //update order status
                        $order->update_status('completed');

                        break;

                    case 'charge.failed':
                        //set wc note
                        $order->add_order_note('Payment failed with Terminal Africa Payment Gateway - ' . $params['data']['platform']);

                        //save to order meta
                        $order->update_meta_data('terminal_africa_payment_id', $params['data']['payment_id']);

                        //save order
                        $order->save();

                        //update order status
                        $order->update_status('failed');

                        break;

                    case 'charge.refunded':
                        //set wc note
                        $order->add_order_note('Payment refunded with Terminal Africa Payment Gateway - ' . $params['data']['platform']);

                        //save to order meta
                        $order->update_meta_data('terminal_africa_payment_id', $params['data']['payment_id']);

                        //save order
                        $order->save();

                        //update order status
                        $order->update_status('refunded');

                        break;

                    case 'charge.cancelled':
                        //set wc note
                        $order->add_order_note('Payment cancelled with Terminal Africa Payment Gateway - ' . $params['data']['platform']);

                        //save to order meta
                        $order->update_meta_data('terminal_africa_payment_id', $params['data']['payment_id']);

                        //save order
                        $order->save();

                        //update order status
                        $order->update_status('cancelled');

                        break;
                }

                //return success
                return wp_send_json_success([
                    'status' => 'success',
                    'message' => 'Payment successful'
                ]);
            } catch (\Exception $e) {
                logTerminalError($e, 'terminal_africa_payment_verify_payment');
                //wp json error
                wp_send_json_error([
                    'status' => 'error',
                    'message' => "Error: " . $e->getMessage()
                ]);
            }
        }

        /**
         * Process the payment.
         *
         * @param int $order_id
         *
         * @return array|void
         */
        public function process_payment($order_id)
        {
            $order = wc_get_order($order_id);

            return array(
                'result' => 'success',
                'redirect' => $order->get_checkout_payment_url(true),
            );
        }

        /**
         * Check if this gateway is enabled and available in the user's country.
         */
        public function is_valid_for_use()
        {

            return true;
        }

        /**
         * Load admin scripts.
         */
        public function admin_scripts()
        {

            if ('woocommerce_page_wc-settings' !== get_current_screen()->id) {
                return;
            }

            $terminal_africa_payment_admin_params = array(
                'plugin_url' => WC_TERMINAL_PAYMENT_URL,
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('wc_terminal_africa_payment_admin_nonce'),
            );

            wp_enqueue_script('wc_terminal_africa_payment_admin', plugins_url('assets/js/terminal_africa_payment-admin.js', WC_TERMINAL_PAYMENT_MAIN_FILE), array(), WC_TERMINAL_PAYMENT_VERSION, true);

            wp_localize_script('wc_terminal_africa_payment_admin', 'wc_terminal_africa_payment_admin_params', $terminal_africa_payment_admin_params);
        }

        /**
         * Check if terminal_africa_payment merchant details is filled.
         */
        public function admin_notices()
        {

            if ($this->enabled == 'no') {
                return;
            }

            return true;
        }

        /**
         * Check if Terminal gateway is enabled.
         *
         * @return bool
         */
        public function is_available()
        {

            if ('yes' == $this->enabled) {

                return true;
            }

            return false;
        }

        /**
         * Outputs scripts used for terminal_africa_payment payment.
         * 
         * @return void
         */
        public function payment_scripts()
        {
            if (!is_checkout_pay_page()) {
                return;
            }

            if ($this->enabled === 'no') {
                return;
            }

            $order_key = sanitize_text_field(urldecode($_GET['key']));
            $order_id  = absint(get_query_var('order-pay'));

            $order = wc_get_order($order_id);
            $api_verify_url = WC()->api_request_url('WC_Terminal_Payment_Gateway') . '?terminal_africa_payment_id=' . $order_id;

            $payment_method = method_exists($order, 'get_payment_method') ? $order->get_payment_method() : $order->payment_method;

            if ($this->id !== $payment_method) {
                return;
            }

            wp_enqueue_script('wc_terminal_africa_payment', TERMINAL_AFRICA_PLUGIN_ASSETS_URL . '/js/terminal_africa_payment.js', array('jquery'), WC_TERMINAL_PAYMENT_VERSION, true);

            wp_localize_script('wc_terminal_africa_payment', 'wc_terminal_africa_payment_params', array(
                'order_id' => $order_id,
                'order_key' => $order_key,
                'api_verify_url' => $api_verify_url,
                'ajax_url' => WC()->ajax_url(),
                'nonce' => wp_create_nonce('wc_terminal_africa_payment_nonce'),
                'redirect_url' => $this->get_return_url($order),
            ));
        }

        /**
         * Add Gateway to checkout page.
         *
         * @param $available_gateways
         *
         * @return mixed
         */
        public function add_gateway_to_checkout($available_gateways)
        {

            if ('no' == $this->enabled) {
                unset($available_gateways[$this->id]);
            }

            return $available_gateways;
        }
    }
}
