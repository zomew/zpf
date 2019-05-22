<?php
/**
 * Created by PhpStorm.
 * User: Jamers
 * Date: 2019/5/22
 * Time: 17:04
 * File: Cls_Robot.php
 */
namespace ZF\DingTalk;

/**
 * 阿里钉钉自定义WebHook机器人相关接口封装
 *
 * @package ZF\DingTalk
 * @author  Jamers <jamersnox@zomew.net>
 * @license https://opensource.org/licenses/GPL-3.0 GPL
 * @since   2019.05.22
 */
class Robot extends \ZF\DingTalk
{
    /**
     * @var bool 自动发送标头
     */
    public static $autoSendHeader = true;

    public static function sendMsg($token, $msg, $raw = false)
    {
        if ($msg instanceof MessageInfo || $msg instanceof RobotInfo) {
            $msg = $msg->__toString();
        } elseif (is_array($msg)) {
            $msg = json_encode($msg, JSON_UNESCAPED_UNICODE);
        } elseif (!is_string($msg)) {
            $msg = '';
        }
        $ret = ['code' => -1, 'msg' => '',];
        if ($token && $msg) {
            $url = self::buildOperateUrl('robot/send', ['access_token' => $token,]);
            $data = self::doRequest($url, $msg, 'POST', 'records', $raw);
            $ret = self::outAry($ret, $data, $raw);
        }
        return $ret;
    }
}
