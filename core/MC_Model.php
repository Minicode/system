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
 * MC_Model Class
 *
 * Database base class, based on the PDO
 *
 * @category      Core
 * @package       MC_Model
 * @author        Wanglong
 * @link          http://phprails.com/docs/core/mc_model
 * @since         Version 1.0
 */

class MC_Model extends MC_DB {

    public function select() {
        
    }

    public function update() {

    }

    public function delete() {

    }

    public function insert($table_name, $data) {
        if (!isset($data[0])) {
            $data = array($data);
        }
        
        $keys   = array_keys($data[0]);
        $fields = implode(',', $keys);
        $values = array();

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

        $cont = implode(',', $values);
        $sql = "INSERT INTO $table_name ($fields) VALUES $cont";

        return $this->exec($sql);
    }
}