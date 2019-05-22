<?php
/**
 * Created by PhpStorm.
 * User: Jamers
 * Date: 2019/5/21
 * Time: 15:22
 * File: Cls_Attendance.php
 */

namespace ZF\DingTalk;

/**
 * 阿里钉钉考勤相关接口封装
 *
 * @package ZF\DingTalk
 * @author  Jamers <jamersnox@zomew.net>
 * @license https://opensource.org/licenses/GPL-3.0 GPL
 * @since   2019.05.21
 * @see     https://open-doc.dingtalk.com/microapp/serverapi2/ufc8dl
 */
class Attendance extends \ZF\DingTalk
{
    /**
     * @var bool 自动发送标头
     */
    public static $autoSendHeader = true;

    /**
     * 企业考勤排班详情
     * @param int  $workDate
     * @param int  $offset
     * @param int  $size
     * @param bool $raw
     *
     * @return array
     * @static
     * @since  2019.05.21
     */
    public static function getListSchedule($workDate = 0, $offset = -1, $size = 0, $raw = false)
    {
        $ret = ['code' => -1, 'msg' => '',];
        if ($workDate == 0) {
            $workDate = time();
        } else {
            $workDate = intval($workDate);
        }
        $data = ['workDate' => date('Y-m-d', $workDate),];
        if ($offset >= 0) {
            $data['offset'] = $offset;
        }
        if ($size > 0) {
            $data['size'] = $size;
        }
        if ($data) {
            $url = self::buildOperateUrl('topapi/attendance/listschedule', ['access_token' => '',]);
            $data = self::doRequest($url, json_encode($data), 'POST', 'result', $raw);
            $ret = self::outAry($ret, $data, $raw);
        }
        return $ret;
    }

    /**
     * 企业考勤组详情
     * @param int  $offset
     * @param int  $size
     * @param bool $raw
     *
     * @return array
     * @static
     * @since  2019.05.21
     */
    public static function getSimpleGroups($offset = -1, $size = 0, $raw = false)
    {
        $ret = ['code' => -1, 'msg' => '',];

        $data = [];
        if ($offset >= 0) {
            $data['offset'] = $offset;
        }
        if ($size > 0) {
            $data['size'] = $size;
        }
        $url = self::buildOperateUrl('topapi/attendance/getsimplegroups', ['access_token' => '',]);
        $data = self::doRequest($url, $data ? json_encode($data) : '', 'POST', 'result', $raw);
        $ret = self::outAry($ret, $data, $raw);
        return $ret;
    }

    /**
     * 获取打卡详情
     * @param array $userIds
     * @param int   $from
     * @param int   $to
     * @param bool  $isI18n
     * @param bool  $raw
     *
     * @return array
     * @static
     * @since  2019.05.21
     */
    public static function getListRecord($userIds = [], $from = 0, $to = 0, $isI18n = false, $raw = false)
    {
        $ret = ['code' => -1, 'msg' => '',];

        $data = [];
        if ($userIds) {
            if ($from <= 0) {
                $from = strtotime(date('Y-m-d'));
            }
            if ($to <= 0) {
                $to = $from + 86400;
            }
            if (is_string($userIds)) {
                $userIds = explode(',', $userIds);
            }
            $data = [
                'userIds' => $userIds,
                'checkDateFrom' => date('Y-m-d H:i:s', $from),
                'checkDateTo' => date('Y-m-d H:i:s', $to),
                'isI18n' => boolval($isI18n) ? 'true' : 'false',
            ];
        }
        if ($data) {
            $url = self::buildOperateUrl('attendance/listRecord', ['access_token' => '',]);
            $data = self::doRequest($url, json_encode($data), 'POST', 'recordresult', $raw);
            $ret = self::outAry($ret, $data, $raw);
        }
        return $ret;
    }

