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
            $ret = self::doRequest($url, [], 'GET', 'userid', $raw);
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
            $ret = self::doRequest($url, [], 'GET', 'userIds', $raw);
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
        $ret = [];
        if ($department_id) {
            $params = ['access_token' => '', 'department_id' => $department_id,];
            $params = self::buildOptionsArray($params, $options);
            $url = self::buildOperateUrl('user/simplelist', $params);
            $ret = self::doRequest($url, [], 'GET', 'userlist', $raw);
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
        $ret = [];
        if ($department_id) {
            $options['offset'] = $offset;
            $options['size'] = $size;
            $params = ['access_token' => '', 'department_id' => $department_id,];
            $params = self::buildOptionsArray($params, $options);
            $url = self::buildOperateUrl('user/listbypage', $params);
            $ret = self::doRequest($url, [], 'GET', 'userlist', $raw);
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
        $ret = self::doRequest($url, [], 'GET', 'admin_list', $raw);
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
        $ret = [];
        if ($userid) {
            $params = ['access_token' => '', 'userid' => $userid,];
            $url = self::buildOperateUrl('topapi/user/get_admin_scope', $params);
            $ret = self::doRequest($url, [], 'GET', 'dept_ids', $raw);
        }
        return $ret;
    }

    /**
     * 根据unionid获取userid
     * @param      $unionid
     * @param bool $raw
     *
     * @return array|mixed
     * @static
     * @since  2019.05.16
     * @see https://open-doc.dingtalk.com/microapp/serverapi2/ege851
     */
    public static function getUseridByUnionid($unionid, $raw = false)
    {
        $ret = [];
        if ($unionid) {
            $params = ['access_token' => '', 'unionid' => $unionid,];
            $url = self::buildOperateUrl('user/getUseridByUnionid', $params);
            $ret = self::doRequest($url, [], 'GET', 'userid', $raw);
        }
        return $ret;
    }
}