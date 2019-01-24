<?php
/**
 * Created by PhpStorm.
 * User: jamer
 * Date: 2018/7/5
 * Time: 10:29
 */

namespace ZF;

/**
 * 存储单位操作运算类
 * @author Jamers <jamersnox@zomew.net>
 * @license https://opensource.org/licenses/GPL-3.0 GPL
 * @since 2018.07.05
 *
 * Class SizeOperate
 * @package ZF
 */
class SizeOperate {
    /**
     * 存储单位列表，最小单位为byte
     *
     * @var array
     */
    private static $unit = array(
        'B' => 0,
        'K' => 1,
        'M' => 2,
        'G' => 3,
        'T' => 4,
        'P' => 5,
        'E' => 6,
        'Z' => 7,
        'BB' => 8,
    );

    /**
     * 将容量转换在字节数
     *
     * @param string $size
     * @return string
     */
    public static function Size2Byte($size = '1m') {
        $ret = '0';
        if ($size) {
            $list = implode('',array_keys(self::$unit));
            if (preg_match('/\s*([\d]+(?:\.[\d]+)?)(['.$list.']?)b?/i',strtoupper($size),$m)) {
                $ret = $m[1];
                if ($m[2]) {
                    $ret = bcmul($ret,bcpow('1024',self::$unit[$m[2]]));
                }
            }
        }
        return $ret;
    }

    /**
     * 将字节数转换成存储单位显示
     *
     * @param string $bytes
     * @return string
     */
    public static function Byte2Unit($bytes = '0') {
        $ret = trim($bytes);
        if (preg_match('/[\d]+/i',$ret)) {
            $tmp = self::$unit;
            arsort($tmp);
            foreach($tmp as $k => $v) {
                $val = bcpow('1024',$v);
                if (bccomp($ret,$val)>=0) {
                    $t = bcdiv($ret,$val,4);
                    $t = self::RoundLastScale($t);
                    $ret = $t.$k;
                    break;
                }
            }
        }
        return $ret;
    }

    /**
     * 将最后一位小数四舍五入
     *
     * @param string $str
     * @return string
     */
    private static function RoundLastScale($str = '0') {
        $ret = $str;
        if (preg_match('/^(\d+)\.(\d+)$/',$ret,$m)){
            if (strlen($m[2])<=1 || preg_match('/^0+$/',$m[2])) {
                $ret = $m[1];
                if (intval(substr($m[2],0,1))>=5) $ret = bcadd($ret,'1');
            }else{
                $ret = $m[1].'.'.substr($m[2],0,-1);
                if (intval(substr($m[2],-1))>=5){
                    $ret = rtrim(bcadd($ret,"0.".str_repeat('0',strlen($m[2])-2).'1',strlen($m[2])-1),'0');
                }
            }
        }
        return $ret;
    }

    /**
     * 存储单位加法运算，支持直接使用数组批量相加
     *
     * @param string|array $left
     * @param string $right
     * @return string
     */
    public static function Size_Add($left,$right = '') {
        $ret = '0';
        if ($left && is_array($left)) {
            $tmp = '0';
            foreach($left as $v) {
                if ($v && is_string($v)) {
                    $tmp = bcadd($tmp,self::Size2Byte($v));
                }
            }
            $ret = self::Byte2Unit($tmp);
        }else{
            if (is_string($left)) $ret = $left;
            if (is_string($right) && $right) $ret = self::Byte2Unit(bcadd(self::Size2Byte($left),self::Size2Byte($right)));
        }
        return $ret;
    }

    /**
     * 存储单位减法运算
     *
     * @param string $left
     * @param string $right
     * @return int|string
     */
    public static function Size_Sub($left = '',$right='') {
        $ret = 0;
        if (is_string($left) && $left) $ret = $left;
        if (is_string($right) && $right) $ret = self::Byte2Unit(bcsub(self::Size2Byte($left),self::Size2Byte($right)));
        return $ret;
    }
}