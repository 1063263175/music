<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/1/16 0016
 * Time: 14:19
 */

namespace app\index\controller;

use app\index\controller\Base;
use think\Db;

class Tao extends Base
{
    /**
     * 发布淘票
     * @param $user_id
     * @param $music_id
     * @param $title
     * @param $content
     * @param string $vedio
     * @param string $img
     * @return \think\response\Json
     */
    public function SetTaoInfo($user_id, $title, $content, $vedio = '', $img = '')
    {
        $info=[
            'user_id'=>$user_id,
            //'music_id'=>$music_id,
            'title'=>$title,
            'content'=>$content,
            'vedio'=>$vedio,
            'img'=>$img,
            'add_time'=>time(),
        ];
        $res=Db::name('tao')->insertGetId($info);
        if ($res>0){
            return $this->asuccess('插入成功');
        }else{
            return $this->aerror('插入失败');
        }
    }

    /**
     * 淘票列表
     * @param string $user_id
     * @param string $music_id
     * @param int $page
     * @param int $pagelimit
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function GetTaoList($user_id = '', $page = 1, $pagelimit = 20)
    {
        if (empty($user_id)){
           $where='';
        }else{
            $where=[
                'tt.user_id'=>$user_id,
            ];
        }
        $limit=Db::name('tao')
            ->alias('tt')
            ->join('tplay_user tu','tu.user_id=tt.user_id','left')
            //->join('tplay_music tm','tm.music_id=tt.music_id','left')
            ->where($where)
            ->order('tao_id','desc')
            ->field('tt.*,tu.nickname,tu.head_img')
            ->page($page,$pagelimit)
            ->select();
        return json($limit);
    }

    /**
     * 淘票详情
     * @param string $user_id
     * @param string $music_id
     * @param int $page
     * @param int $pagelimit
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function GetTaoInfo($tao_id)
    {
        if (empty($tao_id)){
            $where='';
        }else{
            $where=[
                //'tt.user_id'=>$user_id,
                'tt.tao_id'=>$tao_id,
            ];
        }
        $limit=Db::name('tao')
            ->alias('tt')
            ->join('tplay_user tu','tu.user_id=tt.user_id','left')
            //->join('tplay_music tm','tm.music_id=tt.music_id','left')
            ->where($where)
            ->field('tt.*,tu.nickname,tu.head_img')
            ->find();
        return json($limit);
    }


    /**
     * 添加淘票评论
     * @param $user_id
     * @param $tao_id
     * @param $content
     * @return \think\response\Json
     */
    public function SetTaoComment($user_id, $tao_id, $content)
    {
        $info=[
            'user_id'=>$user_id,
            'tao_id'=>$tao_id,
            'content'=>$content,
            'add_time'=>time(),
        ];
        $res=Db::name('tao_comment')
            ->insertGetId($info);
        if ($res>0){
            return $this->asuccess('评论成功');
        }else{
            return $this->aerror('评论失败');
        }
    }

    /**
     * 淘票评论列表
     * @param $tao_id
     * @param int $page
     * @param int $pagelimit
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function GetTaoCommentList($tao_id, $page = 1, $pagelimit = 20)
    {
        $list=Db::name('tao_comment')
            ->alias('tc')
            ->join('tplay_user as tu','tu.user_id=tc.user_id','left')
            ->where('tc.tao_id',$tao_id)
            ->page($page,$pagelimit)
            ->select();
        return json($list);
    }

}