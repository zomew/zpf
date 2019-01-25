<?php
/**
 * Created by PhpStorm.
 * User: Jamers
 * Date: 2019/01/25
 * Time: 13:30
 * File: Cls_PackDemo.php
 */

class PackDemo extends \ZF\Entity {
    /**
     * 演示自动加载配置名
     * @var string
     */
    protected static $config_head = 'self';

    /**
     * 静态调用数据库对象
     * @var \ZF\Pdomysql
     */
    public static $sdb = null;

    /**
     * 静态调用Redis对象
     * @var \ZF\Redislock
     */
    public static $srl = null;

    /**
     * 静态调用MongoDB对象
     * @var \ZF\Mongodb
     */
    public static $smd = null;

    /**
     * 是否已实例化
     * @var bool
     */
    private static $is_construct = false;

    /**
     * 对象初始化
     * Mixing constructor.
     */
    public function __construct() {
        self::$is_construct = true;
        \ZF\Common::LoadConfig();
    }

    /**
     * 连接数据库
     * @param array|string $conf
     */
    protected function ConnectDB($conf = array()) {
        if (!$this->db) {
            if ($conf == array()) {
                $conf = \ZF\Common::LoadConfigData(self::$config_head, 'db');
            } else if (is_string($conf)) {
                $conf = \ZF\Common::LoadConfigData($conf, 'db');
            }
            $this->LoadClass('db', $conf);
        }
    }

    /**
     * 静态方式连接数据库
     * @since 2018.12.10
     *
     * @param array $conf
     * @param bool $alone
     * @return \ZF\Pdomysql
     */
    public static function sConnectDB($conf = array(), $alone = false) {
        $ret = self::$sdb;
        if (!self::$sdb || $alone) {
            if ($conf == array()) {
                $conf = \ZF\Common::LoadConfigData(self::$config_head, 'db');
            } else if (is_string($conf)) {
                $conf = \ZF\Common::LoadConfigData($conf, 'db');
            }
            if ($alone) {
                $ret = new \ZF\Pdomysql($conf);
            } else {
                self::$sdb = new \ZF\Pdomysql($conf);
                $ret = self::$sdb;
            }
        }
        return $ret;
    }

    /**
     * 断开数据库连接
     */
    protected function DisConnectDB() {
        if ($this->db) {
            $this->db->close();
            $this->db = null;
        }
    }

    /**
     * 静态方式断开数据库连接
     * @since 2018.12.10
     *
     */
    public static function sDisConnectDB() {
        if (self::$sdb) {
            self::$sdb->close();
            self::$sdb = null;
        }
    }

    /**
     * 连接Redis
     * @since 2018.11.08
     *
     * @param array $conf
     */
    protected function ConnectRedis($conf = array()) {
        if (!$this->rl) {
            if ($conf == array()) {
                $conf = \ZF\Common::LoadConfigData(self::$config_head, 'redis');
            } else if (is_string($conf)) {
                $conf = \ZF\Common::LoadConfigData($conf, 'redis');
            }
            $this->LoadClass('rl', $conf);
        }
    }

    /**
     * 静态方式连接Redis
     * @since 2018.12.10
     *
     * @param array $conf
     * @param bool $alone
     * @return \ZF\Redislock
     */
    public static function sConnectRedis($conf = array(), $alone = false) {
        $ret = self::$srl;
        if (!self::$srl || $alone) {
            if ($conf == array()) {
                $conf = \ZF\Common::LoadConfigData(self::$config_head, 'redis');
            } else if (is_string($conf)) {
                $conf = \ZF\Common::LoadConfigData($conf, 'redis');
            }
            if ($alone) {
                $ret = new \ZF\Redislock($conf);
            } else {
                self::$srl = new \ZF\Redislock($conf);
                $ret = self::$srl;
            }
        }
        return $ret;
    }

    /**
     * 断开Redis连接
     * @since 2018.11.08
     */
    protected function DisConnectRedis() {
        if ($this->rl) {
            $this->rl->redis->close();
            $this->rl = null;
        }
    }

    /**
     * 静态方式断开Redis连接
     * @since 2018.12.10
     *
     */
    public static function sDisConnectRedis() {
        if (self::$srl) {
            self::$srl->redis->close();
            self::$srl = null;
        }
    }

    /**
     * 连接MongoDB实例化方法
     * @since 2018.12.25
     *
     * @param array $conf
     */
    protected function ConnectMongoDB($conf = array()) {
        if (!$this->md) {
            if ($conf == array()) {
                $conf = \ZF\Common::LoadConfigData(self::$config_head, 'mongodb');
            }
            $this->LoadClass('md', $conf);
        }
    }

    /**
     * 静态方式连接MongoDB
     * @since 2018.12.25
     *
     * @param array $conf
     * @param bool $alone
     * @return null|\ZF\Mongodb
     */
    public static function sConnectMongoDB($conf = array(), $alone = false) {
        $ret = self::$smd;
        if (!self::$smd) {
            if ($conf == array()) {
                $conf = \ZF\Common::LoadConfigData(self::$config_head, 'mongodb');
            }
            if ($alone) {
                $ret = new \ZF\Mongodb($conf);
            } else {
                self::$smd = new \ZF\Mongodb($conf);
                $ret = self::$smd;
            }
        }
        return $ret;
    }

    /**
     * 断开MongoDB连接
     * @since 2018.12.25
     *
     */
    protected function DisConnectMongoDB() {
        if ($this->md) {
            $this->md = null;
        }
    }

    /**
     * 静态方式断开MongoDB连接
     * @since 2018.12.25
     *
     */
    public static function sDisConnectMongoDB() {
        if (self::$smd) {
            self::$smd = null;
        }
    }
}