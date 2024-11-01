<?php
//security
defined('ABSPATH') or die('No script kiddies please!');
//get site name
$site_name = get_bloginfo('name');
?>
<div class="t-container">
    <div class="t-auth-bg" style="background: url('<?php echo esc_url(TERMINAL_AFRICA_PLUGIN_ASSETS_URL . '/img/bg-svg.svg') ?>');">
        <div class="t-content-auth">
            <div class="t-form-area">
                <img class="main-logo-image" src="<?php echo esc_url(TERMINAL_AFRICA_PLUGIN_ASSETS_URL . '/img/logo-full.png') ?>" alt="">
                <p class="t-auth-header">
                    Authenticate Your Account
                </p>
                <p class="t-auth-text">
                    By clicking the Get Started button, you authorize access to your Terminal Africa account.
                </p>
                <div class="t-form-group">
                    <button type="button" data-site-name="<?php echo esc_attr($site_name); ?>" data-domain="<?php echo esc_url(TERMINAL_AFRICA_PLUGIN_WP_ADMIN_URL); ?>" data-url="<?php echo esc_url(TERMINAL_AFRICA_SINGLE_AUTH_URL); ?>" class="t-single-auth">Get Started</button>
                </div>
            </div>
        </div>
    </div>
</div>