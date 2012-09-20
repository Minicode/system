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
 * Mysql database SQL builder drive
 *
 * @package       Minicode
 * @category      DB
 * @subpackage    MC_DB_Mysql
 * @author        Wanglong
 * @link          http://minicode.org/docs/db/mc_db_mysql
 * @since         Version 1.0
 */

class MC_DB_Mysql extends MC_DB_Base {

    /**
     * Basic SELECT statement integration for SQL String
     *
     * @access  public
     * @param   string  $table_name
     * @param   array   $options
     * @return  string
     */
    public function select_by_sql($table_name, $options) {
        $select = empty($options['select']) ? '*' : $options['select'];
        $from   = empty($options['from'])   ? ''  : $options['from'];
        $joins  = empty($options['joins'])  ? ''  : $options['joins'];
        $where  = empty($options['where'])  ? '1' : $options['where'];
        $group  = empty($options['group'])  ? ''  : $options['group'];
        $having = empty($options['having']) ? ''  : $options['having'];
        $order  = empty($options['order'])  ? ''  : $options['order'];
        $start  = empty($options['start'])  ? 0   : $options['start'];
        $limit  = empty($options['limit'])  ? ''  : $options['limit'];

        if ($from === '') {
            $from   = $table_name;
        }

        if ($group !== '') {
            $group  = " GROUP BY {$group}";
        }

        if ($having !== '') {
            $having = " HAVING {$having}";
        }

        if ($order !== '') {
            $order  = " ORDER BY {$order}";
        }

        if ($limit !== '') {
            $limit  = " LIMIT {$start},{$limit}";
        }

        $sql = "SELECT {$select} FROM {$from} {$joins} WHERE {$where}{$group}{$having}{$order}{$limit}";

        return $sql;
    }

    // --------------------------------------------------------------------

    /**
     * Basic UPDATE statement integration for SQL String
     *
     * @access  public
     * @param   string  $table_name
     * @param   array   $data
     * @param   array   $options
     * @return  string
     */
    public function update_by_sql($table_name, $data, $options) {
        $where  = empty($options['where'])  ? '1' : $options['where'];

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $udp[] = $key . '=' . $value[0];
            }
            else {
                $udp[] = $key . '=' . $this->quotes_chars($value);
            }
        }

        $sql = "UPDATE {$table_name} SET " . implode(',', $udp) . " WHERE {$where}";

        return $sql;
    }

    // --------------------------------------------------------------------

    /**
     * Basic INSERT statement integration for SQL String
     *
     * @access  public
     * @param   string  $table_name
     * @param   array   $data
     * @return  string
     */
    public function insert_by_sql($table_name, $data) {
        $data   = isset($data[0]) ? $data : array($data);
        $fields = implode(',', array_keys($data[0]));

        foreach ($data as $i => $row) {
            $vals = array_values($row);
            
            foreach ($vals as $val) {
                $tmp[$i][] = $this->quotes_chars($val);
            }

            $values[] = '(' . implode(',', $tmp[$i]) . ')';
        }

        return "INSERT INTO $table_name ($fields) VALUES" . implode(',', $values);
    }

    // --------------------------------------------------------------------

    /**
     * Basic DELETE statement integration for SQL String
     *
     * @access  public
     * @param   string  $table_name
     * @param   array   $options
     * @return  string
     */
    public function delete_by_sql($table_name, $options) {
        $where  = empty($options['where'])  ? '1' : $options['where'];
        return "DELETE FROM {$table_name} WHERE {$where} MERGE";
    }
}

// END MC_DB_Mysql Class
// By Minicode