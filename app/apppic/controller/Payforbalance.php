<?php
namespace app\apppic\controller;

use WX_PAY\WX_PAY_API;
use think\Db;

class Payforbalance extends Base {

    function wechat_app_balance_pay() {

        // 拉取订单信息
        $oid = $this->request->param('oid');
        $order = Db::name('member_balance_order')->where('id', $oid)->find();
        if (! $order) {
            return $this->returnMessage('error', '订单信息不正确！');
        }

        // // 拉取升级所需金额
        // $needMoney = Db::name('member_level')->where('id', $order['tolevel'])
        //     ->field('need_money')
        //     ->find()['need_money'];
        $money = $order['balance'];

        // 初始化请求
        $WPA = new WX_PAY_API();
        $submitValues['appid'] = $WPA->APPID;
        $submitValues['mch_id'] = $WPA->MCH_ID;
        $submitValues['nonce_str'] = $WPA->getNonceStr();
        // $submitValues['body'] = $order['tolevel'] == 2 ? '升级合伙人' : '升级黄金合伙人';
        $submitValues['body'] = "open_or_recharge:1";
        $submitValues['out_trade_no'] = 'bid|' . $oid;
        // $submitValues['total_fee'] = $money;
        $submitValues['total_fee'] = 1;
        $submitValues['spbill_create_ip'] = $this->request->ip();
        $submitValues['notify_url'] = $this->request->domain() . '/index/pay/notify';
        $submitValues['trade_type'] = 'APP';
        $submitValues['sign_type'] = 'MD5';

        // 签名
        $submitValues['sign'] = $WPA->makeSign($submitValues);

        // 数组转xml
        $submitValues = $WPA->toXml($submitValues);

        // xml转数组
        $res = $WPA->xmlToArray($WPA->postXmlCurl($submitValues));

        if ($res['return_code'] == 'SUCCESS' && $res['result_code'] == 'SUCCESS') {
            // 如果正确的话，代替APP，直接生成APP所需数据
            // 初始化请求
            $finalWPA = new WX_PAY_API();
            $finalValues['appid'] = $res['appid'];
            $finalValues['partnerid'] = $res['mch_id'];
            $finalValues['prepayid'] = $res['prepay_id'];
            $finalValues['package'] = 'Sign=WXPay';
            $finalValues['noncestr'] = $res['nonce_str'];
            $finalValues['timestamp'] = time();
            // 签名
            $finalValues['sign'] = $finalWPA->makeSign($finalValues);
            $this->returnMessage['data'] = $finalValues;
            return json($this->returnMessage);
        } else {
            return $this->returnMessage('error', $res);
        }
    }

    function wechat_app_balance_pay2() {

        // 拉取订单信息
        $oid = $this->request->param('oid');
        $order = Db::name('member_balance_order')->where('id', $oid)->find();

        if (! $order) {
            return $this->returnMessage('error', '订单信息不正确！');
        }

        // 拉取升级所需金额
        // $needMoney = Db::name('member_level')->where('id', $order['tolevel'])
        //     ->field('need_money')
        //     ->find()['need_money'];
        $money = $order['balance'];

        // 初始化请求
        $WPA = new WX_PAY_API();
        $submitValues['appid'] = $WPA->APPID;
        $submitValues['mch_id'] = $WPA->MCH_ID;
        $submitValues['nonce_str'] = $WPA->getNonceStr();
        // $submitValues['body'] = $order['tolevel'] == 2 ? '升级合伙人' : '升级黄金合伙人';
        $submitValues['body'] = "open_or_recharge:1";
        $submitValues['out_trade_no'] = 'bid|' . $oid;
        // $submitValues['total_fee'] = $money;
        $submitValues['total_fee'] = 1;
        $submitValues['spbill_create_ip'] = $this->request->ip();
        $submitValues['notify_url'] = $this->request->domain() . '/index/pay/notify';
        $submitValues['trade_type'] = 'APP';
        $submitValues['sign_type'] = 'MD5';

        // 签名
        $submitValues['sign'] = $WPA->makeSign($submitValues);

        // 数组转xml
        $submitValues = $WPA->toXml($submitValues);

        // xml转数组
        $res = $WPA->xmlToArray($WPA->postXmlCurl($submitValues));

        if ($res['return_code'] == 'SUCCESS' && $res['result_code'] == 'SUCCESS') {
            // 如果正确的话，代替APP，直接生成APP所需数据
            // 初始化请求
            // $finalWPA = new WX_PAY_API();
            // $finalValues['appid'] = $res['appid'];
            // $finalValues['partnerid'] = $res['mch_id'];
            // $finalValues['prepayid'] = $res['prepay_id'];
            // $finalValues['package'] = 'Sign=WXPay';
            // $finalValues['noncestr'] = $res['nonce_str'];
            // $finalValues['timestamp'] = time();
            // // 签名
            // $finalValues['sign'] = $finalWPA->makeSign($finalValues);
            
            return $this->returnMessage('success','success' ,$res);
        } else {
            return $this->returnMessage('error', $res);
        }
    }

