<?php
/**
 * Created by PhpStorm.
 * User: Jamers
 * Date: 2019/01/25
 * Time: 13:30
 * File: Cls_PackDemo.php
 */

/**
 * Class PackDemo
 *
 * @author  Jamers <jamersnox@zomew.net>
 * @license https://opensource.org/licenses/GPL-3.0 GPL
 * @since   2019.01.25
 */
class PackDemo extends \ZF\Entity
{
    /**
     * 演示自动加载配置名
     *
     * @var string
     */
    protected static $config_head = 'self';

    /**
     * 静态调用数据库对象
     *
     * @var \ZF\Pdomysql
     */
    public static $sdb = null;

    /**
     * 静态调用Redis对象
     *
     * @var \ZF\Redislock
     */
    public static $srl = null;

    /**
     * 静态调用MongoDB对象
     *
     * @var \ZF\Mongodb
     */
    public static $smd = null;

    /**
     * 是否已实例化
     *
     * @var bool
     */
    private static $_is_construct = false;

    /**
     * 对象初始化
     * Mixing constructor.
     */
    public function __construct()
    {
        self::$_is_construct = true;
        \ZF\Common::LoadConfig();
    }

    /**
     * 连接数据库
     *
     * @param array $conf 连接信息
     *
     * @return void
     * @since  2018.12.10
     */
    protected function connectDB($conf = array())
    {
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
     *
     * @param array $conf  连接信息
     * @param bool  $alone 是否重建
     *
     * @return \ZF\Pdomysql
     * @static
     * @since  2018.12.10
     */
    public static function sConnectDB($conf = array(), $alone = false)
    {
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
     *
     * @return void
     * @since  2018.12.10
     */
    protected function disConnectDB()
    {
        if ($this->db) {
            $this->db->close();
            $this->db = null;
        }
    }

    /**
     * 静态方式断开数据库连接
     *
     * @return void
     * @static
     * @since  2018.12.10
     */
    public static function sDisConnectDB()
    {
        if (self::$sdb) {
            self::$sdb->close();
            self::$sdb = null;
        }
    }

    /**
     * 连接Redis
     *
     * @param array $conf 连接信息
     *
     * @return void
     * @since  2018.11.08
     */
    protected function connectRedis($conf = array())
    {
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
     *
     * @param array $conf  连接信息
     * @param bool  $alone 是否重建
     *
     * @return \ZF\Redislock
     * @static
     * @since  2018.12.10
     */
    public static function sConnectRedis($conf = array(), $alone = false)
    {
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
     *
     * @return void
     * @since  2018.11.08
     */
    protected function disConnectRedis()
    {
        if ($this->rl) {
            $this->rl->redis->close();
            $this->rl = null;
        }
    }

    /**
     * 静态方式断开Redis连接
     *
     * @return void
     * @static
     * @since  2018.12.10
     */
    public static function sDisConnectRedis()
    {
        if (self::$srl) {
            self::$srl->redis->close();
            self::$srl = null;
        }
    }

    /**
     * 连接MongoDB实例化方法
     *
     * @param array $conf 连接信息
     *
     * @return void
     * @since  2018.12.25
     */
    protected function connectMongoDB($conf = array())
    {
        if (!$this->md) {
            if ($conf == array()) {
                $conf = \ZF\Common::LoadConfigData(self::$config_head, 'mongodb');
            }
            $this->LoadClass('md', $conf);
        }
    }

    /**
     * 静态方式连接MongoDB
     *
     * @param array $conf  连接信息
     * @param bool  $alone 是否重建
     *
     * @return \ZF\Mongodb
     * @static
     * @since  2018.12.25
     */
    public static function sConnectMongoDB($conf = array(), $alone = false)
    {
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
     *
     * @return void
     * @since  2018.12.25
     */
    protected function disConnectMongoDB()
    {
        if ($this->md) {
            $this->md = null;
        }
    }

    /**
     * 静态方式断开MongoDB连接
     *
     * @return void
     * @static
     * @since  2018.12.25
     */
    public static function sDisConnectMongoDB()
    {
        if (self::$smd) {
            self::$smd = null;
        }
    }
}