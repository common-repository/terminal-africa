<?php

namespace App\Terminal\Core;

//security
defined('ABSPATH') or die('No script kiddies please!');
/**
 * Terminal Core
 * @package App\Terminal\Core
 * @since 1.0.0
 * @version 1.0.0
 * @author Terminal Africa
 */
class TerminalCore
{
    //get wc orders where shipment_id exist in metal
    public function get_orders()
    {
        global $terminal_allowed_order_statuses;

        try {
            //get query var terminal_page
            $terminal_page = terminal_param('terminal_page', 1);
            //get terminal africa merchant id
            $terminal_africa_merchant_id = sanitize_text_field(get_option('terminal_africa_merchant_id'));
            //check if mode is live or test
            $mode = 'test';
            //check if class exist TerminalAfricaShippingPlugin
            if (class_exists('TerminalAfricaShippingPlugin')) {
                $TerminalAfricaShippingPlugin = \TerminalAfricaShippingPlugin::instance();
                if ($TerminalAfricaShippingPlugin::$plugin_mode) {
                    $mode = $TerminalAfricaShippingPlugin::$plugin_mode;
                }
            }
            $args = [
                'status' => array_merge(['wc-processing', 'completed', 'on-hold', 'pending'], $terminal_allowed_order_statuses),
                'limit' => 10,
                'paginate' => true,
                'page' => $terminal_page,
                'meta_key' => 'Terminal_africa_shipment_id',
                'meta_compare' => 'EXISTS',
                'meta_query' => [
                    'relation' => 'AND',
                    [
                        'key' => 'Terminal_africa_rateid',
                        'compare' => 'EXISTS',
                    ],
                    [
                        'key' => 'Terminal_africa_merchant_id',
                        'value' => $terminal_africa_merchant_id,
                        'compare' => '=',
                    ],
                    [
                        'key' => 'Terminal_africa_mode',
                        'value' => $mode,
                        'compare' => '=',
                    ],
                ],
            ];

            $orders = wc_get_orders($args);

            return $orders ?: [];
        } catch (\Exception $e) {
            logTerminalError($e);
            return [];
        }
    }

    //getActiveCarrier
    public function getActiveCarrier($carrier_id, $carriers_array_obj, $type)
    {
        try {
            //check if $carriers_array_obj code is not 200
            if ($carriers_array_obj['code'] != 200) {
                return false;
            }
            //get carriers_array_obj
            $carriers_array_obj = $carriers_array_obj['data'];
            //check if carrier_id is in carriers_array_obj
            $carrier = array_filter($carriers_array_obj, function ($carrier) use ($carrier_id) {
                return $carrier->carrier_id == $carrier_id;
            });
            //if carrier_id is in carriers_array_obj
            if ($carrier) {
                //get carrier
                $carrier = array_values($carrier)[0];
                //check if carrier has type enabled
                if ($carrier->{$type}) {
                    //return carrier
                    return true;
                }
            }
            //return false
            return false;
        } catch (\Exception $e) {
            logTerminalError($e);
            return false;
        }
    }

    //sanitize_array
    public function sanitize_array($array)
    {
        try {
            //check if array is not empty
            if (!empty($array)) {
                //loop through array
                foreach ($array as $key => $value) {
                    //check if value is array
                    if (is_array($array)) {
                        //sanitize array
                        $array[$key] = is_array($value) ? $this->sanitize_array($value) : $this->sanitizeDynamic($value);
                    } else {
                        //check if $array is object
                        if (is_object($array)) {
                            //sanitize object
                            $array->$key = $this->sanitizeDynamic($value);
                        } else {
                            //sanitize mixed
                            $array[$key] = $this->sanitizeDynamic($value);
                        }
                    }
                }
            }
            //return array
            return $array;
        } catch (\Exception $e) {
            logTerminalError($e);
            return [];
        }
    }

    //sanitize_object
    public function sanitize_object($object)
    {
        //check if object is not empty
        if (!empty($object)) {
            //loop through object
            foreach ($object as $key => $value) {
                //check if value is array
                if (is_array($value)) {
                    //sanitize array
                    $object->$key = $this->sanitize_array($value);
                } else {
                    //sanitize mixed
                    $object->$key = $this->sanitizeDynamic($value);
                }
            }
        }
        //return object
        return $object;
    }

