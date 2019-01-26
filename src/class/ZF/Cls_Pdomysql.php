<?php

namespace ZF;
/**
 * Desc: php操作mysql的封装类
 * Author lichuang
 * Date: 2016/05/30
 * 连接模式：PDO
 *
 * 添加前缀以及一些其他功能以满足日常需要
 *
 * @Modify Jamers <jamersnox@zomew.net>
 * @license https://opensource.org/licenses/GPL-3.0 GPL
 * @since 2018.06.27
 *
 * 1. 允许使用表前缀，并且会智能区分表名是否已带前缀
 * 2. 添加group by功能
 * 3. 添加批量插入功能 insert_batch
 * 4. 将from 替换原 select操作
 * 5. 将field 修改为 select
 * 6. 添加getAll 及 getOne快速获取数据
 * 7. 使用PDO安全过滤参数进行查询（避免注入风险）
 * 8. 支持同一字段使用多个条件进行查询
 * 9. 添加where in 以及 not in操作，这部分仍然是拼接字符串
 * 10. 支持多个where条件结连，但是复杂的逻辑关系，比如要加括号的，暂时没想到好的实现方式暂时还是自己拼接SQL语句吧
 * 11. 添加统计符合条件的记录条数  2018.10.26
 * 12. 特殊运算方式被当成字段替换的问题处理/update赋值部分允许使用`xx`字段直接处理 2018.11.08
 * 13. 添加批量删除方法，内部条件为 and ，外部条件为 or 2018.12.05
 */

class Pdomysql
{
    /**
     * PDO数据库对象
     * @var \PDO
     */
    protected $_dbh = null; //静态属性,所有数据库实例共用,避免重复连接数据库
    protected $_charset = 'utf8';   //默认字符集
    protected $_dbType = 'mysql';
    protected $_pconnect = false; //是否使用长连接
    protected $_host = 'localhost';
    protected $_port = 3306;
    protected $_user = 'root';
    protected $_pass = '';
    protected $_dbName = null; //数据库名
    protected $_sql = false; //最后一条sql语句
    protected $_prefix = '';
    protected $_where = '';
    protected $_where_array = array();
    protected $_l_where_array = array();
    protected $_where_key_count = array();
    protected $_order = '';
    protected $_limit = '';
    protected $_tbName = '';
    protected $_field = '*';
    protected $_group = '';
    protected $_clear = 0; //状态，0表示查询条件干净，1表示查询条件污染
    protected $_trans = 0; //事务指令数
    protected $_fieldList = array();    //字段列表缓存数据

    /**
     * 初始化类
     * @param array $conf 数据库配置
     */
    public function __construct(array $conf = array())
    {
        class_exists('PDO') or die("PDO: class not exists.");
        if ($conf == array()) {
            Common::LoadConfig();
            if (isset(\Config::$db) && \Config::$db) $conf = \Config::$db;
        }
        $this->_host = $conf['hostname'];
        if (isset($conf['port'])) $this->_port = $conf['port'];
        $this->_user = $conf['username'];
        $this->_pass = $conf['password'];
        $this->_dbName = $conf['database'];
        if (isset($conf['prefix'])) $this->setTblPrefix($conf['prefix']);
        if (isset($conf['pconnect'])) $this->_pconnect = $conf['pconnect'];
        if (isset($conf['charset'])) $this->_charset = $conf['charset'];
        //连接数据库
        if (is_null($this->_dbh)) {
            $this->_connect();
        }
    }

    /**
     * 取表名
     * @param string $tblname
     * @return string
     */
    public function getTablename($tblname = '')
    {
        $ret = $tblname;
        if ($tblname) {
            if ($this->_prefix) {
                if (!preg_match('/^\s*' . $this->_prefix . '.*$/i', $tblname)) {
                    $ret = $this->_prefix . $tblname;
                }
            }
            $this->_tbName = $ret;
        } else {
            if ($this->_tbName) $ret = $this->_tbName;
        }
        return $ret;
    }

    /**
     * 设置表前缀
     * @param $value
     * @return bool
     */
    public function setTblPrefix($value)
    {
        $this->_prefix = trim($value);
        return true;
    }

    /**
     * 取表前缀
     * @return mixed|string
     */
    public function getTblPrefix()
    {
        return $this->_prefix;
    }

