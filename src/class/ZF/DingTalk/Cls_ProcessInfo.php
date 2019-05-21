<?php
/**
 * Created by PhpStorm.
 * User: Jamers
 * Date: 2019/5/21
 * Time: 9:04
 * File: Cls_ProcessInfo.php
 */

namespace ZF\DingTalk;

/**
 * 阿里钉钉审批信息结构
 *
 * @package ZF\DingTalk
 * @author  Jamers <jamersnox@zomew.net>
 * @license https://opensource.org/licenses/GPL-3.0 GPL
 * @since   2019.05.21
 * @see     https://open-doc.dingtalk.com/microapp/serverapi2/cmct1a
 */
class ProcessInfo extends \ZF\DingTalk\CustomStructure
{
    /**
     * @var array 需要将数组转换成连接串的字段
     */
    protected $splitStrField = ['approvers', 'cc_list',];

    /**
     * @var string 数组转换成连接字符串的字符
     */
    protected $splitStr = ',';

    /**
     * @var integer AgentID
     */
    public $agent_id = null;
    protected $p_agent_id = null;

    /**
     * @var string 审批流的唯一码
     */
    public $process_code = '';
    protected $p_process_code = '';

    /**
     * @var string 审批实例发起人的userid
     */
    public $originator_user_id = '';
    protected $p_originator_user_id = '';

    /**
     * @var int 发起人所在的部门
     */
    public $dept_id = 0;
    protected $p_dept_id = 0;

    /**
     * @var array 审批人userid列表
     */
    public $approvers = [];
    protected $p_approvers = [];

    /**
     * @var array 审批人列表，支持会签/或签，优先级高于approvers变量
     */
    public $approvers_v2 = null;
    protected $p_approvers_v2 = null;

    /**
     * @var array 抄送人列表
     */
    public $cc_list = [];
    protected $p_cc_list = [];

    /**
     * @var string 抄送时机，分为（START, FINISH, START_FINISH）
     */
    public $cc_position = null;
    protected $p_cc_position = null;

    /**
     * @var array 审批流表单参数
     * 示例值：[{"ext_value":"扩展值","name":"标题1","value":"标题标题"},{"name":"日期","value":"2019-05-21"},{"value":"null"}]
     * 说明文字只需要"value"，不需要"name"
     */
    public $form_component_values = [];
    protected $p_form_component_values = [];

    /**
     * 设置AgentID
     * @param int $agentid
     *
     * @return void
     * @since  2019.05.21
     */
    public function setAgentId($agentid = 0)
    {
        if ($agentid > 0) {
            $this->agent_id = $agentid;
        } else {
            $config = \ZF\DingTalk::getConfig();
            if (isset($config['AGENTID']) && $config['AGENTID']) {
                $this->agent_id = $config['AGENTID'];
            }
        }
    }
}