    function alipay_app_balance_pay() {
        /*
         * echo $this->request->domain() . '/index/pay/notify_alipay';
         * die();
         */

        // 拉取订单信息
        $oid = $this->request->param('oid');
        $order = Db::name('member_balance_order')->where('id', $oid)->find();

        if (! $order) {
            return $this->returnMessage('error', '订单信息不正确！');
        }

        // 拉取升级所需金额
        // $needMoney = Db::name('member_level')->where('id', $order['tolevel'])
        //     ->field('need_money')
        //     ->find()['need_money'];
        $needMoney = $order['balance'];

        import('ALI_PAY.AopClient');
        import('ALI_PAY.request.AlipayTradeAppPayRequest');
        $aop = new \AopClient();
        $aop->gatewayUrl = "https://openapi.alipay.com/gateway.do";
        $aop->appId = "2018082261122177";
        $aop->rsaPrivateKey = 'MIIEpAIBAAKCAQEAunxjhASi0dAHwsa5hMU5OL5orE8Klf8m93axzVNX5FjRch5wlrnRXifKNkzTa4ThX1WJBBbJDTCnyUs7y/PB6udLE4MRjCQOMPzkQ0/Uny+BAPfBCB91jDqxkY33V59hYcA/UFfxv0Uw1eIGG8QzfFaANs4seZsvOP9bplM2aMB0TdGecB430b4wliWS32WUS44skgCj2jI9aGb8njKVng+ZFi67idSDzZ9dfkfPV6FQhpLo5txQF5gt+HdZYjl6BeiYZf0ZzTFQx4c+nOUpxrrLTw4RYUGkYXzsmAfw80ie6t/UkL7jMwWK8tUCDNkti+ueh5obf9CXanjcf4HhUQIDAQABAoIBAQCTjb50A/MMjkzFudWqjx7Cni5WQEhA/N1JKppuuwYtQHWglSNCr45QsK0YH9udFEv60cQS/zBfhmMMK8IJkzqZch7+NlPZDeJIsqCt/elfdwfcyyvqEHJC3WIIqErQAuTbonC2Uo+OZuHIKgnpSGnwdMUIGEQqiDZqI+rRhL/lSx5WVvu4kzJMi1+nTNH/gOcsPLqZln7zeDxcJOagactRzfhvgSdBqXgHKAaJQqFe3Zh2BTUWoppbDIBetC8mJWcFuT3600oxPZ34KZp1ZKFOf+VnGhY47sWL6hLxiAz9qte0Fkfv+fgeaFyb8hcllAvv8KpjY8FrI1wTekMJQtoBAoGBAPJ62DiK6uZ9LbVhy3FBokaSRvKbXaWAUUxiybuGln3j+8t+ahmx+iI1JU3c4KH2aB3vEgUKHIp4Q9YnmfwcMVTneiAs7C9K/oUlX4XXkdFR9Irmb/yWchTU52VwNoZ3E8TFliqQAVcD0Q0uMEEdrTThBdZOgEPqFn7vTE7rdWIhAoGBAMTiSWY2pNsYHom3Koxtnopgifks+JsKrlCkSwHxiidMtcUVlef6FN/d+jfUbKs6MqZWhsEoNdTol8nB2BZsXRowJgchHv2ruSA7dMSb/7J4DnGLic8WvpDVk8LiJ33/OeVQKhAvNNnTAxaU5+EjkMYywu+u1skzDitEQoY+mPkxAoGAVGUBQJzJKTPEZu8EugSYEGv1GZeNvn6szSNNB2HOmz5wcuEq2Iqjfqh0tWb9ICH9Fv4QjYR5bpBxO+ZIqADAAscWMICyK7u0Xm8lkhX3gJ0/ueB+dbF/P1TivOGTeLWVQdVrcKDydnCzoBzFWsSTdmYje/WSmxCsh72OF6HIF0ECgYEAnBfoJ5CVPVsvAvJkWneiZYVKfnJoG8vpDehjy4Ori6Lmzf1iH0wHdsGv2smg6lQ2yef56HQv0cAib4QvBQAfBF8+FxLsViPqnyJkXmhr3hwPH3iI5tXaekvKXY0d0Ggkh6j54GD83uHMJgAioz2mT2z5XjvY8CV5S1ZQA8znTVECgYBBa3hvVoQXajreiUvmXqB2IFU1MB+2x/ViQ+0y9Srvv50EXzM8cp/mrNecDQQosCmMBcNZs1N6OYd1DyaL92peLXERuc11rJZEpiCat49PDMihFw09rm8q2cvpyC0tJhMQs8ubOo3Dv5uBsK4OW8eU9Po+QgRt+Mj3bbXtl4Bodw==';
        $aop->format = "json";
        $aop->charset = "UTF-8";
        $aop->signType = "RSA2";
        $aop->alipayrsaPublicKey = 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAn4myPA9NuTh1d2nfYteVbfC9Lpq++9D6u8DcfmRlJOW5HJuNP0w3XNMQeKxuARd1CzLXTe9JPtchE3JoDtGRlzsnU1PdxQw8aAOwO0NzLTF3Y5EB8C2uqSFbSJ4Q72AnlQUT9+Uc+qod82H3gi6ZGva9O//x2zQzuF7LucYfdVSx1BcGO90Nr2M2Xa/aKr4S9EdYEGqbyielKkn4kCoq/8KSzDlKYrsCeRMlLYpV7VfeSTQFe/3RhV8ZitB0ZqAnZH3BK/ydHNWuZebCBg+AvpOnj1nm7daQa6zHU3OpTeyRJGLlXIhBPl9oKcQ+hOKPmLdn4chTKKad6BU7jmNmPwIDAQAB';
        // 实例化具体API对应的request类,类名称和接口名称对应,当前调用接口名称：alipay.trade.app.pay
        $request = new \AlipayTradeAppPayRequest();
        // SDK已经封装掉了公共参数，这里只需要传入业务参数
        $body = $order['notes'];
        $bizcontent = "{\"body\":\"" . $body . "\"," . "\"subject\": \"" . $body . "\"," . "\"out_trade_no\": \"" . 'bid|' . $oid . "\"," . "\"timeout_express\": \"30m\"," . "\"total_amount\": \"" . $needMoney . "\"," . "\"product_code\":\"QUICK_MSECURITY_PAY\"" . "}";
        $request->setNotifyUrl($this->request->domain() . '/apppic/payforbalance/notify_alipay');
        /*
         * dump($bizcontent);
         * die();
         */
        $request->setBizContent($bizcontent);
        // 这里和普通的接口调用不同，使用的是sdkExecute
        $response = $aop->sdkExecute($request);
        // htmlspecialchars是为了输出到页面时防止被浏览器将关键参数html转义，实际打印到日志以及http传输不会有这个问题
        // echo htmlspecialchars($response); // 就是orderString 可以直接给客户端请求，无需再做处理。

        $this->returnMessage['data'] = $response;
        
        return json($this->returnMessage);
    }

