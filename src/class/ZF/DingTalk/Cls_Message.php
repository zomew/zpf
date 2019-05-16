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

class Message extends \ZF\DingTalk
{
    /**
     * 异步发送消息
     * @param array  $msg
     * @param string $useridlist
     * @param string $deptidlist
     *
     * @return array|mixed
     * @throws \Exception
     * @static
     * @since  2019.05.15
     */
    public static function asyncSendV2($msg = [], $useridlist = '', $deptidlist = '')
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
                $ret = @json_decode(Common::postRequest($url, $params), true);
            } else {
                throw new \Exception('AGENTID不能为空，请检查配置文件');
            }
        }
        return $ret;
    }
}
