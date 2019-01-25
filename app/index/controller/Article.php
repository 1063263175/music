<?php /** @noinspection PhpParamsInspection */

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
    public function GetArticlePageList($keyword='',$year='',$user_id, $cate_id, $page = 1, $pagelimit = 20, $sort = 'id', $desc = 'desc')
    {
        if (empty($keyword)){
            $key='';
        }else{
            $key['title']=['like','%' . $keyword . '%'];
        }
        if (!empty($year)){
            $ye['create_time']=['between time',[$year . '-1-1',$year . '-12-31']];
        }else{
            $ye='';
        }
        $list=Db::name('article')
            ->where('status',1)
            ->where($key)
            ->where($ye)
            ->where('article_cate_id',$cate_id)
            ->page($page,$pagelimit)
            ->order($sort,$desc)
            ->select();
        $where=[
            'user_id'=>$user_id,
        ];
        foreach ($list as $k=>$v){
            $where['article_id']=$v['id'];
            $where['class']=1;
            $zan=Db::name('article_good')->where($where)->find();
            $where['class']=2;
            $cang=Db::name('article_good')->where($where)->find();
            if (empty($zan)){
                $list[$k]['is_zan']=0;
            }else{
                $list[$k]['is_zan']=1;
            }
            if (empty($cang)){
                $list[$k]['is_cang']=0;
            }else{
                $list[$k]['is_cang']=1;
            }

            $list[$k]['comment_count']=Db::name('article_comment')
                ->where('article_id',$v['id'])
                ->count('id');
            $list[$k]['zan_count']=Db::name('article_good')
                ->where(['article_id'=>$v['id'],'class'=>1])
                ->count('id');


        }
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
        //添加点击量
        Db::name('article')->where('id',$id)->setInc('click');
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
            $this->aerror('提交失败：' . $validate->geterror());
        }
        $post['add_time']=time();
        if (Db::name('article_comment')->insert($post)>0){
            return $this->asuccess('评论成功');
        }else{
            return $this->aerror('评论失败');
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
            ->alias('ac')
            ->join('tplay_user tu','tu.user_id = ac.user_id','left')
            ->where('ac.article_id',$article_id)
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
    public function SetArticleGood($status, $music_id, $user_id, $class)
    {
        //给前端换个变量名
        $article_id=$music_id;
        $info=[
            'article_id'=>$article_id,
            'user_id'=>$user_id,
            'class'=>$class
        ];
        if ($status == 0){
            //取消
            if (Db::name('article_good')->where($info)->delete()!==false){
                return $this->asuccess('取消成功');
            }else{
                return $this->aerror('取消失败');
            }
        }else{
            //加入
            if (Db::name('article_good')->insert($info)>0){
                return $this->asuccess('添加成功');
            }else{
                return $this->aerror('添加失败');
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
            ->join('tplay_article ta','ta.id=ag.article_id','left')
            ->where($where)
            ->page($page,$pagelimit)
            ->order($sort,$desc)
            ->field('ta.*,ag.class')
            ->select();
        return json($list);
    }

    /**
     * 获取咨询评论详情
     * @param $comment_id
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function GetArticleCommentInfo($comment_id)
    {
        $info=Db::name('article_good')->where('id',$comment_id)->find();
        return json($info);
    }

    /**
     * 发布投稿   投稿页是向资讯-热门推文那里的投的，后台审核
     * @return \think\response\Json
     */
    public function SetArticleInfo()
    {

        $model=new \app\admin\model\Article();
        //是新增操作
        if($this->request->isPost()) {
            //是提交操作
            $post = $this->request->post();
            //验证  唯一规则： 表名，字段名，排除主键值，主键名
            $validate = new \think\Validate([
                ['title', 'require', '标题不能为空'],
                ['user_id','require','用户id不能为空'],
                ['music_id','require','音乐节id不能为空'],
                //['article_cate_id', 'require', '请选择分类'],
                ['content', 'require', '文章内容不能为空'],
            ]);
            //验证部分数据合法性
            if (!$validate->check($post)) {
                $this->error('提交失败：' . $validate->getError());
            }
            $post['article_cate_id']=4;
            $post['create_time']=time();
            $post['update_time']=time();
            if(false == $model->allowField(true)->save($post)) {
                return $this->aerror('添加失败');
            } else {
                return $this->asuccess('添加成功,等待审核');
            }
        }else{
            return $this->aerror('用post提交');
        }
    }

    /**
     * 获取music_id
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function GetMusicId()
    {
        $list=Db::name('music')
            ->order('music_id','desc')
            ->field('name,music_id')
            ->select();
        return json($list);
    }

    


}