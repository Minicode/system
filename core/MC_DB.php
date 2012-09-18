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
 * MC_DB Class
 *
 * Database base class, based on the PDO
 *
 * @package       Minicode
 * @category      Core
 * @subpackage    MC_DB
 * @author        Wanglong
 * @link          http://minicode.org/docs/core/mc_db
 * @since         Version 1.0
 */

class MC_DB extends MC_Object {

    /**
     * PDO instance
     *
     * @access  protected
     * @var     PDO
     */
    protected $db;

    // --------------------------------------------------------------------

    /**
     * PDO resource
     *
     * @access  protected
     * @var     PDO resource
     */
    protected $res;

    // --------------------------------------------------------------------

    /**
     * The final SQL statement
     *
     * @access  protected
     * @var     string
     */
    protected $sql;

    // --------------------------------------------------------------------

    /**
     * Dsn
     *
     * @access  protected
     * @var     string
     */
    protected $dsn;

    // --------------------------------------------------------------------

    /**
     * Driver
     *
     * @access  protected
     * @var     string
     */
    protected $driver;

    // --------------------------------------------------------------------

    /**
     * Username
     *
     * @access  protected
     * @var     string
     */
    protected $username;

    // --------------------------------------------------------------------

    /**
     * Password
     *
     * @access  protected
     * @var     string
     */
    protected $password;

    // --------------------------------------------------------------------

    /**
     * POD Options
     *
     * @access  protected
     * @var     string
     */
    protected $options;

    // --------------------------------------------------------------------

    /**
     * Constructor
     *
     * No parameters, the use of the database configuration files 
     * for connecting database, we recommend to do so.
     *
     * @access  public
     * @param   string  $dsn
     * @param   string  $username
     * @param   string  $password
     * @param   array   $options
     * @return  void
     */
    public function __construct($dsn = '', $username = '', $password = '', $options = array()) {
        if (empty($dsn)) {
            $this->cfg = MC_Config::instance();
            $this->cfg->load('database');
            $this->dsn      = self::dsn($this->cfg->dbdriver, $this->cfg->hostname, $this->cfg->database);
            $this->driver   = $this->cfg->dbdriver;
            $this->username = $this->cfg->username;
            $this->password = $this->cfg->password;

            // Sets the default MYSQL_ATTR_INIT_COMMAND
            $charset = empty($this->cfg->encoding) ? 'utf8' : $this->cfg->encoding;
            $this->options[PDO::MYSQL_ATTR_INIT_COMMAND] = "SET NAMES '{$charset}'";
        }
        else {
            $this->dsn      = $dsn;
            $this->username = $username;
            $this->password = $password;
            $this->options  = $options;
        }
    }

    // --------------------------------------------------------------------

    /**
     * Connect to the DataBase
     * 
     * @access  public
     * @return  void
     */
    public function open() {
        $this->db = new PDO($this->dsn, $this->username, $this->password, $this->options);
        $this->db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
        return $this;
    }

    // --------------------------------------------------------------------

    /**
     * Disconnect to the DataBase
     *
     * @access  public
     * @return  void
     */
    public function close() {
        $this->db  = NULL;
        $this->res = NULL;
    }

    // --------------------------------------------------------------------

    /**
     * Execute SQL, to return to the new join id
     *
     * @access  public
     * @param   string  $statement
     * @return  string
     */
    public function exec($statement) {
        if ($this->db->exec($statement)) {
            $this->sql = $statement;
            return $this->last_id();
        }

        $this->error_message();
    }

    // --------------------------------------------------------------------

    /**
     * SQL query
     * 
     * @access  public
     * @param   string $statement
     * @return  object
     */
    public function query($statement) {
        $res = $this->db->query($statement);
        if ($res) {
            $this->res = $res;
            $this->sql = $statement;
            return $this;
        }

        $this->error_message();
    }

    // --------------------------------------------------------------------

    /**
     * Prepared statement
     *
     * @access  public
     * @param   string  $statement
     * @return  object
     */
    public function prepare($statement) {
        $res = $this->db->prepare($statement);
        if ($res) {
            $this->res = $res;
            $this->sql = $statement;
            return $this;
        }

        $this->error_message();
    }

    // --------------------------------------------------------------------

