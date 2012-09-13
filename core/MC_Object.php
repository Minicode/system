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
 * Can be used as the base class for all classes
 *
 * This implementation of the singleton pattern does not conform to the strong definition
 * given by the "Gang of Four." The __construct() method has not be privatized so that
 * a singleton pattern is capable of being achieved; however, multiple instantiations are also
 * possible. This allows the user more freedom with this pattern.
 *
 * @package       Minicode
 * @category      Core
 * @subpackage    MC_Object
 * @author        Wanglong
 * @link          http://minicode.org/docs/core/mc_object
 * @since         Version 1.0
 */

abstract class MC_Object {

    /**
     * Array of cached singleton objects.
     *
     * @access private
     * @var array
     */
    private static $instances = array();

    // --------------------------------------------------------------------

    /**
     * Static method for instantiating a singleton object.
     *
     * @access public
     * @return object
     */
    final public static function instance() {
        $class_name = get_called_class();

        if (!isset(self::$instances[$class_name]))
            self::$instances[$class_name] = new $class_name;

        return self::$instances[$class_name];
    }
}
// END MC_Object Class
// By Minicode