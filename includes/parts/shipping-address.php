<?php

namespace TerminalAfrica\Includes\Parts;

//security
defined('ABSPATH') or die('No script kiddies please!');

use \WpOrg\Requests\Requests;

/**
 * Shipping Engine Module
 * 
 * @package TerminalAfrica\Includes\Parts
 * @version 1.0.0
 * @author Terminal Africa <support@terminal.africa>
 * @since 1.0.0
 * @category Shipping
 * @license GPL-2.0+
 * @copyright 2024 Terminal Africa
 * @link https://terminal.africa
 */
trait Shipping
{
    /**
     * Validate Terminal Rate
     * @param string $rateid
     * @param string $order_id
     * @param string $shipment_id
     * @return mixed
     * @link https://api.terminal.africa/v1/rates/:rate_id
     */
    public static function validateTerminalRate($rateid, $order_id, $shipment_id)
    {
        try {
            //get rate 
            $response = Requests::get(self::$enpoint . 'rates/' . $rateid, [
                'Authorization' => 'Bearer ' . self::$skkey,
            ] + self::$request_header);
            //decode body
            $body = json_decode($response->body);

            //get merchant default address
            $terminal_africa_merchant_address = get_option('terminal_africa_merchant_address', (object)[]);
            //get country
            $country = $terminal_africa_merchant_address->country;
            //get state
            $state = $terminal_africa_merchant_address->state;
            //get address_id
            $merchant_address_id = get_option('terminal_africa_merchant_address_id');
            //get carrier_slug from rate response
            $carrier_slug = $body->data->carrier_slug;
            //check if response is ok
            if ($response->status_code == 200) {
                //check if data has dropoff_required and is true
                if (isset($body->data->dropoff_required) && $body->data->dropoff_required) {
                    //get dropoff locations https://api.terminal.africa/v1/locations/drop-off
                    $dropoff_query_params = [
                        "country" => $country,
                        "state" => $state,
                        "address_id" => $merchant_address_id,
                        "carrier" => $carrier_slug,
                    ];
                    //get dropoff locations
                    $dropoff_locations = self::get_dropoff_locations($dropoff_query_params);
                    //return dropoff_locations
                    return $dropoff_locations;
                } else {
                    //return dropoff_locations
                    return [
                        'code' => 402,
                        'message' => 'no dropoff required',
                        'data' => [],
                    ];
                }
            }
            //logTerminalErrorData
            logTerminalErrorData($response->body, self::$enpoint . 'rates/' . $rateid);
            return [
                'code' => $response->status_code,
                'message' => $body->message,
                'data' => [],
            ];
        } catch (\Exception $e) {
            logTerminalError($e, 'validateTerminalRate');
            return [
                'code' => 500,
                'message' => $e->getMessage(),
                'data' => [],
            ];
        }
    }

    /*
    * Get Dropoff Locations
    * @param array $query_params
    * @return array
    */
    public static function get_dropoff_locations($query_params)
    {
        try {
            //get dropoff locations
            $response = Requests::get(self::$enpoint . 'carriers/locations/drop-off?' . http_build_query($query_params), [
                'Authorization' => 'Bearer ' . self::$skkey,
            ] + self::$request_header);
            //decode body
            $body = json_decode($response->body);
            //check if response is ok
            if ($response->status_code == 200) {
                //return dropoff locations
                return [
                    'code' => 200,
                    'message' => 'success',
                    'data' => $body->data,
                ];
            }
            //logTerminalErrorData
            logTerminalErrorData($response->body, self::$enpoint . 'carriers/locations/drop-off?' . http_build_query($query_params));
            return [
                'code' => $response->status_code,
                'message' => $body->message,
                'data' => [],
            ];
        } catch (\Exception $e) {
            logTerminalError($e, self::$enpoint . 'carriers/locations/drop-off?' . http_build_query($query_params));
            return [
                'code' => 500,
                'message' => $e->getMessage(),
                'data' => [],
            ];
        }
    }

    //get countries
    public static function get_countries()
    {
        try {
            //check if self::$skkey
            if (!self::$skkey) {
                return [];
            }

            //check if session has started
            if (!session_id()) {
                session_start();
            }

            //check if session has terminal_africa_countries_cache_data
            if (isset($_SESSION['terminal_africa_countries_cache_data'])) {
                //return countries
                return $_SESSION['terminal_africa_countries_cache_data'];
            }

            //check if terminal_africa_countries is set
            if ($data = get_option('terminal_africa_countries')) {
                //save data to session
                $_SESSION['terminal_africa_countries_cache_data'] = $data;
                //return countries
                return $data;
            }
            //get countries
            $response  = Requests::get(self::$enpoint . 'countries', [
                'Authorization' => 'Bearer ' . self::$skkey,
            ] + self::$request_header);
            //check if response is ok
            if ($response->status_code == 200) {
                //return countries
                $body = json_decode($response->body);
                $data = $body->data;
                //save data to session
                $_SESSION['terminal_africa_countries_cache_data'] = $data;
                //save raw data
                update_option('terminal_africa_countries', $data);
                //return data
                return $data;
            }
            //logTerminalErrorData
            // logTerminalErrorData($response->body, self::$enpoint . 'countries');
            return [];
        } catch (\Exception $e) {
            logTerminalError($e, self::$enpoint . 'countries');
            return [];
        }
    }

    //get states
    public static function get_states($countryCode = "NG")
    {
        try {
            //check if self::$skkey
            if (!self::$skkey) {
                return [
                    'code' => 404,
                    'message' => "Invalid API Key",
                    'data' => [],
                ];
            }

            //check if session has started
            if (!session_id()) {
                session_start();
            }

            //check if session is set
            if (isset($_SESSION['terminal_africa_state_cache_data'][$countryCode])) {
                //return from cache
                return [
                    'code' => 200,
                    'message' => 'success',
                    'data' => $_SESSION['terminal_africa_state_cache_data'][$countryCode]
                ];
            }

            //check if terminal_africa_states.$countryCode is set
            if ($data = get_option('terminal_africa_states' . $countryCode)) {
                //save data to session
                $_SESSION['terminal_africa_state_cache_data'][$countryCode] = $data;
                //return countries
                return [
                    'code' => 200,
                    'message' => 'success',
                    'data' => $data,
                ];
            }
            //get countries
            $response = Requests::get(self::$enpoint . 'states?country_code=' . $countryCode, [
                'Authorization' => 'Bearer ' . self::$skkey,
                'Content-Type' => 'application/json'
            ] + self::$request_header);
            $body = json_decode($response->body);
            //check if response is ok
            if ($response->status_code == 200) {
                //return countries
                $data = $body->data;
                //save data to session
                $_SESSION['terminal_africa_state_cache_data'][$countryCode] = $data;
                //save raw data
                update_option('terminal_africa_states' . $countryCode, $data);
                //return data
                return [
                    'code' => 200,
                    'message' => 'success',
                    'data' => $data,
                ];
            } else {
                //logTerminalErrorData
                logTerminalErrorData($response->body, self::$enpoint . 'states?country_code=' . $countryCode);
                //
                return [
                    'code' => $response->status_code,
                    'message' => $body->message,
                    'data' => [],
                ];
            }
        } catch (\Exception $e) {
            logTerminalError($e, self::$enpoint . 'states?country_code=' . $countryCode);
            return [
                'code' => 500,
                'message' => $e->getMessage(),
                'data' => [],
            ];
        }
    }