    /**
     * Executive prepared statement
     *
     * @access  public
     * @return  bool
     */
    public function execute() {
        if ($this->res->execute()) {
            return TRUE;
        }

        $this->error_message();
    }

    // --------------------------------------------------------------------

    /**
     * Fetch once data, return one line
     *
     * @access  public
     * @return  mixed
     */
    public function fetch() {
        return $this->res->fetch();
    }

    // --------------------------------------------------------------------

    /**
     * Fetch all data, return array
     *
     * @access  public
     * @return  array
     */
    public function fetch_all() {
        return $this->res->fetchAll();
    }

    // --------------------------------------------------------------------

    /**
     * Last insert id
     *
     * @access  public
     * @return  string
     */
    public function last_id() {
        return $this->db->lastInsertId();
    }

    // --------------------------------------------------------------------

    /**
     * Influence the number of rows in
     *
     * @access  public
     * @return  int
     */
    public function affect_rows() {
        return $this->res->rowCount();
    }

    // --------------------------------------------------------------------

    /**
     * Transaction begin
     * 
     * @access  public
     * @return  boolean
     */
    public function begin() {
        return $this->db->beginTransaction();
    }

    // --------------------------------------------------------------------

    /**
     * Transaction commit
     * 
     * @access  public
     * @return  boolean
     */
    public function commit() {
        return $this->db->commit();
    }

    // --------------------------------------------------------------------

    /**
     * Transaction rolled back
     * 
     * @access  public
     * @return  boolean
     */
    public function rollback() {
        return $this->db->rollBack();
    }

    // --------------------------------------------------------------------

    /**
     * Throw error
     * 
     * @access  public
     * @throws  error
     * @return  void
     */
    public function error_message() {
        $msg = $this->db->errorInfo();
        die('Database Error: ' . $msg[2]);
    }

    // --------------------------------------------------------------------

    /**
     * This function is useful for bind value. 
     * You can specify the type of the value in advance with $type.
     *
     * @access  public
     * @param   string  $key
     * @param   string  $value
     * @param   boolean $type
     * @return  void
     */
    public function bind_value($key, $value, $type = FALSE) {
        if ($type) {
            $this->res->bindValue($key, $value, $type);
        }
        else {
            if (is_int($value))
                $param = PDO::PARAM_INT;
            elseif (is_bool($value))
                $param = PDO::PARAM_BOOL;
            elseif (is_null($value))
                $param = PDO::PARAM_NULL;
            elseif (is_string($value))
                $param = PDO::PARAM_STR;
            else
                $param = FALSE;
                
            if ($param) {
                $this->res->bindValue($key, $value, $param);
            }
        }
    }

    // --------------------------------------------------------------------

    /**
     * This function is useful for bind value on an array. 
     * You can specify the type of the value in advance with $types.
     *
     * @access  public
     * @param   array  $array associative array containing the values ​​to bind
     * @param   array  $types associative array with the desired value for its corresponding key in $array
     * @return  void
     */
    public function bind_values($array, $types = FALSE) {
        foreach ($array as $key => $value) {
            if ($types) {
                $this->bind_value("$key", $value, $types[$key]);
            }
            else {
                $this->bind_value("$key", $value);
            }
        }
    }

    // --------------------------------------------------------------------

    /**
     * Access database version information
     *
     * @access  public
     * @return  string
     */
    public function get_version(){
        return $this->db->getAttribute(PDO::ATTR_DRIVER_NAME);
    }

    // --------------------------------------------------------------------

    /**
     * Generates a adaptive DSN
     *
     * @static
     * @access  public
     * @param   string  $dbdriver
     * @param   string  $hostname
     * @param   string  $database
     * @return  string
     */
    public static function dsn($dbdriver, $hostname, $database) {
        switch ($dbdriver) {
            case 'mysql':
                $dsn = $dbdriver . ":dbname={$database};host={$hostname}";
                break;
            
            default:
                $dsn = $dbdriver . ":dbname={$database};host={$hostname}";
                break;
        }
        return $dsn;
    }

    // --------------------------------------------------------------------

    /**
     * Get PDO support database
     *
     * @static
     * @access  public
     * @return  string
     */
    public static function get_support_drivers(){
        return PDO::getAvailableDrivers();
    }
}

// END MC_DB Class
// By Minicode