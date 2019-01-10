<?php
namespace app\index\controller;

use app\index\controller\Base;
use think\Db;
use think\Validate;

class Article extends Base
{
    /**
     * 获取咨询分类列表
     * @param int $limit
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function GetArticleCateList($limit = 1)
    {
        $cate=Db::name('article_cate')->limit($limit)->select();
        return json($cate);
    }

    /**
     * 获取指定咨询分类信息
     * @param $id
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function GetArticleCateInfo($id)
    {
        $info=Db::name('article_cate')->where('id',$id)->find();
        return json($info);
    }

    /**
     * 获取咨询列表
     * @param $cate_id
     * @param int $page
     * @param int $pagelimit
     * @param string $sort
     * @param string $desc
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function GetArticlePageList($cate_id, $page = 1, $pagelimit = 20, $sort = 'id', $desc = 'desc')
    {
        $list=Db::name('article')
            ->where('article_cate_id',$cate_id)
            ->page($page,$pagelimit)
            ->order($sort,$desc)
            ->select();
        return json($list);
    }

    /**
     * 获取文章详情
     * @param $id
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function GetArticleInfo($id)
    {
        $info=Db::name('article')->where('id',$id)->find();
        return json($info);
    }

    /**
     * 插入评价记录
     */
    public function SetArticleComment()
    {
        $post=$this->request->post();
        $validate=new Validate([
            ['user_id', 'require', 'user_id为空'],
            ['article_id', 'require', 'article_id不能为空'],
            ['content', 'require', '内容不能为空'],
        ]);
        //验证部分数据合法性
        if (!$validate->check($post)) {
            $this->error('提交失败：' . $validate->getError());
        }
        $post['add_time']=time();
        if (Db::name('article_comment')->insert($post)>0){
            return $this->success('评论成功');
        }else{
            return $this->error('评论失败');
        }
    }

    /**
     * 获取指定文章评论列表
     * @param $article_id
     * @param int $page
     * @param int $pagelimit
     * @param string $sort
     * @param string $desc
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function GetArticleCommentPageList($article_id, $page = 1, $pagelimit = 20, $sort = "id", $desc = "desc")
    {
        $list=Db::name('article_comment')
            ->where('article_id',$article_id)
            ->order($sort,$desc)
            ->page($page,$pagelimit)
            ->select();
        return json($list);
    }

    /**
     * 加入,取消收藏或点赞 咨询
     * @param $status 0取消1加入
     * @param $article_id
     * @param $user_id
     * @param $class 1点赞2收藏
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function SetArticleGood($status, $article_id, $user_id, $class)
    {
        $info=[
            'article_id'=>$article_id,
            'user_id'=>$user_id,
            'class'=>$class
        ];
        if ($status == 0){
            //取消
            if (Db::name('article_good')->where($info)->delete()!==false){
                return $this->success('取消成功');
            }else{
                return $this->error('取消失败');
            }
        }else{
            //加入
            if (Db::name('article_good')->insert($info)>0){
                return $this->success('添加成功');
            }else{
                return $this->error('添加失败');
            }
        }
    }

    /**
     * 获取用户点赞或收藏的咨询的列表
     * @param int $class
     * @param $article_id
     * @param $user_id
     * @param int $page
     * @param int $pagelimit
     * @param string $sort
     * @param string $desc
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function GetGoodPageList($class=1, $user_id, $page=1, $pagelimit=20, $sort='id', $desc='desc')
    {
        $where=[
            'class'=>$class,
            //'article_id'=>$article_id,
            'user_id'=>$user_id,
        ];
        $list=Db::name('article_good')
            ->alias('ag')
            ->join('tplay_article as ta','ta.id=ag.article_id','left')
            ->where($where)
            ->page($page,$pagelimit)
            ->order($sort,$desc)
            ->field('ta.*,ag.class')
            ->select();
        return json($list);
    }

    public function GetArticleCommentInfo($comment_id)
    {
        $info=Db::name('article_good')->where('id',$comment_id)->find();
        return json($info);
    }

}