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
 * @package ZF
 * @author  Jamers <jamersnox@zomew.net>
 * @license https://opensource.org/licenses/GPL-3.0 GPL
 * @since   2018.10.22
 */
class TimeLog
{
    /**
     * 开始时间
     *
     * @var int
     */
    private static $start = 0;
    /**
     * 最后更新时间
     *
     * @var int
     */
    private static $last = 0;
    /**
     * 时间记录数组
     *
     * @var array
     */
    private static $timelog = array();
    /**
     * 日志名变量
     *
     * @var string
     */
    private static $file = '';

    private static $debug = false;

    /**
     * 检查是否允许时间调试
     *
     * @return bool
     */
    public static function checkTag()
    {
        if (!self::$file) {
            self::$file = '_TimeLog_'.date('Ymd').'.txt';
        }
        $ret = false;
        if (isset($_GET['timelog']) && $_GET['timelog'] == '1') {
            $ret = true;
        }
        if (self::$debug) {
            $ret = true;
        }
        return $ret;
    }

    /**
     * 开始记录
     *
     * @return void
     * @static
     * @since  2019.03.24
     */
    public static function enable()
    {
        self::$debug = true;
    }

    /**
     * 停止记录
     *
     * @return void
     * @static
     * @since  2019.03.24
     */
    public static function disable()
    {
        self::$debug = false;
    }

    /**
     * 开始记录时间日志
     *
     * @return void
     * @static
     * @since  2019.03.24
     */
    public static function start()
    {
        if (self::checkTag()) {
            self::$start = microtime(true);
            self::$last = self::$start;
            self::$timelog[] = self::getCallInfo() . "\t" . self::$start;
        }
    }

    /**
     * 记录当前时间数据
     *
     * @return void
     * @static
     * @since  2019.03.24
     */
    public static function logTime()
    {
        if (self::checkTag()) {
            $t = microtime(true);
            $p = $t - self::$last;
            self::$last = $t;
            self::$timelog[] = self::getCallInfo() . "\t" . $p . '  '.$t;
        }
    }

    /**
     * 结束日志记录
     *
     * @return void
     * @static
     * @since  2019.03.24
     */
    public static function end()
    {
        if (self::checkTag()) {
            $t = microtime(true);
            $p = $t - self::$last;
            $a = $t - self::$start;
            self::$timelog[] = self::getCallInfo() . "\t" . $p . '   ' .
                $t . "  \ntotal:". $a ;
            \ZF\Common::_savelog(self::$file, "".implode("\n", self::$timelog));
            self::$start = 0;
            self::$last = 0;
            self::$timelog = array();
        }
    }

    /**
     * 获取上层调用信息
     *
     * @return string
     */
    private static function getCallInfo()
    {
        $a = debug_backtrace();
        $ret = '';
        if ($a && isset($a[1])) {
            $b = $a[1];
            $ret = basename($b['file']) . ':' . $b['line'] . ':' . $b['function'].':';
        }
        return $ret;
    }
}
