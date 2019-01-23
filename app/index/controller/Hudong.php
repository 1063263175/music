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
     * 发布朋友圈
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
        $post['add_time']=time();
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
            ->alias('qu')
            ->join('tplay_user tu','qu.user_id=tu.user_id','left')
            ->page($page,$pageLimit)
            ->order('qu.quan_id','desc')
            ->select();
        foreach ($list as $k=>$v){
            $cang=Db::name('quan_good')->where('class',2)->where('user_id',$user_id)->find();
            if (empty($cang)){
                $list[$k]['is_cang']=0;
            }else{
                $list[$k]['is_cang']=1;
            }
            $zan=Db::name('quan_good')->where('class',1)->where('user_id',$user_id)->find();
            $guan=Db::name('guan')->where('buser_id',$v['user_id'])->where('user_id',$user_id)->find();
            if (empty($guan)){
                $list[$k]['is_guan']=0;
            }else{
                $list[$k]['is_guan']=1;
            }
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
            $list[$k]['comment_list']=Db::name('quan_comment')
                ->where('quan_id',$v['quan_id'])
                ->order('id','desc')
                ->limit(4)
                ->select();
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
        ];
        if (!$vali->check($info)) {
            $this->aerror('提交失败：' . $vali->getError());
        }
        $find=Db::name('quan_good')->where($info)->find();
        if ($status == 0){
            $res=Db::name('quan_good')->where($info)->delete();
        }elseif ($status == 1){
            if (!empty($find)){
                return $this->aerror('已存在');
            }
            $info['time']=time();
            $res=Db::name('quan_good')->insertGetId($info);
        }
        if ($res>0){
            return $this->asuccess('提交成功');
        }else{
            return $this->aerror('提交失败');
        }
    }

    /**
     * 朋友圈详情
     * @param $quan_id
     * @return \think\response\Json
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function GetQuanInfo($quan_id)
    {
        //点击量
        Db::name('quan')->where('quan_id',$quan_id)->setInc('click');
        $info=Db::name('quan')
            ->alias('qu')
            ->join('tplay_user','tplay_user.user_id=qu.user_id','left')
            ->where('qu.quan_id',$quan_id)
            ->find();
        //朋友圈的点赞数量
        $info['zan_count']=Db::name('quan_good')
            ->where('quan_id',$info['quan_id'])
            ->where('user_id',$info['user_id'])
            ->where('class',1)
            ->count('id');
        $info['cang_count']=Db::name('quan_good')
            ->where('quan_id',$info['quan_id'])
            ->where('user_id',$info['user_id'])
            ->where('class',2)
            ->count('id');
        $info['comment_count']=Db::name('quan_comment')
            ->where('quan_id',$info['quan_id'])
            ->count('id');

        return json($info);
    }

    /**
     * 添加朋友圈评论
     * @param $user_id
     * @param $quan_id
     * @param $content
     * @return \think\response\Json
     */

    public function SetQuanComment($user_id, $quan_id, $content)
    {
        $info=[
            'user_id'=>$user_id,
            'quan_id'=>$quan_id,
            'content'=>$content,
            'add_time'=>time(),
        ];
        $res=Db::name('quan_comment')->insertGetId($info);
        if ($res>0){
            return $this->asuccess('评论成功');
        }else{
            return $this->aerror('评论失败');
        }
    }

    /**
     * 获取朋友圈评论列表
     * @param $quan_id
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */

    public function GetQuanComment($quan_id)
    {
        $list=Db::name('quan_comment')
            ->alias('qc')
            ->join('tplay_user tu','tu.user_id=qc.user_id','left')
            ->where('quan_id',$quan_id)
            ->where('status',1)
            ->field('qc.*,tu.head_img,tu.nickname')
            ->select();
        return json($list);
    }



    /**
     * 关注取关
     * @param int $status 0取消关注1关注
     * @param int $user_id  当前用户id
     * @param int $buser_id  被关注用户id
     * @return \think\response\Json
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function SetGuan($status=1, $user_id, $buser_id)
    {
        $info=[
            'user_id'=>$user_id,
            'buser_id'=>$buser_id,
        ];
        $find=Db::name('guan')->where($info)->find();
        if ($status == 1){
            //关注
            if (!empty($find)){
                return $this->aerror('已经关注过了');
            }else{
                $info['add_time']=time();
                $res=Db::name('guan')->insertGetId($info);
                if ($res>0){
                    return $this->asuccess('关注成功');
                }else{
                    return $this->aerror('关注失败');
                }
            }
        }elseif ($status == 0){
            //取消关注
            if (empty($find)){
                return $this->aerror('没有关注');
            }else{
                $res=Db::name('guan')->where($info)->delete();
                if ($res !== false){
                    return $this->asuccess('取消成功');
                }else{
                    return $this->aerror('取消失败');
                }
            }
        }else{
            return $this->aerror('参数错误');
        }
    }

    /**
     * 我关注的朋友圈
     * @param $user_id
     * @param int $page
     * @param int $pagelimit
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function GetMyFriend($user_id, $page = 1, $pagelimit = 20)
    {
        $user_ids=Db::name('guan')->where('user_id',$user_id)->field('buser_id')->select();
        foreach ($user_ids as $k=>$v){
            $ids[$k]=$v['buser_id'];
        }
        //$ss=implode(',',$ids);
        //dump($ss);die;
        $where['qu.user_id']=['in',$ids];
        $list=Db::name('quan')
            ->alias('qu')
            ->join('tplay_user tu','qu.user_id=tu.user_id','left')
            ->where($where)
            ->page($page,$pagelimit)
            ->order('quan_id','desc')
            ->select();
        return json($list);
    }

}