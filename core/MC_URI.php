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
 * MC_URI Class
 *
 * Parses URIs and determines routing
 *
 * @category      Core
 * @package       MC_URI
 * @author        Wanglong
 * @link          http://phprails.com/docs/core/mc_uri
 * @since         Version 1.0
 */

class MC_URI extends MC_Object {

    /**
     * Root path file
     *
     * @access public
     * @var string
     */
    public $root_file           = 'index.php';  
     
    // --------------------------------------------------------------------
     
    /**
     * List of cached uri segments
     *
     * @access private
     * @var    array
     */
    private $keyval             = array();

    // --------------------------------------------------------------------
     
    /**
     * List of uri segments
     *
     * @access private
     * @var    array
     */
    private $segments           = array();

    // --------------------------------------------------------------------
     
    /**
     * Re-indexed list of uri segments
     * Starts at 1 instead of 0
     *
     * @access private
     * @var    array
     */
    private $rsegments          = array();

    // --------------------------------------------------------------------

    /**
     * Current uri string
     *
     * @access private
     * @var    string
     */
    private $uri_string;

    // --------------------------------------------------------------------

    /**
     * Constructor
     *
     * @access    public
     */
    public function __construct() {
        $this->cfg = MC_Config::instance();
        $this->cfg->load('config');
    }

    // --------------------------------------------------------------------

    /**
     * Get the URI String
     *
     * @access    public
     * @return    string
     */
    public function fetch_uri_string() {
        if (strtoupper($this->cfg->item('uri_protocol')) == 'AUTO') {
            // Is the request coming from the command line?
            if (php_sapi_name() == 'cli' or defined('STDIN')) {
                $this->set_uri_string($this->parse_cli_args());
                return $this->uri_string;
            }

            // Let's try the REQUEST_URI first, this will work in most situations
            if ($uri = $this->auto_detect_uri()) {
                $this->set_uri_string($uri);
                return $this->uri_string;
            }

            // Is there a PATH_INFO variable?
            // Note: some servers seem to have trouble with getenv() so we'll test it two ways
            $path = (isset($_SERVER['PATH_INFO'])) ? $_SERVER['PATH_INFO'] : @getenv('PATH_INFO');
            if (trim($path, '/') != '' && $path != "/" . $this->root_file) {
                $this->set_uri_string($path);
                return $this->uri_string;
            }

            // No PATH_INFO?... What about QUERY_STRING?
            $path =  (isset($_SERVER['QUERY_STRING'])) ? $_SERVER['QUERY_STRING'] : @getenv('QUERY_STRING');
            if (trim($path, '/') != '') {
                $this->set_uri_string($path);
                return $this->uri_string;
            }

            // As a last ditch effort lets try using the $_GET array
            if (is_array($_GET) && count($_GET) == 1 && trim(key($_GET), '/') != '') {
                $this->set_uri_string(key($_GET));
                return $this->uri_string;
            }

            // We've exhausted all our options...
            $this->uri_string = '';
            return $this->uri_string;
        }

        $uri = strtoupper($this->cfg->item('uri_protocol'));

        if ($uri == 'REQUEST_URI') {
            $this->set_uri_string($this->auto_detect_uri());
            return $this->uri_string;
        }
        elseif ($uri == 'CLI') {
            $this->set_uri_string($this->_parse_cli_args());
            return $this->uri_string;
        }

        $path = isset($_SERVER[$uri]) ? $_SERVER[$uri] : @getenv($uri);
        $this->set_uri_string($path);

        return $this->uri_string;
    }

    // --------------------------------------------------------------------

    /**
     * Set the URI String
     *
     * @access    public
     * @param     string
     * @return    string
     */
    public function set_uri_string($str) {
        // Filter out control characters
        //$str = $this->remove_invisible_characters($str, FALSE);

        // If the URI contains only a slash we'll kill it
        $this->uri_string = ($str == '/') ? '' : $str;
        return $str;
    }

