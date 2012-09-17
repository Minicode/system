<?php
/**
 * PDO 适配器
 *
 * @package       PHP Micro
 * @author        Wanglong
 */

class MC_DB_Adapter extends MC_DB {

    /**
     * PDO实例
     * @var PDO
     */
    protected $pdo;

    /**
     * PDO 数据资源
     * @var PDO resource
     */
    protected $res;

    /**
     * 最后的SQL语句
     * @var string
     */
    protected $sql;

    /**
     * dsn
     * @var string
     */
    protected $dsn;

    /**
     * username
     * @var string
     */
    protected $username;

    /**
     * password
     * @var string
     */
    protected $password;

    /**
     * charset
     * @var string
     */
    protected $charset;

    /**
     * 构造函数
     * @param array $config
     */
    public function __construct($dsn = '', $username = 'root', $password = '', $charset = 'utf8') {
        $this->dsn = $dsn;
        $this->username = $username;
        $this->password = $password;
        $this->charset  = $charset;
    }


    /**
     * 连接数据库
     * @return void
     */
    public function connect() {
        $this->pdo = new PDO($this->dsn, $this->username, $this->password);
        $charset = $this->charset;
        $this->pdo->query("SET names '{$charset}'");
        $this->set_fetch_mode();
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    }

    public function set_fetch_mode($enum = 'assoc') {

        switch ($enum) {
            case 'assoc':
                $VALUE = PDO::FETCH_ASSOC;
                break;

            case 'number':
                $VALUE = PDO::FETCH_NUM;
                break;

            case 'both':
                $VALUE = PDO::FETCH_BOTH;
                break;
            
            default:
                $VALUE = PDO::FETCH_BOTH;
                break;
        }

        $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, $VALUE);
    }

    /**
     * 断开连接
     * @return void
     */
    public function dis_connect() {
        $this->pdo = NULL;
        $this->res = NULL;
    }

    /**
     * 执行sql，返回新加入的id
     * @param string $statement
     * @return string
     */
    public function exec($statement) {
        if ($this->pdo->exec($statement)) {
            $this->sql = $statement;
            return $this->last_id();
        }

        $this->error_message();
    }

    /**
     * 查询sql
     * @param  string $statement
     * @return object
     */
    public function query($statement) {
        $res = $this->pdo->query($statement);
        if ($res) {
            $this->res = $res;
            $this->sql = $statement;
            return $this;
        }

        $this->error_message();
    }

    /**
     * 序列化一次数据
     * @return mixed
     */
    public function fetch() {
        return $this->res->fetch();
    }

    /**
     * 序列化所有数据
     * @return array
     */
    public function fetch_all() {
        return $this->res->fetchAll();
    }

    /**
     * 最后添加的id
     * @return string
     */
    public function last_id() {
        return $this->pdo->lastInsertId();
    }

    /**
     * 影响的行数
     * @return int
     */
    public function affect_rows() {
        return $this->res->rowCount();
    }

    /**
     * 预备语句
     * @param string $statement
     * @return PdoDb
     */
    public function prepare($statement) {
        $res = $this->pdo->prepare($statement);
        if ($res) {
            $this->res = $res;
            $this->sql = $statement;
            return $this;
        }

        $this->error_message();
    }

    /**
     * 执行预备语句
     * @return bool
     */
    public function execute() {
        if ($this->res->execute()) {
            return TRUE;
        }

        $this->error_message();
    }

    /**
     * 开启事务
     * @return bool
     */
    public function begin() {
        return $this->pdo->beginTransaction();
    }

    /**
     * 执行事务
     * @return bool
     */
    public function commit() {
        return $this->pdo->commit();
    }

    /**
     * 回滚事务
     * @return bool
     */
    public function rollback() {
        return $this->pdo->rollBack();
    }

    /**
     * 抛出错误
     * @throws Error
     * @return void
     */
    public function error_message() {
        $msg = $this->pdo->errorInfo();
        die('database error: ' . $msg[2]);
    }

    /**
     * 绑定数据
     * @param array $array
     * @return PdoDb
     */
    public function bind_array($array) {
        foreach ($array as $k => $v) {
            if (is_array($v)) {
                //array的有效结构 array('value'=>xxx,'type'=>PDO::PARAM_XXX)
                $this->res->bindValue($k + 1, $v['value'], $v['type']);
            } else {
                $this->res->bindValue($k + 1, $v, PDO::PARAM_STR);
            }
        }
        return $this;
    }

    public function build_insert() {
        
    }

    /**
    * 可擦写式创建新数据
    * 支持多行并行插入
    * 插入时若存在唯一主键或unqiue索引重复时会停止插入并改换成update操作
    * 特别注意，必须保证每一组数据都具有相同数量和类型的键值对，否则忽略该组数据的插入
    * @access public
    * @author Wanglong
    * @param  array     $data
    */
    public function insert_duplicate($table_name, $data) {
        if (!isset($data[0])) {
            $data = array($data);
        }
        
        $keys = array_keys($data[0]);
        $keys_length = count($keys);
        $fields = implode(',', $keys);

        $values = array();

        for ($i=0, $l=count($data); $i<$l; $i++) {
            //if (count($data[$i]) != $keys_length) continue;

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

        $update = array();
        for ($k=0, $kl=count($keys); $k<$kl; $k++) {
            $cur_k = $keys[$k];
            $update[] = "$cur_k = VALUES($cur_k)";
        }
        
        $cont = implode(',', $values);
        $updt = implode(',', $update);
        
        $sql = "INSERT INTO $table_name ($fields) VALUES $cont ON DUPLICATE KEY UPDATE $updt";

        return $this->exec($sql);
    }

    /**
     * 获取PDO支持的数据库
     * @static
     * @return array
     */
    public static function get_support_drivers(){
        return PDO::getAvailableDrivers();
    }

    /**
     * 获取数据库的版本信息
     * @return array
     */
    public function get_version(){
        return $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
    }

}
