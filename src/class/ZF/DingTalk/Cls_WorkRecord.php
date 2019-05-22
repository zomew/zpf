<?php
/**
 * Created by PhpStorm.
 * User: Jamers
 * Date: 2019/5/21
 * Time: 16:39
 * File: Cls_WorkRecord.php
 */

namespace ZF\DingTalk;

/**
 * 阿里钉钉待办事项相关接口封装
 *
 * @package ZF\DingTalk
 * @author  Jamers <jamersnox@zomew.net>
 * @license https://opensource.org/licenses/GPL-3.0 GPL
 * @since   2019.05.21
 */
class WorkRecord extends \ZF\DingTalk
{
    /**
     * @var bool 自动发送标头
     */
    public static $autoSendHeader = true;

    /**
     * 添加待办事项
     * @param        $userid
     * @param int    $create_time
     * @param string $title
     * @param string $url
     * @param array  $form_list
     * @param bool   $raw
     *
     * @return array
     * @static
     * @since  2019.05.22
     */
    public static function add($userid, $create_time = 0, $title = '', $url = '', $form_list = [], $raw = false)
    {
        $ret = ['code' => -1, 'msg' => '',];
        if ($userid && $title && $form_list) {
            if ($create_time <= 0) {
                $create_time = time() * 1000;
            }
            if (strlen(strval($create_time)) <= 12) {
                $create_time *= 1000;
            }
            $data = ['userid' => $userid, 'create_time' => $create_time, 'title' => $title,
                'url' => $url, 'formItemList' => $form_list,];
            $url = self::buildOperateUrl('topapi/workrecord/add', ['access_token' => '',]);
            $data = self::doRequest($url, json_encode($data), 'POST', 'record_id', $raw);
            $ret = self::outAry($ret, $data, $raw);
        }
        return $ret;
    }

    /**
     * 获取用户待办事项列表
     * @param      $userid
     * @param int  $status
     * @param int  $offset
     * @param int  $limit
     * @param bool $raw
     *
     * @return array
     * @static
     * @since  2019.05.22
     */
    public static function getByUserId($userid, $status = 0, $offset = 0, $limit = 50, $raw = false)
    {
        $ret = ['code' => -1, 'msg' => '',];
        if ($userid) {
            $data = ['userid' => $userid, 'offset' => $offset, 'limit' => $limit, 'status' => $status,];
            $url = self::buildOperateUrl('topapi/workrecord/getbyuserid', ['access_token' => '',]);
            $data = self::doRequest($url, json_encode($data), 'POST', 'records', $raw);
            $ret = self::outAry($ret, $data, $raw);
        }
        return $ret;
    }

    /**
     * 将用户待办事项置为已完成
     * @param      $userid
     * @param      $record_id
     * @param bool $raw
     *
     * @return array
     * @static
     * @since  2019.05.22
     */
    public static function update($userid, $record_id, $raw = false)
    {
        $ret = ['code' => -1, 'msg' => '',];
        if ($userid && $record_id) {
            $data = ['userid' => $userid, 'record_id' => $record_id,];
            $url = self::buildOperateUrl('topapi/workrecord/update', ['access_token' => '',]);
            $data = self::doRequest($url, json_encode($data), 'POST', 'result', $raw);
            $ret = self::outAry($ret, $data, $raw);
        }
        return $ret;
    }
}