    //get cities
    public static function get_cities($countryCode = "NG", $state_code = "LA")
    {
        try {
            //if session is not started start it
            if (session_status() == PHP_SESSION_NONE) {
                session_start();
            }
            //check if terminal_africa_cities.$countryCode.$state_code is set
            if (isset($_SESSION['terminal_africa_cities'][$countryCode][$state_code])) {
                //return countries
                return [
                    'code' => 200,
                    'message' => 'success',
                    'data' => sanitize_array($_SESSION['terminal_africa_cities'][$countryCode][$state_code]),
                ];
            }
            //check if self::$skkey
            if (!self::$skkey) {
                return [
                    'code' => 404,
                    'message' => "Invalid API Key",
                    'data' => [],
                ];
            }
            //sanitize countryCode
            $countryCode = sanitize_text_field($countryCode);
            //sanitize state_code
            $state_code = sanitize_text_field($state_code);
            $query = [
                'country_code' => $countryCode,
                'state_code' => $state_code,
            ];
            //query builder
            $query = http_build_query($query);
            //get cities
            $response = Requests::get(self::$enpoint . 'cities?' . $query, [
                'Authorization' => 'Bearer ' . self::$skkey,
            ] + self::$request_header);
            $body = json_decode($response->body);
            //check if response is ok
            if ($response->status_code == 200) {
                //return countries
                $data = $body->data;
                // save to session
                $_SESSION['terminal_africa_cities'][$countryCode][$state_code] = $data;
                //return data
                return [
                    'code' => 200,
                    'message' => 'success',
                    'data' => $data,
                ];
            } else {
                //logTerminalErrorData
                logTerminalErrorData($response->body, self::$enpoint . 'cities?' . http_build_query([
                    'country_code' => $countryCode,
                    'state_code' => $state_code,
                ]));
                return [
                    'code' => $response->status_code,
                    'message' => $body->message,
                    'data' => [],
                ];
            }
        } catch (\Exception $e) {
            logTerminalError($e, self::$enpoint . 'cities?' . http_build_query([
                'country_code' => $countryCode,
                'state_code' => $state_code,
            ]));
            return [
                'code' => 500,
                'message' => $e->getMessage(),
                'data' => [],
            ];
        }
    }

    //woocommerce_countries
    public static function woocommerce_countries($countries)
    {
        try {
            //get countries
            $countries_raw = self::get_countries();
            //check if countries is not empty
            if (!empty($countries_raw)) {
                //empty countries
                $countries = [];
                //loop through countries
                foreach ($countries_raw as $country) {
                    //add country to countries array
                    $countries[$country->isoCode] = $country->name . ' (' . $country->isoCode . ')';
                }
            }
            return $countries;
        } catch (\Exception $e) {
            logTerminalError($e);
            return $countries;
        }
    }

    //woocommerce_states
    public static function woocommerce_states($states)
    {
        try {
            //get array key
            $countryCode = array_key_first($states);
            //get states
            $states_raw = self::get_states($countryCode);
            //check if states is not empty
            if ($states_raw['code'] != 200) {
                //return states
                return $states;
            }
            //empty states
            $states_d = [];
            //check if states is not empty
            if (!empty($states_raw['data'])) {
                //loop through states
                foreach ($states_raw['data'] as $state) {
                    //add state to states array
                    $states_d[$state->isoCode] = $state->name;
                    //update countryCode
                    $countryCode = $state->countryCode;
                }
            }
            $states[$countryCode] = $states_d;
            return $states;
        } catch (\Exception $e) {
            logTerminalError($e);
            return $states;
        }
    }

    /**
     * Get Terminal Address Data
     * @param string $address_id
     * @return mixed
     */
    public static function getAddressById($address_id)
    {
        try {
            if (!self::$skkey) {
                return [
                    'code' => 404,
                    'message' => "Invalid API Key",
                    'data' => [],
                ];
            }
            //get address
            $response  = Requests::get(self::$enpoint . 'addresses/' . $address_id, [
                'Authorization' => 'Bearer ' . self::$skkey,
            ] + self::$request_header);
            $body = json_decode($response->body);
            //check if response is ok
            if ($response->status_code == 200) {
                //return address data
                $data = $body->data;
                //return data
                return [
                    'code' => 200,
                    'message' => 'success',
                    'data' => $data,
                ];
            }
            //logTerminalErrorData
            logTerminalErrorData($response->body, self::$enpoint . 'addresses/' . $address_id);
            return [
                'code' => 404,
                'message' => $body->message,
                'data' => [],
            ];
        } catch (\Exception $e) {
            logTerminalError($e, self::$enpoint . 'addresses?id=' . $address_id);
            //return
            return [
                'code' => 500,
                'message' => $e->getMessage(),
                'data' => [],
            ];
        }
    }

    //create address
    public static function createAddress($first_name, $last_name, $email, $phone, $line_1, $line_2, $city, $state, $country, $zip_code)
    {
        try {
            if (!self::$skkey) {
                return [
                    'code' => 404,
                    'message' => "Invalid API Key",
                    'data' => [],
                ];
            }

            //address fields
            $addressFields = [
                'first_name' => $first_name,
                'last_name' => $last_name,
                'email' => $email,
                'phone' => $phone,
                'line1' => $line_1,
                'line2' => $line_2,
                'city' => $city,
                'state' => $state,
                'country' => $country,
                'zip' => $zip_code,
            ];

            //check the address fields and remove empty fields
            foreach ($addressFields as $key => $value) {
                if (empty($value)) {
                    //set --
                    $addressFields[$key] = '--';
                }
                // //check key phone and count the number
                // if ($key == 'phone') {
                //     //count the value and remove is length is less 6
                //     if (strlen($value) < 6) {
                //         unset($addressFields[$key]);
                //     }
                // }
            }

            $response = Requests::post(
                self::$enpoint . 'addresses',
                [
                    'Authorization' => 'Bearer ' . self::$skkey,
                    'Content-Type' => 'application/json'
                ] + self::$request_header,
                json_encode($addressFields),
                //time out 60 seconds
                ['timeout' => 60]
            );
            $body = json_decode($response->body);
            //check if response is ok
            if ($response->status_code == 200) {
                //return countries
                $data = $body->data;
                //return data
                return [
                    'code' => 200,
                    'message' => 'success',
                    'data' => $data,
                ];
            } else {
                //logTerminalErrorData
                logTerminalErrorData($response->body, self::$enpoint . 'addresses?' . http_build_query($addressFields));
                return [
                    'code' => $response->status_code,
                    'message' => $body->message,
                    'data' => [],
                ];
            }
        } catch (\Exception $e) {
            logTerminalError($e, self::$enpoint . 'addresses?' . http_build_query([
                'first_name' => $first_name,
                'last_name' => $last_name,
                'email' => $email,
                'phone' => $phone,
                'line1' => $line_1,
                'line2' => $line_2,
                'city' => $city,
                'state' => $state,
                'country' => $country,
                'zip' => $zip_code,
            ]));
            return [
                'code' => 500,
                'message' => $e->getMessage(),
                'data' => [],
            ];
        }
    }

