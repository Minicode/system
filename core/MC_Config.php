<?php
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

// ------------------------------------------------------------------------

/**
 * MC_Config Class
 *
 * This class contains functions that enable config files to be managed
 *
 * @package       Minicode
 * @category      Core
 * @subpackage    MC_Config
 * @author        Wanglong
 * @link          http://minicode.org/docs/core/mc_config
 * @since         Version 1.0
 */

class MC_Config extends MC_Object {

    /**
     * List of all loaded config values
     *
     * @access private
     * @var    array
     */
    public $config       = array();

    // --------------------------------------------------------------------

    /**
     * List of all loaded config files
     *
     * @access private
     * @var    array
     */
    private $is_loaded    = array();

    // --------------------------------------------------------------------

    /**
     * List of paths to search when trying to load a config file.
     * This must be public as it's used by the Loader class.
     *
     * @access private
     * @var    array
     */
    private $config_paths = array(APPPATH);

    // --------------------------------------------------------------------

    /**
     * Constructor
     *
     * @access    public
     */
    public function __construct() {}

    // --------------------------------------------------------------------

    /**
     * The more efficient call configuration properties
     *
     * @access    public
     */
    public function __get($item) {
        return $this->item($item);
    }

    // --------------------------------------------------------------------

    /**
     * Load Config File
     *
     * @param   string  the config file name
     * @param   string  in configuration files define an array variable name
     * @param   bool    if configuration values should be loaded into their own section
     * @param   bool    true if errors should just return false, false if an error message should be displayed
     * @return  bool    if the file was loaded correctly
     */
    public function load($file = '', $assign = 'config', $use_sections = FALSE, $fail_gracefully = FALSE) {
        $file = ($file === '') ? 'config' : str_replace('.php', '', $file);
        $found = $loaded = FALSE;
        
        $check_locations = defined('ENVIRONMENT')
            ? array(ENVIRONMENT.'/'.$file, $file)
            : array($file);

        foreach ($this->config_paths as $path) {
            foreach ($check_locations as $location) {
                $file_path = $path.'config/'.$location.'.php';

                if (in_array($file_path, $this->is_loaded, TRUE)) {
                    $loaded = TRUE;
                    continue 2;
                }

                if (file_exists($file_path)) {
                    $found = TRUE;
                    break;
                }
            }

            if ($found === FALSE) {
                continue;
            }

            include($file_path);

            if ( ! isset($$assign) OR ! is_array($$assign)) {
                if ($fail_gracefully === TRUE) {
                    return FALSE;
                }
                die('Your '.$file_path.' file does not appear to contain a valid configuration array.');
            }

            $config = $$assign;

            if ($use_sections === TRUE) {
                if (isset($this->config[$file])) {
                    $this->config[$file] = array_merge($this->config[$file], $config);
                }
                else {
                    $this->config[$file] = $config;
                }
            }
            else {
                $this->config = array_merge($this->config, $config);
            }

            $this->is_loaded[] = $file_path;
            unset($config);

            $loaded = TRUE;
            break;
        }

        if ($loaded === FALSE) {
            if ($fail_gracefully === TRUE) {
                return FALSE;
            }
            die('The configuration file '.$file.'.php does not exist.');
        }

        return TRUE;
    }

    // --------------------------------------------------------------------

    /**
     * Create a new config sub-object
     *
     * @param   string  section name
     * @return  object
     */
    public function section($section) {
        return new MC_ConfigSection($this->config[$section]);
    }

    // --------------------------------------------------------------------

    /**
     * Fetch a config file item
     *
     * @param   string  the config item name
     * @param   string  the index name
     * @return  string
     */
    public function item($item, $index = '') {
        if ($index == '') {
            return isset($this->config[$item]) ? $this->config[$item] : FALSE;
        }

        return isset($this->config[$index], $this->config[$index][$item]) ? $this->config[$index][$item] : FALSE;
    }

    // --------------------------------------------------------------------

    /**
     * Set a config file item
     *
     * @param   string  the config item key
     * @param   string  the config item value
     * @return  void
     */
    public function set_item($item, $value) {
        $this->config[$item] = $value;
    }
}

/**
 * MC_ConfigSection Class
 *
 * Section config to be managed, it can be created a new config sub-object
 *
 * @package       Minicode
 * @category      MC_Config
 * @subpackage    MC_ConfigSection
 * @author        Wanglong
 * @since         Version 1.0
 */
class MC_ConfigSection {

    /**
     * List of all loaded section config values
     *
     * @access private
     * @var    array
     */
    public $config = array();

    // --------------------------------------------------------------------

    /**
     * Constructor
     *
     * @access    public
     */
    public function __construct($section) {
        $this->config = $section;
    }

    // --------------------------------------------------------------------

    /**
     * The more efficient call section configuration properties
     *
     * @access    public
     */
    public function __get($item) {
        return $this->item($item);
    }

    // --------------------------------------------------------------------

    /**
     * Fetch a section config file item
     *
     * @param   string  the section config item name
     * @return  string
     */
    public function item($item = NULL) {
        if ($item === NULL) {
            return $this->config;
        }

        return isset($this->config[$item]) ? $this->config[$item] : FALSE;
    }
}
// END MC_Config Class
// By Minicode