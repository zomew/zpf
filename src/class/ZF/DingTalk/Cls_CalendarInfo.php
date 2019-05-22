<?php
/**
 * Created by PhpStorm.
 * User: Jamers
 * Date: 2019/5/22
 * Time: 9:37
 * File: Cls_CalendarInfo.php
 */

namespace ZF\DingTalk;

/**
 * 阿里钉钉日程信息结构
 *
 * @package ZF\DingTalk
 * @author  Jamers <jamersnox@zomew.net>
 * @license https://opensource.org/licenses/GPL-3.0 GPL
 * @since   2019.05.22
 * @see     https://open-doc.dingtalk.com/microapp/serverapi2/iqel76
 */
class CalendarInfo extends \ZF\DingTalk\CustomStructure
{
    /**
     * @var string 日程主题
     */
    public $summary = '';
    protected $p_summary = '';

    /**
     * @var array 事项开始前提醒设置
     */
    public $reminder = [];
    protected $p_reminder = [];

    /**
     * @var string 地点
     */
    public $location = null;
    protected $p_location = null;

    /**
     * @var array 接收者userid
     */
    public $receiver_userids = [];
    protected $p_receiver_userids = [];

    /**
     * @var array 结束时间
     */
    public $end_time = ['unix_timestamp' => 0,];
    protected $p_end_time = ['unix_timestamp' => 0,];

    /**
     * @var string 日程类型，默认提醒，未发现有其它参数可设置
     */
    public $calendar_type = 'notification';
    protected $p_calendar_type = 'notification';

    /**
     * @var array 开始时间
     */
    public $start_time = ['unix_timestamp' => 0,];
    protected $p_start_time = ['unix_timestamp' => 0,];

    /**
     * @var array 来源信息，URL必填
     */
    public $source = ['title' => '', 'url' => '',];
    protected $p_source = ['title' => '', 'url' => '',];

    /**
     * @var string 备注信息
     */
    public $description = null;
    protected $p_description = null;

    /**
     * @var string 创建人userid
     */
    public $creator_userid = '';
    protected $p_creator_userid = '';

    /**
     * @var string 请求唯一标识
     */
    public $uuid = '';
    protected $p_uuid = '';

    /**
     * @var string 业务标识
     */
    public $biz_id = '';
    protected $p_biz_id = '';

    /**
     * 部分值特殊设置
     * @param string $name
     * @param        $value
     *
     * @return void
     * @since  2019.05.22
     */
    public function __set(string $name, $value)
    {
        if (in_array($name, ['start_time', 'end_time',])) {
            $value = intval($value);
            if (strlen(strval($value)) <= 12) {
                $value *= 1000;
            }
            $name = 'p_' . $name;
            $this->$name['unix_timestamp'] = $value;
        } else {
            parent::__set($name, $value);
        }
    }
}
