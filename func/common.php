<?php if ( ! defined('SYSPATH')) die('No direct script access allowed');
/**
 * Minicode - only need to need!
 *
 * An open source hyper-light web application agile development framework
 *
 * @package       Minicode
 * @author        Wanglong
 * @copyright     Copyright (c) 2012 - 2013, Minicode.
 * @license       http://minicode.org/docs/license
 * @link          http://minicode.org
 */

/**
 * Common Functions
 *
 * System Common function library, the Minicode 
 * framework of the necessary file
 *
 * @package       Minicode
 * @category      Global Functions
 * @author        Wanglong
 */

if ( ! function_exists('PHP_MINICODE_AUTOLOAD')) {

    /**
     * Automatic loading convention directories class library
     *
     * @param   string
     * @return  void
     */
    function PHP_MINICODE_AUTOLOAD($class_name) {
        $base_path = array(
            APPPATH . 'libraries', 
            APPPATH . 'models',
            SYSPATH . 'core',
            SYSPATH . 'lib'
        );

        foreach ($base_path as $path) {
            $root = $path;

            if (strpos($class_name, '\\') !== false) {
                $namespaces = explode('\\', $class_name);
                $class_name  = array_pop($namespaces);
                $directories = array();

                foreach ($namespaces as $directory)
                    $directories[] = $directory;

                $root .= DIRECTORY_SEPARATOR . implode($directories, DIRECTORY_SEPARATOR);
            }

            $file = "$root/$class_name.php";

            if (file_exists($file))
                require $file;
        }
    }
}

// ------------------------------------------------------------------------

if ( ! function_exists('base_url')) {

    /**
     * Returns your site root URL
     *
     * You are encouraged to use this function any time you 
     * need to generate a local URL so that your pages become 
     * more portable in the event your URL changes.
     *
     * @return  string
     */
    function base_url() {
        if (isset($_SERVER['HTTP_HOST'])) {
            $base_url = ( ! empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off') ? 'https' : 'http';
            $base_url .= '://'.$_SERVER['HTTP_HOST']
                .str_replace(basename($_SERVER['SCRIPT_NAME']), '', $_SERVER['SCRIPT_NAME']);
        }
        else {
            $base_url = 'http://localhost/';
        }

        return $base_url;
    }
}

function db($status = TRUE) {
    
}

// End of file Global.php
// By Minicode