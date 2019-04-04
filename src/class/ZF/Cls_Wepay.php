<?php
/**
 * Created by PhpStorm.
 * User: jamer
 * Date: 2018/9/21
 * Time: 8:22
 */
namespace ZF;

/**
 * 微信支付相关封装类
 *
 * @package ZF
 * @author  Jamers <jamersnox@zomew.net>
 * @license https://opensource.org/licenses/GPL-3.0 GPL
 * @since   2018.09.21
 */
class Wepay
{

    /**
     * 配置文件名称，可以提前修改
     * @var string
     */
    public static $CONFIG = 'Wechat';
    /**
     * 支付相关配置
     * @var string
     */
    private static $appid = '';
    private static $mchid = '';
    private static $paykey = '';
    private static $sslcert = '';
    private static $sslkey = '';
    private static $wid = '';

    /**
     * 检查签名是否正确，可以使用数组也可以使用XML
     *
     * @param array|string $ary
     * @param string       $key 支付key，填写错误将较验失败
     *
     * @return bool
     */
    public static function checkSign($ary = array(), $key = '')
    {
        $ret = false;
        if (is_string($ary)) {
            $ary = self::xml2array($ary);
        }
        if ($key == '') {
            self::init();
            $key = self::$paykey;
        }
        if ($ary && is_array($ary)) {
            $type = 'MD5';
            if (isset($ary['sign_type'])) {
                $type = $ary['sign_type'];
            }
            if (isset($ary['sign']) && $ary['sign']) {
                $tmp = $ary;
                unset($tmp['sign']);
                $sign = self::makeSign($tmp, $key, $type);
                if (hash_equals($ary['sign'], $sign)) {
                    $ret = true;
                }
            }
        }
        return $ret;
    }

    /**
     * 生成签名值，含HMAC-SHA256算法
     *
     * @param array  $params
     * @param string $key
     * @param string $type
     *
     * @return string
     */
    public static function makeSign($params, $key = '', $type = 'MD5')
    {
        if (!is_array($params)) {
            return '';
        }
        if (isset($params['sign'])) {
            unset($params['sign']);
        }
        //签名步骤一：按字典序排序数组参数
        ksort($params);
        $string = self::toUrlParams($params);
        //签名步骤二：在string后加入KEY
        $string = $string . "&key=" . $key;
        //签名步骤三：MD5加密
        switch (strtoupper($type)) {
            case 'HMAC-SHA256':
                $string = hash_hmac("sha256", $string, $key);
                break;
            default:
                $string = md5($string);
                break;
        }
        //签名步骤四：所有字符转为大写
        $result = strtoupper($string);

        return $result;
    }

    /**
     * 将参数结连成字符串
     *
     * @param array $params
     *
     * @return string
     */
    public static function toUrlParams($params)
    {
        $string = '';
        if (!empty($params)) {
            $array = array();
            foreach ($params as $key => $value) {
                $array[] = $key . '=' . $value;
            }
            $string = implode("&", $array);
        }

        return $string;
    }

