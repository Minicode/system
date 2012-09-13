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

if ( ! defined('PHP_VERSION_ID') || PHP_VERSION_ID < 50300)
    die('Minicode framework requires PHP 5.3 or higher');

/*
 * ------------------------------------------------------
 *  Load the global functions
 * ------------------------------------------------------
 */
    require(SYSPATH . 'func/common.php');

/*
 * ------------------------------------------------------
 *  Registered automatic loading mechanism
 * ------------------------------------------------------
 */
    spl_autoload_register('PHP_MINICODE_AUTOLOAD');

/*
 * ------------------------------------------------------
 *  Dispatcher the requested controller
 * ------------------------------------------------------
 */
    $DISP = new MC_Dispatcher;

/*
 * ------------------------------------------------------
 *  Call the requested method
 * ------------------------------------------------------
 */
    $DISP->call_method();

// End of file index.php
// By Minicode