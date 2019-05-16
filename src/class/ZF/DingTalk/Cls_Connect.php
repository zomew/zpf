<?php
/**
 * Created by PhpStorm.
 * User: Jamers
 * Date: 2019/5/16
 * Time: 9:28
 * File: Cls_Connect.php
 */

namespace ZF\DingTalk;

use \ZF\Common;

/**
 * 第三方钉钉登录相关封装
 *
 * @package ZF\DingTalk
 * @author  Jamers <jamersnox@zomew.net>
 * @license https://opensource.org/licenses/GPL-3.0 GPL
 * @since   2019.05.16
 */
class Connect extends \ZF\DingTalk
{
    /**
     * 生成扫码登录页面URL
     * @param string $url
     * @param string $state
     * @param string $type  类型 默认生成直接跳转链接 js 生成JS调用跳转链接(已urlencode)
     *
     * @return string
     * @static
     * @since  2019.05.16
     * @see https://open-doc.dingtalk.com/microapp/serverapi2/kymkv6
     */
    public static function getQrConnectUrl($url = '', $state = 'STATE', $type = '')
    {
        $params = [
            'appid' => '',
            'response_type' => 'code',
            'scope' => 'snsapi_login',
            'state' => $state,
            'redirect_url' => urlencode(urldecode($url)),
        ];
        $opt = 'connect/qrconnect';
        if (strtolower($type) == 'js') {
            $opt = 'connect/oauth2/sns_authorize';
            $ret = urlencode(self::buildOperateUrl($opt, $params));
        } else {
            $ret = self::buildOperateUrl($opt, $params);
        }
        return $ret;
    }

    /**
     * 生成钉钉内免登录跳转URL
     * @param string $url
     * @param string $type
     * @param string $state
     *
     * @return string
     * @static
     * @since  2019.05.16
     */
    public static function getSnsAuthorizeUrl($url = '', $type = 'auth', $state = 'STATE')
    {
        $type = trim(strtolower($type));
        if (in_array($type, ['login', 'auth',])) {
            $scope = 'snsapi_' . $type;
        } else {
            $scope = 'snsapi_auth';
        }
        $params = [
            'appid' => '',
            'response_type' => 'code',
            'scope' => $scope,
            'state' => $state,
            'redirect_url' => urlencode(urldecode($url)),
        ];
        return self::buildOperateUrl('connect/oauth2/sns_authorize', $params);
    }
}
