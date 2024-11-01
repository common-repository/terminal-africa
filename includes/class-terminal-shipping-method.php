<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

/**
 * Terminal Delivery Shipping Method Class
 *
 * Provides real-time shipping rates from Terminal delivery and handle order requests
 *
 * @since 1.0
 * 
 * @extends \WC_Shipping_Method
 */
class WC_Terminal_Delivery_Shipping_Method extends WC_Shipping_Method
{
    /**
     * Constructor.
     *
     * @since 1.0.0
     */
    public function __construct($instance_id = 0)
    {
        $this->id                 = 'terminal_delivery';
        $this->instance_id           = absint($instance_id);
        $this->method_title       = __('Terminal Delivery');
        $this->method_description = __('Get your parcels delivered better, cheaper and quicker via Terminal Delivery');

        $this->supports  = array(
            'settings',
            'shipping-zones',
            // 'instance-settings',
            // 'instance-settings-modal',
        );

        $this->init();

        $this->title = 'Terminal Delivery';

        $this->enabled = $this->get_option('enabled');
    }

    /**
     * Init.
     *
     * Initialize Terminal delivery shipping method.
     *
     * @since 1.0.0
     */
    public function init()
    {
        $this->init_form_fields();
        $this->init_settings();

        // Save settings in admin if you have any defined
        add_action('woocommerce_update_options_shipping_' . $this->id, array($this, 'process_admin_options'));
    }

    /**
     * Init fields.
     *
     * Add fields to the Terminal delivery settings page.
     *
     * @since 1.0.0
     */
    public function init_form_fields()
    {
        $this->form_fields = array(
            'enabled' => array(
                'title'     => __('Enable/Disable'),
                'type'         => 'checkbox',
                'label'     => __('Enable this shipping method'),
                'default'     => 'no',
            ),
            //add link to manage shipping
            'title' => array(
                'title' => __('Manage this page from the admin panel'),
                'type' => 'title',
                'description' => sprintf(
                    __('You can manage this page from the <a href="%s">Terminal admin panel</a>.'),
                    admin_url('admin.php?page=terminal-africa')
                ),
            ),
        );
    }

    function is_available($package)
    {
        if ($this->enabled === "no")
            return false;
        return apply_filters('woocommerce_shipping_' . $this->id . '_is_available', true);
    }


    /**
     * Calculate shipping by sending destination/items to Shipwire and parsing returned rates
     *
     * @since 1.0
     * @param array $package
     */
    public function calculate_shipping($package = array())
    {
        if ($this->get_option('enabled') == 'no') {
            return;
        }

        //check if session is started
        if (session_status() == PHP_SESSION_NONE) {
            @session_start();
        }

        //check if session exists
        $terminal_africa_carriername = sanitize_text_field(WC()->session->get('terminal_africa_carriername'));
        $terminal_africa_amount = sanitize_text_field(WC()->session->get('terminal_africa_amount'));
        $terminal_africa_duration = sanitize_text_field(WC()->session->get('terminal_africa_duration'));
        $guest_email = sanitize_text_field(WC()->session->get('terminal_africa_guest_email'));
        $terminal_africa_rateid = sanitize_text_field(WC()->session->get('terminal_africa_rateid'));
        $terminal_africa_pickuptime = sanitize_text_field(WC()->session->get('terminal_africa_pickuptime'));
        $terminal_africa_carrierlogo = sanitize_text_field(WC()->session->get('terminal_africa_carrierlogo'));
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
        //check if empty $terminal_africa_carriername
        if (empty($terminal_africa_carriername)) {
            //add rate
            $this->add_rate(array(
                'id'        => $this->id . $this->instance_id,
                'label'     => "Terminal",
                'cost'      => 0,
                'meta_data' => [],
            ));
            return;
        }
        //if exist
        if ($terminal_africa_carriername && $terminal_africa_amount && $terminal_africa_duration && $terminal_africa_rateid) {
            //check if $terminal_africa_amount is not string
            if (is_string($terminal_africa_amount)) {
                $terminal_africa_amount = floatval($terminal_africa_amount);
            }
            //add rate
            $this->add_rate(array(
                'id'        => $this->id . $this->instance_id,
                'label'     => $terminal_africa_carriername . " - " . $terminal_africa_duration,
                'cost'      => $terminal_africa_amount,
                'meta_data' => [
                    'duration' => $terminal_africa_duration,
                    'carrier' => $terminal_africa_carriername,
                    'amount' => $terminal_africa_amount,
                    'rate_id' => $terminal_africa_rateid,
                    'pickup_time' => $terminal_africa_pickuptime,
                    'carrier_logo' => $terminal_africa_carrierlogo,
                    'merchant_id' => $terminal_africa_merchant_id,
                    'mode' => $mode,
                ],
            ));
            return;
        }
        //add rate
        $this->add_rate(array(
            'id'        => $this->id . $this->instance_id,
            'label'     => "Terminal",
            'cost'      => 0,
            'meta_data' => [],
        ));
    }
}
