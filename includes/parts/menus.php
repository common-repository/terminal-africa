<?php

namespace TerminalAfrica\Includes\Parts;

//security
defined('ABSPATH') or die('No script kiddies please!');

trait Menus
{
    //add_settings_page
    public static function add_settings_page()
    {
        //add menu page
        add_menu_page(
            'Terminal Africa Shipping',
            'Terminal Africa',
            'manage_options',
            'terminal-africa',
            array(self::class, 'settings_page'),
            //icon
            TERMINAL_AFRICA_PLUGIN_URL . '/assets/img/logo.svg',
            5
        );

        //sub menu
        add_submenu_page(
            'terminal-africa',
            'Get Started - Terminal Africa Shipping',
            'Get Started',
            'manage_options',
            'terminal-africa-get-started',
            array(self::class, 'settings_page')
        );

        //wallet
        add_submenu_page(
            'terminal-africa',
            'Wallet - Terminal Africa Shipping',
            'Wallet',
            'manage_options',
            'terminal-africa-wallet',
            array(self::class, 'settings_page')
        );

        //address
        add_submenu_page(
            'terminal-africa',
            'Address - Terminal Africa Shipping',
            'Address',
            'manage_options',
            'terminal-africa-address',
            array(self::class, 'settings_page')
        );

        //Carriers
        add_submenu_page(
            'terminal-africa',
            'Carriers - Terminal Africa Shipping',
            'Carriers',
            'manage_options',
            'terminal-africa-carriers',
            array(self::class, 'settings_page')
        );

        //settings
        add_submenu_page(
            'terminal-africa',
            'Settings - Terminal Africa Shipping',
            'Settings',
            'manage_options',
            'terminal-africa-settings',
            array(self::class, 'settings_page')
        );

        //update submenu title for the first item
        global $submenu;
        if (isset($submenu['terminal-africa'])) {
            $submenu['terminal-africa'][0][0] = 'Shipments';

            //move terminal-africa-get-started to first index of menu
            $get_started = $submenu['terminal-africa'][1];
            //unset terminal-africa-get-started
            unset($submenu['terminal-africa'][1]);
            //add terminal-africa-get-started to first index of menu
            array_unshift($submenu['terminal-africa'], $get_started);
        }
    }

    //custom header css
    public static function cssHeaderCustom()
    {
?>
        <style>
            #wpcontent {
                padding-left: 0px !important;
            }
        </style>
<?php
    }

    //settings_page
    public static function settings_page()
    {
        //get current page slug
        $current_page = isset($_GET['page']) ? sanitize_text_field($_GET['page']) : 'terminal-africa';
        //add css
        self::cssHeaderCustom();
        //switch pages
        switch ($current_page) {
            case 'terminal-africa':
                $page = 'shipping';
                //check if page match edit and id
                if (isset($_GET['action']) && $_GET['action'] == "edit" && isset($_GET['id']) && $_GET['id'] != "" && isset($_GET['order_id']) && !empty($_GET['order_id']) && isset($_GET['rate_id']) && !empty($_GET['rate_id']) && isset($_GET['nonce']) && wp_verify_nonce($_GET['nonce'], 'terminal_africa_edit_shipment')) {
                    $page = 'manage-shipping';
                }
                break;
            case 'terminal-africa-get-started':
                $page = 'get-started';
                break;
            case 'terminal-africa-wallet':
                $page = 'wallet';
                break;
            case 'terminal-africa-address':
                $page = 'address';
                break;
            case 'terminal-africa-carriers':
                $page = 'carriers';
                break;
            case 'terminal-africa-settings':
                $page = 'settings';
                break;
            default:
                $page = 'dashboard';
                break;
        }
        //check if address is set
        if (!get_option('terminal_africa_merchant_address_id')) {
            //load address
            $page = 'address';
        }
        //check if merchant id is set
        if (!get_option('terminal_africa_merchant_id')) {
            //load auth
            $page = 'auth';
        }
        //require files
        require_once TERMINAL_AFRICA_PLUGIN_DIR . '/templates/' . $page . '.php';
    }

    //add_settings_link
    public static function add_settings_link($links)
    {
        $mylinks = array(
            '<a href="' . admin_url('admin.php?page=terminal-africa') . '">Settings</a>',
        );
        return array_merge($links, $mylinks);
    }
}
