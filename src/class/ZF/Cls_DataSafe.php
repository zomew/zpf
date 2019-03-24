<?php
/**
 * Created by PhpStorm.
 * User: jamer
 * Date: 2018/9/22
 * Time: 14:30
 */

namespace ZF;

/**
 * 数据安全模块，含对称及RSA加解密方法封装
 *
 * @package ZF
 * @author  Jamers <jamersnox@zomew.net>
 * @license https://opensource.org/licenses/GPL-3.0 GPL
 * @since   2018.09.22
 */
class DataSafe
{
    /**
     * 默认加密方式
     * 
     * @var string
     */
    private static $_encrypttype = 'AES-256-ECB';
    /**
     * 默认加密密钥
     * 
     * @var string
     */
    private static $_encryptkey = 'password';

    /**
     * OPENSSL安全加密
     * 
     * @param string $plaintext 
     * @param string $key       
     * @param string $type      
     * 
     * @return string
     */
    public static function opensslEncrypt($plaintext, $key = '', $type = '')
    {
        $ret = '';
        if ($key == '') {
            $key = self::$_encryptkey;
        }
        if ($type != '') {
            $type = strtoupper($type);
            if (!in_array($type, openssl_get_cipher_methods())) {
                $type = '';
            }
        }
        if ($type =='') {
            $type = self::$_encrypttype;
        }
        if ($type && $key && is_string($plaintext) && $plaintext) {
            $ivlen = openssl_cipher_iv_length($type);
            $skey = self::_calcFullLengthKey($key, $ivlen);
            $iv = openssl_random_pseudo_bytes($ivlen);
            $ciphertext_raw = openssl_encrypt(
                $plaintext, 
                $type, 
                $skey, 
                $options = OPENSSL_RAW_DATA, 
                $iv
            );
            $hmac = hash_hmac('sha256', $ciphertext_raw, $skey, $as_binary = true);
            $ret = base64_encode($iv . $hmac . $ciphertext_raw);
        }
        return $ret;
    }

    /**
     * OPENSSL安全解密
     * 
     * @param string $ciphertext 
     * @param string $key        
     * @param string $type 
     * 
     * @return string
     */
    public static function opensslDecrypt($ciphertext, $key = '', $type = '')
    {
        $ret = '';
        if ($key == '') {
            $key = self::$_encryptkey;
        }
        if ($type != '') {
            $type = strtoupper($type);
            if (!in_array($type, openssl_get_cipher_methods())) {
                $type = '';
            }
        }
        if ($type == '') {
            $type = self::$_encrypttype;
        }
        if ($type && $key && is_string($ciphertext) && $ciphertext) {
            $ciphertext = str_replace(' ', '+', $ciphertext);
            $c = base64_decode($ciphertext);
            $ivlen = openssl_cipher_iv_length($type);
            $skey = self::_calcFullLengthKey($key, $ivlen);
            $iv = substr($c, 0, $ivlen);
            $hmac = substr($c, $ivlen, $sha2len = 32);
            $ciphertext_raw = substr($c, $ivlen+$sha2len);
            $original_plaintext = openssl_decrypt(
                $ciphertext_raw, 
                $type, 
                $skey, 
                $options = OPENSSL_RAW_DATA, 
                $iv
            );
            $calcmac = hash_hmac(
                'sha256', 
                $ciphertext_raw, 
                $skey, 
                $as_binary = true
            );
            if (hash_equals($hmac, $calcmac)) {
                $ret = $original_plaintext;
            }
        }
        return $ret;
    }

    /**
     * 生成指定长度的安全密钥
     * 
     * @param string $key 
     * @param int    $len 
     * 
     * @return bool|string
     */
    private static function _calcFullLengthKey($key,$len)
    {
        $ret = $key;
        if (is_string($key) && strlen($key)>0 && strlen($key)<$len) {
            while (strlen($ret)<$len) {
                $ret .= $key;
            }
            $ret = substr($ret, 0, $len);
        }
        return $ret;
    }

    /**
     * 对称加密入口，将值序列化后加密存储
     *
     * @param mixed  $val  
     * @param string $key 
     * @param string $type 
     * 
     * @return string
     */
    public static function serializeEncryptVal($val, $key = '', $type = '')
    {
        return self::opensslEncrypt(serialize($val), $key, $type);
    }

    /**
     * 对称解密入口，将密文解密后反序列号得到原值
     *
     * @param string $msg 
     * @param string $key 
     * @param string $type 
     * 
     * @return mixed
     */
    public static function serializeDecryptVal($msg, $key = '', $type = '')
    {
        return unserialize(self::opensslDecrypt($msg, $key, $type));
    }

    /**
     * RSA公钥加密入口，将值序列化后加密
     * 
     * @param mixed $val 
     * @param mixed $pub 
     * 
     * @return bool|string
     */
    public static function serializeRsaPubEncrypt($val, $pub)
    {
        return self::RsaPubEncrypt(serialize($val), $pub);
    }

