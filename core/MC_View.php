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
 * MC_View Class
 *
 * The view class have a simple tag analytic, In the view they 
 * and native PHP mixed using, they just general variable labels 
 * and constant label, there are also some regular expression 
 * type label, by default it is open, because be lightweight. 
 * If the parse closed using native PHP can also achieve higher 
 * performance.
 *
 * @package       Minicode
 * @category      Core
 * @subpackage    MC_View
 * @author        Wanglong
 * @link          http://minicode.org/docs/core/mc_view
 * @since         Version 1.0
 */

class MC_View extends MC_Object {

	/**
     * Tag boundary left character setting
     *
     * @access public
     * @var    string
     */
	public $qualifier_left  = '{';

	// --------------------------------------------------------------------

	/**
     * Tag boundary right character setting
     *
     * @access public
     * @var    string
     */
	public $qualifier_right = '}';

	// --------------------------------------------------------------------

	/**
     * Temporary preservation registered to the variable
     *
     * @access private
     * @var    array
     */
	private $vars            = array();

	// --------------------------------------------------------------------

	/**
     * Temporary preservation registered to the tags
     *
     * @access private
     * @var    array
     */
	private $tags            = array();

	// --------------------------------------------------------------------

	/**
     * Temporary preservation registered to the expression tags
     *
     * @access private
     * @var    array
     */
	private $exps            = array();

	// --------------------------------------------------------------------

	/**
     * Constructor
     *
     * @access  public
     * @return  void
     */
	public function __construct() {
		$this->router    = MC_Router::instance();
		$this->directory = $this->router->fetch_directory();
		$this->class     = $this->router->fetch_class();
		$this->method    = $this->router->fetch_method();
	}

	// --------------------------------------------------------------------

	/**
     * View file render to display
     *
     * @access  public
     * @param   string
     * @param   boolean    using parse tags ?
     * @return  void
     */
	public function render($view_path = '', $parse = TRUE) {
		foreach($this->vars as $key => $value) {
			$$key = $value;

			if ($parse) {
				$this->set_tag($key, $value);
			}
		}

		unset($key);
		unset($value);

		if (empty($view_path)) {
			$view_path = $this->directory . strtolower($this->class) . '/' . $this->method;
		}

		if ( ! $file_path = realpath(APPPATH . "/views/" . $view_path . '.php')) {
			die('The \'views/' . $view_path . '.php\' view page file can not found.');
		}

		if ($parse) {
			ob_start();
			include $file_path;
			$buffer = ob_get_contents();
			$this->defined_replace();
			$this->parse_replace($buffer);
			ob_end_clean();
			echo $buffer;
		}
		else {
			include $file_path;
		}
	}

	// --------------------------------------------------------------------

	/**
     * Variable assigned to the view page
     *
     * @access  public
     * @param   string
     * @param   string
     * @return  void
     */
	public function assign($var_name, $var_value) {
		$this->vars[$var_name] = $var_value;
	}

	// --------------------------------------------------------------------

	/**
     * Setting a constant tag
     *
     * @access  public
     * @param   string
     * @param   string
     * @return  void
     */
	public function set_tag($key, $value) {
		$this->tags[$this->qualifier_left . $key . $this->qualifier_right] = $value;
	}

	// --------------------------------------------------------------------

	/**
     * Setting a expression tag
     *
     * @access  public
     * @param   string
     * @param   string
     * @return  void
     */
	public function set_exp($pattern, $replace) {
		$this->exps['~' . $this->qualifier_left . $pattern . $this->qualifier_right . '~'] = $replace;
	}

	// --------------------------------------------------------------------

	/**
     * Define some predefined tags
     *
     * @access  protected
     * @return  void
     */
	protected function defined_replace() {
        $base_url = base_url();

        // system constant tags are generally capital
		$this->set_tag('BASE_URL', $base_url);
        $this->set_tag('SCRIPT_URL', $base_url . SCRIPT . '/');
		$this->set_tag('CLASS'   , $this->class);
		$this->set_tag('METHOD'  , $this->method);

		// expression tags
		$this->set_exp('HREF:\s*(.*?)', $base_url . SCRIPT . '/' . '$1');
        $this->set_exp('(?:SRC|LINK|URL):\s*(.*?)', $base_url . '$1');
	}

	// --------------------------------------------------------------------

	/**
     * Tags analytic process
     *
     * @access  protected
     * @param   &string
     * @return  void
     */
	private function parse_replace(&$buffer) {
		// parse replace tags
		$tags = $this->tags;
		$pattern = '~(' . implode('|', array_keys($tags)) . ')~';
		$buffer = preg_replace_callback($pattern, 
			function($match) use($tags){ 
				return $tags[$match[0]];
			}, $buffer);

		// parse replace expression tags
		$buffer = preg_replace(array_keys($this->exps), array_values($this->exps), $buffer);
	}
}
// END MC_View Class
// By Minicode