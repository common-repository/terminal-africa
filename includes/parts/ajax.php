<?php

namespace TerminalAfrica\Includes\Parts;

//security
defined('ABSPATH') or die('No script kiddies please!');

use App\Terminal\Core\TerminalSession;
use Exception;
use WpOrg\Requests\Requests;
use TerminalLogHandler;

/**
 * Ajax Operations
 * 
 * @since 1.0.0
 * @author Terminal Africa <https://terminal.africa>
 * @package Terminal Africa
 * @subpackage Ajax
 */
trait Ajax
{
    /**
     * Init Ajax
     * 
     * @since 1.0.0
     * @return void
     */
    public function init_ajax()
    {
        //ajax terminal_africa_auth
        add_action('wp_ajax_terminal_africa_auth', array($this, 'terminal_africa_auth'));
        add_action('wp_ajax_nopriv_terminal_africa_auth', array($this, 'terminal_africa_auth'));
        //ajax terminal_merchant_save_address
        add_action('wp_ajax_terminal_merchant_save_address', array($this, 'terminal_merchant_save_address'));
        add_action('wp_ajax_nopriv_terminal_merchant_save_address', array($this, 'terminal_merchant_save_address'));
        //get states
        add_action('wp_ajax_terminal_africa_get_states', array($this, 'terminal_africa_get_states'));
        add_action('wp_ajax_nopriv_terminal_africa_get_states', array($this, 'terminal_africa_get_states'));
        //ajax terminal_africa_get_cities
        add_action('wp_ajax_terminal_africa_get_cities', array($this, 'terminal_africa_get_cities'));
        add_action('wp_ajax_nopriv_terminal_africa_get_cities', array($this, 'terminal_africa_get_cities'));
        //ajax terminal_africa_sign_out
        add_action('wp_ajax_terminal_africa_sign_out', array(self::class, 'terminal_africa_sign_out'));
        add_action('wp_ajax_nopriv_terminal_africa_sign_out', array(self::class, 'terminal_africa_sign_out'));
        //ajax terminal_africa_enable_terminal
        add_action('wp_ajax_terminal_africa_enable_terminal', array(self::class, 'terminal_africa_enable_terminal'));
        add_action('wp_ajax_nopriv_terminal_africa_enable_terminal', array(self::class, 'terminal_africa_enable_terminal'));
        //ajax terminal_africa_save_cart_item
        add_action('wp_ajax_terminal_africa_save_cart_item', array($this, 'terminal_africa_save_cart_item'));
        add_action('wp_ajax_nopriv_terminal_africa_save_cart_item', array($this, 'terminal_africa_save_cart_item'));
        //ajax terminal_africa_process_terminal_rates
        add_action('wp_ajax_terminal_africa_process_terminal_rates', array($this, 'terminal_africa_process_terminal_rates'));
        add_action('wp_ajax_nopriv_terminal_africa_process_terminal_rates', array($this, 'terminal_africa_process_terminal_rates'));
        //ajax terminal_africa_save_shipping_carrier
        add_action('wp_ajax_terminal_africa_save_shipping_carrier', array($this, 'terminal_africa_save_shipping_carrier'));
        add_action('wp_ajax_nopriv_terminal_africa_save_shipping_carrier', array($this, 'terminal_africa_save_shipping_carrier'));
        //ajax get rate data
        add_action('wp_ajax_terminal_africa_get_rate_data', array($this, 'terminal_africa_get_rate_data'));
        add_action('wp_ajax_nopriv_terminal_africa_get_rate_data', array($this, 'terminal_africa_get_rate_data'));
        //ajax terminal_customer_save_address
        add_action('wp_ajax_terminal_customer_save_address', array($this, 'terminal_customer_save_address'));
        add_action('wp_ajax_nopriv_terminal_customer_save_address', array($this, 'terminal_customer_save_address'));
        //ajax terminal_africa_process_terminal_rates_customer
        add_action('wp_ajax_terminal_africa_process_terminal_rates_customer', array($this, 'terminal_africa_process_terminal_rates_customer'));
        add_action('wp_ajax_nopriv_terminal_africa_process_terminal_rates_customer', array($this, 'terminal_africa_process_terminal_rates_customer'));
        //ajax terminal_africa_apply_terminal_rates_customer
        add_action('wp_ajax_terminal_africa_apply_terminal_rates_customer', array($this, 'terminal_africa_apply_terminal_rates_customer'));
        add_action('wp_ajax_nopriv_terminal_africa_apply_terminal_rates_customer', array($this, 'terminal_africa_apply_terminal_rates_customer'));
        //ajax terminal_africa_arrange_terminal_delivery
        add_action('wp_ajax_terminal_africa_arrange_terminal_delivery', array($this, 'terminal_africa_arrange_terminal_delivery'));
        add_action('wp_ajax_nopriv_terminal_africa_arrange_terminal_delivery', array($this, 'terminal_africa_arrange_terminal_delivery'));
        //refresh_terminal_wallet
        add_action('wp_ajax_refresh_terminal_wallet', array($this, 'refresh_terminal_wallet'));
        add_action('wp_ajax_nopriv_refresh_terminal_wallet', array($this, 'refresh_terminal_wallet'));
        //refresh_terminal_rate_data
        add_action('wp_ajax_refresh_terminal_rate_data', array($this, 'refresh_terminal_rate_data'));
        add_action('wp_ajax_nopriv_refresh_terminal_rate_data', array($this, 'refresh_terminal_rate_data'));
        //ajax save_terminal_carrier_settings
        add_action('wp_ajax_save_terminal_carrier_settings', array($this, 'save_terminal_carrier_settings'));
        add_action('wp_ajax_nopriv_save_terminal_carrier_settings', array($this, 'save_terminal_carrier_settings'));
        //ajax refresh_terminal_carriers_data
        add_action('wp_ajax_refresh_terminal_carriers_data', array($this, 'refresh_terminal_carriers_data'));
        add_action('wp_ajax_nopriv_refresh_terminal_carriers_data', array($this, 'refresh_terminal_carriers_data'));
        //ajax get_terminal_packaging
        add_action('wp_ajax_get_terminal_packaging', array($this, 'get_terminal_packaging'));
        add_action('wp_ajax_nopriv_get_terminal_packaging', array($this, 'get_terminal_packaging'));
        //ajax get_terminal_shipment_status
        add_action('wp_ajax_get_terminal_shipment_status', array($this, 'get_terminal_shipment_status'));
        add_action('wp_ajax_nopriv_get_terminal_shipment_status', array($this, 'get_terminal_shipment_status'));
        //ajax update user carrier
        add_action('wp_ajax_update_user_carrier_terminal', array($this, 'update_user_carrier_terminal'));
        add_action('wp_ajax_nopriv_update_user_carrier_terminal', array($this, 'update_user_carrier_terminal'));
        //ajax deactivate_terminal_africa
        add_action('wp_ajax_deactivate_terminal_africa', array(self::class, 'deactivate_terminal_africa'));
        add_action('wp_ajax_nopriv_deactivate_terminal_africa', array(self::class, 'deactivate_terminal_africa'));
        //ajax cancel_terminal_shipment
        add_action('wp_ajax_cancel_terminal_shipment', array(self::class, 'cancel_terminal_shipment'));
        add_action('wp_ajax_nopriv_cancel_terminal_shipment', array(self::class, 'cancel_terminal_shipment'));
        //add ajax save_terminal_custom_price_mark_up
        add_action('wp_ajax_save_terminal_custom_price_mark_up', array($this, 'save_terminal_custom_price_mark_up'));
        add_action('wp_ajax_nopriv_save_terminal_custom_price_mark_up', array($this, 'save_terminal_custom_price_mark_up'));
        //add ajax save_terminal_default_currency_code
        add_action('wp_ajax_save_terminal_default_currency_code', array($this, 'save_terminal_default_currency_code'));
        add_action('wp_ajax_nopriv_save_terminal_default_currency_code', array($this, 'save_terminal_default_currency_code'));
        //add ajax terminal_reset_carriers_data
        add_action('wp_ajax_terminal_reset_carriers_data', array($this, 'terminal_reset_carriers_data'));
        add_action('wp_ajax_nopriv_terminal_reset_carriers_data', array($this, 'terminal_reset_carriers_data'));
        //ajax update_user_carrier_shipment_timeline_terminal
        add_action('wp_ajax_update_user_carrier_shipment_timeline_terminal', array($this, 'update_user_carrier_shipment_timeline_terminal'));
        add_action('wp_ajax_nopriv_update_user_carrier_shipment_timeline_terminal', array($this, 'update_user_carrier_shipment_timeline_terminal'));
        //ajax update_user_carrier_shipment_rate_terminal
        add_action('wp_ajax_update_user_carrier_shipment_rate_terminal', array($this, 'update_user_carrier_shipment_rate_terminal'));
        add_action('wp_ajax_nopriv_update_user_carrier_shipment_rate_terminal', array($this, 'update_user_carrier_shipment_rate_terminal'));
        //add ajax update_user_carrier_shipment_insurance_terminal
        add_action('wp_ajax_update_user_carrier_shipment_insurance_terminal', array($this, 'update_user_carrier_shipment_insurance_terminal'));
        add_action('wp_ajax_nopriv_update_user_carrier_shipment_insurance_terminal', array($this, 'update_user_carrier_shipment_insurance_terminal'));
        //ajax terminal_africa_get_address_book
        add_action('wp_ajax_terminal_africa_get_address_book', array($this, 'terminal_africa_get_address_book'));
        add_action('wp_ajax_nopriv_terminal_africa_get_address_book', array($this, 'terminal_africa_get_address_book'));
        //add ajax terminal_africa_get_transactions
        add_action('wp_ajax_terminal_africa_get_transactions', array($this, 'terminal_africa_get_transactions'));
        add_action('wp_ajax_nopriv_terminal_africa_get_transactions', array($this, 'terminal_africa_get_transactions'));
        //ajax terminal_africa_get_shipping_api_data
        add_action('wp_ajax_terminal_africa_get_shipping_api_data', array($this, 'terminal_africa_get_shipping_api_data'));
        //ajax terminal_africa_get_merchant_address_data
        add_action('wp_ajax_terminal_africa_get_merchant_address_data', array($this, 'terminal_africa_get_merchant_address_data'));
        //update_user_terminal_payment_gateway
        add_action('wp_ajax_update_user_terminal_payment_gateway', [$this, 'update_user_terminal_payment_gateway']);
        //ajax request_terminal_africa_payment_access
        add_action('wp_ajax_request_terminal_africa_payment_access', array($this, 'request_terminal_africa_payment_access'));
        //ajax update_user_settings
        add_action('wp_ajax_update_terminal_user_settings', array($this, 'update_terminal_user_settings'));
        //ajax update_terminal_wallet_currency
        add_action('wp_ajax_update_terminal_wallet_currency', array($this, 'update_terminal_wallet_currency'));
        //ajax terminal_africa_close_notice
        add_action('wp_ajax_terminal_africa_close_notice', array($this, 'terminal_africa_close_notice'));
        //add ajax terminal_africa_validate_terminal_shipment
        add_action('wp_ajax_terminal_africa_validate_terminal_shipment', array($this, 'terminal_africa_validate_terminal_shipment'));
    }