    //update address
    public static function updateAddress($merchant_address_id, $first_name, $last_name, $email, $phone, $line_1, $line_2, $city, $state, $country, $zip_code)
    {
        try {
            if (!self::$skkey) {
                return [
                    'code' => 404,
                    'message' => "Invalid API Key",
                    'data' => [],
                ];
            }
            //check if merchant_address_id is empty
            if (empty($merchant_address_id)) {
                return [
                    'code' => 404,
                    'message' => "Invalid merchant_address_id",
                    'data' => [],
                ];
            }

            //address fields 
            $addressFields = [
                'first_name' => $first_name,
                'last_name' => $last_name,
                'email' => $email,
                'phone' => $phone,
                'line1' => $line_1,
                'line2' => $line_2,
                'city' => $city,
                'state' => $state,
                'country' => $country,
                'zip' => $zip_code,
            ];

            //check the address fields and remove empty fields
            foreach ($addressFields as $key => $value) {
                if (empty($value)) {
                    //set --
                    $addressFields[$key] = '--';
                }
                //check key phone and count the number
                // if ($key == 'phone') {
                //     //count the value and remove is length is less 6
                //     if (strlen($value) < 6) {
                //         unset($addressFields[$key]);
                //     }
                // }
            }

            //request 
            $response = Requests::put(
                self::$enpoint . 'addresses/' . $merchant_address_id,
                [
                    'Authorization' => 'Bearer ' . self::$skkey,
                    'Content-Type' => 'application/json'
                ] + self::$request_header,
                json_encode($addressFields), //time out 60 seconds
                ['timeout' => 60]
            );
            //decode response
            $body = json_decode($response->body);
            //check if response is ok
            if ($response->status_code == 200) {
                //return countries
                $data = $body->data;
                //return data
                return [
                    'code' => 200,
                    'message' => 'success',
                    'data' => $data,
                ];
            } else {
                //logTerminalErrorData
                logTerminalErrorData($response->body, self::$enpoint . 'addresses/' . $merchant_address_id . '?' . http_build_query($addressFields));
                return [
                    'code' => $response->status_code,
                    'message' => $body->message,
                    'data' => [],
                ];
            }
        } catch (\Exception $e) {
            logTerminalError($e, self::$enpoint . 'addresses/' . $merchant_address_id . '?' . http_build_query([
                'first_name' => $first_name,
                'last_name' => $last_name,
                'email' => $email,
                'phone' => $phone,
                'line1' => $line_1,
                'line2' => $line_2,
                'city' => $city,
                'state' => $state,
                'country' => $country,
                'zip' => $zip_code,
            ]));
            return [
                'code' => 500,
                'message' => $e->getMessage(),
                'data' => [],
            ];
        }
    }

    /**
     * Get Address
     */
    public static function getAddresses($perPage = 25, $page = 1, $search = '')
    {
        try {
            if (!self::$skkey) {
                return [
                    'code' => 404,
                    'message' => "Invalid API Key",
                    'data' => [],
                ];
            }

            //if session is not started start it
            if (session_status() == PHP_SESSION_NONE) {
                session_start();
            }

            //get plugin mode
            $plugin_mode = self::$plugin_mode;

            //check if terminal_africa_addresses is set
            if (isset($_SESSION['terminal_africa_addresses'][$plugin_mode][$page . $perPage . $search])) {
                //return countries
                return [
                    'code' => 200,
                    'message' => 'success',
                    'data' => sanitize_array($_SESSION['terminal_africa_addresses'][$plugin_mode][$page . $perPage . $search]),
                ];
            }

            //param
            $param = [
                'perPage' => $perPage,
                'page' => $page,
            ];

            //check if search is not empty
            if (!empty($search)) {
                $param['search'] = $search;
            }

            //build query
            $query = http_build_query($param);

            //get address
            $response  = Requests::get(self::$enpoint . 'addresses?' . $query, [
                'Authorization' => 'Bearer ' . self::$skkey,
            ] + self::$request_header);
            $body = json_decode($response->body);
            //check if response is ok
            if ($response->status_code == 200) {
                //return address data
                $data = $body->data;
                //set session
                $_SESSION['terminal_africa_addresses'][$plugin_mode][$page . $perPage . $search] = $data;
                //return data
                return [
                    'code' => 200,
                    'message' => 'success',
                    'data' => $data,
                ];
            }
            //logTerminalErrorData
            logTerminalErrorData($response->body, self::$enpoint . 'addresses');
            return [
                'code' => 404,
                'message' => $body->message,
                'data' => [],
            ];
        } catch (\Exception $e) {
            logTerminalError($e, self::$enpoint . 'addresses');
            //return
            return [
                'code' => 500,
                'message' => $e->getMessage(),
                'data' => [],
            ];
        }
    }

    //create parcel
    public static function createParcel($body)
    {
        try {
            if (!self::$skkey) {
                return [
                    'code' => 404,
                    'message' => "Invalid API Key",
                    'data' => [],
                ];
            }

            $response = Requests::post(
                self::$enpoint . 'parcels',
                [
                    'Authorization' => 'Bearer ' . self::$skkey,
                    'Content-Type' => 'application/json'
                ] + self::$request_header,
                json_encode(
                    $body
                ),
                //time out 60 seconds
                ['timeout' => 60]
            );
            $body = json_decode($response->body);
            //check if response is ok
            if ($response->status_code == 200) {
                //return countries
                $data = $body->data;
                //return data
                return [
                    'code' => 200,
                    'message' => 'success',
                    'data' => $data,
                ];
            } else {
                //logTerminalErrorData
                logTerminalErrorData($response->body, self::$enpoint . 'parcels' . "?" . http_build_query($body));
                //
                return [
                    'code' => $response->status_code,
                    'message' => $body->message,
                    'data' => [],
                ];
            }
        } catch (\Exception $e) {
            logTerminalError($e, self::$enpoint . 'parcels' . "?" . http_build_query($body));
            return [
                'code' => 500,
                'message' => $e->getMessage(),
                'data' => [],
            ];
        }
    }

    //update parcel
    public static function updateParcel($parcel_id, $body)
    {
        try {
            if (!self::$skkey) {
                return [
                    'code' => 404,
                    'message' => "Invalid API Key",
                    'data' => [],
                ];
            }

            $response = Requests::put(
                self::$enpoint . 'parcels' . "/" . $parcel_id,
                [
                    'Authorization' => 'Bearer ' . self::$skkey,
                    'Content-Type' => 'application/json'
                ] + self::$request_header,
                json_encode(
                    $body
                ),
                //time out 60 seconds
                ['timeout' => 60]
            );
            $body = json_decode($response->body);
            //check if response is ok
            if ($response->status_code == 200) {
                //return countries
                $data = $body->data;
                //return data
                return [
                    'code' => 200,
                    'message' => 'success',
                    'data' => $data,
                ];
            } else {
                //logTerminalErrorData
                logTerminalErrorData($response->body, self::$enpoint . 'parcels' . "/" . $parcel_id . "?" . http_build_query($body));
                return [
                    'code' => $response->status_code,
                    'message' => $body->message,
                    'data' => [],
                ];
            }
        } catch (\Exception $e) {
            logTerminalError($e, self::$enpoint . 'parcels' . "/" . $parcel_id . "?" . http_build_query($body));
            return [
                'code' => 500,
                'message' => $e->getMessage(),
                'data' => [],
            ];
        }
    }

    //get parcel
    public static function getParcel($parcel_id)
    {
        try {
            if (!self::$skkey) {
                return [
                    'code' => 404,
                    'message' => "Invalid API Key",
                    'data' => [],
                ];
            }

            $response = Requests::get(
                self::$enpoint . 'parcels' . "/" . $parcel_id,
                [
                    'Authorization' => 'Bearer ' . self::$skkey,
                    'Content-Type' => 'application/json'
                ] + self::$request_header,
                //time out 60 seconds
                ['timeout' => 60]
            );
            $body = json_decode($response->body);
            //check if response is ok
            if ($response->status_code == 200) {
                //return countries
                $data = $body->data;
                //return data
                return [
                    'code' => 200,
                    'message' => 'success',
                    'data' => $data,
                ];
            } else {
                //logTerminalErrorData
                logTerminalErrorData($response->body, self::$enpoint . 'parcels' . "/" . $parcel_id);
                return [
                    'code' => $response->status_code,
                    'message' => $body->message,
                    'data' => [],
                ];
            }
        } catch (\Exception $e) {
            logTerminalError($e, self::$enpoint . 'parcels' . "/" . $parcel_id);
            return [
                'code' => 500,
                'message' => $e->getMessage(),
                'data' => [],
            ];
        }
    }

