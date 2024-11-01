 <?php
    //security
    if (!defined('ABSPATH')) {
        exit("You are not allowed to access this file directly.");
    }
    ?>
 <a href="javascript:;" class="t-btn t-btn-primary t-btn-sm" id="t-carrier-duplicate-shipment-button" data-shipment_id="<?php echo esc_html($shipment_id); ?>" data-order-id="<?php echo esc_html($order_id); ?>" onclick="duplicateTerminalShipment(this, event)" style="padding: 8px 8px;">Duplicate Shipment</a>