    /**
     * terminal_africa_validate_terminal_shipment
     * 
     */
    public function terminal_africa_validate_terminal_shipment()
    {
        try {
            //verify nonce
            if (!wp_verify_nonce($_GET['nonce'], 'terminal_africa_nonce')) {
                wp_send_json([
                    'code' => 400,
                    'message' => 'Wrong nonce, please refresh the page and try again'
                ]);
            }

            //get rateid
            $rateid = sanitize_text_field($_GET['rateid']);
            //get order_id
            $order_id = sanitize_text_field($_GET['order_id']);
            //get shipment_id
            $shipment_id = sanitize_text_field($_GET['shipment_id']);

            //validate rate 
            $validateRate = validateTerminalRate($rateid, $order_id, $shipment_id);

            //check if response code is 200
            if ($validateRate['code'] == 200) {
                //send response
                wp_send_json([
                    'code' => 200,
                    'message' => 'Terminal shipment validated successfully',
                    'data' => $validateRate['data']
                ]);
            }

            //check if response code is 402
            if ($validateRate['code'] == 402) {
                //send response
                wp_send_json([
                    'code' => 402,
                    'message' => $validateRate['message'],
                    'data' => $validateRate['data']
                ]);
            }

            //send response
            wp_send_json([
                'code' => 400,
                'message' => 'Terminal shipment validation failed: ' . $validateRate['message']
            ]);
        } catch (Exception $e) {
            logTerminalError($e, 'terminal_africa_validate_terminal_shipment');
            wp_send_json([
                'code' => 400,
                'message' => "Something went wrong: " . $e->getMessage()
            ]);
        }
    }

    /**
     * terminal_africa_close_notice
     * 
     */
    public function terminal_africa_close_notice()
    {
        try {
            //verify nonce
            if (!wp_verify_nonce($_POST['nonce'], 'terminal_africa_nonce')) {
                wp_send_json([
                    'code' => 400,
                    'message' => 'Wrong nonce, please refresh the page and try again'
                ]);
            }

            //get terminal_africa_notice_closed
            $terminal_africa_notice_closed = get_option('terminal_africa_notice_closed', date('Y-m-d'));

            //add 3 days to terminal_africa_notice_closed
            $terminal_africa_notice_closed = date('Y-m-d', strtotime($terminal_africa_notice_closed . ' + 3 days'));
            //update terminal_africa_notice_closed
            update_option('terminal_africa_notice_closed', $terminal_africa_notice_closed);

            //send response
            wp_send_json([
                'code' => 200,
                'message' => 'Notice closed successfully'
            ]);
        } catch (Exception $e) {
            logTerminalError($e, 'terminal_africa_close_notice');
            wp_send_json([
                'code' => 400,
                'message' => "Something went wrong: " . $e->getMessage()
            ]);
        }
    }

    /**
     * update_terminal_wallet_currency
     * 
     */
    public function update_terminal_wallet_currency()
    {
        try {
            //very nonce
            if (!wp_verify_nonce($_POST['nonce'], 'terminal_africa_nonce')) {
                wp_send_json([
                    'code' => 400,
                    'message' => 'Wrong nonce, please refresh the page and try again'
                ]);
            }

            //check if session has started
            if (session_status() !== PHP_SESSION_ACTIVE) {
                session_start();
            }

            $terminal_africa_merchant_id = get_option('terminal_africa_merchant_id');

            //get currency
            $currency = sanitize_text_field($_POST['currency']);

            //save session terminal_africa_wallet_currency
            $_SESSION['terminal_africa_wallet_currency'] = $currency;

            //get user wallet
            $walletData = getWalletBalance($terminal_africa_merchant_id, true, $currency);

            //check if response code is 200
            if ($walletData['code'] == 200) {
                //save session terminal_africa_wallet_id
                $_SESSION['terminal_africa_wallet_id'] = $walletData['data']->id;
                //update transactions
                getTransactions(1, [], true);
            }

            //send response
            wp_send_json([
                'code' => 200,
                'message' => 'Wallet currency updated successfully'
            ]);
        } catch (\Exception $e) {
            logTerminalError($e, 'update_terminal_wallet_currency');
            wp_send_json([
                'code' => 400,
                'message' => "Something went wrong: " . $e->getMessage()
            ]);
        }
    }

    /**
     * Request payment access
     * 
     * @return void
     */
    public function request_terminal_africa_payment_access()
    {
        try {
            //verify nonce
            if (!wp_verify_nonce($_POST['nonce'], 'terminal_africa_nonce')) {
                wp_send_json([
                    'code' => 400,
                    'message' => 'Wrong nonce, please refresh the page and try again'
                ]);
            }

            $logInstance = TerminalLogHandler::instance();
            //get user data
            $userData = $logInstance->getUserData();
            //site url
            $site_url = site_url();
            $domain = parse_url($site_url, PHP_URL_HOST);

            //request payment access
            $response = Requests::post(
                self::$endpoint . "users/payment/opt-in",
                [
                    'Authorization' => 'Bearer ' . self::$skkey
                ] + self::$request_header,
                json_encode(
                    [
                        'site_email' => get_bloginfo('admin_email'),
                        'domain' => $domain,
                        'platform' => 'wordpress'
                    ] + $userData
                )
            );

            //get body 
            $body = json_decode($response->body);

            //check if response is 200
            if ($response->status_code == 200) {
                //send response
                wp_send_json([
                    'code' => 200,
                    'message' => 'Payment access request sent successfully'
                ]);
            } else {
                wp_send_json([
                    'code' => 400,
                    'message' => 'Payment access request failed: ' . $body->message
                ]);
            }
        } catch (\Exception $e) {
            logTerminalError($e, 'users/payment/opt-in');
            wp_send_json([
                'code' => 400,
                'message' => "Something went wrong: " . $e->getMessage()
            ]);
        }
    }

    //terminal_africa_auth
    public function terminal_africa_auth()
    {
        try {
            $nonce = sanitize_text_field($_POST['nonce']);
            if (!wp_verify_nonce($nonce, 'terminal_africa_nonce')) {
                wp_send_json([
                    'code' => 400,
                    'message' => 'Wrong nonce, please refresh the page and try again'
                ]);
            }
            $public_key = sanitize_text_field($_POST['public_key']);
            $secret_key = sanitize_text_field($_POST['secret_key']);
            if (empty($public_key) || empty($secret_key)) {
                wp_send_json([
                    'code' => 400,
                    'message' => 'Please enter your public key and secret key'
                ]);
            }
            //validate keys
            $validate_keys = $this->checkKeys($public_key, $secret_key);
            //send request
            $response = Requests::get($validate_keys["endpoint"] . "users/secrete", [
                'Authorization' => 'Bearer ' . $secret_key
            ]);
            //check if response is 200
            $body = json_decode($response->body);
            if ($response->status_code == 200) {
                //save keys
                $settings = array(
                    'public_key' => $public_key,
                    'secret_key' => $secret_key,
                    'user_id' => $body->data->user->user_id,
                    'others' => $body->data
                );
                // Save settings
                update_option('terminal_africa_settings', $settings);
                //terminal_africa_merchant_id
                update_option('terminal_africa_merchant_id', $body->data->user->user_id);
                //check if metadata exist
                if (isset($body->data->user->metadata)) {
                    //$isoCode
                    $isoCode = $body->data->user->country;
                    //save metadata
                    update_option('terminal_default_currency_code', ['currency_code' => isset($body->data->user->metadata->default_currency) ? $body->data->user->metadata->default_currency : 'NGN', 'isoCode' => $isoCode]);
                }
                //get shipping settings
                $settings = get_option('woocommerce_terminal_delivery_settings');
                //update shipping settings
                $settings['enabled'] = 'yes';
                update_option('woocommerce_terminal_delivery_settings', $settings);
                //log plugin data
                TerminalLogHandler::terminalLoggerHandler('plugin/activate');
                //return 
                wp_send_json([
                    'code' => 200,
                    'message' => 'Authentication successful'
                ]);
            } else {
                wp_send_json([
                    'code' => 400,
                    'message' => $body->message
                ]);
            }
        } catch (\Exception $e) {
            logTerminalError($e, 'users/secrete');
            wp_send_json([
                'code' => 400,
                'message' => "Something went wrong. Please try again"
            ]);
        }
    }

