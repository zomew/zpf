<?php
/**
 * Created by PhpStorm.
 * User: Jamers
 * Date: 2019/5/21
 * Time: 13:45
 * File: Cls_Report.php
 */

namespace ZF\DingTalk;

/**
 * 阿里钉钉日志相关接口封装
 *
 * @package ZF\DingTalk
 * @author  Jamers <jamersnox@zomew.net>
 * @license https://opensource.org/licenses/GPL-3.0 GPL
 * @since   2019.05.21
 * @see     https://open-doc.dingtalk.com/microapp/serverapi2/yknhmg
 */
class Report extends \ZF\DingTalk
{
    /**
     * @var bool 自动发送标头
     */
    public static $autoSendHeader = true;

    /**
     * 获取日报数据
     * @param int    $start_time
     * @param int    $end_time
     * @param string $template_name
     * @param string $userid
     * @param int    $cursor
     * @param int    $size
     * @param bool   $raw
     *
     * @return array
     * @static
     * @since  2019.05.21
     */
    public static function getReportList(
        $start_time = 0,
        $end_time = 0,
        $template_name = '',
        $userid = '',
        $cursor = 0,
        $size = 10,
        $raw = false
    ) {
        $ret = ['code' => -1, 'msg' => '',];
        $data = [];
        if ($start_time <= 0) {
            $start_time = strtotime(date('Y-m-d')) * 1000;
        }
        if (strlen(strval($start_time)) <= 12) {
            $start_time *= 1000;
        }
        if ($end_time <= 0) {
            $end_time = time() * 1000;
        }
        if (strlen(strval($end_time)) <= 12) {
            $end_time *= 1000;
        }
        if ($start_time && $end_time && $template_name) {
            $data = ['start_time' => $start_time, 'end_time' => $end_time,
                'template_name' => $template_name, 'cursor' => $cursor, 'size' => $size,];
            if ($userid) {
                $data['userid'] = $userid;
            }
        }
        if ($data) {
            $url = self::buildOperateUrl('topapi/report/list', ['access_token' => '',]);
            $data = self::doRequest($url, json_encode($data), 'POST', 'result', $raw);
            $ret = self::outAry($ret, $data, $raw);
        }
        return $ret;
    }

    /**
     * 获取日志统计数据
     * @param      $report_id
     * @param bool $raw
     *
     * @return array
     * @static
     * @since  2019.05.21
     */
    public static function getReportStatistics($report_id, $raw = false)
    {
        $ret = ['code' => -1, 'msg' => '',];
        if ($report_id) {
            $data = ['report_id' => $report_id,];
            $url = self::buildOperateUrl('topapi/report/statistics', ['access_token' => '',]);
            $data = self::doRequest($url, json_encode($data), 'POST', 'result', $raw);
            $ret = self::outAry($ret, $data, $raw);
        }
        return $ret;
    }

    /**
     * 获取日志相关人员列表
     * @param string $report_id
     * @param int    $type
     * @param int    $offset
     * @param int    $size
     * @param bool   $raw
     *
     * @return array
     * @static
     * @since  2019.05.21
     */
    public static function getReportListByType($report_id = '', $type = 0, $offset = -1, $size = 0, $raw = false)
    {
        $ret = ['code' => -1, 'msg' => '',];
        $data = [];
        if ($report_id) {
            $data = ['report_id' => $report_id, 'type' => intval($type),];
            if ($offset >= 0) {
                $data['offset'] = $offset;
            }
            if ($size > 0) {
                $data['size'] = $size;
            }
        }
        if ($data) {
            $url = self::buildOperateUrl('topapi/report/statistics/listbytype', ['access_token' => '',]);
            $data = self::doRequest($url, json_encode($data), 'POST', 'result', $raw);
            $ret = self::outAry($ret, $data, $raw);
        }
        return $ret;
    }

    /**
     * 获取日志接收人员列表
     * @param string $report_id
     * @param int    $offset
     * @param int    $size
     * @param bool   $raw
     *
     * @return array
     * @static
     * @since  2019.05.21
     */
    public static function getReceiverList($report_id = '', $offset = -1, $size = 0, $raw = false)
    {
        $ret = ['code' => -1, 'msg' => '',];
        $data = [];
        if ($report_id) {
            $data = ['report_id' => $report_id,];
            if ($offset >= 0) {
                $data['offset'] = $offset;
            }
            if ($size > 0) {
                $data['size'] = $size;
            }
        }
        if ($data) {
            $url = self::buildOperateUrl('topapi/report/receiver/list', ['access_token' => '',]);
            $data = self::doRequest($url, json_encode($data), 'POST', 'result', $raw);
            $ret = self::outAry($ret, $data, $raw);
        }
        return $ret;
    }

    /**
     * 获取日志评论详情
     * @param string $report_id
     * @param int    $offset
     * @param int    $size
     * @param bool   $raw
     *
     * @return array
     * @static
     * @since  2019.05.21
     */
    public static function getCommentList($report_id = '', $offset = -1, $size = 0, $raw = false)
    {
        $ret = ['code' => -1, 'msg' => '',];
        $data = [];
        if ($report_id) {
            $data = ['report_id' => $report_id,];
            if ($offset >= 0) {
                $data['offset'] = $offset;
            }
            if ($size > 0) {
                $data['size'] = $size;
            }
        }
        if ($data) {
            $url = self::buildOperateUrl('topapi/report/comment/list', ['access_token' => '',]);
            $data = self::doRequest($url, json_encode($data), 'POST', 'result', $raw);
            $ret = self::outAry($ret, $data, $raw);
        }
        return $ret;
    }

    /**
     * 获取用户日志未读数
     * @param      $userid
     * @param bool $raw
     *
     * @return array
     * @static
     * @since  2019.05.21
     */
    public static function getUnreadCount($userid, $raw = false)
    {
        $ret = ['code' => -1, 'msg' => '',];
        if ($userid) {
            $data = ['userid' => $userid,];
            $url = self::buildOperateUrl('topapi/report/getunreadcount', ['access_token' => '',]);
            $data = self::doRequest($url, json_encode($data), 'POST', 'count', $raw);
            $ret = self::outAry($ret, $data, $raw);
        }
        return $ret;
    }

    /**
     * 获取用户可见的日志模板
     * @param string $userid
     * @param int    $offset
     * @param int    $size
     * @param bool   $raw
     *
     * @return array
     * @static
     * @since  2019.05.21
     */
    public static function getTemplateListByUserid($userid = '', $offset = 0, $size = 100, $raw = false)
    {
        $ret = ['code' => -1, 'msg' => '',];
        if ($userid) {
            $data = ['userid' => $userid, 'offset' => $offset, 'size' => $size,];
            $url = self::buildOperateUrl('topapi/report/template/listbyuserid', ['access_token' => '',]);
            $data = self::doRequest($url, json_encode($data), 'POST', 'result', $raw);
            $ret = self::outAry($ret, $data, $raw);
        }
        return $ret;
    }
}
