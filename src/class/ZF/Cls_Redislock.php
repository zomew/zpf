<?php
/**
 * Created by PhpStorm.
 * User: jamer
 * Date: 2018/6/4
 * Time: 11:33
 */

namespace ZF;

/**
 * Redis锁以及相关便捷操作模块
 *
 * @package ZF
 * @author  Jamers <jamersnox@zomew.net>
 * @license https://opensource.org/licenses/GPL-3.0 GPL
 * @since   2018.06.05
 */
class Redislock
{
    /**
     * Redis对象，可以用于进行原生操作
     *
     * @var \Redis
     */
    public $redis;

    /**
     * 原始连接信息
     *
     * @var array
     */
    private $conn;

    /**
     * Redislock constructor.
     * 初始化类，并创建连接
     *
     * @param array $config
     */
    public function __construct($config = array())
    {
        Common::LoadConfig();
        if (!$config && isset(\Config::$redis) && \Config::$redis) {
            $config = \Config::$redis;
        }
        $conn = array(
            'host' => '127.0.0.1',
            'port' => 6379,
            'pass' => '',
            'db' => 1,
        );
        if (isset($config)) {
            $conn = array_merge($conn, $config);
        }
        $this->conn = $conn;
        $this->redis = new \Redis();
        if ($this->redis->connect($conn['host'], $conn['port'])) {
            if ($conn['pass']) {
                if (!$this->redis->auth($conn['pass'])) {
                    exit('Redis password is wrong;');
                }
            }
            $this->redis->select($conn['db']);
        } else {
            exit('Redis connect info wrong!');
        }
    }

    /**
     * 检查连接是否已断，如果已断重连
     *
     * @return void
     */
    public function checkconn()
    {
        try {
            $is_dis = false;
            $ping = strtoupper($this->redis->ping());
            if ($ping != '+PONG') {
                $is_dis = true;
            }
        } catch (\Expcepted $e) {
            $is_dis = true;
        }
        if ($is_dis) {
            $conn = $this->conn;
            if ($this->redis->connect($conn['host'], $conn['port'])) {
                if ($conn['pass']) {
                    if (!$this->redis->auth($conn['pass'])) {
                        exit('Redis password is wrong;');
                    }
                }
                $this->redis->select($conn['db']);
            } else {
                exit('Redis connect info wrong!');
            }
        }
    }

    /**
     * 获取锁
     *
     * @param String $key    锁标识
     * @param Int    $expire 锁过期时间
     *
     * @return Boolean
     */
    public function lock($key, $expire = 5)
    {
        $this->checkconn();
        $is_lock = $this->redis->setnx($key, time()+$expire);
        // 不能获取锁
        if (!$is_lock) {
            // 判断锁是否过期
            $lock_time = $this->redis->get($key);
            // 锁已过期，删除锁，重新获取
            if (time()>$lock_time) {
                $this->unlock($key);
                $is_lock = $this->redis->setnx($key, time()+$expire);
            }
        }
        return $is_lock? true : false;
    }

    /**
     * 释放锁
     *
     * @param String $key 锁标识
     *
     * @return Boolean
     */
    public function unlock($key)
    {
        $this->checkconn();
        return $this->redis->del($key);
    }

    /**
     * LPop原生操作封装
     *
     * @param string $key
     *
     * @return string
     */
    public function lPop($key)
    {
        $this->checkconn();
        return $this->redis->lPop($key);
    }

    /**
     * $Push原生操作封装
     *
     * @param string $key
     * @param string $value
     *
     * @return bool|int
     */
    public function rPush($key, $value)
    {
        $this->checkconn();
        return $this->redis->rPush($key, $value);
    }

