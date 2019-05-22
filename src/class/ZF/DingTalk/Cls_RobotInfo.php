<?php
/**
 * Created by PhpStorm.
 * User: Jamers
 * Date: 2019/5/22
 * Time: 17:16
 * File: Cls_RobotInfo.php
 */
namespace ZF\DingTalk;

/**
 * 阿里钉钉机器人信息结构
 *
 * @package ZF\DingTalk
 * @author  Jamers <jamersnox@zomew.net>
 * @license https://opensource.org/licenses/GPL-3.0 GPL
 * @since   2019.05.22
 * @see     https://open-doc.dingtalk.com/microapp/serverapi2/qf2nxq
 */
class RobotInfo extends MessageInfo
{
    /**
     * @var array AT数组
     */
    protected $at = [];

    /**
     * @var array 类别必须数据
     */
    protected $types = [
        'text' => ['content' => '',],
        'link' => ['messageUrl' => '', 'picUrl' => '', 'title' => '', 'text' => '',],
        'markdown' => ['title' => '', 'text' => '',],
        'actionCard' => [
            'title' => '',
            'markdown' => '',
            'singleTitle' => null,
            'singleURL' => null,
            'btnOrientation' => null,
            'hideAvatar' => null,
            'btns' => null,
        ],
        'feedCard' => [
            'links' => [],
        ],
    ];

    /**
     * 设置@信息
     * @param array $data
     *
     * @return void
     * @since  2019.05.22
     */
    public function setAtValue($data = [])
    {
        if (is_string($data) && $data) {
            if (strtoupper($data) == 'ALL') {
                $this->at['isAtAll'] = true;
            } else {
                $data = explode(',', $data);
                $this->at['atMobiles'] = $data;
                if (isset($this->at['isAtAll'])) {
                    unset($this->at['isAtAll']);
                }
            }
        } elseif (is_array($data) && $data) {
            $this->at['atMobiles'] = $data;
        } else {
            $this->at = [];
        }
    }

    /**
     * 追加信息
     *
     * @return false|string
     * @since  2019.05.22
     */
    public function __toString()
    {
        $ret = parent::__toString();
        if ($this->at) {
            $tmp = json_decode($ret, true);
            $tmp['at'] = $this->at;
            $ret = json_encode($tmp, JSON_UNESCAPED_UNICODE);
        }
        return $ret;
    }

    /**
     * Function getArray
     *
     * @return mixed
     * @since  2019.05.22
     */
    public function getArray()
    {
        $ret = parent::getArray();
        if ($this->at) {
            $ret['at'] = $this->at;
        }
        return $ret;
    }
}
