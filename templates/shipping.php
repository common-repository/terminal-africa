<?php
//security
defined('ABSPATH') or die('No script kiddies please!');
$orders = getTerminalOrders();
//terminal page
$terminal_page = intval(terminal_param('terminal_page', 1));
//next page
$next_page = $terminal_page + 1;
//prev page
$prev_page = $terminal_page - 1;
//check if next page is greater than 1
if ($next_page < 1) {
    $next_page = 1;
}
//check if prev page is greater than 1
if ($prev_page < 1) {
    $prev_page = 1;
}
//plugin url
$plugin_url = admin_url('admin.php?page=terminal-africa');
//append to prev
$prev_url = add_query_arg('terminal_page', $prev_page, $plugin_url);
//append to next
$next_url = add_query_arg('terminal_page', $next_page, $plugin_url);
//sanitize url
$prev_url = esc_url($prev_url);
//sanitize url
$next_url = esc_url($next_url);
?>
<div class="t-container">
    <?php terminal_header("fas fa-cart-plus", "Shipments"); ?>
    <div class="t-body">
        <div class="t-shipping">
            <table width="100%" style="border-collapse: separate; border-spacing: 0px 0px; text-align: center;">
                <thead>
                    <tr>
                        <th style="width: 60px;"></th>
                        <th class="terminal-dashboard-orders-list-table-heading" style="width: 50px;">Carrier</th>
                        <th class="terminal-dashboard-orders-list-table-heading">Shipping ID</th>
                        <th class="terminal-dashboard-orders-list-table-heading">Order ID</th>
                        <th class="terminal-dashboard-orders-list-table-heading">Order Date</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if (!empty($orders->orders)) :
                        foreach ($orders->orders as $order) :
                            $order_id = $order->get_id();
                            $shipment_id = get_post_meta($order_id, 'Terminal_africa_shipment_id', true);
                            $carrier = get_post_meta($order_id, 'Terminal_africa_carriername', true);
                            $order_date = $order->get_date_created()->date('Y-m-d H:i:s');
                            $timeago = human_time_diff(strtotime($order_date), current_time('timestamp')) . ' ago';
                            $rate_id = get_post_meta($order_id, 'Terminal_africa_rateid', true);
                            //arg
                            $arg = array(
                                'page' => 'terminal-africa',
                                'action' => 'edit',
                                'id' => esc_html($shipment_id),
                                'order_id' => esc_html($order_id),
                                'rate_id' => esc_html($rate_id),
                                'nonce' => wp_create_nonce('terminal_africa_edit_shipment')
                            );
                            $shipping_url = add_query_arg($arg, $plugin_url);
                            //get Terminal_africa_carrierlogo
                            $carrirer_logo = get_post_meta($order_id, 'Terminal_africa_carrierlogo', true) ?: TERMINAL_AFRICA_PLUGIN_ASSETS_URL . '/img/logo.svg';
                            $words = explode(" ", $carrier);
                            if (isset($words[0])) {
                                // The string has a first word
                                $firstWord = $words[0];
                            } else {
                                // The string does not have a first word
                                $firstWord = $carrier;
                            }
                    ?>
                            <tr class="t-terminal-dashboard-order-row" onclick="window.location.href='<?php echo esc_url($shipping_url); ?>'">
                                <td style="width: 60px;">
                                    <img src="<?php echo esc_attr($carrirer_logo); ?>" alt="" style="height: 40px;margin-right: 10px;width: 60px;object-fit: contain;">
                                </td>
                                <td style="width: 50px;">
                                    <div class="t-flex" style="justify-content: center;">
                                        <p>
                                            <span>
                                                <?php echo esc_html($firstWord); ?>
                                            </span>
                                        </p>
                                    </div>
                                </td>
                                <td>
                                    <div class="terminal-dashboard-order-link" style="margin-bottom: 0px; font-size: 16px; color: black; text-transform: capitalize;">
                                        <?php echo esc_html($shipment_id); ?>
                                    </div>
                                </td>
                                <td>
                                    <div class="terminal-dashboard-order-name">
                                        <a href="<?php echo esc_url($shipping_url); ?>" style="color: black; text-decoration: none;font-size: 16px;">#<?php echo esc_html($order_id); ?></a>
                                    </div>
                                </td>
                                <td>
                                    <span class="t-status-list">
                                        <?php echo esc_html($timeago); ?>
                                    </span>
                                </td>
                                <td>
                                    <img src="<?php echo TERMINAL_AFRICA_PLUGIN_ASSETS_URL . '/img/arrow-forward.svg';  ?>" alt="">
                                </td>
                            </tr>
                        <?php endforeach;
                    else : ?>
                        <tr>
                            <td colspan="5">
                                <div class="terminal-dashboard-order-name">
                                    <p style="color: rgb(255, 153, 0); text-decoration: none;">No Shipment</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif;
                    ?>
                </tbody>
            </table>
        </div>
        <div class="t-flex" style="margin: 40px 27px;">
            <div class="t-prev-btn">
                <a href="<?php echo $prev_url; ?>" class="t-btn <?php echo $terminal_page == 1 ? 't-disabled' : ''; ?>">Previous</a>
            </div>
            <div class="t-next-btn">
                <a href="<?php echo $next_url; ?>" class="t-btn">Next</a>
            </div>
        </div>
    </div>
</div>