    //createShipment
    public static function createShipment($address_from, $address_to, $parcel_id, $order_id)
    {
        try {
            if (!self::$skkey) {
                return [
                    'code' => 404,
                    'message' => "Invalid API Key",
                    'data' => [],
                ];
            }

            //site url
            $site_url = site_url();
            //get the domain
            $domain = parse_url($site_url, PHP_URL_HOST);

            //get order rate_id meta
            $plugin_rate_id = get_post_meta($order_id, 'Terminal_africa_rateid', true);

            $response = Requests::post(
                self::$enpoint . 'shipments',
                [
                    'Authorization' => 'Bearer ' . self::$skkey,
                    'Content-Type' => 'application/json'
                ] + self::$request_header,
                json_encode(
                    [
                        'address_from' => $address_from,
                        'address_to' => $address_to,
                        'parcel' => $parcel_id,
                        'metadata' => [
                            'domain' => $domain,
                            'order_id' => $order_id,
                            'plugin_rate_id' => $plugin_rate_id
                        ],
                        'source' => 'wordpress'
                    ]
                ),
                //time out 60 seconds
                ['timeout' => 60]
            );
            $body = json_decode($response->body);
            //check if response is ok
            if ($response->status_code == 200) {
                //return countries
                $data = $body->data;
                //return data
                return [
                    'code' => 200,
                    'message' => 'success',
                    'data' => $data,
                ];
            } else {
                //logTerminalErrorData
                logTerminalErrorData($response->body, self::$enpoint . 'shipments' . "?" . http_build_query([
                    'address_from' => $address_from,
                    'address_to' => $address_to,
                    'parcel' => $parcel_id,
                    'source' => 'wordpress'
                ]));
                return [
                    'code' => $response->status_code,
                    'message' => $body->message,
                    'data' => [],
                ];
            }
        } catch (\Exception $e) {
            logTerminalError($e, self::$enpoint . 'shipments' . "?" . http_build_query([
                'address_from' => $address_from,
                'address_to' => $address_to,
                'parcel' => $parcel_id,
                'source' => 'wordpress'
            ]));
            return [
                'code' => 500,
                'message' => $e->getMessage(),
                'data' => [],
            ];
        }
    }

    //getTerminalRates
    public static function getTerminalRates($shipment_id, $merchant_address_id = null, $customer_address_id = null, $parcel = null)
    {
        try {
            if (!self::$skkey) {
                return [
                    'code' => 404,
                    'message' => "Invalid API Key",
                    'data' => [],
                ];
            }
            //get woocommerce currency
            $currency = get_woocommerce_currency();
            //allowed currencies
            /*
                NGN. Available options are AED, AUD, CAD, CNY, EUR, GBP, GHS, HKD, KES, NGN, TZS, UGX, USD, ZAR.
            */
            $allowed_currencies =  ['AED', 'AUD', 'CAD', 'CNY', 'EUR', 'GBP', 'GHS', 'HKD', 'KES', 'NGN', 'TZS', 'UGX', 'USD', 'ZAR'];
            //check if currency is allowed
            if (!in_array($currency, $allowed_currencies)) {
                //set default usd
                $currency = 'USD';
            }
            //site url
            $site_url = site_url();
            //get the domain
            $domain = parse_url($site_url, PHP_URL_HOST);
            //query
            $query = [
                'currency' => $currency,
                'source' => "wordpress",
                'domain' => $domain
            ];
            //check if merchant address id is set
            if ($merchant_address_id && $customer_address_id && $parcel) {
                $query['pickup_address'] = $merchant_address_id;
                $query['delivery_address'] = $customer_address_id;
                $query['parcel_id'] = $parcel;
            } else {
                //set shipment id
                $query['shipment_id'] = $shipment_id;
            }

            //check if shipment insurance is set
            $shipmentInsurance = get_option('update_user_carrier_shipment_insurance_terminal') == 'true' ? true : false;
            //check if shipment insurance is true
            if ($shipmentInsurance) {
                //set shipment insurance
                $query['include_insurance'] = true;
            }
            //query builder
            $query = http_build_query($query);
            $response = Requests::get(
                self::$enpoint . 'rates/shipment?' . $query,
                [
                    'Authorization' => 'Bearer ' . self::$skkey,
                    'Content-Type' => 'application/json'
                ] + self::$request_header,
                //time out 60 seconds
                ['timeout' => 60]
            );
            $body = json_decode($response->body);
            //check if response is ok
            if ($response->status_code == 200) {
                //return countries
                $data = $body->data;
                //return data
                return [
                    'code' => 200,
                    'message' => 'success',
                    'data' => $data,
                ];
            } else {
                //logTerminalErrorData
                logTerminalErrorData($response->body, self::$enpoint . 'rates/shipment' . "?" . http_build_query([
                    'shipment_id' => $shipment_id,
                    'merchant_address_id' => $merchant_address_id,
                    'customer_address_id' => $customer_address_id,
                    'parcel' => $parcel,
                    'source' => 'wordpress',
                    'currency' => get_woocommerce_currency(),
                ]));
                return [
                    'code' => $response->status_code,
                    'message' => $body->message,
                    'data' => [],
                ];
            }
        } catch (\Exception $e) {
            logTerminalError($e, self::$enpoint . 'rates/shipment' . "?" . http_build_query([
                'shipment_id' => $shipment_id,
                'merchant_address_id' => $merchant_address_id,
                'customer_address_id' => $customer_address_id,
                'parcel' => $parcel,
                'source' => 'wordpress',
                'currency' => get_woocommerce_currency(),
            ]));
            return [
                'code' => 500,
                'message' => $e->getMessage(),
                'data' => [],
            ];
        }
    }

    //getTerminalRateData
    public static function getTerminalRateData($rate_id, $force = false)
    {
        try {
            //if session is not started start it
            if (session_status() == PHP_SESSION_NONE) {
                session_start();
            }
            //check if data is in session
            if (isset($_SESSION['ratedata'][$rate_id]) && !$force) {
                return [
                    'code' => 200,
                    'message' => 'success',
                    'data' => sanitize_array($_SESSION['ratedata'][$rate_id]),
                    'from' => 'session',
                ];
            }
            //check if api key is valid
            if (!self::$skkey) {
                return [
                    'code' => 404,
                    'message' => "Invalid API Key",
                    'data' => [],
                ];
            }
            $response = Requests::get(
                self::$enpoint . 'rates/' . $rate_id,
                [
                    'Authorization' => 'Bearer ' . self::$skkey,
                    'Content-Type' => 'application/json'
                ] + self::$request_header,
                //time out 60 seconds
                ['timeout' => 60]
            );
            $body = json_decode($response->body);
            //check if response is ok
            if ($response->status_code == 200) {
                //return countries
                $data = $body->data;
                //save data to session
                $_SESSION['ratedata'][$rate_id] = $data;
                //return data
                return [
                    'code' => 200,
                    'message' => 'success',
                    'data' => $data,
                    'from' => 'api',
                ];
            } else {
                //logTerminalErrorData
                logTerminalErrorData($response->body, self::$enpoint . 'rates/' . $rate_id);
                return [
                    'code' => $response->status_code,
                    'message' => $body->message,
                    'data' => [],
                    'from' => 'api',
                ];
            }
        } catch (\Exception $e) {
            logTerminalError($e, self::$enpoint . 'rates/' . $rate_id);
            return [
                'code' => 500,
                'message' => $e->getMessage(),
                'data' => [],
                'from' => 'api',
            ];
        }
    }

