<?php
/**
 * Created by PhpStorm.
 * User: Jamers
 * Date: 2019/5/23
 * Time: 11:31
 * File: Cls_CallBack.php
 */

namespace ZF\DingTalk;

/**
 * 阿里钉钉回调管理相关接口封装
 *
 * @package ZF\DingTalk
 * @author  Jamers <jamersnox@zomew.net>
 * @license https://opensource.org/licenses/GPL-3.0 GPL
 * @since   2019.05.23
 * @see     https://open-doc.dingtalk.com/microapp/serverapi2/pwz3r5
 */
class CallBack extends \ZF\DingTalk
{
    /**
     * @var bool 自动发送标头
     */
    public static $autoSendHeader = true;

    /**
     * @var array 目前已知事件类型
     * @see https://open-doc.dingtalk.com/microapp/serverapi2/skn8ld
     */
    public static $tags = [
        //通讯录
        'user_add_org',     //通讯录用户增加
        'user_modify_org',  //通讯录用户更改
        'user_leave_org',   //通讯录用户离职
        'org_admin_add',    //通讯录用户被设为管理员
        'org_admin_remove', //通讯录用户被取消设置管理员
        'org_dept_create',  //通讯录企业部门创建
        'org_dept_modify',  //通讯录企业部门修改
        'org_dept_remove',  //通讯录企业部门删除
        'org_remove',       //企业被解散
        'org_change',       //企业信息发生变更
        'label_user_change',//员工角色信息发生变更
        'label_conf_add',   //增加角色或者角色组
        'label_conf_del',   //删除角色或者角色组
        'label_conf_modify',//修改角色或者角色组

        //群会话
        'chat_add_member',      //群会话添加人员
        'chat_remove_member',   //群会话删除人员
        'chat_quit',            //群会话用户主动退群
        'chat_update_owner',    //群会话更换群主
        'chat_update_title',    //群会话更换群名称
        'chat_disband',         //群会话解散群
        'chat_disband_microapp',//绑定了微应用的群会话，在解散时回调

        //签到
        'check_in',         //用户签到

        //审批
        'bpms_task_change',     //审批任务开始，结束，转交
        'bpms_instance_change', //审批实例开始，结束
    ];

    /**
     * 注册业务事件回调
     * @param array  $tag
     * @param string $url
     * @param string $token
     * @param string $aes
     * @param bool   $raw
     *
     * @return array
     * @static
     * @since  2019.05.23
     */
    public static function registerCallBack($tag = [], $url = '', $token = '', $aes = '', $raw = false)
    {
        $ret = ['code' => -1, 'msg' => '',];
        $config = self::getConfig();
        if ($token == '' && isset($config['TOKEN']) && $config['TOKEN']) {
            $token = $config['TOKEN'];
        }
        if ($aes == '' && isset($config['ENCODING_AES_KEY']) && $config['ENCODING_AES_KEY']) {
            $aes = $config['ENCODING_AES_KEY'];
        }
        if ($tag && $token && $aes && $url) {
            $data = [
                'call_back_tag' => $tag,
                'token' => $token,
                'aes_key' => $aes,
                'url' => $url,
            ];

            $url = self::buildOperateUrl('call_back/register_call_back', ['access_token' => '',]);
            $data = self::doRequest($url, json_encode($data), 'POST', '', $raw);
            $ret = self::outAry($ret, $data, $raw);
        }
        return $ret;
    }

    /**
     * 获取事件回调
     * @param bool $raw
     *
     * @return array
     * @static
     * @since  2019.05.23
     */
    public static function getCallBack($raw = false)
    {
        $ret = ['code' => -1, 'msg' => '',];
        $url = self::buildOperateUrl('call_back/get_call_back', ['access_token' => '',]);
        $data = self::doRequest($url, '', 'GET', '', $raw);
        $ret = self::outAry($ret, $data, $raw);
        return $ret;
    }

    /**
     * 更新事件回调
     * @param array  $tag
     * @param string $url
     * @param string $token
     * @param string $aes
     * @param bool   $raw
     *
     * @return array
     * @static
     * @since  2019.05.23
     */
    public static function updateCallBack($tag = [], $url = '', $token = '', $aes = '', $raw = false)
    {
        $ret = ['code' => -1, 'msg' => '',];
        $config = self::getConfig();
        if ($token == '' && isset($config['TOKEN']) && $config['TOKEN']) {
            $token = $config['TOKEN'];
        }
        if ($aes == '' && isset($config['ENCODING_AES_KEY']) && $config['ENCODING_AES_KEY']) {
            $aes = $config['ENCODING_AES_KEY'];
        }
        if ($tag && $token && $aes && $url) {
            $data = [
                'call_back_tag' => $tag,
                'token' => $token,
                'aes_key' => $aes,
                'url' => $url,
            ];

            $url = self::buildOperateUrl('call_back/update_call_back', ['access_token' => '',]);
            $data = self::doRequest($url, json_encode($data), 'POST', 'progress', $raw);
            $ret = self::outAry($ret, $data, $raw);
        }
        return $ret;
    }

    /**
     * 删除事件回调
     * @param bool $raw
     *
     * @return array
     * @static
     * @since  2019.05.23
     */
    public static function deleteCallBack($raw = false)
    {
        $ret = ['code' => -1, 'msg' => '',];
        $url = self::buildOperateUrl('call_back/delete_call_back', ['access_token' => '',]);
        $data = self::doRequest($url, '', 'GET', '', $raw);
        $ret = self::outAry($ret, $data, $raw);
        return $ret;
    }

    /**
     * 获取回调失败的结果
     * @param bool $raw
     *
     * @return array
     * @static
     * @since  2019.05.23
     */
    public static function getCallBackFailedResult($raw = false)
    {
        $ret = ['code' => -1, 'msg' => '',];
        $url = self::buildOperateUrl('call_back/get_call_back_failed_result', ['access_token' => '',]);
        $data = self::doRequest($url, '', 'GET', '', $raw);
        $ret = self::outAry($ret, $data, $raw);
        return $ret;
    }
}
