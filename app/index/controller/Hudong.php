<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/1/15 0015
 * Time: 14:48
 */

namespace app\index\controller;

use app\index\controller\Base;
use think\Db;
use think\Validate;

class Hudong extends Base
{

    /**
     * 发布我的投稿
     * @return \think\response\Json
     */
    public function SetQuan()
    {
        $post=$this->request->post('');
        if (empty($post['user_id'])){
            return $this->aerror('参数错误');
        }elseif (empty($post['content'])){
            return $this->aerror('内容为空');
        }
        $res=Db::name('quan')->insertGetId($post);
        if ($res>0){
            return $this->asuccess('发布成功');
        }else{
            return $this->aerror('发布失败');
        }
    }

    /**
     * 获取朋友圈
     * @param $user_id
     * @param int $page
     * @param int $pageLimit
     * @return \think\response\Json
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function GetQuanList($user_id,$page=1, $pageLimit=20)
    {
        $list=Db::name('quan')
            ->page($page,$pageLimit)
            ->order('quan_id','desc')
            ->select();
        foreach ($list as $k=>$v){
            $cang=Db::name('quan_good')->where('class',2)->where('user_id',$user_id)->find();
            if (empty($cang)){
                $list[$k]['is_cang']=0;
            }else{
                $list[$k]['is_cang']=1;
            }
            $zan=Db::name('quan_good')->where('class',1)->where('user_id',$user_id)->find();
            if (empty($zan)){
                $list[$k]['is_zan']=0;
            }else{
                $list[$k]['is_zan']=1;
            }
            $list[$k]['zan_count']=Db::name('quan_good')
                ->where('class',1)
                ->where('quan_id',$v['quan_id'])
                ->count('quan_id');
            $list[$k]['cang_count']=Db::name('quan_good')
                ->where('class',2)
                ->where('quan_id',$v['quan_id'])
                ->count('quan_id');
        }
        return json($list);
    }

    /**
     * 加入取消点赞或收藏朋友圈
     * @param $user_id
     * @param $quan_id
     * @param $class 1点赞2收藏
     * @param $status 0取消1加入
     * @return \think\response\Json
     */
    public function SetQuanGood($status=1, $user_id, $quan_id, $class)
    {
        $vali=new Validate([
            ['user_id','require','参数错误'],
            ['quan_id','require','参数错误'],
            ['class','require','参数错误'],
        ]);
        $info=[
            'user_id'=>$user_id,
            'quan_id'=>$quan_id,
            'class'=>$class,
            'time'=>time(),
        ];
        if (!$vali->check($info)) {
            $this->aerror('提交失败：' . $vali->getError());
        }
        if ($status == 0){
            $res=Db::name('quan_good')->delete($info);
        }elseif ($status == 1){
            $res=Db::name('quan_good')->insertGetId($info);
        }

        if ($res>0){
            return $this->asuccess('提交成功');
        }else{
            return $this->aerror('提交失败');
        }
    }
}