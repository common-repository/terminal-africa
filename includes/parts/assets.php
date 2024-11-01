<?php

namespace TerminalAfrica\Includes\Parts;

//security
defined('ABSPATH') or die('No script kiddies please!');

trait Assets
{
    //enqueue_scripts
    public static function enqueue_scripts()
    {
        //sweet alert styles
        wp_enqueue_style('terminal-africa-sweet-alert-styles', TERMINAL_AFRICA_PLUGIN_ASSETS_URL . '/css/sweetalert2.min.css', array(), TERMINAL_AFRICA_VERSION);
        //sweet alert scripts
        wp_enqueue_script('terminal-africa-sweet-alert-scripts', TERMINAL_AFRICA_PLUGIN_ASSETS_URL . '/js/sweetalert2.min.js', array('jquery'), TERMINAL_AFRICA_VERSION, true);
        //admin.js
        wp_enqueue_script('terminal-africa-admin-scripts', TERMINAL_AFRICA_PLUGIN_ASSETS_URL . '/js/admin.js', array('jquery', 'jquery-blockui'), TERMINAL_AFRICA_VERSION, true);
        //localize scripts
        wp_localize_script('terminal-africa-admin-scripts', 'terminal_africa_admin', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('terminal_africa_nonce'),
            'loader' => TERMINAL_AFRICA_PLUGIN_ASSETS_URL . '/img/loader.gif',
            'plugin_url' => TERMINAL_AFRICA_PLUGIN_ASSETS_URL
        ));

        //check if cuurent url has terminal
        if (strpos($_SERVER['REQUEST_URI'], 'terminal') === false) {
            return;
        }

        //ignore for terminal-africa-hub
        if (strpos($_SERVER['REQUEST_URI'], 'terminal-africa-hub') !== false) {
            return;
        }

        // Get the section and page from the query parameters
        $section = isset($_GET['section']) ? $_GET['section'] : null;
        $page = isset($_GET['page']) ? $_GET['page'] : null;

        // Check if the page is 'wc-settings' and the section is either 'terminal_delivery' or 'terminal_africa_payment'
        if ($page === 'wc-settings' && in_array($section, ['terminal_delivery', 'terminal_africa_payment'])) {
            // Ignore for wc core page
            return;
        }

