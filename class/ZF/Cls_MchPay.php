<?php
/**
 * Created by PhpStorm.
 * User: Jamers
 * Date: 2018/12/14
 * Time: 9:48
 * File: Cls_MchPay.php
 */

namespace ZF;

/**
 * 企业付款（至零钱、银行卡）相关API封装
 * @author Jamers <jamersnox@zomew.net>
 * @license https://opensource.org/licenses/GPL-3.0 GPL
 * @since 2018.12.14
 *
 * Class MchPay
 * @package ZF
 */
class MchPay {
    private $_config = array();

    /**
     * 实体化类的时候需要传入的参数，可以是公众号相关配置信息，也可以是Config里的配置名称，
     * 如：ANJU 自动获取 \Config::$ANJU_CONFIG 里的配置信息
     * MchPay constructor.
     * @param string $name
     */
    public function __construct($name = '')
    {
        self::LoadConfig();
        if ($name) {
            if (is_string($name)) {
                $name = strtoupper($name)."_CONFIG";
                if (isset(\Config::${$name}) && isset(\Config::${$name}['mp'])) $this->_config = \Config::${$name}['mp'];
            }else if (is_array($name)) {
                $this->_config = $name;
            }
        }
        if (!(isset($this->_config['appid']) && isset($this->_config['mch_id']) && isset($this->_config['pay_key']) && isset($this->_config['cert']) && isset($this->_config['keys']))) {
            trigger_error('Config not exist!', E_USER_ERROR);
        }
    }

    /**
     * 加载配置文件
     */
    private static function LoadConfig()
    {
        if (!class_exists('\Config')) {
            $uname = php_uname('n');
            $config = array(
                ZF_ROOT . "config.{$uname}.php",
                ZF_ROOT.'config.php',
                ZF_ROOT.'config.example.php',
            );
            foreach($config as $v) {
                if (file_exists($v)) {
                    include_once($v);
                    break;
                }
            }
        }
    }

    /**
     * 生成SSL双向认证相关参数
     * @since 2018.12.14
     *
     * @return array
     */
    private function Build_SSL_Array() {
        $ret = array(
            'SSLCERTTYPE' => 'PEM',
            'SSLCERT' => $this->_config['cert'],
            'SSLKEYTYPE' => 'PEM',
            'SSLKEY' => $this->_config['keys'],
        );
        return $ret;
    }

    /**
     * 企业付款至零钱
     * @since 2018.12.14
     *
     * @param string $tno
     * @param string $openid
     * @param float $amount
     * @param string $desc
     * @return array|bool|mixed
     */
    public function transfers($tno = '', $openid = '', $amount = 0.0, $desc = '') {
        $ret = array();
        if ($openid && $amount > 0 && $desc) {
            $url = 'https://api.mch.weixin.qq.com/mmpaymkttransfers/promotion/transfers';
            $params = array(
                'mch_appid' => $this->_config['appid'],
                'mchid' => $this->_config['mch_id'],
                'nonce_str' => Wepay::getNonceStr(),
                'partner_trade_no' => $tno ? $tno : md5("{$openid}{$amount}{$desc}" . microtime(true)),
                'openid' => $openid,
                'check_name' => 'NO_CHECK',
                'amount' => intval($amount * 100),
                'desc' => $desc,
                'spbill_create_ip' => Common::GetIP(),
            );
            $params['sign'] = Wepay::MakeSign($params, $this->_config['pay_key']);
            $xml = Wepay::xml_encode($params);
            $r = Common::_postRequest($url, $xml, $this->Build_SSL_Array());
            if ($r) {
                $tmp = Wepay::xml2array($r);
                if (isset($tmp['return_code'])) {
                    $ret = $tmp;
                }
            }
        }
        return $ret;
    }

    /**
     * 查询30天内订单付款情况
     * @since 2018.12.14
     *
     * @param string $partner_trade_no
     * @return array|bool|mixed
     */
    public function gettransferinfo($partner_trade_no = '') {
        $ret = array();
        if ($partner_trade_no) {
            $url = 'https://api.mch.weixin.qq.com/mmpaymkttransfers/gettransferinfo';

            $params = array(
                'nonce_str' => Wepay::getNonceStr(),
                'partner_trade_no' => $partner_trade_no,
                'mch_id' => $this->_config['mch_id'],
                'appid' => $this->_config['appid'],
            );
            $params['sign'] = Wepay::MakeSign($params, $this->_config['pay_key']);
            $xml = Wepay::xml_encode($params);
            $r = Common::_postRequest($url, $xml, $this->Build_SSL_Array());
            if ($r) {
                $tmp = Wepay::xml2array($r);
                if (isset($tmp['return_code'])) {
                    $ret = $tmp;
                }
            }
        }
        return $ret;
    }

