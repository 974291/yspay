<?php
/**
 * @Created by Aler.gl <974291@qq.com>.
 * User: Aler.gl
 * Date: 2020/9/28
 * Time: 13:38
 */

namespace aler\Yspay;

class Yspay implements Settings {

    /**
     * 商户号
     */
    private $seller_id = '';

    /**
     * 商户名
     */
    private $seller_name = '';

    /**
     * 商户证书
     */
    private $cert = '';

    /**
     * 商户key
     */
    private $key = '';

    /**
     * 商户证书key密码
     */
    private $key_password = '';

    /**
     * 业务代码
     */
    private $business_code = '';

    /**
     * 接口名称
     */
    private $method = '';

    /**
     * 异步通知回调url
     */
    private $notify_url = '';

    /**
     * 小程序appid
     */
    private $appid = '';

    /**
     * 正式环境请求地址
     */
    const PAYURL = 'https://qrcode.ysepay.com/gateway.do';

    /**
     * 默认编码
     */
    const CHARSET = 'UTF-8';

    /**
     * 默认加密方式RSA
     */
    const SIGN_TYPE = 'RSA';

    /**
     * 接口版本
     */
    const VERSION = '3.0';

    // 构造初始化
    public function __construct($config) {
        date_default_timezone_set('PRC');
        if (!empty($config)) {
            $this->method        = $config['method'];
            $this->notify_url    = $config['url'];
            $this->seller_id     = $config['seller_id'];
            $this->seller_name   = $config['seller_name'];
            $this->cert          = $config['cert'];
            $this->key           = $config['key'];
            $this->key_password  = $config['key_password'];
            $this->business_code = $config['business_code'];
            $this->appid         = $config['appid'];
        }

        throw new \Exception('Yspay Error');
    }

    public function Config(): array {
        // TODO: Implement Config() method.
    }


    /**
     * 银盛通微信小程序下单接口
     * ysepay.online.weixin.pay
     * @Author   Aler.gl
     * @DateTime 2020-07-28
     * @param $order 订单号 $openid用户openid $total_amount交易金额
     */
    public function wechatPay($order, $openid, $total_amount) {
        $myParams                = [];
        $myParams['charset']     = self::CHARSET;
        $myParams['method']      = $this->method;
        $myParams['partner_id']  = $this->seller_id;
        $myParams['sign_type']   = self::SIGN_TYPE;
        $myParams['timestamp']   = date('Y-m-d H:i:s', time());
        $myParams['version']     = self::VERSION;
        $myParams['notify_url']  = $this->notify_url;
        $biz_content_arr         = [
            "out_trade_no"    => $order, //订单号
            "shopdate"        => $this->datetime2string(date('Ymd')), //商户日期
            "subject"         => "微信小程序下单接口", //交易标题
            "total_amount"    => $total_amount, //订单金额
            "currency"        => "CNY", //币种
            "seller_id"       => $this->seller_id, //商户号
            "seller_name"     =>  $this->seller_name, //商户名
            "timeout_express" => "24h",
            "business_code"   => $this->business_code,
            "sub_openid"      => $openid,//用户关注公众号openid
            "is_minipg"       => 1, //小程序支付
            "appid"           => $this->appid //公众号appid
        ];
        $myParams['biz_content'] = json_encode($biz_content_arr, JSON_UNESCAPED_UNICODE);//构造字符串
        $myParams['sign'] = $this->sign($myParams);
        return $this->postUrl(self::PAYURL, $myParams, 'ysepay_online_weixin_pay_response');
    }

