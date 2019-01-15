<?php

namespace app\apppic\controller;

use think\Controller;
use think\Db;
use think\Cookie;
use think\Session;

class Message extends Index {

    function getMessage(){
        $message=$this->request->param('message');
        if(!$message){
            $this->returnMessage['code']='error';
            $this->returnMessage['message']='消息不能为空';
        }else{
            $insert_message=Db::name('messages')->insert([
                'create_time'=>time(),
                'ip'=>$this->request->ip(),
                'message'=>$message

            ]);
            if($insert_message<0){
                $this->returnMessage['code']='error';
                $this->returnMessage['message']='发送失败，请检查网络后重试！';
            }else{
                $this->returnMessage['code']='success';
                $this->returnMessage['message']='发送成功';
            }
        }
        return json($this->returnMessage);
    }
}
