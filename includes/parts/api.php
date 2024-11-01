<?php

namespace TerminalAfrica\Includes\Parts;

use TerminalAfricaShippingPlugin;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;
use WpOrg\Requests\Requests;

//security
defined('ABSPATH') or die('No script kiddies please!');

/**
 * Terminal Africa API
 * @since 1.10.22
 */
trait TerminalRESTAPI
{
    /**
     * Allowed order statuses
     * @var array
     */
    public static $allowed_order_statuses = [
        'wc-tdraft',
        'wc-tcancelled',
        'wc-tconfirmed',
        'wc-tpending',
        'wc-tdelivered',
        'wc-tintransit',
    ];

    //overide init
    public function initAPI()
    {
        //add rest api
        add_action('rest_api_init', [$this, 'register_api']);
        //woocommerce_register_shop_order_post_statuses
        add_filter('woocommerce_register_shop_order_post_statuses', [$this, 'add_custom_order_status_v2']);
        //add custom status to wc orders
        add_filter('wc_order_statuses', [$this, 'add_custom_order_status']);
        //wc new order
        add_action('woocommerce_new_order', [$this, 'new_order_fcm_notification'], 10, 1);
        //wc checkout order process
        add_action('woocommerce_checkout_order_processed', [$this, 'new_order_fcm_notification'], 10, 1);
        //wc status change
        add_action('woocommerce_order_status_changed', [$this, 'order_status_changed'], 10, 3);
        //woocommerce_product_data_tabs
        add_filter('woocommerce_product_data_tabs', [$this, 'woocommerce_product_data_tabs'], 10, 1);
        //woocommerce_product_data_panels
        add_action('woocommerce_product_data_panels', [$this, 'woocommerce_product_data_panels'], 10, 1);
        //woocommerce_process_product_meta
        add_action('woocommerce_process_product_meta', [$this, 'woocommerce_process_product_meta'], 10, 1);
    }

    /**
     * woocommerce_product_data_tabs
     * @param $tabs
     * @return mixed
     */
    public function woocommerce_product_data_tabs($tabs)
    {
        //add Terminal HS code tab
        $tabs['terminal-hscode-tab'] = [
            'label' => __('Terminal HS code', 'terminal-africa'),
            'target' => 'wrapper_terminal_hscode',
            //simple and variable product
            'class' => ['show_if_simple', 'show_if_variable'],
        ];
        return $tabs;
    }

    /**
     * woocommerce_product_data_panels
     * @param $post_id
     */
    public function woocommerce_product_data_panels($post_id)
    {
?>
        <div id="wrapper_terminal_hscode" class="panel woocommerce_options_panel">
            <?php
            //add terminal hscode field
            woocommerce_wp_text_input([
                'id' => 'terminal_hscode',
                'label' => __('Terminal HS code', 'terminal-africa'),
                'placeholder' => __('Enter the Product HS code', 'terminal-africa'),
                'desc_tip'      => true,
                'description' => __('Enter the Product HS code', 'terminal-africa'),
                'icon' => 'fa fa-barcode',
            ]);
            ?>
        </div>
<?php
    }

    /**
     * woocommerce_process_product_meta
     * @param $post_id
     */
    public function woocommerce_process_product_meta($post_id)
    {
        //check if post id is set
        if (!isset($post_id)) {
            return;
        }

        //check if terminal hscode is set
        if (isset($_POST['terminal_hscode'])) {
            //sanitize the hscode
            $hscode = sanitize_text_field($_POST['terminal_hscode']);
            //save hscode to post meta
            update_post_meta($post_id, 'terminal_hscode', $hscode);
        }
    }

    /**
     * Update order status
     * @param integer $order_id
     * @param string $old_status
     * @param string $new_status
     */
    public function order_status_changed($order_id, $old_status, $new_status)
    {
        //check if status is cancelled
        if ($new_status === 'cancelled') {
            //ping terminal api server as a webhook
            $this->ping_terminal_api_server($order_id, $new_status);
        }
    }

    /**
     * ping_terminal_api_server
     * @param integer $order_id
     * @param string $status
     */
    public function ping_terminal_api_server($order_id, $status)
    {
        try {
            //do ping here
            //TODO
        } catch (\Exception $e) {
            //log error
            logTerminalError($e);
        }
    }

