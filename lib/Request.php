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
 * Request Class
 *
 * Pre-processes global request data
 *
 * @package       Minicode
 * @category      Libraries
 * @author        Wanglong
 * @link          http://minicode.org/docs/libraries/request
 */
class Request {

	/**
	 * IP address of the current user
	 *
	 * @var string
	 */
	public $ip_address =	FALSE;

	// --------------------------------------------------------------------

	/**
	 * user agent (web browser) being used by the current user
	 *
	 * @var string
	 */
	public $user_agent =	FALSE;

	// --------------------------------------------------------------------


	/**
	 * List of all HTTP request headers
	 *
	 * @var array
	 */
	protected $headers =	array();

	// --------------------------------------------------------------------

	/**
	 * Constructor
	 *
	 * @return	void
	 */
	public function __construct() {}

	// --------------------------------------------------------------------

	/**
	 * Fetch from array
	 *
	 * This is a helper function to retrieve values from global arrays
	 *
	 * @param	array
	 * @param	string
	 * @param	bool
	 * @return	string
	 */
	protected function fetch_from_array(&$array, $index = NULL, $xss_clean = FALSE) {
		$ret = $index === NULL && ! empty($array)
			? $array 
			: ( isset($array[$index])
				? $array[$index]
				: NULL );

		if ($xss_clean === TRUE) {
			$ret = $this->xss_clean($ret);
		}
		
		return $ret;
	}

	// --------------------------------------------------------------------