    //applyTerminalRate($order_id, $rateid, $pickup, $duration, $amount, $carrier_name)
    public static function applyTerminalRate($order_id, $rateid, $pickup, $duration, $amount, $carrier_name, $carrierlogo)
    {
        try {
            //wc order
            $order = wc_get_order($order_id);
            //check if order is valid
            if (!$order) {
                return [
                    'code' => 404,
                    'message' => "Invalid Order",
                    'data' => [],
                ];
            }
            //check if $amount is string
            if (is_string($amount)) {
                $amount = (float) $amount;
            }
            $items    = (array) $order->get_items('shipping');
            // // Loop through shipping items
            foreach ($items as $item) {
                //get shipping method id
                $shipping_method_id = $item->get_method_id();
                //if shipping method id is terminal_delivery
                if ($shipping_method_id == "terminal_delivery") {
                    $item->set_method_title(__("Terminal Delivery - $carrier_name"));
                    $item->set_total($amount);
                    //update item meta
                    $item->update_meta_data('duration', $duration, true);
                    $item->update_meta_data('carrier', $carrier_name, true);
                    $item->update_meta_data('amount', $amount, true);
                    $item->update_meta_data('rate_id', $rateid, true);
                    $item->update_meta_data('pickup_time', $pickup, true);
                    $item->update_meta_data('carrier_logo', $carrierlogo, true);
                    $item->save();
                }
            }
            //calculate totals
            $order->calculate_totals();
            //update meta
            update_post_meta($order_id, 'Terminal_africa_carriername', $carrier_name);
            update_post_meta($order_id, 'Terminal_africa_amount', $amount);
            update_post_meta($order_id, 'Terminal_africa_duration', $duration);
            update_post_meta($order_id, 'Terminal_africa_rateid', $rateid);
            update_post_meta($order_id, 'Terminal_africa_pickuptime', $pickup);
            update_post_meta($order_id, 'Terminal_africa_carrierlogo', $carrierlogo);
            //url
            $shipment_id = get_post_meta($order_id, 'Terminal_africa_shipment_id', true);
            //shipping url
            $plugin_url = admin_url('admin.php?page=terminal-africa');
            //arg
            $arg = array(
                'page' => 'terminal-africa',
                'action' => 'edit',
                'id' => esc_html($shipment_id),
                'order_id' => esc_html($order_id),
                'rate_id' => esc_html($rateid),
                'nonce' => wp_create_nonce('terminal_africa_edit_shipment')
            );
            $plugin_url = add_query_arg($arg, $plugin_url);
            //return data
            return [
                'code' => 200,
                'message' => 'success',
                'data' => [],
                'url' => $plugin_url,
            ];
        } catch (\Exception $e) {
            logTerminalError($e);
            return [
                'code' => 500,
                'message' => $e->getMessage(),
                'data' => [],
            ];
        }
    }

    //getWalletBalance
    public static function getWalletBalance($user_id, $force = false, $currency = 'NGN')
    {
        try {
            //if session is not started start it
            if (session_status() == PHP_SESSION_NONE) {
                session_start();
            }

            //get plugin mode
            $plugin_mode = self::$plugin_mode;

            //check if data is in session
            if (isset($_SESSION['wallet_balance'][$plugin_mode]) && !$force) {
                return [
                    'code' => 200,
                    'message' => 'success',
                    'data' => sanitize_array($_SESSION['wallet_balance'][$plugin_mode]),
                    'from' => 'session'
                ];
            }

            if (!self::$skkey) {
                return [
                    'code' => 404,
                    'message' => "Invalid API Key",
                    'data' => [],
                ];
            }

            $query = [
                'user_id' => $user_id
            ];

            //check if wallet currency session is available
            if (isset($_SESSION['terminal_africa_wallet_currency'])) {
                $query['currency'] = $_SESSION['terminal_africa_wallet_currency'];
            } else {
                $query['currency'] = $currency;
            }

            //query builder
            $query = http_build_query($query);

            //get cities
            $response = Requests::get(self::$enpoint . 'users/wallet?' . $query, [
                'Authorization' => 'Bearer ' . self::$skkey,
            ] + self::$request_header);

            $body = json_decode($response->body);

            //check if response is ok
            if ($response->status_code == 200) {
                //return countries
                $data = $body->data;
                //save to session
                $_SESSION['wallet_balance'][$plugin_mode] = $data;
                //return data
                return [
                    'code' => 200,
                    'message' => 'success',
                    'data' => $data,
                ];
            } else {
                //logTerminalErrorData
                logTerminalErrorData($response->body, self::$endpoint . 'users/wallet?' . http_build_query(['user_id' => $user_id]));
                return [
                    'code' => $response->status_code,
                    'message' => $body->message,
                    'data' => [],
                ];
            }
        } catch (\Exception $e) {
            logTerminalError($e, self::$endpoint . 'users/wallet?' . http_build_query(['user_id' => $user_id]));
            return [
                'code' => 500,
                'message' => $e->getMessage(),
                'data' => [],
            ];
        }
    }

    //getTerminalCarriers
    public static function getTerminalCarriers($type, $force = false)
    {
        try {
            //if session is not started start it
            if (session_status() == PHP_SESSION_NONE) {
                session_start();
            }
            //check if data is in session
            if (isset($_SESSION['terminal_carriers_data'][$type]) && !$force) {
                return [
                    'code' => 200,
                    'message' => 'success',
                    'data' => sanitize_array($_SESSION['terminal_carriers_data'][$type]),
                    'from' => 'session',
                ];
            }
            if (!self::$skkey) {
                return [
                    'code' => 404,
                    'message' => "Invalid API Key",
                    'data' => [],
                ];
            }

            $query = [
                'type' => $type
            ];
            //query builder
            $query = http_build_query($query);
            //get cities
            $response = Requests::get(self::$enpoint . 'carriers?' . $query, [
                'Authorization' => 'Bearer ' . self::$skkey,
            ] + self::$request_header);
            $body = json_decode($response->body);
            //check if response is ok
            if ($response->status_code == 200) {
                //return countries
                $data = $body->data;
                //save to session
                $_SESSION['terminal_carriers_data'][$type] = $data;
                //return data
                return [
                    'code' => 200,
                    'message' => 'success',
                    'data' => $data,
                ];
            } else {
                //logTerminalErrorData
                logTerminalErrorData($response->body, self::$endpoint . 'carriers?' . http_build_query(['type' => $type]));
                return [
                    'code' => $response->status_code,
                    'message' => $body->message,
                    'data' => [],
                ];
            }
        } catch (\Exception $e) {
            logTerminalError($e, self::$endpoint . 'carriers?' . http_build_query(['type' => $type]));
            return [
                'code' => 500,
                'message' => $e->getMessage(),
                'data' => [],
            ];
        }
    }

