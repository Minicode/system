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
Usage: mc [args] [options]

  [project]             Create a new project in current path
  -d                    Look for Minicode system directory
  -h, --help            This help
  -i                    PHP information
  -r [route]            Spaces instead of segments, to simulate the url pathinfo access
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
    include SYSPATH . 'core/MC_CLI_Project.php';
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
        include SYSPATH . 'core/MC_CLI_Install.php';
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

// END mc cli
// By Minicode