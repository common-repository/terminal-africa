<?php
//security
defined('ABSPATH') or die('No script kiddies please!');
?>
<div class="t-container">
    <div class="t-content-auth">
        <img class="main-logo-image" src="<?php echo esc_url(TERMINAL_AFRICA_PLUGIN_ASSETS_URL . '/img/logo-full.png') ?>" alt="">
        <div class="t-form-area">
            <p class="t-auth-text">
                Terminal Delivery is disabled. Please enable it to use the plugin.
            </p>
            <div class="t-form-group">
                <button type="button" id="enableTerminal">Enable Terminal Shipping Method</button>
            </div>
        </div>
    </div>
</div>