    //Enable Multiple Carriers
    public static function enableMultipleCarriers($carriers)
    {
        try {
            $newconverted = [];
            //loop through carriers
            foreach ($carriers as $carrier) {
                $newconverted[] = [
                    'carrier_id' => $carrier['id'],
                    'domestic' => (bool)$carrier['domestic'],
                    'international' => (bool)$carrier['international'],
                    'regional' => (bool)$carrier['regional'],
                ];
            }
            //check $skkey
            if (!self::$skkey) {
                return [
                    'code' => 404,
                    'message' => "Invalid API Key",
                    'data' => [],
                ];
            }

            $response = Requests::post(
                self::$enpoint . 'carriers/multiple/enable',
                [
                    'Authorization' => 'Bearer ' . self::$skkey,
                    'Content-Type' => 'application/json'
                ] + self::$request_header,
                json_encode(
                    [
                        'carriers' => $newconverted
                    ]
                ),
                //time out 60 seconds
                ['timeout' => 60]
            );
            $body = json_decode($response->body);
            //check if response is ok
            if ($response->status_code == 200) {
                //return countries
                $data = $body->data;
                //return data
                return [
                    'code' => 200,
                    'message' => 'success',
                    'data' => $data,
                ];
            } else {
                //logTerminalErrorData
                logTerminalErrorData($response->body, self::$endpoint . 'carriers/multiple/enable?' . http_build_query(['carriers' => $newconverted]));
                return [
                    'code' => $response->status_code,
                    'message' => $body->message,
                    'data' => [],
                ];
            }
        } catch (\Exception $e) {
            logTerminalError($e, self::$endpoint . 'carriers/multiple/enable?' . http_build_query(['carriers' => $carriers]));
            return [
                'code' => 500,
                'message' => $e->getMessage(),
                'data' => [],
            ];
        }
    }

    //Enable Single Carriers
    public static function enableSingleCarriers($carriers)
    {
        try {
            //check $skkey
            if (!self::$skkey) {
                return [
                    'code' => 404,
                    'message' => "Invalid API Key",
                    'data' => [],
                ];
            }
            $response = Requests::post(
                self::$enpoint . 'carriers/multiple/enable',
                [
                    'Authorization' => 'Bearer ' . self::$skkey,
                    'Content-Type' => 'application/json'
                ] + self::$request_header,
                json_encode(
                    $carriers
                ),
                //time out 60 seconds
                ['timeout' => 60]
            );
            $body = json_decode($response->body);
            //check if response is ok
            if ($response->status_code == 200) {
                //return countries
                $data = $body->data;
                //return data
                return [
                    'code' => 200,
                    'message' => 'success',
                    'data' => $data,
                ];
            } else {
                //logTerminalErrorData
                logTerminalErrorData($response->body, self::$endpoint . 'carriers/multiple/enable?' . http_build_query($carriers));
                return [
                    'code' => $response->status_code,
                    'message' => $body->message,
                    'data' => [],
                ];
            }
        } catch (\Exception $e) {
            logTerminalError($e, self::$endpoint . 'carriers/multiple/enable?' . http_build_query($carriers));
            return [
                'code' => 500,
                'message' => $e->getMessage(),
                'data' => [],
            ];
        }
    }

    //Disable Multiple Carriers
    public static function disableMultipleCarriers($carriers)
    {
        try {
            $newconverted = [];
            //loop through carriers
            foreach ($carriers as $carrier) {
                $newconverted[] = [
                    'carrier_id' => $carrier['id'],
                    'domestic' => (bool)$carrier['domestic'],
                    'international' => (bool)$carrier['international'],
                    'regional' => (bool)$carrier['regional'],
                ];
            }
            //check $skkey
            if (!self::$skkey) {
                return [
                    'code' => 404,
                    'message' => "Invalid API Key",
                    'data' => [],
                ];
            }

            $response = Requests::post(
                self::$enpoint . 'carriers/multiple/disable',
                [
                    'Authorization' => 'Bearer ' . self::$skkey,
                    'Content-Type' => 'application/json'
                ] + self::$request_header,
                json_encode(
                    [
                        'carriers' => $newconverted
                    ]
                ),
                //time out 60 seconds
                ['timeout' => 60]
            );
            $body = json_decode($response->body);
            //check if response is ok
            if ($response->status_code == 200) {
                //return countries
                $data = $body->data;
                //return data
                return [
                    'code' => 200,
                    'message' => 'success',
                    'data' => $data,
                ];
            } else {
                //logTerminalErrorData
                logTerminalErrorData($response->body, self::$endpoint . 'carriers/multiple/disable?' . http_build_query(['carriers' => $newconverted]));
                return [
                    'code' => $response->status_code,
                    'message' => $body->message,
                    'data' => [],
                ];
            }
        } catch (\Exception $e) {
            logTerminalError($e, self::$endpoint . 'carriers/multiple/disable?' . http_build_query(['carriers' => $carriers]));
            return [
                'code' => 500,
                'message' => $e->getMessage(),
                'data' => [],
            ];
        }
    }

    //Disable Single Carriers
    public static function disableSingleCarriers($carriers)
    {
        try {
            //check $skkey
            if (!self::$skkey) {
                return [
                    'code' => 404,
                    'message' => "Invalid API Key",
                    'data' => [],
                ];
            }

            $response = Requests::post(
                self::$enpoint . 'carriers/multiple/disable',
                [
                    'Authorization' => 'Bearer ' . self::$skkey,
                    'Content-Type' => 'application/json'
                ] + self::$request_header,
                json_encode(
                    $carriers
                ),
                //time out 60 seconds
                ['timeout' => 60]
            );
            $body = json_decode($response->body);
            //check if response is ok
            if ($response->status_code == 200) {
                //return countries
                $data = $body->data;
                //return data
                return [
                    'code' => 200,
                    'message' => 'success',
                    'data' => $data,
                ];
            } else {
                //logTerminalErrorData
                logTerminalErrorData($response->body, self::$endpoint . 'carriers/multiple/disable?' . http_build_query($carriers));
                return [
                    'code' => $response->status_code,
                    'message' => $body->message,
                    'data' => [],
                ];
            }
        } catch (\Exception $e) {
            logTerminalError($e, self::$enpoint . 'carriers/multiple/disable?' . http_build_query($carriers));
            return [
                'code' => 500,
                'message' => $e->getMessage(),
                'data' => [],
            ];
        }
    }

    //getTerminalPackagingData
    public static function getTerminalPackagingData()
    {
        try {
            //check terminal_default_packaging_id option
            $packaging_id = get_option('terminal_default_packaging_id');
            //check if packaging id is set
            if ($packaging_id) {
                return [
                    'code' => 200,
                    'message' => 'success',
                    'data' => [
                        'packaging_id' => $packaging_id
                    ]
                ];
            }

            if (!self::$skkey) {
                return [
                    'code' => 404,
                    'message' => "Invalid API Key",
                    'data' => [],
                ];
            }
            //get cities
            $response = Requests::get(self::$enpoint . 'packaging', [
                'Authorization' => 'Bearer ' . self::$skkey,
            ] + self::$request_header);
            $body = json_decode($response->body);
            //check if response is ok
            if ($response->status_code == 200) {
                //return countries
                $data = $body->data;
                $packaging = $data->packaging;
                //check the count
                if (count($packaging) > 0) {
                    //get the first element
                    $element = $packaging[0];
                    //save the id to option
                    update_option('terminal_default_packaging_id', $element->packaging_id);
                } else {
                    //create new packaging
                    $create = self::createDefaultPackaging();
                    if ($create['code'] == 200) {
                        //save the id to option
                        update_option('terminal_default_packaging_id', $create['data']->packaging_id);
                    } else {
                        return [
                            'code' => 404,
                            'message' => "Unable to create default packaging",
                            'data' => [],
                        ];
                    }
                }
                //return data
                return [
                    'code' => 200,
                    'message' => 'success',
                    'data' => $data,
                ];
            } else {
                //logTerminalErrorData
                logTerminalErrorData($response->body, self::$endpoint . 'packaging');
                return [
                    'code' => $response->status_code,
                    'message' => $body->message,
                    'data' => [],
                ];
            }
        } catch (\Exception $e) {
            logTerminalError($e, self::$enpoint . 'packaging');
            return [
                'code' => 500,
                'message' => $e->getMessage(),
                'data' => [],
            ];
        }
    }

