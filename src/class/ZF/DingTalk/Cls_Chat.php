<?php
/**
 * Created by PhpStorm.
 * User: Jamers
 * Date: 2019/5/20
 * Time: 13:40
 * File: Cls_Chat.php
 */

namespace ZF\DingTalk;

use \ZF\DingTalk\MessageInfo;
use \ZF\DingTalk\ChatInfo;

/**
 * 群消息相关接口封装
 *
 * @package ZF\DingTalk
 * @author  Jamers <jamersnox@zomew.net>
 * @license https://opensource.org/licenses/GPL-3.0 GPL
 * @since   2019.05.20
 */
class Chat extends \ZF\DingTalk
{
    /**
     * @var array 发送标头
     */
    public static $header = ['Content-Type' => 'application/json',];

    /**
     * 发送群消息
     * @param string $chat_id
     * @param mixed $msg
     * @param bool   $raw
     *
     * @return array
     * @static
     * @since  2019.05.20
     */
    public static function send($chat_id = '', $msg = '', $raw = false)
    {
        $ret = ['code' => -1, 'msg' => '',];
        if ($chat_id && $msg) {
            if ($msg instanceof MessageInfo) {
                $msg = $msg->__toString();
            } elseif (is_array($msg)) {
                $msg = json_encode($msg, JSON_UNESCAPED_UNICODE);
            } elseif (!is_string($msg)) {
                $ret = ['code' => 1, 'msg' => '发送消息格式有误',];
                $msg = '';
            }
            if ($msg) {
                $data = ['chatid' => $chat_id, 'msg' => $msg,];
                $url = self::buildOperateUrl('chat/send', ['access_token' => '',]);
                $data = self::doRequest($url, $data, 'POST', 'messageId', $raw);
                $ret = self::outAry($ret, $data, $raw);
            }
        }
        return $ret;
    }

    /**
     * 获取群消息已读列表
     * @param string $messageId
     * @param int    $cursor
     * @param int    $size
     * @param bool   $raw
     *
     * @return array
     * @static
     * @since  2019.05.20
     */
    public static function getReadList($messageId = '', $cursor = 0, $size = 100, $raw = false)
    {
        $ret = ['code' => -1, 'msg' => '',];
        if ($messageId) {
            if ($cursor < 0) {
                $cursor = 0;
            }
            if ($size <= 0) {
                $size = 1;
            }
            if ($size > 100) {
                $size = 100;
            }
            $params = ['access_token' => '', 'messageId' => $messageId, 'cursor' => $cursor, 'size' => $size,];
            $url = self::buildOperateUrl('chat/getReadList', $params);
            $data = self::doRequest($url, [], 'GET', '', $raw);
            $ret = self::outAry($ret, $data, $raw);
        }
        return $ret;
    }

    /**
     * 创建群对话
     * @param      $data
     * @param bool $raw
     *
     * @return array
     * @static
     * @since  2019.05.20
     */
    public static function create($data, $raw = false)
    {
        $ret = ['code' => -1, 'msg' => '',];
        if ($data) {
            if ($data instanceof ChatInfo) {
                $data = $data->__toString();
            } elseif (is_array($data)) {
                $data = json_encode($data, JSON_UNESCAPED_UNICODE);
            } elseif (!is_string($data)) {
                $ret = ['code' => 1, 'msg' => '发送消息格式有误',];
                $data = '';
            }
            if ($data) {
                $url = self::buildOperateUrl('chat/create', ['access_token' => '',]);
                $data = self::doRequest($url, $data, 'POST', 'chatid', $raw, self::buildHeader(self::$header));
                $ret = self::outAry($ret, $data, $raw);
            }
        }
        return $ret;
    }

    /**
     * 更新群对话消息
     * @param      $data
     * @param bool $raw
     *
     * @return array
     * @static
     * @since  2019.05.20
     */
    public static function update($data, $raw = false)
    {
        $ret = ['code' => -1, 'msg' => '',];
        if ($data instanceof ChatInfo) {
            $data = $data->getUpdateData();
        } elseif (is_string($data)) {
            $data = json_decode($data, true);
        } elseif (!is_array($data)) {
            $data = [];
        }
        if ($data) {
            if (count($data) > 1 && isset($data['chatid']) && is_string($data['chatid']) && $data['chatid']) {
                $url = self::buildOperateUrl('chat/update', ['access_token' => '',]);
                $data = self::doRequest($url, json_encode($data, JSON_UNESCAPED_UNICODE), 'POST', '', $raw, self::buildHeader(self::$header));
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
     * 获取群对话信息
     * @param string $chatid
     * @param bool   $raw
     *
     * @return array
     * @static
     * @since  2019.05.20
     */
    public static function get($chatid = '', $raw = false)
    {
        $ret = ['code' => -1, 'msg' => '',];
        if ($chatid) {
            $params = ['access_token' => '', 'chatid' => $chatid,];
            $url = self::buildOperateUrl('chat/get', $params);
            $data = self::doRequest($url, [], 'GET', 'chat_info', $raw, self::buildHeader(self::$header));
            $ret = self::outAry($ret, $data, $raw);
        }
        return $ret;
    }
}
