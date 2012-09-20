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
 * MC_Dispatcher Class
 *
 * Protectd the route on the actual controller and method of invoking.
 *
 * All the controller file naming rules different from class library, 
 * because it comes from url, in order to ensure compatibility, so the 
 * controller file names must be lower-case, and the class name best 
 * with an uppercase letter or camel style. 
 * (PHP class name for not case-insensitive)
 *
 * @package       Minicode
 * @category      Core
 * @subpackage    MC_Dispatcher
 * @author        Wanglong
 * @link          http://minicode.org/docs/core/mc_dispatcher
 * @since         Version 1.0
 */

class MC_Dispatcher {

    /**
     * Controller instance
     *
     * @access private
     * @var    string
     */
    private $controller;

    // --------------------------------------------------------------------

    /**
     * Class public methods
     *
     * @access private
     * @var    string
     */
    private $class_methods = array();

    // --------------------------------------------------------------------

    /**
     * Constructor
     *
     * Structure is equipped with all the necessary parameters, 
     * check the file and class and check whether to allow calls.
     *
     * @access  public
     * @return  void
     */
    public function __construct() {
        $this->uri       = MC_URI::instance();
        $this->router    = MC_Router::instance();
        $this->router->set_routing();

        $this->directory = $this->router->fetch_directory();
        $this->class     = $this->router->fetch_class();
        $this->method    = $this->router->fetch_method();

        // check and create controller instance
        $this->security_check();
    }

    // --------------------------------------------------------------------

    /**
     * Validate and Call the right method
     *
     * @access  public
     * @return  void
     */
    public function call_method() {
        $this->action_filter('before_filter');

        if (method_exists($this->controller, '_remap')) {
            $this->method = '_remap';
        }
        else {
            // is_callable() returns TRUE on some versions of PHP 5 for private and protected
            // methods, so we'll use this workaround for consistent behavior
            if ( ! in_array(strtolower($this->method), $this->class_methods)) {
                if (method_exists($this->controller, '_missing')) {
                    $this->method = '_missing';
                }
                else {
                    $this->reform_override_404();
                }
            }
        }

        call_user_func_array(array($this->controller, $this->method), array_slice($this->uri->rsegment_array(), 2));
    }

    // --------------------------------------------------------------------

    /**
     * Executive method filter
     *
     * @access  public
     * @param   string
     * @return  void
     */
    public function action_filter($static_property) {
        $instance = $this->controller;
        if( ! empty($instance) && property_exists($instance, $static_property)) {
            // get the specified static attributes
            $option = $instance::$$static_property;
            $option = isset($option[0]) && is_array($option[0]) ? $option : array($option);
            $parent_name = get_class($this->controller);

            // rebase parent attribute
            while ($parent_name = get_parent_class($parent_name)) {
                $parent_vars = get_class_vars($parent_name);
                if (isset($parent_vars[$static_property])) {
                    $parent_option = isset($parent_vars[$static_property][0])
                                     ? $parent_vars[$static_property]
                                     : array($parent_vars[$static_property]);
                }
                $this->merge_unique($option, $parent_option);
            }

            for ($i=0,$l=count($option); $i < $l; $i++) { 
                $this->parse_filter($option[$i]);
            }
        }
    }

    // --------------------------------------------------------------------

    /**
     * Parses filter options
     *
     * @access  public
     * @param   array
     * @return  void
     */
    private function parse_filter(&$option) {
        if (empty($option[0])) {
            return;
        }

        $except = empty($option['except']) ? array() : $option['except'];
        $only   = empty($option['only']) ? $this->class_methods : $option['only'];

        if ( ! in_array($this->method, $except) && in_array($this->method, $only)) {
            if (in_array(strtolower($option[0]), $this->class_methods)) {
                call_user_func_array(array(&$this->controller, $option[0]), array_slice($this->uri->rsegment_array(), 2));
            }
            else {
                die('Cannot access the ' . $this->class . '::' . $this->method . '() Private or Protected Method');
            }
        }
    }

    // --------------------------------------------------------------------

    /**
     * Merge array and eliminate repeated value
     *
     * @access  public
     * @param   array
     * @param   array
     * @return  array
     */
    private function merge_unique(&$a, &$b) {
        if ( ! isset($b) || empty($b)){
            return $a;
        }

        for ($i = 0, $l = count($b); $i < $l; $i++) {
            if ( ! in_array($b[$i], $a)){
                $a[] = $b[$i];
            }
        }

        return $a;
    }

    // --------------------------------------------------------------------

    /**
     * None of the functions in the app controller can be 
     * called via the URI, nor can controller functions 
     * that begin with an underscore. And create the 
     * Controller instance.
     *
     * @access  private
     * @return  void
     */
    private function security_check() {
        if ( ! file_exists(APPPATH.'controllers/'.$this->directory.$this->class.'.php')) {
            die('Unable to load your default or 404 override controller. Please make sure that the controller path is correct.');
        }

        if (strpos($this->method, '_') === 0) {
            $this->reform_override_404();
        }
        else {
            include(APPPATH.'controllers/'.$this->directory.$this->class.'.php');

            if ( ! class_exists($this->class)) {
                die('Class \''.$this->class.'\' not found.');
            }

            // instantiate the requested controller
            $this->controller = new $this->class;
        }

        // get all class methods
        $this->class_methods = array_map('strtolower', get_class_methods($this->controller));
    }

    // --------------------------------------------------------------------

    /**
     * According to the 404 override redistribution class and method.
     *
     * @access  private
     * @return  void
     */
    private function reform_override_404() {
        $x = $this->router->validate_override_404();
        $this->directory = $this->router->fetch_directory();
        $this->class     = ucfirst($x[0]);
        $this->method    = isset($x[1]) ? $x[1] : 'index';

        if ( ! class_exists($this->class)) {
            if ( ! file_exists(APPPATH.'controllers/'.$this->directory . $this->class.'.php')) {
                die('Unable to load your 404 override controller. Please make sure that the controller path is correct.');
            }

            include_once(APPPATH.'controllers/'.$this->directory . $this->class.'.php');

            if ( ! class_exists($this->class)) {
                die('Class \''.$this->class.'\' not found.');
            }

            $this->controller = new $this->class;
        }

        if ( ! method_exists($this->controller, $this->method)) {
            die('Not Found the Override 404 \'' . $this->method . '\' Method');
        }
    }
}
// END MC_Dispatcher Class
// By Minicode