    /**
     * Update user settings from server
     * 
     */
    public function update_terminal_user_settings()
    {
        try {
            //verify nonce
            if (!wp_verify_nonce($_POST['nonce'], 'terminal_africa_nonce')) {
                wp_send_json([
                    'code' => 400,
                    'message' => 'Wrong nonce, please refresh the page and try again'
                ]);
            }

            //send request
            $response = Requests::get(self::$enpoint . "users/secrete", [
                'Authorization' => 'Bearer ' . self::$skkey
            ] + self::$request_header);

            //check if response is 200
            $body = json_decode($response->body);
            if ($response->status_code == 200) {
                //save keys
                $settings = array(
                    'public_key' => self::$public_key,
                    'secret_key' => self::$skkey,
                    'user_id' => $body->data->user->user_id,
                    'others' => $body->data
                );
                // Save settings
                update_option('terminal_africa_settings', $settings);

                //send message
                wp_send_json(
                    [
                        'code' => 200,
                        'message' => 'Settings updated successfully'
                    ]
                );
            } else {
                //send error message
                wp_send_json([
                    'code' => 400,
                    'message' => "Something went wrong: " . $body->message
                ]);
            }
        } catch (\Exception $e) {
            logTerminalError($e, 'users/settings');
            wp_send_json([
                'code' => 400,
                'message' => "Something went wrong: " . $e->getMessage()
            ]);
        }
    }

    /**
     * update_user_terminal_payment_gateway
     * 
     */
    public function update_user_terminal_payment_gateway()
    {
        try {
            //verify nonce
            if (!wp_verify_nonce($_POST['nonce'], 'terminal_africa_nonce')) {
                wp_send_json([
                    'code' => 400,
                    'message' => 'Wrong nonce, please refresh the page and try again'
                ]);
            }

            //get status
            $status = sanitize_text_field($_POST['status']);
            //get woocommerce_terminal_africa_payment_settings
            $woocommerce_terminal_africa_payment_settings = get_option("woocommerce_terminal_africa_payment_settings");
            //check we have woocommerce_terminal_africa_payment_settings
            if ($woocommerce_terminal_africa_payment_settings) {
                //check if we have enabled
                if (isset($woocommerce_terminal_africa_payment_settings['enabled'])) {
                    //update enabled
                    $woocommerce_terminal_africa_payment_settings['enabled'] = $status == 'true' ? 'yes' : 'no';
                    //update woocommerce_terminal_africa_payment_settings
                    update_option("woocommerce_terminal_africa_payment_settings", $woocommerce_terminal_africa_payment_settings);

                    //return
                    wp_send_json([
                        'code' => 200,
                        'message' => 'Payment gateway updated successfully'
                    ]);
                }
            } else {
                //create woocommerce_terminal_africa_payment_settings
                $woocommerce_terminal_africa_payment_settings = array(
                    'enabled' => $status == 'true' ? 'yes' : 'no',
                    'title' => 'Terminal Africa Payment',
                    'description' => 'Accept payments seamlessly via card, account transfers, etc. using Terminal payment gateway.',
                    'testmode' => 'yes'
                );
                //update woocommerce_terminal_africa_payment_settings
                update_option("woocommerce_terminal_africa_payment_settings", $woocommerce_terminal_africa_payment_settings);
                //return
                wp_send_json([
                    'code' => 200,
                    'message' => 'Payment gateway updated successfully'
                ]);
            }

            //return error
            wp_send_json([
                'code' => 400,
                'message' => 'Payment gateway not found'
            ]);
        } catch (\Exception $e) {
            logTerminalError($e);
            wp_send_json([
                'code' => 400,
                'message' => "Something went wrong: " . $e->getMessage()
            ]);
        }
    }

    //checkKeys
    public function checkKeys($pk, $sk)
    {
        try {

            //check if keys has test in them
            if (strpos($pk, 'test') !== false || strpos($sk, 'test') !== false) {
                return [
                    'endpoint' => TERMINAL_AFRICA_TEST_API_ENDPOINT,
                    'payment_endpoint' => TERMINAL_AFRICA_PAYMENT_TEST_API_ENDPOINT,
                    'mode' => 'test'
                ];
            } else if (strpos($pk, 'live') !== false || strpos($sk, 'live') !== false) {
                return [
                    'endpoint' => TERMINAL_AFRICA_API_ENDPOINT,
                    'payment_endpoint' => TERMINAL_AFRICA_PAYMENT_API_ENDPOINT,
                    'mode' => 'live'
                ];
            }
            return [
                'endpoint' => TERMINAL_AFRICA_TEST_API_ENDPOINT,
                'payment_endpoint' => TERMINAL_AFRICA_PAYMENT_TEST_API_ENDPOINT,
                'mode' => 'test'
            ];
        } catch (\Exception $e) {
            logTerminalError($e);
            return [
                'endpoint' => TERMINAL_AFRICA_TEST_API_ENDPOINT,
                'payment_endpoint' => TERMINAL_AFRICA_PAYMENT_TEST_API_ENDPOINT,
                'mode' => 'test'
            ];
        }
    }

    //terminal_merchant_save_address
    public function terminal_merchant_save_address()
    {
        try {

            $nounce = sanitize_text_field($_POST['nonce']);
            if (!wp_verify_nonce($nounce, 'terminal_africa_nonce')) {
                wp_send_json([
                    'code' => 400,
                    'message' => 'Wrong nonce, please refresh the page and try again'
                ]);
            }
            $first_name = sanitize_text_field($_POST['first_name']);
            $last_name = sanitize_text_field($_POST['last_name']);
            $email = sanitize_text_field($_POST['email']);
            $phone = sanitize_text_field($_POST['phone']);
            $line_1 = sanitize_text_field($_POST['line_1']);
            $line_2 = sanitize_text_field($_POST['line_2']);
            $city = sanitize_text_field($_POST['lga']);
            $state = sanitize_text_field($_POST['state']);
            $country = sanitize_text_field($_POST['country']);
            $zip_code = sanitize_text_field($_POST['zip_code']);

            ////////////// address_id///////////
            if (isset($_POST['address_id'])) {
                $address_id = sanitize_text_field($_POST['address_id']);
                //update terminal_africa_merchant_address_id
                update_option('terminal_africa_merchant_address_id', $address_id);
            }

            if (strlen($line_1) > 45) {
                //break to line_2
                $line_2 = substr($line_1, 45);
                //remove the 45 character
                $line_1 = substr($line_1, 0, 45);
            }

            //check if any field is empty
            if (empty($first_name) || empty($last_name) || empty($email) || empty($phone) || empty($line_1) || empty($city) || empty($state) || empty($country)) {
                wp_send_json([
                    'code' => 400,
                    'message' => 'Please fill all required fields'
                ]);
            }
            //check if merchant_address_id is set
            $merchant_address_id = get_option('terminal_africa_merchant_address_id');
            if (empty($merchant_address_id)) {
                //create address
                $create_address = createTerminalAddress($first_name, $last_name, $email, $phone, $line_1, $line_2, $city, $state, $country, $zip_code);
                //check if address is created
                if ($create_address['code'] == 200) {
                    //save address id
                    update_option('terminal_africa_merchant_address_id', $create_address['data']->address_id);
                    //save address
                    update_option('terminal_africa_merchant_address', $create_address['data']);
                    //return
                    wp_send_json([
                        'code' => 200,
                        'message' => 'Address saved successfully'
                    ]);
                } else {
                    wp_send_json([
                        'code' => 400,
                        'message' => $create_address['message']
                    ]);
                }
            } else {
                //update address
                $update_address = updateTerminalAddress($merchant_address_id, $first_name, $last_name, $email, $phone, $line_1, $line_2, $city, $state, $country, $zip_code);
                //check if address is updated
                if ($update_address['code'] == 200) {
                    //save address
                    update_option('terminal_africa_merchant_address', $update_address['data']);
                    //return
                    wp_send_json([
                        'code' => 200,
                        'message' => 'Address updated successfully'
                    ]);
                } else {
                    wp_send_json([
                        'code' => 400,
                        'message' => $update_address['message']
                    ]);
                }
            }
        } catch (\Exception $e) {
            logTerminalError($e);
            wp_send_json([
                'code' => 400,
                'message' => "Something went wrong: " . $e->getMessage()
            ]);
        }
    }

    //terminal_customer_save_address
    public function terminal_customer_save_address()
    {
        try {
            //if session is not started start it
            if (session_status() == PHP_SESSION_NONE) {
                session_start();
            }
            $nounce = sanitize_text_field($_POST['nonce']);
            if (!wp_verify_nonce($nounce, 'terminal_africa_nonce')) {
                wp_send_json([
                    'code' => 400,
                    'message' => 'Wrong nonce, please refresh the page and try again'
                ]);
            }
            $first_name = sanitize_text_field($_POST['first_name']);
            $last_name = sanitize_text_field($_POST['last_name']);
            $email = sanitize_text_field($_POST['email']);
            $phone = sanitize_text_field($_POST['phone']);
            $line_1 = sanitize_text_field($_POST['line_1']);
            $line_2 = sanitize_text_field($_POST['line_2']);
            $city = sanitize_text_field($_POST['lga']);
            $state = sanitize_text_field($_POST['state']);
            $country = sanitize_text_field($_POST['country']);
            $zip_code = sanitize_text_field($_POST['zip_code']);
            $address_id = sanitize_text_field($_POST['address_id']);
            $rate_id = sanitize_text_field($_POST['rate_id']);

            if (strlen($line_1) > 45) {
                //break to line_2
                $line_2 = substr($line_1, 45);
                //remove the 45 character
                $line_1 = substr($line_1, 0, 45);
            }
            //check if any field is empty
            if (empty($first_name) || empty($last_name) || empty($email) || empty($phone) || empty($line_1) || empty($city) || empty($state) || empty($country) || empty($address_id)) {
                wp_send_json([
                    'code' => 400,
                    'message' => 'Please fill all required fields'
                ]);
            }
            //update address
            $update_address = updateTerminalAddress($address_id, $first_name, $last_name, $email, $phone, $line_1, $line_2, $city, $state, $country, $zip_code);
            //check if address is updated
            if ($update_address['code'] == 200) {
                //clear session data
                unset($_SESSION['ratedata'][$rate_id]);
                //return
                wp_send_json([
                    'code' => 200,
                    'message' => 'Address updated successfully',
                    'rate_cleared' => true
                ]);
            } else {
                wp_send_json([
                    'code' => 400,
                    'message' => $update_address['message']
                ]);
            }
        } catch (\Exception $e) {
            logTerminalError($e);
            wp_send_json([
                'code' => 400,
                'message' => "Something went wrong: " . $e->getMessage()
            ]);
        }
    }

