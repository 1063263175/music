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
        $res=Db::name('jiang_user')
            ->where($info)
            ->find();
        if (empty($res)){
            Db::name('jiang_user')
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
        $res=Db::name('jiang_user')->where('$info')->find();
        if (empty($res)){
            $info['chou_number']=$number+1;
            Db::name('jiang_user')->insert($info);
            $num=$info['chou_number'];
        }else{
            $num=$number+$res['chou_number'];
            $res=Db::name('jiang_user')->where($info)->update(['chou_number'=>$num]);
        }
        return json(['title'=>'剩余抽奖次数','num'=>$num]);
    }


    /**
     * 获取抽奖码
     * @param $user_id
     * @param $jiang_id
     * @return \think\response\Json
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function GetJiangCode($user_id, $jiang_id)
    {
        //判断抽奖次数
        $where=[
            'jiang_id'=>$jiang_id,
            'user_id'=>$user_id,
        ];
        $ctime=Db::name('jiang')->where('jiang_id',$jiang_id)->value('kai_time');
        if (time()>$ctime){
            //抽奖时间超时
            return $this->aerror('抽奖时间已过');
        }

        $num=Db::name('jiang_user')->where($where)->value('chou_number');
        if ($num>0){
            //抽奖次数减一
            Db::name('jiang_user')->where($where)->update(['chou_number'=>$num-1]);
        }else{
            return $this->aerror('抽奖次数没了');
        }

        //读取已存在抽奖码确保唯一
        $ex_code=Db::name('jiang_code')
            ->where('jiang_id',$jiang_id)
            ->field('jiang_code')
            ->select();
        //生成抽奖码
        $code=$this->code($ex_code);
        Db::name('jiang_code')->insert([
           'user_id'=>$user_id,
           'jiang_id'=>$jiang_id,
           'jiang_code'=>$code,
            'add_time'=>time(),
        ]);
        return json(['code'=>$code]);


    }

    /**
     * 生成并验证抽奖码
     * @param $ex_code
     * @return string
     */
    function code($ex_code){
        $code=$this->RandCode();
        if (in_array($code,$ex_code)){
            $this->code($ex_code);
        }else{
            return $code;
        }
    }


    /**
     * 生成随机字符串
     * @return string
     */
    public function RandCode()
    {
        $code="ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $rand=$code[rand(0,25)].strtoupper(dechex(date('m'))) .date('d').substr(time(),-5)  .substr(microtime(),2,5).sprintf('%02d',rand(0,99));
        for(
            $a = md5( $rand, true ),
            $s = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ',
            $d = '',
            $f = 0;
            $f < 8;
            $g = ord( $a[ $f ] ), // ord（）函数获取首字母的 的 ASCII值
            $d .= $s[ ( $g ^ ord( $a[ $f + 8 ] ) ) - $g & 0x1F ],//按位亦或，按位与。
            $f++
        );
        return $d;
    }


    /**
     * 获取抽奖列表
     * @param int $page
     * @param int $pagelimit
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function GetJiangList($page=1,$pagelimit=20)
    {
        $list=Db::name('jiang')
            ->page($page,$pagelimit)
            ->order('jiang_id','desc')
            ->select();
        return json($list);
    }

    /**
     * 获取抽奖信息详情
     * @param $jiang_id
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function GetJiangInfo($jiang_id)
    {
        $info=Db::name('jiang')->where('jiang_id',$jiang_id)->find();
        return json($info);
    }


    /**
     * 获取抽奖结果
     * @param $jiang_id
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function GetJiangRes($jiang_id)
    {
        $list=Db::name('jiang_res')
            ->alias('jr')
            ->join('tplay_jiang_code as jc','jc.jiang_code=jr.jiang_code','left')
            ->join('tplay_user as tu','tu.user_id=jc.user_id','left')
            ->where('jr.jiang_id',$jiang_id)
            ->select();
        return json($list);
    }




}