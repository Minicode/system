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

/**
 *--------------------------------------------------------------------------
 * Cli global constant predefined
 *--------------------------------------------------------------------------
 */

define('SYSROOT', dirname(dirname(__FILE__)));
define('SYSPATH', SYSROOT . DIRECTORY_SEPARATOR);
define('SYSDIR', substr(SYSROOT, strrpos(SYSROOT, DIRECTORY_SEPARATOR) + 1));
define('PWD', getcwd());
define('VERSION', '1.0.0');

/**
 *--------------------------------------------------------------------------
 * Cli command universal function
 *--------------------------------------------------------------------------
 */

function in_argv(/* options */) {
    global $argv;
    $options = func_get_args();
    foreach ($options as $val) {
        if(in_array($val, $argv)) {
            return TRUE;
        }
    }
    return FALSE;
}

/**
 *--------------------------------------------------------------------------
 * Cli operation control
 *--------------------------------------------------------------------------
 * 
 * According to different command line parameters, implement 
 * different operating.
 */

$commands = array('install', 'migrate', 'generate');

if (empty($argv[1])) {
    die('Missing parameters! Please view the help, input \'-h\' OR \'--help\'');
}
elseif (strpos($argv[1], '-') === 0) {
    switch ($argv[1]) {
        case '-h': case '--help':
            show_help();
            break;
        case '-v': case '--version':
            show_version();
            break;
        case '-d':
            echo SYSROOT;
            break;
        case '-i':
            echo phpinfo();
            break;
        case '-r':
            access_route(array_slice($argv, 2));
            break;
        default:
            # code...
            break;
    }
}
elseif ( ! in_array($argv[1], $commands)) {
    create_project();
}
elseif ($argv[1] == 'install') {
    install();
}

/**
 *--------------------------------------------------------------------------
 * Cli operation process
 *--------------------------------------------------------------------------
 * 
 * Specific operation function
 */

function show_help() {
    echo('
Usage: mc [options]

  [project]             Create a new project in current path
  -d                    Look for Minicode system directory
  -h, --help            This help
  -i                    PHP information
  -r <route>            Spaces instead of segments, to simulate the url pathinfo access
  -v, --version         Print the Minicode version
    ');
}

function show_version() {
    echo('
Minicode ' . VERSION . '  - only need to need! (cil) (built: 09/13/2012)
An open source hyper-light web application agile development framework
    ');
}

function access_route($args) {
    exec('php index.php ' . implode(' ', $args));
}

function create_project() {
    global $argv;
    $project = new MC_CLI_Project($argv[1]);
    $project->create();
}

function install() {
    global $argv;

    if ( ! isset($argv[2])) {
        die('Missing parameters! Please view the install help, input \'-h\' OR \'--help\'');
    }
    elseif (strpos($argv[2], '-') === 0) {
        switch ($argv[2]) {
            case '-h': case '--help':
                show_install_help();
                break;
            default:
                # code...
                break;
        }
    }
    else {
        $project_name = empty($argv[3]) ? '' : $argv[3];
        $install = new MC_CLI_Install($project_name);
        $install->file_download($argv[2]);
    }
}

function show_install_help() {
    echo('
Usage: mc install [library] [project]

  [library]         Installation class library, the name is the underline style
  [project]         Is empty, the default installation to the system directory
  -h, --help        This help
    ');
}


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

// END mc cli
// By Minicode