<?php
//security
defined('ABSPATH') or die('No script kiddies please!');
//get terminal countries
$countries = get_terminal_countries();
//get the saved currency
$saved_currency = get_option("terminal_default_currency_code", ['isoCode' => 'NG', 'currency_code' => 'NGN']);
//terminal_custom_price_mark_up
$terminal_custom_price_mark_up = get_option('terminal_custom_price_mark_up', '');
//create link 'admin.php?page=wc-settings&tab=shipping'
$settings_link = admin_url('admin.php?page=wc-settings&tab=shipping');
//terminal_africa_settings
$terminal_africa_settings = get_option('terminal_africa_settings', []);
//payment status
$payment_gateway_status = "inactive";
//check if isset payment_gateway_status
if (isset($terminal_africa_settings['others']->user->payment_gateway_status)) {
    $payment_gateway_status = $terminal_africa_settings['others']->user->payment_gateway_status;
}
//payment_signup_link
$payment_signup_link = admin_url('admin.php?page=terminal-africa-get-started');
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
?>
<div class="t-container">
    <?php terminal_header("fas fa-cog", "Settings"); ?>
    <div class="t-body" style="padding-top: 10px;">
        <div class="t-row">
            <div class="t-col-12">
                <div class="t-address-card t-settings-page">
                    <div class="t-flex">
                        <div class="t-carriers-title-tag">
                            <h3 class="t-address-card-header-text t-pl-0">Terminal Africa Settings</h3>
                        </div>
                        <div class="t-merchant-id">
                            <h3 class="t-address-card-header-text t-pl-0">
                                Merchant ID:
                                <span class="t-merchant-id-text"><?php echo esc_html(get_option('terminal_africa_merchant_id')); ?></span>
                            </h3>
                        </div>
                    </div>

                    <div class="t-flex t-settings-page-card t-mb-4">
                        <div class="t-settings-first">
                            <p class="t-settings-page-card-title">
                                Enable Terminal Payment Gateway
                            </p>
                            <p class="t-settings-page-card-description">
                                When enabled, your customers can pay with Terminal Africa Payment Gateway.
                            </p>
                        </div>
                        <div>
                            <div class="t-carrier-embed w-embed">
                                <?php
                                //check payment status
                                switch ($payment_gateway_status) {
                                    case 'inactive':
                                ?>
                                        <a href="<?php echo esc_url($payment_signup_link); ?>">
                                            Learn More
                                        </a>
                                    <?php
                                        break;

                                    case 'active':
                                    ?>
                                        <label class="t-switch t-carrier-switch">
                                            <input type="checkbox" class="t-carrier-checkbox" name="enable_terminal_payment_gateway" id="enable_terminal_payment_gateway" <?php echo $woo_payment_gateway_status == 'yes' ? 'checked' : ''; ?>>
                                            <span class="t-slider round"></span>
                                        </label>
                                <?php
                                        break;

                                    case 'disabled':
                                        echo "<span class='t-settings-badge t-settings-badge-disabled'>disabled</span>";
                                        break;

                                    case 'pending':
                                        echo "<span class='t-settings-badge t-settings-badge-pending'>pending</span>";
                                        break;
                                }
                                ?>
                            </div>
                        </div>
                    </div>

                    <div class="t-flex t-settings-page-card t-mb-4">
                        <div class="t-settings-first">
                            <p class="t-settings-page-card-title">
                                Show Rates
                            </p>
                            <p class="t-settings-page-card-description">
                                When enabled, your customer can see your rates.
                            </p>
                        </div>
                        <div>
                            <div class="t-carrier-embed w-embed">
                                <label class="t-switch t-carrier-switch">
                                    <input type="checkbox" class="t-carrier-checkbox" name="Hide_Shipment_Rate" id="Hide_Shipment_Rate" <?php echo get_option('update_user_carrier_shipment_rate_terminal') == 'true' ? 'checked' : ''; ?>>
                                    <span class="t-slider round"></span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="t-settings-page-card-notice-parent">
                        <div class="t-flex t-settings-page-card t-settings-page-card-notice t-mb-4">
                            <div class="t-settings-first">
                                <p class="t-settings-page-card-title">
                                    Notice!
                                </p>
                                <p class="t-settings-page-card-description">
                                    Terminal Africa rates are currenctly disabled, please enable Terminal Africa rates or set up flat rates as a backup.
                                </p>
                            </div>
                            <div style="min-width: 208px;">
                                <p>
                                    <a href="javascript:;" class="t-notice-section t-notice-section-action" data-link="<?php echo esc_attr($settings_link); ?>">
                                        SET UP FLAT RATES
                                    </a>
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="t-flex t-settings-page-card t-mb-4">
                        <div class="t-settings-first">
                            <p class="t-settings-page-card-title">
                                Show Delivery Timelines
                            </p>
                            <p class="t-settings-page-card-description">
                                When enabled, your rates will show pickup and delivery timelines.
                            </p>
                        </div>
                        <div>
                            <div class="t-carrier-embed w-embed">
                                <label class="t-switch t-carrier-switch">
                                    <input type="checkbox" class="t-carrier-checkbox" name="Hide_Shipment_Timeline" id="Hide_Shipment_Timeline" <?php echo get_option('terminal_user_carrier_shipment_timeline') == 'true' ? 'checked' : ''; ?>>
                                    <span class="t-slider round"></span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="t-flex t-settings-page-card t-mb-4">
                        <div class="t-settings-first">
                            <p class="t-settings-page-card-title">
                                Enable Insurance
                            </p>
                            <p class="t-settings-page-card-description">
                                When enabled, all your rates will include insurance fees.
                            </p>
                        </div>
                        <div>
                            <div class="t-carrier-embed w-embed">
                                <label class="t-switch t-carrier-switch">
                                    <input type="checkbox" class="t-carrier-checkbox" name="Enable_Terminal_Insurance" id="Enable_Terminal_Insurance" <?php echo get_option('update_user_carrier_shipment_insurance_terminal') == 'true' ? 'checked' : ''; ?>>
                                    <span class="t-slider round"></span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="t-flex t-settings-page-card t-mb-4">
                        <div class="t-settings-first">
                            <p class="t-settings-page-card-title">
                                Set Default Currency
                            </p>
                            <p class="t-settings-page-card-description">
                                Set the default currency code for the checkout page.
                            </p>
                        </div>
                        <div style="margin-right: 30px;width: 170px;">
                            <select class="t-form-control t-terminal-country-default-settings" name="terminal_default_currency_code" id="">
                                <option value="">Country</option>
                                <?php foreach ($countries as $key => $country) : ?>
                                    <option value="<?php echo esc_html($country->currency); ?>" data-isocode="<?php echo esc_html($country->isoCode); ?>" data-flag="<?php echo esc_html($country->flag); ?>" <?php echo $saved_currency && $saved_currency['isoCode'] == $country->isoCode ? 'selected' : ''; ?>>
                                        <?php echo esc_html($country->name); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="t-flex t-settings-page-card t-mb-4">
                        <div class="t-settings-first">
                            <p class="t-settings-page-card-title">
                                Custom price mark up
                            </p>
                            <p class="t-settings-page-card-description">
                                Set your own price markup for all your shipments as a percentage (%).
                            </p>
                        </div>
                        <div style="margin-right: 30px;width: 170px;">
                            <input type="number" class="t-form-control" name="terminal_custom_price_mark_up" placeholder="e.g 10 for 10%" id="terminal_custom_price_mark_up" value="<?php echo esc_html($terminal_custom_price_mark_up); ?>" style="height: 49px;">
                        </div>
                    </div>

                    <div class="t-flex t-settings-page-card t-mb-4" id="highlight_payment">
                        <div class="t-settings-first">
                            <p class="t-settings-page-card-title">
                                Switch API Mode
                            </p>
                            <p class="t-settings-page-card-description">
                                Switch between the test and live API keys.
                            </p>
                        </div>
                        <div style="margin-right: 30px;width: 170px;">
                            <a href="javascript:;" class="t-switch-api-keys t-sign-out">
                                Switch API Mode
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>