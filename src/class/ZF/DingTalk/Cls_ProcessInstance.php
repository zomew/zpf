<?php
/**
 * Created by PhpStorm.
 * User: Jamers
 * Date: 2019/5/21
 * Time: 9:19
 * File: Cls_ProcessInstance.php
 */
namespace ZF\DingTalk;

/**
 * 阿里钉钉审批实例接口封装
 *
 * @package ZF\DingTalk
 * @author  Jamers <jamersnox@zomew.net>
 * @license https://opensource.org/licenses/GPL-3.0 GPL
 * @since   2019.05.21
 * @see     https://open-doc.dingtalk.com/microapp/serverapi2/cmct1a
 */
class ProcessInstance extends \ZF\DingTalk
{
    /**
     * @var bool 自动发送标头
     */
    public static $autoSendHeader = true;

    /**
     * 创建新的审批实例
     * @param      $data
     * @param bool $raw
     *
     * @return array
     * @static
     * @since  2019.05.21
     */
    public static function create($data, $raw = false)
    {
        $ret = ['code' => -1, 'msg' => '',];
        if ($data instanceof ProcessInfo) {
            $data = $data->getArray();
        } elseif (is_string($data)) {
            $data = json_decode($data, true);
        } elseif (!is_array($data)) {
            $data = [];
        }
        if ($data) {
            //必填项
            $chklist = ['process_code', 'originator_user_id', 'dept_id', 'form_component_values',];
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
                $url = self::buildOperateUrl('topapi/processinstance/create', ['access_token' => '',]);
                $data = self::doRequest($url, json_encode($data), 'POST', 'process_instance_id', $raw);
                $ret = self::outAry($ret, $data, $raw);
            }
        }
        return $ret;
    }
}
