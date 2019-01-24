<?php
namespace ZF;

/**
 * 通用操作类
 * @author Jamers <jamersnox@zomew.net>
 * @license https://opensource.org/licenses/GPL-3.0 GPL
 * @since 2018.06.02
 *
 * Class Common
 * @package ZF
 */
Class Common {
    /**
     * SSL双向证书认证连接配置文件
     * @var array
     */
    public static $ssl_config = array(
        'SSLCERTTYPE' => '',
        'SSLCERT' => '',
        'SSLCERTPASSWD' => '',
        'SSLKEYTYPE' => '',
        'SSLKEY' => '',
    );

    /**
     * 使用正则将特殊标记替换成对应值
     * @since 2017.06.24 直接正则匹配去重，提高替换效率
     *
     * @param mixed $str
     * @param mixed $ary
     * @param mixed $dim
     * @return string
     */
    public static function SpecialReplace($str, $ary=array(),$dim = array('(@','@)')) {
        //file_put_contents('specary.txt',var_export($ary,true));
        $l = str_replace('(','\(',$dim[0]);
        $l = str_replace('{','\{',$l);
        $r = str_replace(')','\)',$dim[1]);
        $r = str_replace('}','\}',$r);
        $p = '/'.$l.'([\w\d\.]+)'.$r.'/i';
        //echo $str;
        //echo $p;
        preg_match_all($p,$str,$m);
        if (isset($m[1])) $m = array_unique($m[1]);

        $ret = $str;
        if (is_array($ary) && $ary) {
            //foreach ($ary as $k => $v) {
            foreach ($m as $v) {
                if (strpos($v,'.')) {
                    $value = $ary;
                    $t = explode('.',$v);
                    foreach ($t as $x) {
                        if (isset($value[$x])) {
                            $value = $value[$x];
                        }else{
                            break;
                        }
                    }
                }else{
                    if (isset($ary[$v])) {
                        $value = $ary[$v];
                    }else{
                        $value = '';
                    }
                }
                if (is_array($value)) $value = '';
                $ret = str_replace($dim[0].$v.$dim[1],$value,$ret);
            }
        }
        //$ret = str_replace($dim[0].'SESSIONID'.$dim[1],$this->vars['SESSIONID'],$ret);
        $ret = preg_replace($p,'',$ret);
        return $ret;
    }

    /**
     * 发送GET请求
     *
     * @param $url
     * @param array $ssl    用于SSL双向认证配置
     * @return mixed|string
     */
    public static function _getRequest($url, $ssl = array()) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,FALSE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        if ($ssl) {
            $tmp = array();
            foreach($ssl as $k => $v) {
                $tmp[strtoupper(trim($k))] = $v;
            }
            foreach(self::$ssl_config as $k => $v) {
                $key = "CURLOPT_{$k}";
                $value = '';
                if (isset($ssl[$k]) && $ssl[$k]) {
                    $value = $ssl[$k];
                }else if (trim($v)) {
                    $value = $v;
                }
                if ($value) {
                    curl_setopt($ch,$key,$value);
                }
            }
        }
        //$agent = "Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:48.0) Gecko/20100101 Firefox/48.0";
        //curl_setopt ( $ch, CURLOPT_USERAGENT, $agent);
        $res = curl_exec($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);
        self::_savelog('_get_return.txt',$url . "\r\n" . $res);
        if ($info['http_code']!=200) {
            $res = '';
        }
        return $res;
    }

    /**
     * 发送POST请求
     *
     * @param $url
     * @param $post
     * @param array $ssl    用于双向证书认证
     * @return mixed|string
     */
    public static function _postRequest($url, $post, $ssl = array()) {
        $headers = array(
            //'Content-Type' => 'application/x-www-form-urlencoded',
            'Content-Type' => 'application/json',
        );
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,FALSE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        if ($ssl) {
            $tmp = array();
            foreach($ssl as $k => $v) {
                $tmp[strtoupper(trim($k))] = $v;
            }
            foreach(self::$ssl_config as $k => $v) {
                $key = "CURLOPT_{$k}";
                $value = '';
                if (isset($ssl[$k]) && $ssl[$k]) {
                    $value = $ssl[$k];
                }else if (trim($v)) {
                    $value = $v;
                }
                if ($value) {
                    curl_setopt($ch,$key,$value);
                }
            }
        }
        if ($post) {
            curl_setopt($ch, CURLOPT_POST, 1);
            $post = (is_array($post)) ? http_build_query($post) : $post;
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $res = curl_exec($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);
        //file_put_contents('_post_value.txt',$url."\r\n".var_export($post,true)."\r\n".var_export($info,true));
        self::_savelog('_post_return.txt',$url."\r\n".var_export($post,true)."\r\n\r\n".$res."\r\n\r\n");
        //file_put_contents('_post_return.txt',$res);
        if ($info['http_code']!=200) {
            $res = '';
        }
        return $res;
    }

    /**
     * 根据应用状态记录日志信息
     * @param $file
     * @param $str
     * @param  $del_old
     * @param $isappend
     */
    public static function _savelog($file,$str,$del_old = true,$isappend = true) {
        $dir = 'Logs';
        if (defined('ZF_ROOT')) {
            $path = ZF_ROOT . $dir . DIRECTORY_SEPARATOR;
        }else{
            $path = '';
        }
        $file = $path . $file;
        $path = dirname($file).DIRECTORY_SEPARATOR;
        if (file_exists($file) && $del_old) {
            if (date('Y-m-d',filemtime($file))!=date('Y-m-d')) @unlink($file);
        }

        //只有是调试模式才记录日志信息
        if (!class_exists('\Config') || !isset(\Config::$log) || \Config::$log) {
            if ($path && !file_exists($path)) {
                mkdir($path, 0777, true);
            }
            if ($isappend) {
                $mode = 'a';
            }else{
                $mode = 'w';
            }

            $f = fopen($file, $mode);
            $w = date('Y-m-d H:i:s') . "\r\n" . $str . "\r\n\r\n";
            fwrite($f, $w);
            fclose($f);
        }
    }

    /**
     * 取传递过来的参数，可以用逗号分隔多个值
     *
     * @param string $ary
     * @param string $def
     * @return array|mixed
     */
    public static function input($ary = '',$def='') {
        $ret = array();
        $val = $_REQUEST;
        if ($ary && is_string($ary)) $ary = explode(',',$ary);
        if ($ary && is_array($ary)) {
            foreach ($ary as $v) {
                if ($v) {
                    $ret[$v] = $def;
                    if (strpos($v,'.')===false) {
                        if (array_key_exists($v, $val)) {
                            $ret[$v] = $val[$v];
                        }
                    }else{
                        $exp = explode('.',$v);
                        $type = strtolower($exp[0]);
                        $var = $exp[count($exp)-1];
                        switch ($type) {
                            case 'get':
                                if (isset($_GET[$var])) $ret[$v] = $_GET[$var];
                                break;
                            case 'post':
                                if (isset($_POST[$var])) $ret[$v] = $_POST[$var];
                                break;
                            default:
                                $ret[$v] = self::input($var);
                                break;
                        }
                    }
                    if (count($ary) == 1) return $ret[$v];
                }
            }
        }else{
            $ret = $val;
        }
        return $ret;
    }

    /**
     * 取当前链接完整URL
     *
     * @return string
     */
    public static function GetFullUrl() {
        return ((isset($_SERVER['HTTPS'])&&$_SERVER['HTTPS']!='off')?'https://':'http://').$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
    }

    /**
     * 替换特定字符串一次
     *
     * @param $needle
     * @param $replace
     * @param $haystack
     * @return mixed
     */
    public static function str_replace_once($needle, $replace, $haystack) {
        // Looks for the first occurence of $needle in $haystack
        // and replaces it with $replace.
        $pos = strpos($haystack, $needle);
        if ($pos === false) {
            // Nothing found
            return $haystack;
        }
        return substr_replace($haystack, $replace, $pos, strlen($needle));
    }

    /**
     * 正则替换限定替换次数
     *
     * @param $search
     * @param $replace
     * @param $subject
     * @param int $limit
     * @return null|string|string[]
     */
    public static function str_replace_limit($search, $replace, $subject, $limit=-1) {
        // constructing mask(s)...
        if (is_array($search)) {
            foreach ($search as $k=>$v) {
                $search[$k] = '`' . preg_quote($search[$k],'`') . '`';
            }
        }else{
            $search = '`' . preg_quote($search,'`') . '`';
        }
        // replacement
        return preg_replace($search, $replace, $subject, $limit);
    }

    /**
     * 输出Json或JsonP类型字符串
     *
     * @param array $ary
     * @param string $callback
     * @return string
     */
    public static function JsonP($ary = array(),$callback = 'callback') {
        //self::Header_SetNX('Content-type: application/json');
        header('Content-type: application/json');
        $origin = isset($_SERVER['HTTP_ORIGIN'])? $_SERVER['HTTP_ORIGIN'] : '';
        if ($origin && self::CheckOrigin($origin)) {
            header("Access-Control-Allow-Origin: {$origin}");
            header('Access-Control-Allow-Credentials: true');
        }
        $ret = json_encode($ary);
        $c = self::input("get.{$callback}");
        if ($c) {
            $ret = "{$c}({$ret});";
        }
        return $ret;
    }

    /**
     * 检查是否发送相应Header，如果没有发送，有就不发送
     *
     * @param $header_str
     * @return bool
     */
    public static function Header_SetNX($header_str) {
        $ret = true;
        $header_str = trim($header_str);
        $p1 = '/^([^:]+):(.*)$/i';
        if (preg_match($p1,$header_str,$m)) {
            foreach(headers_list() as $v) {
                //echo '/\s*'.trim($m[1]).'\s*:/i<br>'.$v.'<br>';
                if (preg_match('/\s*'.trim($m[1]).'\s*:/i',$v,$n)) {
                    //var_dump($n);
                    $ret = false;
                    break;
                }
            }
            if ($ret) {
                header($header_str);
            }
        }else{
            $ret = false;
        }
        return $ret;
    }

    /**
     * 用于建立互斥文件锁
     * @param $file
     * @return bool|resource
     */
    public static function TrytoLockFile($file) {
        $ret = fopen($file,'w+');
        if (!flock($ret,LOCK_EX | LOCK_NB)){
            fclose($ret);
            $ret = false;
        }
        return $ret;
    }

    /**
     * 解除互斥文件锁，并删除文件
     * @param $fh
     * @param string $file
     */
    public static function UnlockFile($fh,$file = '') {
        if ($fh) {
            flock($fh, LOCK_UN);
            fclose($fh);
            if ($file && file_exists($file)) @unlink($file);
        }
    }

    /**
     * 从原始数组中取出相应数据
     * @since 增加原始值功能，使用半角冒号为保持原值
     *
     * @param array   $all
     * @param array|string $need
     * @param bool  $keep
     * @return array
     */
    public static function getNeedArray($all,$need=array(),$keep = false) {
        $ret = array();
        if ($need && is_string($need)) $need = explode(',',$need);
        if ($need && is_array($need)) {
            if ($all && is_array($all)) {
                foreach ($need as $k => $v) {
                    $key = $k;
                    if (!$keep && is_int($k)) {
                        $key = $v;
                    }
                    if (strpos($v,':')===0) {
                        if ($v == ':') {
                            $value = null;
                        }else {
                            $value = ltrim($v, ':');
                        }
                    }else{
                        if ($v) {
                            $value = $all;
                            $tmp = explode('.',$v);
                            foreach ($tmp as $x) {
                                if (isset($value[$x])) {
                                    $value = $value[$x];
                                }else{
                                    $value = '';
                                    break;
                                }
                            }
                        }else{
                            $value = $v;
                        }
                    }
                    $ret[$key] = $value;
                }
            }
        }
        return $ret;
    }

    /**
     * V4随机GUID生成
     * @return null|string|string[]
     */
    public static function GUID() {
        return preg_replace_callback('/[xy]/i',function($m){
            $r = rand(0,15);
            return dechex(strtolower($m[0]) == 'x'?$r:($r&0x3|0x8));
            },'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx');
    }

    /**
     * 检查是否是GUID
     * @since 2018.11.09
     *
     * @param string $guid
     * @return false
     */
    public static function isGuid($guid = '') {
        return preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i',trim($guid))?true:false;
    }
    
    /**
     * 获取PathInfo参数
     *
     * @return string
     */
    public static function GetPathInfo() {
        $ret = '';
        switch(true) {
            case (isset($_SERVER['PATH_INFO']) && $_SERVER['PATH_INFO']):
                return $_SERVER['PATH_INFO'];
                break;
            case (isset($_SERVER['REQUEST_URI'])):
                $ret = $_SERVER['REQUEST_URI'];
                break;
        }
        if (preg_match('/^\/[\w\d\.]+(\/.*)$/i',$ret,$m)) {
            $ret = $m[1];
        }else{
            $ret = '';
        }
        return $ret;
    }

    /**
     * 获取自身URL
     * @param bool $full    为False时取自身链接，不带参数
     * @return string
     */
    public static function GetSelfUrl($full = true) {
        $uri = $_SERVER['REQUEST_URI'];
        if (!$full) {
            $tmp = explode('?', $uri);
            $uri = array_shift($tmp);
        }
        return  ((isset($_SERVER["HTTP_X_CLIENT_SCHEME"]) && $_SERVER["HTTP_X_CLIENT_SCHEME"]) ? $_SERVER["HTTP_X_CLIENT_SCHEME"] : ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? 'https' : 'http')) . '://' . $_SERVER['HTTP_HOST'] . $uri;
    }

    /**
     * 隐藏电话号码
     *
     * @param string $name
     * @return string
     */
    public static function PhoneMask($name = '') {
        $ret = $name;
        if ($name) {
            if (preg_match('/^(.*?)([\d]{3})([\d]{4})([\d]{4})(.*?)$/',$name,$m)) {
                $ret = $m[1].$m[2].'****'.$m[4].$m[5];
            }
        }
        return $ret;
    }

    /**
     * 创建随机字符串
     * @since 2018.11.09
     *
     * @param int $len
     * @param string $list
     * @return string
     */
    public static function RandStr($len = 30, $list = '') {
        $ret = '';
        if ($list == '') $list = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ23456789';
        if ($len > 0 && strlen($list) > 0) {
            while(strlen($ret)<$len) {
                $ret .= substr($list, mt_rand(0,strlen($list)-1), 1);
            }
        }
        return $ret;
    }

    /**
     * 检查是否是移动设备
     * @since 2018.11.15
     *
     * @return bool
     */
    public static function isMobile() {
        // 如果有HTTP_X_WAP_PROFILE则一定是移动设备
        if (isset ($_SERVER['HTTP_X_WAP_PROFILE'])) {
            return true;
        }
        // 如果via信息含有wap则一定是移动设备,部分服务商会屏蔽该信息
        if (isset ($_SERVER['HTTP_VIA'])) {
            // 找不到为flase,否则为true
            return stristr($_SERVER['HTTP_VIA'], "wap") ? true : false;
        }
        // 脑残法，判断手机发送的客户端标志,兼容性有待提高
        if (isset ($_SERVER['HTTP_USER_AGENT'])) {
            $clientkeywords = array('nokia',
                'sony',
                'ericsson',
                'mot',
                'samsung',
                'htc',
                'sgh',
                'lg',
                'sharp',
                'sie-',
                'philips',
                'panasonic',
                'alcatel',
                'lenovo',
                'iphone',
                'ipod',
                'blackberry',
                'meizu',
                'android',
                'netfront',
                'symbian',
                'ucweb',
                'windowsce',
                'palm',
                'operamini',
                'operamobi',
                'openwave',
                'nexusone',
                'cldc',
                'midp',
                'wap',
                'mobile'
            );
            // 从HTTP_USER_AGENT中查找手机浏览器的关键字
            if (preg_match("/(" . implode('|', $clientkeywords) . ")/i", strtolower($_SERVER['HTTP_USER_AGENT']))) {
                return true;
            }
        }
        // 协议法，因为有可能不准确，放到最后判断
        if (isset ($_SERVER['HTTP_ACCEPT'])) {
            // 如果只支持wml并且不支持html那一定是移动设备
            // 如果支持wml和html但是wml在html之前则是移动设备
            if ((strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') !== false) && (strpos($_SERVER['HTTP_ACCEPT'], 'text/html') === false || (strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') < strpos($_SERVER['HTTP_ACCEPT'], 'text/html')))) {
                return true;
            }
        }
        return false;
    }

    /**
     * 生成特殊构造GUID
     * @since 2018.11.17
     *
     * @param string $tag
     * @return null|string|string[]
     */
    public static function tagGuid($tag = 'xxxxxxxx-xxxx-4xxx-yxxx-000000000000') {
        return preg_replace_callback('/[xy]/i',function($m){
            $r = rand(0,15);
            return dechex(strtolower($m[0]) == 'x'?$r:($r&0x3|0x8));
        },$tag);
    }

    /**
     * 检查GUID是否符合构造规则
     * @since 2018.11.17
     *
     * @param string $guid
     * @param string $tag
     * @return bool|false|int
     */
    public static function checkTagGuid($guid = '', $tag = 'xxxxxxxx-xxxx-4xxx-yxxx-000000000000') {
        $ret = false;
        if ($guid && is_string($guid)) {
            $p = '/^'.preg_replace('/[xy]/i','[0-9a-f]',$tag).'$/i';
            $ret = preg_match($p, trim($guid)) ? true : false;
        }
        return $ret;
    }

    /**
     * 数组随机取值算法
     * @since 2018.12.12
     *
     * @param $arr
     * @param int $num
     * @return array
     */
    public static function array_random_assoc($arr, $num = 1) {
        $keys = array_keys($arr);
        shuffle($keys);

        $r = array();
        for ($i = 0; $i < $num; $i++) {
            $r[$keys[$i]] = $arr[$keys[$i]];
        }
        return $r;
    }

    /**
     * 根据HTTP头判断是否是Ajax请求
     * @since 2018.12.13
     *
     * @return bool
     */
    public static function isAjax() {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
    }

    /**
     * 获取客户端IP地址
     * @return array|false|string
     */
    public static function GetIP() {
        if (isset($_SERVER["HTTP_X_FORWARDED_FOR"]) && $_SERVER["HTTP_X_FORWARDED_FOR"]) {
            $ip = $_SERVER["HTTP_X_FORWARDED_FOR"];
        } elseif (isset($_SERVER["HTTP_CLIENT_IP"]) && $_SERVER["HTTP_CLIENT_IP"]) {
            $ip = $_SERVER["HTTP_CLIENT_IP"];
        } elseif (isset($_SERVER["HTTP_CF_CONNECTING_IP"]) && $_SERVER["HTTP_CF_CONNECTING_IP"]) {
            $ip = $_SERVER["HTTP_CF_CONNECTING_IP"];
        } elseif (isset($_SERVER["REMOTE_ADDR"]) && $_SERVER["REMOTE_ADDR"]) {
            $ip = $_SERVER["REMOTE_ADDR"];
        } elseif (getenv("HTTP_X_FORWARDED_FOR")) {
            $ip = getenv("HTTP_X_FORWARDED_FOR");
        } elseif (getenv("HTTP_CLIENT_IP")) {
            $ip = getenv("HTTP_CLIENT_IP");
        } elseif (getenv("HTTP_CF_CONNECTING_IP")) {
            $ip = getenv("HTTP_CF_CONNECTING_IP");
        } elseif (getenv("REMOTE_ADDR")) {
            $ip = getenv("REMOTE_ADDR");
        } else {
            $ip = "Unknown";
        }
        return $ip;
    }

    /**
     * 检查是否符合跨域规则
     * @since 2018.12.14
     *
     * @param string $origin
     * @return bool
     */
    public static function CheckOrigin($origin = '') {
        $ret = false;
        if ($origin && is_string($origin)) {
            $str = '';
            if ($list = self::LoadConfigData('OriginList')) $str = '|((?:[^\/]*?(?:' .implode('|', $list) . ')))';
            if (preg_match('%^\s*https?://((?:192\.168\.|127\.0\.0\.1|localhost)' . $str .')%i', $origin)) $ret = true;
        }
        return $ret;
    }

    /**
     * 获取当前域名信息
     * @since 2018.12.27
     *
     * @return string
     */
    public static function GetHost() {
        return (isset($_SERVER['REQUEST_SCHEME']) ? $_SERVER['REQUEST_SCHEME'] : 'http') . '://' . $_SERVER['HTTP_HOST'];
    }


    /**
     * UTF编码转数组
     * @since 2018.11.30
     *
     * @param $string
     * @return array
     */
    public static function mbStringToArray($string) {
        $strlen = mb_strlen($string);
        while ($strlen) {
            $array[] = mb_substr($string,0,1,"UTF-8");
            $string = mb_substr($string,1,$strlen,"UTF-8");
            $strlen = mb_strlen($string);
        }
        return $array;
    }

    /**
     * 获取UTF8编码ORD
     * @since 2018.11.30
     *
     * @param $u
     * @return float|int
     */
    public static function uniord($u) {
        $k = mb_convert_encoding($u, 'UCS-2LE', 'UTF-8');
        $k1 = ord(substr($k, 0, 1));
        $k2 = ord(substr($k, 1, 1));
        return $k2 * 256 + $k1;
    }

    /**
     * 获取文本实际长度（中文算2个字节长）
     * @since 2018.12.11
     *
     * @param $str
     * @return int
     */
    public static function GetLength($str) {
        $ret = 0;
        foreach(self::mbStringToArray($str) as $v) {
            if (self::uniord($v) < 255) {
                $ret += 1;
            }else{
                $ret += 2;
            }
        }
        return $ret;
    }

    /**
     * 切割字符串，可直接使用UTF8长度（一个中文算一个长度），也可使用实际长度（一个中文算二个长度）
     * @since 2018.12.30
     *
     * @param string $str
     * @param int $len
     * @param bool $is_utf_len  是否按UTF截取
     * @return string
     */
    public static function CutStr($str = '', $len = 20, $is_utf_len = false) {
        $ret = '';
        if ($str && is_string($str)) {
            if ($is_utf_len) {
                $l = mb_strlen($str);
            }else{
                $l = 0;
                foreach(self::mbStringToArray($str) as $v) {
                    if (self::uniord($v) < 255) {
                        $l += 1;
                    }else{
                        $l += 2;
                    }
                    if ($l <= $len) $ret .= $v;
                }
            }
            if ($l > $len) {
                if ($is_utf_len) {
                    $ret = mb_substr($str, 0, $len);
                }
                $ret .= '...';
            } else {
                $ret = $str;
            }
        }
        return $ret;
    }

    /**
     * 加载配置文件
     */
    public static function LoadConfig() {
        if (!class_exists('\Config')) {
            $uname = php_uname('n');
            $config = array(
                ZF_ROOT . "config.{$uname}.php",
                ZF_ROOT.'config.php',
                ZF_ROOT.'config.example.php',
            );
            foreach($config as $v) {
                if (file_exists($v)) {
                    include_once($v);
                    break;
                }
            }
        }
    }

    /**
     * 获取配置文件中的配置，$name支持用.获取下级元素
     * @since 2018.12.22
     *
     * @param string $head
     * @param string $name
     * @return array
     */
    public static function LoadConfigData(string $head = '', string $name = '') {
        $ret = array();
        if ($head) {
            self::LoadConfig();
            if (class_exists('\Config')) {
                if (isset(\Config::${$head})) {
                    if ($name) {
                        //if (isset(\Config::${$head}[$name]) && \Config::${$head}[$name]) $ret = \Config::${$head}[$name];
                        $ret = self::getNeedArray(\Config::${$head}, $name);
                        if (isset($ret[$name])) $ret = $ret[$name];
                    } else {
                        $ret = \Config::${$head};
                    }
                } else {
                    $head = strtoupper($head);
                    if (strpos($head, '_CONFIG') === false) {
                        $ret = self::LoadConfigData("{$head}_CONFIG", $name);
                    } else {
                        $ret = array();
                    }
                }
            }
        }
        return $ret;
    }

    /**
     * 分页参数处理
     * @since 2019.01.22
     *
     * @param $count
     * @param $page
     * @param $size
     * @param int $max
     * @return bool
     */
    public static function Paging($count, &$page, &$size, &$max = 0) {
        if ($page < 1) $page = 1;
        if ($size < 1) $size = 1;
        $max = ceil($count / $size);
        if ($page > $max) $page = $max;
        return true;
    }
}