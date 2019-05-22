<?php
/**
 * Created by PhpStorm.
 * User: Jamers
 * Date: 2019/5/22
 * Time: 15:56
 * File: Cls_MicroApp.php
 */
namespace ZF\DingTalk;

/**
 * 阿里钉钉应用管理相关接口封装
 *
 * @package ZF\DingTalk
 * @author  Jamers <jamersnox@zomew.net>
 * @license https://opensource.org/licenses/GPL-3.0 GPL
 * @since   2019.05.22
 * @see     https://open-doc.dingtalk.com/microapp/serverapi2/zc304p
 */
class MicroApp extends \ZF\DingTalk
{
    /**
     * @var bool 自动发送标头
     */
    public static $autoSendHeader = true;

    /**
     * 获取应用列表
     * @param bool $raw
     *
     * @return array
     * @static
     * @since  2019.05.22
     */
    public static function getList($raw = false)
    {
        $url = self::buildOperateUrl('microapp/list', ['access_token' => '',]);
        $data = self::doRequest($url, '', 'POST', 'appList', $raw);
        $ret = self::outAry([], $data, $raw);
        return $ret;
    }

    /**
     * 获取指定用户可见应用列表
     * @param      $userid
     * @param bool $raw
     *
     * @return array
     * @static
     * @since  2019.05.22
     */
    public static function getListByUserId($userid, $raw = false)
    {
        $ret = ['code' => -1, 'msg' => '',];
        if ($userid) {
            $url = self::buildOperateUrl('microapp/list_by_userid', ['access_token' => '', 'userid' => $userid,]);
            $data = self::doRequest($url, '', 'GET', 'appList', $raw);
            $ret = self::outAry($ret, $data, $raw);
        }
        return $ret;
    }

    /**
     * 获取应用可见范围
     * @param int  $agentid
     * @param bool $raw
     *
     * @return array
     * @static
     * @since  2019.05.22
     */
    public static function getVisibleScopes($agentid = 0, $raw = false)
    {
        $ret = ['code' => -1, 'msg' => '',];
        if ($agentid > 0) {
            $data = ['agentId' => $agentid,];
            $url = self::buildOperateUrl('microapp/visible_scopes', ['access_token' => '',]);
            $data = self::doRequest($url, json_encode($data), 'POST', '', $raw);
            $ret = self::outAry($ret, $data, $raw);
        }
        return $ret;
    }

    /**
     * 设置应用可见范围
     * @param int   $agentid
     * @param null  $isHidden
     * @param array $deptVisibleScopes
     * @param array $userVisibleScopes
     * @param bool  $raw
     *
     * @return array
     * @static
     * @since  2019.05.22
     */
    public static function setVisibleScopes(
        $agentid = 0,
        $isHidden = null,
        $deptVisibleScopes = [],
        $userVisibleScopes = [],
        $raw = false
    ) {
        $ret = ['code' => -1, 'msg' => '',];
        if ($agentid > 0 && ($isHidden || $deptVisibleScopes || $userVisibleScopes)) {
            $data = ['agentId' => $agentid,];
            if ($isHidden != null) {
                $data['isHidden'] = boolval($isHidden);
            }
            if ($deptVisibleScopes) {
                if (is_string($deptVisibleScopes)) {
                    $deptVisibleScopes = explode(',', $deptVisibleScopes);
                }
                if (is_array($deptVisibleScopes)) {
                    $data['deptVisibleScopes'] = $deptVisibleScopes;
                }
            }
            if ($userVisibleScopes) {
                if (is_string($userVisibleScopes)) {
                    $userVisibleScopes = explode(',', $userVisibleScopes);
                }
                if (is_array($userVisibleScopes)) {
                    $data['userVisibleScopes'] = $userVisibleScopes;
                }
            }
            $url = self::buildOperateUrl('microapp/set_visible_scopes', ['access_token' => '',]);
            $data = self::doRequest($url, json_encode($data), 'POST', '', $raw);
            $ret = self::outAry($ret, $data, $raw);
        }
        return $ret;
    }
}
