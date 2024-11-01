<?php
//security
defined('ABSPATH') or die('No script kiddies please!');
$terminal_africa_merchant_id = get_option('terminal_africa_merchant_id');
$wallet_balance = getWalletBalance($terminal_africa_merchant_id);
//terminal page
$terminal_page = intval(terminal_param('terminal_page', 1));
//startDate
$startDate = terminal_param('startDate');
//endDate
$endDate = terminal_param('endDate');
//flow
$flow = terminal_param('flow');
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
//getTransactions
$transactions = getTransactions($terminal_page, compact('startDate', 'endDate', 'flow'), true);
//get current request url
$current_url = terminal_current_url();
//plugin url
$plugin_url = admin_url($current_url);
//append to prev
$prev_url = add_query_arg('terminal_page', $prev_page, $plugin_url);
//append to next
$next_url = add_query_arg('terminal_page', $next_page, $plugin_url);
//sanitize url
$prev_url = esc_url($prev_url);
//sanitize url
$next_url = esc_url($next_url);

$wallet_data = [];
$other_data = false;
//check if code is 200
if ($wallet_balance['code'] == 200) {
    $wallet_data[] = [
        'balance' => $wallet_balance['data']->amount,
        'currency' => $wallet_balance['data']->currency
    ];
    $other_data = $wallet_balance['data'];
} else {
    $wallet_data[] = [
        'balance' => 0,
        'currency' => 'NGN'
    ];
}

//flow text
$flow_text = 'All';
switch ($flow) {
    case 'in':
        $flow_text = 'Flow In';
        break;
    case 'out':
        $flow_text = 'Flow Out';
        break;
}

//check if wallet currency session is available
if (isset($_SESSION['terminal_africa_wallet_currency'])) {
    $default_currency = $_SESSION['terminal_africa_wallet_currency'];
} else {
    $default_currency = 'NGN';
}

