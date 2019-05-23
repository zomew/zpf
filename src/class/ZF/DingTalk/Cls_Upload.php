<?php
/**
 * Created by PhpStorm.
 * User: Jamers
 * Date: 2019/5/23
 * Time: 10:46
 * File: Cls_Upload.php
 */

namespace ZF\DingTalk;

/**
 * 文件上传相关接口封装
 *
 * @package ZF\DingTalk
 * @author  Jamers <jamersnox@zomew.net>
 * @license https://opensource.org/licenses/GPL-3.0 GPL
 * @since   2019.05.23
 * @see     https://open-doc.dingtalk.com/microapp/serverapi2/wk3krc
 */
class Upload extends \ZF\DingTalk
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
     * 单步文件上传
     * @param      $file
     * @param bool $raw
     *
     * @return array
     * @static
     * @since  2019.05.23
     */
    public static function single($file, $raw = false)
    {
        $ret = ['code' => -1, 'msg' => '',];
        if ($file && file_exists($file)) {
            $config = self::getConfig();
            $params = ['access_token' => '', 'agent_id' => '', 'file_size' => filesize($file),];
            if (isset($config['AGENTID'])) {
                $params['agent_id'] = $config['AGENTID'];
            }
            $data = ['file' => new \CURLFile($file),];
            $url = self::buildOperateUrl('file/upload/single', $params);
            $data = self::doRequest($url, $data, 'POST', 'media_id', $raw, [], ['SAFE_UPLOAD' => true,]);
            $ret = self::outAry($ret, $data, $raw);
        }
        return $ret;
    }

    /**
     * 开启/提交分块上传事务，传upload_id时为提交事务
     * @param        $file
     * @param int    $chunk_numbers
     * @param string $upload_id
     * @param bool   $raw
     *
     * @return array
     * @static
     * @since  2019.05.23
     */
    public static function transaction($file, $chunk_numbers = 1, $upload_id = '', $raw = false)
    {
        $ret = ['code' => -1, 'msg' => '',];
        if ($file && file_exists($file)) {
            $config = self::getConfig();
            $keys = 'upload_id';
            $params = ['access_token' => '', 'agent_id' => '', 'file_size' => filesize($file),
                'chunk_numbers' => $chunk_numbers,];
            if (isset($config['AGENTID'])) {
                $params['agent_id'] = $config['AGENTID'];
            }
            if ($upload_id) {
                $params['upload_id'] = urlencode($upload_id);
                $keys = 'media_id';
            }
            $url = self::buildOperateUrl('file/upload/transaction', $params);
            $data = self::doRequest($url, '', 'GET', $keys, $raw);
            $ret = self::outAry($ret, $data, $raw);
        }
        return $ret;
    }

    /**
     * 上传文件块
     * @param      $file
     * @param      $upload_id
     * @param int  $chunk_sequence
     * @param bool $raw
     *
     * @return array
     * @static
     * @since  2019.05.23
     */
    public static function chunk($file, $upload_id, $chunk_sequence = 1, $raw = false)
    {
        $ret = ['code' => -1, 'msg' => '',];
        if ($file && file_exists($file) && $upload_id && $chunk_sequence > 0) {
            $config = self::getConfig();
            $params = ['access_token' => '', 'agent_id' => '', 'upload_id' => urlencode($upload_id),
                'chunk_sequence' => $chunk_sequence,];
            if (isset($config['AGENTID'])) {
                $params['agent_id'] = $config['AGENTID'];
            }
            $data = ['file' => new \CURLFile($file),];
            $url = self::buildOperateUrl('file/upload/chunk', $params);
            $data = self::doRequest($url, $data, 'POST', '', $raw, [], ['SAFE_UPLOAD' => true,]);
            $ret = self::outAry($ret, $data, $raw);
        }
        return $ret;
    }
}
