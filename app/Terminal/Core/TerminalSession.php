<?php

namespace App\Terminal\Core;

//security
defined('ABSPATH') or die('No script kiddies please!');
/**
 * Terminal Session
 * @package App\Terminal\Core
 * @since 1.0.0
 * @version 1.0.0
 * @author Terminal Africa
 */
class TerminalSession
{
    /**
     * @var TerminalSession
     */
    protected static $_instance = null;

    /**
     * Set session
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function set($key, $value)
    {
        //check if session is available
        if (!session_id()) {
            session_start();
        }
        $_SESSION['terminal_africa_plugin'][$key] = $value;
    }

    /**
     * Get session
     * @param string $key
     * @return mixed
     */
    public function get($key)
    {
        //check if session is available
        if (!session_id()) {
            session_start();
        }
        return isset($_SESSION['terminal_africa_plugin'][$key]) ? $_SESSION['terminal_africa_plugin'][$key] : null;
    }

    /**
     * Unset session
     * @param string $key
     * @return void
     */
    public function unset($key)
    {
        //check if session is available
        if (!session_id()) {
            session_start();
        }
        //check if we have the session
        if (isset($_SESSION['terminal_africa_plugin'][$key])) {
            unset($_SESSION['terminal_africa_plugin'][$key]);
        }
    }

    /**
     * Destroy session
     * @return void
     */
    public function destroy()
    {
        //check if session is available
        if (!session_id()) {
            session_start();
        }

        //check if we have the session
        if (isset($_SESSION['terminal_africa_plugin'])) {
            unset($_SESSION['terminal_africa_plugin']);
        }
    }

    /**
     * Instance
     * @return TerminalSession
     */
    public static function instance()
    {
        //check if instance is available
        if (!self::$_instance) {
            self::$_instance = new TerminalSession();
        }
        return self::$_instance;
    }
}
