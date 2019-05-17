<?php
namespace {
    /**
     * 示例配置文件
     * Class Config
     *
     * @author  Jamers <jamersnox@zomew.net>
     * @license https://opensource.org/licenses/GPL-3.0 GPL
     * @since   2019.03.23
     */
    Class Config
    {
        /**
         * 是否记录日志信息
         *
         * @var bool
         */
        public static $log = true;

        /**
         * Redis连接服务信息
         *
         * @var array
         */
        public static $redis = array(
            'host' => '127.0.0.1',
            'port' => 6379,
            'pass' => '',
            'db' => 0,
        );

        /**
         * 数据库连接信息
         *
         * @var array
         */
        public static $db = array(
            'hostname' => '127.0.0.1',
            'username' => 'username',
            'password' => 'password',
            'database' => 'dbname',
            'port'     => 3306,
            'prefix'   => 'zf_',
            'pconnect' => false,
            'charset'  => 'utf8',
        );

        /**
         * MongoDB相关配置
         *
         * @var array
         */
        public static $mongodb = array(
            'hostname' => '127.0.0.1',
            'username' => 'username',
            'password' => 'password',
            'database' => 'dbname',
            'port'     => '',
        );

        /**
         * 跨域域名关键字列表
         *
         * @var array
         */
        public static $OriginList = array(
            'baidu', 'url',
        );

        /**
         * 阿里大于短信接口相关设置
         *
         * @var array
         */
        public static $DYSDK_CONFIG = array(
            'accessKeyId' => 'your access key id',
            'accessKeySecret' => 'your access key secret',
            'SignName' => array('短信签名' => array('type1', 'type2...',),),
            'TemplateCode' => array(
                'type' => 'SMS_0000001',
            ),
            'OutId' => '',
            'SmsUpExtendCode' => '',
            'White_Ip_List' => array('...',),
            //签名加密密钥
            'EncryptKeys' => 'your sign encrypt keys',
            //时间戳偏移量
            'TimeStampObv' => -1000000300,
            //IP信息隐藏位置
            'bits' => array(16,8,14,2),
            //签名有效时间
            'SignCheckLife' => 300,
        );

        /**
         * 微信相关配置参数
         *
         * @var array
         */
        public static $WECHAT_CONFIG = array(
            //微信支付相关参数
            'wepay' => array(
                'wid' => 0,
                'name' => '公众号名称',
                'appid' => 'your appid',
                'mchid' => 'your mchid',
                'paykey' => 'your paykey',
                'sslcert' => '',
                'sslkey' => '',
            ),
        );

        /**
         * 演示配置数据（详见\self\Cls_PackDemo.php）
         *
         * @var array
         */
        public static $SELF_CONFIG = array(
            'db' => array(
                'hostname' => '127.0.0.1',
                'username' => 'username',
                'password' => 'password',
                'database' => 'dbname',
                'port'     => 3306,
                'prefix'   => 'zf_',
                'pconnect' => false,
                'charset'  => 'utf8',
            ),
            'redis' => array(
                'host' => '127.0.0.1',
                'port' => 6379,
                'pass' => '',
                'db' => 0,
            ),
            'mongodb' => array(
                'hostname' => '127.0.0.1',
                'username' => 'username',
                'password' => 'password',
                'database' => 'dbname',
                'port'     => '',
            ),
        );

        /**
         * @var array 阿里钉钉相关配置项
         */
        public static $DINGTALK_CONFIG = array(
            'db' => array(
                'hostname' => '127.0.0.1',
                'username' => 'username',
                'password' => 'password',
                'database' => 'dbname',
                'port'     => 3306,
                'prefix'   => 'zf_',
                'pconnect' => false,
                'charset'  => 'utf8',
            ),
            'redis' => array(
                'host' => '127.0.0.1',
                'port' => 6379,
                'pass' => '',
                'db' => 0,
            ),
            'mongodb' => array(
                'hostname' => '127.0.0.1',
                'username' => 'username',
                'password' => 'password',
                'database' => 'dbname',
                'port'     => '',
            ),
            //阿里钉钉应用配置
            'dingtalk' => [
                //'OAPI_HOST' => 'https://oapi.dingtalk.com',
                //应用AgentID
                'AGENTID' => '',
                'APP_KEY'   => '',
                'APP_SECRET'=> '',
                //应用管理后台登录参数： https://open-doc.dingtalk.com/microapp/serverapi2/xswxhg
                'CORP_ID' => '',
                'CORP_SECRET' => '',
                //移动登录参数： https://open-doc.dingtalk.com/microapp/serverapi2/kymkv6
                'APPID' => '',
                'APPSECRET' => '',
                //AccessToken是否使用Redis缓存
                'Redis_Cache' => false,
                //是否统一采用钉钉原始请求（使用$raw参数的接口会被影响）
                'FORCE_GET_RAW_DATA' => false,
            ],
        );
    }
}
