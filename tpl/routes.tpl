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
 * URI ROUTING
 *
 * This file lets you re-map URI requests to specific controller functions.
 * Typically there is a one-to-one relationship between a URL string
 * and its corresponding controller class/method. The segments in a
 * URL normally follow this pattern:
 *
 * example.com/class/method/id/
 *
 * In some instances, however, you may want to remap this relationship
 * so that a different class/function is called than the one
 * corresponding to the URL.
 */

// RESERVED ROUTES
// This route indicates which controller class should be loaded if the
// URI contains no data. In the below example, the default route path
// would be loaded, All controllers default action is 'index' method.

$route['default_directory']    = '';
$route['default_controller']   = 'welcome';
$route['default_action']       = 'index';

// This route will tell the Router what URI segments to use if those
// provided in the URL cannot be matched to a valid route.
// eg: $route['override_404'] = 'errors/page_missing';

$route['override_404'] = '';

// These matching routes will remap to the specified uri,
// It supports the regular expression or wildcards.
// eg: $route['users/(:num)'] = 'users/show/$1'

// End of file routes.php
// By Minicode