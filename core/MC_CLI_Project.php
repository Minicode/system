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
 * MC_CLI_Project Class
 *
 * The command line form dynamic create project files 
 * and directories
 *
 * @package       Minicode
 * @category      Core
 * @subpackage    MC_CLI_Project
 * @author        Wanglong
 * @link          http://minicode.org/docs/core/mc_cli_project
 * @since         Version 1.0
 */

class MC_CLI_Project {

    /**
     * Project name
     *
     * @access protected
     * @var    string
     */
    protected $name;

    // --------------------------------------------------------------------

    /**
     * System path
     *
     * @access protected
     * @var    string
     */
    protected $system_path;

    // --------------------------------------------------------------------

    /**
     * System tpl directory
     *
     * @access protected
     * @var    string
     */
    protected $tpl_dir = 'tpl';

    // --------------------------------------------------------------------

    /**
     * Constructor
     *
     * This function can create a new project, to specify a project name
     *
     * @access  public
     * @param   string
     * @return  void
     */
    public function __construct($name = 'app') {
        $this->name        = $name;
        $this->system_path = dirname(SYSPATH) == PWD ? SYSDIR : rtrim(SYSPATH, DIRECTORY_SEPARATOR);
    }

    // --------------------------------------------------------------------

    /**
     * According to the project name to create a 
     * series of files and directories
     *
     * @access  public
     * @return  void
     */
    public function create() {
        if($this->mkdir($this->name, 0)) {
            $str = $this->read('index.tpl');
            $str = str_replace('{#system_path#}', $this->system_path, $str);
            $str = str_replace('{#system_dir#}', SYSDIR, $str);
            $str = str_replace('{#application_folder#}', $this->name, $str);
            $index_file = file_exists('index.php') ? $this->name : 'index';
            file_put_contents($index_file  . '.php', $str);

            if ( ! file_exists('.htaccess')) {
                $htaccess = $this->read('.htaccess.tpl');
                $htaccess = str_replace('{#index_file#}', $index_file, $htaccess);
                file_put_contents('.htaccess', $htaccess);
            }

            $this->mkdir('config');
            $this->mkdir('models');
            $this->mkdir('views');
            $this->mkdir('controllers');

            $this->copy('config.tpl', 'config/config.php');
            $this->copy('routes.tpl', 'config/routes.php');
            $this->copy('database.tpl', 'config/database.php');
            $this->copy('welcome.tpl', 'controllers/welcome.php');
        }
    }

    // --------------------------------------------------------------------

    /**
     * Create some types of directory
     *
     * @access  protected
     * @param   string
     * @param   int
     * @return  boolean
     */
    protected function mkdir($dirname, $type = 1) {
        if ($type == 0 && is_dir($dirname)) {
            die('ERROR: ' . $dirname . ' project exists.');
        }

        switch ($type) {
            case 0:
                $path = $dirname;
                break;

            case 1:
                $path = $this->name . DIRECTORY_SEPARATOR . $dirname;
                break;
            
            default:
                $path = $this->name;
                break;
        }

        mkdir($path);
        echo 'create a ' .  $path . ' directory successfully' . chr(10);
        return TRUE;
    }

    // --------------------------------------------------------------------

    /**
     * From the tpl directory file copy to project directory
     *
     * @access  protected
     * @param   string
     * @param   string
     * @return  void
     */
    protected function copy($tpl, $target) {
        copy(SYSPATH . $this->tpl_dir . DIRECTORY_SEPARATOR . $tpl, $this->name . DIRECTORY_SEPARATOR . $target);
    }

    // --------------------------------------------------------------------

    /**
     * Read a file from the tpl directory
     *
     * @access  protected
     * @param   string
     * @return  string
     */
    protected function read($tpl) {
        return file_get_contents(SYSPATH . DIRECTORY_SEPARATOR . $this->tpl_dir . DIRECTORY_SEPARATOR . $tpl);
    }
}