    /**
     * 检查Redis中还有多少个队列待处理
     *
     * @param string $key
     *
     * @return array
     */
    public function checkLoopNum($key)
    {
        $this->checkconn();
        $list = $this->redis->hGetAll($key);
        $ret = array('count' => 0, 'list' => array(),'all' => 0,);
        if ($list) {
            foreach ($list as $k => $v) {
                $n = intval($v);
                $x = 0;
                for ($i = 0; $i < $n; $i++) {
                    //if (!in_array("{$k}_{$i}",$all)) {
                    if (true) {
                        //未在处理的检查是否还存在
                        if ($this->redis->lLen("{$k}_{$i}") > 0) {
                            $ret['count']++;
                            $ret['list'][] = "{$k}_{$i}";
                            $x++;
                        }
                    } else {
                        $x++;
                    }
                }
                if ($x <= 0) {
                    //如果都为空，删除主记录中的数据
                    $this->redis->hDel($key, $k);
                    //删除缓存的消息发送URL
                    $this->delWXMSGUrl($k);
                    $this->delPROJECT($k);
                }
            }
            //$ret['all'] = count($all) + $ret['count'];
            $ret['all'] = $ret['count'];
        }
        return $ret;
    }

    /**
     * Redis中rPush数组，加速redis操作，可配置批量处理数量，返回push进去的数量
     *
     * @param string $key 键值
     * @param array  $ary 一维数据数组
     * @param int    $num 一次处理的数量，默认100
     *
     * @return int
     */
    public function rPushArray($key, $ary = array(), $num = 100)
    {
        $ret = 0;
        if ($key && is_string($key) && $ary && is_array($ary)) {
            $this->checkconn();
            $list = array();
            $cmd = '$ret += $this->redis->rPush($key, (@keys@));';
            foreach ($ary as $k => $v) {
                if (count($list) >= $num) {
                    eval(str_replace('(@keys@)', implode(', ', $list), $cmd));
                    $list = array();
                }
                $list[] = '$ary["'.$k.'"]';
            }
            if ($list) {
                eval(str_replace('(@keys@)', implode(', ', $list), $cmd));
            }
        }
        return $ret;
    }

    /**
     * 设置微信消息发送URL
     *
     * @param string $key
     * @param string $url
     *
     * @return bool|int
     */
    public function setWXMSGUrl($key, $url)
    {
        $ret = false;
        if ($key && $url && is_string($key) && is_string($url)) {
            $this->checkconn();
            $ret = $this->redis->hSet('WEIXIN_MSG_SEND_URL', $key, $url);
        }
        return $ret;
    }

    /**
     * 设置消息发送标志
     *
     * @param string $key
     * @param string $url
     *
     * @return bool|int
     */
    public function setPROJECT($key, $url)
    {
        $ret = false;
        if ($key && $url && is_string($key) && is_string($url)) {
            $this->checkconn();
            $ret = $this->redis->hSet('MSG_PROJECT_TAG', $key, $url);
        }
        return $ret;
    }

    /**
     * 删除微信消息发送URL
     *
     * @param string $key
     *
     * @return bool|int
     */
    public function delWXMSGUrl($key)
    {
        $this->checkconn();
        return $this->redis->hDel('WEIXIN_MSG_SEND_URL', $key);
    }

    /**
     * 删除发送标志
     *
     * @param string $key
     *
     * @return bool|int
     */
    public function delPROJECT($key)
    {
        $this->checkconn();
        return $this->redis->hDel('MSG_PROJECT_TAG', $key);
    }

    /**
     * 取所有的发送URL
     *
     * @return array
     */
    public function getWXMSGUrl()
    {
        $this->checkconn();
        return $this->redis->hGetAll('WEIXIN_MSG_SEND_URL');
    }

    /**
     * 获取所有发送标志
     *
     * @return array
     */
    public function getPROJECT()
    {
        $this->checkconn();
        return $this->redis->hGetAll('MSG_PROJECT_TAG');
    }