    /**
     * New order fcm notification
     * @param $order_id
     */
    public function new_order_fcm_notification($order_id)
    {
        //silent till launch.
        return true;
        /////////// NEW ORDER FCM NOTIFICATION /////////////
        try {
            //send FCM notification
            if (!self::$skkey) {
                //log error to debug
                error_log("Terminal Africa Shipping Plugin: Secret key is not set");
                return false;
            }

            //get terminal africa merchant id
            $terminal_africa_merchant_id = sanitize_text_field(get_option('terminal_africa_merchant_id'));

            //get site url
            $site_url = site_url();
            //get the domain
            $domain = parse_url($site_url, PHP_URL_HOST);

            //data_query
            $data_query = [
                'title' => "New WooCommerce Order",
                'body' => "You have a new woocommerce order with id: #{$order_id}",
                'click_action' => "./OrderActivity",
                'unique_id' => strval($order_id),
                'metadata' => json_encode([
                    'source' => 'wordpress',
                    'user_id' => $terminal_africa_merchant_id ?: 'none',
                    'domain' => $domain,
                ])
            ];

            $response = Requests::post(
                self::$enpoint . 'users/notifications/push',
                [
                    'Authorization' => 'Bearer ' . self::$skkey,
                    'Content-Type' => 'application/json'
                ],
                json_encode(
                    $data_query
                ),
                //time out 60 seconds
                ['timeout' => 60]
            );
            $body = json_decode($response->body);
            //append $body to data_query
            $data_query['endpoint_response'] = $body;
            //check if response is ok
            if ($response->status_code == 200) {
                //return true
                return true;
            } else {
                //logTerminalErrorData
                logTerminalErrorData($response->body, self::$enpoint . 'users/notifications/push' . "?" . http_build_query($data_query));
                return false;
            }
        } catch (\Exception $e) {
            //log error
            logTerminalError($e);
        }
    }

    /**
     * add_custom_order_status_v2
     */
    public function add_custom_order_status_v2($order_statuses)
    {
        //add custom status   
        return $order_statuses + [
            'wc-tdraft'    => array(
                'label'                     => _x('Shipment Draft ✏️', 'Order status', 'woocommerce'),
                'public'                    => false,
                'exclude_from_search'       => false,
                'show_in_admin_all_list'    => true,
                'show_in_admin_status_list' => true,
                /* translators: %s: number of orders */
                'label_count'               => _n_noop('Shipment Draft <span class="count">(%s)</span>', 'Shipment Draft <span class="count">(%s)</span>', 'woocommerce'),
            ),
            'wc-tcancelled'    => array(
                'label'                     => _x('Shipment Cancelled 🛑', 'Order status', 'woocommerce'),
                'public'                    => false,
                'exclude_from_search'       => false,
                'show_in_admin_all_list'    => true,
                'show_in_admin_status_list' => true,
                /* translators: %s: number of orders */
                'label_count'               => _n_noop('Shipment Cancelled <span class="count">(%s)</span>', 'Shipment Cancelled <span class="count">(%s)</span>', 'woocommerce'),
            ),
            'wc-tconfirmed'    => array(
                'label'                     => _x('Shipment Confirmed 🙂', 'Order status', 'woocommerce'),
                'public'                    => false,
                'exclude_from_search'       => false,
                'show_in_admin_all_list'    => true,
                'show_in_admin_status_list' => true,
                /* translators: %s: number of orders */
                'label_count'               => _n_noop('Shipment Confirmed <span class="count">(%s)</span>', 'Shipment Confirmed <span class="count">(%s)</span>', 'woocommerce'),
            ),
            'wc-tpending'    => array(
                'label'                     => _x('Shipment Pending ⌛️', 'Order status', 'woocommerce'),
                'public'                    => false,
                'exclude_from_search'       => false,
                'show_in_admin_all_list'    => true,
                'show_in_admin_status_list' => true,
                /* translators: %s: number of orders */
                'label_count'               => _n_noop('Shipment Pending <span class="count">(%s)</span>', 'Shipment Pending <span class="count">(%s)</span>', 'woocommerce'),
            ),
            'wc-tdelivered'    => array(
                'label'                     => _x('Shipment Delivered ✅', 'Order status', 'woocommerce'),
                'public'                    => false,
                'exclude_from_search'       => false,
                'show_in_admin_all_list'    => true,
                'show_in_admin_status_list' => true,
                /* translators: %s: number of orders */
                'label_count'               => _n_noop('Shipment Delivered <span class="count">(%s)</span>', 'Shipment Delivered <span class="count">(%s)</span>', 'woocommerce'),
            ),
            'wc-tintransit'    => array(
                'label'                     => _x('Shipment In Transit ✈️', 'Order status', 'woocommerce'),
                'public'                    => false,
                'exclude_from_search'       => false,
                'show_in_admin_all_list'    => true,
                'show_in_admin_status_list' => true,
                /* translators: %s: number of orders */
                'label_count'               => _n_noop('Shipment In Transit <span class="count">(%s)</span>', 'Shipment In Transit <span class="count">(%s)</span>', 'woocommerce'),
            ),
        ];
    }

