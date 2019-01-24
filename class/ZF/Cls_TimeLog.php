<?php
/**
 * Created by PhpStorm.
 * User: jamer
 * Date: 2018/10/22
 * Time: 9:31
 */

namespace ZF;

/**
 * 用于记录代码执行时间的类，用于优化代码逻辑之用
 *
 * @author Jamers <jamersnox@zomew.net>
 * @license https://opensource.org/licenses/GPL-3.0 GPL
 * @since 2018.10.22
 *
 * Class TimeLog
 * @package ZF
 */
class TimeLog {
    /**
     * 开始时间
     * @var int
     */
    private static $start = 0;
    /**
     * 最后更新时间
     * @var int
     */
    private static $last = 0;
    /**
     * 时间记录数组
     * @var array
     */
    private static $timelog = array();
    /**
     * 日志名变量
     * @var string
     */
    private static $file = '';

    private static $debug = false;

    /**
     * 检查是否允许时间调试
     *
     * @return bool
     */
    public static function CheckTag() {
        if (!self::$file) self::$file = '_TimeLog_'.date('Ymd').'.txt';
        $ret = false;
        if (isset($_GET['timelog']) && $_GET['timelog'] == '1') $ret = true;
        if (self::$debug) $ret = true;
        return $ret;
    }

    public static function Enable() {
        self::$debug = true;
    }

    public static function Disable() {
        self::$debug = false;
    }

    /**
     * 开始记录时间日志
     */
    public static function Start() {
        if (self::CheckTag()) {
            self::$start = microtime(true);
            self::$last = self::$start;
            self::$timelog[] = self::getCallInfo() . "\t" . self::$start;
        }
    }

    /**
     * 记录当前时间数据
     */
    public static function LogTime() {
        if (self::CheckTag()) {
            $t = microtime(true);
            $p = $t - self::$last;
            self::$last = $t;
            self::$timelog[] = self::getCallInfo() . "\t" . $p . '  '.$t;
        }
    }

    /**
     * 结束日志记录
     */
    public static function End() {
        if (self::CheckTag()) {
            $t = microtime(true);
            $p = $t - self::$last;
            $a = $t - self::$start;
            self::$timelog[] = self::getCallInfo() . "\t" . $p . '   ' . $t . "  \ntotal:". $a ;
            \ZF\Common::_savelog(self::$file, "".implode("\n",self::$timelog));
            self::$start = 0;
            self::$last = 0;
            self::$timelog = array();
        }
    }

    /**
     * 获取上层调用信息
     * @return string
     */
    private static function getCallInfo() {
        $a = debug_backtrace();
        $ret = '';
        if ($a && isset($a[1])) {
            $b = $a[1];
            $ret = basename($b['file']) . ':' . $b['line'] . ':' . $b['function'].':';
        }
        return $ret;
    }
}