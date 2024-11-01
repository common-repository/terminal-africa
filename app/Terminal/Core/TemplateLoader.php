<?php

namespace App\Terminal\Core;
//security
defined('ABSPATH') or die('No script kiddies please!');
/**
 * Terminal Template Loader
 * @package App\Terminal\Core
 * @since 1.0.0
 * @version 1.0.0
 * @author Terminal Africa
 */
class TemplateLoader
{
    //load template
    public static function load($template, $data = [])
    {
        try {
            $dir = TERMINAL_AFRICA_PLUGIN_DIR . '/templates/';
            $file = $dir . $template . '.php';
            if (file_exists($file)) {
                extract($data);
                include $file;
            }
        } catch (\Exception $e) {
            logTerminalError($e);
            //throw
            throw $e;
        }
    }

    //get content
    public static function get($template, $data = [])
    {
        ob_start();
        self::load($template, $data);
        return ob_get_clean();
    }
}
