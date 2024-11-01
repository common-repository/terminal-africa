 <?php
    //security
    if (!defined('ABSPATH')) {
        exit("You are not allowed to access this file directly.");
    }
    ?>
 <a href="javascript:;" class="t-btn t-btn-primary t-btn-sm" id="t-carrier-change-button" data-shipment_id="<?php echo esc_html($shipment_id); ?>" data-order-id="<?php echo esc_html($order_id); ?>" onclick="changeTerminalCarrier(this, event)" style="padding: 8px 8px;">Change Carrier</a>
 <a href="javascript:;" class="t-btn t-btn-primary t-btn-sm" id="t-carrier-change-button" data-shipment_id="<?php echo esc_html($shipment_id); ?>" data-rate-id="<?php echo esc_html($rate_id); ?>" data-order-id="<?php echo esc_html($order_id); ?>" onclick="arrangeTerminalDelivery(this, event)" style="padding: 8px 8px;">Arrange for delivery</a>