    //dynamic sanitize
    public function sanitizeDynamic($data)
    {
        $type = gettype($data);
        switch ($type) {
            case 'array':
                return $this->sanitize_array($data);
                break;
            case 'object':
                return $this->sanitize_object($data);
                break;
            default:
                return sanitize_text_field($data);
                break;
        }
    }

    /**
     * Error logs
     * @param \Exception $e
     * @param string $endpoint
     * @return bool
     */
    public function logTerminalError($e, $endpoint = 'none')
    {
        try {
            $errorMessage = $e->getMessage();
            //check if error message matched 'Operation timed out'
            if (strpos($errorMessage, 'Operation timed out') !== false) {
                //return true
                return true;
            }
            //get merchant id
            $terminal_africa_merchant_id = get_option('terminal_africa_merchant_id');
            //confirm if endpoint is url
            if (filter_var($endpoint, FILTER_VALIDATE_URL)) {
                //extract the parsed parameters on the endoint
                $url_components = parse_url($endpoint);
                parse_str($url_components['query'], $body);
                //get url 
                $endpoint = $url_components['path'];
            } else {
                $body = [];
            }
            //request to terminal africa api
            $response = wp_remote_post('https://api.terminal.africa/v1/error-log', [
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
                'body' => json_encode([
                    'endpoint' => $endpoint,
                    'data_sent' => $body,
                    'page' => $_SERVER['REQUEST_URI'],
                    'user_id' => $terminal_africa_merchant_id,
                    'platform' => 'wordpress',
                    'metadata' => [
                        'error' => $e->getMessage(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                        'admin_email' => get_option('admin_email'),
                        'site_url' => get_site_url(),
                        'site_name' => get_bloginfo('name'),
                        'site_description' => get_bloginfo('description'),
                    ],
                ]),
            ]);
            //check if response is not an error
            if (is_wp_error($response)) {
                //store
                error_log($response->get_error_message());
            }
            //check if response is not 200
            if (wp_remote_retrieve_response_code($response) != 200) {
                //store
                error_log(wp_remote_retrieve_response_message($response));
            }
            //return true
            return true;
        } catch (\Exception $e) {
            //log error
            error_log($e->getMessage());
            return false;
        }
    }

    /**
     * Error logs for non exceptions data
     * @param string $endpoint
     * @return bool
     */
    public function logTerminalErrorData($data, $endpoint = 'none')
    {
        try {
            //get merchant id
            $terminal_africa_merchant_id = get_option('terminal_africa_merchant_id');
            //confirm if endpoint is url
            if (filter_var($endpoint, FILTER_VALIDATE_URL)) {
                //extract the parsed parameters on the endoint
                $url_components = parse_url($endpoint);
                parse_str($url_components['query'], $body);
                //get url 
                $endpoint = $url_components['path'];
            } else {
                $body = [];
            }
            //request to terminal africa api
            $response = wp_remote_post('https://api.terminal.africa/v1/error-log', [
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
                'body' => json_encode([
                    'endpoint' => $endpoint,
                    'data_sent' => $body,
                    'page' => $_SERVER['REQUEST_URI'],
                    'user_id' => $terminal_africa_merchant_id,
                    'platform' => 'wordpress',
                    'metadata' => [
                        'response_body' => $data,
                        'admin_email' => get_option('admin_email'),
                        'site_url' => get_site_url(),
                        'site_name' => get_bloginfo('name'),
                        'site_description' => get_bloginfo('description'),
                    ],
                ]),
            ]);
            //check if response is not an error
            if (is_wp_error($response)) {
                //store
                error_log($response->get_error_message());
            }
            //check if response is not 200
            if (wp_remote_retrieve_response_code($response) != 200) {
                //store
                error_log(wp_remote_retrieve_response_message($response));
            }
            //return true
            return true;
        } catch (\Exception $e) {
            //log error
            error_log($e->getMessage());
            return false;
        }
    }
}
