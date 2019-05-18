<?php
/**
 * Created by PhpStorm.
 * User: Jamers
 * Date: 2019/5/16
 * Time: 10:11
 * File: Cls_Sns.php
 */

namespace ZF\DingTalk;

/**
 * SNS登录相关封装
 *
 * @package ZF\DingTalk
 * @author  Jamers <jamersnox@zomew.net>
 * @license https://opensource.org/licenses/GPL-3.0 GPL
 * @since   2019.05.16
 */
class Sns extends \ZF\DingTalk
{
    /**
     * 根据临时授权码获取用户信息
     * @param string $code
     * @param bool   $raw
     *
     * @return array|mixed
     * @static
     * @since  2019.05.16
     * @see https://open-doc.dingtalk.com/microapp/serverapi2/kymkv6
     */
    public static function getUserInfoByCode($code = '', $raw = false)
    {
        $data = ['tmp_auth_code' => $code,];
        $config = self::getConfig();
        $appid = '';
        $appsecret = '';
        if (isset($config['APPID'])) {
            $appid = $config['APPID'];
        }
        if (isset($config['APPSECRET'])) {
            $appsecret = $config['APPSECRET'];
        }
        $params = ['accessKey' => $appid, 'timestamp' => time(),];
        $params['signature'] = self::signature($params['timestamp'], $appsecret);
        $url = self::buildOperateUrl('sns/getuserinfo_bycode', $params);
        $data = self::doRequest($url, $data, 'POST', 'user_info', $raw);
        $ret = self::outAry([], $data, $raw);
        return $ret;
    }
}