    /**
     * 订单退款接口
     *
     * @param $out_trade_no  订单号
     * @param $trade_no      交易流水号
     * @param $refund_amount 退款金额
     * @param $refund_reason 退款缘由
     */
    public function orderRefund($out_trade_no, $trade_no, $refund_amount, $refund_reason) {
        $myParams               = [];
        $myParams['charset']    = self::CHARSET;
        $myParams['method']     = $this->method;
        $myParams['partner_id'] = $this->seller_id;
        $myParams['sign_type']  = self::SIGN_TYPE;
        $myParams['timestamp']  = date('Y-m-d H:i:s', time());
        $myParams['version']    = self::VERSION;

        $biz_content_arr         = [
            "out_trade_no"   => $out_trade_no,
            "trade_no"       => $trade_no,
            "refund_amount"  => $refund_amount,
            "refund_reason"  => $refund_reason,
            "out_request_no" => 'RD' . $this->datetime2string(date('Y-m-d H:i:s'))
        ];
        $myParams['biz_content'] = json_encode($biz_content_arr, JSON_UNESCAPED_UNICODE);//构造字符串
        $myParams['sign']        = $this->sign($myParams);
        return $this->postUrl(self::PAYURL, $myParams, 'ysepay_online_trade_refund_response');
    }

    /**
     * 分账查询
     * @Author   Aler.gl
     * @DateTime 2020-07-28
     * ysepay.single.division.online.query
     */
    function division_query() {
        $myParams                = [];
        $myParams['charset']     = self::CHARSET;
        $myParams['method']      = $this->method;
        $myParams['partner_id']  = $this->seller_id;
        $myParams['sign_type']   = self::SIGN_TYPE;
        $myParams['timestamp']   = date('Y-m-d H:i:s', time());
        $myParams['version']     = self::VERSION;
        $myParams['notify_url']  = $this->notify_url;
        $biz_content             = [
            "src_usercode" => $this->seller_id,
            "out_batch_no" => "S" . date('YmdHis', time()),
            "out_trade_no" => $this->getOrderNo(time()),
            "sys_flag"     => "DD"
        ];
        $myParams['biz_content'] = json_encode($biz_content, JSON_UNESCAPED_UNICODE);//构造字符串
        $myParams['sign']        = $this->sign($myParams);
        var_dump($myParams);
        $this->postUrl(self::PAYURL, $myParams, 'ysepay_single_division_online_query_response');
    }

    /**
     * 日期转字符
     * @Author   Aler.gl
     * @DateTime 2020-07-28
     * 输入参数：yyyy-MM-dd HH:mm:ss
     * 输出参数：yyyyMMddHHmmss
     */
    private static function datetime2string($datetime) {
        return preg_replace('/\-*\:*\s*/', '', $datetime);
    }

    /**
     * 银盛通支付生成订单号
     * @Author   Aler.gl
     * @DateTime 2020-07-28
     * @return   [type]                   [description]
     */
    private static function getOrderNo($orderno) {
        $prefix = ['LC' => 10, 'DK' => 11, 'HK' => 12, 'FL' => 13, 'GZ' => 14, 'RE' => 15, 'RP' => 16, 'DJ' => 17, 'TX' => 18, 'DF' => 19, 'IG' => 20,];
        $p      = $prefix[substr($orderno, 0, 2)];
        if (!empty($p)) {
            return substr($orderno, 2, strlen($orderno) - 2);
        }
        return false;
    }

    /**
     * 同步响应操作
     * @Author   Aler.gl
     * @DateTime 2020-07-28
     */
    private function respondUrl($params) {
        //返回的数据处理
        @$sign = trim($params['sign']);
        $result = $params;
        unset($result['sign']);
        ksort($result);
        $url = "";
        foreach ($result as $key => $val) {
            if ($val) $url .= $key . '=' . $val . '&';
        }
        $data = trim($url, '&');
        //var_dump($data);
        trace('return|data:' . $data . '|sign:' . $sign, 'paylog');
        if (true == $this->signCheck($sign, $data)) {
            echo "验证签名成功!";
        }

        echo '验证签名失败!';
    }


    /**
     * 异步通知回调url
     * @Author   Aler.gl
     * @DateTime 2020-07-28
     */
    public function notifyUrl($params) {
        @$sign = trim($params['sign']);
        $result = $params;
        unset($result['sign']);
        ksort($result);
        $url = '';
        foreach ($result as $key => $val) {
            if ($val) {
                $url .= $key . '=' . $val . '&';
            }
        }
        $data = trim($url, '&');

        //验证签名，写入日志
        if (true == $this->signCheck($sign, $data)) {
            trace('Verify success!|notify|:' . $data . '|sign:' . $sign, 'pay');
        }

        trace('Validation failure!|notify|:' . $data . '|sign:' . $sign, 'pay');

        echo 'success';
        exit;
    }


