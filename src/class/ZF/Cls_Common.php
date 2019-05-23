<?php
namespace ZF;

/**
 * 通用操作类
 * Class Common
 *
 * @package ZF
 *
 * @author  Jamers <jamersnox@zomew.net>
 * @license https://opensource.org/licenses/GPL-3.0 GPL
 *
 * @since 2018.06.02
 */
class Common
{
    /**
     * SSL双向证书认证连接配置文件
     *
     * @var array
     */
    public static $ssl_config = array(
        'SSLCERTTYPE' => '',
        'SSLCERT' => '',
        'SSLCERTPASSWD' => '',
        'SSLKEYTYPE' => '',
        'SSLKEY' => '',
        'SAFE_UPLOAD' => '',
    );

    /**
     * 使用正则将特殊标记替换成对应值
     *
     * @param string $str 原始字符串
     * @param array  $ary 数据源
     * @param array  $dim 替换标签
     *
     * @return string
     *
     * @since 2017.06.24 直接正则匹配去重，提高替换效率
     */
    public static function specialReplace(
        $str,
        $ary = array(),
        $dim = array('(@','@)')
    ) {
        $l = str_replace('(', '\(', $dim[0]);
        $l = str_replace('{', '\{', $l);
        $r = str_replace(')', '\)', $dim[1]);
        $r = str_replace('}', '\}', $r);
        $p = '/' . $l . '([\w\d\.]+)' . $r . '/i';
        preg_match_all($p, $str, $m);
        if (isset($m[1])) {
            $m = array_unique($m[1]);
        }

        $ret = $str;
        if (is_array($ary) && $ary) {
            foreach ($m as $v) {
                if (strpos($v, '.')) {
                    $value = $ary;
                    $t = explode('.', $v);
                    foreach ($t as $x) {
                        if (isset($value[$x])) {
                            $value = $value[$x];
                        } else {
                            break;
                        }
                    }
                } else {
                    if (isset($ary[$v])) {
                        $value = $ary[$v];
                    } else {
                        $value = '';
                    }
                }
                if (is_array($value)) {
                    $value = '';
                }
                $ret = str_replace($dim[ 0 ] . $v . $dim[ 1 ], $value, $ret);
            }
        }
        $ret = preg_replace($p, '', $ret);
        return (string) $ret;
    }

