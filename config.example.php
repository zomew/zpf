<?php
    namespace {
        Class Config
        {
            /**
             * 是否记录日志信息
             * @var bool
             */
            public static $log = true;

            /**
             * redis连接服务信息
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
             * @var array
             */
            public static $OriginList = array(
                'baidu', 'url',
            );

            /**
             * 阿里大于短信接口相关设置
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
        }
    }