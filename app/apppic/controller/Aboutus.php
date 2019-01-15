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
namespace app\apppic\controller;

use think\Cache;
use think\Controller;
use think\Loader;
use think\Db;
use think\Cookie;
use think\Session;

class Aboutus extends Index {

    function aboutApi(){
        $aboutus=Db::name('about')->select();
        $this->returnMessage['code']='success';
        $this->returnMessage['message']='获取成功';
        $this->returnMessage['data']=$aboutus;
        return json($this->returnMessage);
    }
}