    /**
     * 发送GET请求
     *
     * @param string $url URL地址
     * @param array  $header
     * @param array  $ssl SSL参数
     *
     * @return string
     * @static
     *
     * @since 2019.03.23
     */
    public static function getRequest($url, $header = [], $ssl = [])
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_HEADER, false);
        if ($header) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        if ($ssl) {
            $tmp = array();
            foreach ($ssl as $k => $v) {
                $tmp[strtoupper(trim($k))] = $v;
            }
            foreach (self::$ssl_config as $k => $v) {
                $key = "CURLOPT_{$k}";
                $value = '';
                if (isset($ssl[$k]) && $ssl[$k]) {
                    $value = $ssl[$k];
                } elseif (trim($v)) {
                    $value = $v;
                }
                if ($value && defined($key)) {
                    curl_setopt($ch, constant($key), $value);
                }
            }
        }
        //$agent = "Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:48.0) Gecko/20100101 Firefox/48.0";
        //curl_setopt ( $ch, CURLOPT_USERAGENT, $agent);
        $res = curl_exec($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);
        self::saveLog('_get_return.txt', $url . "\r\n" . $res);
        if ($info['http_code']!=200) {
            $res = '';
        }
        return $res;
    }

    /**
     * 发送POST请求
     *
     * @param string $url  链接
     * @param mixed  $post 数据
     * @param array  $header
     * @param array  $ssl  SSL证书，用于双向认证
     *
     * @return mixed|string
     * @static
     * @since  2019.03.23
     */
    public static function postRequest($url, $post, $header = [], $ssl = array())
    {
        $headers = array(
            //'Content-Type' => 'application/x-www-form-urlencoded',
            'Content-Type' => 'application/json',
        );
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_HEADER, false);
        if ($header) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        if ($ssl) {
            $tmp = array();
            foreach ($ssl as $k => $v) {
                $tmp[strtoupper(trim($k))] = $v;
            }
            foreach (self::$ssl_config as $k => $v) {
                $key = "CURLOPT_{$k}";
                $value = '';
                if (isset($ssl[$k])) {
                    $value = $ssl[$k];
                } elseif (trim($v)) {
                    $value = $v;
                }
                if ($value && defined($key)) {
                    curl_setopt($ch, constant($key), $value);
                }
            }
        }
        curl_setopt($ch, CURLOPT_POST, 1);
        if ($post) {
            if (!isset($ssl['SAFE_UPLOAD'])) {
                $post = (is_array($post)) ? http_build_query($post) : $post;
            }
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        }
        $res = curl_exec($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);
        //file_put_contents('_post_value.txt',$url."\r\n".var_export($post,true)."\r\n".var_export($info,true));
        self::saveLog(
            '_post_return.txt',
            $url."\r\n".var_export($post, true)."\r\n\r\n".$res."\r\n\r\n"
        );
        if ($info['http_code']!=200) {
            $res = '';
        }
        return $res;
    }

    /**
     * 根据应用状态记录日志信息
     *
     * @param string $file     文件名
     * @param string $str      内容
     * @param bool   $del_old  是否删除旧文件
     * @param bool   $isappend 是否追加内容
     *
     * @return void
     * @static
     * @since  2019.03.23
     */
    public static function saveLog($file, $str, $del_old = true, $isappend = true)
    {
        $dir = 'Logs';
        if (defined('ZF_ROOT')) {
            $path = ZF_ROOT . $dir . DIRECTORY_SEPARATOR;
        } else {
            $path = '';
        }
        $file = $path . $file;
        $path = dirname($file).DIRECTORY_SEPARATOR;
        if (file_exists($file) && $del_old) {
            if (date('Y-m-d', filemtime($file)) != date('Y-m-d')) {
                @unlink($file);
            }
        }

        //只有是调试模式才记录日志信息
        if (!class_exists('\Config') || !isset(\Config::$log) || \Config::$log) {
            if ($path && !file_exists($path)) {
                mkdir($path, 0777, true);
            }
            if ($isappend) {
                $mode = 'a';
            } else {
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
     * @param mixed  $ary 需要获取的内容
     * @param string $def 默认值
     *
     * @return array|mixed
     * @static
     * @since  2019.03.23
     */
    public static function input($ary = '', $def = '')
    {
        $ret = array();
        $val = $_REQUEST;
        if ($ary && is_string($ary)) {
            $ary = explode(',', $ary);
        }
        if ($ary && is_array($ary)) {
            foreach ($ary as $v) {
                if ($v) {
                    $ret[$v] = $def;
                    if (strpos($v, '.') === false) {
                        if (array_key_exists($v, $val)) {
                            $ret[$v] = $val[$v];
                        }
                    } else {
                        $exp = explode('.', $v);
                        $type = strtolower($exp[0]);
                        $var = $exp[count($exp)-1];
                        switch ($type) {
                            case 'get':
                                if (isset($_GET[$var])) {
                                    $ret[$v] = $_GET[$var];
                                }
                                break;
                            case 'post':
                                if (isset($_POST[$var])) {
                                    $ret[$v] = $_POST[$var];
                                }
                                break;
                            default:
                                $ret[$v] = self::input($var);
                                break;
                        }
                    }
                    if (count($ary) == 1) {
                        $ret = $ret[$v];
                    }
                }
            }
        } else {
            $ret = $val;
        }
        return $ret;
    }

    /**
     * 取当前链接完整URL
     *
     * @return string
     * @static
     * @since  2019.03.23
     */
    public static function getFullUrl()
    {
        return $_SERVER['REQUEST_SCHEME'] . '://' .
            $_SERVER['HTTP_HOST'] .
            $_SERVER['REQUEST_URI'];
    }

    /**
     * 替换特定字符串一次
     *
     * @param string $needle   查找内容
     * @param string $replace  替换内容
     * @param string $haystack 原始内容
     *
     * @return mixed
     * @static
     * @since  2019.03.23
     */
    public static function strReplaceOnce($needle, $replace, $haystack)
    {
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
     * @param string $search  查找内容
     * @param string $replace 替换内容
     * @param string $subject 原始内容
     * @param int    $limit   替换次数 -1 为不限制
     *
     * @return null|string|string[]
     * @static
     * @since  2019.03.23
     */
    public static function strReplaceLimit($search, $replace, $subject, $limit = -1)
    {
        // constructing mask(s)...
        if (is_array($search)) {
            foreach ($search as $k => $v) {
                $search[$k] = '`' . preg_quote($search[$k], '`') . '`';
            }
        } else {
            $search = '`' . preg_quote($search, '`') . '`';
        }
        // replacement
        return preg_replace($search, $replace, $subject, $limit);
    }

    /**
     * 输出Json或JsonP类型字符串
     *
     * @param array  $ary      输出数组
     * @param string $callback callback字符串
     * @param bool   $header   是否发送Header
     *
     * @return mixed|\Services_JSON|string
     * @static
     * @since  2019.03.23
     */
    public static function jsonStr(
        $ary = array(),
        $callback = 'callback',
        $header = true
    ) {
        if ($header) {
            header('Content-type: application/json');
            $origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';
            if ($origin && self::checkOrigin($origin)) {
                header("Access-Control-Allow-Origin: {$origin}");
                header('Access-Control-Allow-Credentials: true');
            }
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
     * @param string $header_str 消息头字符串
     *
     * @return bool
     * @static
     * @since  2019.03.23
     */
    public static function headerSetNX($header_str)
    {
        $ret = true;
        $header_str = trim($header_str);
        $p1 = '/^([^:]+):(.*)$/i';
        if (preg_match($p1, $header_str, $m)) {
            foreach (headers_list() as $v) {
                if (preg_match('/\s*' . trim($m[1]) . '\s*:/i', $v, $n)) {
                    $ret = false;
                    break;
                }
            }
            if ($ret) {
                header($header_str);
            }
        } else {
            $ret = false;
        }
        return $ret;
    }

    /**
     * 建立互斥文件锁
     *
     * @param string $file 文件名
     *
     * @return bool|resource
     * @static
     * @since  2019.03.23
     */
    public static function tryToLockFile($file)
    {
        $ret = fopen($file, 'w+');
        if (!flock($ret, LOCK_EX | LOCK_NB)) {
            fclose($ret);
            $ret = false;
        }
        return $ret;
    }

    /**
     * 解除互斥文件锁，并删除文件
     *
     * @param mixed  $fh   文件句柄
     * @param string $file 文件名
     *
     * @return void
     * @static
     * @since  2019.03.23
     */
    public static function unlockFile($fh, $file = '')
    {
        if ($fh) {
            flock($fh, LOCK_UN);
            fclose($fh);
            if ($file && file_exists($file)) {
                @unlink($file);
            }
        }
    }

    /**
     * 从原始数组中取出相应数据
     *
     * @param array $all  所有数据
     * @param array $need 需要的数据
     * @param bool  $keep 是否保留原键值
     *
     * @return array
     * @static
     * @since  2019.03.23
     * @since  增加原始值功能，使用半角冒号为保持原值
     */
    public static function getNeedArray($all, $need = array(), $keep = false)
    {
        $ret = array();
        if ($need && is_string($need)) {
            $need = explode(',', $need);
        }
        if ($need && is_array($need)) {
            if ($all && is_array($all)) {
                foreach ($need as $k => $v) {
                    $key = $k;
                    if (!$keep && is_int($k)) {
                        $key = $v;
                    }
                    if (strpos($v, ':') === 0) {
                        if ($v == ':') {
                            $value = null;
                        } else {
                            $value = ltrim($v, ':');
                        }
                    } else {
                        if ($v) {
                            $value = $all;
                            $tmp = explode('.', $v);
                            foreach ($tmp as $x) {
                                if (isset($value[$x])) {
                                    $value = $value[$x];
                                } else {
                                    $value = '';
                                    break;
                                }
                            }
                        } else {
                            $value = $v;
                        }
                    }
                    $ret[$key] = $value;
                }
                if (count($need) == 1 && isset($key) && $key) {
                    $ret = $ret[$key];
                }
            }
        }
        return $ret;
    }

    /**
     * V4随机GUID生成
     *
     * @return null|string|string[]
     * @static
     * @since  2019.03.23
     */
    public static function generalGUID()
    {
        return preg_replace_callback(
            '/[xy]/i',
            function ($m) {
                $r = rand(0, 15);
                return dechex(strtolower($m[0]) == 'x'?$r:($r&0x3|0x8));
            },
            'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'
        );
    }

    /**
     * 检查是否是GUID
     *
     * @param string $guid GUID
     *
     * @return bool
     * @static
     * @since  2019.03.23
     */
    public static function isGuid($guid = '')
    {
        return preg_match(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i',
            trim($guid)
        ) ? true : false;
    }

    /**
     * 获取PathInfo参数
     *
     * @return string
     * @static
     * @since  2019.03.23
     */
    public static function getPathInfo()
    {
        $ret = '';
        switch (true) {
            case (isset($_SERVER['PATH_INFO']) && $_SERVER['PATH_INFO']):
                return $_SERVER['PATH_INFO'];
                break;
            case (isset($_SERVER['REQUEST_URI'])):
                $ret = $_SERVER['REQUEST_URI'];
                break;
        }
        if (preg_match('/^\/[\w\d\.]+(\/.*)$/i', $ret, $m)) {
            $ret = $m[1];
        } else {
            $ret = '';
        }
        return $ret;
    }

    /**
     * 获取自身URL
     *
     * @param bool $full 为False时取自身链接，不带参数
     *
     * @return string
     * @static
     * @since  2019.03.23
     */
    public static function getSelfUrl($full = true)
    {
        $uri = $_SERVER['REQUEST_URI'];
        if (!$full) {
            $tmp = explode('?', $uri);
            $uri = array_shift($tmp);
        }
        return  $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . $uri;
    }

    /**
     * 隐藏电话号码
     *
     * @param string $name Name
     *
     * @return string
     * @static
     * @since  2019.03.23
     */
    public static function phoneMask($name = '')
    {
        $ret = $name;
        if ($name) {
            if (preg_match('/^(.*?)([\d]{3})([\d]{4})([\d]{4})(.*?)$/', $name, $m)) {
                $ret = $m[1].$m[2].'****'.$m[4].$m[5];
            }
        }
        return $ret;
    }

    /**
     * 隐藏数组数据中的电话号码
     *
     * @param array  $data  数据数组
     * @param string $field 字段名
     *
     * @return array
     * @static
     * @since  2019.02.16
     */
    public static function phoneMaskArray($data = array(), $field = '')
    {
        $ret = $data;
        if ($data && $field) {
            if (is_string($field)) {
                $field = explode(',', $field);
            }
            if ($field && is_array($field)) {
                foreach ($ret as $k => $v) {
                    foreach ($field as $n) {
                        if (isset($v[$n]) && is_string($v[$n])) {
                            $ret[$k][$n] = self::phoneMask($v[$n]);
                        }
                    }
                }
            }
        }
        return $ret;
    }

    /**
     * 创建随机字符串
     *
     * @param int    $len  长度
     * @param string $list 字符串清单
     *
     * @return string
     * @static
     * @since  2018.11.09
     */
    public static function randStr($len = 30, $list = '')
    {
        $ret = '';
        if ($list == '') {
            $list = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ23456789';
        }
        if ($len > 0 && strlen($list) > 0) {
            while (strlen($ret)<$len) {
                $ret .= substr($list, mt_rand(0, strlen($list)-1), 1);
            }
        }
        return $ret;
    }

    /**
     * 检查是否是移动设备
     *
     * @return bool
     * @static
     * @since  2018.11.15
     */
    public static function isMobile()
    {
        // 如果有HTTP_X_WAP_PROFILE则一定是移动设备
        if (isset($_SERVER['HTTP_X_WAP_PROFILE'])) {
            return true;
        }
        // 如果via信息含有wap则一定是移动设备,部分服务商会屏蔽该信息
        if (isset($_SERVER['HTTP_VIA'])) {
            // 找不到为flase,否则为true
            return stristr($_SERVER['HTTP_VIA'], "wap") ? true : false;
        }
        // 脑残法，判断手机发送的客户端标志,兼容性有待提高
        if (isset($_SERVER['HTTP_USER_AGENT'])) {
            $clientkeywords = array(
                'nokia',
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
            if (preg_match(
                "/(" . implode('|', $clientkeywords) . ")/i",
                strtolower($_SERVER['HTTP_USER_AGENT'])
            )
            ) {
                return true;
            }
        }
        // 协议法，因为有可能不准确，放到最后判断
        if (isset($_SERVER['HTTP_ACCEPT'])) {
            // 如果只支持wml并且不支持html那一定是移动设备
            // 如果支持wml和html但是wml在html之前则是移动设备
            if ((strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') !== false)
                && (strpos($_SERVER['HTTP_ACCEPT'], 'text/html') === false || (strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') < strpos($_SERVER['HTTP_ACCEPT'], 'text/html')))
            ) {
                return true;
            }
        }
        return false;
    }

    /**
     * 生成特殊构造GUID
     *
     * @param string $tag tag
     *
     * @return null|string|string[]
     * @static
     * @since  2018.11.17
     */
    public static function tagGuid($tag = 'xxxxxxxx-xxxx-4xxx-yxxx-000000000000')
    {
        return preg_replace_callback(
            '/[xy]/i',
            function ($m) {
                $r = rand(0, 15);
                return dechex(strtolower($m[0]) == 'x' ? $r : ($r & 0x3 | 0x8));
            },
            $tag
        );
    }

    /**
     * 检查GUID是否符合构造规则
     *
     * @param string $guid guid
     * @param string $tag  tag
     *
     * @return bool
     * @static
     * @since  2018.11.17
     */
    public static function checkTagGuid(
        $guid = '',
        $tag = 'xxxxxxxx-xxxx-4xxx-yxxx-000000000000'
    ) {
        $ret = false;
        if ($guid && is_string($guid)) {
            $p = '/^' . preg_replace('/[xy]/i', '[0-9a-f]', $tag) . '$/i';
            $ret = preg_match($p, trim($guid)) ? true : false;
        }
        return $ret;
    }

    /**
     * 数组随机取值算法
     *
     * @param array $arr 数组
     * @param int   $num 数量
     *
     * @return array
     * @static
     * @since  2018.12.12
     */
    public static function arrayRandomAssoc($arr, $num = 1)
    {
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
     *
     * @return bool
     * @static
     * @since  2018.12.13
     */
    public static function isAjax()
    {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
    }

    /**
     * 获取客户端IP地址
     *
     * @return array|false|string
     * @static
     * @since  2019.03.23
     */
    public static function getIP()
    {
        if (isset($_SERVER["HTTP_X_FORWARDED_FOR"])
            && $_SERVER["HTTP_X_FORWARDED_FOR"]
        ) {
            $ip = $_SERVER["HTTP_X_FORWARDED_FOR"];
        } elseif (isset($_SERVER["HTTP_CLIENT_IP"]) && $_SERVER["HTTP_CLIENT_IP"]) {
            $ip = $_SERVER["HTTP_CLIENT_IP"];
        } elseif (isset($_SERVER["HTTP_CF_CONNECTING_IP"])
            && $_SERVER["HTTP_CF_CONNECTING_IP"]
        ) {
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
     *
     * @param string $origin originStr
     *
     * @return bool
     * @static
     * @since  2018.12.14c
     */
    public static function checkOrigin($origin = '')
    {
        $ret = false;
        if ($origin && is_string($origin)) {
            $str = '';
            if ($list = self::loadConfigData('OriginList')) {
                $str = '|((?:[^\/]*?(?:' .implode('|', $list) . ')))';
            }
            if (preg_match(
                '%^\s*https?://((?:192\.168\.|127\.0\.0\.1|localhost)' . $str . ')%i',
                $origin
            )
            ) {
                $ret = true;
            }
        }
        return $ret;
    }

    /**
     * 获取当前域名信息
     *
     * @return string
     * @static
     * @since  2018.12.27
     */
    public static function getHost()
    {
        return (isset($_SERVER['REQUEST_SCHEME'])?$_SERVER['REQUEST_SCHEME']:'http')
            . '://' . $_SERVER['HTTP_HOST'];
    }

    /**
     * UTF编码转数组
     *
     * @param string $string string
     *
     * @return array
     * @static
     * @since  2018.11.30
     */
    public static function mbStringToArray($string)
    {
        $strlen = mb_strlen($string);
        $array = array();
        while ($strlen) {
            $array[] = mb_substr($string, 0, 1, "UTF-8");
            $string = mb_substr($string, 1, $strlen, "UTF-8");
            $strlen = mb_strlen($string);
        }
        return $array;
    }

    /**
     * 获取UTF8编码ORD
     *
     * @param string $u UTF字符串
     *
     * @return float|int
     * @static
     * @since  2018.11.30
     */
    public static function uniord($u)
    {
        $k = mb_convert_encoding($u, 'UCS-2LE', 'UTF-8');
        $k1 = ord(substr($k, 0, 1));
        $k2 = ord(substr($k, 1, 1));
        return $k2 * 256 + $k1;
    }

    /**
     * 获取文本实际长度（中文算2个字节长）
     *
     * @param string $str string
     *
     * @return int
     * @static
     * @since  2018.12.11
     */
    public static function getLength($str)
    {
        $ret = 0;
        foreach (self::mbStringToArray($str) as $v) {
            if (self::uniord($v) < 255) {
                $ret += 1;
            } else {
                $ret += 2;
            }
        }
        return $ret;
    }

    /**
     * 切割字符串，可直接使用UTF8长度（一个中文算一个长度），也可使用实际长度（一个中文算二个长度）
     *
     * @param string $str        string
     * @param int    $len        长度
     * @param bool   $is_utf_len 是否按UTF截取
     *
     * @return bool|string
     * @static
     * @since  2018.12.30
     */
    public static function cutStr($str = '', $len = 20, $is_utf_len = false)
    {
        $ret = '';
        if ($str && is_string($str)) {
            if ($is_utf_len) {
                $l = mb_strlen($str);
            } else {
                $l = 0;
                foreach (self::mbStringToArray($str) as $v) {
                    if (self::uniord($v) < 255) {
                        $l += 1;
                    } else {
                        $l += 2;
                    }
                    if ($l <= $len) {
                        $ret .= $v;
                    }
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
     *
     * @return void
     * @static
     * @since  2019.03.23
     */
    public static function loadConfig()
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
                    include_once $v;
                    break;
                }
            }
        }
    }

    /**
     * 获取配置文件中的配置，$name支持用.获取下级元素
     *
     * @param string $head 标签头
     * @param string $name 标签名
     *
     * @return array|mixed
     * @static
     * @since  2018.12.22
     */
    public static function loadConfigData(string $head = '', string $name = '')
    {
        $ret = array();
        if ($head) {
            self::loadConfig();
            if (class_exists('\Config')) {
                if (isset(\Config::${$head})) {
                    if ($name) {
                        $ret = self::getNeedArray(\Config::${$head}, $name);
                        if (isset($ret[$name])) {
                            $ret = $ret[$name];
                        }
                    } else {
                        $ret = \Config::${$head};
                    }
                } else {
                    $head = strtoupper($head);
                    if (strpos($head, '_CONFIG') === false) {
                        $ret = self::loadConfigData("{$head}_CONFIG", $name);
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
     *
     * @param int $count 总数
     * @param int $page  页数
     * @param int $size  分页数
     * @param int $max   最大页数
     *
     * @return bool
     * @static
     * @since  2019.01.22
     */
    public static function paging($count, &$page, &$size, &$max = 0)
    {
        if ($page < 1) {
            $page = 1;
        }
        if ($size < 1) {
            $size = 1;
        }
        $max = ceil($count / $size);
        if ($page > $max) {
            $page = $max;
        }
        return true;
    }
}
