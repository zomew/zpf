<?php
/**
 * Created by PhpStorm.
 * User: Jamers
 * Date: 2019/5/18
 * Time: 10:56
 * File: Cls_ExtContact.php
 */

namespace ZF\DingTalk;

use \ZF\DingTalk\ExtContactInfo;

/**
 * 阿里钉钉外部联系人类封装
 *
 * @package ZF\DingTalk
 * @author  Jamers <jamersnox@zomew.net>
 * @license https://opensource.org/licenses/GPL-3.0 GPL
 * @since   2019.05.18
 */
class ExtContact extends \ZF\DingTalk
{
    /**
     * 获取外部联系人标签组列表
     * @param int  $size
     * @param int  $offset
     * @param bool $raw
     *
     * @return array
     * @static
     * @since  2019.05.18
     */
    public static function getLabelList($size = 0, $offset = -1, $raw = false)
    {
        $ret = ['code' => -1, 'msg' => '',];
        $data = [];
        if ($size > 0) {
            $data['size'] = $size;
        }
        if ($offset >= 0) {
            $data['offset'] = $offset;
        }
        $url = self::buildOperateUrl('topapi/extcontact/listlabelgroups', ['access_token' => '',]);
        $data = self::doRequest($url, $data, 'POST', 'results', $raw);
        $ret = self::outAry($ret, $data, $raw);
        return $ret;
    }

    /**
     * 获取外部联系人列表
     * @param int  $size
     * @param int  $offset
     * @param bool $raw
     *
     * @return array
     * @static
     * @since  2019.05.18
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
        $url = self::buildOperateUrl('topapi/extcontact/list', ['access_token' => '',]);
        $data = self::doRequest($url, $data, 'POST', 'results', $raw);
        $ret = self::outAry($ret, $data, $raw);
        return $ret;
    }

    /**
     * 获取外部联系人详细信息
     * @param string $userId
     * @param bool   $raw
     *
     * @return array
     * @static
     * @since  2019.05.18
     */
    public static function getDetailInfo($userId = '', $raw = false)
    {
        $ret = ['code' => -1, 'msg' => '',];
        if ($userId) {
            $data = ['user_id' => $userId,];
            $url = self::buildOperateUrl('topapi/extcontact/get', ['access_token' => '',]);
            $data = self::doRequest($url, $data, 'POST', 'result', $raw);
            $ret = self::outAry($ret, $data, $raw);
        }
        return $ret;
    }

    /**
     * 创建外部联系人
     * @param      $data
     * @param bool $raw
     *
     * @return array
     * @static
     * @since  2019.05.18
     */
    public static function create($data, $raw = false)
    {
        $ret = ['code' => -1, 'msg' => '',];
        if ($data instanceof ExtContactInfo) {
            $data = $data->getArray();
        } elseif (is_string($data)) {
            $data = json_decode($data, true);
        } elseif (!is_array($data)) {
            $data = [];
        }
        if ($data) {
            //必填项
            $chklist = ['label_ids', 'follower_user_id', 'name', 'state_code', 'mobile',];
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
                $url = self::buildOperateUrl('topapi/extcontact/create', ['access_token' => '',]);
                $data = self::doRequest($url, "contact=" . json_encode($data), 'POST', 'userid', $raw);
                $ret = self::outAry($ret, $data, $raw);
            }
        }
        return $ret;
    }

    /**
     * 更新外部联系人信息
     * @param      $data
     * @param bool $raw
     *
     * @return array
     * @static
     * @since  2019.05.18
     */
    public static function update($data, $raw = false)
    {
        $ret = ['code' => -1, 'msg' => '',];
        if ($data instanceof ExtContactInfo) {
            $data = $data->getUpdateData();
        } elseif (is_string($data)) {
            $data = json_decode($data, true);
        } elseif (!is_array($data)) {
            $data = [];
        }
        if ($data) {
            $msg = '';
            $data = self::checkUpdateField($data, $msg);
            if ($data) {
                $url = self::buildOperateUrl('topapi/extcontact/update', ['access_token' => '',]);
                $data = self::doRequest($url, $data, 'POST', '', $raw);
                $ret = self::outAry($ret, $data, $raw);
            } else {
                $code = 1;
                $msg = '没有需要更新的字段或者缺少更新用户标识 ' . $msg;
                $ret = ['code' => $code, 'msg' => $msg,];
            }
        }
        return $ret;
    }

    /**
     * 检查更新字段是否都有，如果有返回更新字符串
     * @param array $data
     * @param null  $msg
     *
     * @return string
     * @static
     * @since  2019.05.18
     */
    private static function checkUpdateField($data = [], &$msg = null)
    {
        $chkField = ['user_id', 'label_ids', 'follower_user_id', 'name',];
        $need = [];
        foreach ($chkField as $v) {
            if (!isset($data[$v])) {
                $need[] = $v;
            }
        }
        if ($need) {
            $msg = implode(',', $need);
            $ret = '';
        } else {
            $ret = 'contact=' . json_encode($data, JSON_UNESCAPED_UNICODE);
        }
        return $ret;
    }

    /**
     * 删除外部联系人
     * @param      $user_id
     * @param bool $raw
     *
     * @return array
     * @static
     * @since  2019.05.18
     */
    public static function delete($user_id, $raw = false)
    {
        $ret = ['code' => -1, 'msg' => '',];
        if ($user_id) {
            $data = ['user_id' => strval($user_id),];
            $url = self::buildOperateUrl('topapi/extcontact/delete', ['access_token' => '',]);
            $data = self::doRequest($url, $data, 'POST', '', $raw);
            $ret = self::outAry($ret, $data, $raw);
        }
        return $ret;
    }
}
