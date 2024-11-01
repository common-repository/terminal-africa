<?php

/**
 * Plugin Name: Terminal Africa
 * Plugin URI:  https://wordpress.org/plugins/terminal-africa/
 * Author:      Terminal
 * Author URI:  http://www.terminal.africa
 * Description: Terminal Africa Shipping Method Plugin for WooCommerce
 * Version:     1.13.7
 * License:     GPL-2.0+
 * License URL: http://www.gnu.org/licenses/gpl-2.0.txt
 * text-domain: terminal-africa
 * Requires Plugins: woocommerce
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die('Direct access is not allowed');
}

// Define constants.
define('TERMINAL_AFRICA_VERSION', time());
define('TERMINAL_AFRICA_PLUGIN_FILE', __FILE__);
define('TERMINAL_AFRICA_PLUGIN_BASENAME', plugin_basename(__FILE__));
define('TERMINAL_AFRICA_PLUGIN_DIR', __DIR__);
//TERMINAL_AFRICA_PLUGIN_PATH
define('TERMINAL_AFRICA_PLUGIN_PATH', untrailingslashit(plugin_dir_path(__FILE__)));
define('TERMINAL_AFRICA_PLUGIN_URL', untrailingslashit(plugins_url(basename(plugin_dir_path(__FILE__)), basename(__FILE__))));
define('TERMINAL_AFRICA_PLUGIN_ASSETS_URL', TERMINAL_AFRICA_PLUGIN_URL . '/assets');
//api endpoint
define('TERMINAL_AFRICA_API_ENDPOINT', 'https://api.terminal.africa/v1/');
define('TERMINAL_AFRICA_TEST_API_ENDPOINT', 'https://sandbox.terminal.africa/v1/');
//slug
define('TERMINAL_AFRICA_TEXT_DOMAIN', 'terminal-africa');
//tracking url
define('TERMINAL_AFRICA_TRACKING_URL_LIVE', 'https://app.terminal.africa/shipments/track/');
//single auth url
define('TERMINAL_AFRICA_SINGLE_AUTH_URL', 'https://dashboard.terminal.africa/auth/authorize/wordpress/');
//plugin wp admin url
define('TERMINAL_AFRICA_PLUGIN_WP_ADMIN_URL', admin_url('admin.php?page=terminal-africa'));
//Terminal Africa Payment API
define('TERMINAL_AFRICA_PAYMENT_API_ENDPOINT', 'https://pay.terminal.africa/v1/payments/');
define('TERMINAL_AFRICA_PAYMENT_TEST_API_ENDPOINT', 'https://sandboxpay.terminal.africa/v1/payments/');

/**
 * WooCommerce Terminal Delivery Loader.
 * 
 */
class WC_Terminal_Delivery_Loader
{
    /** minimum PHP version required by this plugin */
    const MINIMUM_PHP_VERSION = '5.4.0';

    /** minimum WordPress version required by this plugin */
    const MINIMUM_WP_VERSION = '6.2';

    /** minimum WooCommerce version required by this plugin */
    const MINIMUM_WC_VERSION = '4.0';

    /** the plugin name, for displaying notices */
    const PLUGIN_NAME = 'Terminal Delivery for WooCommerce';

    /** the plugin slug, for action links */
    const PLUGIN_SLUG = 'terminal-africa';

    /** @var array the admin notices to add */
    private $notices = array();

    /** @var \WC_Terminal_Delivery_Loader single instance of this class */
    private static $instance;

    private static $active_plugins;

    /**
     * Sets up the loader.
     *
     */
    protected function __construct()
    {
        self::$active_plugins = (array) get_option('active_plugins', array());

        if (is_multisite()) {
            self::$active_plugins = array_merge(self::$active_plugins, get_site_option('active_sitewide_plugins', array()));
        }

        if (!$this->wc_active_check()) {
            //add install wc notice
            add_action('admin_notices', array($this, 'terminal_install_wc_notice'));
            return;
        }

        register_activation_hook(__FILE__, array($this, 'activation_check'));

        add_action('admin_init', array($this, 'check_environment'));
        add_action('admin_init', array($this, 'add_plugin_notices'));
        add_action('admin_notices', array($this, 'admin_notices'), 15);

        // if the environment check fails, initialize the plugin
        if ($this->is_environment_compatible()) {
            add_action('plugins_loaded', array($this, 'init_plugin'));
        }
    }

