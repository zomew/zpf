<?php
/**
 * Created by PhpStorm.
 * User: Jamers
 * Date: 2019/5/22
 * Time: 11:18
 * File: Cls_CheckIn.php
 */

namespace ZF\DingTalk;

/**
 * 阿里钉钉签到接口封装
 *
 * @package ZF\DingTalk
 * @author  Jamers <jamersnox@zomew.net>
 * @license https://opensource.org/licenses/GPL-3.0 GPL
 * @since   2019.05.22
 * @see     https://open-doc.dingtalk.com/microapp/serverapi2/uyr2ah
 */
class CheckIn extends \ZF\DingTalk
{
    /**
     * @var bool 自动发送标头
     */
    public static $autoSendHeader = true;

    /**
     * 获取部门用户签到数据
     * @param        $dept_id
     * @param int    $from
     * @param int    $to
     * @param int    $offset
     * @param int    $size
     * @param string $order
     * @param bool   $raw
     *
     * @return array
     * @static
     * @since  2019.05.22
     */
    public static function getRecord($dept_id, $from = 0, $to = 0, $offset = -1, $size = 0, $order = '', $raw = false)
    {
        $ret = ['code' => -1, 'msg' => '',];
        if ($dept_id > 0) {
            if ($from <= 0) {
                $from = strtotime(date('Y-m-d'));
            }
            if ($to <= 0) {
                if (strlen($from) <= 12) {
                    $to = $from + 86400;
                } else {
                    $to = $from + 86400000;
                }
            }
            if (strlen(strval($from)) <= 12) {
                $from *= 1000;
            }
            if (strlen(strval($to)) <= 12) {
                $to *= 1000;
            }
            $params = [
                'access_token' => '',
                'department_id' => $dept_id,
                'start_time' => $from,
                'end_time' => $to,
            ];
            if ($offset >= 0) {
                $params['offset'] = $offset;
            }
            if ($size > 0) {
                $params['size'] = $size;
            }
            if ($order) {
                $order = trim(strtolower($order));
                if (in_array($order, ['asc', 'desc',])) {
                    $params['order'] = $order;
                }
            }
            $url = self::buildOperateUrl('checkin/record', $params);
            $data = self::doRequest($url, '', 'GET', 'data', $raw);
            $ret = self::outAry($ret, $data, $raw);
        }
        return $ret;
    }

    /**
     * 获取指定用户签到记录数据
     * @param array $userid_list
     * @param int   $from
     * @param int   $to
     * @param int   $cursor
     * @param int   $size
     * @param bool  $raw
     *
     * @return array
     * @static
     * @since  2019.05.22
     */
    public static function getUsersRecord($userid_list = [], $from = 0, $to = 0, $cursor = 0, $size = 100, $raw = false)
    {
        $ret = ['code' => -1, 'msg' => '',];
        if (is_array($userid_list)) {
            $userid_list = implode(',', $userid_list);
        } elseif (!is_string($userid_list)) {
            $userid_list = '';
        }
        if ($userid_list) {
            if ($from <= 0) {
                $from = strtotime(date('Y-m-d'));
            }
            if ($to <= 0) {
                if (strlen($from) <= 12) {
                    $to = $from + 86400;
                } else {
                    $to = $from + 86400000;
                }
            }
            if (strlen(strval($from)) <= 12) {
                $from *= 1000;
            }
            if (strlen(strval($to)) <= 12) {
                $to *= 1000;
            }
            $data = [
                'userid_list' => $userid_list,
                'start_time' => $from,
                'end_time' => $to,
                'cursor' => $cursor,
                'size' => $size,
            ];

            $url = self::buildOperateUrl('topapi/checkin/record/get', ['access_token' => '',]);
            $data = self::doRequest($url, json_encode($data), 'POST', 'result', $raw);
            $ret = self::outAry($ret, $data, $raw);
        }
        return $ret;
    }
}
