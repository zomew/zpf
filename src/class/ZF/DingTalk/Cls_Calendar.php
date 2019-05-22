<?php
/**
 * Created by PhpStorm.
 * User: Jamers
 * Date: 2019/5/22
 * Time: 10:12
 * File: Cls_Calendar.php
 */
namespace ZF\DingTalk;

/**
 * 阿里钉钉日程相关接口封装
 *
 * @package ZF\DingTalk
 * @author  Jamers <jamersnox@zomew.net>
 * @license https://opensource.org/licenses/GPL-3.0 GPL
 * @since   2019.05.22
 * @see     https://open-doc.dingtalk.com/microapp/serverapi2/iqel76
 */
class Calendar extends \ZF\DingTalk
{
    /**
     * 添加日程
     * @param      $data
     * @param bool $raw
     *
     * @return array
     * @static
     * @since  2019.05.22
     */
    public static function create($data, $raw = false)
    {
        $ret = ['code' => -1, 'msg' => '',];
        if ($data instanceof CalendarInfo) {
            $data = $data->getArray();
        } elseif (is_string($data)) {
            $data = json_decode($data, true);
        } elseif (!is_array($data)) {
            $data = [];
        }
        if ($data) {
            //必填项
            $chklist = ['summary', 'receiver_userids', 'end_time', 'calendar_type',
                'start_time', 'source', 'uuid', 'biz_id',];
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
                $url = self::buildOperateUrl('topapi/calendar/create', ['access_token' => '',]);
                $data = self::doRequest($url, "create_vo=" . json_encode($data), 'POST', 'result', $raw);
                $ret = self::outAry($ret, $data, $raw);
            }
        }
        return $ret;
    }
}