    //terminal_africa_get_states
    public function terminal_africa_get_states()
    {
        try {
            $countryCode = sanitize_text_field($_GET['countryCode']);
            //nounce
            $nonce = sanitize_text_field($_GET['nonce']);
            if (!wp_verify_nonce($nonce, 'terminal_africa_nonce')) {
                wp_send_json([
                    'code' => 400,
                    'message' => 'Wrong nonce, please refresh the page and try again'
                ]);
            }
            //get states
            $states = get_terminal_states($countryCode);
            //check if states is empty
            if (empty($states['data'])) {
                wp_send_json([
                    'code' => 400,
                    'data' => [],
                    'message' => 'No states found, please select another country'
                ]);
            }
            //return
            wp_send_json([
                'code' => 200,
                'states' => $states['data'],
                'message' => 'States loaded',
            ]);
        } catch (\Exception $e) {
            logTerminalError($e);
            wp_send_json([
                'code' => 400,
                'data' => [],
                'message' => "Something went wrong: " . $e->getMessage()
            ]);
        }
    }

    //terminal_africa_get_cities
    public function terminal_africa_get_cities()
    {
        try {
            $stateCode = sanitize_text_field($_GET['stateCode']);
            $countryCode = sanitize_text_field($_GET['countryCode']);
            //nounce
            $nonce = sanitize_text_field($_GET['nonce']);
            if (!wp_verify_nonce($nonce, 'terminal_africa_nonce')) {
                wp_send_json([
                    'code' => 400,
                    'message' => 'Wrong nonce, please refresh the page and try again'
                ]);
            }
            //get cities
            $cities = get_terminal_cities($countryCode, $stateCode);
            //check if cities is empty
            if (empty($cities['data'])) {
                wp_send_json([
                    'code' => 400,
                    'cities' => [],
                    'message' => 'No cities found, please select another state'
                ]);
            }
            //return
            wp_send_json([
                'code' => 200,
                'cities' => $cities['data'],
                'message' => 'Cities loaded',
            ]);
        } catch (\Exception $e) {
            logTerminalError($e);
            wp_send_json([
                'code' => 400,
                'cities' => [],
                'message' => "Something went wrong: " . $e->getMessage()
            ]);
        }
    }

    //terminal_africa_sign_out
    public static function terminal_africa_sign_out()
    {
        try {
            //nounce
            $nonce = sanitize_text_field($_GET['nonce']);
            if (!wp_verify_nonce($nonce, 'terminal_africa_nonce')) {
                wp_send_json([
                    'code' => 400,
                    'message' => 'Wrong nonce, please refresh the page and try again'
                ]);
            }
            //delete options
            self::deactivate();
            //return
            wp_send_json([
                'code' => 200,
                'redirect_url' => admin_url('admin.php?page=terminal-africa'),
                'message' => 'Signed out successfully',
            ]);
        } catch (\Exception $e) {
            logTerminalError($e);
            wp_send_json([
                'code' => 400,
                'message' => "Something went wrong: " . $e->getMessage()
            ]);
        }
    }

    //terminal_africa_enable_terminal
    public static function terminal_africa_enable_terminal()
    {
        try {
            //nounce
            $nonce = sanitize_text_field($_POST['nonce']);
            if (!wp_verify_nonce($nonce, 'terminal_africa_nonce')) {
                wp_send_json([
                    'code' => 400,
                    'message' => 'Wrong nonce, please refresh the page and try again'
                ]);
            }
            //get shipping settings
            $settings = get_option('woocommerce_terminal_delivery_settings');
            //update shipping settings
            $settings['enabled'] = 'yes';
            update_option('woocommerce_terminal_delivery_settings', $settings);
            //return
            wp_send_json([
                'code' => 200,
                'message' => 'Terminal enabled successfully',
            ]);
        } catch (\Exception $e) {
            logTerminalError($e);
            wp_send_json([
                'code' => 400,
                'message' => "Something went wrong: " . $e->getMessage()
            ]);
        }
    }

    //terminal_africa_save_cart_item
    public function terminal_africa_save_cart_item()
    {
        try {
            //check if its ajax
            $nonce = sanitize_text_field($_POST['nonce']);
            if (!wp_verify_nonce($nonce, 'terminal_africa_nonce')) {
                wp_send_json([
                    'code' => 400,
                    'message' => 'Wrong nonce, please refresh the page and try again'
                ]);
            }
            //terminal_check_checkout_product_for_shipping_support
            $check_shipping_support = terminal_check_checkout_product_for_shipping_support();
            ///check if check_shipping_support is "false"
            if ($check_shipping_support === "false") {
                wp_send_json([
                    'code' => 400,
                    'message' => 'Downloadable products are not supported'
                ]);
            }

            //check if session is started
            if (session_status() == PHP_SESSION_NONE) {
                session_start();
            }

            //recaculate cart total
            WC()->cart->calculate_totals();

            //get cart item
            $cart_item = WC()->cart->get_cart();
            //check if cart item is empty
            if (empty($cart_item)) {
                wp_send_json([
                    'code' => 400,
                    'message' => 'Cart is empty'
                ]);
            }

            $data_items = [];
            //loop through cart items
            foreach ($cart_item as $item) {
                //$product_id
                $product_id = $item['product_id'];

                //get product hs code
                $terminal_hscode = get_post_meta($product_id, 'terminal_hscode', true);

                //get product image
                $product_image = get_the_post_thumbnail_url($product_id);

                //data items
                $data_items[] = [
                    'name' => $item['data']->get_name(),
                    'quantity' => !empty($item['quantity']) ? intval($item['quantity']) : 1,
                    'value' => $item['line_total'],
                    'description' => "{$item['quantity']} of {$item['data']->get_name()} at {$item['data']->get_price()} each for a total of {$item['line_total']}",
                    'type' => 'parcel',
                    'currency' => get_woocommerce_currency(),
                    'weight' => !empty($item['data']->get_weight()) ? (float)$item['data']->get_weight() : 0.1,
                    'hs_code' => $terminal_hscode,
                    'image' => $product_image ? $product_image : TERMINAL_AFRICA_PLUGIN_ASSETS_URL . '/img/logo-footer.png',
                    'plugin_product_id' => $product_id
                ];
            }
            //check if terminal_default_packaging_id is set
            $packaging_id = get_option('terminal_default_packaging_id');
            //verify packaging id
            $verifyDefaultPackaging = verifyDefaultPackaging($packaging_id);
            //check if verifyDefaultPackaging is 200
            if ($verifyDefaultPackaging['code'] != 200) {
                wp_send_json([
                    'code' => 400,
                    'message' => 'Unable to verify default packaging, please try again'
                ]);
            }
            //get new packaging id
            $packaging_id = $verifyDefaultPackaging['packaging_id'];

            //site url
            $site_url = site_url();
            //get the domain
            $domain = parse_url($site_url, PHP_URL_HOST);

            //arrange parcel
            $parcel = [
                'packaging' => $packaging_id,
                'weight_unit' => 'kg',
                'items' => $data_items,
                'description' => 'Order from ' . get_bloginfo('name'),
                'metadata' => [
                    'domain' => $domain,
                    'source' => 'wordpress'
                ],
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
                    //return
                    wp_send_json([
                        'code' => 200,
                        'type' => 'percel',
                        'message' => 'Parcel updated successfully',
                    ]);
                } else {
                    wp_send_json([
                        'code' => 401,
                        'type' => 'percel',
                        'message' => $response['message'] . " or clear your browser cache and try again"
                    ]);
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
                //return
                wp_send_json([
                    'code' => 200,
                    'message' => 'Parcel created successfully',
                ]);
            } else {
                wp_send_json([
                    'code' => 401,
                    'message' => $response['message'] . " or clear your browser cache and try again"
                ]);
            }
        } catch (\Exception $e) {
            logTerminalError($e, 'terminal_africa_save_cart_item');
            wp_send_json([
                'code' => 400,
                'message' => "Something went wrong: " . $e->getMessage()
            ]);
        }
    }

