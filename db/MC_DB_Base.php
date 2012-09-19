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

    abstract public function select_by_sql($table_name, $options);
    abstract public function update_by_sql($table_name, $data, $options);
    abstract public function insert_by_sql($table_name, $data);
    abstract public function delete_by_sql($table_name, $options);
    abstract public function insert_duplicate_by_sql($table_name, $options);
    
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