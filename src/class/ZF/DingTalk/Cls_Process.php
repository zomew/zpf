<?php
/**
 * Created by PhpStorm.
 * User: Jamers
 * Date: 2019/5/21
 * Time: 9:19
 * File: Cls_ProcessInstance.php
 */
namespace ZF\DingTalk;

/**
 * 阿里钉钉审批相关接口封装
 *
 * @package ZF\DingTalk
 * @author  Jamers <jamersnox@zomew.net>
 * @license https://opensource.org/licenses/GPL-3.0 GPL
 * @since   2019.05.21
 * @see     https://open-doc.dingtalk.com/microapp/serverapi2/cmct1a
 */
class Process extends \ZF\DingTalk
{
    /**
     * @var bool 自动发送标头
     */
    public static $autoSendHeader = true;

    /**
     * 创建新的审批实例
     * @param      $data
     * @param bool $raw
     *
     * @return array
     * @static
     * @since  2019.05.21
     */
    public static function create($data, $raw = false)
    {
        $ret = ['code' => -1, 'msg' => '',];
        if ($data instanceof ProcessInfo) {
            $data = $data->getArray();
        } elseif (is_string($data)) {
            $data = json_decode($data, true);
        } elseif (!is_array($data)) {
            $data = [];
        }
        if ($data) {
            //必填项
            $chklist = ['process_code', 'originator_user_id', 'dept_id', 'form_component_values',];
            $need = [];
            foreach ($chklist as $v) {
                if (!isset($data[$v])) {
                    $need[] = $v;
                }
            }
            if ($need) {
                $msg = implode(',', $need) . '字段必须设置，请检查参数';
                $ret = ['code' => 1, 'msg' => $msg,];
            } else {
                $url = self::buildOperateUrl('topapi/processinstance/create', ['access_token' => '',]);
                $data = self::doRequest($url, json_encode($data), 'POST', 'process_instance_id', $raw);
                $ret = self::outAry($ret, $data, $raw);
            }
        }
        return $ret;
    }

    /**
     * 获取指定审批单的实例审批ID列表
     * @param       $process_code
     * @param int   $start_time
     * @param int   $end_time
     * @param int   $size
     * @param int   $cursor
     * @param array $userid_list
     * @param bool  $raw
     *
     * @return array
     * @static
     * @since  2019.05.21
     */
    public static function getListIds(
        $process_code,
        $start_time = 0,
        $end_time = 0,
        $size = 0,
        $cursor = 0,
        $userid_list = [],
        $raw = false
    ) {
        $ret = ['code' => -1, 'msg' => '',];
        if ($start_time <= 0) {
            $start_time = strtotime(date('Y-m-d')) * 1000;
        }
        if (strlen(strval($start_time)) <= 10) {
            $start_time *= 1000;
        }
        if ($size < 0) {
            $size = 10;
        }
        if ($process_code && $start_time) {
            $data = ['process_code' => $process_code, 'start_time' => $start_time,];
            if ($end_time > 0) {
                if (strlen(strval($end_time)) <= 10) {
                    $end_time *= 1000;
                }
                $data['end_time'] = $end_time;
            }
            if ($size) {
                $data['size'] = $size;
            }
            if ($cursor) {
                $data['cursor'] = $cursor;
            }
            if ($userid_list) {
                if (is_array($userid_list)) {
                    $data['userid_list'] = implode(',', $userid_list);
                } elseif (is_string($userid_list)) {
                    $data['userid_list'] = $userid_list;
                }
            }
            $url = self::buildOperateUrl('topapi/processinstance/listids', ['access_token' => '',]);
            $data = self::doRequest($url, json_encode($data), 'POST', 'result', $raw);
            $ret = self::outAry($ret, $data, $raw);
        }
        return $ret;
    }

    /**
     * 获取指定审批实例详细信息
     * @param      $process_id
     * @param bool $raw
     *
     * @return array
     * @static
     * @since  2019.05.21
     */
    public static function get($process_id, $raw = false)
    {
        $ret = ['code' => -1, 'msg' => '',];
        if ($process_id) {
            $data = ['process_instance_id' => $process_id,];
            $url = self::buildOperateUrl('topapi/processinstance/get', ['access_token' => '',]);
            $data = self::doRequest($url, json_encode($data), 'POST', 'process_instance', $raw);
            $ret = self::outAry($ret, $data, $raw);
        }
        return $ret;
    }

    /**
     * 获取待审批数量
     * @param      $userid
     * @param bool $raw
     *
     * @return array
     * @static
     * @since  2019.05.21
     */
    public static function getTodoNum($userid, $raw = false)
    {
        $ret = ['code' => -1, 'msg' => '',];
        if ($userid) {
            $data = ['userid' => $userid,];
            $url = self::buildOperateUrl('topapi/process/gettodonum', ['access_token' => '',]);
            $data = self::doRequest($url, json_encode($data), 'POST', 'count', $raw);
            $ret = self::outAry($ret, $data, $raw);
        }
        return $ret;
    }

    /**
     * 获取用户可见审批表单列表
     * @param      $userid
     * @param int  $offset
     * @param int  $size
     * @param bool $raw
     *
     * @return array
     * @static
     * @since  2019.05.21
     */
    public static function listByUserId($userid, $offset = 0, $size = 100, $raw = false)
    {
        $ret = ['code' => -1, 'msg' => '',];
        if ($userid) {
            $data = ['userid' => $userid, 'offset' => $offset, 'size' => $size,];
            $url = self::buildOperateUrl('topapi/process/listbyuserid', ['access_token' => '',]);
            $data = self::doRequest($url, json_encode($data), 'POST', 'count', $raw);
            $ret = self::outAry($ret, $data, $raw);
        }
        return $ret;
    }
}