    /**
     * Add custom order status
     * @param $order_statuses
     * @return mixed
     */
    public function add_custom_order_status($order_statuses)
    {
        //custom status
        $custom_status = [
            //wc-draft
            'wc-tdraft' => _x('Shipment Draft ✏️', 'Order status', 'terminal-africa'),
            //wc-cancelled
            'wc-tcancelled' => _x('Shipment Cancelled 🛑', 'Order status', 'terminal-africa'),
            //wc-confirmed
            'wc-tconfirmed' => _x('Shipment Confirmed 🙂', 'Order status', 'terminal-africa'),
            //wc-pending
            'wc-tpending' => _x('Shipment Pending ⌛️', 'Order status', 'terminal-africa'),
            //wc-delivered
            'wc-tdelivered' => _x('Shipment Delivered ✅', 'Order status', 'terminal-africa'),
            //wc-in-transit
            'wc-tintransit' => _x('Shipment In Transit ✈️', 'Order status', 'terminal-africa'),
        ];
        //return
        return array_merge($order_statuses, $custom_status);
    }

    /**
     * Register API
     */
    public function register_api()
    {
        //register api
        register_rest_route(
            'terminal-africa/v1',
            '/orders',
            [
                'methods' => WP_REST_Server::READABLE,
                'callback' => [$this, 'orders'],
                'permission_callback' => [$this, 'api_permission']
            ]
        );

        //register update order status
        register_rest_route(
            'terminal-africa/v1',
            '/update-order-status',
            [
                'methods' => WP_REST_Server::EDITABLE,
                'callback' => [$this, 'update_order_status'],
                'permission_callback' => [$this, 'api_permission']
            ]
        );

        //order_meta
        register_rest_route(
            'terminal-africa/v1',
            '/order_meta',
            [
                'methods' => WP_REST_Server::EDITABLE,
                'callback' => [$this, 'update_order_meta'],
                'permission_callback' => [$this, 'api_permission']
            ]
        );

        //deactivate the plugin
        register_rest_route(
            'terminal-africa/v1',
            '/deactivate',
            [
                'methods' => WP_REST_Server::EDITABLE,
                'callback' => [$this, 'deactivateThePlugin'],
                'permission_callback' => [$this, 'api_permission']
            ]
        );

        //update user settings
        register_rest_route(
            'terminal-africa/v1',
            '/update-user-settings',
            [
                'methods' => WP_REST_Server::EDITABLE,
                'callback' => [$this, 'update_user_settings'],
                'permission_callback' => [$this, 'api_permission']
            ]
        );

        /**
         * Pull products from terminal africa
         */
        register_rest_route(
            'terminal-africa/v1',
            '/pull-products',
            [
                'methods' => WP_REST_Server::READABLE,
                'callback' => [$this, 'pull_products'],
                'permission_callback' => [$this, 'api_permission']
            ]
        );

        /**
         * Update product
         */
        register_rest_route(
            'terminal-africa/v1',
            '/update-product',
            [
                'methods' => WP_REST_Server::EDITABLE,
                'callback' => [$this, 'update_product'],
                'permission_callback' => [$this, 'api_permission']
            ]
        );

        /**
         * Update bulk products
         */
        register_rest_route(
            'terminal-africa/v1',
            '/update-bulk-products',
            [
                'methods' => WP_REST_Server::EDITABLE,
                'callback' => [$this, 'update_bulk_products'],
                'permission_callback' => [$this, 'api_permission']
            ]
        );
    }

    /**
     * getBearerToken
     */
    public function getBearerToken()
    {
        //get the headers
        $headers = getallheaders();

        //check if the authorization header is set
        if (isset($headers['Authorization'])) {
            //get the authorization header
            $authorization = $headers['Authorization'];

            //split the authorization header
            $authorization = explode(' ', $authorization);

            //check if the authorization header is set
            if (isset($authorization[1])) {
                //return the authorization header
                return $authorization[1];
            }
        }

        //return false
        return false;
    }

