<?php
namespace app\apppic\model;
use WX_PAY\WX_PAY_API;
class Pay extends Base
{

    /**
     * @brief    退款的前置处理
     * @desc     主要用于验证业务处理,是否可以退款，
     *           可以退款则返回订单信息，失败则抛出异常
     * @Author   zyy
     * @DateTime 2019-01-10
     * @param    [type]     $params [description]
     * @return   [type]             [description]
     */
    public function beforePayRefund($params)
    {
        // 业务验证
        if (false) {
            throw new \Exception("订单不合法", -100);
        }
        return $params;
    }

    // 统一微信接口参数
    private function wxParseParams($params = [])
    {
        $args = [];
        if (empty($params['transaction_id']) && empty($params['out_trade_no'])) {
            throw new \Exception("请输入微信订单号或者商户订单号", -201);
        } 
        $out_trade_no = $params['out_trade_no']; // 商户订单号
        # 商户订单退款验证逻辑
        # ......
        if (empty($params['total_fee']) || intval($params['total_fee']) < 0) {
            throw new \Exception("请输入订单总费用", -202);
        }
        if (empty($params['refund_fee']) || intval($params['refund_fee']) < 0) {
            throw new \Exception("请输入退款金额", -203);
        }
        $args['refund_desc'] = $params['refund_desc'] ?? '退款';
        // 接口必传参数已验证，请自行验证其它参数
        $args['total_fee'] = $params['total_fee'];  // 订单金额（单位分）
        $args['refund_fee'] = $params['refund_fee']; // 退款金额(单位分)
        $args['out_trade_no'] = $out_trade_no;  // 商户订单号
        $args['out_refund_no'] = $args['out_refund_no'] ?? $out_trade_no;; // 商户退款单号
        if (empty($args)) {
            throw new \Exception("退款信息错误", -204);
        }
        return $args;
    }

    // 微信支付退款申请
    public function wxPayRefund($args)
    {
        try {
            $params = $this->wxParseParams($args);  
            $params['appid'] = config('wx_pay_config.appid');
            $params['mch_id'] = config('wx_pay_config.mch_id');
            $params['notify_url'] = config('wx_pay_config.refund_notify_url');
            // 初始化请求
            $WPA = new WX_PAY_API();
            $WPA->APPID = $params['appid'];
            $WPA->MCH_ID = $params['mch_id'];
            $WPA->PAY_KEY = config('wx_pay_config.mch_secret');
            $WPA->KEY_PATH = config('wx_pay_config.cert_key_path');
            $WPA->CERT_PATH = config('wx_pay_config.cert_path');
            $params['nonce_str'] = $WPA->getNonceStr();
            $params['sign_type'] = 'MD5';
            // 签名
            $params['sign'] = $WPA->makeSign($params);
            $params = $WPA->toXml($params);
            $res = $WPA->postXmlCurl($params, config('wx_pay_config.refund_url'), true);
            $res = $WPA->xmlToArray($res);
            if (isset($res['return_code']) && $res['return_code'] != 'SUCCESS') {
                throw new Exception($res['return_msg'] ?? '失败', -300);
            }
            if (isset($res['result_code']) && $res['result_code'] != 'SUCCESS') {
                throw new Exception($res['err_code_des'] ?? '失败', -301);
            }
            return $args;
        } catch (\Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }
        return $args;
    }

    // 退款申请提交成功后的业务处理
    public function afterPayRefund($params)
    {
        # 处理退款成功的逻辑
        return ['code' => 1, 'msg' => 'success','data'=>$params];
    }



    // 异步通知的前置操作，验证订单业务。。
    public function beforeWxPayRefundNotify($orderInfo)
    {
        # 订单业务...
        return true;
    }

    // 微信退款异步通知的签名验证
    public function wxRefundCheckSign()
    {
        $responseData = file_get_contents("php://input");
        if (empty($responseData)) {
            throw new \Exception("订单信息错误", -100);
        }
        $WPA = new WX_PAY_API();
        $datas = $WPA->xmlToArray($responseData);
        if (empty($datas) || empty($datas['req_info'])) {
            throw new \Exception("订单信息错误", -101);
        }
        $decrypt = base64_decode($datas['req_info'], true);
        $xmlStr = openssl_decrypt(
            $decrypt,
            'aes-256-ecb',
            md5(config('wx_pay_config.mch_secret')),
            OPENSSL_RAW_DATA
        );
        $datas['orderInfo'] = $WPA->xmlToArray($xmlStr);
        return $datas['orderInfo'];
    }

    // 微信支付退款异步通知处理
    public function wxPayRefundNotify()
    {
        $orderInfo = [];
        try {
            $orderInfo = $this->wxRefundCheckSign();
            $this->beforeWxPayRefund($orderInfo);
            $this->afterPayRefundNotify($orderInfo);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), $e->getCode());
        }
        return $orderInfo;
    }

    // 退款成功后的订单业务处理
    public function afterPayRefundNotify($orderInfo)
    {
        # 订单业务...
        return true;
    }

    // 返回给微信的xml
    public function returnWxMsg($isSuccess, $extra = [])
    {
        $xml = "<xml>";
        if (!is_array($extra)) {
            $extra = [];
        }
        $extra['return_code'] = $isSuccess ? 'SUCCESS' : 'FAIL';
        $extra = array_reverse($extra);
        foreach ($extra as $key => $val) {
            if (is_numeric($val)){
                $xml.="<".$key.">".$val."</".$key.">";
            }else{
                $xml.="<".$key."><![CDATA[".$val."]]></".$key.">";
            }
        }
        $xml.="</xml>";
        return $xml; 
    }
}