    //terminal_africa_process_terminal_rates
    public function terminal_africa_process_terminal_rates()
    {
        try {
            $nonce = sanitize_text_field($_POST['nonce']);
            if (!wp_verify_nonce($nonce, 'terminal_africa_nonce')) {
                wp_send_json([
                    'code' => 400,
                    'message' => 'Wrong nonce, please refresh the page and try again'
                ]);
            }
            //data
            $first_name = sanitize_text_field($_POST['first_name']);
            $last_name = sanitize_text_field($_POST['last_name']);
            $state  = sanitize_text_field($_POST['state']);
            $stateCode  = sanitize_text_field($_POST['stateCode']);
            $city = sanitize_text_field($_POST['city']);
            $country = sanitize_text_field($_POST['countryCode']);
            $zip_code = sanitize_text_field($_POST['billing_postcode']);
            $line_1 = sanitize_text_field($_POST['line_1']);
            //clean phone allow only numbers and +
            $phone = sanitize_text_field($_POST['phone']);
            // $phone = preg_replace('/[^0-9\+]/', '', $phone);
            // $zip_code = preg_replace('/[^0-9]/', '', $zip_code);
            $email = sanitize_text_field($_POST['email']);
            //guest_email_hashed
            $guest_email_hashed = md5($email);
            //check if line_1 is greater than 45 characters
            $line_2 = "";
            if (strlen($line_1) > 45) {
                //break to line_2
                $line_2 = substr($line_1, 45);
                //remove the 45 character
                $line_1 = substr($line_1, 0, 45);
            }
            //terminal session
            $terminalSession = TerminalSession::instance();
            //check if merchant_address_id is set
            $merchant_address_id = get_option('terminal_africa_merchant_address_id');
            if (!empty($merchant_address_id)) {
                //check if not empty $parcel_id 
                $parcel_id = $terminalSession->get('terminal_africa_parcel_id');
                if (empty($parcel_id)) {
                    //wc notice
                    wc_add_notice('Terminal Parcel is empty, please refresh the page and try again', 'error');
                    //return error
                    wp_send_json([
                        'code' => 400,
                        'message' => 'Terminal Parcel is empty, please refresh the page and try again'
                    ]);
                }
                $terminalSession = TerminalSession::instance();
                //check if address id is set
                $address_id = $terminalSession->get('terminal_africa_guest_address_id' . $guest_email_hashed);
                if (empty($address_id)) {
                    //create address
                    $create_address = createTerminalAddress($first_name, $last_name, $email, $phone, $line_1, $line_2, $city, $state, $country, $zip_code);
                    //check if address is created
                    if ($create_address['code'] == 200) {
                        //save address id wc session
                        $terminalSession->set('terminal_africa_guest_address_id' . $guest_email_hashed, $create_address['data']->address_id);
                        $address_id = $create_address['data']->address_id;
                    } else {
                        wp_send_json([
                            'code' => 400,
                            'message' => $create_address['message'],
                            'endpoint' => 'create_address'
                        ]);
                    }
                } else {
                    //update address
                    $update_address = updateTerminalAddress($address_id, $first_name, $last_name, $email, $phone, $line_1, $line_2, $city, $state, $country, $zip_code);
                    //check if address is updated
                    if ($update_address['code'] == 200) {
                        //save address id wc session
                        $terminalSession->set('terminal_africa_guest_address_id' . $guest_email_hashed, $update_address['data']->address_id);
                        $address_id = $update_address['data']->address_id;
                    } else {
                        wp_send_json([
                            'code' => 400,
                            'message' => $update_address['message'],
                            'endpoint' => 'update_address'
                        ]);
                    }
                }

                $address_from = $merchant_address_id;
                $address_to = $address_id;
                $parcel = $parcel_id;
                //get rates
                $get_rates = getTerminalRatesvbyAddressId($address_from, $address_to, $parcel);
                //check if rates is gotten
                if ($get_rates['code'] == 200) {
                    $terminal_price_markup = get_option('terminal_custom_price_mark_up', '');
                    //return
                    wp_send_json([
                        'code' => 200,
                        'message' => 'Rates gotten successfully',
                        'terminal_price_markup' => $terminal_price_markup,
                        'data' => $get_rates['data']
                    ]);
                } else {
                    //wc notice
                    wc_add_notice($get_rates['message'], 'error');
                    //return error
                    wp_send_json([
                        'code' => 400,
                        'message' => $get_rates['message'],
                        'endpoint' => 'get_rates'
                    ]);
                }
            }
            //add wc notice
            wc_add_notice("Terminal Merchant address not set, please contact the admin", 'error');
            //return error
            wp_send_json([
                'code' => 400,
                'message' => 'Terminal Merchant address not set'
            ]);
        } catch (\Exception $e) {
            logTerminalError($e, 'terminal_africa_process_terminal_rates');
            wp_send_json([
                'code' => 400,
                'message' => "Error: " . $e->getMessage()
            ]);
        }
    }

    //terminal_africa_save_shipping_carrier
    public function terminal_africa_save_shipping_carrier()
    {
        try {
            $nonce = sanitize_text_field($_POST['nonce']);
            if (!wp_verify_nonce($nonce, 'terminal_africa_nonce')) {
                wp_send_json([
                    'code' => 400,
                    'message' => 'Wrong nonce, please refresh the page and try again'
                ]);
            }
            //data
            $carriername = sanitize_text_field($_POST['carriername']);
            $amount = sanitize_text_field($_POST['amount']);
            $duration = sanitize_text_field($_POST['duration']);
            $email = sanitize_text_field($_POST['email']);
            $rateid = sanitize_text_field($_POST['rateid']);
            $pickuptime = sanitize_text_field($_POST['pickup']);
            $carrierlogo = sanitize_text_field($_POST['carrierlogo']);
            //wc session
            WC()->session->set('terminal_africa_carriername', $carriername);
            WC()->session->set('terminal_africa_amount', $amount);
            WC()->session->set('terminal_africa_duration', $duration);
            WC()->session->set('terminal_africa_guest_email', $email);
            WC()->session->set('terminal_africa_rateid', $rateid);
            WC()->session->set('terminal_africa_pickuptime', $pickuptime);
            WC()->session->set('terminal_africa_carrierlogo', $carrierlogo);
            //save backup data to php session
            //check if session is started
            if (session_status() == PHP_SESSION_NONE) {
                session_start();
            }
            $_SESSION['terminal_africa_carriername'] = $carriername;
            $_SESSION['terminal_africa_amount'] = $amount;
            $_SESSION['terminal_africa_duration'] = $duration;
            $_SESSION['terminal_africa_guest_email'] = $email;
            $_SESSION['terminal_africa_rateid'] = $rateid;
            $_SESSION['terminal_africa_pickuptime'] = $pickuptime;
            $_SESSION['terminal_africa_carrierlogo'] = $carrierlogo;
            //return
            wp_send_json([
                'code' => 200,
                'message' => 'Carrier saved successfully'
            ]);
        } catch (\Exception $e) {
            logTerminalError($e);
            wp_send_json([
                'code' => 400,
                'message' => "Error: " . $e->getMessage()
            ]);
        }
    }

    //terminal_africa_get_rate_data
    public function terminal_africa_get_rate_data()
    {
        try {
            $nonce = sanitize_text_field($_GET['nonce']);
            if (!wp_verify_nonce($nonce, 'terminal_africa_nonce')) {
                wp_send_json([
                    'code' => 400,
                    'message' => 'Wrong nonce, please refresh the page and try again'
                ]);
            }
            //data
            $rate_id = sanitize_text_field($_GET['rate_id']);
            //check if rate_id is empty
            if (empty($rate_id)) {
                //return error
                wp_send_json([
                    'code' => 400,
                    'message' => 'Rate ID is empty, please refresh the page and try again'
                ]);
            }
            //get rate data
            $get_rate_data = getTerminalRateData($rate_id);
            //check if rate data is gotten
            if ($get_rate_data['code'] == 200) {
                //return
                wp_send_json([
                    'code' => 200,
                    'message' => 'Rate data gotten successfully',
                    'data' => $get_rate_data['data']
                ]);
            } else {
                //return error
                wp_send_json([
                    'code' => 400,
                    'message' => $get_rate_data['message'],
                    'endpoint' => 'get_rate_data'
                ]);
            }
        } catch (\Exception $e) {
            logTerminalError($e, 'terminal_africa_get_rate_data');
            wp_send_json([
                'code' => 400,
                'message' => "Error: " . $e->getMessage(),
                'endpoint' => 'get_rate_data'
            ]);
        }
    }

    //terminal_africa_process_terminal_rates_customer
    public function terminal_africa_process_terminal_rates_customer()
    {
        try {
            $nonce = sanitize_text_field($_GET['nonce']);
            if (!wp_verify_nonce($nonce, 'terminal_africa_nonce')) {
                wp_send_json([
                    'code' => 400,
                    'message' => 'Wrong nonce, please refresh the page and try again'
                ]);
            }
            //data
            $shipment_id = sanitize_text_field($_GET['shipment_id']);
            //get rate
            $get_rate = getTerminalRates($shipment_id);
            //check if rate is gotten
            if ($get_rate['code'] == 200) {
                //return
                wp_send_json([
                    'code' => 200,
                    'message' => 'Rate gotten successfully',
                    'data' => $get_rate['data']
                ]);
            } else {
                //return error
                wp_send_json([
                    'code' => 400,
                    'message' => $get_rate['message'],
                    'endpoint' => 'get_rate'
                ]);
            }
        } catch (\Exception $e) {
            logTerminalError($e, 'terminal_africa_process_terminal_rates_customer');
            wp_send_json([
                'code' => 400,
                'message' => "Error: " . $e->getMessage(),
                'endpoint' => 'get_rate'
            ]);
        }
    }

    //terminal_africa_apply_terminal_rates_customer
    public function terminal_africa_apply_terminal_rates_customer()
    {
        try {
            $nonce = sanitize_text_field($_GET['nonce']);
            if (!wp_verify_nonce($nonce, 'terminal_africa_nonce')) {
                wp_send_json([
                    'code' => 400,
                    'message' => 'Wrong nonce, please refresh the page and try again'
                ]);
            }
            //data
            $order_id = sanitize_text_field($_GET['order_id']);
            $rateid = sanitize_text_field($_GET['rateid']);
            $pickup = sanitize_text_field($_GET['pickup']);
            $duration = sanitize_text_field($_GET['duration']);
            $amount = sanitize_text_field($_GET['amount']);
            $carrier_name = sanitize_text_field($_GET['carrier_name']);
            $carrierlogo = sanitize_text_field($_GET['carrierlogo']);
            //check if rate_id is empty
            if (empty($rateid) || empty($pickup) || empty($duration) || empty($amount) || empty($carrier_name)) {
                //return error
                wp_send_json([
                    'code' => 400,
                    'message' => 'Rate ID, pickup, duration, amount or carrier name is empty, please refresh the page and try again'
                ]);
            }
            //apply rate
            $apply_rate = applyTerminalRate($order_id, $rateid, $pickup, $duration, $amount, $carrier_name, $carrierlogo);
            //check if rate is applied
            if ($apply_rate['code'] == 200) {
                //return
                wp_send_json([
                    'code' => 200,
                    'message' => 'Rate applied successfully',
                    'url' => $apply_rate['url'],
                    'data' => $apply_rate['data']
                ]);
            } else {
                //return error
                wp_send_json([
                    'code' => 400,
                    'message' => $apply_rate['message'],
                    'endpoint' => 'apply_rate'
                ]);
            }
        } catch (\Exception $e) {
            logTerminalError($e, 'terminal_africa_apply_terminal_rates_customer');
            wp_send_json([
                'code' => 400,
                'message' => "Error: " . $e->getMessage(),
                'endpoint' => 'apply_rate'
            ]);
        }
    }

