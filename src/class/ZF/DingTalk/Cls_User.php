<?php
/**
 * Created by PhpStorm.
 * User: Jamers
 * Date: 2019/5/15
 * Time: 14:46
 * File: Cls_User.php
 */

namespace ZF\DingTalk;

use \ZF\Common;
use \ZF\DingTalk\UserInfo;

/**
 * 阿里钉钉用户操作相关API
 *
 * @package ZF\DingTalk
 * @author  Jamers <jamersnox@zomew.net>
 * @license https://opensource.org/licenses/GPL-3.0 GPL
 * @since   2019.05.15
 */
class User extends \ZF\DingTalk
{
    /**
     * 根据授权码获取用户ID
     * @param string $code
     * @param bool   $raw
     *
     * @return array|mixed|string
     * @static
     * @since  2019.05.15
     * @see https://open-doc.dingtalk.com/microapp/serverapi2/clotub
     */
    public static function getUserIdByAuthCode(string $code = '', bool $raw = false)
    {
        $ret = '';
        if ($code) {
            $url = self::buildOperateUrl('user/getuserinfo', ['access_token' => '', 'code' => $code,]);
            $data = self::doRequest($url, [], 'GET', 'userid', $raw);
            $ret = self::outAry($ret, $data, $raw);
        }
        return $ret;
    }

    /**
     * 根据userid获取用户信息
     * @param string $userid
     * @param string $lang
     *
     * @return array|mixed
     * @static
     * @since  2019.05.15
     * @see https://open-doc.dingtalk.com/microapp/serverapi2/clotub
     */
    public static function getUserInfoByUserId(string $userid = '', string $lang = 'zh_CN')
    {
        $ret = [];
        if ($userid) {
            if ($lang == '') {
                $lang = 'zh_CN';
            }
            $url = self::buildOperateUrl('user/get', ['access_token' => '', 'userid' => $userid, 'lang' => $lang,]);
            $ret = @json_decode(Common::getRequest($url), true);
        }
        return $ret;
    }

    /**
     * 获取部门用户userid列表
     * @param      $deptId
     * @param bool $raw
     *
     * @return array|mixed
     * @static
     * @since  2019.05.16
     * @see https://open-doc.dingtalk.com/microapp/serverapi2/ege851
     */
    public static function getDeptMemberList($deptId, $raw = false)
    {
        $ret = [];
        if ($deptId) {
            $url = self::buildOperateUrl('user/getDeptMember', ['access_token' => '', 'deptId' => $deptId,]);
            $data = self::doRequest($url, [], 'GET', 'userIds', $raw);
            $ret = self::outAry($ret, $data, $raw);
        }
        return $ret;
    }

    /**
     * 获取部门用户清单
     * @param       $department_id
     * @param array $options
     * @param bool  $raw
     *
     * @return array|mixed
     * @static
     * @since  2019.05.16
     * @see https://open-doc.dingtalk.com/microapp/serverapi2/ege851
     */
    public static function getSimpleList($department_id, array $options = [], bool $raw = false)
    {
        $ret = ['code' => -1, 'msg' => '',];
        if ($department_id) {
            $params = ['access_token' => '', 'department_id' => $department_id,];
            $params = self::buildOptionsArray($params, $options);
            $url = self::buildOperateUrl('user/simplelist', $params);
            $data = self::doRequest($url, [], 'GET', 'userlist', $raw);
            $ret = self::outAry($ret, $data, $raw);
        }
        return $ret;
    }

    /**
     * 获取部门成员详细信息列表
     * @param       $department_id
     * @param int   $offset
     * @param int   $size
     * @param array $options
     * @param bool  $raw
     *
     * @return array|mixed
     * @static
     * @since  2019.05.16
     * @see https://open-doc.dingtalk.com/microapp/serverapi2/ege851
     */
    public static function getDepartmentDetailList(
        $department_id,
        $offset = 0,
        $size = 100,
        array $options = [],
        bool $raw = false
    ) {
        $ret = ['code' => -1, 'msg' => '',];
        if ($department_id) {
            $options['offset'] = $offset;
            $options['size'] = $size;
            $params = ['access_token' => '', 'department_id' => $department_id,];
            $params = self::buildOptionsArray($params, $options);
            $url = self::buildOperateUrl('user/listbypage', $params);
            $data = self::doRequest($url, [], 'GET', 'userlist', $raw);
            $ret = self::outAry($ret, $data, $raw);
        }
        return $ret;
    }