    /**
     * 批量发送微信模板信息
     *
     * @param string $url        发送链接
     * @param array  $ary        消息数据列表
     * @param string $prj        发送消息项目标识
     * @param int    $single_num 单批次发送数量
     *
     * @return void
     */
    public function batchSendWXMSG($url, $ary = array(), $prj = 'JZ/Tpl', $single_num = 100)
    {
        if ($url && $ary && is_array($ary)) {
            $this->checkconn();
            $key = 'LoopArray';
            if (isset(\Config::$part_key)) {
                $key = \Config::$part_key;
            }
            $md5 = md5('time_' . microtime(true) . rand(100, 999999));
            $count = ceil(count($ary)/$single_num);
            $this->redis->hSet($key, $md5, strval($count));
            $this->setWXMSGUrl($md5, $url);
            $this->setPROJECT($md5, $prj);
            $c = 0;
            $l = 0;
            $list = array();
            foreach ($ary as $v) {
                if ($c>=$single_num) {
                    $this->rPushArray("{$md5}_{$l}", $list);
                    $l ++;
                    $c = 0;
                    $list = array();
                }
                $list[] = $v;
                $c ++;
            }
            if ($list) {
                $this->rPushArray("{$md5}_{$l}", $list);
            }
        }
    }

    /**
     * 批量清除所有待发送信息
     *
     * @param string $key
     *
     * @return void
     */
    public function batchCleanMSB($key)
    {
        $this->checkconn();
        $list = $this->redis->hGetAll($key);
        if ($list) {
            foreach ($list as $k => $v) {
                $n = intval($v);
                for ($i = 0; $i < $n; $i++) {
                    $this->redis->lTrim("{$k}_{$i}", 0, 1);
                    $this->redis->lPop("{$k}_{$i}");
                    $this->redis->del("P_{$k}_{$i}");
                }
                //如果都为空，删除主记录中的数据
                $this->redis->hDel($key, $k);
                //删除缓存的消息发送URL
                $this->delWXMSGUrl($k);
                $this->delPROJECT($k);
            }
        }
    }

    /**
     * 批量删除Hash键值
     *
     * @param array|string $keys 需要删除hash键值的列表
     *
     * @return int 返回删除的hash内键值的总数量
     */
    public function delHashKeys($keys)
    {
        $ret = 0;
        if ($keys) {
            $this->checkconn();
            $cmd = '$ret += $this->redis->hDel($key, (@keys@));';
            if (is_string($keys)) {
                $keys = explode(',', $keys);
            }
            if (is_array($keys)) {
                foreach ($keys as $v) {
                    $key = $v;
                    $list = $this->redis->hKeys($key);
                    if ($list) {
                        $tmp = array();
                        foreach ($list as $k => $v) {
                            if ($v) {
                                $tmp[$k] = "'{$v}'";
                            }
                        }
                        eval(str_replace('(@keys@)', implode(', ', $tmp), $cmd));
                    }
                }
            }
        }
        return $ret;
    }

    /**
     * 手动设置Redis数据库ID，返回当前ID库
     *
     * @param int $id
     *
     * @return mixed
     */
    public function selectdb($id = -1)
    {
        if ($id != -1) {
            $id = intval($id);
            if ($id >= 0 && $id < 256) {
                $this->conn['db'] = $id;
                $this->checkconn();
                $this->redis->select($this->conn['db']);
            }
        }
        return $this->conn['db'];
    }

    /**
     * 检查指定WID是否在批量发送消息
     *
     * @param int $wid
     *
     * @return bool
     */
    public function checkSending($wid = 0)
    {
        $ret = false;
        if ($wid) {
            $list = $this->getWXMSGUrl();
            if ($list && is_array($list)) {
                foreach ($list as $k => $v) {
                    $id = 0;
                    if (stripos($v, '(@token@)') !== false) {
                        $id = 2;
                    } else {
                        if (preg_match('/\(@token_(\d+)@\)/i', $v, $m)) {
                            $id = intval($m[1]);
                        }
                    }
                    if ($id == intval($wid)) {
                        $ret = true;
                        break;
                    }
                }
            }
        }
        return $ret;
    }
}
