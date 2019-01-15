<?php
namespace app\index\controller;

use app\index\controller\Base;
use think\Db;

class User extends Base
{
    /**
     * 获取openID
     * @param $code
     * @return bool|mixed|string|\think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function GetOpenid($code)
    {
        $web_config = Db::name('webconfig')->where('web','web')->find();
        //配置appid
        $appid = $web_config['appid'];
        //配置appscret
        $secret = $web_config['appscret'];
        
        //api接口
        $api="https://api.weixin.qq.com/sns/jscode2session?appid=" . $appid . "&secret=" . $secret . "&js_code=" . $code . "&grant_type=authorization_code&connect_redirect=1";
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 500);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($curl, CURLOPT_URL, $api);
        $res = curl_exec($curl);
        curl_close($curl);
        //dump(json_decode($res, true));
        $res=json_decode($res, true);

        //$res['openid']=21121;
        if (!empty($res['openid'])){
            $user=Db::name('user')->where('openid',$res['openid'])->field('user_id')->find();
            if (empty($user)){
                $user['user_id']=Db::name('user')->insertGetId(['openid'=>$res['openid']]);
                $res['set_user_info']=1;
            }else{
                $res['set_user_info']=0;
            }
            $res['user_id']=$user['user_id'];
        }
        $res=json($res);
        return $res;
    }

    /**
     * 获取用户点赞或收藏的活动音乐节夜店的列表
     * @param $user_id
     * @param int $class 1活动 2夜店
     * @param int $page
     * @param int $pagelimit
     * @param string $sort
     * @param string $desc
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function GetCangList($music_class=1,$user_id, $class = 2, $page = 1, $pagelimit = 20, $sort = 'id', $desc = 'desc')
    {
        $list=Db::name('good')
            ->alias('gd')
            ->join('tplay_music mu','gd.music_id=mu.music_id','left')
            ->where('gd.user_id',$user_id)
            ->where('gd.class',$class)
            ->where('mu.class',$music_class)
            ->field('mu.*,gd.user_id')
            ->order($sort,$desc)
            ->page($page,$pagelimit)
            ->select();
        return json($list);
    }


    /**
     * 插入个人信息
     * @param $city
     * @param $country
     * @param $gender
     * @param $nickname
     * @param $province
     * @param $openid
     * @return \think\response\Json
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function SetUserInfo($city, $country, $gender, $nickname, $province,$openid,$head_img)
    {
        $info=[
            'city'=>$city,
            'country'=>$country,
            'gender'=>$gender,
            'nickname'=>$nickname,
            'province'=>$province,
            'head_img'=>$head_img,
        ];
        $res=Db::name('user')->where('openid',$openid)->update($info);
        if ($res!==false){
            return $this->asuccess('添加个人信息成功');
        }else{
            return $this->aerror('添加个人信息失败');
        }
    }

    /**
     * 获取用户订单
     * @param $user_id
     * @param $page
     * @param $pagelimit
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function GetUserOrder($user_id,$page=1,$pagelimit=20)
    {
        $list=Db::name('order')
            ->alias('or')
            ->join('tplay_music tm','or.music_id=tm.music_id','left')
            ->where('or.user_id',$user_id)
            ->order('or.add_time','desc')
            ->field('or.*,tm.name,tm.time,tm.end_time,tm.description,tm.thumb_path,tm.site')
            ->page($page,$pagelimit)
            ->select();
        return json($list);
    }


    /**
     * 修改头像
     * @param $user_id
     * @param $head_img
     * @return \think\response\Json
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function UpdateHeadImg($user_id, $head_img)
    {
        $res=Db::name('user')->where('user_id',$user_id)->update(['head_img'=>$head_img]);
        if ($res>0){
            return $this->asuccess('修改成功');
        }else{
            return $this->aerror('修改失败');
        }
    }


    /**
     * 意见反馈
     * @return \think\response\Json
     */
    public function SetFankui()
    {
        $post['user_id']=$this->request->post('user_id');
        $post['fankui']=$this->request->post('fankui');
        $post['add_time']=time();
        $res=Db::name('fankui')->insertGetId($post);
        if ($res>0){
            return $this->asuccess('反馈成功');
        }else{
            return $this->aerror('反馈失败');
        }
    }

    /**
     * @param string $openid
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function GetUserInfo($openid='')
    {      
      if($openid){
        $list=Db::name('user')
            ->where('openid',$openid)
            ->find();
        if(!$list){
           $list['status']="openid 不存在";
        }
        return json($list);
      }else{
        $list['status']="openid 为空";
         return json($list);
      }
    }
    
}