	/**
	 * XSS Clean
	 *
	 * Sanitizes data so that Cross Site Scripting Hacks can be
	 * prevented.  This function does a fair amount of work but
	 * it is extremely thorough, designed to prevent even the
	 * most obscure XSS attempts.  Nothing is ever 100% foolproof,
	 * of course, but I haven't been able to get anything passed
	 * the filter.
	 *
	 * Note: This function should only be used to deal with data
	 * upon submission. It's not something that should
	 * be used for general runtime processing.
	 *
	 * This function was based in part on some code and ideas I
	 * got from Bitflux: http://channel.bitflux.ch/wiki/XSS_Prevention
	 *
	 * To help develop this script I used this great list of
	 * vulnerabilities along with a few other hacks I've
	 * harvested from examining vulnerabilities in other programs:
	 * http://ha.ckers.org/xss.html
	 *
	 * @param	mixed	string or array
	 * @param 	bool
	 * @return	string
	 */
	public function xss_clean($str, $is_image = FALSE) {
		// Is the string an array?
		if (is_array($str)) {
			while (list($key) = each($str)) {
				$str[$key] = $this->xss_clean($str[$key]);
			}

			return $str;
		}

		/*
		 * URL Decode
		 *
		 * Just in case stuff like this is submitted:
		 *
		 * <a href="http://%77%77%77%2E%67%6F%6F%67%6C%65%2E%63%6F%6D">Google</a>
		 *
		 * Note: Use rawurldecode() so it does not remove plus signs
		 */
		$str = rawurldecode($str);

		/*
		 * Convert character entities to ASCII
		 *
		 * This permits our tests below to work reliably.
		 * We only convert entities that are within tags since
		 * these are the ones that will pose security problems.
		 */
		$str = preg_replace_callback("/[a-z]+=([\'\"]).*?\\1/si", function($match) {
			return str_replace(array('>', '<', '\\'), array('&gt;', '&lt;', '\\\\'), $match[0]);
		}, $str);

		/*
		 * Convert all tabs to spaces
		 *
		 * This prevents strings like this: ja	vascript
		 * NOTE: we deal with spaces between characters later.
		 * NOTE: preg_replace was found to be amazingly slow here on
		 * large blocks of data, so we use str_replace.
		 */
		$str = str_replace("\t", ' ', $str);

		// Capture converted string for later comparison
		$converted_string = $str;

		/*
		 * Makes PHP tags safe
		 *
		 * Note: XML tags are inadvertently replaced too:
		 *
		 * <?xml
		 *
		 * But it doesn't seem to pose a problem.
		 */
		if ($is_image === TRUE) {
			// Images have a tendency to have the PHP short opening and
			// closing tags every so often so we skip those and only
			// do the long opening tags.
			$str = preg_replace('/<\?(php)/i', '&lt;?\\1', $str);
		}
		else {
			$str = str_replace(array('<?', '?'.'>'),  array('&lt;?', '?&gt;'), $str);
		}

		/*
		 * Compact any exploded words
		 *
		 * This corrects words like:  j a v a s c r i p t
		 * These words are compacted back to their correct state.
		 */
		$words = array(
			'javascript', 'expression', 'vbscript', 'script', 'base64',
			'applet', 'alert', 'document', 'write', 'cookie', 'window'
		);


		foreach ($words as $word) {
			$word = implode('\s*', str_split($word)).'\s*';

			// We only want to do this when it is followed by a non-word character
			// That way valid stuff like "dealer to" does not become "dealerto"
			$str = preg_replace_callback('#('.substr($word, 0, -3).')(\W)#is', function($matches) {
				return preg_replace('/\s+/s', '', $matches[1]).$matches[2];
			}, $str);
		}

		/*
		 * Remove disallowed Javascript in links or img tags
		 * We used to do some version comparisons and use of stripos for PHP5,
		 * but it is dog slow compared to these simplified non-capturing
		 * preg_match(), especially if the pattern exists in the string
		 */
		
		do {
			$original = $str;

			if (preg_match('/<a/i', $str)) {
				$str = preg_replace_callback('#<a\s+([^>]*?)(?:>|$)#si', function($match) {
					$out = str_replace(array('<', '>'), '', $match[1]);
					if (preg_match_all('#\s*[a-z\-]+\s*=\s*(\042|\047)([^\\1]*?)\\1#is', $str, $matches)) {
						foreach ($matches[0] as $m) {
							$out .= preg_replace('#/\*.*?\*/#s', '', $m);
						}
					}

					return str_replace($match[1],
					preg_replace('#href=.*?(?:alert\(|alert&\#40;|javascript:|livescript:|mocha:|charset=|window\.|document\.|\.cookie|<script|<xss|data\s*:)#si', '', $out), $match[0]);
				}, $str);
			}

			if (preg_match('/<img/i', $str)) {
				$str = preg_replace_callback('#<img\s+([^>]*?)(?:\s?/?>|$)#si', function($match) {
					$out = str_replace(array('<', '>'), '', $match[1]);
					if (preg_match_all('#\s*[a-z\-]+\s*=\s*(\042|\047)([^\\1]*?)\\1#is', $str, $matches)) {
						foreach ($matches[0] as $m) {
							$out .= preg_replace('#/\*.*?\*/#s', '', $m);
						}
					}

					return str_replace($match[1],
						preg_replace('#src=.*?(?:alert\(|alert&\#40;|javascript:|livescript:|mocha:|charset=|window\.|document\.|\.cookie|<script|<xss|base64\s*,)#si', '', $out), $match[0]);
				}, $str);
			}

			if (preg_match('/script|xss/i', $str)) {
				$str = preg_replace('#</*(?:script|xss).*?>#si', '[removed]', $str);
			}
		}
		while ($original !== $str);

		unset($original);

		/*
		 * Sanitize naughty HTML elements
		 *
		 * If a tag containing any of the words in the list
		 * below is found, the tag gets converted to entities.
		 *
		 * So this: <blink>
		 * Becomes: &lt;blink&gt;
		 */
		$naughty = 'alert|applet|audio|basefont|base|behavior|bgsound|blink|body|embed|expression|form|frameset|frame|head|html|ilayer|iframe|input|isindex|layer|link|meta|object|plaintext|style|script|textarea|title|video|xml|xss';
		$str = preg_replace_callback('#<(/*\s*)('.$naughty.')([^><]*)([><]*)#is', function($matches) {
			return '&lt;'.$matches[1].$matches[2].$matches[3] // encode opening brace
			// encode captured opening or closing brace to prevent recursive vectors:
			.str_replace(array('>', '<'), array('&gt;', '&lt;'), $matches[4]);
		}, $str);

		/*
		 * Sanitize naughty scripting elements
		 *
		 * Similar to above, only instead of looking for
		 * tags it looks for PHP and JavaScript commands
		 * that are disallowed. Rather than removing the
		 * code, it simply converts the parenthesis to entities
		 * rendering the code un-executable.
		 *
		 * For example:	eval('some code')
		 * Becomes:	eval&#40;'some code'&#41;
		 */
		$str = preg_replace('#(alert|cmd|passthru|eval|exec|expression|system|fopen|fsockopen|file|file_get_contents|readfile|unlink)(\s*)\((.*?)\)#si',
					'\\1\\2&#40;\\3&#41;',
					$str);

		/*
		 * Images are Handled in a Special Way
		 * - Essentially, we want to know that after all of the character
		 * conversion is done whether any unwanted, likely XSS, code was found.
		 * If not, we return TRUE, as the image is clean.
		 * However, if the string post-conversion does not matched the
		 * string post-removal of XSS, then it fails, as there was unwanted XSS
		 * code found and removed/changed during processing.
		 */
		if ($is_image === TRUE) {
			return ($str === $converted_string);
		}

		return $str;
	}

