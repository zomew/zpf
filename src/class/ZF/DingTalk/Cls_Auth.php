<?php
/**
 * Created by PhpStorm.
 * User: Jamers
 * Date: 2019/5/18
 * Time: 16:00
 * File: Cls_Auth.php
 */

namespace ZF\DingTalk;

/**
 * 阿里钉钉权限相关接口封装
 *
 * @package ZF\DingTalk
 * @author  Jamers <jamersnox@zomew.net>
 * @license https://opensource.org/licenses/GPL-3.0 GPL
 * @since   2019.05.18
 */
class Auth extends \ZF\DingTalk
{
    /**
     * 获取通讯录权限范围
     *
     * @return array
     * @static
     * @since  2019.05.18
     */
    public static function getScopes()
    {
        $url = self::buildOperateUrl('auth/scopes', ['access_token' => '',]);
        $data = self::doRequest($url, [], 'GET', '', $raw);
        $ret = self::outAry([], $data, $raw);
        return $ret;
    }
}
