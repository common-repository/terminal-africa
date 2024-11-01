<?php
//security
defined('ABSPATH') or die('No script kiddies please!');

use App\Terminal\Core\TerminalCore;

//getTerminalOrders
//check if function exists
/**
 * Get terminal orders
 * @return mixed
 */
if (!function_exists('getTerminalOrders')) {
    function getTerminalOrders()
    {
        $terminal_core = new TerminalCore();
        return $terminal_core->get_orders();
    }
}

//getActiveCarrier
//check if function exists
/**
 * Get active carrier
 * @param $carrier_id
 * @param $carriers_array_obj
 * @param $type
 * @return bool
 */
if (!function_exists('getActiveCarrier')) {
    function getActiveCarrier($carrier_id, $carriers_array_obj, $type)
    {
        $terminal_core = new TerminalCore();
        return $terminal_core->getActiveCarrier($carrier_id, $carriers_array_obj, $type);
    }
}

//getTerminalTemplate
//check if function exists
/**
 * Get terminal template
 * @param $template
 * @param array $data
 * @return string
 */
if (!function_exists('getTerminalTemplate')) {
    function getTerminalTemplate($template, $data = [])
    {
        return \App\Terminal\Core\TemplateLoader::get($template, $data);
    }
}

//sanitize_array
//check if function exists
/**
 * Sanitize array
 * @param $array
 * @return array
 */
if (!function_exists('sanitize_array')) {
    function sanitize_array($array)
    {
        $terminal_core = new TerminalCore();
        return $terminal_core->sanitize_array($array);
    }
}

//App\Terminal\Core\TerminalCore::logTerminalError
//check if function exists
/**
 * Log terminal error
 * @param \Exception $e
 * @param string $endpoint
 * @return bool
 */
if (!function_exists('logTerminalError')) {
    function logTerminalError($e, $endpoint = 'none')
    {
        $terminal_core = new TerminalCore();
        return $terminal_core->logTerminalError($e, $endpoint);
    }
}

//check if function exists
/**
 * Log terminal error
 * @param $data
 * @param string $endpoint
 * @return bool
 */
if (!function_exists('logTerminalErrorData')) {
    function logTerminalErrorData($data, $endpoint = 'none')
    {
        $terminal_core = new TerminalCore();
        return $terminal_core->logTerminalErrorData($data, $endpoint);
    }
}