    // --------------------------------------------------------------------

    /**
     * Fetch a URI Segment
     *
     * This function returns the URI segment based on the number provided.
     *
     * @access    public
     * @param     integer
     * @param     bool
     * @return    string
     */
    public function segment($n, $no_result = FALSE) {
        return ( ! isset($this->segments[$n])) ? $no_result : $this->segments[$n];
    }

    // --------------------------------------------------------------------

    /**
     * Fetch a URI "routed" Segment
     *
     * This function returns the re-routed URI segment (assuming 
     * routing rules are used) based on the number provided.  If 
     * there is no routing this function returns the same result 
     * as $this->segment()
     *
     * @access    public
     * @param     integer
     * @param     bool
     * @return    string
     */
    public function rsegment($n, $no_result = FALSE) {
        return ( ! isset($this->rsegments[$n])) ? $no_result : $this->rsegments[$n];
    }

    // --------------------------------------------------------------------

    /**
     * Fetch a URI Segment and add a trailing slash
     *
     * @access    public
     * @param     integer
     * @param     string
     * @return    string
     */
    public function slash_segment($n, $where = 'trailing') {
        return $this->_slash_segment($n, $where, 'segment');
    }

    // --------------------------------------------------------------------

    /**
     * Fetch a URI Segment and add a trailing slash
     *
     * @access    public
     * @param     integer
     * @param     string
     * @return    string
     */
    public function slash_rsegment($n, $where = 'trailing') {
        return $this->_slash_segment($n, $where, 'rsegment');
    }

    // --------------------------------------------------------------------

    /**
     * Set the segment Array
     *
     * @access    public
     * @param     array
     * @return    array
     */
    public function set_segments($segments) {
        return $this->segments = $segments;
    }

    // --------------------------------------------------------------------
    
    /**
     * Set the routed segment Array
     *
     * @access    public
     * @param     array
     * @return    array
     */
    public function set_rsegments($rsegments) {
         return $this->rsegments = $rsegments;
    }

    // --------------------------------------------------------------------

    /**
     * Segment Array
     *
     * @access    public
     * @return    array
     */
    public function segment_array() {
        return $this->segments;
    }

    // --------------------------------------------------------------------

    /**
     * Routed Segment Array
     *
     * @access    public
     * @return    array
     */
    public function rsegment_array() {
        return $this->rsegments;
    }

    // --------------------------------------------------------------------

    /**
     * Total number of segments
     *
     * @access    public
     * @return    integer
     */
    public function total_segments() {
        return count($this->segments);
    }

    // --------------------------------------------------------------------

    /**
     * Total number of routed segments
     *
     * @access    public
     * @return    integer
     */
    public function total_rsegments() {
        return count($this->rsegments);
    }

    // --------------------------------------------------------------------

    /**
     * Generate a key value pair from the URI string
     *
     * This function generates and associative array of URI data starting
     * at the supplied segment. For example, if this is your URI:
     *
     *    example.com/user/search/name/joe/location/UK/gender/male
     *
     * You can use this function to generate an array with this prototype:
     *
     * array (
     *            name => joe
     *            location => UK
     *            gender => male
     *       )
     *
     * @access    public
     * @param     integer     the starting segment number
     * @param     array      an array of default values
     * @return    array
     */
    public function uri_to_assoc($n = 3, $default = array()) {
        return $this->_uri_to_assoc($n, $default, 'segment');
    }

    // --------------------------------------------------------------------

    /**
     * Identical to above only it uses the re-routed segment array
     *
     * @access     public
     * @param      integer    the starting segment number
     * @param      array      an array of default values
     * @return     array
     *
     */
    public function ruri_to_assoc($n = 3, $default = array()) {
        return $this->_uri_to_assoc($n, $default, 'rsegment');
    }

    // --------------------------------------------------------------------

