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
     * PDOStatement
     *
     * @access  protected
     * @var     PDOStatement
     */
    protected $stm;

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
     * Host
     *
     * @access  protected
     * @var     string
     */
    protected $host     = 'localhost';

    // --------------------------------------------------------------------

    /**
     * Database Name
     *
     * @access  protected
     * @var     string
     */
    protected $dbname;

    // --------------------------------------------------------------------

    /**
     * Driver
     *
     * @access  protected
     * @var     string
     */
    protected $driver   = 'mysql';

    // --------------------------------------------------------------------

    /**
     * Username
     *
     * @access  protected
     * @var     string
     */
    protected $username = 'root';

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
            $this->cfg      = config('database');
            $this->host     = empty($this->cfg['hostname']) ? $this->host : $this->cfg['hostname'];
            $this->dbname   = empty($this->cfg['database']) ? $this->dbname : $this->cfg['database'];
            $this->driver   = empty($this->cfg['dbdriver']) ? $this->driver : $this->cfg['dbdriver'];
            $this->username = empty($this->cfg['username']) ? $this->username : $this->cfg['username'];
            $this->password = empty($this->cfg['password']) ? $this->password : $this->cfg['password'];
            $this->dsn      = self::dsn($this->driver, $this->host, $this->dbname);

            // Sets the default MYSQL_ATTR_INIT_COMMAND
            $charset = empty($this->cfg['encoding']) ? 'utf8' : $this->cfg['encoding'];
            $this->options[PDO::MYSQL_ATTR_INIT_COMMAND] = "SET NAMES '{$charset}'";
            $this->options[PDO::ATTR_PERSISTENT] = $this->cfg['pconnect'];
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
        $this->stm = NULL;
        $this->sql = NULL;
    }

    // --------------------------------------------------------------------

    /**
     * Execute SQL, executes an SQL statement in a single function call, 
     * returning the number of rows affected by the statement.
     *
     * Statement execution failure won't throw an exception.
     *
     * exec() directly execute SQL statement, won't get any result set.
     * Bottom SQL operation, suitable for complex of the statement.
     * exec() can only be used on INSERT, UPDATE and DELETE operation, 
     * should not be used to SELECT query. 
     *
     * @access  public
     * @param   string  $statement
     * @return  int or FALSE
     */
    public function exec($statement) {
        return $this->db->exec($statement);
    }

    // --------------------------------------------------------------------

    /**
     * SQL query
     *
     * Execute a query, only after the success can be achieved 
     * the result set, failure throw an exception.
     *
     * Suggested only SELECT query when use it, other INSERT, 
     * UPDATE or DELETE operation recommend using exec or prepare 
     * and execute.
     * 
     * @access  public
     * @param   string $statement
     * @return  object or Error
     */
    public function query($statement) {
        $stm = $this->db->query($statement);
        if ($stm) {
            $this->stm = $stm;
            $this->sql = $statement;
            return $this;
        }

        $this->error_message();
    }

    // --------------------------------------------------------------------

    /**
     * Prepared SQL statement
     *
     * Advance preparation a SQL statement and immediately get a 
     * preprocessing the result set. No matter whether it is right 
     * or not.
     *
     * We recommend in any case are using it !!!
     *
     * @access  public
     * @param   string  $statement
     * @return  object
     */
    public function prepare($statement) {
        $this->stm = $this->db->prepare($statement);
        $this->sql = $statement;
        return $this;
    }

    // --------------------------------------------------------------------

    /**
     * Executive prepared statement
     *
     * Execution of prepared SQL result set, it can fault-tolerant, 
     * if query for failure is empty the result set.
     *
     * @access  public
     * @return  bool
     */
    public function execute() {
        return @$this->stm->execute();
    }

    // --------------------------------------------------------------------

    /**
     * Fetch once data, return one line
     *
     * @access  public
     * @return  mixed
     */
    public function fetch() {
        return $this->stm->fetch();
    }

    // --------------------------------------------------------------------

    /**
     * Fetch all data, return array
     *
     * @access  public
     * @return  array
     */
    public function fetch_all() {
        return $this->stm->fetchAll();
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
     * Returns the number of rows affected by the last DELETE, INSERT, 
     * or UPDATE statement executed by the corresponding PDOStatement object.
     * If the last SQL statement executed by the associated PDOStatement was 
     * a SELECT statement, some databases may return the number of rows 
     * returned by that statement. However, this behaviour is not guaranteed 
     * for all databases and should not be relied on for portable applications.
     * (Note: exec() invalid, because it won't get the result set)
     *
     * @access  public
     * @return  int
     */
    public function affect_rows() {
        return $this->stm ? $this->stm->rowCount() : 0;
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
            $this->stm->bindValue($key, $value, $type);
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
                $this->stm->bindValue($key, $value, $param);
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
                $this->bind_value($key, $value, $types[$key]);
            }
            else {
                $this->bind_value($key, $value);
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
    public function version(){
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
    public static function support_drivers(){
        return PDO::getAvailableDrivers();
    }
}

// END MC_DB Class
// By Minicode