	// --------------------------------------------------------------------

	/**
	 * Fetch an item from the GET array
	 *
	 * @param	string
	 * @param	bool
	 * @return	string
	 */
	public function get($index = NULL, $xss_clean = FALSE) {
		return $this->fetch_from_array($_GET, $index, $xss_clean);
	}

	// --------------------------------------------------------------------

	/**
	 * Fetch an item from the POST array
	 *
	 * @param	string
	 * @param	bool
	 * @return	string
	 */
	public function post($index = NULL, $xss_clean = FALSE) {
		return $this->fetch_from_array($_POST, $index, $xss_clean);
	}

	// --------------------------------------------------------------------

	/**
	 * Fetch an item from the SERVER array
	 *
	 * @param	string
	 * @param	bool
	 * @return	string
	 */
	public function server($index = '', $xss_clean = FALSE) {
		return $this->fetch_from_array($_SERVER, $index, $xss_clean);
	}

	// --------------------------------------------------------------------

	/**
	 * Fetch the IP Address
	 *
	 * @return	string
	 */
	public function ip_address() {
		if ($this->ip_address !== FALSE) {
			return $this->ip_address;
		}

		if (config_item('proxy_ips') != '' && $this->server('HTTP_X_FORWARDED_FOR') && $this->server('REMOTE_ADDR')) {
			$has_ranges = strpos($proxies, '/') !== false;
			$proxies = preg_split('/[\s,]/', config_item('proxy_ips'), -1, PREG_SPLIT_NO_EMPTY);
			$proxies = is_array($proxies) ? $proxies : array($proxies);
		
			if ($has_ranges) {
				$long_ip = ip2long($_SERVER['REMOTE_ADDR']);
				$bit_32 = 1 << 32;

				// Go through each of the IP Addresses to check for and
				// test against range notation
				foreach($proxies as $ip) {
					list($address, $mask_length) = explode('/', $ip);

					// Generate the bitmask for a 32 bit IP Address
					$bitmask = $bit_32 - (1 << (32 - (int)$mask_length));
					if (($long_ip & $bitmask) == $address) {
						$this->ip_address = $_SERVER['HTTP_X_FORWARDED_FOR'];
						break;
					}
				}

			} else {
				$this->ip_address = in_array($_SERVER['REMOTE_ADDR'], $proxies) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];
			}
		}
		elseif ( ! $this->server('HTTP_CLIENT_IP') && $this->server('REMOTE_ADDR')) {
			$this->ip_address = $_SERVER['REMOTE_ADDR'];
		}
		elseif ($this->server('REMOTE_ADDR') && $this->server('HTTP_CLIENT_IP')) {
			$this->ip_address = $_SERVER['HTTP_CLIENT_IP'];
		}
		elseif ($this->server('HTTP_CLIENT_IP')) {
			$this->ip_address = $_SERVER['HTTP_CLIENT_IP'];
		}
		elseif ($this->server('HTTP_X_FORWARDED_FOR')) {
			$this->ip_address = $_SERVER['HTTP_X_FORWARDED_FOR'];
		}