    /**
     * Generate a URI string from an associative array
     *
     *
     * @access    public
     * @param     array    an associative array of key/values
     * @return    array
     */
    public function assoc_to_uri($array) {
        $temp = array();
        foreach ((array)$array as $key => $val) {
            $temp[] = $key;
            $temp[] = $val;
        }

        return implode('/', $temp);
    }

    // --------------------------------------------------------------------

    /**
     * Filter segments for malicious characters
     *
     * @access    public
     * @param     string
     * @return    string
     */
    public function filter_uri($str) {
        if ($str !== '' && $this->cfg->item('permitted_uri_chars') != '' && $this->cfg->item('enable_query_strings') === FALSE) {
            // preg_quote() in PHP 5.3 escapes -, so the str_replace() and addition of - to preg_quote() is to maintain backwards
            // compatibility as many are unaware of how characters in the permitted_uri_chars will be parsed as a regex pattern
            if ( ! preg_match('|^['.str_replace(array('\\-', '\-'), '-', preg_quote($this->cfg->item('permitted_uri_chars'), '-')).']+$|i', urldecode($str))) {
                die('The URI you submitted has disallowed characters.');
            }
        }

        // Convert programatic characters to entities and return
        return str_replace(
                    array('$',     '(',     ')',     '%28',   '%29'), // Bad
                    array('&#36;', '&#40;', '&#41;', '&#40;', '&#41;'), // Good
                    $str);
    }

    // --------------------------------------------------------------------

    /**
     * Fetch the entire URI string
     *
     * @access    public
     * @return    string
     */
    public function uri_string() {
        return $this->uri_string;
    }

    // --------------------------------------------------------------------

    /**
     * Fetch the entire Re-routed URI string
     *
     * @access    public
     * @return    string
     */
    public function ruri_string() {
        return '/'.implode('/', $this->rsegment_array());
    }

    // --------------------------------------------------------------------

    /**
     * Remove the suffix from the URL if needed
     *
     * @access    public
     * @return    void
     */
    public function remove_url_suffix() {
        $suffix = (string) $this->cfg->item('url_suffix');

        if ($suffix !== '' && ($offset = strrpos($this->uri_string, $suffix)) !== FALSE) {
            $this->uri_string = substr_replace($this->uri_string, '', $offset, strlen($suffix));
        }
    }

    // --------------------------------------------------------------------

    /**
     * Explode the URI Segments. The individual segments will
     * be stored in the $this->segments array.
     *
     * @access    public
     * @return    void
     */
    public function explode_segments() {
        foreach (explode("/", preg_replace("|/*(.+?)/*$|", "\\1", $this->uri_string)) as $val) {
            // Filter segments for security
            $val = trim($this->filter_uri($val));

            if ($val != '') {
                $this->segments[] = $val;
            }
        }
    }

    // --------------------------------------------------------------------

    /**
     * Re-index Segments
     *
     * This function re-indexes the $this->segment array so that it
     * starts at 1 rather than 0.  Doing so makes it simpler to
     * use functions like $this->uri->segment(n) since there is
     * a 1:1 relationship between the segment array and the actual segments.
     *
     * @access    public
     * @return    void
     */
    public function reindex_segments() {
        array_unshift($this->segments, NULL);
        array_unshift($this->rsegments, NULL);
        unset($this->segments[0]);
        unset($this->rsegments[0]);
    }

    // --------------------------------------------------------------------

