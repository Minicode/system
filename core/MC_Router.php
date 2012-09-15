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
 * MC_Router Class
 *
 * Parses URIs and determines routing
 *
 * @package       Minicode
 * @category      Core
 * @subpackage    MC_Router
 * @author        Wanglong
 * @link          http://minicode.org/docs/core/mc_router
 * @since         Version 1.0
 */

class MC_Router extends MC_Object {

    /**
     * Current class name
     *
     * @access  private
     * @var     string
     */
    private $class                        = '';

    // --------------------------------------------------------------------

    /**
     * Current method name
     *
     * @access  private
     * @var     string
     */
    private $method                       = '';

    // --------------------------------------------------------------------

    /**
     * Sub-directory that contains the requested controller class
     *
     * @access  private
     * @var     string
     */
    private $directory                    = '';

    // --------------------------------------------------------------------

    /**
     * Constructor
     *
     * Runs the route mapping function.
     * It require URI class supporting.
     *
     * @access  public
     * @return  void
     */
    public function __construct() {
        $this->uri = MC_URI::instance();
        $this->cfg = MC_Config::instance();
        $this->cfg->load('config');
        $this->cfg->load('routes', 'route', TRUE);
        
        $this->routes = $this->cfg->section('routes');
    }

    // --------------------------------------------------------------------

    /**
     * Set the route mapping
     *
     * This function determines what should be served based on
     * the URI request,as well as any routes that have been set 
     * in the class properties.
     *
     * @access  public
     * @return  void
     */
    public function set_routing() {
        // Are query strings enabled in the config file? Normally phpRails doesn't utilize query strings
        // since URI segments are more search-engine friendly, but they can optionally be used.
        // If this feature is enabled, we will gather the directory/class/method a little differently
        $segments = array();
        if (strtoupper($this->cfg->uri_protocol) === 'QUERY_STRING' && isset($_GET[$this->cfg->controller_trigger])) {
            if (isset($_GET[$this->cfg->directory_trigger])) {
                $this->set_directory(trim($this->uri->filter_uri($_GET[$this->cfg->directory_trigger])));
                $segments[] = $this->fetch_directory();
            }

            if (isset($_GET[$this->cfg->controller_trigger])) {
                $this->set_class(trim($this->uri->filter_uri($_GET[$this->cfg->controller_trigger])));
                $segments[] = $this->fetch_class();
            }

            if (isset($_GET[$this->cfg->function_trigger])) {
                $this->set_method(trim($this->uri->filter_uri($_GET[$this->cfg->function_trigger])));
                $segments[] = $this->fetch_method();
            }
        }

        // Were there any query string segments? If so, we'll validate them and bail out since we're done.
        if (count($segments) > 0) {
            return $this->set_request($segments);
        }

        // Fetch the complete URI string
        $this->uri->fetch_uri_string();

        // Is there a URI string? If not, run the default controller.
        if ($this->uri->uri_string() == '') {
            return $this->set_default_request();
        }

        $this->uri->remove_url_suffix(); // Remove the URL suffix
        $this->uri->explode_segments(); // Compile the segments into an array
        $this->parse_routes(); // Parse any custom routing that may exist
        $this->uri->reindex_segments(); // Re-index the segment array so that it starts with 1 rather than 0
    }

    // --------------------------------------------------------------------

    /**
     * Validates the 404 segments. Attempts to determine the path to 
     * the 404 page of controller
     *
     * @access  public
     * @return  array
     */
    public function validate_override_404() {
        static $x;

        if (empty($x)) {

            $override_404 = $this->routes->override_404;

            if (empty($override_404)) {
                die('The page you requested was not found.');
            }

            $override_404 = trim($override_404, '/');

            $x = explode('/', $override_404);
            $this->set_directory('');

            if ($this->has_file($x[0])) {
                return $x;
            }

            if (count($x) > 1 && $this->has_dir($x[0])) {
                $this->set_directory($x[0]);
                return array_slice($x, 1);
            }
        }

        return $x;
    }

    // --------------------------------------------------------------------

    /**
     * Set the class name
     *
     * @access  public
     * @param   string
     * @return  void
     */
    public function set_class($class) {
        $this->class = ucfirst(str_replace(array('/', '.'), '', $class));
    }

    // --------------------------------------------------------------------

    /**
     * Fetch the current class
     *
     * @access  public
     * @return  string
     */
    public function fetch_class() {
        return $this->class;
    }

    // --------------------------------------------------------------------

    /**
     * Set the method name
     *
     * @access  public
     * @param   string
     * @return  void
     */
    public function set_method($method) {
        $this->method = $method;
    }

    // --------------------------------------------------------------------

    /**
     * Fetch the current method
     *
     * @access  public
     * @return  string
     */
    public function fetch_method() {
        return ($this->method === $this->fetch_class()) ? $this->routes->default_action : $this->method;
    }

