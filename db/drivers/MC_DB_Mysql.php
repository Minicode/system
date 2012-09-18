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
 * MC_DB_Mysql Class
 *
 * Mysql database SQL drive
 *
 * @package       Minicode
 * @category      DB
 * @subpackage    MC_DB_Mysql
 * @author        Wanglong
 * @link          http://minicode.org/docs/db/mc_db_mysql
 * @since         Version 1.0
 */

class MC_DB_Mysql implements MC_DB_DriverProtocol {

    public function select_by_sql() {
        
    }

    public function update_by_sql() {
        
    }

   /**
     * Insert new data for SQL
     *
     * @access  public
     * @param   string  $table_name
     * @param   array   $data
     * @return  string
     */
    public function insert_by_sql($table_name, $data) {
        $data   = isset($data[0]) ? $data : array($data);
        $fields = implode(',', array_keys($data[0]));

        for ($i=0, $l=count($data); $i<$l; $i++) {
            $vals = array_values($data[$i]);
            $vals = array_map(function($source){
                if (is_numeric($source)) {
                    return $source;
                }
                else {
                    return "'" . addslashes($source) . "'";
                }
            }, $vals);

            $values[] = '(' . implode(',', $vals) . ')';
        }

        return "INSERT INTO $table_name ($fields) VALUES" . implode(',', $values);
    }

    public function delete_by_sql() {
        
    }
}

// END MC_DB_Mysql Class
// By Minicode