    /**
     * 连接数据库的方法
     */
    protected function _connect()
    {
        $dsn = $this->_dbType . ':host=' . $this->_host . ';port=' . $this->_port . ';dbname=' . $this->_dbName;
        $options = $this->_pconnect ? array(\PDO::ATTR_PERSISTENT => true) : array();
        try {
            $dbh = new \PDO($dsn, $this->_user, $this->_pass, $options);
            $dbh->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);  //设置如果sql语句执行错误则抛出异常，事务会自动回滚
            $dbh->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false); //禁用prepared statements的仿真效果(防SQL注入)
        } catch (\PDOException $e) {
            die('Connection failed: ' . $e->getMessage());
        }
        $dbh->exec("SET NAMES {$this->_charset}");
        $this->_dbh = $dbh;
    }

    /**
     * 字段和表名添加 `符号
     * 保证指令中使用关键字不出错 针对mysql
     * @param string $value
     * @return string
     */
    protected function _addChar($value)
    {
        if ('*' == $value || false !== strpos($value, '(') || false !== strpos($value, '.') || false !== strpos($value, '`')) {
            //如果包含* 或者 使用了sql方法 则不作处理
        } elseif (false === strpos($value, '`')) {
            $value = '`' . trim($value) . '`';
        }
        return $value;
    }

    /**
     * 取得数据表的字段信息
     * @param string $tbName 表名
     * @return array
     */
    protected function _tbFields($tbName)
    {
        $tbName = $this->getTablename($tbName);
        if (isset($this->_fieldList[$tbName])) return $this->_fieldList[$tbName];
        $sql = 'SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME=:tbName AND TABLE_SCHEMA=:dbName ';
        $stmt = $this->_dbh->prepare($sql);
        $stmt->execute(array('tbName' => $tbName, 'dbName' => $this->_dbName,));
        $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $ret = array();
        foreach ($result as $key => $value) {
            $ret[$value['COLUMN_NAME']] = 1;
        }
        $this->_fieldList[$tbName] = $ret;
        return $ret;
    }

    /**
     * 过滤并格式化数据表字段
     * @param string $tbName 数据表名
     * @param array $data POST提交数据
     * @return array $newdata
     */
    protected function _dataFormat($tbName, $data)
    {
        $tbName = $this->getTablename($tbName);
        if (!is_array($data)) return array();
        $table_column = $this->_tbFields($tbName);
        $ret = array();
        foreach ($data as $key => $val) {
            if (!is_scalar($val)) continue; //值不是标量则跳过
            if (array_key_exists($key, $table_column)) {
                $key = $this->_addChar($key);
                if (is_int($val)) {
                    $val = intval($val);
                } elseif (is_float($val)) {
                    $val = floatval($val);
                } elseif (preg_match('%^\s*(?:\(\w*(\+|\-|\*|/)?\w*\)|.*?`[\w\d]+`.*?)\s*$%i', $val)) {
                    // 支持在字段的值里面直接使用其它字段 ,例如 (score+1) (name) 必须包含括号
                    //$val = $val;
                } elseif (is_string($val)) {
                    //$val = '"' . addslashes($val) . '"';
                    $val = $this->_dbh->quote($val);
                }
                $ret[$key] = $val;
            }
        }
        return $ret;
    }

    /**
     * 执行查询 主要针对 SELECT, SHOW 等指令
     * @param string $sql sql指令
     * @return mixed
     */
    protected function _doQuery($sql = '')
    {
        $this->_checkconn();
        $this->_sql = $sql;
        $pdostmt = $this->_dbh->prepare($this->_sql); //prepare或者query 返回一个PDOStatement
        $this->_l_where_array = $this->_where_array;
        $pdostmt->execute($this->_where_array);
        $result = $pdostmt->fetchAll(\PDO::FETCH_ASSOC);
        return $result;
    }

    /**
     * 执行语句 针对 INSERT, UPDATE 以及DELETE,exec结果返回受影响的行数
     * @param string $sql sql指令
     * @return integer
     */
    protected function _doExec($sql = '')
    {
        if ($this->_clear > 0) $this->_clear();
        $this->_checkconn();
        $ok = true;
        if ($this->_where_array) {
            foreach ($this->_where_array as $k => $v) {
                $sql = str_replace(':' . $k . ' ', "'" . str_replace("'", "\'", $v) . "' ", $sql);
            }
            $rs = $this->select('count(*) as ct')->_select();
            if ($rs[0]['ct'] <= 0) $ok = false;
        }
        $this->_sql = $sql;
        $this->_l_where_array = array();
        $this->_clear = 1;
        if ($ok) {
            return $this->_dbh->exec($this->_sql);
        }else{
            return 0;
        }
    }

    /**
     * 执行sql语句，自动判断进行查询或者执行操作
     * @param string $sql SQL指令
     * @return mixed
     */
    public function doSql($sql = '')
    {
        $this->_clear();
        $queryIps = 'INSERT|UPDATE|DELETE|REPLACE|CREATE|DROP|LOAD DATA|SELECT .* INTO|COPY|ALTER|GRANT|REVOKE|LOCK|UNLOCK';
        if (preg_match('/^\s*"?(' . $queryIps . ')\s+/i', $sql)) {
            return $this->_doExec($sql);
        } else {
            //查询操作
            return $this->_doQuery($sql);
        }
    }

    /**
     * 获取最近一次查询的sql语句
     * @return String 执行的SQL
     */
    public function getLastSql()
    {
        $ret = $this->_sql;
        foreach ($this->_l_where_array as $k => $v) {
            $ret = str_replace(':'.$k.' ',"'".str_replace("'","\'",$v)."' ",$ret);
        }
        return $ret;
    }

    /**
     * 插入方法
     * @param string $tbName 操作的数据表名
     * @param array $data 字段-值的一维数组
     * @return int 受影响的行数
     */
    public function insert($tbName, array $data)
    {
        $tbName = $this->getTablename($tbName);
        $data = $this->_dataFormat($tbName, $data);
        if (!$data) return;
        $sql = "insert into `" . $tbName . "` (" . implode(',', array_keys($data)) . ") values (" . implode(',', array_values($data)) . ")";
        return $this->_doExec($sql);
    }

    /**
     * 批量插入数据
     * @param $tbName
     * @param array $data
     * @return int
     */
    public function insert_batch($tbName, array $data)
    {
        $tbName = $this->getTablename($tbName);
        $ret = 0;
        if ($data && is_array($data) && isset($data[0])) {
            $tmp = array_shift($data);
            $value = $this->_dataFormat($tbName, $tmp);
            if (!$value) return $ret;
            $sql = "insert into `" . $tbName . "` (" . implode(',', array_keys($value)) . ") values ";
            $list = array();
            $list[] = "(" . implode(',', array_values($value)) . ")";
            while ($data) {
                $tmp = array_shift($data);
                $value = $this->_dataFormat($tbName, $tmp);
                if ($value) $list[] = "(" . implode(',', array_values($value)) . ")";
            }
            $sql .= implode(',', $list);
            $ret = $this->_doExec($sql);
        }
        return $ret;
    }

    /**
     * 删除方法
     * @param string $tbName 操作的数据表名
     * @return int 受影响的行数
     */
    public function delete($tbName)
    {
        //安全考虑,阻止全表删除
        $tbName = $this->getTablename($tbName);
        if (!trim($this->_where)) return false;
        $sql = "delete from `" . $tbName . "` " . $this->_where;
        return $this->_doExec($sql);
    }

    /**
     * 批量删除方法
     * @since 2018.12.05
     *
     * @param $tbName
     * @param array $data
     * @param string $in_logic
     * @return int
     */
    public function delete_batch($tbName, array $data, $in_logic = 'and') {
        $ret = 0;
        if ($data && is_array($data)) {
            $this->_clear();
            foreach($data as $v) {
                $this->where($v, $in_logic, 'or');
            }
            $ret = $this->delete($tbName);
        }
        return $ret;
    }

    /**
     * 更新函数
     * @param string $tbName 操作的数据表名
     * @param array $data 参数数组
     * @return int 受影响的行数
     */
    public function update($tbName, array $data)
    {
        $tbName = $this->getTablename($tbName);
        //安全考虑,阻止全表更新
        if (!trim($this->_where)) return false;
        $data = $this->_dataFormat($tbName, $data);
        if (!$data) return;
        $valArr = array();
        foreach ($data as $k => $v) {
            $valArr[] = $k . '=' . $v;
        }
        $valStr = implode(',', $valArr);
        $sql = "update `" . trim($tbName) . "` set " . trim($valStr) . " " . trim($this->_where);
        return $this->_doExec($sql);
    }

    /**
     * 查询函数
     * @param string $tbName 操作的数据表名
     * @return array 结果集
     */
    private function _select($tbName = '')
    {
        if ($this->_clear > 0) $this->_clear();
        $tbName = $this->getTablename($tbName);
        $sql = "select " . trim($this->_field) . " from `" . $tbName . "` " . trim($this->_where) . " " . trim($this->_group) . " " . trim($this->_order) . " " . trim($this->_limit);
        $this->_clear = 1;
        return $this->_doQuery(trim($sql));
    }

    /**
     * 用于执行前进行查看查询语句，主要用于调试
     * @param string $tbName
     * @return mixed|string
     */
    public function getSql($tbName = '') {
        $tbName = $this->getTablename($tbName);
        $sql = "select " . trim($this->_field) . " from `" . $tbName . "` " . trim($this->_where) . " " . trim($this->_order) . " " . trim($this->_group) . " " . trim($this->_limit);
        foreach ($this->_where_array as $k => $v) {
            $sql = str_replace(':'.$k.' ',"'".str_replace("'","\'",$v)."' ",$sql);
        }
        return $sql;
    }

    /**
     * 获取所有数组
     * @param string $tbName
     * @return array
     */
    public function getAll($tbName = '')
    {
        return $this->_select($tbName);
    }

    /**
     * 获取单条记录
     * @param string $tbName
     * @return array
     */
    public function getOne($tbName = '')
    {
        $rs = $this->limit('1')->_select($tbName);
        $ret = array();
        if ($rs && isset($rs[0])) $ret = $rs[0];
        return $ret;
    }

    /**
     * 统计记录条数
     * @param string $tbName
     * @return int
     */
    public function count($tbName = '') {
        $ret = 0;
        $rs = $this->select('count(*) as _cnt_')->_select($tbName);
        if (is_array($rs) && isset($rs[0]['_cnt_'])) $ret = intval($rs[0]['_cnt_']);
        return $ret;
    }

    /**
     * 字段值求和
     * @since 2019.01.22
     *
     * @param string $field
     * @param string $tbName
     * @return float|int
     */
    public function sum($field = '', $tbName = '') {
        $ret = 0;
        if ($field) {
            $rs = $this->select("sum(`{$field}`) as _sum_")->_select($tbName);
            if (is_array($rs) && isset($rs[0]['_sum_'])) $ret = floatval($rs[0]['_sum_']);
        }
        return $ret;
    }

    /**
     * 添加From条件
     * @param string $tbName
     * @return $this
     */
    public function from($tbName = '')
    {
        if ($tbName) {
            $this->_tbName = $this->getTablename($tbName);
        }
        return $this;
    }

    /**
     * Where in 查询
     * @param $option
     * @param string $logic
     * @param string $outlogic
     * @return Pdomysql
     */
    public function wherein($option,$logic = 'and',$outlogic = 'and') {
        return $this->_where_not_in($option, $logic, false,$outlogic);
    }

    /**
     * Where not in 查询
     * @param $option
     * @param string $logic
     * @param string $outlogic
     * @return Pdomysql
     */
    public function wherenotin($option,$logic = 'and',$outlogic = 'and') {
        return $this->_where_not_in($option, $logic, true, $outlogic);
    }

    /**
     * Where in 及 Where not in相关数据
     * @param $option
     * @param string $logic
     * @param bool $isnotin
     * @param string $outlogic
     * @return $this
     */
    private function _where_not_in($option,$logic = 'and',$isnotin = false,$outlogic = 'and') {
        if ($option) {
            if ($this->_clear > 0) $this->_clear();
            $logic = trim($logic);
            if (!$this->_where) {
                $this->_where = ' where (';
                $this->_where_key_count = array();
                $this->_where_array = array();
            } else {
                if ($option) {
                    $this->_where .= " {$outlogic} (";
                }
            }
            $opt = $isnotin ? 'not in' : 'in';
            if (is_array($option) && $option) {
                $ismark = false;
                foreach ($option as $k => $v) {
                    if (is_array($v) && $v) {
                        $condition = ' (' . $this->_addChar($k) . " {$opt} (" . $this->_addQuote($v) . '))';
                        $this->_where .= $ismark ? " {$logic} {$condition}" : $condition;
                        $ismark = true;
                    }
                }
            }
            $this->_where .= ')';
        }
        return $this;
    }

    /**
     * 给数据添加引号
     * @param array $ary
     * @return string
     */
    private function _addQuote($ary = array()) {
        $ret = '';
        if ($ary && is_array($ary)) {
            $tmp = array();
            foreach($ary as $v) {
                if (!is_array($v)) {
                    //$tmp[] = "'".addslashes($v)."'";
                    $tmp[] = $this->_dbh->quote($v);
                }
            }
            $ret = trim(implode(',',$tmp));
        }
        return $ret;
    }

    /**
     * @param mixed $option 组合条件的二维数组，例：$option['field1'] = array(1,'>=','or')
     * @param string $logic 逻辑操作符
     * @param string $outlogic 外层逻辑操作符
     * @return $this
     */
    public function where($option,$logic = 'and', $outlogic = 'and')
    {
        if ($option) {
            if ($this->_clear > 0) $this->_clear();
            $logic = ' ' . trim($logic) . ' ';
            $outlogic = ' ' . trim($outlogic) . ' ';
            if (!$this->_where) {
                $this->_where = ' where (';
                $this->_where_key_count = array();
                $this->_where_array = array();
            } else {
                if ($option) {
                    $this->_where .= "{$outlogic}(";
                }
            }

            if (is_string($option)) {
                $this->_where .= $option;
            } elseif (is_array($option)) {
                foreach ($option as $k => $v) {
                    if (is_array($v)) {
                        if (is_array($v[0])) {
                            $condition = '(';
                            $submark = 0;
                            foreach ($v as $n) {
                                $r = (isset($n[1]) && trim($n[1])) ? $n[1] : '=';
                                $l = isset($n[2]) ? ' ' . trim($n[2]) . ' ' : $logic;
                                if (preg_match('/`.*`/i', $n[0])) {
                                    $condition .= $l . ' ' . $this->_addChar($k) . ' ' . $r . ' ' . $n[0] . ' ';
                                } else {
                                    $tag = $this->getWhereTag($k);
                                    $this->_where_array[$tag] = $n[0];
                                    if ($submark == 0) {
                                        $l = '';
                                        $submark = 1;
                                    }
                                    $condition .= $l . ' ' . $this->_addChar($k) . ' ' . $r . ' :' . $tag . ' ';
                                }
                            }
                            $condition .= ')';
                        } else {
                            $relative = (isset($v[1]) && trim($v[1])) ? $v[1] : '=';
                            $logic = isset($v[2]) ? ' ' . trim($v[2]) . ' ' : $logic;
                            if (preg_match('/`.*`/i', $v[0])) {
                                $condition = '(' . $this->_addChar($k) . ' ' . $relative . ' ' . $v[0] . ')';
                            } else {
                                $tag = $this->getWhereTag($k);
                                //$condition = '(' . $this->_addChar($k) . ' ' . $relative . ' ' . $v[0] . ')';
                                $this->_where_array[$tag] = $v[0];
                                $condition = '(' . $this->_addChar($k) . ' ' . $relative . ' :' . $tag . ' )';
                            }
                        }
                    } else {
                        //$logic = 'and';
                        if (preg_match('/`.*`/i', $v)) {
                            $condition = '(' . $this->_addChar($k) . '=' . $v . ')';
                        } else {
                            if (false !== strpos($k, '(') || false !== strpos($k, '.') || false !== strpos($k, '`')) {
                                $condition = '(' . $this->_addChar($k) . '=\'' . $v . '\')';
                            } else {
                                $tag = $this->getWhereTag($k);
                                //$condition = '(' . $this->_addChar($k) . '=' . $v . ')';
                                $this->_where_array[$tag] = $v;
                                $condition = '(' . $this->_addChar($k) . '=:' . $tag . ' )';
                            }
                        }
                    }
                    $this->_where .= isset($mark) ? $logic . $condition : $condition;
                    $mark = 1;
                }
            }
            $this->_where .= ')';
        }
        return $this;
    }

    /**
     * 设置排序
     * @param mixed $option 排序条件数组 例:array('sort'=>'desc')
     * @return $this
     */
    public function order($option)
    {
        if ($option) {
            if ($this->_clear > 0) $this->_clear();
            $this->_order = ' order by ';
            if (is_string($option)) {
                $this->_order .= $option;
            } elseif (is_array($option)) {
                foreach ($option as $k => $v) {
                    $order = $this->_addChar($k) . ' ' . $v;
                    $this->_order .= isset($mark) ? ',' . $order : $order;
                    $mark = 1;
                }
            }
        }
        return $this;
    }

    /**
     * 设置查询行数及页数
     * @param int $page pageSize不为空时为页数，否则为行数
     * @param int $pageSize 为空则函数设定取出行数，不为空则设定取出行数及页数
     * @return $this
     */
    public function limit($page, $pageSize = null)
    {
        if ($page > 0) {
            if ($this->_clear > 0) $this->_clear();
            if ($pageSize === null) {
                $this->_limit = "limit " . $page;
            } else {
                $pageval = intval(($page - 1) * $pageSize);
                $this->_limit = "limit " . $pageval . "," . $pageSize;
            }
        }
        return $this;
    }

    /**
     * 设置查询字段
     * @param mixed $field 字段数组
     * @param bool $raw 是否使用原字符串
     * @return $this
     */
    public function select($field,$raw=false)
    {
        if ($field) {
            if ($this->_clear > 0) $this->_clear();
            if ($raw) {
                if (is_string($field)) $this->_field = $field;
                if (is_array($field)) $this->_field = implode(',', $field);
            } else {
                if (is_string($field)) {
                    $field = explode(',', $field);
                }
                $nField = array_map(array($this, '_addChar'), $field);
                $this->_field = implode(',', $nField);
            }
        }
        return $this;
    }

    /**
     * 设置Group参数
     *
     * @param string $option
     * @return $this
     */
    public function group($option = '')
    {
        if ($option) {
            if ($this->_clear > 0) $this->_clear();
            if ($option) {
                $this->_group = 'group by ' . trim($option);
            }
        }
        return $this;
    }

    /**
     * 清理标记函数
     */
    protected function _clear()
    {
        $this->_where = '';
        $this->_order = '';
        $this->_limit = '';
        $this->_tbName = '';
        $this->_group = '';
        $this->_field = '*';
        $this->_clear = 0;
        $this->_where_array = array();
        $this->_where_key_count = array();
    }

    /**
     * 手动清理标记
     * @return $this
     */
    public function clearKey()
    {
        $this->_clear();
        return $this;
    }

    /**
     * 启动事务
     * @return void
     */
    public function startTrans()
    {
        //数据rollback 支持
        if ($this->_trans == 0) $this->_dbh->beginTransaction();
        $this->_trans++;
        return;
    }

    /**
     * 用于非自动提交状态下面的查询提交
     * @return boolean
     */
    public function commit()
    {
        $result = true;
        if ($this->_trans > 0) {
            $result = $this->_dbh->commit();
            $this->_trans = 0;
        }
        return $result;
    }

    /**
     * 事务回滚
     * @return boolean
     */
    public function rollback()
    {
        $result = true;
        if ($this->_trans > 0) {
            $result = $this->_dbh->rollback();
            $this->_trans = 0;
        }
        return $result;
    }

    /**
     * 关闭连接
     * PHP 在脚本结束时会自动关闭连接。
     */
    public function close()
    {
        if (!is_null($this->_dbh)) $this->_dbh = null;
    }

    /**
     * 检查连接是否正常，不正常重新连接
     */
    private function _checkconn() {
        try {
            $this->_dbh->getAttribute(\PDO::ATTR_SERVER_INFO);
        }catch(\PDOException $e) {
            if(strpos($e->getMessage(), 'MySQL server has gone away')!==false) {
                $this->close();
                $this->_connect();
            }
        }
    }

    /**
     * 取最后插入的主键ID
     *
     * @return string
     */
    public function getLastInsertId() {
        return $this->_dbh->lastInsertId();
    }

    /**
     * 生成字段对应PDO标记
     * @param $k
     * @return string
     */
    protected function getWhereTag($k) {
        $k = 'Tag'.md5($k);
        if (!isset($this->_where_key_count[$k])) {
            $tag = $k;
            $this->_where_key_count[$k] = 1;
        }else{
            $tag = $k.'_'.strval($this->_where_key_count[$k]);
            $this->_where_key_count[$k]++;
        }
        return $tag;
    }

    /**
     * 分割成一次多少条数据单独请求
     * @since 2018.12.24
     *
     * @param string $tblName
     * @param array $ary
     * @param int $nums
     * @return int
     */
    public function split_batch_insert($tblName = '', $ary = array(), $nums = 100) {
        $ret = 0;
        if ($nums < 50) $nums = 50;
        if ($tblName && $ary) {
            $tmp = array();
            foreach($ary as $v) {
                if (count($tmp) >= $nums) {
                    $ret += $this->insert_batch($tblName, $tmp);
                    $tmp = array();
                }
                $tmp[] = $v;
            }
            if ($tmp) {
                $ret += $this->insert_batch($tblName, $tmp);
            }
        }
        return $ret;
    }
}