    //create packaging
    public static function createDefaultPackaging()
    {
        try {
            //check if terminal_africa_merchant_id is set
            $terminal_africa_merchant_id = get_option('terminal_africa_merchant_id');
            if (!$terminal_africa_merchant_id) {
                return [
                    'code' => 404,
                    'message' => "Invalid Merchant ID",
                    'data' => [],
                ];
            }
            //check $skkey
            if (!self::$skkey) {
                return [
                    'code' => 404,
                    'message' => "Invalid API Key",
                    'data' => [],
                ];
            }

            $response = Requests::post(
                self::$enpoint . 'packaging',
                [
                    'Authorization' => 'Bearer ' . self::$skkey,
                    'Content-Type' => 'application/json'
                ] + self::$request_header,
                json_encode(
                    [
                        "height" => 1,
                        "length" => 47,
                        "name" => "DHL Express Large Flyer",
                        "size_unit" => "cm",
                        "type" => "soft-packaging",
                        "user" => $terminal_africa_merchant_id,
                        "weight" => 0.1,
                        "weight_unit" => "kg",
                        "width" => 38
                    ]
                ),
                //time out 60 seconds
                ['timeout' => 60]
            );
            $body = json_decode($response->body);
            //check if response is ok
            if ($response->status_code == 200) {
                //return countries
                $data = $body->data;
                //return data
                return [
                    'code' => 200,
                    'message' => 'success',
                    'data' => $data,
                ];
            } else {
                //logTerminalErrorData
                logTerminalErrorData($response->body, self::$endpoint . 'packaging?' . http_build_query([
                    "height" => 1,
                    "length" => 47,
                    "name" => "DHL Express Large Flyer",
                    "size_unit" => "cm",
                    "type" => "soft-packaging",
                    "user" => $terminal_africa_merchant_id,
                    "weight" => 0.1,
                    "weight_unit" => "kg",
                    "width" => 38
                ]));
                return [
                    'code' => $response->status_code,
                    'message' => $body->message,
                    'data' => [],
                ];
            }
        } catch (\Exception $e) {
            logTerminalError($e, self::$enpoint . 'packaging');
            return [
                'code' => 500,
                'message' => $e->getMessage(),
                'data' => [],
            ];
        }
    }

    //verifyDefaultPackage
    public static function verifyDefaultPackaging($packaging_id)
    {
        try {
            if (!self::$skkey) {
                return [
                    'code' => 404,
                    'message' => "Invalid API Key",
                    'data' => [],
                ];
            }
            //get cities
            $response = Requests::get(self::$enpoint . 'packaging/' . $packaging_id, [
                'Authorization' => 'Bearer ' . self::$skkey,
            ] + self::$request_header);
            $body = json_decode($response->body);
            //check if response is ok
            if ($response->status_code == 200) {
                //update the option
                update_option('terminal_default_packaging_id', $packaging_id);
                //return data
                return [
                    'code' => 200,
                    'message' => 'success',
                    'data' => $body->data,
                    'packaging_id' => $packaging_id
                ];
            } else {
                //create new default packaging
                $create = self::createDefaultPackaging();
                if ($create['code'] == 200) {
                    //save the id to option
                    update_option('terminal_default_packaging_id', $create['data']->packaging_id);
                    //return data
                    return [
                        'code' => 200,
                        'message' => 'success',
                        'data' => $create['data'],
                        'packaging_id' => $create['data']->packaging_id
                    ];
                } else {
                    //logTerminalErrorData
                    logTerminalErrorData($response->body, self::$endpoint . 'packaging/' . $packaging_id);
                    return [
                        'code' => 404,
                        'message' => "Unable to create default packaging",
                        'data' => [],
                    ];
                }
            }
        } catch (\Exception $e) {
            logTerminalError($e, self::$enpoint . 'packaging/' . $packaging_id);
            return [
                'code' => 500,
                'message' => $e->getMessage(),
                'data' => [],
            ];
        }
    }

    //arrange pickup and delivery
    public static function arrangePickupAndDelivery($shipment_id, $rate_id, $dropoff_id = null)
    {
        try {
            //check $skkey
            if (!self::$skkey) {
                return [
                    'code' => 404,
                    'message' => "Invalid API Key",
                    'data' => [],
                ];
            }

            //args
            $args = [
                "shipment_id" => $shipment_id,
                "rate_id" => $rate_id
            ];

            //check if dropoff_id is set
            if (!empty($dropoff_id)) {
                $args["dropoff_id"] = $dropoff_id;
            }

            $response = Requests::post(
                self::$enpoint . 'shipments/pickup',
                [
                    'Authorization' => 'Bearer ' . self::$skkey,
                    'Content-Type' => 'application/json'
                ] + self::$request_header,
                json_encode($args),
                //time out 60 seconds
                ['timeout' => 60]
            );
            $body = json_decode($response->body);
            //check if response is ok
            if ($response->status_code == 200 && $body->status) {
                //return countries
                $data = $body->data;
                //return data
                return [
                    'code' => 200,
                    'message' => 'success',
                    'data' => $data,
                ];
            } else {
                //logTerminalErrorData
                logTerminalErrorData($response->body, self::$endpoint . 'shipments/pickup?' . http_build_query([
                    "shipment_id" => $shipment_id,
                    "rate_id" => $rate_id,
                ]));
                return [
                    'code' => $response->status_code,
                    'message' => $body->message,
                    'data' => [],
                ];
            }
        } catch (\Exception $e) {
            logTerminalError($e, self::$enpoint . 'shipments/pickup?' . http_build_query([
                "shipment_id" => $shipment_id,
                "rate_id" => $rate_id,
            ]));
            return [
                'code' => 500,
                'message' => $e->getMessage(),
                'data' => [],
            ];
        }
    }

    //getTerminalShipmentStatus
    public static function getTerminalShipmentStatus($shipment_id)
    {
        try {
            if (!self::$skkey) {
                return [
                    'code' => 404,
                    'message' => "Invalid API Key",
                    'data' => [],
                ];
            }
            //get shipment status
            $response = Requests::get(self::$enpoint . 'shipments/' . $shipment_id, [
                'Authorization' => 'Bearer ' . self::$skkey,
            ] + self::$request_header);
            $body = json_decode($response->body);
            //check if response is ok
            if ($response->status_code == 200) {
                //return countries
                $data = $body->data->status;
                //return data
                return [
                    'code' => 200,
                    'message' => 'success',
                    'data' => $data,
                    'shipment_info' => $body->data
                ];
            } else {
                //logTerminalErrorData
                logTerminalErrorData($response->body, self::$endpoint . 'shipments/' . $shipment_id);
                return [
                    'code' => $response->status_code,
                    'message' => $body->message,
                    'data' => '',
                    'shipment_info' => []
                ];
            }
        } catch (\Exception $e) {
            logTerminalError($e, self::$enpoint . 'shipments/' . $shipment_id);
            return [
                'code' => 500,
                'message' => $e->getMessage(),
                'data' => '',
                'shipment_info' => []
            ];
        }
    }

