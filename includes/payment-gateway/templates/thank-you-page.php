<?php

/**
 * Thank you page template
 * 
 * @package Terminal Africa Payment Gateway
 * @version 1.11.10
 */

//check for security
if (!defined('ABSPATH')) {
    exit("Direct access not allowed");
}
?>
<ul class="woocommerce-order-overview woocommerce-thankyou-order-details order_details">

    <?php if ($order->get_payment_method_title()) : ?>
        <li class="woocommerce-order-overview__payment-method method">
            <?php esc_html_e('Payment Status:', 'woocommerce'); ?>
            <strong class="terminal-africa-payment-status" data-order-id="<?php echo $order->get_id(); ?>">
                .....
            </strong>
        </li>
    <?php endif; ?>

</ul>