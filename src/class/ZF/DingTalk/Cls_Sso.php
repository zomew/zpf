<?php
/**
 * Created by PhpStorm.
 * User: Jamers
 * Date: 2019/5/15
 * Time: 14:57
 * File: Cls_Sso.php
 */

namespace ZF\DingTalk;

/**
 * Class Sso
 *
 * @package ZF\DingTalk
 * @author  Jamers <jamersnox@zomew.net>
 * @license https://opensource.org/licenses/GPL-3.0 GPL
 * @since   2019.05.15
 */
class Sso extends \ZF\DingTalk
{
    /**
     * 获取SSO Token
     * @param string $corpid
     * @param string $corpsecret
     *
     * @return mixed|string
     * @throws \Exception
     * @static
     * @since  2019.05.15
     * @see https://open-doc.dingtalk.com/microapp/serverapi2/xswxhg
     */
    public static function getSsoToken(string $corpid = '', string $corpsecret = '')
    {
        $ret = '';
        $config = self::getConfig();
        if ($corpid && $corpsecret) {
            $config = array_merge($config, ['CORP_ID' => $corpid, 'CORP_SECRET' => $corpsecret,]);
        }
        if (!isset($config['CORP_ID']) || !isset($config['CORP_SECRET'])) {
            throw new \Exception("SSO配置有误，请检查相关配置");
        }
        $params = ['corpid' => $config['CORP_ID'], 'corpsecret' => $config['CORP_SECRET'],];
        $url = self::buildOperateUrl('sso/gettoken', $params);
        $json = self::doRequest($url, [], 'GET', 'access_token');
        if (is_string($json)) {
            $ret = $json;
        }
        return $ret;
    }

    /**
     * 根据SSO Code获取用户信息
     * @param string $code
     * @param bool   $raw
     *
     * @return array|mixed
     * @throws \Exception
     * @static
     * @since  2019.05.15
     * @see https://open-doc.dingtalk.com/microapp/serverapi2/xswxhg
     */
    public static function getUserInfoBySsoCode(string $code = '', bool $raw = false)
    {
        $ret = [];
        if ($code) {
            $url = self::buildOperateUrl('sso/getuserinfo', ['access_token' => self::getSsoToken(), 'code' => $code,]);
            $data = self::doRequest($url, [], 'GET', 'user_info', $raw);
            $ret = self::outAry($ret, $data, $raw);
        }
        return $ret;
    }
}
