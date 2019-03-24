<?php
/**
 * Created by PhpStorm.
 * User: jamer
 * Date: 2018/7/7
 * Time: 11:01
 */

namespace ZF;
/**
 * 自定义环境变量配置类
 *
 * @package ZF
 * @author  Jamers <jamersnox@zomew.net>
 * @license https://opensource.org/licenses/GPL-3.0 GPL
 * @since   2018.07.07
 */
class DotEnv
{
    /**
     * ENV文件路径
     * 
     * @var
     */
    private static $_envfile;
    /**
     * 获取的ENV数据数组
     * 
     * @var
     */
    private static $_ENV;

    /**
     * 新建对象时将路径优先处理
     * DotEnv constructor.
     * 
     * @param string $path 
     */
    public function __construct($path='')
    {
        self::getEnvFile($path);
    }

    /**
     * 获取并设置环境变量文件，支持自定义文件
     * 
     * @param string $path 
     * 
     * @return mixed|string
     */
    public static function getEnvFile($path = '')
    {
        if ($path == '') {
            if (self::$_envfile) {
                return self::$_envfile;
            }
            if (defined('ZW_ROOT')) {
                $path = ZW_ROOT;
            } else if (defined('ZF_ROOT')) {
                $path = ZF_ROOT;
            } else {
                $path = __DIR__;
            }
        }
        if (is_dir($path)) {
            $path = rtrim($path.'\\/').DIRECTORY_SEPARATOR;
            if (file_exists($path.'.env')) {
                self::$_envfile = $path.'.env';
            } else if (file_exists($path.'.env.example')) {
                self::$_envfile = $path.'.env.example';
            }
        } else {
            if (file_exists($path)) {
                self::$_envfile = $path;
            }
        }
        return self::$_envfile;
    }

    /**
     * 获取环境变量数据
     * 
     * @param string $key 
     * @param string $path 
     * @param bool   $sensitive 
     * 
     * @return array|string
     */
    public static function getEnvData($key = '',$path = '',$sensitive = true)
    {
        $ret = '';
        if ($key == '') return self::getEnvArray($path);
        if (strpos($key, '.') === false) {
            $group = 'MAIN';
            $key = trim($key);
        } else {
            $l = explode('.', $key);
            $group = trim(array_shift($l));
            $key = trim(implode('.', $l));
        }
        if ($group == '' || $key == '') {
            return '';
        }
        if (!self::$_ENV) {
            self::getEnvArray($path);
        }
        if (isset(self::$_ENV[$group][$key])) {
            $ret = self::$_ENV[$group][$key];
        } else {
            if ($sensitive) {
                $ret = '';
            } else {
                foreach (self::$_ENV as $k => $v) {
                    if (strtolower($group) == strtolower($k)) {
                        foreach ($v as $m => $n) {
                            if (strtolower($key) == strtolower($m)) {
                                return $n;
                            }
                            break;
                        }
                    }
                }
            }
        }
        return $ret;
    }

    /**
     * 将环境变量全部读取到数组中
     * 
     * @param string $path 
     * 
     * @return array
     */
    public static function getEnvArray($path = '')
    {
        $ret = array();
        if (!self::$_envfile) {
            self::getEnvFile($path);
        }
        if (self::$_envfile) {
            $ary = file(self::$_envfile);
            $group = 'MAIN';
            if ($ary) {
                foreach ($ary as $v) {
                    if (preg_match('/^\s*\[\s*([\d\w]+)\s*\]\s*$/i', $v, $m)) {
                        $group = trim($m[1]);
                    }
                    if (preg_match(
                        '/^(?![#;])([\w\d]+)\s*=(.*?)[\r\n]?$/si', $v, $m
                    )
                    ) {
                        if (!isset($ret[$group][trim($m[1])])) {
                            $ret[$group][trim($m[1])] = trim($m[2]);
                        }
                    }
                }
                self::$_ENV = $ret;
            }
        }
        return $ret;
    }

    /**
     * 将环境变量写入系统
     * 
     * @param array $data 
     * 
     * @return void
     */
    public static function setEnvVariable($data = array())
    {
        if (!$data && !self::$_ENV) {
            return;
        }
        if (!$data) {
            $data = self::$_ENV;
        }
        foreach ($data as $k => $v) {
            if ($v && is_array($v)) {
                foreach ($v as $m => $n) {
                    if ($k == 'MAIN') {
                        $name = $m;
                    } else {
                        $name = "{$k}.{$m}";
                    }
                    $value = strval($n);

                    if (function_exists('apache_getenv')
                        && function_exists('apache_setenv')
                        && apache_getenv($name)
                    ) {
                        apache_setenv($name, $value);
                    }
                    if (function_exists('putenv')) {
                        putenv("$name=$value");
                    }
                    $_ENV[$name] = $value;
                    $_SERVER[$name] = $value;
                }
            }
        }
    }

    /**
     * 清除指定环境变量
     * 
     * @param string $name 
     * 
     * @return void
     */
    public static function cleanEnvVariable($name)
    {
        if (function_exists('apache_getenv') 
            && function_exists('apache_setenv') 
            && apache_getenv($name)
        ) {
            apache_setenv($name, '');
        }
        if (function_exists('putenv')) {
            putenv($name);
        }
        unset($_ENV[$name], $_SERVER[$name]);
    }

    /**
     * 读取系统环境变量
     * 
     * @param string $name 
     * 
     * @return array|false|null|string
     */
    public static function getEnvVariable($name)
    {
        switch (true) {
        case array_key_exists($name, $_ENV):
            return $_ENV[$name];
        case array_key_exists($name, $_SERVER):
            return $_SERVER[$name];
        default:
            $value = getenv($name);
            return $value === false ? null : $value;
        }
    }

    /**
     * 读取变量文件并且设置环境变量
     * 
     * @param string $path
     * 
     * @return void
     */
    public static function loadAndSetEnvData($path)
    {
        self::getEnvArray($path);
        self::setEnvVariable();
    }
}