    function notify_alipay() {
        // 接收数据
        if ($this->request->param('trade_status') == 'TRADE_SUCCESS') {
            Db::name('temp_log')->insert([
                'content' => $this->request->param('trade_status') . 'BID:' . $this->request->param('out_trade_no'),
                'time' => time(),
                'from' => 2
            ]);
            
            $orderID = explode('|', $this->request->param('out_trade_no'))[1];
            
            $order = Db::name('member_balance_order')->where('id', $orderID)->find();
            if ($order['state'] == 1) {
                return 'success';
            } else {
                if (member_balance($order['mid'], $order['balance'], $orderID,$order['notes'])) {
                    return 'success';
                }
            }
        }

        /*
         * if ($datas['return_code'] == 'SUCCESS') {
         * $orderID = explode('|', $datas['out_trade_no'])[1];
         * $order = Db::name('member_level_order')->where('id', $orderID)->find();
         * if ($order['state'] == 1) {
         * $return['return_code'] = 'SUCCESS';
         * $return['return_msg'] = 'OK';
         * echo $WPA->toXml($return);
         * } else {
         * if (member_level_up($order['mid'], $order['tolevel'], $orderID)) {
         * $return['return_code'] = 'SUCCESS';
         * $return['return_msg'] = 'OK';
         * echo $WPA->toXml($return);
         * }
         * }
         * }
         */
    }