    //terminal_africa_arrange_terminal_delivery
    public function terminal_africa_arrange_terminal_delivery()
    {
        try {
            $nonce = sanitize_text_field($_POST['nonce']);
            if (!wp_verify_nonce($nonce, 'terminal_africa_nonce')) {
                wp_send_json([
                    'code' => 400,
                    'message' => 'Wrong nonce, please refresh the page and try again'
                ]);
            }
            //data
            $order_id = sanitize_text_field($_POST['order_id']);
            $rateid = sanitize_text_field($_POST['rateid']);
            $shipment_id = sanitize_text_field($_POST['shipment_id']);
            $dropoff_id = sanitize_text_field($_POST['dropoff_id']);
            //check if rate_id is empty
            if (empty($rateid) || empty($shipment_id)) {
                //return error
                wp_send_json([
                    'code' => 400,
                    'message' => 'Rate ID or shipment ID is empty, please refresh the page and try again'
                ]);
            }

            //check if dropoff_id is none
            if ($dropoff_id == 'none') {
                $dropoff_id = null;
            }

            //arrange delivery
            $delivery = arrangePickupAndDelivery($shipment_id, $rateid, $dropoff_id);
            //check if delivery is arranged
            if ($delivery['code'] == 200) {
                //add order meta
                update_post_meta($order_id, 'terminal_africa_delivery_arranged', "yes");
                //return
                wp_send_json([
                    'code' => 200,
                    'message' => 'Delivery arranged successfully',
                    'data' => $delivery['data']
                ]);
            } else {
                //return error
                wp_send_json([
                    'code' => 401,
                    'message' => $delivery['message'],
                    'endpoint' => 'arrange_delivery'
                ]);
            }
        } catch (\Exception $e) {
            logTerminalError($e, 'terminal_africa_arrange_terminal_delivery');
            wp_send_json([
                'code' => 400,
                'message' => "Error: " . $e->getMessage(),
                'endpoint' => 'arrange_delivery'
            ]);
        }
    }

    //refresh_terminal_wallet
    public function refresh_terminal_wallet()
    {
        try {
            $nonce = sanitize_text_field($_GET['nonce']);
            if (!wp_verify_nonce($nonce, 'terminal_africa_nonce')) {
                wp_send_json([
                    'code' => 400,
                    'message' => 'Wrong nonce, please refresh the page and try again'
                ]);
            }
            //data
            $terminal_africa_merchant_id = get_option('terminal_africa_merchant_id');
            //get wallet balance
            $wallet_balance = getWalletBalance($terminal_africa_merchant_id, true);
            //check if wallet balance is gotten
            if ($wallet_balance['code'] == 200) {
                //return
                wp_send_json([
                    'code' => 200,
                    'message' => 'Wallet balance gotten successfully',
                    'data' => $wallet_balance['data']
                ]);
            } else {
                //return error
                wp_send_json([
                    'code' => 400,
                    'message' => $wallet_balance['message'],
                    'endpoint' => 'get_wallet_balance'
                ]);
            }
        } catch (\Exception $e) {
            logTerminalError($e, 'refresh_terminal_wallet');
            wp_send_json([
                'code' => 400,
                'message' => "Error: " . $e->getMessage(),
                'endpoint' => 'get_wallet_balance'
            ]);
        }
    }

    //refresh_terminal_rate
    public function refresh_terminal_rate_data()
    {
        try {
            $nonce = sanitize_text_field($_GET['nonce']);
            if (!wp_verify_nonce($nonce, 'terminal_africa_nonce')) {
                wp_send_json([
                    'code' => 400,
                    'message' => 'Wrong nonce, please refresh the page and try again'
                ]);
            }
            //data
            $rate_id = sanitize_text_field($_GET['rate_id']);
            //get rate
            $get_rate = getTerminalRateData($rate_id, true);
            //check if rate is gotten
            if ($get_rate['code'] == 200) {
                //return
                wp_send_json([
                    'code' => 200,
                    'message' => 'Rate gotten successfully',
                    'data' => $get_rate['data']
                ]);
            } else {
                //return error
                wp_send_json([
                    'code' => 400,
                    'message' => $get_rate['message'],
                    'endpoint' => 'get_rate'
                ]);
            }
        } catch (\Exception $e) {
            logTerminalError($e, 'refresh_terminal_rate_data');
            wp_send_json([
                'code' => 400,
                'message' => "Error: " . $e->getMessage(),
                'endpoint' => 'get_rate'
            ]);
        }
    }

    //save_terminal_carrier_settings
    public function save_terminal_carrier_settings()
    {
        try {
            //if session is not started start it
            if (session_status() == PHP_SESSION_NONE) {
                session_start();
            }
            $nonce = sanitize_text_field($_POST['nonce']);
            if (!wp_verify_nonce($nonce, 'terminal_africa_nonce')) {
                wp_send_json([
                    'code' => 400,
                    'message' => 'Wrong nonce, please refresh the page and try again'
                ]);
            }
            //data
            $terminalEnabledCarriers = sanitize_array($_POST['terminalEnabledCarriers']);
            $terminalDisabledCarriers = sanitize_array($_POST['terminalDisabledCarriers']);
            //check if terminalEnabledCarriers is empty
            if (empty($terminalEnabledCarriers) || empty($terminalDisabledCarriers)) {
                //return error
                wp_send_json([
                    'code' => 400,
                    'message' => 'Please select at least one carrier',
                ]);
            }
            //save settings
            //enableMultipleCarriers
            $enableMultipleCarriers = enableMultipleCarriers($terminalEnabledCarriers);
            //disableMultipleCarriers
            $disableMultipleCarriers = disableMultipleCarriers($terminalDisabledCarriers);
            //check if settings are saved
            if ($enableMultipleCarriers['code'] == 200 && $disableMultipleCarriers['code'] == 200) {
                //clear cache
                unset($_SESSION['terminal_carriers_data']);
                //return
                wp_send_json([
                    'code' => 200,
                    'message' => 'Settings saved successfully',
                ]);
            } else {
                //return error
                wp_send_json([
                    'code' => 400,
                    'message' => $enableMultipleCarriers['message'] . ', ' . $disableMultipleCarriers['message'],
                ]);
            }
        } catch (\Exception $e) {
            logTerminalError($e, 'save_terminal_carrier_settings');
            wp_send_json([
                'code' => 400,
                'message' => "Error: " . $e->getMessage(),
            ]);
        }
    }

    //refresh_terminal_carriers_data
    public function refresh_terminal_carriers_data()
    {
        try {
            $nonce = sanitize_text_field($_GET['nonce']);
            if (!wp_verify_nonce($nonce, 'terminal_africa_nonce')) {
                wp_send_json([
                    'code' => 400,
                    'message' => 'Wrong nonce, please refresh the page and try again'
                ]);
            }
            //getUserCarriers
            $user_carriers_domestic = getUserCarriers('domestic', true);
            //getUserCarriers international
            $user_carriers_international = getUserCarriers('international', true);
            //getUserCarriers regional
            $user_carriers_regional = getUserCarriers('regional', true);
            //get domestic carriers 
            $domestic_carriers = getTerminalCarriers('domestic', true);
            //get international carriers
            $international_carriers = getTerminalCarriers('international', true);
            //regional
            $regional_carriers = getTerminalCarriers('regional', true);
            //check if carriers are gotten
            if ($domestic_carriers['code'] == 200 && $international_carriers['code'] == 200 && $regional_carriers['code'] == 200 && $user_carriers_domestic['code'] == 200 && $user_carriers_international['code'] == 200 && $user_carriers_regional['code'] == 200) {
                //return
                wp_send_json([
                    'code' => 200,
                    'message' => 'Carriers updated successfully',
                ]);
            } else {
                //return error
                wp_send_json([
                    'code' => 400,
                    'message' => 'Carriers not updated, please try again',
                    'endpoint' => 'get_carriers'
                ]);
            }
        } catch (\Exception $e) {
            logTerminalError($e, 'refresh_terminal_carriers_data');
            wp_send_json([
                'code' => 400,
                'message' => "Error: " . $e->getMessage(),
                'endpoint' => 'get_carriers'
            ]);
        }
    }

    //get packaging
    public function get_terminal_packaging()
    {
        try {
            $nonce = sanitize_text_field($_GET['nonce']);
            if (!wp_verify_nonce($nonce, 'terminal_africa_nonce')) {
                wp_send_json([
                    'code' => 400,
                    'message' => 'Wrong nonce, please refresh the page and try again'
                ]);
            }
            //get packaging
            $get_packaging = getTerminalPackagingData();
            //check if packaging is gotten
            if ($get_packaging['code'] == 200) {
                //return
                wp_send_json([
                    'code' => 200,
                    'message' => 'Packaging gotten successfully',
                    'data' => $get_packaging['data']
                ]);
            } else {
                //return error
                wp_send_json([
                    'code' => 400,
                    'message' => $get_packaging['message'],
                    'endpoint' => 'get_packaging'
                ]);
            }
        } catch (\Exception $e) {
            logTerminalError($e, 'get_terminal_packaging');
            wp_send_json([
                'code' => 400,
                'message' => "Error: " . $e->getMessage(),
                'endpoint' => 'get_packaging'
            ]);
        }
    }

