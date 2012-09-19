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
        $protocol = $self . DIRECTORY_SEPARATOR . 'MC_DB_Base.php';
        $class    = 'MC_DB_' . $this->driver;
        $adapter  = $drivers . DIRECTORY_SEPARATOR . $class . '.php';

        if ( ! file_exists($protocol)) {
            die('Not Found MC_DB_Base.php file');
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
     * SQL statement execution
     *
     * Direct returns a data set, multiple data set to return 
     * to a two dimensional array.
     * Not SELECT query the will not receive any data set, 
     * returns an empty array
     *
     * @access  public
     * @param   string  $sql
     * @param   array   $bind
     * @param   boolean $all
     * @return  array
     */
    public function sql($sql = '', $bind = array(), $all = TRUE) {
        $this->prepare($sql);
        $this->bind_values($bind);
        $this->execute();
        return $all ? $this->fetch_all() : $this->fetch();
    }

    // --------------------------------------------------------------------

    /**
     * Obtain one data
     *
     * @access  public
     * @param   string  $table_name
     * @param   array   $options
     * @param   array   $bind
     * @return  array
     */
    public function one($table_name, $options = array(), $bind = array()) {
        $sql = $this->adapter->select_by_sql($table_name, $options);
        return $this->sql($sql, $bind, FALSE);
    }

    // --------------------------------------------------------------------

    /**
     * Obtain all data
     *
     * @access  public
     * @param   string  $table_name
     * @param   array   $options
     * @param   array   $bind
     * @return  array
     */
    public function all($table_name, $options = array(), $bind = array()) {
        $sql = $this->adapter->select_by_sql($table_name, $options);
        return $this->sql($sql, $bind);
    }

    // --------------------------------------------------------------------

    /**
     * Update data for SQL
     *
     * @access  public
     * @param   string  $table_name
     * @param   array   $data
     * @param   array   $options
     * @return  string
     */
    public function update($table_name, $data, $options = array(), $bind = array()) {
        $sql = $this->adapter->update_by_sql($table_name, $data, $options);
        return $this->exec($sql);
    }

    // --------------------------------------------------------------------

    /**
     * Insert new data
     *
     * @access  public
     * @param   string  $table_name
     * @param   array   $data
     * @return  int
     */
    public function insert($table_name, $data) {
        $sql = $this->adapter->insert_by_sql($table_name, $data);
        return $this->exec($sql);
    }

    // --------------------------------------------------------------------

    /**
     * Delete data for SQL
     *
     * @access  public
     * @param   string  $table_name
     * @param   array   $options
     * @return  string
     */
    public function delete($table_name, $options = array()) {
        $sql = $this->adapter->delete_by_sql($table_name, $options);
        return $this->exec($sql);
    }

    // --------------------------------------------------------------------

    /**
     * Recordable compact type insert new data.
     * Support multiple lines parallel insert.
     * When insertion if there is only one primary key or unqiue index 
     * repetition will cease insert and change into update operation.
     * Note: must guarantee that each set of data with the same number 
     * and types of key value on, or ignore the data insertion.
     *
     * @access  public
     * @param   string  $table_name
     * @param   array   $data
     * @return  string
     */
    public function insert_duplicate($table_name, $data) {
        $sql = $this->adapter->insert_duplicate_by_sql($table_name, $data);
        return $this->exec($sql);
    }
}

// END MC_DB_Mysql Class
// By Minicode