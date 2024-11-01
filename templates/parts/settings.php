<?php
//security
defined('ABSPATH') or die('No script kiddies please!');
?>
<div class="t-row">
    <div class="t-col-12">
        <div class="t-address-card-carriers t-settings-section">
            <div class="t-carrier-card-header">
                <div class="t-flex">
                    <div class="t-carriers-title-tag">
                        <h4 class="t-address-card-header-text t-pl-0">Terminal Settings</h4>
                    </div>
                    <div class="t-merchant-id">
                        <h4 class="t-address-card-header-text t-pl-0">Merchant ID: <span class="t-merchant-id-text"><?php echo esc_html(get_option('terminal_africa_merchant_id')); ?></span></h4>
                    </div>
                </div>
            </div>
            <div class="t-carrier-card-body">
                <div class="t-row t-justify-content-between">
                    <div class="t-col-4 t-col-md-4 t-col-md-12">
                        <div class="t-flex" style="align-items: baseline;">
                            <div class="t-price-mark-up" style="width:40%;">
                                <div class="t-carriers-custom-markup">
                                    <!-- custom price mark up -->
                                    <h3 class="t-title t-mb-1">
                                        Custom price mark up:
                                    </h3>
                                    <span>
                                        Enter a custom price mark up for all your shipments in percentage (%)
                                    </span>
                                    <input type="number" class="t-form-control" name="terminal_custom_price_mark_up" placeholder="e.g 10 for 10%" id="terminal_custom_price_mark_up" value="<?php echo esc_html($terminal_custom_price_mark_up); ?>">
                                </div>
                            </div>
                            <div class="t-settings-toggler" style="width:40%;">
                                <div class="t-row">
                                    <div class="t-col" style="margin-bottom: 10px;">
                                        <input type="checkbox" name="Hide_Shipment_Timeline" id="Hide_Shipment_Timeline" <?php echo get_option('terminal_user_carrier_shipment_timeline') == 'true' ? 'checked' : ''; ?>>
                                        <label for="Hide_Shipment_Timeline">Hide Shipment Timeline</label>
                                    </div>
                                    <div class="t-col" style="margin-bottom: 10px;">
                                        <input type="checkbox" name="Hide_Shipment_Rate" id="Hide_Shipment_Rate" <?php echo get_option('update_user_carrier_shipment_rate_terminal') == 'true' ? 'checked' : ''; ?>>
                                        <label for="Hide_Shipment_Rate">Hide Shipment Rate</label>
                                    </div>
                                    <div class="t-col">
                                        <input type="checkbox" name="Enable_Terminal_Insurance" id="Enable_Terminal_Insurance" <?php echo get_option('update_user_carrier_shipment_insurance_terminal') == 'true' ? 'checked' : ''; ?>>
                                        <label for="Enable_Terminal_Insurance">Enable Shipment Insurance</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="t-col-3 t-col-md-6 t-col-md-12">
                        <!-- custom price mark up -->
                        <h3 class="t-title t-mb-1">
                            Default Currency Code:
                        </h3>
                        <p class="t-mb-2">
                            Set the default currency code for the checkout page.
                        </p>
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
            </div>
        </div>
    </div>
</div>