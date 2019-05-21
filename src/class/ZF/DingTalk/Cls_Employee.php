<?php
/**
 * Created by PhpStorm.
 * User: Jamers
 * Date: 2019/5/21
 * Time: 11:32
 * File: Cls_SmartHrm.php
 */

namespace ZF\DingTalk;

/**
 * 阿里钉钉智能人事相关接口封装
 *
 * @package ZF\DingTalk
 * @author  Jamers <jamersnox@zomew.net>
 * @license https://opensource.org/licenses/GPL-3.0 GPL
 * @since   2019.05.21
 * @since   因为没有开通此应用，此类均没有实际测试，有任何问题请及时联系或提交PR
 * @see     https://open-doc.dingtalk.com/microapp/serverapi2/rikq4i
 */
class Employee extends \ZF\DingTalk
{
    /**
     * @var bool 自动发送标头
     */
    public static $autoSendHeader = true;

    /**
     * 批量获取员工指定字段信息
     * @param array $userid_list
     * @param array $field_filter_list
     * @param bool  $raw
     *
     * @return array
     * @static
     * @since  2019.05.21
     */
    public static function list($userid_list = [], $field_filter_list = [], $raw = false)
    {
        $ret = ['code' => -1, 'msg' => '',];
        if ($userid_list) {
            if (is_array($userid_list)) {
                $userid_list = implode(',', $userid_list);
            } elseif (!is_string($userid_list)) {
                $userid_list = '';
            }
            if ($field_filter_list) {
                if (is_array($field_filter_list)) {
                    $field_filter_list = implode(',', $field_filter_list);
                } elseif (!is_string($field_filter_list)) {
                    $field_filter_list = '';
                }
            }
            if ($userid_list) {
                $data = ['userid_list' => $userid_list,];
                if ($field_filter_list) {
                    $data['field_filter_list'] = $field_filter_list;
                }
                $url = self::buildOperateUrl('topapi/smartwork/hrm/employee/list', ['access_token' => '',]);
                $data = self::doRequest($url, json_encode($data), 'POST', 'result', $raw);
                $ret = self::outAry($ret, $data, $raw);
            }
        }
        return $ret;
    }

    /**
     * 查询企业待入职员工列表
     * @param int  $offset
     * @param int  $size
     * @param bool $raw
     *
     * @return array
     * @static
     * @since  2019.05.21
     */
    public static function queryPreentry($offset = 0, $size = 50, $raw = false)
    {
        $ret = ['code' => -1, 'msg' => '',];
        $data = ['offset' => $offset, 'size' => $size,];
        $url = self::buildOperateUrl('topapi/smartwork/hrm/employee/querypreentry', ['access_token' => '',]);
        $data = self::doRequest($url, $data, 'POST', 'result', $raw);
        $ret = self::outAry($ret, $data, $raw);
        return $ret;
    }

    /**
     * 查询指定状态的员工列表
     * @param array $status_list
     * @param int   $offset
     * @param int   $size
     * @param bool  $raw
     *
     * @return array
     * @static
     * @since  2019.05.21
     */
    public static function queryOnJob($status_list = [], $offset = 0, $size = 50, $raw = false)
    {
        $ret = ['code' => -1, 'msg' => '',];
        $list = [2, 3, 5, -1,];     //2 试用期 3 正式 5 待离职 -1 无状态
        $status = [];
        if ($status_list) {
            if (is_string($status_list)) {
                $status_list = explode(',', $status_list);
            }
            if (is_array($status_list)) {
                foreach (array_unique($status_list) as $v) {
                    if (in_array($v, $list)) {
                        $status[] = $v;
                    }
                }
            }
        } else {
            $status = $list;
        }
        $data = ['status_list' => implode(',', $status),'offset' => $offset, 'size' => $size,];
        $url = self::buildOperateUrl('topapi/smartwork/hrm/employee/queryonjob', ['access_token' => '',]);
        $data = self::doRequest($url, $data, 'POST', 'result', $raw);
        $ret = self::outAry($ret, $data, $raw);
        return $ret;
    }

    /**
     * 查询离职员工UserId
     * @param int  $offset
     * @param int  $size
     * @param bool $raw
     *
     * @return array
     * @static
     * @since  2019.05.21
     */
    public static function queryDimission($offset = 0, $size = 50, $raw = false)
    {
        $ret = ['code' => -1, 'msg' => '',];
        $data = ['offset' => $offset, 'size' => $size,];
        $url = self::buildOperateUrl('topapi/smartwork/hrm/employee/querydimission', ['access_token' => '',]);
        $data = self::doRequest($url, $data, 'POST', 'result', $raw);
        $ret = self::outAry($ret, $data, $raw);
        return $ret;
    }

    /**
     * 获取指定离职userid的信息
     * @param array $userid_list
     * @param bool  $raw
     *
     * @return array
     * @static
     * @since  2019.05.21
     */
    public static function listDimission($userid_list = [], $raw = false)
    {
        $ret = ['code' => -1, 'msg' => '',];
        if ($userid_list) {
            if (is_array($userid_list)) {
                $userid_list = implode(',', $userid_list);
            } elseif (!is_string($userid_list)) {
                $userid_list = '';
            }
        }
        if ($userid_list) {
            $data = ['userid_list' => $userid_list,];
            $url = self::buildOperateUrl('topapi/smartwork/hrm/employee/listdimission', ['access_token' => '',]);
            $data = self::doRequest($url, $data, 'POST', 'result', $raw);
            $ret = self::outAry($ret, $data, $raw);
        }
        return $ret;
    }

    /**
     * 添加待入职人员信息
     * @param string $name
     * @param string $mobile
     * @param string $pre_entry_time
     * @param string $op_userid
     * @param array  $extend_info
     * @param bool   $raw
     *
     * @return array
     * @static
     * @since  2019.05.21
     */
    public static function addPreentry(
        $name = '',
        $mobile = '',
        $pre_entry_time = '',
        $op_userid = '',
        $extend_info = [],
        $raw = false
    ) {
        $ret = ['code' => -1, 'msg' => '',];
        $data = [];
        if ($name && $mobile) {
            $data = ['name' => $name, 'mobile' => $mobile,];
            if ($pre_entry_time) {
                $data['pre_entry_time'] = $pre_entry_time;
            }
            if ($op_userid) {
                $data['op_userid'] = $op_userid;
            }
            if ($extend_info) {
                if (is_array($extend_info)) {
                    $extend_info = json_encode($extend_info, JSON_UNESCAPED_UNICODE);
                } elseif (!is_string($extend_info)) {
                    $extend_info = '';
                }
                if ($extend_info) {
                    $data['extend_info'] = $extend_info;
                }
            }
        }
        if ($data) {
            $url = self::buildOperateUrl('topapi/smartwork/hrm/employee/addpreentry', ['access_token' => '',]);
            $data = self::doRequest($url, json_encode(['param' => $data,]), 'POST', 'userid', $raw);
            $ret = self::outAry($ret, $data, $raw);
        }
        return $ret;
    }
}
