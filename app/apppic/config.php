<?php
// +----------------------------------------------------------------------
// | Tplay [ WE ONLY DO WHAT IS NECESSARY ]
// +----------------------------------------------------------------------
// | Copyright (c) 2017 http://tplay.pengyichen.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 听雨 < 389625819@qq.com >
// +----------------------------------------------------------------------

//配置文件
return [
    'view_replace_str' => [
        '__CSS__'    => '/static/admin/css',
        '__PUBLIC__' => '/static/public',
        '__JS__'     => '/static/admin/js',
    ],
    'wx_pay_config'    => [
    	// 小程序
        'appid'     => 'wx8f6d9c5c5e521140',
        // 商户号
        'mch_id'    => '1520212901',
        // 秘钥
        'mch_secret' => 'WYXLSB64higongyi201031lezhong201',
        // 微信退款地址
        'refund_url' => 'https://api.mch.weixin.qq.com/secapi/pay/refund',
        // 退款异步通知地址
        'refund_notify_url' => 'https://gynx.appudid.cn/apppic/Pay/refundNotify',
        // 证书路径
        'cert_key_path' => '/www/cert/gynx.appudid.cn/apiclient_key.pem',
        'cert_path' => '/www/cert/gynx.appudid.cn/apiclient_cert.pem',
    ]
];