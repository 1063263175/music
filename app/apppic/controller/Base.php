<?php

namespace app\apppic\controller;

use think\Request;

/*
 * API基类
 */
class Base
{
    // Request依赖注入
    protected $request;

    // 构造返回信息
    protected $returnMessage = [
        'return_code'    => 'success', // success/error
        'return_message' => 'success', // success/error message
    ];

    // SQL条件构造
    protected $wheres = [];

    // Request依赖注入
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    // 公共返回信息方法
    public function returnMessage($return_code = null, $return_message = null, $data = null)
    {
        if (!empty($return_code)) {
            $this->returnMessage['return_code'] = $return_code;
        }
        if (!empty($return_message)) {
            $this->returnMessage['return_message'] = $return_message;
        }
        if (!empty($data)) {
            $this->returnMessage['data'] = $data;
        }
        return json($this->returnMessage);
    }

    // 用户登陆判断
    public function checkLogin()
    {
        if (!session('member')) {
            header('Content-Type:application/json; charset=utf-8');
            $this->returnMessage['return_code']    = 'error';
            $this->returnMessage['return_message'] = '您还未登陆，请登陆。';
            echo json_encode($this->returnMessage, JSON_UNESCAPED_UNICODE);
            exit();
        }
    }
}