    /**
     * RSA公钥解决入口，将密文解密后反序列号得到原值
     * 
     * @param string $msg 
     * @param mixed  $pub 
     * 
     * @return mixed
     */
    public static function serializeRsaPubDecrypt($msg, $pub)
    {
        return unserialize(self::rsaPubDecrypt($msg, $pub));
    }

    /**
     * RSA私钥加密入口，将值序列化后加密
     * 
     * @param mixed  $val 
     * @param mixed  $prv 
     * @param string $pass 
     * 
     * @return bool|string
     */
    public static function serializersaPrvEncrypt($val, $prv, $pass = '')
    {
        return self::rsaPrvEncrypt(serialize($val), $prv, $pass);
    }

    /**
     * RSA私钥解密入口，将密文解密后反序列号得到原值
     * 
     * @param string $msg 
     * @param mixed  $prv 
     * @param string $pass 
     * 
     * @return mixed
     */
    public static function serializersaPrvDecrypt($msg, $prv, $pass = '')
    {
        return unserialize(self::rsaPrvDecrypt($msg, $prv, $pass));
    }

    /**
     * 检查openssl模块是否已安装
     *
     * @param bool $exit 
     * 
     * @return bool
     */
    private static function _checkOpenssl($exit = true)
    {
        $ret = extension_loaded('openssl');
        if (!$ret && $exit) {
            try {
                $err = 'PHP Module [openssl] is not installed!';
                throw new \Exception($err);
            }catch(\Exception $e){
                exit(
                    $e->getFile() . ':' . $e->getLine() . '<br><br> "' .
                    $e->getMessage() . '"<br><br><b>Track:</b><br>' .
                    $e->getTraceAsString()
                );
            }
        }
        return $ret;
    }

    /**
     * RSA公钥加密函数
     *
     * @param string $cleartext 明文
     * @param string $pub 
     * 
     * @return bool|string
     */
    public static function rsaPubEncrypt($cleartext = '', $pub = '')
    {
        $ret = false;
        if (self::_checkOpenssl() && $pub) {
            $key = openssl_pkey_get_public($pub);
            if ($key && $cleartext) {
                $ary = str_split($cleartext, self::_getEncryptSize($key));
                $ret = '';
                foreach ($ary as $v) {
                    $s = '';
                    if (openssl_public_encrypt($v, $s, $key) === false) {
                        return false;
                    }
                    $ret .= $s;
                }
                if ($ret) {
                    $ret = base64_encode($ret);
                }
            }
        }
        return $ret;
    }

    /**
     * RSA公钥解密函数
     *
     * @param string $e   密文
     * @param mixed  $pub 公钥
     * 
     * @return bool|string
     */
    public static function rsaPubDecrypt($e = '', $pub = '')
    {
        $ret = false;
        if (self::_checkOpenssl() && $pub) {
            $key = openssl_pkey_get_public($pub);
            if ($key && $e) {
                $ary = str_split(base64_decode($e), self::_getDecryptSize($key));
                $ret = '';
                foreach ($ary as $v) {
                    $s = '';
                    if (openssl_public_decrypt($v, $s, $key) === false) {
                        return false;
                    }
                    $ret .= $s;
                }
            }
        }
        return $ret;
    }

    /**
     * 根据密钥长度计算加密区块长度
     *
     * @param mixed $key 
     * 
     * @return int
     */
    private static function _getEncryptSize($key)
    {
        $ret = 50;
        $ary = openssl_pkey_get_details($key);
        $get_encrypt_size = function ( $bits = 1024 ) { 
            $v = $bits/8-11;
            return intval($v-$v%50);
        };
        if (isset($ary) && is_array($ary) && isset($ary['bits'])) {
            $ret = $get_encrypt_size($ary['bits']);
        }
        return $ret;
    }

    /**
     * 根据密钥长度计算解密区块长度
     *
     * @param mixed $key 
     * 
     * @return int
     */
    private static function _getDecryptSize($key)
    {
        $ret = 64;
        $ary = openssl_pkey_get_details($key);
        $get_decrypt_size = function ( $bits = 1024 ) {
            return intval($bits/8);
        };
        if (isset($ary) && is_array($ary) && isset($ary['bits'])) {
            $ret = $get_decrypt_size($ary['bits']);
        }
        return $ret;
    }