    //get User Carriers
    public static function getUserCarriers($type = "domestic", $force = false)
    {
        try {
            //if session is started
            if (session_status() == PHP_SESSION_NONE) {
                session_start();
            }
            //check if session is set
            if (isset($_SESSION['terminal_africa_carriers'][$type]) && !$force) {
                return [
                    'code' => 200,
                    'message' => 'success',
                    'data' => sanitize_array($_SESSION['terminal_africa_carriers'][$type]),
                ];
            }
            if (!self::$skkey) {
                return [
                    'code' => 404,
                    'message' => "Invalid API Key",
                    'data' => [],
                ];
            }
            //get shipment status
            $response = Requests::get(self::$enpoint . 'users/carriers?type=' . $type, [
                'Authorization' => 'Bearer ' . self::$skkey,
            ] + self::$request_header);
            $body = json_decode($response->body);
            //check if response is ok
            if ($response->status_code == 200) {
                //return countries
                $data = $body->data->carriers;
                //save session
                $_SESSION['terminal_africa_carriers'][$type] = $data;
                //return data
                return [
                    'code' => 200,
                    'message' => 'success',
                    'data' => $data,
                ];
            } else {
                //logTerminalErrorData
                logTerminalErrorData($response->body, self::$endpoint . 'users/carriers?type=' . $type);
                return [
                    'code' => $response->status_code,
                    'message' => $body->message,
                    'data' => [],
                ];
            }
        } catch (\Exception $e) {
            logTerminalError($e, self::$enpoint . 'users/carriers?type=' . $type);
            return [
                'code' => 500,
                'message' => $e->getMessage(),
                'data' => [],
            ];
        }
    }

    //cancel_terminal_shipment
    public static function cancelTerminalShipment($shipment_id)
    {
        try {
            //check $skkey
            if (!self::$skkey) {
                return [
                    'code' => 404,
                    'message' => "Invalid API Key",
                    'data' => [],
                ];
            }

            $response = Requests::post(
                self::$enpoint . 'shipments/cancel',
                [
                    'Authorization' => 'Bearer ' . self::$skkey,
                    'Content-Type' => 'application/json'
                ] + self::$request_header,
                json_encode(
                    [
                        "shipment_id" => $shipment_id
                    ]
                ),
                //time out 60 seconds
                ['timeout' => 60]
            );
            $body = json_decode($response->body);
            //check if response is ok
            if ($response->status_code == 200) {
                //return countries
                $data = $body->data;
                //return data
                return [
                    'code' => 200,
                    'message' => 'success',
                    'data' => $data,
                ];
            } else {
                //logTerminalErrorData
                logTerminalErrorData($response->body, self::$endpoint . 'shipments/cancel?' . http_build_query([
                    "shipment_id" => $shipment_id,
                ]));
                return [
                    'code' => $response->status_code,
                    'message' => $body->message,
                    'data' => [],
                ];
            }
        } catch (\Exception $e) {
            logTerminalError($e, self::$enpoint . 'shipments/cancel?' . http_build_query([
                "shipment_id" => $shipment_id,
            ]));
            return [
                'code' => 500,
                'message' => $e->getMessage(),
                'data' => [],
            ];
        }
    }

    /**
     * updateDefaultCurrencyCode
     * @param string $currency_code
     */
    public static function updateDefaultCurrencyCode($currency_code = 'NGN')
    {
        try {
            //check $skkey
            if (!self::$skkey) {
                return [
                    'code' => 404,
                    'message' => "Invalid API Key",
                    'data' => [],
                ];
            }

            $response = Requests::post(
                self::$enpoint . 'users/default-currency',
                [
                    'Authorization' => 'Bearer ' . self::$skkey,
                    'Content-Type' => 'application/json'
                ] + self::$request_header,
                json_encode(
                    [
                        "currency" => $currency_code
                    ]
                ),
                //time out 60 seconds
                ['timeout' => 60]
            );
            $body = json_decode($response->body);
            //check if response is ok
            if ($response->status_code == 200) {
                //return countries
                $data = $body->data;
                //return data
                return [
                    'code' => 200,
                    'message' => 'success',
                    'data' => $data,
                ];
            } else {
                //logTerminalErrorData
                logTerminalErrorData($response->body, self::$endpoint . 'users/default-currency?' . http_build_query([
                    "currency" => $currency_code,
                ]));
                return [
                    'code' => $response->status_code,
                    'message' => $body->message,
                    'data' => [],
                ];
            }
        } catch (\Exception $e) {
            logTerminalError($e, self::$enpoint . 'users/default-currency?' . http_build_query([
                "currency" => $currency_code,
            ]));
            return [
                'code' => 500,
                'message' => $e->getMessage(),
                'data' => [],
            ];
        }
    }

    /**
     * Get Transactions
     * 
     * @since 1.11.1
     */
    public static function getTransactions($page = 1, $filter = [], $force = false)
    {
        try {
            //check $skkey
            if (!self::$skkey) {
                return [
                    'code' => 404,
                    'message' => "Invalid API Key",
                    'data' => [],
                ];
            }

            //check if session is started
            if (session_status() == PHP_SESSION_NONE) {
                session_start();
            }

            $userData = get_option("terminal_africa_settings");

            if (!isset($userData['others']->user->wallet)) {
                return [
                    'code' => 404,
                    'message' => "Invalid Wallet ID",
                    'data' => [],
                ];
            }

            //check if wallet id session is available
            if (isset($_SESSION['terminal_africa_wallet_id'])) {
                $userWalletId = $_SESSION['terminal_africa_wallet_id'];
            } else {
                $userWalletId = $userData['others']->user->wallet;
            }

            $dataQuery =
                [
                    "perPage" => 10,
                    "page" => $page,
                    "wallet" => $userWalletId
                ];

            //get plugin mode
            $plugin_mode = self::$plugin_mode;

            //append if filter is set and value not empty
            if (!empty($filter)) {
                //loop through filter
                $filter = array_filter($filter, function ($value) {
                    //ignore empty values
                    return !empty($value);
                });

                //check if filter is not empty
                if (!empty($filter)) {
                    //append to query
                    $dataQuery = array_merge($dataQuery, $filter);
                }
            }

            //hash $dataQuery
            $dataHash = md5(json_encode($dataQuery));

            //check if session is set
            if (isset($_SESSION['terminal_africa_transactions'][$plugin_mode][$dataHash]) && !$force) {
                return [
                    'code' => 200,
                    'message' => 'success',
                    'data' => sanitize_array($_SESSION['terminal_africa_transactions'][$plugin_mode][$dataHash]),
                    'from' => 'session',
                ];
            }

            $response = Requests::get(
                self::$enpoint . 'transactions?' . http_build_query($dataQuery),
                [
                    'Authorization' => 'Bearer ' . self::$skkey,
                    'Content-Type' => 'application/json'
                ] + self::$request_header,
                //time out 60 seconds
                ['timeout' => 60]
            );
            $body = json_decode($response->body);
            //check if response is ok
            if ($response->status_code == 200) {
                //return countries
                $data = $body->data;
                //save to session
                $_SESSION['terminal_africa_transactions'][$plugin_mode][$dataHash] = $data;
                //return data
                return [
                    'code' => 200,
                    'message' => 'success',
                    'data' => $data,
                ];
            } else {
                //logTerminalErrorData
                logTerminalErrorData($response->body, self::$endpoint . 'transactions?' . http_build_query([
                    "perPage" => 25,
                    "page" => $page
                ]));
                return [
                    'code' => $response->status_code,
                    'message' => $body->message,
                    'data' => [],
                ];
            }
        } catch (\Exception $e) {
            logTerminalError($e, self::$enpoint . 'transactions?' . http_build_query([
                "perPage" => 25,
                "page" => $page
            ]));
            return [
                'code' => 500,
                'message' => $e->getMessage(),
                'data' => [],
            ];
        }
    }
}
