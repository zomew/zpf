<?php
/**
 *Created by PhpStorm.
 *User: Jamers
 *Date: 2018/12/15
 *Time: 14:25
 *File: Cls_DySDK.php
 */
namespace ZF;

/**
 *阿里大于短信发送封装类
 *
 *@package ZF
 *@author  Jamers <jamersnox@zomew.net>
 *@license https://opensource.org/licenses/GPL-3.0 GPL
 *@since   2018.12.15
 */
class DySDK
{
    /**
     *配置文件格式
     *
     *@var array
     */
    private static $config = array();

    /**
     *CSRF Token 键值
     *
     *@var string
     */
    private static $Csrf_Token_Key = '_SESSION_CSRF_TOKEN';

    /**
     *配置示例代码
     *
     *@var array
     */
    private static $config_example = array(
        //APPID
        'accessKeyId' => 'your access key id',
        //APPSecret
        'accessKeySecret' => 'your access key secret',
        'SignName' => '短信签名',
        //type 与 短信模板对应表
        'TemplateCode' => array(
            'type' => 'SMS_0000001',
        ),
        //可选: 设置发送短信流水号
        'OutId' => '',
        //可选: 上行短信扩展码, 扩展码字段控制在7位或以下，无特殊需求用户请忽略此字段
        'SmsUpExtendCode' => '',
        //可发送IP白名单清单
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

    private static $keys = 'your sign encrypt keys';
    private static $obv = -1000000300;
    private static $bits = array(16,8,14,2);

    /**
     *生成签名并发起请求
     *
     *@param $accessKeyId     string AccessKeyId (https://ak-console.aliyun.com/)
     *@param $accessKeySecret string AccessKeySecret
     *@param $domain string API接口所在域名
     *@param $params array API具体参数
     *@param $security boolean 使用https
     *@param $method string 使用GET或POST方法请求，VPC仅支持POST
     *
     *@return bool|string 返回API接口调用结果，当发生错误时返回false
     */
    private static function request(
        $accessKeyId,
        $accessKeySecret,
        $domain,
        $params,
        $security = false,
        $method = 'POST'
    ) {
        $apiParams = array_merge(array (
            "SignatureMethod" => "HMAC-SHA1",
            "SignatureNonce" => uniqid(mt_rand(0, 0xffff), true),
            "SignatureVersion" => "1.0",
            "AccessKeyId" => $accessKeyId,
            "Timestamp" => gmdate("Y-m-d\TH:i:s\Z"),
            "Format" => "JSON",
        ), $params);
        ksort($apiParams);
        $sortedQueryStringTmp = "";
        foreach ($apiParams as $key => $value) {
            $sortedQueryStringTmp .= "&" . self::encode($key) . "=" . self::encode($value);
        }
        $stringToSign = "${method}&%2F&" . self::encode(substr($sortedQueryStringTmp, 1));
        $sign = base64_encode(hash_hmac("sha1", $stringToSign, $accessKeySecret . "&", true));
        $signature = self::encode($sign);
        $url = ($security ? 'https' : 'http')."://{$domain}/";
        try {
            $content = self::fetchContent($url, $method, "Signature={$signature}{$sortedQueryStringTmp}");
            return $content;
        } catch (\Exception $e) {
            return false;
        }
    }

    private static function encode($str)
    {
        $res = urlencode($str);
        $res = preg_replace("/\+/", "%20", $res);
        $res = preg_replace("/\*/", "%2A", $res);
        $res = preg_replace("/%7E/", "~", $res);
        return $res;
    }

    private static function fetchContent($url, $method, $body)
    {
        $ch = curl_init();

        if ($method == 'POST') {
            curl_setopt($ch, CURLOPT_POST, 1);//post提交方式
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        } else {
            $url .= '?'.$body;
        }

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "x-sdk-client" => "php/2.0.0"
        ));

        if (substr($url, 0, 5) == 'https') {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        }

        $rtn = curl_exec($ch);

