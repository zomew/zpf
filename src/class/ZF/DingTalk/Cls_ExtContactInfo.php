<?php
/**
 * Created by PhpStorm.
 * User: Jamers
 * Date: 2019/5/18
 * Time: 8:42
 * File: Cls_ExtContactInfo.php
 */

namespace ZF\DingTalk;

/**
 * 阿里钉钉外部联系人信息结构
 *
 * @package ZF\DingTalk
 * @author  Jamers <jamersnox@zomew.net>
 * @license https://opensource.org/licenses/GPL-3.0 GPL
 * @since   2019.05.18
 * @see https://open-doc.dingtalk.com/microapp/serverapi2/nb93oa
 */
class ExtContactInfo extends CustomStructure
{
    /**
     * @var string 外部联系人userid
     */
    public $userid = '';
    protected $p_userid = '';

    /**
     * @var string 职位
     */
    public $title = '';
    protected $p_title = '';

    /**
     * @var array 标签ID列表
     */
    public $label_ids = [];
    protected $p_label_ids = [];

    /**
     * @var array 共享部门ID列表
     */
    public $share_dept_ids = [];
    protected $p_share_dept_ids = [];

    /**
     * @var string 地址
     */
    public $address = '';
    protected $p_address = '';

    /**
     * @var string 备注
     */
    public $remark = '';
    protected $p_remark = '';

    /**
     * @var string 负责人userid
     */
    public $follower_user_id = '';
    protected $p_follower_user_id = '';

    /**
     * @var string 姓名
     */
    public $name = '';
    protected $p_name = '';

    /**
     * @var string 国家代码
     */
    public $state_code = '86';
    protected $p_state_code = '86';

    /**
     * @var string 公司名称
     */
    public $company_name = '';
    protected $p_company_name = '';

    /**
     * @var array 共享员工userid列表
     */
    public $share_user_ids = [];
    protected $p_share_user_ids = [];

    /**
     * @var string 手机号
     */
    public $mobile = '';
    protected $p_mobile = '';

    /**
     * 扩展实体化函数，用于处理特殊的数据结构
     * @param string $data
     */
    public function __construct($data = '')
    {
        if (is_string($data)) {
            $data = json_decode($data, true);
        }
        if (is_array($data)) {
            if (isset($data['contact'])) {
                $data = $data['contact'];
            }
        } else {
            $data = '';
        }
        parent::__construct($data);
    }

    /**
     * 魔术方法转字符串，由于此组件数据参数不一致，需要单独处理
     *
     * @return false|string
     * @since  2019.05.18
     */
    public function __toString()
    {
        $ret = json_decode(parent::magicToString(), true);
        return json_encode(['contact' => $ret,], JSON_UNESCAPED_UNICODE);
    }

    /**
     * 获取更新数据数组，由于不一致需要特殊处理
     *
     * @return array
     * @since  2019.05.18
     */
    public function getUpdateData()
    {
        $ret = [];
        if ($this->updateField) {
            if ($this->p_userid) {
                $ret['userid'] = $this->p_userid;
            }
            $ret = array_merge($ret, parent::getUpdateData());
        }
        return ['contact' => $ret,];
    }

    /**
     * 魔术方法设置值，并确定更新字段
     * @param string $name
     * @param        $value
     *
     * @return void
     * @since  2019.05.18
     */
    public function __set(string $name, $value)
    {
        $real = 'p_' . $name;
        if ($name == 'userid' && $this->$real) {
            //已经有userid就不允许再修改了
            return;
        }
        $this->magicSet($name, $value);
    }
}