    //get_terminal_shipment_status
    public function get_terminal_shipment_status()
    {
        try {
            $nonce = sanitize_text_field($_GET['nonce']);
            if (!wp_verify_nonce($nonce, 'terminal_africa_nonce')) {
                wp_send_json([
                    'code' => 400,
                    'message' => 'Wrong nonce, please refresh the page and try again'
                ]);
            }
            //data
            $shipment_id = sanitize_text_field($_GET['shipment_id']);
            $order_id = sanitize_text_field($_GET['order_id']);
            $rate_id = sanitize_text_field($_GET['rate_id']);
            //get shipment status
            $get_shipment_status = getTerminalShipmentStatus($shipment_id);
            //check if shipment status is gotten
            if ($get_shipment_status['code'] == 200) {
                $status = $get_shipment_status['data'];
                //cancellation_request
                $cancellation_request = $get_shipment_status['shipment_info']->cancellation_request;
                //get terminal template
                switch ($status) {
                    case 'draft':
                        $getTerminalTemplate = getTerminalTemplate('shipment-button/button', compact('rate_id', 'order_id', 'shipment_id'));
                        break;
                    case 'canceled':
                        $getTerminalTemplate = getTerminalTemplate('shipment-button/duplicate-shipment', compact('rate_id', 'order_id', 'shipment_id'));
                        break;
                    case 'confirmed':
                        $getTerminalTemplate = getTerminalTemplate('shipment-button/cancel-shipment', compact('rate_id', 'order_id', 'shipment_id'));
                        break;
                    default:
                        $getTerminalTemplate = '';
                        break;
                }
                //check if cancellation request is true
                if ($cancellation_request) {
                    $getTerminalTemplate = getTerminalTemplate('shipment-button/duplicate-shipment', compact('rate_id', 'order_id', 'shipment_id'));
                }
                //return
                wp_send_json([
                    'code' => 200,
                    'message' => 'Shipment status gotten successfully',
                    'data' => $status,
                    'button' => $getTerminalTemplate,
                    'shipment_info' => $get_shipment_status['shipment_info'],
                ]);
            } else {
                //return error
                wp_send_json([
                    'code' => 400,
                    'message' => $get_shipment_status['message'],
                    'endpoint' => 'get_shipment_status',
                    'data' => 'not available',
                    'button' => 'Try shipment again.',
                ]);
            }
        } catch (\Exception $e) {
            logTerminalError($e, 'get_terminal_shipment_status');
            wp_send_json([
                'code' => 400,
                'message' => "Error: " . $e->getMessage(),
                'endpoint' => 'get_shipment_status',
                'data' => 'not available',
                'button' => 'Try shipment again.',
            ]);
        }
    }

    //update_user_carrier_terminal
    public function update_user_carrier_terminal()
    {
        try {
            //check if session is started
            if (session_status() == PHP_SESSION_NONE) {
                session_start();
            }
            $nonce = sanitize_text_field($_POST['nonce']);
            if (!wp_verify_nonce($nonce, 'terminal_africa_nonce')) {
                wp_send_json([
                    'code' => 400,
                    'message' => 'Wrong nonce, please refresh the page and try again'
                ]);
            }
            //data
            $carriers = sanitize_array($_POST['carrierObj']);
            $status = sanitize_text_field($_POST['status']);
            //sanitize object data
            foreach ($carriers as $key => $value) {
                $carriers[$key] = $value;
            }
            //check if carriers is empty
            if (empty($carriers)) {
                //return error
                wp_send_json([
                    'code' => 400,
                    'message' => 'Please select at least one carrier',
                ]);
            }
            $arrayData = [
                'carriers' => [
                    [
                        'carrier_id' => $carriers['id'],
                        'domestic' => (bool)$carriers['domestic'],
                        'international' => (bool)$carriers['international'],
                        'regional' => (bool)$carriers['regional'],
                    ]
                ]
            ];
            //check if status is enabled
            if ($status == 'enabled') {
                //enable carrier
                $enable_carrier = enableSingleCarriers($arrayData);
                //check if carrier is enabled
                if ($enable_carrier['code'] == 200) {
                    //clear cache
                    unset($_SESSION['terminal_carriers_data']);
                    //terminal_africa_carriers
                    unset($_SESSION['terminal_africa_carriers']);
                    //return
                    wp_send_json([
                        'code' => 200,
                        'message' => 'Carrier enabled successfully',
                    ]);
                } else {
                    //return error
                    wp_send_json([
                        'code' => 400,
                        'message' => $enable_carrier['message'],
                    ]);
                }
            } else {
                //disable carrier
                $disable_carrier = disableSingleCarriers($arrayData);
                //check if carrier is disabled
                if ($disable_carrier['code'] == 200) {
                    //clear cache
                    unset($_SESSION['terminal_carriers_data']);
                    //terminal_africa_carriers
                    unset($_SESSION['terminal_africa_carriers']);
                    //return
                    wp_send_json([
                        'code' => 200,
                        'message' => 'Carrier disabled successfully',
                    ]);
                } else {
                    //return error
                    wp_send_json([
                        'code' => 400,
                        'message' => $disable_carrier['message'],
                    ]);
                }
            }
            //return
            wp_send_json([
                'code' => 500,
                'message' => 'Something went wrong, please try again',
            ]);
        } catch (\Exception $e) {
            logTerminalError($e, 'update_user_carrier_terminal');
            wp_send_json([
                'code' => 400,
                'message' => "Error: " . $e->getMessage(),
            ]);
        }
    }

    //cancel_terminal_shipment
    public static function cancel_terminal_shipment()
    {
        try {
            $nonce = sanitize_text_field($_GET['nonce']);
            if (!wp_verify_nonce($nonce, 'terminal_africa_nonce')) {
                wp_send_json([
                    'code' => 400,
                    'message' => 'Wrong nonce, please refresh the page and try again'
                ]);
            }
            //data
            $shipment_id = sanitize_text_field($_GET['shipment_id']);
            //cancel shipment
            $cancel_shipment = cancelTerminalShipment($shipment_id);
            //check if shipment is canceled
            if ($cancel_shipment['code'] == 200) {
                //return
                wp_send_json([
                    'code' => 200,
                    'message' => 'Shipment canceled successfully',
                ]);
            } else {
                //return error
                wp_send_json([
                    'code' => 400,
                    'message' => $cancel_shipment['message'],
                ]);
            }
        } catch (\Exception $e) {
            logTerminalError($e, 'cancel_terminal_shipment');
            wp_send_json([
                'code' => 400,
                'message' => "Error: " . $e->getMessage(),
            ]);
        }
    }

    /**
     * Ajax save_terminal_custom_price_mark_up
     * @return mixed
     * @since 1.10.4
     */
    public function save_terminal_custom_price_mark_up()
    {
        try {
            $nonce = sanitize_text_field($_POST['nonce']);
            if (!wp_verify_nonce($nonce, 'terminal_africa_nonce')) {
                wp_send_json([
                    'code' => 400,
                    'message' => 'Wrong nonce, please refresh the page and try again'
                ]);
            }
            //data
            $percentage = sanitize_text_field($_POST['percentage']);
            //save custom price mark up
            update_option('terminal_custom_price_mark_up', $percentage);
            //check if percentage is empty
            if (empty($percentage)) {
                //return error
                wp_send_json([
                    'code' => 200,
                    'message' => 'Default price mark up saved successfully',
                ]);
            }
            //return
            wp_send_json([
                'code' => 200,
                'message' => 'Custom price mark up saved successfully',
            ]);
        } catch (\Exception $e) {
            logTerminalError($e, 'save_terminal_custom_price_mark_up');
            wp_send_json([
                'code' => 400,
                'message' => "Error: " . $e->getMessage(),
            ]);
        }
    }

    /**
     * save_terminal_default_currency_code
     * @return mixed
     * @since 1.10.5
     */
    public function save_terminal_default_currency_code()
    {
        try {
            $nonce = sanitize_text_field($_POST['nonce']);
            if (!wp_verify_nonce($nonce, 'terminal_africa_nonce')) {
                wp_send_json([
                    'code' => 400,
                    'message' => 'Wrong nonce, please refresh the page and try again'
                ]);
            }
            //data
            $currency_code = sanitize_text_field($_POST['currency_code']) ?: "NGN";
            //isoCode
            $isoCode = sanitize_text_field($_POST['isocode']) ?: "NG";
            //update default currency code on terminal server
            $update_default_currency_code = updateDefaultCurrencyCode($currency_code);
            //check if currency code is updated
            if ($update_default_currency_code['code'] == 200) {
                //save default currency code
                update_option('terminal_default_currency_code', ['currency_code' => $currency_code, 'isoCode' => $isoCode]);
                //return
                wp_send_json([
                    'code' => 200,
                    'message' => 'Default currency code saved successfully',
                ]);
            } else {
                //return error
                wp_send_json([
                    'code' => 400,
                    'message' => $update_default_currency_code['message'],
                ]);
            }
        } catch (\Exception $e) {
            logTerminalError($e, 'save_terminal_default_currency_code');
            wp_send_json([
                'code' => 400,
                'message' => "Error: " . $e->getMessage(),
            ]);
        }
    }

    /**
     * Reset carriers data
     */
    public function terminal_reset_carriers_data()
    {
        try {
            $nonce = sanitize_text_field($_POST['nonce']);
            if (!wp_verify_nonce($nonce, 'terminal_africa_nonce')) {
                wp_send_json([
                    'code' => 400,
                    'message' => 'Wrong nonce, please refresh the page and try again'
                ]);
            }
            //check if session is started
            if (session_status() == PHP_SESSION_NONE) {
                session_start();
            }
            //reset carrier data or delete session
            WC()->session->__unset('terminal_africa_carriername');
            WC()->session->__unset('terminal_africa_amount');
            WC()->session->__unset('terminal_africa_duration');
            WC()->session->__unset('terminal_africa_rateid');
            //return
            wp_send_json([
                'code' => 200,
                'message' => 'Carriers data reset successfully',
            ]);
        } catch (\Exception $e) {
            logTerminalError($e, 'terminal_reset_carriers_data');
            wp_send_json([
                'code' => 400,
                'message' => "Error: " . $e->getMessage(),
            ]);
        }
    }

    /**
     * update_user_carrier_shipment_timeline_terminal
     */
    public function update_user_carrier_shipment_timeline_terminal()
    {
        try {
            $nonce = sanitize_text_field($_POST['nonce']);
            if (!wp_verify_nonce($nonce, 'terminal_africa_nonce')) {
                wp_send_json([
                    'code' => 400,
                    'message' => 'Wrong nonce, please refresh the page and try again'
                ]);
            }
            //status
            $status = sanitize_text_field($_POST['status']);
            //update option
            update_option('terminal_user_carrier_shipment_timeline', $status);
            //return
            wp_send_json([
                'code' => 200,
                'message' => 'Shipment timeline updated successfully',
            ]);
        } catch (\Exception $e) {
            logTerminalError($e, 'update_user_carrier_shipment_timeline_terminal');
            wp_send_json([
                'code' => 400,
                'message' => "Error: " . $e->getMessage(),
            ]);
        }
    }