    /**
     * terminal_install_wc_notice
     */
    public function terminal_install_wc_notice()
    {
        $class = 'notice notice-error';
        $message = __('Terminal Delivery requires WooCommerce to be installed and activated.', 'terminal-africa');
        $link = admin_url('plugin-install.php?s=woocommerce&tab=search&type=term');

        printf('<div class="%1$s"><p>%2$s <a href="%3$s">Install</a></p></div>', esc_attr($class), esc_html($message), esc_url($link));
    }

    /**
     * Initializes the plugin.
     *
     */
    public function init_plugin()
    {
        if (!$this->plugins_compatible()) {
            return;
        }
        //load vendor
        require_once TERMINAL_AFRICA_PLUGIN_PATH . '/vendor/autoload.php';

        // load the main plugin class
        require(plugin_dir_path(__FILE__) . 'includes' . DIRECTORY_SEPARATOR . 'class-terminal-delivery.php');

        wc_Terminal_delivery();

        $shipping = new WC_Terminal_Delivery_Shipping_Method;
        //check if shipping method is enabled 
        if ($shipping->enabled == "no") {
            add_action('admin_notices', array($this, 'terminal_delivery_disabled_notice'));
        }

        // Include the main Terminal Africa class.
        if (!class_exists('TerminalAfricaShippingPlugin')) {
            include_once __DIR__ . '/includes/parts/menus.php';
            include_once __DIR__ . '/includes/parts/ajax.php';
            include_once __DIR__ . '/includes/parts/assets.php';
            include_once __DIR__ . '/includes/parts/activation.php';
            include_once __DIR__ . '/includes/parts/api.php';
            include_once __DIR__ . '/includes/parts/shipping-address.php';
            include_once __DIR__ . '/includes/class-terminal-africa.php';
            include_once __DIR__ . '/includes/Helpers/helper.php';

            //include terminal log handler
            require_once TERMINAL_AFRICA_PLUGIN_PATH . '/includes/terminalLogHandler.php';

            // Register hooks that are fired when the plugin is activated or deactivated.
            // register_activation_hook(__FILE__, [TerminalLogHandler::class, 'terminalActivatorHandler']);
            register_deactivation_hook(__FILE__, [TerminalLogHandler::class, 'terminalDeactionHandler']);
            //upgrader_process_complete
            add_action('upgrader_process_complete', [TerminalLogHandler::class, 'terminalUpdateHandler'], 10, 2);
        }
        //add settings page
        add_filter('plugin_action_links_' . plugin_basename(__FILE__), array('TerminalAfricaShippingPlugin', 'add_settings_link'));
    }

    public function wc_active_check()
    {
        return in_array('woocommerce/woocommerce.php', self::$active_plugins) || array_key_exists('woocommerce/woocommerce.php', self::$active_plugins);
    }

    //terminal_delivery_disabled_notice
    public function terminal_delivery_disabled_notice()
    {
        $class = 'notice notice-error';
        $message = __('Terminal Delivery is disabled. Please enable it to use the plugin.', 'terminal-africa');
        $link = admin_url('admin.php?page=wc-settings&tab=shipping&section=terminal_delivery');

        printf('<div class="%1$s"><p>%2$s <a href="%3$s">Enable</a></p></div>', esc_attr($class), esc_html($message), esc_url($link));
    }

    /**
     * Checks the server environment and other factors and deactivates plugins as necessary.
     *
     * Based on http://wptavern.com/how-to-prevent-wordpress-plugins-from-activating-on-sites-with-incompatible-hosting-environments
     *
     */
    public function activation_check()
    {
        if (!$this->is_environment_compatible()) {

            $this->deactivate_plugin();

            wp_die(self::PLUGIN_NAME . ' could not be activated. ' . $this->get_environment_message());
        }
    }

    /**
     * Checks the environment on loading WordPress, just in case the environment changes after activation.
     *
     */
    public function check_environment()
    {
        if (!$this->is_environment_compatible() && is_plugin_active(plugin_basename(__FILE__))) {

            $this->deactivate_plugin();

            $this->add_admin_notice('bad_environment', 'error', self::PLUGIN_NAME . ' has been deactivated. ' . $this->get_environment_message());
        }
    }

