<?php
/**
 * Created by PhpStorm.
 * User: jamer
 * Date: 2018/9/7
 * Time: 10:14
 */

namespace ZF;

/**
 * MongoDB操作类，原生的和官方的第三方类操作起来太麻烦了，手工封装一个吧
 *
 * @package ZF
 * @author  Jamers <jamersnox@zomew.net>
 * @license https://opensource.org/licenses/GPL-3.0 GPL
 * @since   2018.09.07
 */
class Mongodb
{
    /**
     * MongoDB连接对象
     *
     * @var \MongoDB\Driver\Manager
     */
    public $manager;
    /**
     * 数据库名称
     *
     * @var string
     */
    private $dbname;
    /**
     * 数据库连接字符串
     *
     * @var string
     */
    private $config;
    /**
     * 最后断线检查时间
     *
     * @var int
     */
    private $checktime = 0;

    /**
     * 连接MongoDB
     *
     * @param string $conf
     *
     * @return void
     */
    public function connectMongodb($conf = '')
    {
        if ($conf && is_string($conf)) {
            $this->config = $conf;
        }
        if ($this->config) {
            try {
                $this->manager = new \MongoDB\Driver\Manager($this->config);
            } catch (\MongoDB\Driver\Exception $e) {
                exit($e->__toString());
            }
            $this->checktime = time();
        }
    }

    /**
     * 初始化MongoDB类，配置参数可使用连接字符串或者数组
     *
     * Mongodb constructor.
     *
     * @param string $conf
     */
    public function __construct($conf = '')
    {
        class_exists('\MongoDB\Driver\Manager') or die('MongoDB class is not exists.');
        $this->loadConfig();
        if (!$conf) {
            if (isset(\Config::$mongodb) && \Config::$mongodb) {
                $conf = \Config::$mongodb;
            }
        }
        if (is_array($conf)) {
            $conf = $this->buildDSN($conf);
        } elseif (is_string($conf)) {
        } else {
            $conf = 'mongodb://localhost:27017/';
        }
        if (is_null($this->manager)) {
            $this->connectMongodb($conf);
        }
    }

    /**
     * 组织集合名称
     *
     * @param string $name
     *
     * @return string
     */
    public function getCollectionName($name = '')
    {
        $ret = $name;
        if ($name && is_string($name)) {
            if (!preg_match('/^\s*' . $this->dbname . '\..*$/i', $name)) {
                $ret = $this->dbname. '.' . $name;
            }
        }
        return $ret;
    }

    /**
     * 加载默认配置文件
     *
     * @return void
     * @since  2019.03.23
     */
    public function loadConfig()
    {
        if (!class_exists('\Config')) {
            $uname = php_uname('n');
            $config = array(
                ZF_ROOT . "config.{$uname}.php",
                ZF_ROOT . 'config.php',
                ZF_ROOT . 'config.example.php',
            );
            foreach ($config as $v) {
                if (file_exists($v)) {
                    include_once $v;
                    break;
                }
            }
        }
    }

    /**
     * 将数组转换成连接字符串
     *
     * @param array $conf
     *
     * @return string
     */
    public function buildDSN($conf = array())
    {
        $ret = 'mongodb://localhost:27017/';
        if ($conf && is_array($conf)) {
            $ret = 'mongodb://(@auth@)(@host@)/(@options@)';
            $ary = array('host' => 'localhost');
            if (isset($conf['username']) && $conf['username']) {
                $ary['auth'] = $conf['username'].':' .
                    (isset($conf['password'])?$conf['password']:'').'@';
            }
            if (isset($conf['hostname'])) {
                $port = array();
                if (isset($conf['port']) && $conf['port']) {
                    if (is_string($conf['port'])) {
                        $port = explode(',', $conf['port']);
                    } elseif (is_array($conf['port'])) {
                        $port = $conf['port'];
                    }
                }
                $host = array();
                $list = array();
                if (isset($conf['hostname']) && $conf['hostname']) {
                    if (is_string($conf['hostname'])) {
                        $host = explode(',', $conf['hostname']);
                    } elseif (is_array($conf['hostname'])) {
                        $host = $conf['hostname'];
                    }
                }
                if ($host) {
                    foreach ($host as $k => $v) {
                        if ($v && is_string($v)) {
                            $p = '';
                            if ($port) {
                                if (count($port) == 1) {
                                    $p = $port[0];
                                } elseif (isset($port[$k])) {
                                    $p = $port[$k];
                                }
                            }
                            if ($p) {
                                $list[] = $v.':'.$p;
                            } else {
                                $list[] = $v;
                            }
                        }
                    }
                    if ($list) {
                        $ary['host'] = implode(',', $list);
                    }
                }
            }
            if (isset($conf['database']) && $conf['database']) {
                $ary['database'] = $conf['database'];
                $this->dbname = $conf['database'];
            }
            if (isset($conf['options']) && $conf['options']
                && is_string($conf['options'])
            ) {
                $ary['options'] = '?'.$conf['options'];
            }
            $ret = Common::specialReplace($ret, $ary);
        }
        return $ret;
    }