    // --------------------------------------------------------------------

    /**
     * Set the directory name
     *
     * @access  public
     * @param   string
     * @return  void
     */
    public function set_directory($dir) {
        $this->directory = strtolower(str_replace(array('/', '.'), '', $dir)).'/';
    }

    // --------------------------------------------------------------------

    /**
     * Fetch the sub-directory (if any) that contains the requested 
     * controller class
     *
     * @access  public
     * @return  string
     */
    public function fetch_directory() {
        return $this->directory;
    }

    // --------------------------------------------------------------------

    /**
     * Set the default request
     *
     * @access  private
     * @return  void
     */
    private function set_default_request() {
        $this->set_directory($this->routes->default_directory);
        $this->set_class($this->routes->default_controller);
        $this->set_method($this->routes->default_action);

        $this->uri->set_rsegments(array($this->routes->default_controller, $this->routes->default_action));

        // re-index the routed segments array so it starts with 1 rather than 0
        $this->uri->reindex_segments();
    }

    // --------------------------------------------------------------------

    /**
     * Set the request
     *
     * This function takes an array of URI segments as
     * input, and sets the current class/method
     *
     * @access  private
     * @param   array
     * @param   bool
     * @return  void
     */
    private function set_request($segments = array()) {
        $segments = $this->validate_segments($segments);

        if (count($segments) == 0) {
            return $this->set_default_request();
        }

        $this->set_class($segments[0]);

        if (isset($segments[1])) {
            // A standard method request
            $this->set_method($segments[1]);
        }
        else {
            // This lets the "routed" segment array identify that the default
            // index method is being used.
            $this->set_method($segments[1] = $this->routes->default_action);
        }

        // Update our "routed" segment array to contain the segments.
        // Note: If there is no custom routing, this array will be
        // identical to $this->uri->segments
        $this->uri->set_rsegments($segments);
    }

    // --------------------------------------------------------------------

    /**
     * The filename prefix of the file exists
     *
     * @access  private
     * @param   string
     * @return  boolean
     */
    private function has_file($pre_name) {
        return file_exists(APPPATH.'/controllers/'.strtolower($pre_name).'.php');
    }

    // --------------------------------------------------------------------

    /**
     * The prename of the dir exists
     *
     * @access  private
     * @param   string
     * @return  boolean
     */
    private function has_dir($pre_name) {
        return is_dir(APPPATH.'/controllers/'.strtolower($pre_name));
    }

    // --------------------------------------------------------------------

    /**
     * Validates the supplied segments.  Attempts to determine the path to
     * the controller.
     *
     * @access  private
     * @param   array
     * @return  array
     */
    private function validate_segments($segments) {
        if (count($segments) == 0) {
            return $segments;
        }

        // Does the requested controller exist in the root folder?
        if ($this->has_file($segments[0])) {
            return $segments;
        }

        // Is the controller in a sub-folder?
        if ($this->has_dir($segments[0])) {
            // Set the directory and remove it from the segment array
            $this->set_directory($segments[0]);
            $segments = array_slice($segments, 1);

            $segments[0] = count($segments) > 0 ? $segments[0] : $this->routes->default_controller;

            if ( ! $this->has_file($this->fetch_directory().$segments[0])) {
                return $this->validate_override_404();
            }
            return $segments;
        }

        // If we've gotten this far it means that the URI does not correlate to a valid
        // controller class.  We will now see if there is an override
        return $this->validate_override_404();
    }

    // --------------------------------------------------------------------

    /**
     * Parse Routes
     *
     * This function matches any routes that may exist in
     * the remap property against the URI to
     * determine if the class/method need to be remapped.
     *
     * @access  protected
     * @return  void
     */
    protected function parse_routes() {
        // Turn the segment array into a URI string
        $uri = implode('/', $this->uri->segment_array());

        // Is there a literal match?  If so we're done
        if ($this->routes->item($uri)) {
            return $this->set_request(explode('/', $this->routes->item($uri)));
        }

        // Loop through the route array looking for wild-cards
        foreach ($this->routes->item() as $key => $val) {
            // Convert wild-cards to RegEx
            $key = str_replace(array(':any', ':num'), array('.+', '[0-9]+'), $key);

            // Does the RegEx match?
            if (preg_match('#^'.$key.'$#', $uri)) {
                // Do we have a back-reference?
                if (strpos($val, '$') !== FALSE && strpos($key, '(') !== FALSE) {
                    $val = preg_replace('#^'.$key.'$#', $val, $uri);
                }

                return $this->set_request(explode('/', $val));
            }
        }

        // If we got this far it means we didn't encounter a
        // matching route so we'll set the site default route
        $this->set_request($this->uri->segment_array());
    }
}
// END MC_Router Class
// By Minicode