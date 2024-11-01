<?php

namespace TerminalAfrica\Includes\Parts;

use TerminalLogHandler;

//security
defined('ABSPATH') or die('No script kiddies please!');

trait Activation
{
    /**
     * Activate init
     * 
     */
    public function activate_terminal_init()
    {
        //activate payment gateway
        $this->activate_payment_gateway_init();
        //check user is on checkout
        add_action('wp', [$this, 'check_user_on_checkout_init'], PHP_INT_MAX);
    }

    //activate
    public static function activate()
    {
        try {
            //check if terminal_africa_redirected is set
            if (get_option('terminal_africa_redirected')) {
                //return
                return;
            }
            //check if current page is plugin settings page
            if (isset($_GET['page']) && $_GET['page'] == 'terminal-africa') {
                //return
                return;
            }
            //check if merchant id is set
            if (!get_option('terminal_africa_merchant_id')) {
                //save option redirected 
                update_option('terminal_africa_redirected', true);
                //redirect to plugin settings page
                wp_redirect(admin_url('admin.php?page=terminal-africa'));
                exit;
            }
        } catch (\Exception $e) {
            logTerminalError($e);
            //log error 
            error_log($e->getMessage());
        }
    }

    //deactivate_terminal_africa
    public static function deactivate_terminal_africa()
    {
        try {
            self::deactivate();
            //remove plugin from active
            deactivate_plugins(plugin_basename(TERMINAL_AFRICA_PLUGIN_FILE));
            wp_send_json([
                'code' => 200,
                'message' => 'Plugin deactivated successfully'
            ]);
        } catch (\Exception $e) {
            logTerminalError($e);
            wp_send_json([
                'code' => 500,
                'message' => $e->getMessage()
            ]);
        }
    }

    //deactivate
    public static function deactivate()
    {
        try {
            //remove plugin from active
            TerminalLogHandler::terminalDeactionHandler();
            //delete terminal_africa_merchant_id
            delete_option('terminal_africa_merchant_id');
            //delete terminal_africa_settings
            delete_option('terminal_africa_settings');
            //terminal_africa_redirected
            delete_option('terminal_africa_redirected');
            //terminal_africa_countries
            delete_option('terminal_africa_countries');
            //terminal_africa_merchant_address_id
            delete_option('terminal_africa_merchant_address_id');
            //terminal_africa_merchant_address
            delete_option('terminal_africa_merchant_address');
            //terminal_default_packaging_id
            delete_option('terminal_default_packaging_id');
            //disable shipping method
            //get shipping settings
            $settings = get_option('woocommerce_terminal_delivery_settings');
            //update shipping settings
            $settings['enabled'] = 'no';
            update_option('woocommerce_terminal_delivery_settings', $settings);
            //unset session
            //if session is not started start it
            if (session_status() == PHP_SESSION_NONE) {
                session_start();
            }
            //check if data is in session
            if (isset($_SESSION['wallet_balance'])) {
                //unset session
                unset($_SESSION['wallet_balance']);
            }
            //$_SESSION['terminal_africa_cities']
            if (isset($_SESSION['terminal_africa_cities'])) {
                //unset session
                unset($_SESSION['terminal_africa_cities']);
            }
            //$_SESSION['ratedata']
            if (isset($_SESSION['ratedata'])) {
                //unset session
                unset($_SESSION['ratedata']);
            }
            //remove all woocomerce session
            //check if class exist
            if (function_exists('WC')) {
                //check if wc session exist
                if (WC()->session) {
                    //get all session
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
            //delete terminal_africa_states
            global $wpdb;
            //use prepared statement
            $wpdb->prepare("DELETE FROM $wpdb->options WHERE option_name LIKE %s;", ['terminal_africa_states%']);
        } catch (\Exception $e) {
            logTerminalError($e);
            //log error 
            error_log($e->getMessage());
        }
    }

    /**
     * Activate payment gateway
     * 
     */
    public function activate_payment_gateway_init()
    {
        try {
            //terminal_africa_settings
            $terminal_africa_settings = get_option('terminal_africa_settings', []);
            //payment status
            $payment_gateway_status = "inactive";
            //check if isset payment_gateway_status
            if (isset($terminal_africa_settings['others']->user->payment_gateway_status)) {
                $payment_gateway_status = $terminal_africa_settings['others']->user->payment_gateway_status;
            }
            //check if update_user_terminal_payment_gateway is set
            if (get_option('update_user_terminal_payment_gateway')) {
                //return
                return;
            }
            //check if payment_gateway_status is active then update payment_gateway_status to active
            if ($payment_gateway_status == "active") {
                update_option('update_user_terminal_payment_gateway', 'true');
            } else {
                update_option('update_user_terminal_payment_gateway', 'false');
            }
        } catch (\Exception $e) {
            logTerminalError($e);
            //log error 
            error_log("Error activating payment gateway: " . $e->getMessage());
        }
    }

    /**
     * Check user is on checkout
     * 
     */
    public function check_user_on_checkout_init()
    {
        try {
            //check terminal user payment status
            if (terminal_africa_shipping_plugin()::$user_payment_status != "active") {
                //return false if user payment status is not active
                return false;
            }
            //get user payment status woocommerce_terminal_africa_payment_settings
            $woocommerce_terminal_africa_payment_settings = get_option("woocommerce_terminal_africa_payment_settings");
            //set $woo_payment_gateway_status
            $woo_payment_gateway_status = "no";
            //check if isset woocommerce_terminal_africa_payment_settings
            if ($woocommerce_terminal_africa_payment_settings) {
                //check if isset enabled
                if (isset($woocommerce_terminal_africa_payment_settings['enabled'])) {
                    $woo_payment_gateway_status = $woocommerce_terminal_africa_payment_settings['enabled'];
                }
            }

            //check if enabled is no, do nothing
            if ($woo_payment_gateway_status == "no") {
                //return
                return;
            }

            /**
             * Exceptions
             * unset all payment gateway except terminal_africa_payment, cod, cheque, bacs
             * @var array
             */
            $exceptions = [
                'terminal_africa_payment', //Terminal Africa Payment Gateway
                'cod', //Cash on Delivery
                'cheque', //Check Payments
                'bacs' //Direct Bank Transfer
            ];
            //apply filter
            $exceptions = apply_filters('terminal_africa_payment_gateway_exceptions', $exceptions);
            //get all payment gateway
            $payment_gateways = WC()->payment_gateways->payment_gateways;
            //loop through payment gateway
            foreach ($payment_gateways as $key => $gateway) {
                // //check if payment gateway key is not match Terminal
                if (!in_array($gateway->id, $exceptions)) {
                    //update enabled to no
                    $payment_gateways[$key]->enabled = 'no';
                }
            }
            //update payment gateway
            WC()->payment_gateways->payment_gateways = $payment_gateways;
        } catch (\Exception $e) {
            logTerminalError($e);
            //log error 
            error_log("Error checking user on checkout: " . $e->getMessage());
        }
    }
}