    /**
     * 企业付款至银行卡
     * @since 2018.12.14
     *
     * @param string $tno   订单号
     * @param string $bno
     * @param string $bname
     * @param string $bcode
     * @param float $amount
     * @param string $desc
     * @return array|bool|mixed
     */
    public function pay_bank($tno = '', $bno = '', $bname = '', $bcode = '', $amount = 0.0, $desc = '') {
        $ret = array();
        if ($bno && $bname && $bcode && $amount > 0) {
            $url = 'https://api.mch.weixin.qq.com/mmpaysptrans/pay_bank';
            $params = array(
                'mch_id' => $this->_config['mch_id'],
                'partner_trade_no' => $tno ? $tno : md5("{$bno}_{$bname}_{$bcode}{$amount}{$desc}"),
                'nonce_str' => Wepay::getNonceStr(),
                'enc_bank_no' => $this->rsa_public_info_encrypt($bno),
                'enc_true_name' => $this->rsa_public_info_encrypt($bname),
                'bank_code' => $bcode,
                'amount' => intval($amount * 100),
                'desc' => $desc,
            );
            $params['sign'] = Wepay::MakeSign($params, $this->_config['pay_key']);
            $xml = Wepay::xml_encode($params);
            $r = Common::_postRequest($url, $xml, $this->Build_SSL_Array());
            if ($r) {
                $tmp = Wepay::xml2array($r);
                if (isset($tmp['return_code'])) {
                    $ret = $tmp;
                }
            }
        }

        return $ret;
    }

    /**
     * 从微信端获取加密公钥
     * @since 2018.12.14
     *
     * @return bool|string
     */
    private function getpublickey() {
        $keys = ZF_ROOT . 'secret/'. $this->_config['appid'] . md5(php_uname()) . '.pub';
        $ret = '';
        $path = pathinfo($keys, PATHINFO_DIRNAME);
        if (!file_exists($path)) mkdir($path, 0777, true);
        if (file_exists($keys)) {
            $ret = file_get_contents($keys);
        }else{
            $url = 'https://fraud.mch.weixin.qq.com/risk/getpublickey';
            $params = array(
                'mch_id' => $this->_config['mch_id'],
                'nonce_str' => Wepay::getNonceStr(),
            );
            $params['sign'] = Wepay::MakeSign($params, $this->_config['pay_key']);
            $xml = Wepay::xml_encode($params);
            $r = Common::_postRequest($url, $xml, $this->Build_SSL_Array());
            if ($r) {
                $tmp = Wepay::xml2array($r);
                if (isset($tmp['return_code']) && $tmp['return_code'] == 'SUCCESS' && isset($tmp['pub_key'])) {
                    $ret = $tmp['pub_key'];
                    file_put_contents($keys, $ret);
                }
            }
        }
        return $ret;
    }

    /**
     * 微信付款到银行卡，敏感信息加密
     * @since 2018.12.14
     *
     * @param string $info
     * @return bool|string
     */
    public function rsa_public_info_encrypt($info = '') {
        $ret = '';
        if ($info) {
            $ret = DataSafe::RsaPubEncrypt($info, $this->getpublickey());
        }
        return $ret;
    }

    public function query_bank($tno = '') {
        $ret = array();
        if ($tno) {
            $url = 'https://api.mch.weixin.qq.com/mmpaysptrans/query_bank';
            $params = array(
                'mch_id' => $this->_config['mch_id'],
                'partner_trade_no' => $tno,
                'nonce_str' => Wepay::getNonceStr(),
            );
            $params['sign'] = Wepay::MakeSign($params, $this->_config['pay_key']);
            $xml = Wepay::xml_encode($params);
            $r = Common::_postRequest($url, $xml, $this->Build_SSL_Array());
            if ($r) {
                $tmp = Wepay::xml2array($r);
                if (isset($tmp['return_code'])) {
                    $ret = $tmp;
                }
            }
        }
        return $ret;
    }

    /**
     * 银行CODE列表，仅供参考
     * @var array
     */
    public static $bank_code_list = array(
        1002 => '工商银行',
        1005 => '农业银行',
        1026 => '中国银行',
        1003 => '建设银行',
        1001 => '招商银行',
        1066 => '邮储银行',
        1020 => '交通银行',
        1004 => '浦发银行',
        1006 => '民生银行',
        1009 => '兴业银行',
        1010 => '平安银行',
        1021 => '中信银行',
        1025 => '华夏银行',
        1027 => '广发银行',
        1022 => '光大银行',
        1032 => '北京银行',
        1056 => '宁波银行',
    );
}