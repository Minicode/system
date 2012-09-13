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
 * MC_Controller Class
 *
 * Controller base class, defined some method and classes 
 * method in there to fast call.
 *
 * @package       Minicode
 * @category      Core
 * @subpackage    MC_Controller
 * @author        Wanglong
 * @link          http://minicode.org/docs/core/mc_controller
 * @since         Version 1.0
 */

class MC_Controller {

    /**
     * Call the view class assign method
     *
     * @access public
     * @var    string
     */
	public function assign(/* key, value */) {
		call_user_func_array(array(MC_View::instance(), 'assign'), func_get_args());
	}

    /**
     * Call the view class render method
     *
     * @access public
     * @var    string
     */
	public function render(/* args */) {
		call_user_func_array(array(MC_View::instance(), 'render'), func_get_args());
	}
}
// END MC_Controller Class
// By Minicode