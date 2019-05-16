<?php
/**
 * Created by PhpStorm.
 * User: Jamers
 * Date: 2019/5/15
 * Time: 11:32
 * File: Cls_DingTalk.php
 */

namespace ZF;

/**
 * 阿里钉钉相关模块封装
 *
 * @package ZF
 * @author  Jamers <jamersnox@zomew.net>
 * @license https://opensource.org/licenses/GPL-3.0 GPL
 * @since   2019.05.15
 */
class DingTalk extends Entity
{
    private static $OAPI_HOST = 'https://oapi.dingtalk.com';
    private static $config = [];
    private static $redis_cache = false;

    private static $Redis_Token_Key = 'DingTalk_AccessToken';

    /**
     * 演示自动加载配置名
     *
     * @var string
     */
    protected static $config_head = 'DINGTALK';

    /**
     * 静态调用数据库对象
     *
     * @var Pdomysql
     */
    public static $sdb = null;

    /**
     * 静态调用Redis对象
     *
     * @var Redislock
     */
    public static $srl = null;

    /**
     * 静态调用MongoDB对象
     *
     * @var Mongodb
     */
    public static $smd = null;

    /**
     * 是否已实例化
     *
     * @var bool
     */
    private static $is_construct = false;

    /**
     * 对象初始化
     * Mixing constructor.
     */
    public function __construct()
    {
        self::$is_construct = true;
        Common::loadConfig();
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
                $conf = Common::loadConfigData(self::$config_head, 'db');
            } elseif (is_string($conf)) {
                $conf = Common::loadConfigData($conf, 'db');
            }
            $this->loadClass('db', $conf);
        }
    }

    /**
     * 静态方式连接数据库
     *
     * @param array $conf  连接信息
     * @param bool  $alone 是否重建
     *
     * @return Pdomysql
     * @static
     * @since  2018.12.10
     */
    public static function sConnectDB($conf = array(), $alone = false)
    {
        $ret = self::$sdb;
        if (!self::$sdb || $alone) {
            if ($conf == array()) {
                $conf = Common::loadConfigData(self::$config_head, 'db');
            } elseif (is_string($conf)) {
                $conf = Common::loadConfigData($conf, 'db');
            }
            if ($alone) {
                $ret = new Pdomysql($conf);
            } else {
                self::$sdb = new Pdomysql($conf);
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
                $conf = Common::loadConfigData(self::$config_head, 'redis');
            } elseif (is_string($conf)) {
                $conf = Common::loadConfigData($conf, 'redis');
            }
            $this->loadClass('rl', $conf);
        }
    }

    /**
     * 静态方式连接Redis
     *
     * @param array $conf  连接信息
     * @param bool  $alone 是否重建
     *
     * @return Redislock
     * @static
     * @since  2018.12.10
     */
    public static function sConnectRedis($conf = array(), $alone = false)
    {
        $ret = self::$srl;
        if (!self::$srl || $alone) {
            if ($conf == array()) {
                $conf = Common::loadConfigData(self::$config_head, 'redis');
            } elseif (is_string($conf)) {
                $conf = Common::loadConfigData($conf, 'redis');
            }
            if ($alone) {
                $ret = new Redislock($conf);
            } else {
                self::$srl = new Redislock($conf);
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
                $conf = Common::loadConfigData(self::$config_head, 'mongodb');
            }
            $this->loadClass('md', $conf);
        }
    }

    /**
     * 静态方式连接MongoDB
     *
     * @param array $conf  连接信息
     * @param bool  $alone 是否重建
     *
     * @return Mongodb
     * @static
     * @since  2018.12.25
     */
    public static function sConnectMongoDB($conf = array(), $alone = false)
    {
        $ret = self::$smd;
        if (!self::$smd) {
            if ($conf == array()) {
                $conf = Common::loadConfigData(self::$config_head, 'mongodb');
            }
            if ($alone) {
                $ret = new Mongodb($conf);
            } else {
                self::$smd = new Mongodb($conf);
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
    
    /**
     * 获取钉钉相关配置信息
     * @param bool $focus
     *
     * @return array|mixed
     * @static
     * @since  2019.05.15
     */
    public static function getConfig(bool $focus = false)
    {
        if (!$focus && self::$config) {
            $ret = self::$config;
        } else {
            Common::loadConfig();
            if ($ret = Common::loadConfigData(self::$config_head, 'dingtalk')) {
                if (isset($ret['OAPI_HOST']) && $ret['OAPI_HOST'] && !defined('OAPI_HOST')) {
                    self::$OAPI_HOST = $ret['OAPI_HOST'];
                }
                if (isset($ret['Redis_Cache']) && $ret['Redis_Cache']) {
                    self::$redis_cache = true;
                    unset($ret['Redis_Cache']);
                }
                if (isset($ret['OAPI_HOST'])) {
                    unset($ret['OAPI_HOST']);
                }
                self::$config = $ret;
            }
        }
        return $ret;
    }

    /**
     * 生成操作URL
     * @param string $opt
     * @param array  $params
     *
     * @return string
     * @static
     * @since  2019.05.15
     */
    public static function buildOperateUrl(string $opt = '', array $params = [])
    {
        $config = self::getConfig();
        $opt = '/' . trim($opt, '/ ');
        $params_ary = [];
        foreach ($params as $k => $v) {
            $key = trim($k);
            if (is_string($v) || is_numeric($v)) {
                if ($v === '') {
                    switch ($key) {
                        case 'access_token':
                            $params_ary[] = "{$key}=" . self::getAccessToken();
                            break;
                        case 'appkey':
                            $params_ary[] = "{$key}=" . $config['APP_KEY'];
                            break;
                        case 'appsecret':
                            // 扫码登录部分键值相同了
                            if (stripos($opt, '/connect/') !== 0) {
                                $params_ary[] = "{$key}=" . $config['APP_SECRET'];
                            } else {
                                $params_ary[] = "{$key}=" . $config['APPSECRET'];
                            }
                            break;
                        case 'appid':
                            $params_ary[] = "{$key}=" . $config['APPID'];
                            break;
                        default:
                            $params_ary[] = "{$key}=";
                            break;
                    }
                } else {
                    $params_ary[] = "{$key}={$v}";
                }
            }
        }
        return self::$OAPI_HOST . $opt . '?' . implode('&', $params_ary);
    }

    /**
     * 获取AccessToken
     * @param bool   $focus
     * @param string $app_key
     * @param string $app_secret
     *
     * @return mixed|string
     * @static
     * @since  2019.05.15
     * @see https://open-doc.dingtalk.com/microapp/serverapi2/eev437
     */
    public static function getAccessToken(bool $focus = false, string $app_key = '', string $app_secret = '')
    {
        $config = self::getConfig();
        $rl = null;
        if (self::$redis_cache) {
            $rl = self::sConnectRedis();
        }
        $params = ['appkey' => '', 'appsecret' => '',];
        if ($app_key && $app_secret) {
            $config = array_merge($config, ['APP_KEY' => $app_key, 'APP_SECRET' => $app_secret,]);
            $params = ['appkey' => $app_key, 'appsecret' => $app_secret,];
        }
        $token = '';
        if (!$focus && self::$redis_cache) {
            if ($data = $rl->redis->hGet(self::$Redis_Token_Key, $config['APP_KEY'])) {
                if ($json = @json_decode($data, true)) {
                    if (is_array($json) && isset($json['token']) && $json['token'] &&
                        isset($json['expire']) && time() <= intval($json['expire'])) {
                        $token = $json['token'];
                    }
                }
            }
        }
        if (!$token) {
            $url = self::buildOperateUrl('gettoken', $params);
            if ($data = Common::getRequest($url)) {
                if ($ary = @json_decode($data, true)) {
                    if (is_array($ary) && isset($ary['errcode'])) {
                        if ($ary['errcode'] == 0 && isset($ary['access_token'])) {
                            $token = $ary['access_token'];
                            if (self::$redis_cache) {
                                $json = ['token' => $token, 'expire' => time() + 7200,];
                                $rl->redis->hSet(self::$Redis_Token_Key, $config['APP_KEY'], json_encode($json));
                            }
                        }
                    }
                }
            }
        }
        return $token;
    }

    /**
     * 免登场景的签名算法
     * @param int    $timestamp
     * @param string $appSecret
     *
     * @return string
     * @static
     * @since  2019.05.16
     * @see https://open-doc.dingtalk.com/microapp/faquestions/hxs5v9
     */
    public static function signature($timestamp = 0, $appSecret = '')
    {
        if ($timestamp == 0) {
            $timestamp = time();
        }
        if ($appSecret == '') {
            $config = self::getConfig();
            if (isset($config['APPSECRET']) && $config['APPSECRET']) {
                $appSecret = $config['APPSECRET'];
            }
        }
        return base64_encode(hash_hmac('sha256', $timestamp, $appSecret, true));
    }

    /**
     * 发起API请求，并返回相应数据
     * @param string $url
     * @param array  $data
     * @param string $type
     * @param string $keys
     * @param bool   $raw
     *
     * @return array|mixed
     * @static
     * @since  2019.05.16
     */
    protected static function doRequest(string $url, $data = [], $type = '', $keys = '', $raw = false)
    {
        $ret = [];
        if ($url) {
            if ($type == '') {
                $type = 'GET';
            }
            $type = trim(strtoupper($type));
            if (in_array($type, ['POST', 'GET',])) {
                $json = [];
                if ($type == 'GET') {
                    $json = @json_decode(Common::getRequest(self::appendParams($data, $url)), true);
                }
                if ($type == 'POST') {
                    $json = @json_decode(Common::postRequest($url, $data), true);
                }
                if (is_array($json) && isset($json['errcode'])) {
                    if ($raw) {
                        $ret = $json;
                    } else {
                        if ($json['errcode'] == 0 && isset($json[$keys])) {
                            $ret = $json[$keys];
                        } else {
                            $ret = $json;
                        }
                    }
                }
            }
        }
        return $ret;
    }

    /**
     * 在链接后附加参数
     * @param array  $ary
     * @param string $url
     *
     * @return string
     * @static
     * @since  2019.05.16
     */
    protected static function appendParams($ary = [], $url = '')
    {
        if ($ary && is_array($ary)) {
            $append = http_build_query($ary);
        } else {
            $append = $ary;
        }
        if ($append) {
            if (strpos($url, '?') === false) {
                $url .= '?' . $append;
            } else {
                $url .= '&' . $append;
            }
        }
        return $url;
    }

    /**
     * 组装User查询可选字段数据
     * @param array $source
     * @param array $options
     *
     * @return array
     * @static
     * @since  2019.05.16
     */
    protected static function buildOptionsArray(array $source = [], array $options = [])
    {
        $params = $source;
        if ($options) {
            //$list = ['lang', 'offset', 'size', 'order',];
            if (isset($options['lang']) && $options['lang']) {
                $params['lang'] = $options['lang'];
            }
            if (isset($options['offset']) && isset($options['size']) &&
                $options['offset'] >= 0 && $options['size'] > 0) {
                if ($options['size'] > 100) {
                    $options['size'] = 100;
                }
                $params['offset'] = $options['offset'];
                $params['size'] = $options['size'];
            }
            if (isset($options['order']) && $options['order']) {
                $options['order'] = strtolower($options['order']);
                if (in_array($options['order'], explode(',', 'entry_asc,entry_desc,modify_asc,modify_desc,custom'))) {
                    $params['order'] = $options['order'];
                }
            }
        }
        return $params;
    }
}
