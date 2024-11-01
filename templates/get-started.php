<?php
//security
defined('ABSPATH') or die('No script kiddies please!');
//terminal_africa_settings
$terminal_africa_settings = get_option('terminal_africa_settings', []);
//payment status
$payment_gateway_status = "inactive";
//check if isset payment_gateway_status
if (isset($terminal_africa_settings['others']->user->payment_gateway_status)) {
    $payment_gateway_status = $terminal_africa_settings['others']->user->payment_gateway_status;
}
?>
<div class="t-container">
    <?php terminal_header("fas fa-globe", "Get Started"); ?>
    <div class="t-body" style="padding-top: 10px;">
        <div class="t-row">
            <div class="t-col-12">
                <div class="t-address-card t-settings-page">
                    <div class="t-flex">
                        <div class="t-carriers-title-tag">
                            <h3 class="t-address-card-header-text t-pl-0">Check out these tips to get started</h3>
                        </div>
                    </div>
                    <div class="t-flex t-flex-dynamic">
                        <div class="card-payment-gateway get-started-button t-get-started-active" data-view="get-started-payment-section">
                            <div class="t-flex">
                                <img src="<?php echo esc_url(TERMINAL_AFRICA_PLUGIN_ASSETS_URL . '/img/payment.svg') ?>" alt="Get support" style="margin-right: 10px;">
                                <span>
                                    Payment Gateway
                                </span>
                            </div>
                        </div>
                        <div class="card-shipping get-started-button" data-view="get-started-shipping-section">
                            <div class="t-flex">
                                <img src="<?php echo esc_url(TERMINAL_AFRICA_PLUGIN_ASSETS_URL . '/img/card-shipping-icon.svg') ?>" alt="How shipping works" style="margin-right: 10px;">
                                <span>
                                    Shipping
                                </span>
                            </div>
                        </div>
                        <div class="card-get-support get-started-button" data-view="get-started-support-section">
                            <div class="t-flex">
                                <img src="<?php echo esc_url(TERMINAL_AFRICA_PLUGIN_ASSETS_URL . '/img/support-inactive.svg') ?>" alt="Get support" style="margin-right: 10px;">
                                <span>
                                    Get Support
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="get-started-shipping-section get-started-custom-view" style="display: none;">
                        <div class="get-started-shipping-content-area">
                            <h3>
                                Learn how to ship on Terminal Africa
                            </h3>
                            <p>
                                Packaging, Carriers, Rates, and much more. Get the basics on how you can deliver products to your customers around the world with ease.
                            </p>
                        </div>
                        <div class="t-w-embed-youtubevideo" style="padding-top: 56.1702%;margin-top: 60px;"><iframe src="https://www.youtube.com/embed/BNYJYoeJjmc?rel=0&amp;amp;controls=1&amp;amp;autoplay=0&amp;amp;mute=0&amp;amp;start=0" frameborder="0" allow="autoplay; encrypted-media" style="position: absolute; left: 0px; top: 0px; width: 100%; height: 100%; pointer-events: auto;border-radius: 20px;"></iframe></div>
                    </div>
                    <div class="get-started-support-section get-started-custom-view" style="display: none;">
                        <div class="get-started-support-content-area">
                            <h3>
                                Speak to an expert
                            </h3>
                            <p>
                                Schedule a free online call with a member of our team or send us an email.
                            </p>
                            <p>
                                We're available to help with any issues.
                            </p>
                            <p>
                                You can also get support using the live chat option.
                            </p>
                            <div class="t-get-started-support-actions-link">
                                <p>
                                    <a target="_blank" href="https://calendly.com/terminal-africa/terminal-support-session" class="t-support-actions-link t-call">Schedule a call</a>
                                </p>
                                <p style="margin-left: 25px;">
                                    <a href="mailto:support@terminal.africa" class="t-support-actions-link">Send an email</a>
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="get-started-payment-section get-started-custom-view">
                        <div class="get-started-payment-content-area">
                            <div class="get-started-payment-content-area--header">
                                <img src="<?php echo esc_url(TERMINAL_AFRICA_PLUGIN_ASSETS_URL . '/img/payment_header.svg') ?>" alt="Terminal Africa Payment">
                            </div>
                            <div class="get-started-payment-content-area--body">
                                <h3>
                                    Access Payments Globally
                                </h3>
                                <p>
                                    At Terminal Africa, we revolutionize logistics and financial transactions. With our trusted logistics services and new global payment options, we ensure your business runs smoothly.
                                </p>
                                <h4>
                                    New Payment Solutions:
                                </h4>
                                <ul>
                                    <li>
                                        <span>PayPal</span>
                                    </li>
                                    <li>
                                        <span>Stripe</span>
                                    </li>
                                    <li>
                                        <span>Flutterwave</span>
                                    </li>
                                    <li>
                                        <span>Paystack</span>
                                    </li>
                                    <li>
                                        <span>Apple pay</span>
                                    </li>
                                    <li>
                                        <span>Google pay</span>
                                    </li>
                                </ul>
                                <h4>
                                    Why Choose Us?
                                </h4>
                                <p class="ptext">
                                    <b>- Integrated Solutions:</b> Logistics and payments under one roof. <br>
                                    <b>- Global Expertise:</b> Experienced in international trade and finance. <br>
                                    <b>- Trusted Partnerships:</b> Leading payment platforms for the best options. <br>
                                    <b>- Commitment to Excellence:</b> Exceeding expectations with every shipment and transaction.
                                </p>

                                <p>
                                    Click on the button below to gain access to our various payment options and streamline your operations and expand your global reach
                                </p>
                            </div>
                            <div class="t-get-started-support-actions-link get-started-payment-content-area--footer">
                                <?php if ($payment_gateway_status == "inactive") { ?>
                                    <a href="javascript:;" class="t-support-actions-link terminal-africa-payment-request">Request access</a>
                                <?php } else {
                                ?>
                                    <div class="terminal-payment-gateway-status-log">
                                        <p>
                                            Terminal Africa Payment Gateway Status:
                                        </p>
                                        <span class="t-settings-badge t-settings-badge-<?php echo $payment_gateway_status; ?>"><?php echo $payment_gateway_status; ?></span>
                                    </div>
                                <?php
                                } ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>