    /**
     * API Permission
     */
    public function api_permission()
    {
        //get the bearer token
        $token = $this->getBearerToken();
        //check if the token is set
        if ($token) {
            //verify token matches the own token
            if ($token == self::$skkey) {
                //return true
                return true;
            }
        }
        //return custom error
        return new \WP_Error('invalid_token', 'Invalid user token', ['status' => 401]);
    }

    /**
     * Pull products from terminal africa
     */
    public function pull_products(WP_REST_Request $request)
    {
        try {
            //get the limit and page
            $limit = $request->get_param('limit');
            $page = $request->get_param('page');
            //args
            $args = [
                'limit' => $limit ?: 10,
                'page' => $page ?: 1,
                'status' => 'publish',
                'orderby' => 'date', //or 'title'
                'order' => 'DESC', //or 'ASC'
            ];
            //search
            $search = $request->get_param('s');
            if (!empty($search)) {
                $args['s'] = $search;
            }
            //orderby
            $orderby = $request->get_param('orderby');
            if (!empty($orderby)) {
                $args['orderby'] = $orderby;
            }
            //order
            $order = $request->get_param('order');
            if (!empty($order)) {
                $args['order'] = $order;
            }

            //product_id
            $product_id = $request->get_param('product_id');
            if (!empty($product_id)) {
                $args['product_id'] = $product_id;
            }

            //get is_empty_hscode
            $is_empty_hscode = $request->get_param('is_empty_hscode');
            if (!empty($is_empty_hscode)) {
                // Add the condition for empty hscode
                $args['meta_query'][] = [
                    [
                        'relation' => 'AND',
                        [
                            'key' => 'terminal_hscode',
                            'value' => '',
                            'compare' => '=',
                        ]
                    ]
                ];
            }
            //get all products
            $products = wc_get_products($args);
            //loop through
            $products_list = [];
            foreach ($products as $product) {
                $product = $product->get_data();
                //product id
                $product_id = $product['id'];
                //get product image
                $product_image = get_the_post_thumbnail_url($product_id);
                //terminal_hscode
                $terminal_hscode = get_post_meta($product_id, 'terminal_hscode', true);
                //pass data
                $products_list[] = (object)[
                    "product_id" => $product['id'],
                    "name" => $product['name'],
                    "description" => $product['description'],
                    "original_price" => $product['price'],
                    "sale_price" => $product['sale_price'],
                    "thumbnail" => $product_image ? $product_image : TERMINAL_AFRICA_PLUGIN_ASSETS_URL . '/img/logo-footer.png',
                    "weight" =>
                    (float)get_post_meta($product_id, '_weight', true) ?: 0.1,
                    "length" => $product['length'],
                    "width" => $product['width'],
                    "height" => $product['height'],
                    "hscode" => $terminal_hscode
                ];
            }
            //response
            $response = [
                "status" => 200,
                "meta" => [
                    "page" => $page ?: 1,
                    "limit" => $limit ?: 10,
                    "total" => count($products)
                ],
                "message" => "Products fetched successfully",
                "data" => $products_list,
            ];
            //return
            return new WP_REST_Response($response, 200);
        } catch (\Exception $e) {
            logTerminalError($e);
            //response
            $response = [
                "status" => 500,
                "message" => "Error loading products: " . $e->getMessage(),
                "data" => [],
            ];
            //return
            return new WP_REST_Response($response, 500);
        }
    }

    /**
     * Update product
     */
    public function update_product(WP_REST_Request $request)
    {
        try {
            //get the product id
            $product_id = $request->get_param('product_id');
            //get the product
            $product = wc_get_product($product_id);

            //check if the product is set
            if (!$product) {
                //response
                $response = [
                    "status" => 404,
                    "message" => "Product not found",
                    "data" => [],
                ];
                //return
                return new WP_REST_Response($response, 404);
            }

            //get the hscode
            $hscode = $request->get_param('hscode');
            //sanitize hscode
            $hscode = sanitize_text_field($hscode);
            //update the product hscode
            update_post_meta($product_id, 'terminal_hscode', $hscode);

            //response
            $response = [
                "status" => 200,
                "message" => "Product updated successfully",
                "data" => [
                    "product" => $product_id,
                    "hscode" => $hscode
                ]
            ];
            //return
            return new WP_REST_Response($response, 200);
        } catch (\Exception $e) {
            logTerminalError($e);
            //response
            $response = [
                "status" => 500,
                "message" => "Error updating product: " . $e->getMessage(),
                "data" => [],
            ];
            //return
            return new WP_REST_Response($response, 500);
        }
    }