    /**
     * 验签转明码
     * @param $sign 签名字符串
     * @Author   Aler.gl
     * @DateTime 2020-07-28
     * @param $data
     * @return   $success
     */
    private function signCheck($sign, $data) {
        $certificateCAcerContent = file_get_contents($this->cert);//公钥
        $certificateCApemContent = '-----BEGIN CERTIFICATE-----' . PHP_EOL . chunk_split(base64_encode($certificateCAcerContent), 64, PHP_EOL) . '-----END CERTIFICATE-----' . PHP_EOL;
        //print_r("验签密钥" . $certificateCApemContent);
        // 签名验证
        $success = openssl_verify($data, base64_decode($sign), openssl_get_publickey($certificateCApemContent), OPENSSL_ALGO_SHA1);
        //var_dump($success);
        return $success;
    }

    /**
     * 签名
     * @Author   Aler.gl
     * @DateTime 2020-07-29
     * @param input data
     */
    private function sign($data) {
        ksort($data);
        $signStr = "";
        foreach ($data as $key => $val) {
            $signStr .= $key . '=' . $val . '&';
        }
        $signStr = trim($signStr, '&');
        $sign    = $this->sign_encrypt(['data' => $signStr]);
        return trim($sign['check']);
    }

    /**
     * 签名加密
     * @Author   Aler.gl
     * @DateTime 2020-07-28
     * @param input data
     * @return success
     * @return check
     * @return msg
     */
    private function sign_encrypt($input) {
        $return = ['success' => 0, 'msg' => '', 'check' => ''];
        $pkcs12 = file_get_contents($this->key);
        if (openssl_pkcs12_read($pkcs12, $certs, $this->key_password)) {
            //var_dump('证书,密码,正确读取');
            $privateKey = $certs['pkey'];
            $publicKey  = $certs['cert'];
            $signedMsg  = "";
            //print_r("加密密钥" . $privateKey);
            if (openssl_sign($input['data'], $signedMsg, $privateKey, OPENSSL_ALGO_SHA1)) {
                //var_dump('签名正确生成');
                $return['success'] = 1;
                $return['check']   = base64_encode($signedMsg);
                $return['msg']     = base64_encode($input['data']);
            }
        }

        return $return;
    }


    /**
     * DES加密方法
     * @Author   Aler.gl
     * @DateTime 2020-07-28
     * @param $data 传入需要加密的证件号码
     * @return string 返回加密后的字符串
     */
    private function ECBEncrypt($data, $key) {
        $encrypted = openssl_encrypt($data, 'DES-ECB', $key, 1);
        return base64_encode($encrypted);
    }

    /**
     * DES解密方法
     * @Author   Aler.gl
     * @DateTime 2020-07-28
     * @param $data 传入需要解密的字符串
     * @return string 返回解密后的证件号码
     */
    private function doECBDecrypt($data, $key) {
        $encrypted = base64_decode($data);
        $decrypted = openssl_decrypt($encrypted, 'DES-ECB', $key, 1);
        return $decrypted;
    }

    /**
     * post发送请求
     * @Author   Aler.gl
     * @DateTime 2020-07-28
     * @param $url
     * @param $myParams
     * @param $response_name
     * @return false|string
     */
    private function postUrl($url, $myParams, $response_name) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($myParams));
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $response = curl_exec($ch);
        if (!curl_errno($ch) && null != $response['sign']) {
            $response = json_decode($response, true);
            $data     = json_encode($response[$response_name], JSON_UNESCAPED_UNICODE);
            return $response;

//            var_dump($response);die;
//            if ($this->signCheck($response['sign'], $data)) {
//                return $response[$response_name];
//            }
//            return false;
        }

    }


}