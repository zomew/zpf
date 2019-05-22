<?php
/**
 * Created by PhpStorm.
 * User: Jamers
 * Date: 2019/5/22
 * Time: 13:30
 * File: Cls_Crypto.php
 */
namespace ZF\DingTalk;

/**
 * 阿里钉钉加密相关类
 *
 * @package ZF\DingTalk
 * @author  Jamers <jamersnox@zomew.net>
 * @license https://opensource.org/licenses/GPL-3.0 GPL
 * @since   2019.05.22
 */
class Crypto
{
    private $m_token = '';
    private $m_encodingAesKey = '';
    private $m_suiteKey = '';

    /**
     * 获取SHA1签名
     * @param string ...$param
     *
     * @return string
     * @static
     * @since  2019.05.22
     */
    public static function getSHA1(string ...$param)
    {
        $ret = '';
        if ($param) {
            sort($param, SORT_STRING);
            try {
                $ret = sha1(implode($param));
            } catch (\Exception $e) {
            }
        }
        return $ret;
    }

    /**
     * Function getNonceStr
     * @param int $length
     *
     * @return string
     * @static
     * @since  2019.05.22
     */
    public static function getNonceStr($length = 32)
    {
        $chars = "abcdefghijklmnopqrstuvwxyz0123456789";
        $str ="";
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars)-1), 1);
        }
        return $str;
    }

    /**
     * 实例化
     * @param string $token
     * @param string $encodingAesKey
     * @param string $suiteKey
     */
    public function __construct($token = '', $encodingAesKey = '', $suiteKey = '')
    {
        $this->init($token, $encodingAesKey, $suiteKey);
    }

    /**
     * 初始化相关参数
     * @param string $token
     * @param string $encodingAesKey
     * @param string $suiteKey
     *
     * @return void
     * @since  2019.05.22
     */
    public function init($token = '', $encodingAesKey = '', $suiteKey = '')
    {
        if ($token) {
            $this->m_token = $token;
        }
        if ($encodingAesKey) {
            $this->m_encodingAesKey = $encodingAesKey;
        }
        if ($suiteKey) {
            $this->m_suiteKey = $suiteKey;
        }
    }

    /**
     * 加密消息
     * @param        $plain
     * @param string $nonce
     * @param int    $timestamp
     *
     * @return array
     * @since  2019.05.22
     */
    public function encryptMsg($plain, $nonce = '', $timestamp = 0)
    {
        $ret = [];
        $pc = new Prpcrypt($this->m_encodingAesKey);
        $tmp = $pc->encrypt($plain, $this->m_suiteKey);
        if (isset($tmp[0]) && isset($tmp[1]) && $tmp[0] == ErrorCode::$OK && $tmp[1]) {
            if ($timestamp == 0) {
                $timestamp = time();
            }
            if ($nonce == '') {
                $nonce = self::getNonceStr(16);
            }
            $sha1 = self::getSHA1($this->m_token, $timestamp, $nonce, $tmp[1]);
            if ($sha1) {
                $ret = [
                    'msg_signature' => $sha1,
                    'encrypt' => $tmp[1],
                    'timeStamp' => $timestamp,
                    'nonce' => $nonce,
                ];
            }
        }
        return $ret;
    }

    /**
     * 解密消息
     * @param $signature
     * @param $timestamp
     * @param $nonce
     * @param $encrypt
     *
     * @return mixed|string
     * @since  2019.05.22
     */
    public function decryptMsg($signature, $timestamp, $nonce, $encrypt)
    {
        $ret = '';
        $pc = new Prpcrypt($this->m_encodingAesKey);
        $sha1 = self::getSHA1($this->m_token, $timestamp, $nonce, $encrypt);
        if ($sha1 && $sha1 == $signature) {
            $tmp = $pc->decrypt($encrypt, $this->m_suiteKey);
            if (isset($tmp[0]) && isset($tmp[1]) && $tmp[0] == ErrorCode::$OK) {
                $ret = $tmp[1];
            }
        }
        return $ret;
    }
}

