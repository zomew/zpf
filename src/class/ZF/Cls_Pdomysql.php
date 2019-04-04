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
 * @Modify  Jamers <jamersnox@zomew.net>
 * @license https://opensource.org/licenses/GPL-3.0 GPL
 * @since   2018.06.27
 *
 * 1. 允许使用表前缀，并且会智能区分表名是否已带前缀
 * 2. 添加group by功能
 * 3. 添加批量插入功能 insertBatch
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
 * 14. where in以及not in 操作，添加子查询功能 2019.04.03
 */

class Pdomysql
{
    /**
     * PDO数据库对象
     *
     * @var \PDO
     */
    protected $dbh = null; //静态属性,所有数据库实例共用,避免重复连接数据库
    protected $charset = 'utf8';   //默认字符集
    protected $dbType = 'mysql';
    protected $pconnect = false; //是否使用长连接
    protected $host = 'localhost';
    protected $port = 3306;
    protected $user = 'root';
    protected $pass = '';
    protected $dbName = null; //数据库名
    protected $sql = false; //最后一条sql语句
    protected $prefix = '';
    protected $where = '';
    protected $where_array = array();
    protected $l_where_array = array();
    protected $where_key_count = array();
    protected $order = '';
    protected $limit = '';
    protected $tbName = '';
    protected $field = '*';
    protected $group = '';
    protected $clear = 0; //状态，0表示查询条件干净，1表示查询条件污染
    protected $trans = 0; //事务指令数
    protected $fieldList = array();    //字段列表缓存数据

    /**
     * 初始化类
     *
     * @param array $conf 数据库配置
     */
    public function __construct(array $conf = array())
    {
        class_exists('PDO') or die("PDO: class not exists.");
        if ($conf == array()) {
            Common::LoadConfig();
            if (isset(\Config::$db) && \Config::$db) {
                $conf = \Config::$db;
            }
        }
        $this->host = $conf['hostname'];
        if (isset($conf['port'])) {
            $this->port = $conf['port'];
        }
        $this->user = $conf['username'];
        $this->pass = $conf['password'];
        $this->dbName = $conf['database'];
        if (isset($conf['prefix'])) {
            $this->setTblPrefix($conf['prefix']);
        }
        if (isset($conf['pconnect'])) {
            $this->pconnect = $conf['pconnect'];
        }
        if (isset($conf['charset'])) {
            $this->charset = $conf['charset'];
        }
        //连接数据库
        if (is_null($this->dbh)) {
            $this->connect();
        }
    }

    /**
     * 取表名
     *
     * @param string $tblname
     *
     * @return string
     */
    public function getTablename($tblname = '')
    {
        $ret = $tblname;
        if ($tblname) {
            if ($this->prefix) {
                if (!preg_match('/^\s*' . $this->prefix . '.*$/i', $tblname)) {
                    $ret = $this->prefix . $tblname;
                }
            }
            $this->tbName = $ret;
        } else {
            if ($this->tbName) {
                $ret = $this->tbName;
            }
        }
        return $ret;
    }

    /**
     * 设置表前缀
     *
     * @param string $value
     *
     * @return bool
     */
    public function setTblPrefix($value)
    {
        $this->prefix = trim($value);
        return true;
    }

    /**
     * 取表前缀
     *
     * @return mixed|string
     */
    public function getTblPrefix()
    {
        return $this->prefix;
    }

