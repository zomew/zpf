<?php
/**
 * Created by PhpStorm.
 * User: Jamers
 * Date: 2018/12/17
 * Time: 13:30
 * File: Cls_SmsAuthCode.php
 */

namespace ZF;

/**
 * 短信验证码较验模块
 *
 * @package ZF
 * @author  Jamers <jamersnox@zomew.net>
 * @license https://opensource.org/licenses/GPL-3.0 GPL
 * @since   2018.12.17
 */
class SmsAuthCode
{
    /**
     * 发送短信验证码
     *
     * @param string $mobile 
     * 
     * @return void
     * @since  2018.12.17
     */
    public static function sendCode($mobile = '')
    {
        if ($mobile 
            && preg_match(
                "/^(13[0-9]|14[579]|15[0-3,5-9]|16[6]|17[0135678]|18[0-9]|19[89])\d{8}$/", 
                $mobile
            )
        ) {
            $msg = '';
            if (defined('RUN_ENV') 
                && in_array(RUN_ENV, array('local', /*'dev',*/))
            ) {
                $dev = true;
            } else {
                $dev = false;
            }
            if ($dev || DySDK::CheckCsrfToken()) {
                $token = DySDK::BuildCsrfToken();

                if (!$dev && isset($_SESSION['login_code'])) {
                    $time = time() - $_SESSION['login_code']['time'];
                    if ($time < 60) {
                        $residue = 60 - $time;
                        $msg = '您已经获取验证码，请' . $residue . '秒后重试';
                    }
                }
                if ($msg == '') {
                    $code = rand(1000, 9999);
                    $_SESSION['login_code']['tel'] = $mobile;
                    $_SESSION['login_code']['code'] = $code;
                    $_SESSION['login_code']['time'] = time();

                    $resp = array(
                        'code' => 0,
                        'msg' => 'success',
                    );

                    if (!$dev) {
                        $resp = DySDK::sendSms(
                            $mobile, 
                            1, 
                            array('code' => $code,), 
                            true
                        );
                    } else {
                        $resp['data'] = $code;
                    }
                    $resp['token'] = $token;
                } else {
                    $resp = array('code' => 1, 'msg' => $msg, 'token' => $token,);
                }
            } else {
                $resp = array('code' => 1, 'msg' => '非法请求',);
            }
            exit(Common::JsonP($resp));
        } else {
            $msg = '您所输入的手机号码无效';
        }
        exit(Common::JsonP(array('code' => 1, 'msg' => $msg,)));
    }

    /**
     * 检查验证码是否过期
     *
     * @param string $mobile 
     * @param string $code 
     * 
     * @return bool
     * @since  2018.12.17
     */
    public static function checkAuthCode($mobile = '', $code = '')
    {
        if (!isset($_SESSION['login_code'])) {
            exit(Common::JsonP(array('code' => 1, 'msg' => '请先获取验证码',)));
        }
        $login_code = $_SESSION['login_code'];
        // 5分钟有效期
        $time = time() - $login_code['time'];
        if ($time > 300) {
            exit(Common::JsonP(array('code' => 1, 'msg' => '验证码已过期，请重新获取',)));
        }
        // 手机是否一致
        if ($mobile != $login_code['tel']) {
            exit(Common::JsonP(array('code' => 1, 'msg' => '与发送手机号不一致',)));
        }
        // 是否正确
        if ($code != $login_code['code']) {
            exit(Common::JsonP(array('code' => 1, 'msg' => '无效的验证码',)));
        }
        return true;
    }
}