		if ($this->ip_address === FALSE) {
			return $this->ip_address = '0.0.0.0';
		}

		if (strpos($this->ip_address, ',') !== FALSE) {
			$x = explode(',', $this->ip_address);
			$this->ip_address = trim(end($x));
		}

		if ( ! $this->valid_ip($this->ip_address)) {
			return $this->ip_address = '0.0.0.0';
		}

		return $this->ip_address;
	}

	// --------------------------------------------------------------------

	/**
	 * Validate IP Address
	 *
	 * @param	string
	 * @param	string	'ipv4' or 'ipv6'
	 * @return	bool
	 */
	public function valid_ip($ip, $which = '') {
		switch (strtolower($which)) {
			case 'ipv4':
				$which = FILTER_FLAG_IPV4;
				break;
			case 'ipv6':
				$which = FILTER_FLAG_IPV6;
				break;
			default:
				$which = NULL;
				break;
		}

		return (bool) filter_var($ip, FILTER_VALIDATE_IP, $which);
	}

	// --------------------------------------------------------------------

	/**
	 * User Agent
	 *
	 * @return	string
	 */
	public function user_agent() {
		if ($this->user_agent !== FALSE) {
			return $this->user_agent;
		}

		return $this->user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : FALSE;
	}

	// --------------------------------------------------------------------

	/**
	 * Request Headers
	 *
	 * In Apache, you can simply call apache_request_headers(), however for
	 * people running other webservers the function is undefined.
	 *
	 * @param	bool	XSS cleaning
	 * @return	array
	 */
	public function request_headers($xss_clean = FALSE) {
		// Look at Apache go!
		if (function_exists('apache_request_headers')) {
			$headers = apache_request_headers();
		}
		else {
			$headers['Content-Type'] = isset($_SERVER['CONTENT_TYPE']) ? $_SERVER['CONTENT_TYPE'] : @getenv('CONTENT_TYPE');

			foreach ($_SERVER as $key => $val) {
				if (strpos($key, 'HTTP_') === 0) {
					$headers[substr($key, 5)] = $this->fetch_from_array($_SERVER, $key, $xss_clean);
				}
			}
		}

		// take SOME_HEADER and turn it into Some-Header
		foreach ($headers as $key => $val) {
			$key = str_replace('_', ' ', strtolower($key));
			$key = str_replace(' ', '-', ucwords($key));

			$this->headers[$key] = $val;
		}

		return $this->headers;
	}

	// --------------------------------------------------------------------

	/**
	 * Get Request Header
	 *
	 * Returns the value of a single member of the headers class member
	 *
	 * @param	string	array key for $this->headers
	 * @param	bool	XSS Clean or not
	 * @return	mixed	FALSE on failure, string on success
	 */
	public function get_request_header($index, $xss_clean = FALSE) {
		if (empty($this->headers)) {
			$this->request_headers();
		}

		if ( ! isset($this->headers[$index])) {
			return NULL;
		}

		return ($xss_clean === TRUE)
			? $this->xss_clean($this->headers[$index])
			: $this->headers[$index];
	}

	// --------------------------------------------------------------------

	/**
	 * Is ajax Request?
	 *
	 * Test to see if a request contains the HTTP_X_REQUESTED_WITH header
	 *
	 * @return 	bool
	 */
	public function is_ajax_request() {
		return ( ! empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');
	}

	// --------------------------------------------------------------------

	/**
	 * Is cli Request?
	 *
	 * Test to see if a request was made from the command line
	 *
	 * @return 	bool
	 */
	public function is_cli_request() {
		return (php_sapi_name() === 'cli' OR defined('STDIN'));
	}

	// --------------------------------------------------------------------

	/**
	 * Get Request Method
	 *
	 * Return the Request Method
	 *
	 * @param	bool	uppercase or lowercase
	 * @return 	bool
	 */
	public function method($upper = FALSE) {
		return ($upper)
			? strtoupper($this->server('REQUEST_METHOD'))
			: strtolower($this->server('REQUEST_METHOD'));
	}
}

// END Request Class
// By Minicode