    /**
     * 插入单个文档
     * @param string $collectionName
     * @param array  $data
     * @param array  $options
     *
     * @return bool|\MongoDB\Driver\WriteResult
     * @throws \MongoDB\Driver\Exception\Exception
     */
    public function insert($collectionName = '', $data = array(), $options = array())
    {
        $ret = false;
        if ($collectionName && $data) {
            $this->checkConnect();
            $bulk = new \MongoDB\Driver\BulkWrite();
            $bulk->insert($data);
            try {
                $ret = $this->manager->executeBulkWrite($this->getCollectionName($collectionName), $bulk, $options);
            } catch (\MongoDB\Driver\Exception $e) {
                exit($e->__toString());
            }
        }
        return $ret;
    }

    /**
     * 插入多个文档，数据为二维数组
     * @param string $collectionName
     * @param array  $data
     * @param array  $options
     *
     * @return bool|\MongoDB\Driver\WriteResult
     * @throws \MongoDB\Driver\Exception\Exception
     */
    public function batchInsert($collectionName = '', $data = array(), $options = array())
    {
        $ret = false;
        if ($collectionName && $data) {
            $this->checkConnect();
            $bulk = new \MongoDB\Driver\BulkWrite();
            foreach ($data as $v) {
                $bulk->insert($v);
            }
            try {
                $ret = $this->manager->executeBulkWrite($this->getCollectionName($collectionName), $bulk, $options);
            } catch (\MongoDB\Driver\Exception $e) {
                exit($e->__toString());
            }
        }
        return $ret;
    }

    /**
     * 查询数据
     * @param string $collectionName
     * @param array $filter
     * @param array $options
     * @return array|bool
     * @throws \MongoDB\Driver\Exception\Exception
     */
    public function query($collectionName = '', $filter = array(), $options = array())
    {
        $ret = false;
        if ($collectionName) {
            $this->checkConnect();
            $this->checkId($filter);
            try {
                $r = $this->manager->executeQuery(
                    $this->getCollectionName($collectionName),
                    new \MongoDB\Driver\Query($filter, $options)
                );
                $ret = array();
                foreach ($r as $v) {
                    $ret[] = get_object_vars($v);
                }
            } catch (\MongoDB\Driver\Exception $e) {
                exit($e->__toString());
            }
        }
        return $ret;
    }

    /**
     * 更新数据
     * @param string $collectionName
     * @param array  $filter
     * @param array  $data
     * @param array  $options
     *
     * @return bool|\MongoDB\Driver\WriteResult
     * @throws \MongoDB\Driver\Exception\Exception
     */
    public function update($collectionName = '', $filter = array(), $data = array(), $options = array())
    {
        $ret = false;
        if ($collectionName && $data && $filter) {
            $this->checkConnect();
            $bulk = new \MongoDB\Driver\BulkWrite();
            $this->checkId($filter);
            $bulk->update($filter, $data, $options);
            try {
                $ret = $this->manager->executeBulkWrite($this->getCollectionName($collectionName), $bulk);
            } catch (\MongoDB\Driver\Exception $e) {
                exit($e->__toString());
            }
        }
        return $ret;
    }

    /**
     * 删除记录
     * @param string $collectionName
     * @param array  $filter
     * @param int    $limit
     *
     * @return bool|\MongoDB\Driver\WriteResult
     * @throws \MongoDB\Driver\Exception\Exception
     */
    public function delete($collectionName = '', $filter = array(), $limit = 0)
    {
        $ret = false;
        if ($collectionName && $filter) {
            $this->checkConnect();
            $bulk = new \MongoDB\Driver\BulkWrite();
            $this->checkId($filter);
            $bulk->delete($filter, array('limit' => $limit));
            try {
                $ret = $this->manager->executeBulkWrite($this->getCollectionName($collectionName), $bulk);
            } catch (\MongoDB\Driver\Exception $e) {
                exit($e->__toString());
            }
        }
        return $ret;
    }

    /**
     * 批量删除
     * @param string $collectionName
     * @param array  $filter
     * @param int    $limit
     *
     * @return bool|\MongoDB\Driver\WriteResult
     * @throws \MongoDB\Driver\Exception\Exception
     */
    public function batchDelete($collectionName = '', $filter = array(), $limit = 0)
    {
        $ret = false;
        if ($collectionName && $filter) {
            $this->checkConnect();
            $bulk = new \MongoDB\Driver\BulkWrite();
            foreach ($filter as $v) {
                if ($v && is_array($v)) {
                    $tmp = $v;
                    $this->checkId($tmp);
                    $bulk->delete($tmp, array('limit' => $limit));
                }
            }
            try {
                $ret = $this->manager->executeBulkWrite($this->getCollectionName($collectionName), $bulk);
            } catch (\MongoDB\Driver\Exception $e) {
                exit($e->__toString());
            }
        }
        return $ret;
    }