    /**
     * 连接数据库的方法
     *
     * @return void
     * @since  2019.03.23
     */
    protected function connect()
    {
        $dsn = $this->dbType . ':host=' . $this->host . ';port=' . $this->port . ';dbname=' . $this->dbName;
        $options = $this->pconnect ? array(\PDO::ATTR_PERSISTENT => true) : array();
        try {
            $dbh = new \PDO($dsn, $this->user, $this->pass, $options);
            $dbh->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);  //设置如果sql语句执行错误则抛出异常，事务会自动回滚
            $dbh->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false); //禁用prepared statements的仿真效果(防SQL注入)
        } catch (\PDOException $e) {
            die('Connection failed: ' . $e->getMessage());
        }
        $dbh->exec("SET NAMES {$this->charset}");
        $this->dbh = $dbh;
    }

    /**
     * 字段和表名添加 `符号
     * 保证指令中使用关键字不出错 针对mysql
     *
     * @param string $value
     *
     * @return string
     */
    protected function addChar($value)
    {
        if ('*' == $value || false !== strpos($value, '(')
            || false !== strpos($value, '.')
            || false !== strpos($value, '`')
        ) {
            //如果包含* 或者 使用了sql方法 则不作处理
        } elseif (false === strpos($value, '`')) {
            $value = '`' . trim($value) . '`';
        }
        return $value;
    }

    /**
     * 取得数据表的字段信息
     *
     * @param string $tbName 表名
     *
     * @return array
     */
    protected function tbFields($tbName)
    {
        $tbName = $this->getTablename($tbName);
        if (isset($this->fieldList[$tbName])) {
            return $this->fieldList[$tbName];
        }
        $sql = 'SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME=:tbName AND TABLE_SCHEMA=:dbName ';
        $stmt = $this->dbh->prepare($sql);
        $stmt->execute(array('tbName' => $tbName, 'dbName' => $this->dbName,));
        $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $ret = array();
        foreach ($result as $key => $value) {
            $ret[$value['COLUMN_NAME']] = 1;
        }
        $this->fieldList[$tbName] = $ret;
        return $ret;
    }

    /**
     * 过滤并格式化数据表字段
     *
     * @param string $tbName 数据表名
     * @param array  $data   POST提交数据
     *
     * @return array $newdata
     */
    protected function dataFormat($tbName, $data)
    {
        $tbName = $this->getTablename($tbName);
        if (!is_array($data)) {
            return array();
        }
        $table_column = $this->tbFields($tbName);
        $ret = array();
        foreach ($data as $key => $val) {
            if (!is_scalar($val)) {
                continue; //值不是标量则跳过
            }
            if (array_key_exists($key, $table_column)) {
                $key = $this->addChar($key);
                if (is_int($val)) {
                    $val = intval($val);
                } elseif (is_float($val)) {
                    $val = floatval($val);
                } elseif (preg_match('%^\s*(?:\(\w*(\+|\-|\*|/)?\w*\)|.*?`[\w\d]+`.*?)\s*$%i', $val)) {
                    // 支持在字段的值里面直接使用其它字段 ,例如 (score+1) (name) 必须包含括号
                    //$val = $val;
                } elseif (is_string($val)) {
                    $val = $this->dbh->quote($val);
                }
                $ret[$key] = $val;
            }
        }
        return $ret;
    }

    /**
     * 执行查询 主要针对 SELECT, SHOW 等指令
     *
     * @param string $sql sql指令
     *
     * @return mixed
     */
    protected function doQuery($sql = '')
    {
        $this->checkconn();
        $this->sql = $sql;
        $pdostmt = $this->dbh->prepare($this->sql); //prepare或者query 返回一个PDOStatement
        $this->l_where_array = $this->where_array;
        $pdostmt->execute($this->where_array);
        $result = $pdostmt->fetchAll(\PDO::FETCH_ASSOC);
        return $result;
    }

    /**
     * 执行语句 针对 INSERT, UPDATE 以及DELETE,exec结果返回受影响的行数
     *
     * @param string $sql sql指令
     *
     * @return integer
     */
    protected function doExec($sql = '')
    {
        if ($this->clear > 0) {
            $this->clear();
        }
        $this->checkconn();
        $ok = true;
        if ($this->where_array) {
            foreach ($this->where_array as $k => $v) {
                $sql = str_replace(':' . $k . ' ', "'" . str_replace("'", "\'", $v) . "' ", $sql);
            }
            $rs = $this->select('count(*) as ct')->prvSelect();
            if ($rs[0]['ct'] <= 0) {
                $ok = false;
            }
        }
        $this->sql = $sql;
        $this->l_where_array = array();
        $this->clear = 1;
        if ($ok) {
            return $this->dbh->exec($this->sql);
        } else {
            return 0;
        }
    }

    /**
     * 执行sql语句，自动判断进行查询或者执行操作
     *
     * @param string $sql SQL指令
     *
     * @return mixed
     */
    public function doSql($sql = '')
    {
        $this->clear();
        $qIps = 'INSERT|UPDATE|DELETE|REPLACE|CREATE|DROP|LOAD DATA|SELECT.* INTO|COPY|ALTER|GRANT|REVOKE|LOCK|UNLOCK';
        if (preg_match('/^\s*"?(' . $qIps . ')\s+/i', $sql)) {
            return $this->doExec($sql);
        } else {
            //查询操作
            return $this->doQuery($sql);
        }
    }

    /**
     * 获取最近一次查询的sql语句
     *
     * @return string 执行的SQL
     */
    public function getLastSql()
    {
        $ret = $this->sql;
        foreach ($this->l_where_array as $k => $v) {
            $ret = str_replace(':' . $k . ' ', "'" . str_replace("'", "\'", $v) . "' ", $ret);
        }
        return $ret;
    }

    /**
     * 插入方法
     *
     * @param string $tbName 操作的数据表名
     * @param array  $data   字段-值的一维数组
     *
     * @return int 受影响的行数
     */
    public function insert($tbName, array $data)
    {
        $tbName = $this->getTablename($tbName);
        $data = $this->dataFormat($tbName, $data);
        if (!$data) {
            return 0;
        }
        $sql = "insert into `" . $tbName . "` (" . implode(',', array_keys($data)) .
            ") values (" . implode(',', array_values($data)) . ")";
        return $this->doExec($sql);
    }

    /**
     * 批量插入数据
     *
     * @param string $tbName
     * @param array  $data
     *
     * @return int
     */
    public function insertBatch($tbName, array $data)
    {
        $tbName = $this->getTablename($tbName);
        $ret = 0;
        if ($data && is_array($data) && isset($data[0])) {
            $tmp = array_shift($data);
            $value = $this->dataFormat($tbName, $tmp);
            if (!$value) {
                return $ret;
            }
            $sql = "insert into `" . $tbName . "` (" . implode(',', array_keys($value)) . ") values ";
            $list = array();
            $list[] = "(" . implode(',', array_values($value)) . ")";
            while ($data) {
                $tmp = array_shift($data);
                $value = $this->dataFormat($tbName, $tmp);
                if ($value) {
                    $list[] = "(" . implode(',', array_values($value)) . ")";
                }
            }
            $sql .= implode(',', $list);
            $ret = $this->doExec($sql);
        }
        return $ret;
    }

    /**
     * 删除方法
     *
     * @param string $tbName 操作的数据表名
     *
     * @return int 受影响的行数
     */
    public function delete($tbName)
    {
        //安全考虑,阻止全表删除
        $tbName = $this->getTablename($tbName);
        if (!trim($this->where)) {
            return false;
        }
        $sql = "delete from `" . $tbName . "` " . $this->where;
        return $this->doExec($sql);
    }

    /**
     * 批量删除方法
     *
     * @param string $tbName
     * @param array  $data
     * @param string $in_logic
     *
     * @return int
     * @since  2018.12.05
     */
    public function deleteBatch($tbName, array $data, $in_logic = 'and')
    {
        $ret = 0;
        if ($data && is_array($data)) {
            $this->clear();
            foreach ($data as $v) {
                $this->where($v, $in_logic, 'or');
            }
            $ret = $this->delete($tbName);
        }
        return $ret;
    }

    /**
     * 更新函数
     *
     * @param string $tbName 操作的数据表名
     * @param array  $data   参数数组
     *
     * @return int 受影响的行数
     */
    public function update($tbName, array $data)
    {
        $tbName = $this->getTablename($tbName);
        //安全考虑,阻止全表更新
        if (!trim($this->where)) {
            return false;
        }
        $data = $this->dataFormat($tbName, $data);
        if (!$data) {
            return 0;
        }
        $valArr = array();
        foreach ($data as $k => $v) {
            $valArr[] = $k . '=' . $v;
        }
        $valStr = implode(',', $valArr);
        $sql = "update `" . trim($tbName) . "` set " . trim($valStr) .
            " " . trim($this->where);
        return $this->doExec($sql);
    }

    /**
     * 查询函数
     *
     * @param string $tbName 操作的数据表名
     *
     * @return array 结果集
     */
    private function prvSelect($tbName = '')
    {
        if ($this->clear > 0) {
            $this->clear();
        }
        $tbName = $this->getTablename($tbName);
        $sql = "select " . trim($this->field) . " from `" . $tbName . "` " .
            trim($this->where) . " " . trim($this->group) . " " .
            trim($this->order) . " " . trim($this->limit);
        $this->clear = 1;
        return $this->doQuery(trim($sql));
    }

    /**
     * 用于执行前进行查看查询语句，主要用于调试
     *
     * @param string $tbName 表名
     * @param bool   $clean  是否清除where数据
     *
     * @return mixed|string
     */
    public function getSql($tbName = '', $clean = false)
    {
        $tbName = $this->getTablename($tbName);
        $sql = "select " . trim($this->field) . " from `" . $tbName . "` " .
            trim($this->where) . " " . trim($this->group) . " " .
            trim($this->order) . " " . trim($this->limit);
        foreach ($this->where_array as $k => $v) {
            $sql = str_replace(
                ':' . $k . ' ',
                "'" . str_replace("'", "\'", $v) . "' ",
                $sql
            );
        }
        if ($clean) {
            $this->clear = 1;
        }
        return $sql;
    }

    /**
     * 获取所有数组
     *
     * @param string $tbName
     *
     * @return array
     */
    public function getAll($tbName = '')
    {
        return $this->prvSelect($tbName);
    }

    /**
     * 获取单条记录
     *
     * @param string $tbName
     *
     * @return array
     */
    public function getOne($tbName = '')
    {
        $rs = $this->limit('1')->prvSelect($tbName);
        $ret = array();
        if ($rs && isset($rs[0])) {
            $ret = $rs[0];
        }
        return $ret;
    }

    /**
     * 统计记录条数
     *
     * @param string $tbName
     *
     * @return int
     */
    public function count($tbName = '')
    {
        $ret = 0;
        $rs = $this->select('count(*) as _cnt_')->prvSelect($tbName);
        if (is_array($rs) && isset($rs[0]['_cnt_'])) {
            $ret = intval($rs[0]['_cnt_']);
        }
        return $ret;
    }

    /**
     * 字段值求和
     *
     * @param string $field
     * @param string $tbName
     *
     * @return float|int
     * @since  2019.01.22
     */
    public function sum($field = '', $tbName = '')
    {
        $ret = 0;
        if ($field) {
            $rs = $this->select("sum(`{$field}`) as _sum_")->prvSelect($tbName);
            if (is_array($rs) && isset($rs[0]['_sum_'])) {
                $ret = floatval($rs[0]['_sum_']);
            }
        }
        return $ret;
    }

    /**
     * 添加From条件
     *
     * @param string $tbName
     *
     * @return $this
     */
    public function from($tbName = '')
    {
        if ($tbName) {
            $this->tbName = $this->getTablename($tbName);
        }
        return $this;
    }

    /**
     * Where in 查询
     *
     * @param array  $option
     * @param string $logic
     * @param string $outlogic
     *
     * @return Pdomysql
     */
    public function wherein($option, $logic = 'and', $outlogic = 'and')
    {
        return $this->prvWhereNotIn($option, $logic, false, $outlogic);
    }

    /**
     * Where not in 查询
     *
     * @param array  $option
     * @param string $logic
     * @param string $outlogic
     *
     * @return Pdomysql
     */
    public function wherenotin($option, $logic = 'and', $outlogic = 'and')
    {
        return $this->prvWhereNotIn($option, $logic, true, $outlogic);
    }

    /**
     * Where in 及 Where not in相关数据
     *
     * @param array  $option
     * @param string $logic
     * @param bool   $isnotin
     * @param string $outlogic
     *
     * @return $this
     */
    private function prvWhereNotIn($option, $logic = 'and', $isnotin = false, $outlogic = 'and')
    {
        if ($option) {
            if ($this->clear > 0) {
                $this->clear();
            }
            $logic = trim($logic);
            if (!$this->where) {
                $this->where = ' where (';
                $this->where_key_count = array();
                $this->where_array = array();
            } else {
                if ($option) {
                    $this->where .= " {$outlogic} (";
                }
            }
            $opt = $isnotin ? 'not in' : 'in';
            if (is_array($option) && $option) {
                $ismark = false;
                foreach ($option as $k => $v) {
                    if (is_array($v) && $v) {
                        $condition = ' (' . $this->addChar($k) . " {$opt} (" . $this->addQuote($v) . '))';
                        $this->where .= $ismark ? " {$logic} {$condition}" : $condition;
                        $ismark = true;
                    }
                    if (is_string($v) && $v) {
                        $condition = ' (' . $this->addChar($k) . " {$opt} (" . $v . '))';
                        $this->where .= $ismark ? " {$logic} {$condition}" : $condition;
                        $ismark = true;
                    }
                }
            }
            $this->where .= ')';
        }
        return $this;
    }

    /**
     * 给数据添加引号
     *
     * @param array $ary
     *
     * @return string
     */
    private function addQuote($ary = array())
    {
        $ret = '';
        if ($ary && is_array($ary)) {
            $tmp = array();
            foreach ($ary as $v) {
                if (!is_array($v)) {
                    $tmp[] = $this->dbh->quote($v);
                }
            }
            $ret = trim(implode(',', $tmp));
        }
        return $ret;
    }

    /**
     * Where条件
     *
     * @param mixed  $option   组合条件的二维数组，例：$option['field1'] = array(1,'>=','or')
     * @param string $logic    逻辑操作符
     * @param string $outlogic 外层逻辑操作符
     *
     * @return $this
     */
    public function where($option, $logic = 'and', $outlogic = 'and')
    {
        if ($option) {
            if ($this->clear > 0) {
                $this->clear();
            }
            $logic = ' ' . trim($logic) . ' ';
            $outlogic = ' ' . trim($outlogic) . ' ';
            if (!$this->where) {
                $this->where = ' where (';
                $this->where_key_count = array();
                $this->where_array = array();
            } else {
                if ($option) {
                    $this->where .= "{$outlogic}(";
                }
            }

            if (is_string($option)) {
                $this->where .= $option;
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
                                    $condition .= $l . ' ' . $this->addChar($k) .
                                        ' ' . $r . ' ' . $n[0] . ' ';
                                } else {
                                    $tag = $this->getWhereTag($k);
                                    $this->where_array[$tag] = $n[0];
                                    if ($submark == 0) {
                                        $l = '';
                                        $submark = 1;
                                    }
                                    $condition .= $l . ' ' . $this->addChar($k) .
                                        ' ' . $r . ' :' . $tag . ' ';
                                }
                            }
                            $condition .= ')';
                        } else {
                            $relative = (isset($v[1]) && trim($v[1])) ? $v[1] : '=';
                            $logic = isset($v[2]) ? ' ' . trim($v[2]) . ' ' : $logic;
                            if (preg_match('/`.*`/i', $v[0])) {
                                $condition = '(' . $this->addChar($k) . ' ' .
                                    $relative . ' ' . $v[0] . ')';
                            } else {
                                $tag = $this->getWhereTag($k);
                                $this->where_array[$tag] = $v[0];
                                $condition = '(' . $this->addChar($k) . ' ' . $relative . ' :' . $tag . ' )';
                            }
                        }
                    } else {
                        //$logic = 'and';
                        if (preg_match('/`.*`/i', $v)) {
                            $condition = '(' . $this->addChar($k) . '=' . $v . ')';
                        } else {
                            if (false !== strpos($k, '(')
                                || false !== strpos($k, '.')
                                || false !== strpos($k, '`')
                            ) {
                                $condition = '(' . $this->addChar($k) . '=\'' .
                                    $v . '\')';
                            } else {
                                $tag = $this->getWhereTag($k);
                                $this->where_array[$tag] = $v;
                                $condition = '(' . $this->addChar($k) . '=:' .
                                    $tag . ' )';
                            }
                        }
                    }
                    $this->where .= isset($mark) ? $logic . $condition : $condition;
                    $mark = 1;
                }
            }
            $this->where .= ')';
        }
        return $this;
    }

    /**
     * 设置排序
     *
     * @param mixed $option 排序条件数组 例:array('sort'=>'desc')
     *
     * @return $this
     */
    public function order($option)
    {
        if ($option) {
            if ($this->clear > 0) {
                $this->clear();
            }
            $this->order = ' order by ';
            if (is_string($option)) {
                $this->order .= $option;
            } elseif (is_array($option)) {
                foreach ($option as $k => $v) {
                    $order = $this->addChar($k) . ' ' . $v;
                    $this->order .= isset($mark) ? ',' . $order : $order;
                    $mark = 1;
                }
            }
        }
        return $this;
    }

    /**
     * 设置查询行数及页数
     *
     * @param int $page     pageSize不为空时为页数，否则为行数
     * @param int $pageSize 为空则函数设定取出行数，不为空则设定取出行数及页数
     *
     * @return $this
     */
    public function limit($page, $pageSize = null)
    {
        if ($page > 0) {
            if ($this->clear > 0) {
                $this->clear();
            }
            if ($pageSize === null) {
                $this->limit = "limit " . $page;
            } else {
                $pageval = intval(($page - 1) * $pageSize);
                $this->limit = "limit " . $pageval . "," . $pageSize;
            }
        }
        return $this;
    }

    /**
     * 设置查询字段
     *
     * @param mixed $field 字段数组
     * @param bool  $raw   是否使用原字符串
     *
     * @return $this
     */
    public function select($field, $raw = false)
    {
        if ($field) {
            if ($this->clear > 0) {
                $this->clear();
            }
            if ($raw) {
                if (is_string($field)) {
                    $this->field = $field;
                }
                if (is_array($field)) {
                    $this->field = implode(',', $field);
                }
            } else {
                if (is_string($field)) {
                    $field = explode(',', $field);
                }
                $nField = array_map(array($this, 'addChar'), $field);
                $this->field = implode(',', $nField);
            }
        }
        return $this;
    }

    /**
     * 设置Group参数
     *
     * @param string $option
     *
     * @return $this
     */
    public function group($option = '')
    {
        if ($option) {
            if ($this->clear > 0) {
                $this->clear();
            }
            if ($option) {
                $this->group = 'group by ' . trim($option);
            }
        }
        return $this;
    }

    /**
     * 清理标记函数
     *
     * @return void
     */
    protected function clear()
    {
        $this->where = '';
        $this->order = '';
        $this->limit = '';
        $this->tbName = '';
        $this->group = '';
        $this->field = '*';
        $this->clear = 0;
        $this->where_array = array();
        $this->where_key_count = array();
    }

    /**
     * 手动清理标记
     *
     * @return $this
     */
    public function clearKey()
    {
        $this->clear();
        return $this;
    }

    /**
     * 启动事务
     *
     * @return void
     */
    public function startTrans()
    {
        //数据rollback 支持
        if ($this->trans == 0) {
            $this->dbh->beginTransaction();
        }
        $this->trans++;
        return;
    }

    /**
     * 用于非自动提交状态下面的查询提交
     *
     * @return boolean
     */
    public function commit()
    {
        $result = true;
        if ($this->trans > 0) {
            $result = $this->dbh->commit();
            $this->trans = 0;
        }
        return $result;
    }

    /**
     * 事务回滚
     *
     * @return boolean
     */
    public function rollback()
    {
        $result = true;
        if ($this->trans > 0) {
            $result = $this->dbh->rollback();
            $this->trans = 0;
        }
        return $result;
    }

    /**
     * 关闭连接
     * PHP 在脚本结束时会自动关闭连接。
     *
     * @return void
     */
    public function close()
    {
        if (!is_null($this->dbh)) {
            $this->dbh = null;
        }
    }

    /**
     * 检查连接是否正常，不正常重新连接
     *
     * @return void
     */
    private function checkconn()
    {
        try {
            $this->dbh->getAttribute(\PDO::ATTR_SERVER_INFO);
        } catch (\PDOException $e) {
            if (strpos($e->getMessage(), 'MySQL server has gone away') !== false) {
                $this->close();
                $this->connect();
            }
        }
    }

    /**
     * 取最后插入的主键ID
     *
     * @return string
     */
    public function getLastInsertId()
    {
        return $this->dbh->lastInsertId();
    }

    /**
     * 生成字段对应PDO标记
     *
     * @param string $k keys
     *
     * @return string
     */
    protected function getWhereTag($k)
    {
        $k = 'Tag'.md5($k);
        if (!isset($this->where_key_count[$k])) {
            $tag = $k;
            $this->where_key_count[$k] = 1;
        } else {
            $tag = $k.'_'.strval($this->where_key_count[$k]);
            $this->where_key_count[$k]++;
        }
        return $tag;
    }

    /**
     * 分割成一次多少条数据单独请求
     *
     * @param string $tblName
     * @param array  $ary
     * @param int    $nums
     *
     * @return int
     *
     * @since 2018.12.24
     */
    public function splitBatchInsert($tblName = '', $ary = array(), $nums = 100)
    {
        $ret = 0;
        if ($nums < 50) {
            $nums = 50;
        }
        if ($tblName && $ary) {
            $tmp = array();
            foreach ($ary as $v) {
                if (count($tmp) >= $nums) {
                    $ret += $this->insertBatch($tblName, $tmp);
                    $tmp = array();
                }
                $tmp[] = $v;
            }
            if ($tmp) {
                $ret += $this->insertBatch($tblName, $tmp);
            }
        }
        return $ret;
    }
}
