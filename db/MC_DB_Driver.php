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
 * MC_DB_Driver Class
 *
 * Database driverentry and interface factory
 *
 * @package       Minicode
 * @category      Core
 * @subpackage    MC_DB_Driver
 * @author        Wanglong
 * @link          http://minicode.org/docs/db/mc_db_driver
 * @since         Version 1.0
 */

class MC_DB_Driver extends MC_DB {

    /**
     * Database adapter instance
     *
     * @access  private
     * @var     object
     */
    private $adapter;

    // --------------------------------------------------------------------

    /**
     * Factory, An object it only exec once
     *
     * We have to create a database adapter, in order to ensure high-level 
     * interface (or packaging SQL) can be applied in any database type
     * without the need to change your code.
     *
     * @access  public
     * @return  void
     */
    public function factory() {
        $self     = dirname(__FILE__);
        $drivers  = $self . DIRECTORY_SEPARATOR . 'drivers';
        $protocol = $self . DIRECTORY_SEPARATOR . 'MC_DB_DriverProtocol.php';
        $class    = 'MC_DB_' . $this->driver;
        $adapter  = $drivers . DIRECTORY_SEPARATOR . $class . '.php';

        if ( ! file_exists($protocol)) {
            die('Not Found MC_DB_DriverProtocol.php file');
        }

        if ( ! file_exists($adapter)) {
            die('Not Found ' . $class . '.php file driver');
        }

        require $protocol;
        require $adapter;
        $this->adapter = new $class;
    }

    // --------------------------------------------------------------------

    /**
     * Insert new data
     *
     * @access  public
     * @param   string  $table_name
     * @param   array   $data
     * @return  void
     */
    public function insert($table_name, $data) {
        $sql = $this->adapter->insert_by_sql($table_name, $data);
        $this->exec($sql);
    }
}

// END MC_DB_Mysql Class
// By Minicode