    /**
     * 获取管理员列表
     * @param bool $raw
     *
     * @return array|mixed
     * @static
     * @since  2019.05.16
     * @see https://open-doc.dingtalk.com/microapp/serverapi2/ege851
     */
    public static function getAdminList($raw = false)
    {
        $url = self::buildOperateUrl('user/get_admin', ['access_token' => '',]);
        $data = self::doRequest($url, [], 'GET', 'admin_list', $raw);
        $ret = self::outAry([], $data, $raw);
        return $ret;
    }

    /**
     * 获取用户可管理的部门列表ID
     * @param      $userid
     * @param bool $raw
     *
     * @return array|mixed
     * @static
     * @since  2019.05.16
     */
    public static function getAdminScope($userid, $raw = false)
    {
        $ret = ['code' => -1, 'msg' => '',];
        if ($userid) {
            $params = ['access_token' => '', 'userid' => $userid,];
            $url = self::buildOperateUrl('topapi/user/get_admin_scope', $params);
            $data = self::doRequest($url, [], 'GET', 'dept_ids', $raw);
            $ret = self::outAry($ret, $data, $raw);
        }
        return $ret;
    }

    /**
     * 根据unionid获取userid
     * @param      $unionid
     * @param bool $raw
     *
     * @return string|mixed
     * @static
     * @since  2019.05.16
     * @see https://open-doc.dingtalk.com/microapp/serverapi2/ege851
     */
    public static function getUseridByUnionid($unionid, $raw = false)
    {
        $ret = '';
        if ($unionid) {
            $params = ['access_token' => '', 'unionid' => $unionid,];
            $url = self::buildOperateUrl('user/getUseridByUnionid', $params);
            $data = self::doRequest($url, [], 'GET', 'userid', $raw);
            $ret = self::outAry($ret, $data, $raw);
        }
        return $ret;
    }

    /**
     * 创建用户，成功返回userid，错误返回原始错误消息数组
     * @param mixed $data 允许为UserInfo对象或JSON串或者数组
     * @param bool  $raw
     *
     * @return array
     * @static
     * @since  2019.05.16
     */
    public static function create($data, $raw = false)
    {
        $ret = ['code' => -1, 'msg' => '',];
        if ($data instanceof UserInfo) {
            $data = $data->getArray();
        } elseif (is_string($data)) {
            $data = json_decode($data, true);
        } elseif (!is_array($data)) {
            $data = [];
        }
        if ($data) {
            //必填项
            $chklist = ['name', 'department', 'mobile',];
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
                $url = self::buildOperateUrl('user/create', ['access_token' => '',]);
                $data = self::doRequest($url, json_encode($data, JSON_UNESCAPED_UNICODE), 'POST', 'userid', $raw);
                $ret = self::outAry($ret, $data, $raw);
            }
        }
        return $ret;
    }

    /**
     * 更新用户数据
     * @param mixed $data 允许为UserInfo对象或JSON串或者数组
     * @param bool  $raw
     *
     * @return array
     * @static
     * @since  2019.05.16
     */
    public static function update($data, $raw = false)
    {
        $ret = ['code' => -1, 'msg' => '',];
        if ($data instanceof UserInfo) {
            $data = $data->getUpdateData();
        } elseif (is_string($data)) {
            $data = json_decode($data, true);
        } elseif (!is_array($data)) {
            $data = [];
        }
        if ($data) {
            if (count($data) > 1 && isset($data['userid']) && is_string($data['userid']) && $data['userid']) {
                $url = self::buildOperateUrl('user/update', ['access_token' => '',]);
                $data = self::doRequest($url, json_encode($data, JSON_UNESCAPED_UNICODE), 'POST', '', $raw);
                $ret = self::outAry($ret, $data, $raw);
            } else {
                $code = 1;
                $msg = '没有需要更新的字段或者缺少更新用户标识';
                $ret = ['code' => $code, 'msg' => $msg,];
            }
        }
        return $ret;
    }

    /**
     * 删除用户
     * @param $userid
     * @param bool $raw
     *
     * @return array
     * @static
     * @since  2019.05.16
     */
    public static function delete(string $userid, $raw = false)
    {
        $ret = ['code' => -1, 'msg' => '',];
        if ($userid && is_string($userid)) {
            $url = self::buildOperateUrl('user/delete', ['access_token' => '', 'userid' => $userid,]);
            $data = self::doRequest($url, [], 'GET', '', $raw);
            $ret = self::outAry($ret, $data, $raw);
        }
        return $ret;
    }
}
