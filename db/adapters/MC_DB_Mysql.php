<?php
/**
 * PDO 适配器
 *
 * @package       PHP Micro
 * @author        Wanglong
 */

class MC_DB_Mysql extends MC_DB {

    /**
     * 构造函数
     * @param array $config
     */
    public function __construct() {

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
    public function insert($table_name, $data, $duplicate = FALSE) {
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

        if ($duplicate) {
            $update = array();
            for ($k=0, $kl=count($keys); $k<$kl; $k++) {
                $cur_k    = $keys[$k];
                $update[] = "$cur_k = VALUES($cur_k)";
            }
            $updt = implode(',', $update);
            $sql = "INSERT INTO $table_name ($fields) VALUES $cont ON DUPLICATE KEY UPDATE $updt";
        }
        else {
            $sql = "INSERT INTO $table_name ($fields) VALUES $cont";
        }

        return $this->exec($sql);
    }
}