/**
 * PKCS7Encoder class
 *
 * 提供基于PKCS7算法的加解密接口.
 */
class PKCS7Encoder
{
    public static $block_size = 32;

    /**
     * 对需要加密的明文进行填充补位
     * @param $text
     *
     * @return string
     * @since  2019.05.22
     */
    public function encode($text)
    {
        $text_length = strlen($text);
        //计算需要填充的位数
        $amount_to_pad = PKCS7Encoder::$block_size - ($text_length % PKCS7Encoder::$block_size);
        if ($amount_to_pad == 0) {
            $amount_to_pad = PKCS7Encoder::$block_size;
        }
        //获得补位所用的字符
        $pad_chr = chr($amount_to_pad);
        $tmp = "";
        for ($index = 0; $index < $amount_to_pad; $index++) {
            $tmp .= $pad_chr;
        }
        return $text . $tmp;
    }

    /**
     * 对解密后的明文进行补位删除
     * @param $text
     *
     * @return bool|string
     * @since  2019.05.22
     */
    public function decode($text)
    {
        $pad = ord(substr($text, -1));
        if ($pad < 1 || $pad > PKCS7Encoder::$block_size) {
            $pad = 0;
        }
        return substr($text, 0, (strlen($text) - $pad));
    }
}

/**
 * Prpcrypt class
 *
 * 提供接收和推送给公众平台消息的加解密接口.
 */
class Prpcrypt
{
    public $key;

    public function __construct($k)
    {
        $this->key = base64_decode($k . "=");
    }

