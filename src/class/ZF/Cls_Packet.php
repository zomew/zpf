<?php
/**
 * Created by PhpStorm.
 * User: Jamers
 * Date: 2018/10/27
 * Time: 14:06
 * File: Cls_Packet.php
 */

namespace ZF;

/**
 * 二进制数据封装类
 *
 * @package ZF
 * @author  Jamers <jamersnox@zomew.net>
 * @license https://opensource.org/licenses/GPL-3.0 GPL
 * @since   2018.10.27
 */
class Packet
{
    /**
     * 生成数据包长度
     * 
     * @param string $msg 
     * @param int    $add 
     * 
     * @return string
     */
    private static function _buildPacketLen($msg, $add = 0)
    {
        return pack('N', strlen($msg)+$add);
    }

    /**
     * 生成数据包标头
     * 
     * @param string $type 
     * 
     * @return string
     */
    private static function _buildPacketHead($type = '00')
    {
        $list = str_split($type);
        $l = intval($list[0]) & 0xFF;
        $r = 0;
        if (isset($list[1])) {
            $r = intval($list[1]) & 0xFF;
        }
        return pack('CC', $l ^ 0xA, $r ^ 0xE);
    }

    /**
     * 将数据打包成二进制数据包
     * 
     * @param string $type 
     * @param string $msg 
     * 
     * @return string
     */
    public static function buildPacket($type = '00', $msg = '')
    {
        return self::_buildPacketLen($msg, 0xA) . self::_buildPacketHead($type) . 
            self::_buildPacketLen($msg) . $msg;
    }

    /**
     * 读取二进制包头信息
     * 
     * @param string $msg 
     * 
     * @return array
     */
    private static function _getPacketHead($msg)
    {
        $r = unpack('C*', substr($msg, 4, 2));
        $r[1] ^= 0xA;
        $r[2] ^= 0xE;
        return $r;
    }

    /**
     * 读取数据包长度
     * 
     * @param string $msg 
     * @param int    $add 
     * 
     * @return mixed
     */
    public static function getPacketLen($msg, $add = 0)
    {
        $r = unpack('N', substr($msg, 0+$add, 4));
        return $r[1];
    }

    /**
     * 解析数据包并较验包头信息是否正确
     * 
     * @param string $msg 
     * 
     * @return array
     */
    public static function getPacket($msg)
    {
        $r = array();
        $r[0] = self::_getPacketHead($msg);
        if (strlen($msg) > 0xA) {
            $r[1] = substr($msg, 0xA);
        } else {
            $rl[1] = '';
            return $r;
        }

        $l = array();
        $l[0] = self::getPacketLen($msg);
        $l[1] = self::getPacketLen($msg, 6);
        $lm = strlen($r[1]);
        if (!(($lm + 0xA == $l[0]) && ($lm == $l[1]))) {
            $r = array();
        }
        return $r;
    }
}