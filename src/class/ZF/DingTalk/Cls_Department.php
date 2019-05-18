<?php
/**
 * Created by PhpStorm.
 * User: Jamers
 * Date: 2019/5/17
 * Time: 8:52
 * File: Cls_Department.php
 */

namespace ZF\DingTalk;

use \ZF\DingTalk\DepartmentInfo;

/**
 * 部门相关类封装
 *
 * @package ZF\DingTalk
 * @author  Jamers <jamersnox@zomew.net>
 * @license https://opensource.org/licenses/GPL-3.0 GPL
 * @since   2019.05.17
 * @see https://open-doc.dingtalk.com/microapp/serverapi2/dubakq
 */
class Department extends \ZF\DingTalk
{
    /**
     * 获取子部门列表
     * @param int  $dept_id
     * @param bool $raw
     *
     * @return array|mixed
     * @static
     * @since  2019.05.17
     */
    public static function getSubDeptIdList($dept_id = 0, $raw = false)
    {
        $ret = ['code' => -1, 'msg' => '',];
        if ($dept_id > 0) {
            $url = self::buildOperateUrl('department/list_ids', ['access_token' => '', 'id' => $dept_id,]);
            $data = self::doRequest($url, [], 'GET', 'sub_dept_id_list', $raw);
            $ret = self::outAry($ret, $data, $raw);
        }
        return $ret;
    }

    /**
     * 获取部分列表
     * @param int    $dept_id
     * @param bool   $child
     * @param string $lang
     * @param bool   $raw
     *
     * @return array|mixed
     * @static
     * @since  2019.05.17
     */
    public static function getList($dept_id = 1, $child = false, $lang = 'zh_CN', $raw = false)
    {
        $ret = ['code' => -1, 'msg' => '',];
        if ($dept_id > 0) {
            if ($lang == '') {
                $lang = 'zh_CN';
            }
            $params = ['access_token' => '', 'id' => $dept_id, 'fetch_child' => $child, 'lang' => $lang,];
            $url = self::buildOperateUrl('department/list', $params);
            $data = self::doRequest($url, [], 'GET', 'department', $raw);
            $ret = self::outAry($ret, $data, $raw);
        }
        return $ret;
    }

    /**
     * 获取部门信息
     * @param        $dept_id
     * @param string $lang
     * @param bool   $raw
     *
     * @return array|mixed
     * @static
     * @since  2019.05.17
     */
    public static function getDeptInfo($dept_id, $lang = 'zh_CN', $raw = false)
    {
        $ret = ['code' => -1, 'msg' => '',];
        if ($dept_id > 0) {
            if ($lang == '') {
                $lang = 'zh_CN';
            }
            $url = self::buildOperateUrl('department/get', ['access_token' => '', 'id' => $dept_id, 'lang' => $lang,]);
            $data = self::doRequest($url, [], 'GET', '', $raw);
            $ret = self::outAry($ret, $data, $raw);
        }
        return $ret;
    }

    /**
     * 获取部门上级部门ID路径（至根）
     * @param      $dept_id
     * @param bool $raw
     *
     * @return array|mixed
     * @static
     * @since  2019.05.17
     */
    public static function getParentDeptList($dept_id, $raw = false)
    {
        $ret = ['code' => -1, 'msg' => '',];
        if ($dept_id > 0) {
            $url = self::buildOperateUrl(
                'department/list_parent_depts_by_dept',
                ['access_token' => '', 'id' => $dept_id,]
            );
            $data = self::doRequest($url, [], 'GET', 'parentIds', $raw);
            $ret = self::outAry($ret, $data, $raw);
        }
        return $ret;
    }

    /**
     * 获取用户上级部门列表（至根）
     * @param string $userid
     * @param bool   $raw
     *
     * @return array
     * @static
     * @since  2019.05.17
     */
    public static function getUserParentDeptList(string $userid, $raw = false)
    {
        $ret = ['code' => -1, 'msg' => '',];
        if ($userid) {
            $url = self::buildOperateUrl('department/list_parent_depts', ['access_token' => '', 'userId' => $userid,]);
            $data = self::doRequest($url, [], 'GET', 'department', $raw);
            $ret = self::outAry($ret, $data, $raw);
        }
        return $ret;
    }

    /**
     * 获取企业用户数量
     * @param int  $onlyActive 0 包含未激活钉钉用户数 1 激活钉钉用户数
     * @param bool $raw
     *
     * @return array
     * @static
     * @since  2019.05.17
     */
    public static function getOrgUserCount($onlyActive = 0, $raw = false)
    {
        $params = ['access_token' => '', 'onlyActive' => intval($onlyActive),];
        $url = self::buildOperateUrl('user/get_org_user_count', $params);
        $data = self::doRequest($url, [], 'GET', 'count', $raw);
        return self::outAry([], $data, $raw);
    }

    /**
     * 创建部门
     * @param $data
     * @param bool $raw
     *
     * @return array
     * @static
     * @since  2019.05.17
     */
    public static function create($data, $raw = false)
    {
        $ret = ['code' => -1, 'msg' => '',];
        if ($data instanceof DepartmentInfo) {
            $data = $data->getArray();
        } elseif (is_string($data)) {
            $data = json_decode($data, true);
        } elseif (!is_array($data)) {
            $data = [];
        }
        if ($data) {
            //必填项
            $chklist = ['name', 'parentid',];
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
                $url = self::buildOperateUrl('department/create', ['access_token' => '',]);
                $data = self::doRequest($url, json_encode($data, JSON_UNESCAPED_UNICODE), 'POST', 'id', $raw);
                $ret = self::outAry($ret, $data, $raw);
            }
        }
        return $ret;
    }

    /**
     * 更新部门信息
     * @param $data
     * @param bool $raw
     *
     * @return array
     * @static
     * @since  2019.05.17
     */
    public static function update($data, $raw = false)
    {
        $ret = ['code' => -1, 'msg' => '',];
        if ($data instanceof DepartmentInfo) {
            $data = $data->getUpdateData();
        } elseif (is_string($data)) {
            $data = json_decode($data, true);
        } elseif (!is_array($data)) {
            $data = [];
        }
        if ($data) {
            if (count($data) > 1 && isset($data['id']) && is_string($data['id']) && $data['id']) {
                $url = self::buildOperateUrl('department/update', ['access_token' => '',]);
                $data = self::doRequest($url, json_encode($data, JSON_UNESCAPED_UNICODE), 'POST', 'id', $raw);
                $ret = self::outAry([], $data, $raw);
            } else {
                $msg = '没有需要更新的字段或者缺少更新用户标识';
                $ret = ['code' => 1, 'msg' => $msg,];
            }
        }
        return $ret;
    }

    /**
     * 删除部门
     * @param mixed $dept_id
     * @param bool  $raw
     *
     * @return array
     * @static
     * @since  2019.05.17
     */
    public static function delete($dept_id, $raw = false)
    {
        $ret = ['code' => -1, 'msg' => '',];
        if ($dept_id) {
            $url = self::buildOperateUrl('department/delete', ['access_token' => '', 'id' => $dept_id,]);
            $data = self::doRequest($url, $ret, 'GET', '', $raw);
            $ret = self::outAry([], $data, $raw);
        }
        return $ret;
    }
}
