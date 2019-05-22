<?php
/**
 * Created by PhpStorm.
 * User: Jamers
 * Date: 2019/5/20
 * Time: 9:53
 * File: Cls_MessageInfo.php
 */

namespace ZF\DingTalk;

use \ZF\Common;

/**
 * 阿里钉钉消息结构
 *
 * @package ZF\DingTalk
 * @author  Jamers <jamersnox@zomew.net>
 * @license https://opensource.org/licenses/GPL-3.0 GPL
 * @since   2019.05.20
 * @see     https://open-doc.dingtalk.com/microapp/serverapi2/ye8tup
 */
class MessageInfo extends CustomStructure
{
    /**
     * @var array 类别必须数据
     */
    protected $types = [
        'text' => ['content' => '',],
        'image' => ['media_id' => '',],
        'voice' => ['media_id' => '', 'duration' => '',],
        'file' => ['media_id' => '',],
        'link' => ['messageUrl' => '', 'picUrl' => '', 'title' => '', 'text' => '',],
        'oa' => [
            'message_url' => '',
            'pc_message_url' => null,
            'head' => ['bgcolor' => 'FFBBBBBB', 'text' => '',],
            'body' => [
                'title' => null,
                'form' => null,
                'rich' => null,
                'content' => null,
                'image' => null,
                'file_count' => null,
                'author' => null,
            ],
        ],
        'markdown' => ['title' => '', 'text' => '',],
        'action_card' => [
            'title' => '',
            'markdown' => '',
            'single_title' => null,
            'single_url' => null,
            'btn_orientation' => null,
            'btn_json_list' => null,
        ],
    ];

    /**
     * @var string 消息类型
     */
    public $msgtype = '';
    protected $p_msgtype = '';

    /**
     * @var bool 是否只保留框架内的有效字段
     */
    public $safe = false;
    private $p_safe = false;

    /**
     * @var array 私有数据
     */
    protected $data = [];

    /**
     * 用于代码提示
     */
    public $content;
    public $media_id;
    public $duration;
    public $messageUrl;
    public $picUrl;
    public $title;
    public $text;
    public $message_url;
    public $pc_message_url;
    public $head;
    public $body;
    public $markdown;
    public $single_title;
    public $single_url;
    public $btn_orientation;
    public $btn_json_list;

    /**
     * 清除自定义属性中的NULL字段，返回未填字段
     * @param array $ary
     * @param array $need
     *
     * @return array
     * @since  2019.05.20
     */
    private function getCleanData($ary = [], &$need = [])
    {
        $ret = $ary;
        if (is_array($ary) && $ary) {
            foreach ($ary as $k => $v) {
                if ($v === null) {
                    unset($ret[$k]);
                } elseif (is_array($v) && $v) {
                    $ret[$k] = $this->getCleanData($v);
                } elseif (!$v) {
                    $need[] = $k;
                }
            }
        }
        return $ret;
    }

    /**
     * 魔术方法设置相关参数
     * @param string $name
     * @param        $value
     *
     * @return void
     * @since  2019.05.20
     */
    public function __set(string $name, $value)
    {
        if (strtolower($name) == 'msgtype') {
            if (is_string($value)) {
                if ($value !== $this->p_msgtype && isset($this->types[$value])) {
                    $this->p_msgtype = $value;
                    $this->data = $this->types[$value];
                }
            }
        } elseif ($name == 'safe') {
            $this->p_safe = boolval($value);
        } else {
            if (isset($this->data[$name])) {
                if ('array' == gettype($this->data[$name])) {
                    if (is_array($value)) {
                        if (!$this->p_safe) {
                            $this->data[$name] = array_merge($this->data[$name], $value);
                        } else {
                            foreach ($value as $k => $v) {
                                if (isset($this->data[$name][$k])) {
                                    $this->data[$name][$k] = $v;
                                }
                            }
                        }
                    }
                } else {
                    $this->data[$name] = $value;
                }
            }
        }
    }

    /**
     * 魔术方法获取相关数据
     * @param string $name
     *
     * @return mixed|string|null
     * @since  2019.05.20
     */
    public function __get($name)
    {
        if (strtolower($name) == 'msgtype') {
            $ret = $this->p_msgtype;
        } elseif ($name == 'safe') {
            $ret = $this->p_safe;
        } else {
            if (isset($this->data[$name])) {
                $ret = $this->data[$name];
            } else {
                $ret = null;
            }
        }
        return $ret;
    }

    /**
     * 转出成JSON字符串
     *
     * @return false|string
     * @since  2019.05.20
     */
    public function __toString()
    {
        $ret = [];
        if ($this->p_msgtype) {
            $need = [];
            $data = $this->getCleanData($this->data, $need);
            $ret = ['msgtype' => $this->p_msgtype, $this->p_msgtype => $data,];
        }
        return json_encode($ret, JSON_UNESCAPED_UNICODE);
    }

    /**
     * 消息结构构造函数
     * @param string $data
     */
    public function __construct($data = '')
    {
        if ($data) {
            if (is_string($data)) {
                $data = @json_decode($data, true);
            }
            if ($data instanceof MessageInfo) {
                $data = $data->getArray();
            }
            if (is_array($data)) {
                if (isset($data['msgtype']) && isset($this->types[$data['msgtype']])
                    && isset($data[$data['msgtype']]) && is_array($data[$data['msgtype']]) && $data[$data['msgtype']]
                ) {
                    $this->p_msgtype = $data['msgtype'];
                    $this->data = array_merge($this->types[$data['msgtype']], $data[$data['msgtype']]);
                }
            }
        }
        parent::__construct();
    }
}