        if ($rtn === false) {
            // 大多由设置等原因引起，一般无法保障后续逻辑正常执行，
            // 所以这里触发的是E_USER_ERROR，会终止脚本执行，无法被try...catch捕获，需要用户排查环境、网络等故障
            trigger_error("[CURL_" . curl_errno($ch) . "]: " . curl_error($ch), E_USER_ERROR);
        }
        curl_close($ch);

        return $rtn;
    }

    /**
     *发送短信接口
     *@since 2018.12.15
     *
     *@param string $mobile
     *@param int $type
     *@param array $body
     *@param bool $security
     *@return array
     */
    public static function sendSms($mobile = '', $type = 0, $body = array(), $security = false)
    {
        self::loadConfig();
        //检查IP白名单   客户端发送请求，不需要检查白名单
        //$ip = self::checkIP(true);
        $ip = Common::GetIP();
        $ret = array('code' => 1, 'msg' => 'params config is not correct',);
        if ($mobile && is_string($mobile) && isset(self::$config['TemplateCode'][$type]) && $body && is_array($body)) {
            $params = array();
            $params["PhoneNumbers"] = $mobile;
            $params["SignName"] = '';
            if (isset(self::$config['SignName']) && is_array(self::$config['SignName']) && self::$config['SignName']) {
                foreach (self::$config['SignName'] as $k => $v) {
                    if (in_array(intval($type), $v)) {
                        $params["SignName"] = $k;
                    }
                }
            } elseif (is_string(self::$config['SignName'])) {
                $params["SignName"] = self::$config['SignName'];
            }
            if (!$params["SignName"]) {
                trigger_error('Sign Config Error!', E_USER_ERROR);
                exit;
            }
            $params["TemplateCode"] = self::$config['TemplateCode'][$type];

            $params['TemplateParam'] = $body;
            if (isset(self::$config['OutId']) && self::$config['OutId']) {
                $params['OutId'] = self::$config['OutId'];
            }
            if (isset(self::$config['SmsUpExtendCode']) && self::$config['SmsUpExtendCode']) {
                $params['SmsUpExtendCode'] = self::$config['SmsUpExtendCode'];
            }

            if (!empty($params["TemplateParam"]) && is_array($params["TemplateParam"])) {
                $params["TemplateParam"] = json_encode($params["TemplateParam"], JSON_UNESCAPED_UNICODE);
            }

            // 此处可能会抛出异常，注意catch
            $content = self::request(
                self::$config['accessKeyId'],
                self::$config['accessKeySecret'],
                "dysmsapi.aliyuncs.com",
                array_merge($params, array(
                    "RegionId" => "cn-hangzhou",
                    "Action" => "SendSms",
                    "Version" => "2017-05-25",
                )),
                $security
            );
            if ($content) {
                Common::_savelog("sms_".date('Ymd').".txt", "ip={$ip},req=".json_encode(\ZF\Common::input()).",resp=" . $content);
                $tmp = @json_decode($content, true);
                if ($tmp && isset($tmp['Code']) && $tmp['Code'] == 'OK') {
                    $ret = array('code' => 0, 'msg' => 'success',);
                } else {
                    $ret = array('code' => 1, 'msg' => 'message send failed', 'detail' => $tmp,);
                    if ($tmp && isset($tmp['Code'])) {
                        if (isset(self::$ErrorMessage[$tmp['Code']])) {
                            $ret['msg'] = self::$ErrorMessage[$tmp['Code']];
                        } else {
                            $ret['msg'] = "错误代码： {$tmp['Code']}";
                        }
                    }
                }
            }
        }
        return $ret;
    }

    /**
     *DySDK constructor.
     *@param array $conf
     */
    public function __construct($conf = array())
    {
        if ($conf && is_array($conf)) {
            self::$config = $conf;
        }
        self::loadConfig();
    }

    /**
     *加载配置文件
     *@since 2018.12.15
     *
     *@param bool $focus
     */
    private static function loadConfig($focus = false)
    {
        if (!class_exists('\Config')) {
            $uname = php_uname('n');
            $config = array(
                ZF_ROOT . "config.{$uname}.php",
                ZF_ROOT.'config.php',
                ZF_ROOT.'config.example.php',
            );
            foreach ($config as $v) {
                if (file_exists($v)) {
                    include_once($v);
                    break;
                }
            }
        }
        if ($focus || !self::$config) {
            if (class_exists("\Config") && isset(\Config::$DYSDK_CONFIG)) {
                self::$config = \Config::$DYSDK_CONFIG;
            }
        }
        if (!self::$config || !isset(self::$config['accessKeyId']) || !isset(self::$config['accessKeySecret'])) {
            trigger_error('DySDK Config is not exist', E_USER_ERROR);
            exit;
        }
    }

    /**
     *检查IP白名单
     *@since 2018.12.15
     *
     *@param bool $exit
     *@return string
     */
    public static function checkIP($exit = true)
    {
        $ret = false;
        self::loadConfig();
        $ip = Common::GetIP();
        if (in_array($ip, self::$config['White_Ip_List'])) {
            $ret = true;
        } else {
            //本地开发环境允许通过
            if (preg_match('/^(?:192\.168\.|127\.0\.0\.1)/', $ip)) {
                $ret = true;
            }
        }
        if ($exit && !$ret) {
            exit(Common::JsonP(array('code' => 1, 'msg' => 'IP address is blocked', 'data' => null,)));
        }
        return $ip;
    }

    /**
     *检查签名
     *@since 2018.12.15
     *@deprecated 2018.12.17 老接口的签名方式弃用，换新的
     *
     *@return bool
     */
    private static function checkSignBak()
    {
        $sign = Common::input('sign');
        $ret = false;
        if ($sign && $sign == md5(date('Myhd'))) {
            $ret = true;
        }
        if (!$ret) {
            exit(Common::JsonP(array('code' => 1, 'msg' => 'Sign check is failed')));
        }
        return $ret;
    }

    /**
     *发送短消息功能入口
     *@since 2018.12.15
     */
    /*public static function sendSms() {
        //检查签名
        self::checkSign();
        $param = @json_decode(Common::input('param'), true);
        header('Content-type: application/json');
        exit(self::_sendSms(Common::input('mobile'), Common::input('type'), $param));
    }*/

    /**
     *生成签名数据
     *@since 2018.12.17
     *
     *@param string $ak
     *@param int $stamp
     *@param string $ip
     *@param bool $ary     是否强制返回数组
     *@return array|string
     */
    public static function generalSign($ak = '', $stamp = 0, $ip = '', $ary = false)
    {
        self::loadConfig();
        if ($ak && $stamp) {
            $str = true;
        } else {
            $str = false;
        }
        if ($ak == '') {
            $ak = md5(Common::RandStr(30));
        }
        $obv = self::$obv;
        if (isset(self::$config['TimeStampObv']) && self::$config['TimeStampObv']) {
            $obv = self::$config['TimeStampObv'];
        }
        if ($stamp == 0) {
            $stamp = time() + $obv;
        }
        if ($ip == '') {
            $ip = Common::GetIP();
        }
        $keys = self::$keys;
        if (isset(self::$config['EncryptKeys']) && self::$config['EncryptKeys']) {
            $keys = self::$config['EncryptKeys'];
        }
        $s = self::hideip(md5("|_{$ak},&{$keys}-{$ip}]{$stamp}"), $ip);
        if (!$str || $ary) {
            $ret = array('a' => $ak, 't' => $stamp, 's' => $s,);
        } else {
            $ret = $s;
        }
        return $ret;
    }

    /**
     *在密钥中隐藏IP
     *@since 2018.12.17
     *
     *@param $key
     *@param string $ip
     *@return string
     */
    public static function hideip($key, $ip = '')
    {
        self::loadConfig();
        $ret = $key;
        if ($ip == '') {
            $ip = Common::GetIP();
        }
        if (preg_match('/(\d+)\.(\d+)\.(\d+)\.(\d+)/', $ip, $m)) {
            $ary = array();
            for ($i = 1; $i <= 4; $i++) {
                $v = intval($m[$i]);
                if ($v>255 || $v < 0) {
                    return $ret;
                }
                $ary[] = sprintf('%02x', $v ^ 0x79);
            }
            $bits = self::$bits;
            if (isset(self::$config['bits']) && self::$config['bits']) {
                $bits = self::$config['bits'];
            }
            foreach ($bits as $k => $v) {
                $start = ($v-1)*2;
                $end = $start + 2;
                $ret = substr($ret, 0, $start) . $ary[$k] . substr($ret, $end, strlen($ret)-$end);
            }
        }
        return $ret;
    }

    /**
     *从Hash中读取出IP
     *@since 2018.12.17
     *
     *@param $hash
     *@return string
     */
    public static function readip($hash)
    {
        $ary = array();
        if (is_string($hash) && strlen($hash)>=32) {
            foreach (self::$bits as $k => $v) {
                $start = ($v-1)*2;
                $end = $start + 2;
                $ary[] = hexdec(substr($hash, $start, $end-$start)) ^ 0x79;
            }
        }
        return implode('.', $ary);
    }

    /**
     *检查签名是否正确
     *@since 2018.12.17
     *
     *@return bool
     */
    public static function checkSign()
    {
        self::loadConfig();
        $sign = Common::input('s');
        $ak = Common::input('a');
        $stamp = Common::input('t');
        $ret = false;
        if ($sign && is_string($sign)) {
            $obv = self::$obv;
            if (isset(self::$config['TimeStampObv']) && self::$config['TimeStampObv']) {
                $obv = self::$config['TimeStampObv'];
            }
            $life = -1;
            if (isset(self::$config['SignCheckLife']) && self::$config['SignCheckLife']) {
                $life = self::$config['SignCheckLife'];
            }
            if ($sign && ($life < 0 || (intval($stamp) - $obv + $life >= time()))
                && $sign == self::generalSign($ak, $stamp)
            ) {
                $ret = true;
            }
        }
        if (!$ret) {
            exit(Common::JsonP(array('code' => 1, 'msg' => 'Sign check is failed')));
        }
        return $ret;
    }

    /**
     *生成短信发送密钥
     *@since 2018.12.17
     *
     *@return string
     */
    public static function buildJsVar()
    {
        $ret = '';
        //TODO: 检查URL判断哪些需要生成短信发送密钥
        if (true) {
            $t = self::generalSign();
            $token = self::buildCsrfToken();
            $ret = "var y_a = '{$t['a']}';var y_t = '{$t['t']}';var y_s = '{$t['s']}';var y_k = '{$token}';";
        }
        return $ret;
    }

    /**
     *生成csrf token 当需要安全操作时调用
     *@since 2018.12.17
     *
     *@param bool $onlyreturn
     *@return string
     */
    public static function buildCsrfToken($onlyreturn = false)
    {
        $token = hash('sha256', php_uname().'_'.strval(mt_rand(3, 10000)).microtime(true));
        if (!$onlyreturn) {
            $SESSION[self::$Csrf_Token_Key] = $token;
        }
        return $token;
    }

    /**
     *检查CSRF token是否正确
     *@param bool $exit
     *@return bool
     */
    public static function checkCsrfToken($exit = false)
    {
        $ret = false;
        if (isset($_SESSION[self::$Csrf_Token_Key])) {
            if (Common::input('token') == $_SESSION[self::$Csrf_Token_Key]) {
                $ret = true;
            }
            unset($_SESSION[self::$Csrf_Token_Key]);
        }
        if (!$ret && $exit) {
            exit('CSRF Token Error!');
        }
        return $ret;
    }

    /**
     *短信发送错误消息
     *@var array
     */
    public static $ErrorMessage = array(
        'isv.MOBILE_NUMBER_ILLEGAL' => '您输入的手机号码有误',
        'isv.MOBILE_COUNT_OVER_LIMIT' => '您的手机号码发送短信已超过限制',
        'isv.TEMPLATE_MISSING_PARAMETERS' => '您提交的参数无效',
        'isv.BLACK_KEY_CONTROL_LIMIT' => '系统已禁止向您的手机发送短信',
    );
}
