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
 * MC_CLI_Install Class
 *
 * From the package source, install the specified 
 * class library file. This class at present only 
 * support single PHP class file installation.
 *
 * @package       Minicode
 * @category      Core
 * @subpackage    MC_CLI_Install
 * @author        Wanglong
 * @link          http://minicode.org/docs/core/mc_cli_install
 * @since         Version 1.0
 */

class MC_CLI_Install {

    /**
     * Project name
     *
     * @access protected
     * @var    string
     */
    protected $project_name;

    // --------------------------------------------------------------------

    /**
     * Install path
     *
     * @access protected
     * @var    string
     */
    protected $install_path;

    // --------------------------------------------------------------------

    /**
     * Remote file source
     *
     * @access protected
     * @var    string
     */
    protected $file_source = 'https://raw.github.com/Minicode/libraries/master/';

    // --------------------------------------------------------------------

    /**
     * Constructor
     *
     * The specified item name used to determine class library 
     * in where, if is empty, the default for the system directory
     *
     * @access  public
     * @param   string
     * @return  void
     */
    public function __construct($project_name = '') {
        if ( ! empty($project_name) && ! is_dir($project_name)) {
            die('In the current directory not found \'' . $project_name . '\' project directory');
        }

        $this->install_path = empty($project_name) 
            ? (dirname(SYSPATH) == PWD ? SYSDIR : SYSPATH) . '/lib/'
            : $project_name . '/libraries/';
    }

    // --------------------------------------------------------------------

    /**
     * From the tpl directory file copy to project directory
     *
     * @access  protected
     * @param   string
     * @return  void
     */
    public function file_download($file_name) {
        $class = $this->parse_name($file_name);
        $url   = $this->file_source . $file_name . '/' . $class . '.php'; 
        $code  = $this->file_read($url);

        if (empty($code)) {
            echo('This \'' . $class . '\' library installation failure');
        }
        else {
            if ( ! is_dir($this->install_path)) {
                mkdir($this->install_path);
            }
            
            file_put_contents($this->install_path . $class . '.php', $code);
            echo 'Successful install the \'' . $class . '\' class library';
        }
    }

    // --------------------------------------------------------------------

    /**
     * Read remote file
     *
     * @access  protected
     * @param   string
     * @return  string
     */
    protected function file_read($url) {
        $ch = curl_init();  
        curl_setopt($ch, CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);   
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); 
        $result = curl_exec($ch);
        curl_close($ch);

        preg_match('/^<!/', $result, $matches);
        if ( ! empty($matches) && $matches[0] == '<!') {
            return FALSE;
        }
        
        return $result;
    }

    // --------------------------------------------------------------------

    /**
     * Underline conversion Camel style named
     *
     * @access  protected
     * @param   string
     * @return  string
     */
    protected function parse_name($name) {
        return ucfirst(preg_replace("/_([a-zA-Z])/e", "strtoupper('\\1')", $name));
    }
}