    /**
     * 获取打卡结果
     * @param array $userIds
     * @param int   $from
     * @param int   $to
     * @param int   $offset
     * @param int   $limit
     * @param bool  $isI18n
     * @param bool  $raw
     *
     * @return array
     * @static
     * @since  2019.05.21
     */
    public static function getList(
        $userIds = [],
        $from = 0,
        $to = 0,
        $offset = 0,
        $limit = 0,
        $isI18n = false,
        $raw = false
    ) {
        $ret = ['code' => -1, 'msg' => '',];

        $data = [];
        if ($userIds) {
            if ($from <= 0) {
                $from = strtotime(date('Y-m-d'));
            }
            if ($to <= 0) {
                $to = $from + 86400;
            }
            if (is_string($userIds)) {
                $userIds = explode(',', $userIds);
            }
            $data = [
                'userIdList' => $userIds,
                'workDateFrom' => date('Y-m-d H:i:s', $from),
                'workDateTo' => date('Y-m-d H:i:s', $to),
                'offset' => $offset,
                'limit' => $limit,
                'isI18n' => boolval($isI18n) ? 'true' : 'false',
            ];
        }
        if ($data) {
            $url = self::buildOperateUrl('attendance/list', ['access_token' => '',]);
            $data = self::doRequest($url, json_encode($data), 'POST', 'recordresult', $raw);
            $ret = self::outAry($ret, $data, $raw);
        }
        return $ret;
    }

    /**
     * 获取请假时长
     * @param string $userid
     * @param int    $from
     * @param int    $to
     * @param bool   $raw
     *
     * @return array
     * @static
     * @since  2019.05.21
     */
    public static function getLeaveApproveDuration($userid = '', $from = 0, $to = 0, $raw = false)
    {
        $ret = ['code' => -1, 'msg' => '',];

        $data = [];
        if ($userid) {
            if ($from <= 0) {
                $from = strtotime(date('Y-m-d'));
            }
            if ($to <= 0) {
                $to = $from + 86400;
            }
            $data = [
                'userid' => $userid,
                'from_date' => date('Y-m-d H:i:s', $from),
                'to_date' => date('Y-m-d H:i:s', $to),
            ];
        }
        if ($data) {
            $url = self::buildOperateUrl('topapi/attendance/getleaveapproveduration', ['access_token' => '',]);
            $data = self::doRequest($url, json_encode($data), 'POST', 'result', $raw);
            $ret = self::outAry($ret, $data, $raw);
        }
        return $ret;
    }

    /**
     * 查询请假状态
     * @param array $userIds
     * @param int   $from
     * @param int   $to
     * @param int   $offset
     * @param int   $size
     * @param bool  $raw
     *
     * @return array
     * @static
     * @since  2019.05.21
     */
    public static function getLeaveStatus(
        $userIds = [],
        $from = 0,
        $to = 0,
        $offset = 0,
        $size = 20,
        $raw = false
    ) {
        $ret = ['code' => -1, 'msg' => '',];

        $data = [];
        if ($userIds) {
            if ($from <= 0) {
                $from = strtotime(date('Y-m-d'));
            }
            if ($to <= 0) {
                $to = $from + 86400;
            }
            if (strlen(strval($from)) <= 12) {
                $from *= 1000;
            }
            if (strlen(strval($to)) <= 12) {
                $to *= 1000;
            }
            if (is_array($userIds)) {
                $userIds = implode(',', $userIds);
            }
            $data = [
                'userid_list' => $userIds,
                'start_time' => $from,
                'end_time' => $to,
                'offset' => $offset,
                'size' => $size,
            ];
        }
        if ($data) {
            $url = self::buildOperateUrl('topapi/attendance/getleavestatus', ['access_token' => '',]);
            $data = self::doRequest($url, json_encode($data), 'POST', 'result', $raw);
            $ret = self::outAry($ret, $data, $raw);
        }
        return $ret;
    }

    /**
     * 获取用户考勤组
     * @param      $userid
     * @param bool $raw
     *
     * @return array
     * @static
     * @since  2019.05.21
     */
    public static function getUserGroup($userid, $raw = false)
    {
        $ret = ['code' => -1, 'msg' => '',];
        if ($userid) {
            $data = ['userid' => $userid,];
            $url = self::buildOperateUrl('topapi/attendance/getusergroup', ['access_token' => '',]);
            $data = self::doRequest($url, json_encode($data), 'POST', 'result', $raw);
            $ret = self::outAry($ret, $data, $raw);
        }
        return $ret;
    }
}