    /**
     * Update bulk products
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function update_bulk_products(WP_REST_Request $request)
    {
        try {
            //get the products
            $products = $request->get_params();
            //check if products is an array
            if (!is_array($products)) {
                //response
                $response = [
                    "status" => 400,
                    "message" => "Products is not an array",
                    "data" => [],
                ];
                //return
                return new WP_REST_Response($response, 400);
            }

            //check is not emtpy
            if (empty($products)) {
                //respond
                $response = [
                    "status" => 400,
                    "message" => "Products data is required",
                    "data" => [],
                ];
                //return
                return new WP_REST_Response($response, 400);
            }

            //sanitize array
            $formatted_products = sanitize_array($products);

            //loop through the products
            foreach ($formatted_products as $product) {
                //get the product id
                $product_id = (int)$product['product_id'];
                //get the hscode
                $hscode = $product['hscode'];
                //update the product hscode
                update_post_meta($product_id, 'terminal_hscode', $hscode);
            }

            //response
            $response = [
                "status" => 200,
                "message" => "Products updated successfully",
                "data" => $formatted_products,
            ];
            //return
            return new WP_REST_Response($response, 200);
        } catch (\Exception $e) {
            logTerminalError($e);
            //response
            $response = [
                "status" => 500,
                "message" => "Error updating products: " . $e->getMessage(),
                "data" => [],
            ];
            //return
            return new WP_REST_Response($response, 500);
        }
    }

    /**
     * Get all wc orders
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function orders(WP_REST_Request $request)
    {
        try {
            global $terminal_allowed_order_statuses;
            //remove all "wc" in terminal_allowed_order_statuses array
            $terminal_allowed_order_statuses = array_map(function ($status) {
                return str_replace('wc-', '', $status);
            }, $terminal_allowed_order_statuses);
            //order id
            $order_id = $request->get_param('order_id');
            //sanitize order id
            $order_id = sanitize_text_field($order_id);
            //check if the order id is set
            if ($order_id) {
                //load single order
                return $this->loadOrder($order_id);
            }
            //page
            $page = $request->get_param('page');
            //sanitize page id
            $page = sanitize_text_field($page);
            //limit
            $limit = $request->get_param('limit');
            //sanitize limit
            $limit = sanitize_text_field($limit);
            //date_from
            $date_from = $request->get_param('date_from');
            //sanitize date from
            $date_from = sanitize_text_field($date_from);
            //date_to
            $date_to = $request->get_param('date_to');
            //sanitize date to
            $date_to = sanitize_text_field($date_to);
            //customer email
            $customer_email = $request->get_param('customer_email');
            //sanitize customer email
            $customer_email = sanitize_text_field($customer_email);
            //orderby
            $orderby = $request->get_param('orderby');
            //sanitize order by
            $orderby = sanitize_text_field($orderby);
            //order
            $ordermode = $request->get_param('ordermode');
            //sanitize
            $ordermode = sanitize_text_field($ordermode);
            //get all orders
            $orders = wc_get_orders([
                'limit' => $limit ?: 10,
                'page' => $page ?: 1,
                'date_before' => $date_to ?: '',
                'date_after' => $date_from ?: '',
                'status' => ['processing', 'completed', 'on-hold', 'pending'] + $terminal_allowed_order_statuses,
                'billing_email' => $customer_email,
                //order by date
                'orderby' => $orderby ?: 'date',
                //order type
                'order' => $ordermode ?: 'ASC',
                //where order id
                'include' => [$order_id]
            ]);
            //get total orders
            $total_orders = wc_get_orders([
                'limit' => -1,
                'page' => 1,
                'date_before' => $date_to ?: '',
                'date_after' => $date_from ?: '',
                'status' => ['processing', 'completed', 'on-hold', 'pending'] + $terminal_allowed_order_statuses,
                'billing_email' => $customer_email,
                //where order id
                'include' => [$order_id]
            ]);
            //orders list
            $orders_list = [];
            //loop through the orders
            foreach ($orders as $order) {
                //get the order id
                $order_id = $order->get_id();
                //get the order data
                $order_data = $order->get_data();
                //get the order date
                $order_date = $order->get_date_created();
                //get the order date
                $order_date = $order_date->date('Y-m-d H:i:s');
                //get the products
                $items = $order->get_items();
                //products
                $products = [];
                //loop through the products
                foreach ($items as $product_id => $item) {
                    //convert to int $product_id
                    $product_id = intval($product_id);
                    $products[] = [
                        "name" => $item->get_name(),
                        "quantity" => intval($item->get_quantity()) ?: 1,
                        "value" => $item->get_total(),
                        "description" => "{$item->get_quantity()} of {$item->get_name()} at {$item->get_total()} each for a total of {$item->get_total()}",
                        "type" => "parcel",
                        "currency" => get_woocommerce_currency(),
                        "weight" => (float)get_post_meta($product_id, '_weight', true) ?: 0.1
                    ];
                }
                //check if order has Terminal_africa_shipment_id
                $shipment_id = get_post_meta(
                    $order_id,
                    'Terminal_africa_shipment_id',
                    true
                );
                //orders list
                $orders_list[] = [
                    "id" => $order_id,
                    "shipment_id" => $shipment_id ?: null,
                    "products" => $products,
                    "default_address" => get_option('terminal_africa_merchant_address_id', null),
                    'order_meta' => [
                        'carrier' => get_post_meta($order_id, 'Terminal_africa_carriername', true),
                        'amount' => get_post_meta($order_id, 'Terminal_africa_amount', true),
                        'duration' => get_post_meta($order_id, 'Terminal_africa_duration', true),
                        'rate_id' => get_post_meta($order_id, 'Terminal_africa_rateid', true),
                        'shipment_id' => get_post_meta($order_id, 'Terminal_africa_shipment_id', true),
                        'pickup_time' => get_post_meta($order_id, 'Terminal_africa_pickuptime', true),
                        'carrier_logo' => get_post_meta($order_id, 'Terminal_africa_carrierlogo', true),
                        'status' => get_post_meta($order_id, 'Terminal_africa_status', true),
                    ],
                    "extra" => $order_data,
                ];
            }
            //response
            $response = [
                "status" => 200,
                "message" => "Orders fetched successfully",
                "meta" => [
                    "page" => $page ?: 1,
                    "limit" => $limit ?: 10,
                    "total" => count($total_orders)
                ],
                "data" => $orders_list,
            ];
            //return
            return new WP_REST_Response($response, 200);
        } catch (\Exception $e) {
            //response
            $response = [
                "status" => 500,
                "message" => "Error loading orders",
                "data" => $e->getMessage(),
            ];
            //return
            return new WP_REST_Response($response, 500);
        }
    }

    /**
     * Load single order
     * @param $order_id
     */
    public function loadOrder($order_id)
    {
        try {
            //get the order
            $order = wc_get_order($order_id);
            //check if the order is set
            if (!$order) {
                //response
                $response = [
                    "status" => 404,
                    "message" => "Order not found",
                    "data" => [],
                ];
                //return
                return new WP_REST_Response($response, 404);
            }
            //get the order data
            $order_data = $order->get_data();
            //get the order date
            $order_date = $order->get_date_created();
            //get the order date
            $order_date = $order_date->date('Y-m-d H:i:s');
            //get the products
            $items = $order->get_items();
            //products
            $products = [];
            //loop through the products
            foreach ($items as $product_id => $item) {
                //convert to int $product_id
                $product_id = intval($product_id);
                $products[] = [
                    "name" => $item->get_name(),
                    "quantity" => intval($item->get_quantity()) ?: 1,
                    "value" => $item->get_total(),
                    "description" => "{$item->get_quantity()} of {$item->get_name()} at {$item->get_total()} each for a total of {$item->get_total()}",
                    "type" => "parcel",
                    "currency" => get_woocommerce_currency(),
                    "weight" => (float)get_post_meta($product_id, '_weight', true) ?: 0.1
                ];
            }
            //check if order has Terminal_africa_shipment_id
            $shipment_id = get_post_meta($order_id, 'Terminal_africa_shipment_id', true);
            //orders list
            $orders_list[] = [
                "id" => $order_id,
                "shipment_id" => $shipment_id ?: "none",
                "products" => $products,
                "extra" => $order_data,
                'order_meta' => [
                    'carrier' => get_post_meta($order_id, 'Terminal_africa_carriername', true),
                    'amount' => get_post_meta($order_id, 'Terminal_africa_amount', true),
                    'duration' => get_post_meta($order_id, 'Terminal_africa_duration', true),
                    'rate_id' => get_post_meta($order_id, 'Terminal_africa_rateid', true),
                    'shipment_id' => get_post_meta($order_id, 'Terminal_africa_shipment_id', true),
                    'pickup_time' => get_post_meta($order_id, 'Terminal_africa_pickuptime', true),
                    'carrier_logo' => get_post_meta($order_id, 'Terminal_africa_carrierlogo', true),
                    'status' => get_post_meta($order_id, 'Terminal_africa_status', true),
                ],
            ];
            //response
            $response = [
                "status" => 200,
                "message" => "Single order fetched successfully",
                "data" => $orders_list,
            ];
            //return
            return new WP_REST_Response($response, 200);
        } catch (\Exception $e) {
            //response
            $response = [
                "status" => 500,
                "message" => "Error loading order",
                "data" => $e->getMessage(),
            ];
            //return
            return new WP_REST_Response($response, 500);
        }
    }

