<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/1/11 0011
 * Time: 10:54
 */

namespace app\index\controller;

use app\index\controller\Base;
use think\Db;

class Shop extends Base
{
    public function SetOrder($user_id, $music_id, $vip, $number = 1, $money,$link_name,$link_tel,$link_addressed,$link_wechat)
    {
        //余票数量
        $yp=Db::name('music')->where('music_id',$music_id)->value('number');
        if ($yp>0&&$yp >= $number){
            $yp=$yp-$number;
            Db::name('music')->where('music_id',$music_id)->update(['number'=>$yp]);
        }else{
            return $this->aerror('余票不足');
        }

        $info=[
            'user_id'=>$user_id,
            'music_id'=>$music_id,
            'vip'=>$vip,
            'number'=>$number,
            'money'=>$money*$number,
            'add_time'=>time(),
            'link_name'=>$link_name,
            'link_tel'=>$link_tel,
            'link_addressed'=>$link_addressed,
            'link_wechat'=>$link_wechat,
        ];

        $res=Db::name('order')->insertGetId($info);

        if ($res>0){
            return $this->asuccess('订单成功',['order_id'=>$res]);
        }else{
            return $this->aerror('订单失败');
        }


    }
}