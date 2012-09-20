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
 * MC_DB_Base Abstract Class
 *
 * Database driven unified interface abstract class
 *
 * @package       Minicode
 * @category      DB
 * @subpackage    MC_DB_Base
 * @author        Wanglong
 * @link          http://minicode.org/docs/db/mc_db_base
 * @since         Version 1.0
 */

abstract class MC_DB_Base {

    /**
     * Basic SELECT statement integration for SQL String
     *
     * @abstract
     * @access  public
     * @param   string  $table_name
     * @param   array   $data
     * @param   array   $options
     * @return  string
     */
    abstract public function select_by_sql($table_name, $options);

    // --------------------------------------------------------------------

   /**
     * Basic UPDATE statement integration for SQL String
     *
     * @abstract
     * @access  public
     * @param   string  $table_name
     * @param   array   $data
     * @param   array   $options
     * @return  string
     */
    abstract public function update_by_sql($table_name, $data, $options);

    // --------------------------------------------------------------------

     /**
     * Basic INSERT statement integration for SQL String
     *
     * @abstract
     * @access  public
     * @param   string  $table_name
     * @param   array   $data
     * @return  string
     */
    abstract public function insert_by_sql($table_name, $data);

    // --------------------------------------------------------------------

    /**
     * Basic DELETE statement integration for SQL String
     *
     * @abstract
     * @access  public
     * @param   string  $table_name
     * @param   array   $options
     * @return  string
     */
    abstract public function delete_by_sql($table_name, $options);

    // --------------------------------------------------------------------
    
    /**
     * For different types of data do different and processing, 
     * for should the string with quotes, numeric not quotes.
     *
     * @access  protected
     * @param   string  $chars
     * @return  string
     */
    protected function quotes_chars($chars) {
        if (is_numeric($chars)) {
            return $chars;
        }

        if (get_magic_quotes_gpc()) {
            return "'" . $chars . "'";
        }

        return "'" . addslashes($chars) . "'";
    }
}

// END MC_DB_Base Abstract Class
// By Minicode