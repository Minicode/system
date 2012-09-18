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
 * MC_DB_DriverProtocol Interface
 *
 * Database driven unified interface protocols
 *
 * @package       Minicode
 * @category      DB
 * @subpackage    MC_DB_DriverProtocol
 * @author        Wanglong
 * @link          http://minicode.org/docs/db/mc_db_driver_protocol
 * @since         Version 1.0
 */

interface  MC_DB_DriverProtocol {

    public function select_by_sql();
    public function update_by_sql();
    public function insert_by_sql($table_name, $data);
    public function delete_by_sql();
    
}

// END MC_DB_DriverProtocol Interface
// By Minicode