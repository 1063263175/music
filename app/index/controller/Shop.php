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
    /**
     * 添加订单
     * @param $user_id
     * @param $music_id
     * @param $vip
     * @param int $number
     * @param $money
     * @param $link_name
     * @param $link_tel
     * @param $link_addressed
     * @param $link_wechat
     * @return \think\response\Json
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function SetOrder($user_id, $music_id, $vip, $number = 1, $money,$link_name,$link_tel,$link_addressed,$link_wechat)
    {
        //余票数量
        $yp=Db::name('music')->where('music_id',$music_id)->value('number');
        if ($yp>0&&$yp >= $number){
            $yp=$yp-$number;
            Db::name('music')->where('music_id',$music_id)->update(['number'=>$yp]);
           $info=[
            'user_id'=>$user_id,
            'music_id'=>$music_id,
            'vip'=>$vip,
            'number'=>$number,
            'money'=>$money,
            'add_time'=>time(),
            'link_name'=>$link_name,
            'link_tel'=>$link_tel,
            'link_addressed'=>$link_addressed,
            'link_wechat'=>$link_wechat,
        ];

        $res=Db::name('order')->insertGetId($info);

        if ($res>0){
            $data['status']='订单成功';
            $data['order_id']=$res;
        }else{
           $data['status']='订单失败';
            $data['order_id']=0;  
          
         }                
          
          
        }else{
           
           $data['status']='余票不足';
            $data['order_id']=0;
        }

       
       return  json($data);
    }

    /**
     * 音乐节参与会员
     * @param $music_id
     * @param int $page
     * @param int $pagelimit
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function GetMusicUserList($music_id, $page = 1, $pagelimit = 20)
    {
        $list=Db::name('order')
            ->alias('or')
            ->join('tplay_user','or.user_id = tplay_user.user_id','right')
            ->where('or.music_id',$music_id)
            ->where('is_pay',1)
            ->order('pay_time','desc')
            ->page($page,$pagelimit)
            ->select();
        return json($list);
    }


    /**
     * 获取用户抽奖次数
     * @param $user_id
     * @param $jiang_id
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function GetUserChou($user_id, $jiang_id)
    {
        $info=[
            'user_id'=>$user_id,
            'jiang_id'=>$jiang_id
        ];
        $res=Db::name('jianng_user')
            ->where($info)
            ->find();
        if (empty($res)){
            Db::name('jianng_user')
                ->insert($info);
            $res['chou_number']=1;
        }
        return json($res['chou_number']);
    }


    /**
     * 添加用户的抽奖次数
     * @param $user_id
     * @param $jiang_id
     * @param int $number
     * @return \think\response\Json
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function SetUserChou($user_id, $jiang_id, $number = 1)
    {
        $info=[
            'user_id'=>$user_id,
            'jiang_id'=>$jiang_id,
        ];
        $res=Db::name('jianng_user')->where('$info')->find();
        if (empty($res)){
            $info['chou_number']=$number+1;
            Db::name('jianng_user')->insert($info);
            $num=$info['chou_number'];
        }else{
            $num=$number+$res['chou_number'];
            $res=Db::name('jianng_user')->where($info)->update(['chou_number'=>$num]);
        }
        return json(['title'=>'剩余抽奖次数','num'=>$num]);
    }





}