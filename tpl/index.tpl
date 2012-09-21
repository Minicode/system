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

/*
 *---------------------------------------------------------------
 * SYSTEM FOLDER NAME
 *---------------------------------------------------------------
 *
 * This variable must contain the name of your "system" folder.
 * Include the path if the folder is not in the same  directory
 * as this file.
 */

    $system_path = array(

        // Used for local development environment
        'development' => '{#system_path#}',

        // Used for online real environment
        // For the sake of safety, can set up and web 
        // directory different path, can multiple applications 
        // share a system core. eg: '/usr/local/minicode'
        'production'  => 'minicode',

        // Used for online test environment
        'testing'     => ''
    );

/*
 *---------------------------------------------------------------
 * APPLICATION FOLDER NAME
 *---------------------------------------------------------------
 *
 * If you want this front controller to use a different "application"
 * folder then the default one you can set its name here. The folder
 * can also be renamed or relocated anywhere on your server. If
 * you do, use a full server path
 *
 * NO TRAILING SLASH!
 */

    $application_folder = array(

        // Used for local development environment
        'development' => '{#application_folder#}',

        // Used for online real environment
        // For the sake of safety, can set up and web 
        // directory different path
        'production'  => '{#application_folder#}',

        // Used for online test environment
        'testing'     => '{#application_folder#}'
    );

/*
 *---------------------------------------------------------------
 * APPLICATION ENVIRONMENT
 *---------------------------------------------------------------
 *
 * You can load different configurations depending on your
 * current environment. Setting the environment also influences
 * things like logging and error reporting.
 *
 * This can be set to anything, but default usage is:
 *
 *     development
 *     testing
 *     production
 *
 * NOTE: If you change these, also change the error_reporting() code below
 */
    define('ENVIRONMENT', isset($_SERVER['MC_ENV']) ? $_SERVER['MC_ENV'] : 'development');

/*
 *---------------------------------------------------------------
 * ERROR REPORTING
 *---------------------------------------------------------------
 *
 * Different environments will require different levels of error reporting.
 * By default development will show errors but testing and live will hide them.
 */
    switch (ENVIRONMENT) {
        case 'development':
            error_reporting(-1);
            ini_set('display_errors', 1);
        break;

        case 'testing':
        case 'production':
            error_reporting(E_ALL ^ E_NOTICE ^ E_DEPRECATED ^ E_STRICT);
            ini_set('display_errors', 0);
        break;

        default:
            header('HTTP/1.1 503 Service Unavailable.', TRUE, 503);
            die('The application environment is not set correctly.');
    }

/*
 * ---------------------------------------------------------------
 *  Resolve the system path for increased reliability
 * ---------------------------------------------------------------
 */
    // Make sure environment system path
    $system_path = $system_path[ENVIRONMENT];

    // Set the current directory correctly for CLI requests
    if (defined('STDIN')) {
        chdir(dirname(__FILE__));
    }

    if (($_temp = realpath($system_path)) !== FALSE) {
        $system_path = $_temp . DIRECTORY_SEPARATOR;
    }
    else {
        // Ensure there's a trailing slash
        $system_path = rtrim($system_path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
    }

    // Is the system path correct?
    if ( ! is_dir($system_path)) {
        header('HTTP/1.1 503 Service Unavailable.', TRUE, 503);
        die('Your system folder path does not appear to be set correctly. Please open the following file and correct this: '.pathinfo(__FILE__, PATHINFO_BASENAME));
    }

/*
 * -------------------------------------------------------------------
 *  Now that we know the path, set the main path constants
 * -------------------------------------------------------------------
 */
    // The name of THIS file
    define('ROOT', pathinfo(__FILE__, PATHINFO_BASENAME));

    // Path to the system folder
    define('SYSPATH', $system_path);

    // Path to the front controller (this file)
    define('BASEPATH', str_replace(ROOT, '', __FILE__));

    // Name of the "system folder"
    define('SYSDIR', trim(strrchr(trim(SYSPATH, DIRECTORY_SEPARATOR), DIRECTORY_SEPARATOR), DIRECTORY_SEPARATOR));

    // Make sure environment application folder path
    $application_folder = $application_folder[ENVIRONMENT];

    // The path to the "application" folder
    if (is_dir($application_folder)) {
        if (($_temp = realpath($application_folder)) !== FALSE) {
            $application_folder = $_temp;
        }

        define('APPPATH', $application_folder . DIRECTORY_SEPARATOR);
    }
    else {
        if ( ! is_dir(BASEPATH.$application_folder . DIRECTORY_SEPARATOR)) {
            header('HTTP/1.1 503 Service Unavailable.', TRUE, 503);
            die('Your application folder path does not appear to be set correctly. Please open the following file and correct this: '.ROOT);
        }

        define('APPPATH', BASEPATH . $application_folder . DIRECTORY_SEPARATOR);
    }

/*
 * --------------------------------------------------------------------
 * LOAD THE BOOTSTRAP FILE
 * --------------------------------------------------------------------
 *
 * And away we go...
 */
require_once SYSPATH . 'minicode.php';

// End of file index.php
// By Minicode