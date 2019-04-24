<?php
@session_start();

/**
 * 动态调用初始化文件
 *
 * @author  Jamers <jamersnox@zomew.net>
 * @license https://opensource.org/licenses/GPL-3.0 GPL
 * @since   2017.12.26
 */
if (!defined("ZF_ROOT")) {
    define("ZF_ROOT", dirname(__FILE__) . DIRECTORY_SEPARATOR);
}
if (PHP_MAJOR_VERSION < 7) {
    throw new \Exception('Please try to upgrade your PHP version to more than 7.0');
}
if (defined('STDIN')) {
    chdir(dirname(__FILE__));
}

if (!defined("RUN_ENV")) {
    $run_env = getenv("RUN_ENV");
    if (empty($run_env)) {
        $run_env = 'local';
    }
    define('RUN_ENV', $run_env);
}

/**
 * 自动加载函数
 *
 * @param string $cls classname
 *
 * @return bool
 * @since  2019.03.23
 */
function autoload($cls)
{
    $base_list = array(
        'class',
        'self',
    );
    $base = 'class'.DIRECTORY_SEPARATOR;
    $libs = array();
    if (file_exists(ZF_ROOT . 'static_libs.php')) {
        $libs = include ZF_ROOT . 'static_libs.php';
    } elseif (file_exists(ZF_ROOT . 'static_libs.example.php')) {
        $libs = include ZF_ROOT . 'static_libs.example.php';
    }
    $name = trim(strtolower($cls), ' \\');
    $file = '';
    if (isset($libs[$name])) {
        $file = ZF_ROOT  . $base . $libs[strtolower($cls)];
    } else {
        foreach ($base_list as $v) {
            $base = $v . DIRECTORY_SEPARATOR;
            $a = explode('\\', $cls);
            $a[count($a) - 1] = 'Cls_' . ucfirst($a[count($a) - 1]);
            $file = ZF_ROOT . $base . implode(DIRECTORY_SEPARATOR, $a) . '.php';
            if (file_exists($file)) {
                break;
            }
        }
    }

    if ($file && file_exists($file)) {
        include_once $file;
    }
    return true;
}

spl_autoload_register('autoload');

$composer_autoload = ZF_ROOT . 'vendor/autoload.php';
if (file_exists($composer_autoload)) {
    include_once $composer_autoload;
}

/*if (!class_exists("\CONFIG") || !isset(\CONFIG::$debug) || !\CONFIG::$debug) {
    error_reporting(0);
    ini_set('display_errors', '0');
}else{
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
}*/