    /**
     * XML转数组
     *
     * @param string $xml
     *
     * @return bool|mixed
     */
    public static function xml2array($xml)
    {
        if (!$xml || !is_string($xml)) {
            return false;
        }

        libxml_disable_entity_loader(true);
        $data = json_decode(
            json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)),
            true
        );
        return $data;
    }

    /**
     * 产生随机字符串，不长于32位
     *
     * @param int $length
     *
     * @return string 产生的随机字符串
     */
    public static function getNonceStr($length = 32)
    {
        $chars = "abcdefghijklmnopqrstuvwxyz0123456789";
        $str ="";
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars)-1), 1);
        }
        return $str;
    }

    /**
     * 数组转换成XML
     *
     * @param array $data
     * @param bool  $cdata
     *
     * @return string
     * @since  2018.12.14
     */
    private static function array2xml($data, $cdata = true)
    {
        $xml = '';
        foreach ($data as $key => $val) {
            is_numeric($key) && $key = "item id=\"$key\"";
            $xml .= "<$key>";
            $xml .= (is_array($val) || is_object($val)) ? self::array2xml($val) :
                ($cdata ? self::xmlSafeStr($val) : $val);
            list($key,) = explode(' ', $key);
            $xml .= "</$key>";
        }
        return $xml;
    }

    /**
     * 过滤XML不安全字符串
     *
     * @param string $str
     *
     * @return string
     * @since  2018.12.14
     */
    private static function xmlSafeStr($str)
    {
        return '<![CDATA[' .
            preg_replace("/[\\x00-\\x08\\x0b-\\x0c\\x0e-\\x1f]/", '', $str) .
            ']]>';
    }

    /**
     * XML编码
     *
     * @param mixed  $data     数据
     * @param string $root     根节点名
     * @param bool   $cdata    是否添加CDATA标签
     * @param string $attr     根节点属性
     * @param string $id       数字索引子节点key转换的属性名
     * @param string $encoding 数据编码
     *
     * @return string
     */
    public static function xmlEncode(
        $data,
        $root = 'xml',
        $cdata = true,
        $attr = '',
        $id = 'id',
        $encoding = 'utf-8'
    ) {
        if (is_array($attr)) {
            $attr = array();
            foreach ($attr as $key => $value) {
                $attr[] = "{$key}=\"{$value}\"";
            }
            $attr = implode(' ', $attr);
        }
        $attr = trim($attr);
        $attr = empty($attr) ? '' : " {$attr}";
        $xml = "<{$root}{$attr}>";
        //$xml .= self::array2xml($data, $item, $id);
        $xml .= self::array2xml($data, $cdata);
        $xml .= "</{$root}>";
        return $xml;
    }


    /**
     * 统一下单接口
     *
     * @param array  $ary
     * @param string $openid
     * @param bool   $isTest
     *
     * @return array
     * @throws \Exception
     * @since  2018.12.26
     */
    public static function unifiedOrder($ary = array(), $openid = '', $isTest = false)
    {
        self::init();
        $ret = array('code' => -1, 'msg' => '未知错误','data' => null,);
        if (!$openid) {
            return array('code' => 2, 'msg' => '跳转支付openid不正确',);
        }
        $paykey = self::$paykey;
        $url = "https://api.mch.weixin.qq.com/pay/unifiedorder";
        if ($isTest) {
            //沙盒测试时只能是1.01及1.02
            $ary['total_fee'] = 101;
            $url = self::getTestUrl($url);
            $signurl = 'https://api.mch.weixin.qq.com/sandboxnew/pay/getsignkey';
            $p = array(
                'mch_id' => self::$mchid,
                'nonce_str' => self::getNonceStr(),
            );
            $p = self::makeSign($p, $paykey);
            $x = self::xmlDecode(
                self::postXmlCurl(self::xmlEncodeSimple($p), $signurl)
            );

            $msg = 'request='.$signurl.'?'.urldecode(http_build_query($p)).
                ',response='.json_encode($x);
            Common::_savelog('_unifiedorder.txt', $msg, false, true);

            if (isset($x['sandbox_signkey'])) {
                $paykey = $x['sandbox_signkey'];
            }
        }
        $list = explode(',', 'body,detail,out_trade_no,total_fee,notify_url,openid');
        $lost = array();
        foreach ($list as $v) {
            if (!isset($ary[$v])) {
                $lost[] = $v;
            }
        }
        if ($lost) {
            $ret = array('code' => 1, 'msg' => '支付参数中缺少以下字段:' . implode(',', $lost));
        } else {
            /**
             * $ary     所需要的参数
             * body             商品描述
             * detail           详细描述
             * attach           附加数据，返回时原样返回
             * out_trade_no     商户订单号
             * total_fee        价格（单位分）
             * notify_url       支付成功回调地址
             * openid           用户标识（原公众号openid）
             */
            // 支付参数
            $attach = '';
            if (isset($ary['attach'])) {
                $attach = $ary['attach'];
            }
            $param = array(
                'appid' => self::$appid,                       // 公众号appid
                'mch_id' => self::$mchid,                      // 微信支付商户id
                'nonce_str' => self::getNonceStr(),            // 随机字符串
                'spbill_create_ip' => $_SERVER["REMOTE_ADDR"],  // 发起ip
                'body' => $ary['body'],                         // 商品名称
                'detail' => $ary['detail'],                     // 商品描述
                'out_trade_no' => $ary['out_trade_no'],         // 商户订单号
                'total_fee' => $ary['total_fee'],               // 支付金额单位分
                'notify_url' => $ary['notify_url'],             // 支付结果通知地址
                'trade_type' => 'JSAPI',                        // 支付方式
                'attach' => $ary['openid'].'|'.$attach,         // 附加数据，在查询API和支付通知中原样返回，该字段主要用于商户携带订单的自定义数据
                'openid' => $openid,                            // 发起支付的用户openid
            );

            // 签名算法
            ksort($param);
            $sign = strtoupper(
                md5(urldecode(http_build_query($param)) . '&key=' . $paykey)
            );
            $param['sign'] = $sign;

            $r = self::xmlDecode(
                self::postXmlCurl(self::xmlEncodeSimple($param), $url)
            );

            $msg = 'request='.$url.'?'.urldecode(http_build_query($param)).
                ',response='.json_encode($r);
            Common::_savelog('_unifiedorder.txt', $msg, false, true);

            if ($r['return_code'] != 'SUCCESS') {
                $ret = array('code' => 1, 'msg' => $r['return_msg']);
            } else {
                // 返回给jsapi数据
                $rest['appId'] = $r['appid'];
                $rest['timeStamp'] = strval(time());
                $rest['nonceStr'] = $r['nonce_str'];
                $rest['package'] = 'prepay_id='.$r['prepay_id'];
                $rest['signType'] = 'MD5';
                ksort($rest);
                $sign = strtoupper(
                    md5(urldecode(http_build_query($rest)).'&key='.$paykey)
                );
                $rest['paySign'] = $sign;

                return array('code'=>0, 'msg'=>'success', 'data'=>$rest);
            }
        }
        return $ret;
    }

    /**
     * 生成沙盒链接，用于测试
     *
     * @param string $url
     *
     * @return string
     */
    public static function getTestUrl($url = '')
    {
        $ret = '';
        if ($url) {
            $ret = $url;
            if (preg_match('%^(https?://[^/]*/)(sandboxnew/)?(.*)$%i', $url, $m)) {
                if (!$m[2]) {
                    $ret = $m[1].'sandboxnew/'.$m[3];
                }
            }
        }
        return $ret;
    }

    /**
     * 以post方式提交xml到对应的接口url
     *
     * @param string $xml     需要post的xml数据
     * @param string $url     url
     * @param bool   $useCert 是否需要证书，默认不需要
     * @param int    $second  url执行超时时间，默认30s
     *
     * @throws \Exception
     * @return mixed
     */
    private static function postXmlCurl($xml, $url, $useCert = false, $second = 30)
    {
        $ch = curl_init();
        //设置超时
        curl_setopt($ch, CURLOPT_TIMEOUT, $second);

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);//严格校验
        //设置header
        curl_setopt($ch, CURLOPT_HEADER, false);
        //要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        if ($useCert == true) {
            self::init();
            //设置证书
            //使用证书：cert 与 key 分别属于两个.pem文件
            if (self::$sslcert && self::$sslkey) {
                curl_setopt($ch, CURLOPT_SSLCERTTYPE, 'PEM');
                curl_setopt($ch, CURLOPT_SSLCERT, self::$sslcert);
                curl_setopt($ch, CURLOPT_SSLKEYTYPE, 'PEM');
                curl_setopt($ch, CURLOPT_SSLKEY, self::$sslkey);
            } else {
                throw new \Exception("未设置SSL证书，无法发起相应请求");
            }
        }
        //post提交方式
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
        //运行curl
        $data = curl_exec($ch);
        //返回结果
        if ($data) {
            curl_close($ch);
            Common::_savelog('_postxml.txt', $url . "\r\n" . $xml . "\r\n\r\n" . $data . "\r\n\r\n");
            return $data;
        } else {
            $error = curl_errno($ch);
            curl_close($ch);
            throw new \Exception("curl出错，错误码:$error");
        }
    }

    /**
     * 将数组转成XML
     *
     * @param array $arr
     *
     * @return string
     */
    public static function xmlEncodeSimple($arr)
    {
        $xml = "<xml>";
        foreach ($arr as $key => $val) {
            if (is_numeric($val)) {
                $xml.="<".$key.">".$val."</".$key.">";
            } else {
                $xml.="<".$key."><![CDATA[".$val."]]></".$key.">";
            }
        }
        $xml.="</xml>";
        return $xml;
    }

    /**
     * 将XML转成数组
     *
     * @param string $xml
     *
     * @return mixed
     */
    public static function xmlDecode($xml)
    {
        //禁止引用外部xml实体
        libxml_disable_entity_loader(true);
        $values = json_decode(
            json_encode(
                simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)
            ),
            true
        );
        return $values;
    }

    /**
     * 生成签名
     *
     * @param array  $param
     * @param string $paykey
     *
     * @return array
     */
    public static function sign($param = array(), $paykey = '')
    {
        self::init();
        if (isset($param['sign'])) {
            unset($param['sign']);
        }
        if ($paykey == '') {
            $paykey = self::$paykey;
        }
        ksort($param);
        $sign = strtoupper(
            md5(urldecode(http_build_query($param)) . '&key=' . $paykey)
        );
        $param['sign'] = $sign;
        return $param;
    }

    /**
     * 获取config值
     *
     * @param string $config
     *
     * @return string
     * @since  2018.12.25
     */
    protected static function getConfig($config = '')
    {
        if ($config == '' && self::$CONFIG && is_string(self::$CONFIG)) {
            $config = self::$CONFIG;
        }
        return $config;
    }

    /**
     * 初始化环境参数
     *
     * @param string $config
     * @param bool   $focus
     *
     * @return array
     * @since  2018.12.26
     */
    public static function init($config = '', $focus = false)
    {
        $ret = array();
        if ($focus || !self::$appid || !self::$mchid || !self::$paykey) {
            $conf = Common::LoadConfigData(self::getConfig($config), 'wepay');
            if ($conf) {
                foreach ($conf as $k => $v) {
                    if (isset(self::${$k}) && $v) {
                        self::${$k} = $v;
                    }
                }
                $ret = $conf;
            }
        }
        return $ret;
    }
}