    function notify() {
        // 接收数据
        $input = $GLOBALS['HTTP_RAW_POST_DATA'];
        $WPA = new WX_PAY_API();
        $datas = $WPA->xmlToArray($input);

        Db::name('temp_log')->insert([
            'content' => $input,
            'time' => time()
        ]);

        if ($datas['return_code'] == 'SUCCESS') {
            $orderID = explode('|', $datas['out_trade_no'])[1];
            $order = Db::name('member_banlance_order')->where('id', $orderID)->find();
            if ($order['state'] == 1) {
                $return['return_code'] = 'SUCCESS';
                $return['return_msg'] = '支付成功';
                echo $WPA->toXml($return);
            } else {
                if (member_balance($order['mid'], $order['banlance'], $orderID, $order['notes'])) {
                    $return['return_code'] = 'SUCCESS';
                    $return['return_msg'] = '支付成功';
                    echo $WPA->toXml($return);
                }
            }
        }
    }

    function notify2() {
        // 接收数据
        $input = '<xml><appid><![CDATA[wxbbd226949d0b1a4f]]></appid>
<bank_type><![CDATA[CFT]]></bank_type>
<cash_fee><![CDATA[1]]></cash_fee>
<fee_type><![CDATA[CNY]]></fee_type>
<is_subscribe><![CDATA[N]]></is_subscribe>
<mch_id><![CDATA[1502881601]]></mch_id>
<nonce_str><![CDATA[vgudhugwaeu63upk43zzjvl6g3yoo09r]]></nonce_str>
<openid><![CDATA[oAME20mOsE6kiFL1hWh96h6HKOb8]]></openid>
<out_trade_no><![CDATA[oid|31]]></out_trade_no>
<result_code><![CDATA[SUCCESS]]></result_code>
<return_code><![CDATA[SUCCESS]]></return_code>
<sign><![CDATA[8D3E8862F19DF8513749E90F729D3767]]></sign>
<time_end><![CDATA[20180730181253]]></time_end>
<total_fee>1</total_fee>
<trade_type><![CDATA[APP]]></trade_type>
<transaction_id><![CDATA[4200000147201807309968456066]]></transaction_id>
</xml>';
        $WPA = new WX_PAY_API();
        $datas = $WPA->xmlToArray($input);

        Db::name('temp_log')->insert([
            'content' => $input,
            'time' => time()
        ]);

        if ($datas['return_code'] == 'SUCCESS') {
            $orderID = explode('|', $datas['out_trade_no'])[1];
            $order = Db::name('member_balance_order')->where('id', $orderID)->find();
            if ($order['state'] == 1) {
                $return['return_code'] = 'SUCCESS';
                $return['return_msg'] = '支付成功';
                echo $WPA->toXml($return);
            } else {
                if (member_level_up($order['mid'], $order['balance'], $orderID,$order['notes'])) {
                    $return['return_code'] = 'SUCCESS';
                    $return['return_msg'] = '支付成功';
                    echo $WPA->toXml($return);
                }
            }
        }
    }
}