    /**
     * 聚合查询
     * @param string $collectionName
     * @param array $command
     * @param string $databaseName
     * @return array|bool
     * @throws \MongoDB\Driver\Exception\Exception
     */
    public function aggregate($collectionName = '', $command = array(), $databaseName = '')
    {
        $ret = false;
        if ($collectionName && $command) {
            $this->checkConnect();
            $raw = array(
                'aggregate' => 'logs',
                'pipeline' => array(),
                'cursor' => (object) array(),
            );
            if (isset($command['aggregate']) && isset($command['pipeline'])) {
                $command = array_merge($raw, $command);
            } else {
                $raw['pipeline'] = $command;
                $command = $raw;
            }
            if ($collectionName) {
                $command['aggregate'] = $collectionName;
            }
            if ($databaseName == '') {
                $databaseName = $this->dbname;
            }
            $cmd = new \MongoDB\Driver\Command($command);
            try {
                $r = $this->manager->executeReadCommand($databaseName, $cmd);
                $ret = array();
                foreach ($r as $v) {
                    $ret[] = get_object_vars($v);
                }
            } catch (\MongoDB\Driver\Exception $e) {
                exit($e->__toString());
            }
        }
        return $ret;
    }

    /**
     * 检查是否断线，断线重连
     * @param bool $focus
     *
     * @return void
     * @throws \MongoDB\Driver\Exception\Exception
     */
    private function checkConnect($focus = false)
    {
        if ($focus || time() > $this->checktime + 30) {
            $cmd = new \MongoDB\Driver\Command(array('ping' => 1));
            $ok = true;
            try {
                $this->manager->executeReadCommand($this->dbname, $cmd);
                $this->checktime = time();
            } catch (\MongoDB\Driver\Exception $e) {
                $ok = false;
            }
            if (!$ok) {
                $this->connectMongodb();
            }
        }
    }

    /**
     * 处理字符串类型_id
     * @param $filter
     */
    private function checkId(&$filter)
    {
        if (isset($filter['_id'])) {
            if ($filter['_id']) {
                if (is_string($filter['_id'])) {
                    $filter['_id'] = new \MongoDB\BSON\ObjectId($filter['_id']);
                }
            }
            if (! $filter['_id'] instanceof \MongoDB\BSON\ObjectId) {
                unset($filter['_id']);
            }
        }
    }

    /**
     * 统计筛选记录条数（相对于查询交互数据更少，效率更高，用于不需要数据集的情况）
     * @param string $collectionName
     * @param array  $command
     * @param string $databaseName
     *
     * @return bool
     * @throws \MongoDB\Driver\Exception\Exception
     * @since  2019.02.20
     */
    public function count($collectionName = '', $command = array(), $databaseName = '')
    {
        $ret = false;
        if ($collectionName && $command) {
            $this->checkConnect();
            $raw = array(
                'count' => 'logs',
                'query' => array(),
            );
            if (isset($command['count']) && isset($command['query'])) {
                $command = array_merge($raw, $command);
            } else {
                $raw['query'] = $command;
                $command = $raw;
            }
            if ($collectionName) {
                $command['count'] = $collectionName;
            }
            if ($databaseName == '') {
                $databaseName = $this->dbname;
            }
            $cmd = new \MongoDB\Driver\Command($command);
            try {
                $r = $this->manager->executeReadCommand($databaseName, $cmd);
                if ($r) {
                    $tmp = $r->toArray()[0];
                    if ($tmp && is_object($tmp) && property_exists($tmp, 'n')) {
                        $ret = $tmp->n;
                    }
                }
            } catch (\MongoDB\Driver\Exception $e) {
                exit($e->__toString());
            }
        }
        return $ret;
    }

    /**
     * MongoDB原生去重，不能排序，需要排序建议使用aggregate
     * @param string $collectionName
     * @param array  $command
     * @param string $key
     * @param string $databaseName
     *
     * @return array
     * @throws \MongoDB\Driver\Exception\Exception
     * @since  2019.02.20
     */
    public function distinct($collectionName = '', $command = array(), $key = '', $databaseName = '')
    {
        $ret = array();
        if ($collectionName && $command) {
            $this->checkConnect();
            $raw = array(
                __FUNCTION__ => 'logs',
                'key' => "",
                'query' => array(),
            );
            if (isset($command[__FUNCTION__]) && isset($command['query'])) {
                $command = array_merge($raw, $command);
            } else {
                if ($key && is_string($key)) {
                    $raw['query'] = $command;
                    $command = $raw;
                }
            }
            if (!isset($command[__FUNCTION__])) {
                $command = array_merge(array(__FUNCTION__ => 'logs',), $command);
            }
            if ($collectionName) {
                $command[__FUNCTION__] = $collectionName;
            }
            if ($databaseName == '') {
                $databaseName = $this->dbname;
            }
            if ($command['key']) {
                $cmd = new \MongoDB\Driver\Command($command);
                try {
                    $r = $this->manager->executeReadCommand($databaseName, $cmd);
                    if ($r) {
                        $tmp = $r->toArray();
                        if (isset($tmp[0]) && is_object($tmp[0]) && property_exists($tmp[0], 'values')) {
                            $ret = $tmp[0]->values;
                        }
                    }
                } catch (\MongoDB\Driver\Exception $e) {
                    exit($e->__toString());
                }
            } else {
                trigger_error('distinct keys is not set');
            }
        }
        return $ret;
    }
}
