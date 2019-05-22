<?php
/**
 * Created by PhpStorm.
 * User: Jamers
 * Date: 2019/5/22
 * Time: 16:31
 * File: Cls_StepInfo.php
 */
namespace ZF\DingTalk;

/**
 * 阿里钉钉运行相关接口封装
 *
 * @package ZF\DingTalk
 * @author  Jamers <jamersnox@zomew.net>
 * @license https://opensource.org/licenses/GPL-3.0 GPL
 * @since   2019.05.22
 * @see     https://open-doc.dingtalk.com/microapp/serverapi2/emdh5f
 */
class StepInfo extends \ZF\DingTalk
{
    /**
     * 获取用户钉钉运行开启状态
     * @param      $userid
     * @param bool $raw
     *
     * @return array
     * @static
     * @since  2019.05.22
     */
    public static function getUserStatus($userid, $raw = false)
    {
        $ret = ['code' => -1, 'msg' => '',];
        if ($userid) {
            $url = self::buildOperateUrl('topapi/health/stepinfo/getuserstatus', ['access_token' => '', 'userid' => $userid,]);
            $data = self::doRequest($url, '', 'GET', 'status', $raw);
            $ret = self::outAry($ret, $data, $raw);
        }
        return $ret;
    }

    /**
     * 获取个人或部门指定日期的钉钉运动数据
     * @param       $type
     * @param       $object_id
     * @param array $stat_dates
     * @param bool  $raw
     *
     * @return array
     * @static
     * @since  2019.05.22
     */
    public static function getList($type, $object_id, $stat_dates = [], $raw = false)
    {
        $ret = ['code' => -1, 'msg' => '',];
        if ($object_id) {
            if ($stat_dates == []) {
                $stat_dates = [date('Ymd')];
            } elseif (is_array($stat_dates) && $stat_dates) {
                $tmp = [];
                foreach ($stat_dates as $v) {
                    if (!preg_match('/^\d{8}$/i', $v)) {
                        $tmp[] = date('Ymd', $v);
                    } else {
                        $tmp[] = strval($v);
                    }
                }
                $tmp = array_unique($tmp);
                sort($tmp);
                $stat_dates = implode(',', $tmp);
            }
            $data = ['type' => intval($type), 'object_id' => $object_id, 'stat_dates' => $stat_dates,];
            $url = self::buildOperateUrl('topapi/health/stepinfo/list', ['access_token' => '',]);
            $data = self::doRequest($url, $data, 'POST', 'stepinfo_list', $raw);
            $ret = self::outAry($ret, $data, $raw);
        }
        return $ret;
    }

    public static function getListByUserId($userids = [], $stat_date = 0, $raw = false)
    {
        $ret = ['code' => -1, 'msg' => '',];
        if ($userids) {
            if (is_array($userids)) {
                $userids = implode(',', $userids);
            }
            if (is_array($stat_date)) {
                if ($stat_date) {
                    $stat_date = array_shift($stat_date);
                } else {
                    $stat_date = 0;
                }
            }
            if ($stat_date == 0) {
                $stat_date = date('Ymd');
            } elseif (is_string($stat_date) && !preg_match('/^\d{8}$/i', $stat_date)) {
                $stat_date = date('Ymd', $stat_date);
            }
            $data = ['userids' => $userids, 'stat_date' => $stat_date,];
            $url = self::buildOperateUrl('topapi/health/stepinfo/listbyuserid', ['access_token' => '',]);
            $data = self::doRequest($url, $data, 'POST', 'stepinfo_list', $raw);
            $ret = self::outAry($ret, $data, $raw);
        }
        return $ret;
    }
}
