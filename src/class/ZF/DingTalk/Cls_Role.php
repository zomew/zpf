<?php
/**
 * Created by PhpStorm.
 * User: Jamers
 * Date: 2019/5/17
 * Time: 17:18
 * File: Cls_Role.php
 */

namespace ZF\DingTalk;

use \ZF\Common;

/**
 * 阿里钉钉角色管理模块封装
 *
 * @package ZF\DingTalk
 * @author  Jamers <jamersnox@zomew.net>
 * @license https://opensource.org/licenses/GPL-3.0 GPL
 * @since   2019.05.17
 */
class Role extends \ZF\DingTalk
{
    /**
     * 获取角色列表
     * @param int  $size
     * @param int  $offset
     * @param bool $raw
     *
     * @return array
     * @static
     * @since  2019.05.17
     */
    public static function getList($size = 0, $offset = -1, $raw = false)
    {
        $ret = ['code' => -1, 'msg' => '',];
        $data = [];
        if ($size > 0) {
            $data['size'] = $size;
        }
        if ($offset >= 0) {
            $data['offset'] = $offset;
        }
        $url = self::buildOperateUrl('topapi/role/list', ['access_token' => '',]);
        $data = self::doRequest($url, $data, 'POST', 'result', $raw);
        $ret = self::outAry($ret, $data, $raw);
        return $ret;
    }

    /**
     * 获取角色下用户列表
     * @param int  $role_id
     * @param int  $size
     * @param int  $offset
     * @param bool $raw
     *
     * @return array
     * @static
     * @since  2019.05.17
     */
    public static function getSimpleList($role_id = 0, $size = 0, $offset = -1, $raw = false)
    {
        $ret = ['code' => -1, 'msg' => '',];
        if ($role_id > 0) {
            $data = ['role_id' => $role_id,];
            if ($size > 0) {
                $data['size'] = $size;
            }
            if ($offset >= 0) {
                $data['offset'] = $offset;
            }
            $url = self::buildOperateUrl('topapi/role/simplelist', ['access_token' => '',]);
            $data = self::doRequest($url, $data, 'POST', 'result', $raw);
            $ret = self::outAry($ret, $data, $raw);
        }
        return $ret;
    }

    /**
     * 获取角色组数据
     * @param int  $group_id
     * @param bool $raw
     *
     * @return array
     * @static
     * @since  2019.05.17
     */
    public static function getRoleGroup($group_id = 0, $raw = false)
    {
        $ret = ['code' => -1, 'msg' => '',];
        if ($group_id > 0) {
            $data = ['group_id' => $group_id,];
            $url = self::buildOperateUrl('topapi/role/getrolegroup', ['access_token' => '',]);
            $data = self::doRequest($url, $data, 'POST', 'role_group', $raw);
            $ret = self::outAry($ret, $data, $raw);
        }
        return $ret;
    }

    /**
     * 获取角色信息
     * @param int  $role_id
     * @param bool $raw
     *
     * @return array
     * @static
     * @since  2019.05.17
     */
    public static function getRole($role_id = 0, $raw = false)
    {
        $ret = ['code' => -1, 'msg' => '',];
        if ($role_id > 0) {
            $data = ['roleId' => $role_id,];
            $url = self::buildOperateUrl('topapi/role/getrole', ['access_token' => '',]);
            $data = self::doRequest($url, $data, 'POST', 'role_group', $raw);
            $ret = self::outAry($ret, $data, $raw);
        }
        return $ret;
    }

    /**
     * 添加角色
     * @param string $roleName
     * @param int    $groupId
     *
     * @return array
     * @static
     * @since  2019.05.17
     */
    public static function addRole($roleName = '', $groupId = 0)
    {
        $ret = ['code' => -1, 'msg' => '',];
        if ($roleName && $groupId > 0) {
            $data = ['roleName' => $roleName, 'groupId' => $groupId,];
            $url = self::buildOperateUrl('topapi/role/add_role', ['access_token' => '',]);
            $data = self::doRequest($url, $data, 'POST', 'roleId', $raw);
            $ret = self::outAry($ret, $data, $raw);
        }
        return $ret;
    }

    /**
     * 更新角色数据
     * @param string $roleName
     * @param int    $roleId
     *
     * @return array
     * @static
     * @since  2019.05.17
     */
    public static function updateRole($roleName = '', $roleId = 0)
    {
        $ret = ['code' => -1, 'msg' => '',];
        if ($roleName && $roleId > 0) {
            $data = ['roleName' => $roleName, 'roleId' => $roleId,];
            $url = self::buildOperateUrl('topapi/role/update_role', ['access_token' => '',]);
            $data = self::doRequest($url, $data, 'POST', '', $raw);
            $ret = self::outAry($ret, $data, $raw);
        }
        return $ret;
    }

    /**
     * 删除角色
     * @param int $roleId
     *
     * @return array
     * @static
     * @since  2019.05.17
     */
    public static function deleteRole($roleId = 0)
    {
        $ret = ['code' => -1, 'msg' => '',];
        if ($roleId > 0) {
            $data = [ 'role_id' => $roleId,];
            $url = self::buildOperateUrl('topapi/role/deleterole', ['access_token' => '',]);
            $data = self::doRequest($url, $data, 'POST', '', $raw);
            $ret = self::outAry($ret, $data, $raw);
        }
        return $ret;
    }

    /**
     * 添加角色组
     * @param string $name
     *
     * @return array
     * @static
     * @since  2019.05.17
     */
    public static function addRoleGroup($name = '')
    {
        $ret = ['code' => -1, 'msg' => '',];
        if ($name) {
            $data = [ 'name' => $name,];
            $url = self::buildOperateUrl('role/add_role_group', ['access_token' => '',]);
            $data = self::doRequest($url, $data, 'POST', 'groupId', $raw);
            $ret = self::outAry($ret, $data, $raw);
        }
        return $ret;
    }

    /**
     * 批量增加员工角色
     * @param array $roleIds
     * @param array $userIds
     *
     * @return array
     * @static
     * @since  2019.05.17
     */
    public static function addRolesForeMps($roleIds = [], $userIds = [])
    {
        $ret = ['code' => -1, 'msg' => '',];
        if ($roleIds && $userIds) {
            $data = [ 'roleIds' => implode(',', $roleIds), 'userIds' => implode(',', $userIds),];
            $url = self::buildOperateUrl('topapi/role/addrolesforemps', ['access_token' => '',]);
            $data = self::doRequest($url, $data, 'POST', '', $raw);
            $ret = self::outAry($ret, $data, $raw);
        }
        return $ret;
    }

    /**
     * 批量删除员工角色
     * @param array $roleIds
     * @param array $userIds
     *
     * @return array
     * @static
     * @since  2019.05.17
     */
    public static function delRolesForeMps($roleIds = [], $userIds = [])
    {
        $ret = ['code' => -1, 'msg' => '',];
        if ($roleIds && $userIds) {
            $data = [ 'roleIds' => implode(',', $roleIds), 'userIds' => implode(',', $userIds),];
            $url = self::buildOperateUrl('topapi/role/removerolesforemps', ['access_token' => '',]);
            $data = self::doRequest($url, $data, 'POST', '', $raw);
            $ret = self::outAry($ret, $data, $raw);
        }
        return $ret;
    }
}