    /**
     * update_user_carrier_shipment_rate_terminal
     */
    public function update_user_carrier_shipment_rate_terminal()
    {
        try {
            $nonce = sanitize_text_field($_POST['nonce']);
            if (!wp_verify_nonce($nonce, 'terminal_africa_nonce')) {
                wp_send_json([
                    'code' => 400,
                    'message' => 'Wrong nonce, please refresh the page and try again'
                ]);
            }
            //status
            $status = sanitize_text_field($_POST['status']);
            //update option
            update_option('update_user_carrier_shipment_rate_terminal', $status);

            //get shipping settings
            $settings = get_option('woocommerce_terminal_delivery_settings');

            //check if status is true
            if ($status == 'true') {
                //update shipping settings
                $settings['enabled'] = 'yes';
            } else {
                //update shipping settings
                $settings['enabled'] = 'no';
            }
            //update method
            update_option('woocommerce_terminal_delivery_settings', $settings);

            //return
            wp_send_json([
                'code' => 200,
                'message' => 'Shipment rate updated successfully',
                'shipment_rate' => $status
            ]);
        } catch (\Exception $e) {
            logTerminalError($e, 'update_user_carrier_shipment_rate_terminal');
            wp_send_json([
                'code' => 400,
                'message' => "Error: " . $e->getMessage(),
            ]);
        }
    }

    /**
     * update_user_carrier_shipment_insurance_terminal
     */
    public function update_user_carrier_shipment_insurance_terminal()
    {
        try {
            $nonce = sanitize_text_field($_POST['nonce']);
            if (!wp_verify_nonce($nonce, 'terminal_africa_nonce')) {
                wp_send_json([
                    'code' => 400,
                    'message' => 'Wrong nonce, please refresh the page and try again'
                ]);
            }
            //status
            $status = sanitize_text_field($_POST['status']);
            //update option
            update_option('update_user_carrier_shipment_insurance_terminal', $status);
            //return
            wp_send_json([
                'code' => 200,
                'message' => 'Shipment insurance updated successfully',
            ]);
        } catch (\Exception $e) {
            logTerminalError($e, 'update_user_carrier_shipment_insurance_terminal');
            wp_send_json([
                'code' => 400,
                'message' => "Error: " . $e->getMessage(),
            ]);
        }
    }

    /**
     * Get Address Book
     */
    public function terminal_africa_get_address_book()
    {
        try {
            $nonce = sanitize_text_field($_GET['nonce']);
            if (!wp_verify_nonce($nonce, 'terminal_africa_nonce')) {
                wp_send_json([
                    'code' => 400,
                    'message' => 'Wrong nonce, please refresh the page and try again'
                ]);
            }

            //get page
            $page = sanitize_text_field($_GET['page']);
            //search
            $search = sanitize_text_field($_GET['search']);

            //get the addresses from the server
            $addresses = terminalAfricaAddresses(25, $page, $search);

            //check if addresses are gotten
            if ($addresses['code'] == 200) {
                //return
                wp_send_json([
                    'code' => 200,
                    'message' => 'Addresses gotten successfully',
                    'data' => $addresses['data']
                ]);
            } else {
                //return error
                wp_send_json([
                    'code' => 400,
                    'message' => $addresses['message'],
                    'endpoint' => 'get_addresses'
                ]);
            }
        } catch (\Exception $e) {
            logTerminalError($e, 'terminal_africa_get_address_book');
            wp_send_json([
                'code' => 400,
                'message' => "Error: " . $e->getMessage(),
            ]);
        }
    }

    /**
     * Get Transactions
     * 
     * @since 1.11.1
     */
    public function terminal_africa_get_transactions()
    {
        try {
            $nonce = sanitize_text_field($_GET['nonce']);
            $page = sanitize_text_field($_GET['page']);

            if (!wp_verify_nonce($nonce, 'terminal_africa_nonce')) {
                wp_send_json([
                    'code' => 400,
                    'message' => 'Wrong nonce, please try again'
                ]);
            }
            //get the addresses from the server
            $transactions = getTransactions($page);
            //check if addresses are gotten
            if ($transactions['code'] == 200) {
                //return
                wp_send_json([
                    'code' => 200,
                    'message' => 'Transactions gotten successfully',
                    'data' => $transactions['data']
                ]);
            } else {
                //return error
                wp_send_json([
                    'code' => 400,
                    'message' => $transactions['message'],
                    'endpoint' => 'get_transactions'
                ]);
            }
        } catch (\Exception $e) {
            logTerminalError($e, 'terminal_africa_get_transactions');
            wp_send_json([
                'code' => 400,
                'message' => "Error: " . $e->getMessage(),
            ]);
        }
    }

    /**
     * Manage Shipment Page
     * 
     * @return void
     */
    public function terminal_africa_get_shipping_api_data()
    {
        try {
            $nonce = sanitize_text_field($_GET['nonce']);
            if (!wp_verify_nonce($nonce, 'terminal_africa_nonce')) {
                wp_send_json([
                    'code' => 400,
                    'message' => 'Wrong nonce, please try again'
                ]);
            }
            //sanitize
            $shipping_id = sanitize_text_field($_GET['id']);
            //sanitize
            $order_id = sanitize_text_field($_GET['order_id']);
            //get order
            $order = wc_get_order($order_id);
            //rate_id
            $rate_id = sanitize_text_field($_GET['rate_id']);
            //get rate data
            $get_rate_data = getTerminalRateData($rate_id);
            //order date
            $order_date = $order->get_date_created();
            //get order date
            $order_date = $order_date->date('Y-m-d H:i:s');
            //human readable date
            $order_time = human_time_diff(strtotime($order_date), current_time('timestamp')) . ' ago';
            //order status
            $order_status = $order->get_status();
            //$order_url
            $order_url = admin_url('post.php?post=' . $order_id . '&action=edit');
            //order shipping method
            $order_shipping_method = $order->get_shipping_method();
            //order shipping price
            $order_shipping_price = $order->get_shipping_total();
            //get the items
            $items = $order->get_items();
            //check if $get_rate_data is not empty
            $saved_address = null;
            $saved_others = null;
            if ($get_rate_data['code'] == 200) {
                $saved_address = $get_rate_data['data']->delivery_address;
                $saved_others = $get_rate_data['data'];
            }
            $states = get_terminal_states($saved_address ? $saved_address->country : 'NG');
            $states = $states['data'];

            //check if saved others is not empty
            $saved_address_state = $saved_others ? $saved_others->delivery_address->state : 'Lagos';

            //get the state isoCode from $states
            $stateIso = array_filter($states, function ($state) use ($saved_address_state) {
                return $state->name == $saved_address_state;
            });
            //shift the state isoCode
            $stateIso = array_shift($stateIso);

            //get cities
            $cities = get_terminal_cities($saved_address ? $saved_address->country : 'NG', $stateIso->isoCode);

            //get order currency
            $order_currency = $order->get_currency();
            //loop through shipping method
            $shippingItems    = (array) $order->get_items('shipping');
            //$shipping_cost
            $shipping_cost = $saved_others->amount;
            // Loop through shipping shippingItems
            foreach ($shippingItems as $item) {
                //get shipping method id
                $shipping_method_id = $item->get_method_id();
                //if shipping method id is terminal_delivery
                if ($shipping_method_id == "terminal_delivery") {
                    //get shipping cost
                    $shipping_cost = $item->get_total();
                    break;
                }
            }

            //get shipping price
            $shipping_price =
                $saved_others ? wc_price($shipping_cost, ['currency' => $order_currency]) : 0;

            //compact all data
            $data = compact('shipping_id', 'order_id', 'order_date', 'order_time', 'order_status', 'order_url', 'order_shipping_method', 'order_shipping_price', 'items', 'saved_address', 'saved_others', 'states', 'order_currency', 'shipping_cost', 'cities', 'shipping_price');

            //return data
            wp_send_json([
                'code' => 200,
                'message' => 'Data gotten successfully',
                'data' => $data
            ]);
        } catch (\Exception $e) {
            logTerminalError($e, 'terminal_africa_get_shipping_api_data');
            wp_send_json([
                'code' => 400,
                'message' => "Error: " . $e->getMessage(),
            ]);
        }
    }

    /**
     * Get Merchant Address Data
     * 
     */
    public function terminal_africa_get_merchant_address_data()
    {
        try {
            //get nonce
            $nonce = sanitize_text_field($_GET['nonce']);
            if (!wp_verify_nonce($nonce, 'terminal_africa_nonce')) {
                wp_send_json([
                    'code' => 400,
                    'message' => 'Wrong nonce, please try again'
                ]);
            }
            //get saved address
            $saved_address = get_option('terminal_africa_merchant_address', false);
            $saved_address_state = "LA";
            //get merchant address id
            $merchant_address_id = get_option('terminal_africa_merchant_address_id', '');

            $states = get_terminal_states($saved_address ? $saved_address->country : 'NG');
            $states = $states['data'];

            //check if saved others is not empty
            $saved_address_state = $saved_address ? $saved_address->state : 'Lagos';

            //get the state isoCode from $states
            $stateIso = array_filter($states, function ($state) use ($saved_address_state) {
                return $state->name == $saved_address_state;
            });
            //shift the state isoCode
            $stateIso = array_shift($stateIso);

            //get cities
            $cities = get_terminal_cities($saved_address ? $saved_address->country : 'NG', $stateIso->isoCode);

            //shippingData
            $shippingData = compact('states', 'cities');
            //return 
            wp_send_json([
                'code' => 200,
                'message' => 'Data gotten successfully',
                'data' => compact('saved_address', 'merchant_address_id', 'shippingData')
            ]);
        } catch (\Exception $e) {
            logTerminalError($e, 'terminal_africa_get_merchant_address_data');
            wp_send_json([
                'code' => 400,
                'message' => "Error: " . $e->getMessage(),
            ]);
        }
    }
}