    /**
     * RSA私钥加密函数
     *
     * @param string $cleartext 明文
     * @param mixed  $prv       私钥
     * @param string $prv_pass  私钥密码
     * 
     * @return bool|string
     */
    public static function rsaPrvEncrypt($cleartext = '',$prv = '', $prv_pass = '')
    {
        $ret = false;
        if (self::_checkOpenssl() && $prv) {
            $key = openssl_pkey_get_private($prv, $prv_pass);
            if ($key && $cleartext) {
                $ary = str_split($cleartext, self::_getEncryptSize($key));
                $ret = '';
                foreach ($ary as $v) {
                    $s = '';
                    if (openssl_private_encrypt($v, $s, $key) === false) {
                        return false;
                    }
                    $ret .= $s;
                }
                if ($ret) {
                    $ret = base64_encode($ret);
                }
            }
        }
        return $ret;
    }

    /**
     * RSA私钥解密函数
     *
     * @param string $e        密文
     * @param mixed  $prv      私钥
     * @param string $prv_pass 私钥密码
     * 
     * @return bool|string
     */
    public static function rsaPrvDecrypt($e='', $prv='', $prv_pass='')
    {
        $ret = false;
        if (self::_checkOpenssl() && $prv) {
            $key = openssl_pkey_get_private($prv, $prv_pass);
            if ($key && $e) {
                $ary = str_split(base64_decode($e), self::_getDecryptSize($key));
                $ret = '';
                foreach ($ary as $v) {
                    $s = '';
                    if (openssl_private_decrypt($v, $s, $key) === false) {
                        return false;
                    }
                    $ret .= $s;
                }
            }
        }
        return $ret;
    }

    /**
     * 创建新的RSA密钥对
     *
     * @param int    $bits 
     * @param string $type 
     * @param string $cnf 
     * 
     * @return bool|array
     */
    public static function createNewRsaKey($bits = 2048, $type = 'sha512', $cnf = '')
    {
        $config = array(
            'digest_alg' => $type,
            'private_key_bits' => $bits,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        );
        if ($cnf) {
            if (file_exists($cnf)) {
                $config['config'] = $cnf;
            }
        }
        $ret = false;
        $r = openssl_pkey_new($config);
        if ($r) {
            $pub = $prv = '';
            openssl_pkey_export($r, $prv, null, $config);
            $d = openssl_pkey_get_details($r);
            if ($d && is_array($d) && isset($d['key'])) {
                $pub = $d['key'];
            }
            if ($pub && $prv) {
                $ret = array('prv'=>$prv,'pub'=>$pub,);
            }
        }
        return $ret;
    }

    /**
     * 使用私钥签名，用于较验内容是否被修改过
     *
     * @param string $data 
     * @param string $prv 
     * @param int    $type 
     * @param string $pass 
     * 
     * @return bool|string
     */
    public static function rsaSign($data, $prv = '', 
        $type = OPENSSL_ALGO_SHA256, $pass = ''
    ) {
        $ret = false;
        if ($prv) {
            $key = openssl_pkey_get_private($prv, $pass);
            if ($key) {
                openssl_sign($data, $sign, $key, $type);
                if ($sign) {
                    $ret = base64_encode($sign);
                }
            }
        }
        return $ret;
    }

    /**
     * 使用公钥较验签名是否正确
     *
     * @param string $data 
     * @param string $sign 
     * @param string $pub 
     * @param string $type 
     * 
     * @return bool
     */
    public static function rsaVerify($data, $sign, $pub = '',
        $type = 'sha256WithRSAEncryption'
    ) {
        $ret = false;
        if ($sign && $pub) {
            $pub = openssl_pkey_get_public($pub);
            if ($pub) {
                $r = openssl_verify($data, base64_decode($sign), $pub, $type);
                switch($r) {
                case 1:
                    $ret = true;
                    break;
                case 0:
                    $ret = false;
                    break;
                default:
                    echo openssl_error_string();
                    break;
                }
            }
        }
        return $ret;
    }

    /**
     * 简单XOR算法处理文本信息，可通过自身逆运算，加密数据必须是可打印字符
     *
     * @param string $str      消息本体
     * @param bool   $isbase64 是否是base64格式，如果是base64格式就是解密
     * 
     * @return string
     */
    public static function xorData($str = '', $isbase64 = false)
    {
        $list1 = array(0xF2, 0x24, 0x0A, 0x15, 0x0F, 0xEE, 0x64, 0x09,);
        $list2 = array(5, 2, 8, 9, 3,);
        $ret = '';
        $msg = $str;
        if ($isbase64) {
            $msg = base64_decode($str);
        } else {
            $msg = preg_replace('/[^\x20-\x7E]+/', '', $msg);
        }
        if ($msg && is_string($msg)) {
            $l = str_split($msg);
            foreach ($l as $k => $v) {
                $val = ord($v);
                $x = $list1[$k % count($list1)] ^ $list2[$k % count($list2)];
                $ret .= chr($val ^ $x);
            }
        }
        if (!$isbase64) {
            $ret = base64_encode($ret);
        } else {
            if (!preg_match('/^[\x20-\x7E]+$/', $ret)) {
                $ret = '';
            }
        }
        return $ret;
    }
}