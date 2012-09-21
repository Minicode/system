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
 * Common Functions ( Very Important !!! )
 *
 * System Common function library, the Minicode 
 * framework of the necessary file. Some of the 
 * core class library also need it.
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
        static $base_paths;

        if ( ! $base_paths) {
            $cfg = config();
            $ext_paths = isset($cfg['autoload_paths_ext']) 
                        ? $cfg['autoload_paths_ext'] 
                        : array();

            $ext_paths = array_map(function($value) {
                return APPPATH . $value;
            }, $ext_paths);

            $base_paths = array_merge(
                array(
                    APPPATH . 'libraries', 
                    APPPATH . 'models',
                    SYSPATH . 'core',
                    SYSPATH . 'lib'
                ), $ext_paths
            );
        }

        foreach ($base_paths as $path) {
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

// ------------------------------------------------------------------------

if ( ! function_exists('import')) {

    /**
     * Package import function
     *
     * This function will include the current project in the directory 
     * specified directory file, if not find the file, it will try to 
     * in this name directory to find this file, if there is no returns 
     * FALSE, returning TRUE success.
     *
     * The second parameter search specified directory. The default 
     * is "helpers", if there is no this directory, please hand create.
     *
     * @param   string
     * @param   string
     * @return  boolean
     */
    function import($name = '', $directory = 'helpers') {
        static $loaded = array();

        if ( ! isset($loaded[$directory . DIRECTORY_SEPARATOR . $name])) {
            $path = APPPATH . $directory . DIRECTORY_SEPARATOR . $name . '.php';
            if ( ! file_exists($path)) {

                $path = APPPATH . $directory . DIRECTORY_SEPARATOR . $name . DIRECTORY_SEPARATOR .  $name . '.php';

                if ( ! file_exists($path)) {
                    return FALSE;
                }
            }

            require $path;
            $loaded[$directory . '/' . $name] = TRUE;
        }
       
        return TRUE;
    }
}

// ------------------------------------------------------------------------

if ( ! function_exists('config')) {

    /**
     * Load the specified configuration file
     *
     * It uses up and convenient and flexible, recommended in the 
     * project to use it.
     *
     * @param   string  the config file name
     * @param   string  in configuration files define an array variable name
     * @return  array
     */
    function config($file = FALSE, $assign = FALSE) {
        static $loaded = array();

        $file = $file ? str_replace('.php', '', $file) : 'config';

        if ( ! empty($loaded[$file])) {
            return $loaded[$file];
        }
        
        $check_locations = defined('ENVIRONMENT')
            ? array(ENVIRONMENT. DIRECTORY_SEPARATOR . $file, $file)
            : array($file);

        foreach ($check_locations as $location) {
            $file_path = APPPATH . 'config' . DIRECTORY_SEPARATOR . $location . '.php';

            if (file_exists($file_path)) {
                require_once $file_path;

                $assign = $assign ? $assign : $file;

                if ( ! isset($$assign) OR ! is_array($$assign)) {
                    die('Your '.$file_path.' file does not appear to contain a valid configuration array.');
                }

                return $loaded[$file] = $$assign;
            }
        }

        die('The configuration file '.$file.'.php does not exist.');
    }
}

// ------------------------------------------------------------------------

if ( ! function_exists('db')) {

    /**
     * Database global fast interface
     *
     * Usually direct call db() can get a database object and connect
     * it, it comes from the MC_DB (based on the PDO). The default 
     * connection parameters using database configuration file.
     *
     * This object joined cache can be repeated use, you can use it to 
     * do any bottom even complex database operation.
     *
     * We have to create a database adapter, in order to ensure high-level 
     * interface (or packaging SQL) can be applied in any database type
     * without the need to change your code.
     *
     * If this function to join complete parameters, will attempt to create 
     * a new MC_DB_Driver object, connected to the new database connection.
     *
     * @param   string
     * @param   string
     * @param   string
     * @param   array
     * @return  object  MC_DB_Driver
     */
    function &db($dsn = '', $username = '', $password = '', $options = array()) {
        static $is_included = FALSE;
        static $databases   = array();

        if ( ! $is_included) {
            require SYSPATH . 'db' . DIRECTORY_SEPARATOR . 'MC_DB_Driver.php';
            $is_included = TRUE;
        }

        if (empty($dsn)) {

            if ( ! isset($databases[0])) {
                $databases[0] = new MC_DB_Driver;
                $databases[0]->open()->factory();
            }

            return $databases[0];
        }

        $identifer = md5($dsn . $username);

        if ( ! isset($databases[$identifer])) {
            $databases[$identifer] = new MC_DB_Driver($dsn, $username, $password, $options);
            $databases[$identifer]->open()->factory();
        }

        return $databases[$identifer];
    }
}

// End of file common.php
// By Minicode