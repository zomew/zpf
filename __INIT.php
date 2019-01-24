<?php
@session_start();

/**
 * 动态调用初始化文件
 * @author Jamers <jamersnox@zomew.net>
 * @license https://opensource.org/licenses/GPL-3.0 GPL
 * @since 2017.12.26
 */
if (!defined("ZF_ROOT")) {
    define("ZF_ROOT",dirname(__FILE__).DIRECTORY_SEPARATOR);
}

if (defined('STDIN')) chdir(dirname(__FILE__));

if (!defined("RUN_ENV")) {
    $run_env = getenv("RUN_ENV");
    if (empty($run_env)) {
        $run_env = 'local';
    }
    define('RUN_ENV', $run_env);
}

function autoload($cls) {
    $base = 'class'.DIRECTORY_SEPARATOR;
    $libs = array(
        'smarty' => 'libs/Smarty-3.1.30/Smarty.class.php',
        'qrcode' => 'libs/phpqrcode/phpqrcode.php',
    );
    $name = trim(strtolower($cls),' \\');
    if (isset($libs[$name])) {
        $file = ZF_ROOT  . $base . $libs[strtolower($cls)];
    }else{
        $a = explode('\\',$cls);
        $a[count($a)-1] = 'Cls_'.ucfirst($a[count($a)-1]);
        $file = ZF_ROOT . $base . implode(DIRECTORY_SEPARATOR,$a) . '.php';
    }

    if (file_exists($file)) {
        include_once($file);
    }
    return true;
}

spl_autoload_register('autoload');

/*if (!class_exists("\CONFIG") || !isset(\CONFIG::$debug) || !\CONFIG::$debug) {
    error_reporting(0);
    ini_set('display_errors', '0');
}else{
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
}*/