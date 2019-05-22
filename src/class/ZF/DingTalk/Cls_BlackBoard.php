<?php
/**
 * Created by PhpStorm.
 * User: Jamers
 * Date: 2019/5/22
 * Time: 15:51
 * File: Cls_BlackBoard.php
 */
namespace ZF\DingTalk;

/**
 * 阿里钉钉公告相关接口封装
 *
 * @package ZF\DingTalk
 * @author  Jamers <jamersnox@zomew.net>
 * @license https://opensource.org/licenses/GPL-3.0 GPL
 * @since   2019.05.22
 * @see     https://open-doc.dingtalk.com/microapp/serverapi2/knmd16
 */
class BlackBoard extends \ZF\DingTalk
{
    /**
     * @var bool 自动发送标头
     */
    public static $autoSendHeader = true;

    /**
     * 获取指定用户当前可见的10条公告信息
     * @param      $userid
     * @param bool $raw
     *
     * @return array
     * @static
     * @since  2019.05.22
     */
    public static function listTopTen($userid, $raw = false)
    {
        $ret = ['code' => -1, 'msg' => '',];
        if ($userid) {
            $data = ['userid' => $userid,];
            $url = self::buildOperateUrl('topapi/blackboard/listtopten', ['access_token' => '',]);
            $data = self::doRequest($url, json_encode($data), 'POST', 'blackboard_list', $raw);
            $ret = self::outAry($ret, $data, $raw);
        }
        return $ret;
    }
}