?>
<div class="t-container">
    <?php terminal_header("fas fa-book", "Wallet"); ?>
    <div class="terminal-standard-wrapper">
        <div class="terminal-standard-block">
            <div class="t-wallet-background t-wallet-home">
                <div class="t-balance-container">
                    <?php
                    foreach ($wallet_data as $balance) :
                    ?>
                        <div class="terminal-balance-block t-<?php echo esc_html($balance['currency']); ?>-balance" data-balance="<?php echo esc_html($balance['balance']); ?>">
                            <div class="terminal-wallet-currency-switch">
                                <h1 class="t-wallet-balance-title"><?php echo esc_html($balance['currency']); ?> Balance</h1>
                                <div>
                                    <select class="t-switch-wallet w-select">
                                        <option value="NGN" <?php echo selected("NGN", $default_currency); ?>>NGN (₦)</option>
                                        <option value="USD" <?php echo selected("USD", $default_currency); ?>>USD ($)</option>
                                    </select>
                                </div>
                            </div>
                            <div class="t-balance-figure"><?php echo $balance['currency'] == "NGN" ? '₦' : '$'; ?><?php echo esc_html($balance['balance']); ?></div>
                            <div class="t-balance-footer-text">Total available including pending transactions</div>
                        </div>
                    <?php
                    endforeach;
                    ?>
                </div>
                <div class="t-balance-container">
                    <div class="t-landing-action-link-button">
                        <a class="t-wallet-link-wrapper t-top-up-landing" onclick="gotoTerminalPage(this, 't-wallet-topup')"><img src="<?php echo esc_url(TERMINAL_AFRICA_PLUGIN_URL . '/assets/img/WalletIcon.png'); ?>" loading="lazy" height="30" width="30" alt="" class="t-top-up-image-green-landscape">
                            <div class="t-quick-link-text">Top Up</div>
                        </a>
                    </div>
                </div>
            </div>
            <div class="t-wallet-background t-wallet-topup" style="display:none ;">
                <div class="t-terminal-dashboard-back-link-block"><a class="t-terminal-dashboard-back-link" onclick="gotoTerminalPage(this, 't-wallet-home')">Wallet</a></div>
                <div class="t-top-up-wallet-wrapper t-amount-input">
                    <h4 class="t-wallet-heading-text">Enter topup amount</h4>
                    <div class="t-topup-amount-block">
                        <input placeholder="₦0.00" class="t-top-up-amount-input" id="t-top-up-amount-input" step=".01" data-max="0" min="0" type="text">
                    </div>
                    <div>
                        <div class="t-balance-sub-text">Balance after topup - ₦0.00</div><select class="t-switch-wallet w-select">
                            <option value="NGN">Nigerian Naira (₦)</option>
                        </select>
                    </div>
                    <div class="t-wallet-label">SELECT A PAYMENT METHOD</div>
                    <ul role="list" class="t-topup-wallet-list-wrapper w-list-unstyled">
                        <li data-method="bank-transfer" class="t-list-item-section active bottom"><img data-method="bank-transfer" src="<?php echo esc_url(TERMINAL_AFRICA_PLUGIN_URL . '/assets/img/Bank-Icon-Orange.png'); ?>" loading="lazy" height="50" width="60" alt="" class="t-topup-icon">
                            <div data-method="bank-transfer" class="t-topup-method-block">
                                <h3 data-method="bank-transfer" class="t-topup-method-heading">Bank Transfer</h3>
                                <div data-method="bank-transfer" class="t-topup-method-text">Top up by sending money to a Nigerian bank account</div>
                            </div>
                            <div class="t-wallet-option-check-icon"></div>
                        </li>
                    </ul>
                    <div>
                        <a class="t-topup-cta-link w-inline-block" onclick="gotoTerminalPage(this, 't-confirm-bank')">
                            Next
                        </a>
                    </div>
                </div>
                <!-- T-Bank Account -->
                <div class="t-confirm-top-up-wallet-block t-confirm-bank" style="display:none ;">
                    <h4 class="t-wallet-heading-text">Make a Transfer of</h4>
                    <div class="t-top-up-amount">₦1,000.00</div>
                    <div class="t-balance-sub-text">to this account</div>
                    <ul role="list" class="t-bank-details-list w-list-unstyled">
                        <li class="t-bank-details-list-item">
                            <img src="<?php echo esc_url(TERMINAL_AFRICA_PLUGIN_URL . '/assets/img/bankimage.png'); ?>" loading="lazy" width="60" alt="" class="t-bank-account-icon">
                            <div class="t-bank-info-text bank-details"><?php echo $other_data ? esc_html($other_data->bank_name) : 'NULL' ?></div>
                            <h3 class="t-ban-account-number bank-details"><?php echo $other_data ? esc_html($other_data->account_number) : 'NULL' ?></h3>
                            <div class="t-bank-info-text bank-details"><?php echo $other_data ? esc_html($other_data->account_name) : 'NULL' ?></div>
                        </li>
                    </ul>
                    <div><a class="t-topup-cta-link orange w-inline-block" onclick="confirmTerminalTransfer(this, event)">
                            <div>Confirm</div>
                        </a></div>
                </div>
            </div>

        </div>
    </div>
    <div class="t-transaction-container">
        <div class="t-header-transaction-title t-center">
            <h3>All Transactions</h3>
        </div>
        <div class="t-transaction-header t-flex" style="display: none;">
            <div class="t-transaction-header-left">
                <div class="t-flex">
                    <p>All Transactions</p>
                    <p class="t-transaction-header-left-arrow t-ml-2"><?php echo esc_html($flow_text); ?> <img src="<?php echo esc_url(TERMINAL_AFRICA_PLUGIN_URL . '/assets/img/arrow_down.svg'); ?>" alt=""></p>
                </div>
                <div class="t-transaction-header-left-option" style="display: none;">
                    <ul>
                        <li data-menu="out" onclick="filterTransaction(this)">
                            Flow Out
                        </li>
                        <li data-menu="in" onclick="filterTransaction(this)">
                            Flow In
                        </li>
                        <li data-menu="all" onclick="filterTransaction(this)">
                            All
                        </li>
                    </ul>
                </div>
            </div>
            <div class="t-transaction-header-right">
                <div class="t-transaction-header-right-t-flex">
                    <div class="t-start-date">
                        <label for="t_start_date">Start Date</label>
                        <input type="date" name="t_start_date" id="t_start_date" value="<?php echo $startDate ? $startDate : date('Y-m-d'); ?>">
                    </div>
                    <div class="t-end-date t-ml-2">
                        <label for="t_end_date">End Date</label>
                        <input type="date" name="t_end_date" id="t_end_date" value="<?php echo $endDate ? $endDate : date('Y-m-d'); ?>">
                    </div>
                    <div class="t-filter-btn">
                        <a href="javascript:void();" class="t-btn t-filter-transaction">Filter</a>
                    </div>
                </div>
                <!-- <div class="t-search-placeholder">
                    <img src="<?php echo esc_url(TERMINAL_AFRICA_PLUGIN_URL . '/assets/img/search.png'); ?>" alt="">
                    <input type="text" placeholder="Search">
                </div> -->
            </div>
        </div>
        <div class="t-transaction-body t-table-wallet">
            <table>
                <thead>
                    <tr>
                        <th scope="col">Date</th>
                        <th scope="col">Description</th>
                        <th scope="col">Reference Number</th>
                        <th scope="col">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    //$transactions
                    if ($transactions['code'] == 200 && isset($transactions['data']->transactions)) {
                        //check if next page is active
                        if (!$transactions['data']->pagination->hasNextPage) {
                            $next_url = 'javascript:void();';
                        }
                        //check if prev page is active
                        if (!$transactions['data']->pagination->hasPrevPage) {
                            $prev_url = 'javascript:void();';
                        }
                        foreach ($transactions['data']->transactions as $transaction) :
                            $transactionDate = $transaction->created_at;
                            //format 05/03/2021 7:00:00PM
                            $transactionDate = date('d/m/Y h:i:sA', strtotime($transactionDate));
                    ?>
                            <tr>
                                <td data-label="Date">
                                    <div class="t-flex-transaction">
                                        <div class="t-transaction-icon">
                                            <img src="<?php echo esc_url(TERMINAL_AFRICA_PLUGIN_URL . '/assets/img/' . $transaction->flow . '.svg'); ?>" alt="">
                                        </div>
                                        <div>
                                            <?php echo esc_html($transactionDate) ?>
                                        </div>
                                    </div>
                                </td>
                                <td data-label="Description">
                                    <?php echo esc_html($transaction->description) ?>
                                </td>
                                <td data-label="Reference Number">
                                    <?php echo esc_html($transaction->reference) ?>
                                </td>
                                <td data-label="Amount">
                                    <?php echo $transaction->currency  . ' ' . number_format($transaction->amount, 2); ?>
                                </td>
                            </tr>
                        <?php
                        endforeach;
                        ?>
                        <tr style="border-bottom: none !important;">
                            <td colspan="4">
                                <div class="t-flex t-m-1">
                                    <div class="t-prev-btn">
                                        <a href="<?php echo $prev_url; ?>" class="t-btn <?php echo !$transactions['data']->pagination->hasPrevPage ? 't-disabled' : ''; ?>">Previous</a>
                                    </div>
                                    <div class="t-next-btn">
                                        <a href="<?php echo $next_url; ?>" class="t-btn <?php echo !$transactions['data']->pagination->hasNextPage ? 't-disabled' : ''; ?>">Next</a>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php
                    } else {
                        echo '<tr><td colspan="4" style="text-align:center;">No Transactions</td></tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>