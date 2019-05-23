<?php
/**
 * Created by PhpStorm.
 * User: Jamers
 * Date: 2019/5/23
 * Time: 9:58
 * File: Cls_CSpace.php
 */
namespace ZF\DingTalk;

/**
 * 阿里钉钉网盘管理接口封装
 *
 * @package ZF\DingTalk
 * @author  Jamers <jamersnox@zomew.net>
 * @license https://opensource.org/licenses/GPL-3.0 GPL
 * @since   2019.05.23
 * @see     https://open-doc.dingtalk.com/microapp/serverapi2/wk3krc
 */
class CSPace extends \ZF\DingTalk
{
    /**
     * @var bool 自动发送标头
     */
    public static $autoSendHeader = true;

    /**
     * 发送钉盘文件给指定用户
     * @param      $userid
     * @param      $media_id
     * @param      $file_name
     * @param bool $raw
     *
     * @return array
     * @static
     * @since  2019.05.23
     */
    public static function addToSingleChat($userid, $media_id, $file_name, $raw = false)
    {
        $ret = ['code' => -1, 'msg' => '',];
        if ($userid && $media_id && $file_name) {
            $config = self::getConfig();
            $data = ['access_token' => '', 'agent_id' => '', 'userid' => $userid,
                'media_id' => $media_id, 'file_name' => $file_name,];
            if (isset($config['AGENTID'])) {
                $data['agent_id'] = $config['AGENTID'];
            }
            $url = self::buildOperateUrl('cspace/add_to_single_chat', $data);
            $data = self::doRequest($url, [], 'POST', '', $raw);
            $ret = self::outAry($ret, $data, $raw);
        }
        return $ret;
    }

    /**
     * 新增文件到用户钉盘
     * @param      $code
     * @param      $media_id
     * @param      $space_id
     * @param      $folder_id
     * @param      $name
     * @param null $overwrite
     * @param bool $raw
     *
     * @return array
     * @static
     * @since  2019.05.23
     */
    public static function add($code, $media_id, $space_id, $folder_id, $name, $overwrite = null, $raw = false)
    {
        $ret = ['code' => -1, 'msg' => '',];
        if ($code && $media_id && $space_id && $folder_id && $name) {
            $config = self::getConfig();
            $data = ['access_token' => '', 'agent_id' => '', 'code' => $code,
                'media_id' => $media_id, 'space_id' => $space_id, 'name' => $name,];
            if (isset($config['AGENTID'])) {
                $data['agent_id'] = $config['AGENTID'];
            }
            if ($overwrite !== null) {
                $data['overwrite'] = boolval($overwrite) ? 'true' : 'false';
            }
            $url = self::buildOperateUrl('cspace/add', $data);
            $data = self::doRequest($url, [], 'GET', 'dentry', $raw);
            $ret = self::outAry($ret, $data, $raw);
        }
        return $ret;
    }

    /**
     * 获取自定义空间ID space_id
     * @param string $domain
     * @param bool   $raw
     *
     * @return array
     * @static
     * @since  2019.05.23
     */
    public static function getCustomSpace($domain = '', $raw = false)
    {
        $ret = ['code' => -1, 'msg' => '',];
        if ($domain) {
            $config = self::getConfig();
            $data = ['access_token' => '', 'agent_id' => '',];
            if (isset($config['AGENTID'])) {
                $data['agent_id'] = $config['AGENTID'];
            }
            if ($domain) {
                $data['domain'] = $domain;
            }
            $url = self::buildOperateUrl('cspace/get_custom_space', $data);
            $data = self::doRequest($url, '', 'GET', 'spaceid', $raw);
            $ret = self::outAry($ret, $data, $raw);
        }
        return $ret;
    }

    /**
     * 授权用户访问企业自定义空间
     * @param string $domain
     * @param string $type
     * @param string $userid
     * @param string $path
     * @param array  $fields
     * @param int    $duration
     * @param bool   $raw
     *
     * @return array
     * @static
     * @since  2019.05.23
     */
    public static function grantCustomSpace(
        $domain = '',
        $type = 'add',
        $userid = '',
        $path = '',
        $fields = [],
        $duration = 0,
        $raw = false
    ) {
        $ret = ['code' => -1, 'msg' => '',];
        if ($domain && $type && $userid) {
            $config = self::getConfig();
            $data = ['access_token' => '', 'agent_id' => '', 'type' => $type, 'userid' => $userid,
                'duration' => $duration,];
            if (isset($config['AGENTID'])) {
                $data['agent_id'] = $config['AGENTID'];
            }
            if ($domain) {
                $data['domain'] = $domain;
            }
            if ($type == 'add' && $path == '') {
                $data['path'] = '/';
            }
            if ($path) {
                $data['path'] = $path;
            }
            if ($type == 'download') {
                if (is_array($fields)) {
                    $fields = implode(',', $fields);
                } elseif (!is_string($fields)) {
                    $fields = '';
                }
                if (!$fields) {
                    $ret = ['code' => 1, 'msg' => 'download 时 fields参数必传'];
                } else {
                    $data['fields'] = $fields;
                }
            }
            if ($ret['code'] <= 0) {
                $url = self::buildOperateUrl('cspace/grant_custom_space', $data);
                $data = self::doRequest($url, '', 'GET', '', $raw);
                $ret = self::outAry($ret, $data, $raw);
            }
        }
        return $ret;
    }
}
