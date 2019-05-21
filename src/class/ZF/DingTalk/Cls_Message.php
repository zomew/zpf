<?php
/**
 * Created by PhpStorm.
 * User: Jamers
 * Date: 2019/5/15
 * Time: 16:30
 * File: Cls_Message.php
 */

namespace ZF\DingTalk;

use \ZF\Common;

/**
 * 消息发送接口封装
 *
 * @package ZF\DingTalk
 * @author  Jamers <jamersnox@zomew.net>
 * @license https://opensource.org/licenses/GPL-3.0 GPL
 * @since   2019.05.17
 */
class Message extends \ZF\DingTalk
{
    /**
     * 异步发送消息，返回task_id
     * @param array  $msg
     * @param string $useridlist
     * @param string $deptidlist
     * @param bool   $raw
     *
     * @return array
     * @static
     * @since  2019.05.15
     */
    public static function asyncSendV2($msg = [], $useridlist = '', $deptidlist = '', $raw = false)
    {
        $ret = ['errcode' => -1, 'errmsg' => '',];
        if ($msg && ($useridlist || $deptidlist)) {
            $config = self::getConfig();
            if (isset($config['AGENTID']) && $config['AGENTID']) {
                $url = self::buildOperateUrl('topapi/message/corpconversation/asyncsend_v2', ['access_token' => '',]);
                $params = ['agent_id' => $config['AGENTID'],];
                if (is_array($useridlist)) {
                    $useridlist = implode(',', $useridlist);
                }
                if (is_array($deptidlist)) {
                    $deptidlist = implode(',', $deptidlist);
                }
                if (strtoupper($useridlist) == 'ALL') {
                    $params['to_all_user'] = true;
                } elseif ($useridlist) {
                    $params['userid_list'] = $useridlist;
                }
                if ($deptidlist) {
                    $params['dept_id_list'] = $deptidlist;
                }
                if (is_array($msg)) {
                    $params['msg'] = json_encode($msg, JSON_UNESCAPED_UNICODE);
                } else {
                    $params['msg'] = $msg;
                }
                $data = self::doRequest($url, $params, 'POST', 'task_id', $raw);
                $ret = self::outAry($ret, $data, $raw);
            } else {
                trigger_error('AGENTID不能为空，请检查配置文件', E_USER_ERROR);
            }
        }
        return $ret;
    }

    /**
     * 查看消息发送进度
     * @param int  $task_id
     * @param bool $raw
     *
     * @return array
     * @static
     * @since  2019.05.20
     */
    public static function getSendProgress($task_id = 0, $raw = false)
    {
        $ret = ['code' => -1, 'msg' => '',];
        if ($task_id > 0) {
            $config = self::getConfig();
            $data = ['agent_id' => '', 'task_id' => $task_id,];
            if (isset($config['AGENTID'])) {
                $data['agent_id'] = $config['AGENTID'];
            }
            $url = self::buildOperateUrl('topapi/message/corpconversation/getsendprogress', ['access_token' => '',]);
            $data = self::doRequest($url, $data, 'POST', 'progress', $raw);
            $ret = self::outAry($ret, $data, $raw);
        }
        return $ret;
    }

    /**
     * 获取消息发送结果
     * @param int  $task_id
     * @param bool $raw
     *
     * @return array
     * @static
     * @since  2019.05.20
     */
    public static function getSendResult($task_id = 0, $raw = false)
    {
        $ret = ['code' => -1, 'msg' => '',];
        if ($task_id > 0) {
            $config = self::getConfig();
            $data = ['agent_id' => '', 'task_id' => $task_id,];
            if (isset($config['AGENTID'])) {
                $data['agent_id'] = $config['AGENTID'];
            }
            $url = self::buildOperateUrl('topapi/message/corpconversation/getsendresult', ['access_token' => '',]);
            $data = self::doRequest($url, $data, 'POST', 'send_result', $raw);
            $ret = self::outAry($ret, $data, $raw);
        }
        return $ret;
    }

    /**
     * 发送消息到群消息
     * @param string $sender
     * @param string $cid
     * @param string $msg
     * @param bool   $raw
     *
     * @return array
     * @static
     * @since  2019.05.20
     * @see    https://open-doc.dingtalk.com/microapp/serverapi2/pm0m06
     */
    public static function sendToConversation($sender = '', $cid = '', $msg = '', $raw = false)
    {
        $ret = ['code' => -1, 'msg' => '',];
        if ($sender && $cid && $msg) {
            if ($msg instanceof MessageInfo) {
                $msg = $msg->__toString();
            } elseif (is_array($msg)) {
                $msg = json_encode($msg, JSON_UNESCAPED_UNICODE);
            } elseif (!is_string($msg)) {
                $msg = '';
            }
            if ($msg) {
                $data = ['sender' => $sender, 'cid' => $cid, 'msg' => $msg,];
                $url = self::buildOperateUrl('message/send_to_conversation', ['access_token' => '',]);
                $data = self::doRequest($url, $data, 'POST', 'receiver', $raw);
                if (is_string($data)) {
                    $data = explode('|', $data);
                }
                $ret = self::outAry($ret, $data, $raw);
            } else {
                $ret = ['code' => 1, 'msg' => '所发送的消息格式有误',];
            }
        }
        return $ret;
    }
}