    /**
     * Detects the URI
     *
     * This function will detect the URI automatically (pathinfo) and fix the query string
     * if necessary.
     *
     * @access    private
     * @return    string
     */
    private function auto_detect_uri() {
        if ( ! isset($_SERVER['REQUEST_URI'], $_SERVER['SCRIPT_NAME'])) {
            return '';
        }

        if (strpos($_SERVER['REQUEST_URI'], $_SERVER['SCRIPT_NAME']) === 0) {
            $uri = substr($_SERVER['REQUEST_URI'], strlen($_SERVER['SCRIPT_NAME']));
        }
        elseif (strpos($_SERVER['REQUEST_URI'], dirname($_SERVER['SCRIPT_NAME'])) === 0) {
            $uri = substr($_SERVER['REQUEST_URI'], strlen(dirname($_SERVER['SCRIPT_NAME'])));
        }
        else {
            $uri = $_SERVER['REQUEST_URI'];
        }

        // This section ensures that even on servers that require the URI to be in the query string (Nginx) a correct
        // URI is found, and also fixes the QUERY_STRING server var and $_GET array.
        if (strpos($uri, '?/') === 0) {
            $uri = substr($uri, 2);
        }

        $parts = explode('?', $uri, 2);
        $uri = $parts[0];
        if (isset($parts[1])) {
            $_SERVER['QUERY_STRING'] = $parts[1];
            parse_str($_SERVER['QUERY_STRING'], $_GET);
        }
        else {
            $_SERVER['QUERY_STRING'] = '';
            $_GET = array();
        }

        if ($uri === '/' OR empty($uri)) {
            return '/';
        }

        $uri = parse_url('pseudo://hostname/'.$uri, PHP_URL_PATH);

        // Do some final cleaning of the URI and return it
        return str_replace(array('//', '../'), '/', trim($uri, '/'));
    }

    // --------------------------------------------------------------------

    /**
     * Parse cli arguments
     *
     * Take each command line argument and assume it is a URI segment.
     *
     * @access    private
     * @return    string
     */
    private function parse_cli_args() {
        $args = array_slice($_SERVER['argv'], 1);
        return $args ? '/' . implode('/', $args) : '';
    }

    // --------------------------------------------------------------------

    /**
     * Generate a key value pair from the URI string or Re-routed URI string
     *
     * @access    private
     * @param     integer    the starting segment number
     * @param     array      an array of default values
     * @param     string     which array we should use
     * @return    array
     */
    private function _uri_to_assoc($n = 3, $default = array(), $which = 'segment') {
        if ($which == 'segment') {
            $total_segments = 'total_segments';
            $segment_array  = 'segment_array';
        }
        else {
            $total_segments = 'total_rsegments';
            $segment_array  = 'rsegment_array';
        }

        if ( ! is_numeric($n)) {
            return $default;
        }

        if (isset($this->keyval[$n])) {
            return $this->keyval[$n];
        }

        if ($this->$total_segments() < $n) {
            if (count($default) == 0) {
                return array();
            }

            $retval = array();
            foreach ($default as $val) {
                $retval[$val] = FALSE;
            }
            return $retval;
        }

        $segments = array_slice($this->$segment_array(), ($n - 1));

        $i = 0;
        $lastval = '';
        $retval  = array();
        foreach ($segments as $seg) {
            if ($i % 2) {
                $retval[$lastval] = $seg;
            }
            else {
                $retval[$seg] = FALSE;
                $lastval = $seg;
            }

            $i++;
        }

        if (count($default) > 0) {
            foreach ($default as $val) {
                if ( ! array_key_exists($val, $retval)) {
                    $retval[$val] = FALSE;
                }
            }
        }

        // Cache the array for reuse
        $this->keyval[$n] = $retval;
        return $retval;
    }

    // --------------------------------------------------------------------

    /**
     * Fetch a URI Segment and add a trailing slash - helper function
     *
     * @access    private
     * @param     integer
     * @param     string
     * @param     string
     * @return    string
     */
    private function _slash_segment($n, $where = 'trailing', $which = 'segment') {
        $leading    = '/';
        $trailing    = '/';

        if ($where == 'trailing') {
            $leading    = '';
        }
        elseif ($where == 'leading') {
            $trailing    = '';
        }

        return $leading.$this->$which($n).$trailing;
    }
}
// END MC_URI Class
// By Minicode