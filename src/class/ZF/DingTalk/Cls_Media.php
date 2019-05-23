<?php
/**
 * Created by PhpStorm.
 * User: Jamers
 * Date: 2019/5/23
 * Time: 8:55
 * File: Cls_Media.php
 */
namespace ZF\DingTalk;

/**
 * 阿里钉钉媒体管理相关接口封装
 *
 * @package ZF\DingTalk
 * @author  Jamers <jamersnox@zomew.net>
 * @license https://opensource.org/licenses/GPL-3.0 GPL
 * @since   2019.05.23
 * @see     https://open-doc.dingtalk.com/microapp/serverapi2/bcmg0i
 */
class Media extends \ZF\DingTalk
{
    /**
     * @var bool 自动发送标头
     */
    public static $autoSendHeader = true;

    /**
     * @var array 发送标头
     */
    public static $header = ['Content-Type' => 'multipart/form-data',];

    /**
     * 上传媒体文件
     * @param        $file
     * @param string $type
     * @param bool   $raw
     *
     * @return array
     * @static
     * @since  2019.05.23
     */
    public static function upload($file, $type = 'file', $raw = false)
    {
        $type = strtolower($type);
        if (!in_array($type, ['image', 'voice', 'file',])) {
            $type = 'file';
        }
        $ret = ['code' => -1, 'msg' => '未找到对应文件，请使用绝对路径',];
        if ($file && file_exists($file)) {
            $data = ['media' => new \CURLFile($file), 'type' => $type,];
            $url = self::buildOperateUrl('media/upload', ['access_token' => '',]);
            $option = ['SAFE_UPLOAD' => true,];
            $data = self::doRequest($url, $data, 'POST', '', $raw, [], $option);
            $ret = self::outAry($ret, $data, $raw);
        }
        return $ret;
    }
}