    /**
     * Update order status
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function update_order_status(WP_REST_Request $request)
    {
        try {
            //order id
            $order_id = $request->get_param('order_id');
            //sanitize order id
            $order_id = sanitize_text_field($order_id);
            //order status
            $order_status = $request->get_param('order_status');
            //sanitize order status
            $order_status = sanitize_text_field($order_status);
            //check if the order status is set
            if (!$order_status) {
                //response
                $response = [
                    "status" => 400,
                    "message" => "Order status is required",
                    "data" => [],
                ];
                //return
                return new WP_REST_Response($response, 400);
            }
            //check if the order status is allowed
            if (!in_array($order_status, self::$allowed_order_statuses)) {
                //response
                $response = [
                    "status" => 400,
                    "message" => "Order status is not allowed",
                    "data" => [],
                ];
                //return
                return new WP_REST_Response($response, 400);
            }
            //get the order
            $order = wc_get_order($order_id);
            //check if the order is set
            if ($order) {
                //update the order status
                $order->update_status($order_status);
                //add customer order note
                $order->add_order_note(
                    sprintf(
                        __('Order status changed to %s', 'terminal-africa'),
                        wc_get_order_status_name($order_status)
                    )
                );
                //save the order
                $order->save();
                //trigger the order status change
                do_action('woocommerce_order_status_' . $order_status, $order_id);
                //response
                $response = [
                    "status" => 200,
                    "message" => "Order status updated successfully",
                    "data" => $order->get_data(),
                ];
                //return
                return new WP_REST_Response($response, 200);
            }
            //response
            $response = [
                "status" => 404,
                "message" => "Order not found",
                "data" => [],
            ];
            //return
            return new WP_REST_Response($response, 404);
        } catch (\Exception $e) {
            //response
            $response = [
                "status" => 500,
                "message" => "Error updating order status",
                "data" => $e->getMessage(),
            ];
            //return
            return new WP_REST_Response($response, 500);
        }
    }

    /**
     * update_order_meta
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function update_order_meta(WP_REST_Request $request)
    {
        try {
            //order id
            $order_id = $request->get_param('order_id');
            //sanitize order id
            $order_id = sanitize_text_field($order_id);
            //order meta
            $order_meta = $request->get_param('order_meta');
            //sanitize array
            $order_meta = sanitize_array($order_meta);
            //check if the order meta is set
            if (!$order_meta && is_array($order_meta)) {
                //response
                $response = [
                    "status" => 400,
                    "message" => "Order meta is required",
                    "data" => [],
                ];
                //return
                return new WP_REST_Response($response, 400);
            }
            //get the order
            $order = wc_get_order($order_id);
            //check if the order is set
            if ($order) {
                //check if mode is live or test
                $mode = 'test';
                //check plugin mode
                if (self::$plugin_mode) {
                    $mode = self::$plugin_mode;
                }
                //get merchant id
                $terminal_africa_merchant_id = sanitize_text_field(get_option('terminal_africa_merchant_id'));
                //update order status
                //check if $order_meta['carrier'] is set
                if (isset($order_meta['carrier'])) {
                    update_post_meta($order_id, 'Terminal_africa_carriername', $order_meta['carrier']);
                    $order->update_meta_data('Terminal_africa_carriername', $order_meta['carrier']);
                }
                //check if $order_meta['amount'] is set
                if (isset($order_meta['amount'])) {
                    update_post_meta($order_id, 'Terminal_africa_amount', $order_meta['amount']);
                    $order->update_meta_data('Terminal_africa_amount', $order_meta['amount']);
                }
                //check if $order_meta['duration'] is set
                if (isset($order_meta['duration'])) {
                    update_post_meta($order_id, 'Terminal_africa_duration', $order_meta['duration']);
                    $order->update_meta_data('Terminal_africa_duration', $order_meta['duration']);
                }
                //check if $order_meta['rate_id'] is set
                if (isset($order_meta['rate_id'])) {
                    update_post_meta($order_id, 'Terminal_africa_rateid', $order_meta['rate_id']);
                    $order->update_meta_data('Terminal_africa_rateid', $order_meta['rate_id']);
                }
                //check if $order_meta['shipment_id'] is set
                if (isset($order_meta['shipment_id'])) {
                    update_post_meta($order_id, 'Terminal_africa_shipment_id', $order_meta['shipment_id']);
                    $order->update_meta_data('Terminal_africa_shipment_id', $order_meta['shipment_id']);
                }
                //check if $order_meta['pickup_time'] is set
                if (isset($order_meta['pickup_time'])) {
                    update_post_meta($order_id, 'Terminal_africa_pickuptime', $order_meta['pickup_time']);
                    $order->update_meta_data('Terminal_africa_pickuptime', $order_meta['pickup_time']);
                }
                //check if $order_meta['carrier_logo'] is set
                if (isset($order_meta['carrier_logo'])) {
                    update_post_meta($order_id, 'Terminal_africa_carrierlogo', $order_meta['carrier_logo']);
                    $order->update_meta_data('Terminal_africa_carrierlogo', $order_meta['carrier_logo']);
                }
                //check if $order_meta['status'] is set
                if (isset($order_meta['status'])) {
                    update_post_meta($order_id, 'Terminal_africa_status', $order_meta['status']);
                    $order->update_meta_data('Terminal_africa_status', $order_meta['status']);
                }
                update_post_meta($order_id, 'Terminal_africa_merchant_id', $terminal_africa_merchant_id);

                $order->update_meta_data('Terminal_africa_merchant_id', $terminal_africa_merchant_id);

                update_post_meta($order_id, 'Terminal_africa_mode', $mode);

                $order->update_meta_data('Terminal_africa_mode', $mode);

                //update through api
                update_post_meta($order_id, 'Terminal_africa_api_ping', "yes");

                $order->update_meta_data('Terminal_africa_api_ping', "yes");
                //save the order
                $order->save();
                //response
                $response = [
                    "status" => 200,
                    "message" => "Order meta updated successfully",
                    "data" => $order_meta,
                ];
                //return
                return new WP_REST_Response($response, 200);
            }
            //response
            $response = [
                "status" => 404,
                "message" => "Order not found",
                "data" => [],
            ];
            //return
            return new WP_REST_Response($response, 404);
        } catch (\Exception $e) {
            //response
            $response = [
                "status" => 500,
                "message" => "Error updating order meta",
                "data" => $e->getMessage(),
            ];
            //return
            return new WP_REST_Response($response, 500);
        }
    }

    /**
     * Deactivate the plugin
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function deactivateThePlugin(WP_REST_Request $request)
    {
        try {
            self::deactivate();
            //deactivate the plugin
            deactivate_plugins(TERMINAL_AFRICA_PLUGIN_BASENAME);
            //response
            $response = [
                "status" => 200,
                "message" => "Plugin deactivated successfully",
                "data" => [],
            ];
            //return
            return new WP_REST_Response($response, 200);
        } catch (\Exception $e) {
            //response
            $response = [
                "status" => 500,
                "message" => "Error deactivating plugin",
                "data" => $e->getMessage(),
            ];
            //return
            return new WP_REST_Response($response, 500);
        }
    }

    /**
     * Update user settings
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function update_user_settings(WP_REST_Request $request)
    {
        try {
            $instance = TerminalAfricaShippingPlugin::instance();
            //send request
            $response = Requests::get($instance::$enpoint . "users/secrete", [
                'Authorization' => 'Bearer ' . $instance::$skkey
            ]);

            //check if response is 200
            $body = json_decode($response->body);
            if ($response->status_code == 200) {
                //save keys
                $settings = array(
                    'public_key' => $instance::$public_key,
                    'secret_key' => $instance::$skkey,
                    'user_id' => $body->data->user->user_id,
                    'others' => $body->data
                );

                // Save settings
                update_option('terminal_africa_settings', $settings);

                //send message
                return new WP_REST_Response(
                    [
                        'code' => 200,
                        'message' => 'User settings updated successfully'
                    ],
                    200
                );
            }

            //send error message
            return new WP_REST_Response([
                'code' => 400,
                'message' => "Something went wrong: " . $body->message
            ], 400);
        } catch (\Exception $e) {
            //response
            $response = [
                "status" => 500,
                "message" => "Error updating user settings",
                "data" => $e->getMessage(),
            ];
            //return
            return new WP_REST_Response($response, 500);
        }
    }
}
