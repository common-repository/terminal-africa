<?php
//security
defined('ABSPATH') or die('No script kiddies please!');

$carriers = getTerminalCarriers('domestic');
$userCarriersD = getUserCarriers('domestic');
$userCarriersI = getUserCarriers('international');
$userCarriersR = getUserCarriers('regional');
$internationalCarriers = getTerminalCarriers('international');
$regionalCarriers = getTerminalCarriers('regional');
//carriers data array
$carriersData = [
    'domestic' => [
        'title' => 'Local Couriers',
        'carriers' => $carriers['data']->carriers,
        'userCarriers' => $userCarriersD
    ],
    'regional' => [
        'title' => 'Regional Carriers',
        'carriers' => $regionalCarriers['data']->carriers,
        'userCarriers' => $userCarriersR
    ],
    'international' => [
        'title' => 'International Carriers',
        'carriers' => $internationalCarriers['data']->carriers,
        'userCarriers' => $userCarriersI
    ]
];
?>
<div class="t-container">
    <?php terminal_header("fas fa-car", "Carriers"); ?>
    <div class="t-body t-carrier-new" style="padding-top: 10px;">
        <div class="t-row">
            <div class="t-col-12">
                <div class="t-address-info">
                    <div class="t-carriers-title-tag">
                        <!-- instructions -->
                        <h3 class="t-title t-mb-1">
                            <strong>Carriers</strong>
                        </h3>
                        <p class="t-text t-mt-1">
                            Select your choice of carriers from our available partners below
                        </p>
                    </div>
                </div>
            </div>
            <div class="t-col-12">
                <div class="t-address-info">
                    <div class="t-carrier-toggle-all terminal-carrier-card">
                        <div class="t-flex">
                            <div>
                                <h3>Show All Partners</h3>
                                <p>
                                    When toggled on, will display all carriers when booking shipments or getting quotes
                                </p>
                            </div>
                            <div>
                                <label class="t-switch">
                                    <input type="checkbox" class="t-carrier-toggle-all-checkbox">
                                    <span class="t-slider round"></span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php
            foreach ($carriersData as $type => $carrierData) :
                $title = $carrierData['title'];
                $carriers = $carrierData['carriers'];
                $userCarriers = $carrierData['userCarriers'];
            ?>
                <div class="t-col-12">
                    <div class="t-address-info">
                        <div class="t-carrier-list terminal-carrier-card">
                            <h3><?php echo esc_html($title); ?></h3>
                            <div class="t-row">
                                <?php
                                foreach ($carriers as $carrier) :
                                    $carrier_name = $carrier->name;
                                    $carrier_logo = $carrier->logo;
                                    $carrier_id = $carrier->carrier_id;
                                    $carrier_active = $carrier->active;
                                    $domestic = $carrier->domestic ? 'true' : 'false';
                                    $international = $carrier->international ? 'true' : 'false';
                                    $regional = $carrier->regional ? 'true' : 'false';
                                    $slug = $carrier->slug;
                                ?>
                                    <div class="t-col-lg-4 t-col-md-6 t-col-sm-12">
                                        <div class="t-carrier-region-listing-block" data-domestic="<?php echo esc_html($domestic); ?>" data-international="<?php echo esc_html($international); ?>" data-regional="<?php echo esc_html($regional); ?>">
                                            <div class="t-carrier-name-wrapper">
                                                <div class="t-carrier-logo-wrapper">
                                                    <div class="t-carrier-logo-block dhl" style="background-image: url(<?php echo esc_url($carrier_logo); ?>);"></div>
                                                </div>
                                                <div class="t-carrier-name-block">
                                                    <div class="t-carrier-name"><?php echo esc_html($carrier_name); ?></div>
                                                </div>
                                                <?php
                                                if (!$carrier_active) :
                                                ?>
                                                    <div class="t-flex t-coming-soon-flex">
                                                        <div class="t-carrier-coming-soon">Coming Soon</div>
                                                    </div>
                                                <?php
                                                endif;
                                                ?>
                                            </div>
                                            <?php
                                            if ($carrier_active) :
                                            ?>
                                                <div class="t-carrier-embed w-embed">
                                                    <label class="t-switch t-carrier-switch">
                                                        <input type="checkbox" data-carrier-id="<?php echo esc_html($carrier_id); ?>" data-slug="<?php echo esc_html($slug); ?>" class="t-carrier-checkbox" <?php echo getActiveCarrier($carrier_id, $userCarriers, $type) ? 'checked' : ''; ?>>
                                                        <span class="t-slider round"></span>
                                                    </label>
                                                </div>
                                            <?php
                                            endif;
                                            ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php
                //check if its last item
                if ($carrierData !== end($carriersData)) :
                ?>
                    <div class="t-col-12">
                        <div class="t-space-no-border"></div>
                    </div>
            <?php
                endif;
            endforeach;
            ?>
        </div>
    </div>
</div>