    /**
     * 对明文进行加密
     * @param        $text
     * @param string $appid
     *
     * @return array
     * @since  2019.05.22
     */
    public function encrypt($text, $appid = '')
    {
        try {
            //获得16位随机字符串，填充到明文之前
            $random = $this->getRandomStr();//"aaaabbbbccccdddd";
            $text = $random . pack("N", strlen($text)) . $text . $appid;
            if (function_exists('mcrypt_module_open')) {
                // 网络字节序
                $size = mcrypt_get_block_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC);
                $module = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_CBC, '');
                $iv = substr($this->key, 0, 16);
                //使用自定义的填充方式对明文进行补位填充
                $pkc_encoder = new PKCS7Encoder;
                $text = $pkc_encoder->encode($text);
                mcrypt_generic_init($module, $this->key, $iv);
                //加密
                $encrypted = mcrypt_generic($module, $text);
                mcrypt_generic_deinit($module);
                mcrypt_module_close($module);
                //print(base64_encode($encrypted));
                //使用BASE64对加密后的字符串进行编码
                return array(ErrorCode::$OK, base64_encode($encrypted));
            } elseif (function_exists('openssl_encrypt')) {
                $iv = substr($this->key, 0, 16);
                $pkc_encoder = new PKCS7Encoder;
                $text = $pkc_encoder->encode($text);
                $encrypted = openssl_encrypt(
                    $text,
                    'AES-256-CBC',
                    substr($this->key, 0, 32),
                    OPENSSL_ZERO_PADDING,
                    $iv
                );
                return array(ErrorCode::$OK, $encrypted);
            } else {
                return array(ErrorCode::$EncryptAESError, null);
            }
        } catch (\Exception $e) {
            return array(ErrorCode::$EncryptAESError, null);
        }
    }

    /**
     * 对密文进行解密
     * @param        $encrypted
     * @param string $appid
     *
     * @return array|string
     * @since  2019.05.22
     */
    public function decrypt($encrypted, $appid = '')
    {
        if (function_exists('mcrypt_module_open') && function_exists('mcrypt_generic_init')) {
            try {
                //使用BASE64对需要解密的字符串进行解码
                $ciphertext_dec = base64_decode($encrypted);
                $module = mcrypt_module_open(
                    MCRYPT_RIJNDAEL_128,
                    '',
                    MCRYPT_MODE_CBC,
                    ''
                );
                $iv = substr($this->key, 0, 16);
                mcrypt_generic_init($module, $this->key, $iv);
                //解密
                $decrypted = mdecrypt_generic($module, $ciphertext_dec);
                mcrypt_generic_deinit($module);
                mcrypt_module_close($module);
            } catch (Exception $e) {
                return array(ErrorCode::$DecryptAESError, null);
            }
        } elseif (function_exists('openssl_decrypt')) {
            try {
                //使用BASE64对需要解密的字符串进行解码
                $iv = substr($this->key, 0, 16);
                $decrypted = openssl_decrypt(
                    $encrypted,
                    'AES-256-CBC',
                    substr($this->key, 0, 32),
                    OPENSSL_ZERO_PADDING,
                    $iv
                );
            } catch (\Exception $e) {
                return array(ErrorCode::$DecryptAESError, null);
            }
        } else {
            return array(ErrorCode::$DecryptAESError, null);
        }

        try {
            //去除补位字符
            $pkc_encoder = new PKCS7Encoder;
            $result = $pkc_encoder->decode($decrypted);
            //去除16位随机字符串,网络字节序和AppId
            if (strlen($result) < 16) {
                return "";
            }
            $content = substr($result, 16, strlen($result));
            $len_list = unpack("N", substr($content, 0, 4));
            $xml_len = $len_list[1];
            $xml_content = substr($content, 4, $xml_len);
            $from_appid = substr($content, $xml_len + 4);
            if (!$appid) {
                $appid = $from_appid;
            }
            //如果传入的appid是空的，则认为是订阅号，使用数据中提取出来的appid
        } catch (\Exception $e) {
            return array(ErrorCode::$IllegalBuffer, null);
        }
        if ($from_appid != $appid) {
            return [ErrorCode::$ValidateAppidError, null];
        }
        //不注释上边两行，避免传入appid是错误的情况
        return array(0, $xml_content, $from_appid); //增加appid，为了解决后面加密回复消息的时候没有appid的订阅号会无法回复
    }


    /**
     * 随机生成16位字符串
     * @return string 生成的字符串
     */
    public function getRandomStr()
    {

        $str = "";
        $str_pol = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz";
        $max = strlen($str_pol) - 1;
        for ($i = 0; $i < 16; $i++) {
            $str .= $str_pol[mt_rand(0, $max)];
        }
        return $str;
    }
}

/**
 * error code
 * 仅用作类内部使用，不用于官方API接口的errCode码
 */
class ErrorCode
{
    public static $OK = 0;
    public static $ValidateSignatureError = 40001;
    public static $ParseXmlError = 40002;
    public static $ComputeSignatureError = 40003;
    public static $IllegalAesKey = 40004;
    public static $ValidateAppidError = 40005;
    public static $EncryptAESError = 40006;
    public static $DecryptAESError = 40007;
    public static $IllegalBuffer = 40008;
    public static $EncodeBase64Error = 40009;
    public static $DecodeBase64Error = 40010;
    public static $GenReturnXmlError = 40011;
    public static $errCode = array(
        '0' => '处理成功',
        '40001' => '校验签名失败',
        '40002' => '解析xml失败',
        '40003' => '计算签名失败',
        '40004' => '不合法的AESKey',
        '40005' => '校验ID失败',
        '40006' => 'AES加密失败',
        '40007' => 'AES解密失败',
        '40008' => '公众平台发送的xml不合法',
        '40009' => 'Base64编码失败',
        '40010' => 'Base64解码失败',
        '40011' => '生成回包xml失败'
    );

    public static function getErrText($err)
    {
        if (isset(self::$errCode[$err])) {
            return self::$errCode[$err];
        } else {
            return false;
        }
    }
}