        //enqueue styles
        //font awesome 
        wp_enqueue_style('terminal-africa-font-awesome-styles', TERMINAL_AFRICA_PLUGIN_ASSETS_URL . '/css/fontawesome.min.css', array(), TERMINAL_AFRICA_VERSION);
        //check if select2 is already loaded
        if (!wp_script_is('select2', 'enqueued')) {
            //select2 styles
            wp_enqueue_style('terminal-africa-select2-styles', TERMINAL_AFRICA_PLUGIN_ASSETS_URL . '/css/select2.css', array(), TERMINAL_AFRICA_VERSION);
        }
        //terminal africa styles
        wp_enqueue_style('terminal-africa-styles', TERMINAL_AFRICA_PLUGIN_ASSETS_URL . '/css/styles.css', array(), TERMINAL_AFRICA_VERSION);
        //izitoast css
        wp_enqueue_style('terminal-africa-izitoast-styles', TERMINAL_AFRICA_PLUGIN_ASSETS_URL . '/css/iziToast.min.css', array(), TERMINAL_AFRICA_VERSION);
        //enqueue scripts
        //font awesome scripts
        wp_enqueue_script('terminal-africa-font-awesome-scripts', TERMINAL_AFRICA_PLUGIN_ASSETS_URL . '/js/fontawesome.min.js', array('jquery'), TERMINAL_AFRICA_VERSION, true);
        //izitoast scripts
        wp_enqueue_script('terminal-africa-izitoast-scripts', TERMINAL_AFRICA_PLUGIN_ASSETS_URL . '/js/iziToast.min.js', array('jquery'), TERMINAL_AFRICA_VERSION, true);
        //terminal africa scripts
        wp_enqueue_script('terminal-africa-scripts', TERMINAL_AFRICA_PLUGIN_ASSETS_URL . '/js/scripts.js', array('jquery', 'select2', 'jquery-blockui'), TERMINAL_AFRICA_VERSION, true);
        //terminal africa scripts
        wp_enqueue_script('terminal-africa-admin-scripts-loggin', TERMINAL_AFRICA_PLUGIN_ASSETS_URL . '/js/admin-scripts-loggin.js', array('jquery', 'select2', 'jquery-blockui'), TERMINAL_AFRICA_VERSION, true);
        //add style wp-components-css
        wp_enqueue_style('wp-components');
        //terminal phone book react module
        wp_enqueue_script('terminal-phonebook-react-module', TERMINAL_AFRICA_PLUGIN_URL . '/build/dashboard.js', array('wp-element', 'wp-components'), TERMINAL_AFRICA_VERSION, true);
        //terminal phonebook css
        wp_enqueue_style('terminal-phonebook-react-module-css', TERMINAL_AFRICA_PLUGIN_URL . '/build/dashboard.css', array(), TERMINAL_AFRICA_VERSION);
        //responsive css
        wp_enqueue_style('terminal-africa-styles-responsive', TERMINAL_AFRICA_PLUGIN_ASSETS_URL . '/css/responsive.css', array(), TERMINAL_AFRICA_VERSION);
        //wallet page url
        $wallet_url = add_query_arg(array('tab' => 'deposit'), admin_url('admin.php?page=terminal-africa-wallet'));
        $packaging_id = get_option('terminal_default_packaging_id');
        //localize scripts
        wp_localize_script('terminal-africa-scripts', 'terminal_africa', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('terminal_africa_nonce'),
            'loader' => TERMINAL_AFRICA_PLUGIN_ASSETS_URL . '/img/loader.gif',
            'plugin_url' => TERMINAL_AFRICA_PLUGIN_ASSETS_URL,
            'getting_started_url' => get_option('terminal_africa_merchant_address_id') ? 'none' : admin_url('admin.php?page=terminal-africa-get-started'),
            'wallet_url' => $wallet_url,
            'wallet_home' => admin_url('admin.php?page=terminal-africa-wallet'),
            'packaging_id' => $packaging_id ? 'yes' : 'no',
            'currency' => get_woocommerce_currency(),
            'tracking_url' => TERMINAL_AFRICA_TRACKING_URL_LIVE,
            'terminal_africal_countries' => get_terminal_countries(),
            'support_active_img' => TERMINAL_AFRICA_PLUGIN_ASSETS_URL . '/img/support-active.svg',
            'support_inactive_img' => TERMINAL_AFRICA_PLUGIN_ASSETS_URL . '/img/support-inactive.svg',
            'shipping_active_img' => TERMINAL_AFRICA_PLUGIN_ASSETS_URL . '/img/card-shipping-icon.svg',
            'shipping_inactive_img' => TERMINAL_AFRICA_PLUGIN_ASSETS_URL . '/img/card-shipping-icon-inactive.svg',
        ));
    }

    //enqueue_scripts
    public static function enqueue_frontend_script()
    {
        if (function_exists('WC')) {
            $cart_item = WC()->cart->get_cart();
            //sweet alert styles
            wp_enqueue_style('terminal-africa-sweet-alert-styles', TERMINAL_AFRICA_PLUGIN_ASSETS_URL . '/css/sweetalert2.min.css', array(), TERMINAL_AFRICA_VERSION);
            //sweet alert scripts
            wp_enqueue_script('terminal-africa-sweet-alert-scripts', TERMINAL_AFRICA_PLUGIN_ASSETS_URL . '/js/sweetalert2.min.js', array('jquery'), TERMINAL_AFRICA_VERSION, true);
            //init add to cart ajax
            wp_enqueue_script('terminal-africa-terminaldata-for-parcel-scripts', TERMINAL_AFRICA_PLUGIN_ASSETS_URL . '/js/terminaldata-parcel.js', array('jquery', 'select2'), TERMINAL_AFRICA_VERSION, true);
            //localize scripts
            wp_localize_script('terminal-africa-terminaldata-for-parcel-scripts', 'terminal_africa_parcel', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('terminal_africa_nonce'),
                'loader' => TERMINAL_AFRICA_PLUGIN_ASSETS_URL . '/img/loader.gif',
                'plugin_url' => TERMINAL_AFRICA_PLUGIN_ASSETS_URL,
                'terminal_check_checkout_product_for_shipping_support' => self::check_checkout_product_for_shipping_support(),
                //check if cart item is empty
                'is_cart_empty' => empty($cart_item) ? 'yes' : 'no',
                'terminal_user_carrier_shipment_timeline' => get_option('terminal_user_carrier_shipment_timeline', 'false'),
                'update_user_carrier_shipment_rate_terminal' => get_option('update_user_carrier_shipment_rate_terminal', 'false'),
                'terminal_autoload_merchant_address' => terminal_autoload_merchant_address()
            ));

            //check if checkiut page
            if (is_checkout()) {
                $enabled = true;
                //check if address is set
                if (!get_option('terminal_africa_merchant_address_id')) {
                    //add notice
                    wc_add_notice(__('Please set your merchant address in the <a href="' . admin_url('admin.php?page=terminal-africa-get-started') . '">settings page</a> to enable Terminal Africa.', 'terminal-africa'), 'error');
                    //$enabled false
                    $enabled = false;
                }
                //check if merchant id is set
                if (!get_option('terminal_africa_merchant_id')) {
                    //add notice
                    wc_add_notice(__('Please set your merchant id in the <a href="' . admin_url('admin.php?page=terminal-africa-get-started') . '">settings page</a> to enable Terminal Africa.', 'terminal-africa'), 'error');
                    //$enabled false
                    $enabled = false;
                }

                //check if hide shipment rate is true
                if (get_option('update_user_carrier_shipment_rate_terminal') == 'false') {
                    //$enabled false
                    $enabled = false;
                }

                //check if enabled is set to no
                $shipping = new \WC_Terminal_Delivery_Shipping_Method;
                //check if shipping method is enabled 
                if ($shipping->enabled == "no") {
                    //$enabled false
                    $enabled = false;
                }
                //check if enabled
                if (!$enabled) {
                    return;
                }
                //haystack
                $haystack = apply_filters('active_plugins', get_option('active_plugins'));
                //use in array check
                if (in_array('checkout-for-woocommerce/checkout-for-woocommerce.php', $haystack)) {
                    //filter checkout wc
                    self::checkoutWCAsset();
                    //enqueue_extension_frontend_script
                    // self::enqueue_extension_frontend_script_checkoutwc();
                } else if (in_array('checkoutwc-lite/checkout-for-woocommerce.php', $haystack)) {
                    //filter checkout wc
                    self::checkoutWCAsset();
                    //enqueue_extension_frontend_script
                    // self::enqueue_extension_frontend_script_checkoutwc();
                } else if (in_array('fluid-checkout/fluid-checkout.php', $haystack)) {
                    //filter fluid checkout wc
                    self::fluidCheckoutWCAsset();
                    //enqueue_extension_frontend_script
                    // self::enqueue_extension_frontend_script_fluidcheckout();
                } else {
                    //wc checkout asset core
                    self::checkoutWCAssetCore();
                    //enqueue_extension_frontend_script
                    self::enqueue_extension_frontend_script();
                }
            }
        }
    }

    /**
     * Checkout Asset for WC Core
     */
    public static function checkoutWCAssetCore()
    {
        //sweet alert styles
        wp_enqueue_style('terminal-africa-sweet-alert-styles', TERMINAL_AFRICA_PLUGIN_ASSETS_URL . '/css/sweetalert2.min.css', array(), TERMINAL_AFRICA_VERSION);
        //checkoutcss
        wp_enqueue_style('terminal-africa-checkout-styles', TERMINAL_AFRICA_PLUGIN_ASSETS_URL . '/css/checkout.css', array(), TERMINAL_AFRICA_VERSION);
        //sweet alert scripts
        wp_enqueue_script('terminal-africa-sweet-alert-scripts', TERMINAL_AFRICA_PLUGIN_ASSETS_URL . '/js/sweetalert2.min.js', array('jquery'), TERMINAL_AFRICA_VERSION, true);
        //checkout
        wp_enqueue_script('terminal-africa-checkout-scripts', TERMINAL_AFRICA_PLUGIN_ASSETS_URL . '/js/checkout.js', array('jquery', 'select2'), TERMINAL_AFRICA_VERSION, true);
        //terminaldata
        wp_enqueue_script('terminal-africa-terminaldata-scripts', TERMINAL_AFRICA_PLUGIN_ASSETS_URL . '/js/terminaldata.js', array('jquery', 'select2'), TERMINAL_AFRICA_VERSION, true);
        //add checkout-for-shipping
        wp_enqueue_script('terminal-africa-checkout-for-shipping-scripts', TERMINAL_AFRICA_PLUGIN_ASSETS_URL . '/js/checkout-for-shipping.js', array('jquery', 'select2'), TERMINAL_AFRICA_VERSION, true);
        //add terminaldata-for-shipping
        wp_enqueue_script('terminal-africa-terminaldata-for-shipping-scripts', TERMINAL_AFRICA_PLUGIN_ASSETS_URL . '/js/terminaldata-for-shipping.js', array('jquery', 'select2'), TERMINAL_AFRICA_VERSION, true);
        //wc checkout block notice
        $wc_checkout_block_notice = '';
        if (current_user_can('administrator')) {
            $wc_checkout_block_notice =  sprintf(
                '<a href="%s" class="button wc-forward">%s</a>',
                esc_url(admin_url('post.php?post=' . wc_get_page_id('checkout') . '&action=edit')),
                esc_html__('Switch to classic checkout', 'text-domain')
            );
        }
        //localize scripts
        wp_localize_script('terminal-africa-checkout-scripts', 'terminal_africa', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('terminal_africa_nonce'),
            'loader' => TERMINAL_AFRICA_PLUGIN_ASSETS_URL . '/img/loader.gif',
            'plugin_url' => TERMINAL_AFRICA_PLUGIN_ASSETS_URL,
            'getting_started_url' => get_option('terminal_africa_merchant_address_id') ? 'none' : admin_url('admin.php?page=terminal-africa-get-started'),
            'currency' => get_woocommerce_currency(),
            'tracking_url' => TERMINAL_AFRICA_TRACKING_URL_LIVE,
            'terminal_africal_countries' => get_terminal_countries(),
            'terminal_check_checkout_product_for_shipping_support' => self::check_checkout_product_for_shipping_support(),
            'terminal_price_markup' => get_option('terminal_custom_price_mark_up', ''),
            'multicurrency' => self::wooMulticurrency(),
            'edit_checkout_page_link' => $wc_checkout_block_notice
        ));
    }

    /**
     * Checkout Asset for CheckoutWC
     */
    public static function checkoutWCAsset()
    {
        //sweet alert styles
        wp_enqueue_style('terminal-africa-sweet-alert-styles', TERMINAL_AFRICA_PLUGIN_ASSETS_URL . '/css/sweetalert2.min.css', array(), TERMINAL_AFRICA_VERSION);
        //checkoutcss
        wp_enqueue_style('terminal-africa-checkout-styles', TERMINAL_AFRICA_PLUGIN_ASSETS_URL . '/css/checkout.css', array(), TERMINAL_AFRICA_VERSION);
        //sweet alert scripts
        wp_enqueue_script('terminal-africa-sweet-alert-scripts', TERMINAL_AFRICA_PLUGIN_ASSETS_URL . '/js/sweetalert2.min.js', array('jquery'), TERMINAL_AFRICA_VERSION, true);
        //checkout
        wp_enqueue_script('terminal-africa-checkoutWC-scripts', TERMINAL_AFRICA_PLUGIN_ASSETS_URL . '/js/checkoutWC.js', array('jquery', 'select2'), TERMINAL_AFRICA_VERSION, true);
        //localize scripts
        wp_localize_script('terminal-africa-checkoutWC-scripts', 'terminal_africa', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('terminal_africa_nonce'),
            'loader' => TERMINAL_AFRICA_PLUGIN_ASSETS_URL . '/img/loader.gif',
            'plugin_url' => TERMINAL_AFRICA_PLUGIN_ASSETS_URL,
            'getting_started_url' => get_option('terminal_africa_merchant_address_id') ? 'none' : admin_url('admin.php?page=terminal-africa-get-started'),
            'currency' => get_woocommerce_currency(),
            'tracking_url' => TERMINAL_AFRICA_TRACKING_URL_LIVE,
            'terminal_africal_countries' => get_terminal_countries(),
            'terminal_check_checkout_product_for_shipping_support' => self::check_checkout_product_for_shipping_support(),
            'terminal_price_markup' => get_option('terminal_custom_price_mark_up', ''),
            'multicurrency' => self::wooMulticurrency()
        ));
    }

    /**
     * fluidCheckoutWCAsset
     */
    public static function fluidCheckoutWCAsset()
    {
        //sweet alert styles
        wp_enqueue_style('terminal-africa-sweet-alert-styles', TERMINAL_AFRICA_PLUGIN_ASSETS_URL . '/css/sweetalert2.min.css', array(), TERMINAL_AFRICA_VERSION);
        //checkoutcss
        wp_enqueue_style('terminal-africa-checkout-styles', TERMINAL_AFRICA_PLUGIN_ASSETS_URL . '/css/checkout.css', array(), TERMINAL_AFRICA_VERSION);
        //fluid checkout css
        wp_enqueue_style('terminal-africa-fluid-checkout-styles', TERMINAL_AFRICA_PLUGIN_ASSETS_URL . '/css/fluidCheckout.css', array(), TERMINAL_AFRICA_VERSION);
        //sweet alert scripts
        wp_enqueue_script('terminal-africa-sweet-alert-scripts', TERMINAL_AFRICA_PLUGIN_ASSETS_URL . '/js/sweetalert2.min.js', array('jquery'), TERMINAL_AFRICA_VERSION, true);
        //checkout
        wp_enqueue_script('terminal-africa-fluid-checkoutWC-scripts', TERMINAL_AFRICA_PLUGIN_ASSETS_URL . '/js/fluidCheckout.js', array('jquery', 'select2'), TERMINAL_AFRICA_VERSION, true);
        //localize scripts
        wp_localize_script('terminal-africa-fluid-checkoutWC-scripts', 'terminal_africa', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('terminal_africa_nonce'),
            'loader' => TERMINAL_AFRICA_PLUGIN_ASSETS_URL . '/img/loader.gif',
            'plugin_url' => TERMINAL_AFRICA_PLUGIN_ASSETS_URL,
            'getting_started_url' => get_option('terminal_africa_merchant_address_id') ? 'none' : admin_url('admin.php?page=terminal-africa-get-started'),
            'currency' => get_woocommerce_currency(),
            'tracking_url' => TERMINAL_AFRICA_TRACKING_URL_LIVE,
            'terminal_africal_countries' => get_terminal_countries(),
            'terminal_check_checkout_product_for_shipping_support' => self::check_checkout_product_for_shipping_support(),
            'terminal_price_markup' => get_option('terminal_custom_price_mark_up', ''),
            'multicurrency' => self::wooMulticurrency()
        ));
    }

    /**
     * enqueue_extension_frontend_script
     */
    public static function enqueue_extension_frontend_script()
    {
        //enqueue extension scripts
        wp_enqueue_script('terminal-africa-extension-native-checkout-scripts', TERMINAL_AFRICA_PLUGIN_ASSETS_URL . '/js/extension/native-checkout.js', array('jquery', 'select2'), TERMINAL_AFRICA_VERSION, true);
    }

    /**
     * enqueue_extension_frontend_script checkoutwc
     */
    public static function enqueue_extension_frontend_script_checkoutwc()
    {
        //enqueue extension scripts
        wp_enqueue_script('terminal-africa-extension-native-checkout-scripts-checkoutwc', TERMINAL_AFRICA_PLUGIN_ASSETS_URL . '/js/extension/checkoutWC.js', array('jquery', 'select2'), TERMINAL_AFRICA_VERSION, true);
    }

    /**
     * enqueue_extension_frontend_script fluidcheckout
     */
    public static function enqueue_extension_frontend_script_fluidcheckout()
    {
        //enqueue extension scripts
        wp_enqueue_script('terminal-africa-extension-native-checkout-scripts-fluidcheckout', TERMINAL_AFRICA_PLUGIN_ASSETS_URL . '/js/extension/fluid-checkout.js', array('jquery', 'select2'), TERMINAL_AFRICA_VERSION, true);
    }

    /**
     * WooCommerce Multi Currency 
     * check if multi currency is available
     * @return array
     */
    public static function wooMulticurrency()
    {
        //check if multi currency class exist 'WOOMULTI_CURRENCY' or 'WOOMULTI_CURRENCY_F'
        if (!class_exists('WOOMULTI_CURRENCY') && !class_exists('WOOMULTI_CURRENCY_F')) {
            // Multi-currency class doesn't exist, return []
            return [];
        }
        //check if multi currency freemium exist
        $data = get_option('woo_multi_currency_params', array());
        //$rate
        $rate = [];
        //loop through the currency
        foreach ($data["currency"] as $key => $value) {
            $var = [$data["currency_rate"][$key], $data["currency_rate_fee"][$key]];
            $rate[$value] = $var;
        }
        return $rate[get_woocommerce_currency()];
    }

    /**
     * Fluid checkout override style
     * @return void
     */
    public static function fluid_checkout_override_style()
    {
?>
        <style>
            /**
            Fluid checkout manipulation
            */
            .fluid-checkout-state {
                clear: both;
                width: 100% !important;
                float: none !important;
            }

            .fluid-checkout-city {
                width: 100% !important;
                float: none !important;
                margin-right: 0px !important;
            }

            @media (min-width: 550px) {
                div.woocommerce form .form-row.form-row-last.fluid-checkout-state {
                    clear: none;
                    float: none !important;
                    width: 100% !important
                }

                div.woocommerce form .form-row.form-row-first.fluid-checkout-city {
                    clear: none;
                    float: none !important;
                    width: 100% !important
                }
            }
        </style>
        <?php
    }

    //header
    public static function header($icon, $title)
    {
        ob_start();
        require TERMINAL_AFRICA_PLUGIN_PATH . '/templates/parts/header.php';
        return ob_get_clean();
    }

    //get template part
    public static function getTerminalPart($template, $args = [])
    {
        ob_start();
        //extract args
        extract($args);
        require TERMINAL_AFRICA_PLUGIN_PATH . "/templates/parts/$template.php";
        return ob_get_clean();
    }

    /**
     * Check if the product in the cart is supported for shipping
     */
    public static function check_checkout_product_for_shipping_support()
    {
        //get cart items
        $cartItems = WC()->cart->get_cart();
        //check if cart is empty
        if (empty($cartItems)) {
            //return false
            return "false";
        }
        //status
        $status_array = [];
        //loop through cart items
        foreach ($cartItems as $cartItem) {
            //get product id
            $productId = $cartItem['product_id'];
            //get product
            $product = wc_get_product($productId);
            //check if product is virtual or downloadable
            if ($product->is_virtual() || $product->is_downloadable()) {
                //append
                $status_array[] = "true"; //enable for all
            } else {
                //append
                $status_array[] = "true";
            }
        }
        //check if all is false
        if (count(array_unique($status_array)) === 1 && end($status_array) === 'false') {
            //return false
            return "false";
        } else {
            //return true
            return "true";
        }
    }

    /**
     * formatPhoneNumber
     * @param string $phone
     * @param string $countryCode
     * 
     * @return string
     */
    public static function formatPhoneNumber($phone, $countryCode = 'NG')
    {
        //find country where isoCode
        $tm_countries = get_terminal_countries();
        //check if country code is not empty
        $tm_country = array_filter($tm_countries, function ($country) use ($countryCode) {
            return $country->isoCode === $countryCode;
        });
        //get first element
        $tm_country = reset($tm_country);

        // Phone code
        $phonecode = $tm_country->phonecode;

        // Check if phonecode does not include +
        if (strpos($phonecode, "+") === false) {
            $phonecode = "+" . $phonecode;
        }

        // Remove + and space and special characters from phone
        // $phone = preg_replace('/[-+() ]/', '', $phone);

        if ($phone) {
            if (strpos($phone, "+") === false) {
                // Append to phone
                $phone = $phonecode . $phone;
            }
        } else {
            $phone = "";
        }

        return $phone;
    }

    //wp head checkout
    public static function wp_head_checkout()
    {
        //domain exclude
        $domainExclude = [
            'miamiahair.com'
        ];
        //domain exceptions
        $domainExceptions = [
            'www.milipays.com'
        ];
        if (function_exists('WC')) {
            if (is_checkout()) {
        ?>
                <style>
                    .select2-container {
                        width: 100% !important;
                    }

                    #billing_phone_field .select2.select2-container.select2-container--default {
                        height: 100% !important;
                    }

                    #billing_phone_field .select2-container--default .select2-selection--single {
                        height: <?php echo in_array($_SERVER['HTTP_HOST'], $domainExceptions) ? '67%' : '100%'; ?> !important;
                    }

                    <?php
                    //check if current domain is in domain exclude
                    if (!in_array($_SERVER['HTTP_HOST'], $domainExclude)) :
                    ?>#billing_phone_field .select2-container--default .select2-selection--single .select2-selection__rendered {
                        line-height: 40px;
                    }

                    <?php else : ?>#billing_phone_field .select2-container--default .select2-selection--single .select2-selection__rendered {
                        line-height: normal;
                    }

                    <?php endif; ?>#billing_phone_field .select2-container--default .select2-selection--single .select2-selection__arrow {
                        top: <?php echo in_array($_SERVER['HTTP_HOST'], $domainExceptions) ? '20px' : '7px'; ?>;
                    }

                    .swal2-image {
                        height: 80px;
                        max-height: 80px;
                    }

                    <?php
                    //check if current domain is in domain exclude
                    if (in_array($_SERVER['HTTP_HOST'], $domainExceptions)) :
                    ?>ul#shipping_method.woocommerce-shipping-methods li {
                        width: min-content;
                        float: right;
                        display: block;
                    }

                    <?php endif; ?>
                </style>
                <?php
                $checkout = WC()->checkout();
                //get checkout billing state
                $billing_state = $checkout->get_value('billing_state') ?: '';
                //get checkout billing city
                $billing_city = $checkout->get_value('billing_city') ?: '';
                $billing_postcode = $checkout->get_value('billing_postcode') ?: '';
                //shipping post code
                $shipping_postcode = $checkout->get_value('shipping_postcode') ?: '';
                //terminal_shipping_state
                $terminal_shipping_state = $checkout->get_value('shipping_state') ?: '';
                //terminal_shipping_city
                $terminal_shipping_city = $checkout->get_value('shipping_city') ?: '';
                //check if , is in billing city
                if (strpos($billing_city, ',') !== false) {
                    $billing_city = explode(',', $billing_city);
                    $billing_city = $billing_city[0];
                }
                ?>
                <script>
                    var terminal_billing_state = '<?php echo esc_html($billing_state); ?>';
                    var terminal_billing_city = '<?php echo esc_html($billing_city); ?>';
                    var terminal_shipping_state = '<?php echo esc_html($terminal_shipping_state); ?>';
                    var terminal_shipping_city = '<?php echo esc_html($terminal_shipping_city); ?>';
                    window.terminal_billing_postcode = '<?php echo esc_html($billing_postcode); ?>';
                    var terminal_shipping_postcode = '<?php echo esc_html($shipping_postcode); ?>';
                </script>
<?php

            }
        }
    }
}