    /**
     * Adds notices for out-of-date WordPress and/or WooCommerce versions.
     *
     */
    public function add_plugin_notices()
    {
        if (!$this->is_wp_compatible()) {

            $this->add_admin_notice('update_wordpress', 'error', sprintf(
                '%s requires WordPress version %s or higher. Please %supdate WordPress &raquo;%s',
                '<strong>' . self::PLUGIN_NAME . '</strong>',
                self::MINIMUM_WP_VERSION,
                '<a href="' . esc_url(admin_url('update-core.php')) . '">',
                '</a>'
            ));
        }

        if (!$this->is_wc_compatible()) {
            $this->add_admin_notice('update_woocommerce', 'error', sprintf(
                '%1$s requires WooCommerce version %2$s or higher. Please %3$supdate WooCommerce%4$s to the latest version, or %5$sdownload the minimum required version &raquo;%6$s',
                '<strong>' . self::PLUGIN_NAME . '</strong>',
                self::MINIMUM_WC_VERSION,
                '<a href="' . esc_url(admin_url('update-core.php')) . '">',
                '</a>',
                '<a href="' . esc_url('https://downloads.wordpress.org/plugin/woocommerce.' . self::MINIMUM_WC_VERSION . '.zip') . '">',
                '</a>'
            ));
        }
    }

    /**
     * Determines if the required plugins are compatible.
     *
     * @return bool
     */
    protected function plugins_compatible()
    {
        return $this->is_wp_compatible() && $this->is_wc_compatible();
    }

    /**
     * Determines if the WordPress compatible.
     *
     * @return bool
     */
    protected function is_wp_compatible()
    {
        return version_compare(get_bloginfo('version'), self::MINIMUM_WP_VERSION, '>=');
    }

    /**
     * Determines if the WooCommerce compatible.
     *
     * @return bool
     */
    protected function is_wc_compatible()
    {
        return defined('WC_VERSION') && version_compare(WC_VERSION, self::MINIMUM_WC_VERSION, '>=');
    }

    /**
     * Deactivates the plugin.
     *
     */
    protected function deactivate_plugin()
    {
        deactivate_plugins(plugin_basename(__FILE__));

        if (isset($_GET['activate'])) {
            unset($_GET['activate']);
        }
    }

    /**
     * Adds an admin notice to be displayed.
     *
     * @param string $slug the slug for the notice
     * @param string $class the css class for the notice
     * @param string $message the notice message
     */
    public function add_admin_notice($slug, $class, $message)
    {
        $this->notices[$slug] = array(
            'class'   => $class,
            'message' => $message
        );
    }

    /**
     * Displays any admin notices set.
     *
     * @see \WC_TerminalDelivery_Loader_Loader::add_admin_notice()
     *
     */
    public function admin_notices()
    {
        foreach ($this->notices as $notice_key => $notice) :

?>
            <div class="<?php echo esc_attr($notice['class']); ?>">
                <p><?php echo wp_kses($notice['message'], array('a' => array('href' => array()))); ?></p>
            </div>
<?php

        endforeach;
    }

    /**
     * Determines if the server environment is compatible with this plugin.
     *
     * Override this method to add checks for more than just the PHP version.
     *
     * @return bool
     */
    protected function is_environment_compatible()
    {
        return version_compare(PHP_VERSION, self::MINIMUM_PHP_VERSION, '>=');
    }

    /**
     * Gets the message for display when the environment is incompatible with this plugin.
     *
     * @return string
     */
    protected function get_environment_message()
    {
        return sprintf('The minimum PHP version required for this plugin is %1$s. You are running %2$s.', self::MINIMUM_PHP_VERSION, PHP_VERSION);;
    }

    /**
     * Cloning instances is forbidden due to singleton pattern.
     *
     */
    public function __clone()
    {
        _doing_it_wrong(__FUNCTION__, sprintf('You cannot clone instances of %s.', get_class($this)), '1.0.0');
    }

    /**
     * Unserializing instances is forbidden due to singleton pattern.
     *
     */
    public function __wakeup()
    {
        _doing_it_wrong(__FUNCTION__, sprintf('You cannot unserialize instances of %s.', get_class($this)), '1.0.0');
    }

    /**
     * Gets the main loader instance.
     *
     * Ensures only one instance can be loaded.
     *
     *
     * @return \WC_Terminal_Delivery_Loader
     */
    public static function instance()
    {
        if (self::$instance === null) {
            self::$instance = new self;
        }

        return self::$instance;
    }
}

// fire it up!
WC_Terminal_Delivery_Loader::instance();
