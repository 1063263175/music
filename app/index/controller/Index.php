<?php
namespace app\index\controller;

use app\index\controller\Base;
use think\Db;

class Index extends Base
{
    public function index()
    {

    }

    /**
     * 获取配置信息
     * @return \think\response\Json
     */
    public function GetConfig()
    {
        $config=[
            'host'=>'https://' . $_SERVER['SERVER_NAME'],
        ];
        return json($config);
    }

    /**
     * 单图广告,幻灯片
     * @param $adv_class 广告分类id
     * @param int $limit 读取个数
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function adv($adv_class, $limit = 1)
    {
        if (empty($adv_class)){
            $this->error('参数错误');
        }
        $list=Db::name('adv')
            ->alias('aa')
            ->join('tplay_attachment att','aa.img=att.id')
            ->where('aa.class',$adv_class)
            ->field('aa.title,aa.class,aa.description,att.filepath')
            ->order('aa.id','desc')
            ->limit($limit)
            ->select();
        foreach ($list as $k=>$v){
            $list[$k]['host']='http://' . $_SERVER['SERVER_NAME'];
        }
        return json($list);
    }


    /**
     * 获取指定页音乐节/夜店列表
     * @param int $class  类型 1音乐节2夜店
     * @param int $page 页数
     * @param int $limit 每页个数
     * @param int $is_top 置顶
     * @param string $sort 排序字段
     * @param string $desc 顺序
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function GetMusicPageList($keyword='',$year='',$user_id=0,$class=1,$page = "1", $limit = "20",$is_top="0", $sort = " mu.music_id ", $desc = "desc")
    {
            if (empty($keyword)){
                $key='';
            }else{
                $key['mu.name']=['like','%' . $keyword . '%'];
            }
            if (!empty($year)){
                $ye['mu.create_time']=['between time',[$year . '-1-1',$year . '-12-31']];
            }else{
                $ye='';
            }
        
            $where=[
                'mu.is_top'=>$is_top,
                'mu.class'=>$class
            ];
            $list = Db::name('music')
                ->alias('mu')
                ->where($where)
                ->where($key)
                ->where($ye)
                ->order($sort,$desc)
                ->page($page,$limit)
                ->select();
            foreach ($list as $k=>$v){
                $zan=GetMusicGood($user_id,$v['music_id'],1);
                $cang=GetMusicGood($user_id,$v['music_id'],2);
                //获取评论数量
                //dump($this->GetCommentCount($v['music_id']));die;
                $list[$k]['comment_count']=$this->GetCommentCount($v['music_id'],'0');
                //获取点赞数量
                $list[$k]['zan_count']=Db::name('good')
                    ->where('music_id',$v['music_id'])
                    ->count('music_id');
                //获取前三条评论
                $list[$k]['comment_list']=$this->GetCommentList($v['music_id'],1,3,'0');
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
            }

            //dump($list);
            return json($list);
       

    }
  //砍价商品列表
 public function GetMusicPageListbar($keyword='',$year='',$user_id=0,$class=1,$page = "1", $limit = "20",$is_top="0", $sort = " mu.music_id ", $desc = "desc")
    {
            if (empty($keyword)){
                $key='';
            }else{
                $key['mu.name']=['like','%' . $keyword . '%'];
            }
            if (!empty($year)){
                $ye['mu.create_time']=['between time',[$year . '-1-1',$year . '-12-31']];
            }else{
                $ye='';
            }
        
            $where=[
                 'mu.is_bar'=>1,
            ];
            $list = Db::name('music')
                ->alias('mu')
                ->where($where)
                ->where($key)
                ->where($ye)
                ->order($sort,$desc)
                ->page($page,$limit)
                ->select();
            foreach ($list as $k=>$v){
                $zan=GetMusicGood($user_id,$v['music_id'],1);
                $cang=GetMusicGood($user_id,$v['music_id'],2);
                //获取评论数量
                //dump($this->GetCommentCount($v['music_id']));die;
                $list[$k]['comment_count']=$this->GetCommentCount($v['music_id'],'0');
                //获取点赞数量
                $list[$k]['zan_count']=Db::name('good')
                    ->where('music_id',$v['music_id'])
                    ->count('music_id');
                //获取前三条评论
                $list[$k]['comment_list']=$this->GetCommentList($v['music_id'],1,3,'0');
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
            }

            //dump($list);
            return json($list);
       

    }
    /**
     * 添加评论
     * @param $user_id
     * @param $music_id
     * @param $content
     * @return bool
     */
    public function SetComment($user_id, $music_id, $content="")
    {
        if (empty($content)){
            $this->error('内容不能为空');
        }else{
            if (Db::name('comment')->insert(['user_id'=>$user_id,'music_id'=>$music_id,'content'=>$content,'add_time'=>time()])>0){
                return json(['res'=>1]);
            }else{
                return json(['res'=>0]);
            }
        }
    }

    /**
     * 加入,取消收藏或点赞 (活动)
     * @param $status 0取消 1加入
     * @param $class 1点赞 2收藏
     * @param $user_id
     * @param $music_id
     * @return bool
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function Good($status, $class, $user_id, $music_id)
    {
        $info=[
            'class'=>$class,
            'user_id'=>$user_id,
            'music_id'=>$music_id
        ];
        if ($status == 0){
            //取消
            if (Db::name('good')->where($info)->delete()){
                return json(['res'=>1]);
            }else{
                return json(['res'=>0]);
            }
        }elseif ($status == 1){
            //加入
            if (Db::name('good')->insertGetId($info)>0){
                return json(['res'=>1]);
            }else{
                return json(['res'=>0]);
            }
        }
    }

    /**
     * 获取音乐节夜店详情
     * @param $music_id
     * @param string $user_id
     * @return \think\response\Json
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function GetMusicInfo($music_id, $user_id='')
    {
        //添加点击量
        Db::name('music')->where('music_id',$music_id)->setInc('click');
        $info=Db::name('music')
            ->where('music_id',$music_id)
            ->find();
        //查询收藏以及点赞情况
        $whereZan=[
            'music_id'=>$music_id,
            'user_id'=>$user_id,
            'class'=>1
        ];
        $whereCang=[
            'music_id'=>$music_id,
            'user_id'=>$user_id,
            'class'=>2
        ];
        if (empty($user_id)){
            $info['zan']=0;
            $info['cang']=0;
        }else{
            if (Db::name('good')->where($whereZan)->value('id')>0){
                $info['zan']=1;
            }else{
                $info['zan']=0;
            }
            if (Db::name('good')->where($whereCang)->value('id')>0){
                $info['cang']=1;
            }else{
                $info['cang']=0;
            }
        }
        //收藏及点赞数量
        $info['zan_num']=Db::name('good')->where(['class'=>1,'music_id'=>$music_id])->count('id');
        $info['cang_num']=Db::name('good')->where(['class'=>2,'music_id'=>$music_id])->count('id');
        return json($info);
    }

    /**
     * 获取活动歌曲列表
     * @param $music_id
     * @param int $page
     * @param int $pagelimit
     * @param string $sort
     * @param string $desc
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function GetMusicSing($music_id, $page=1, $pagelimit=20, $sort='sing_id', $desc='desc')
    {
        $list=Db::name('sing')
            ->where('music_id',$music_id)
            ->page($page,$pagelimit)
            ->order($sort,$desc)
            ->select();
        return json($list);
    }

    /**
     * 获取歌曲详情
     * @param $sing_id
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function GetSingInfo($sing_id)
    {
        $info=Db::name('sing')->where('sing_id',$sing_id)->find();
        return json($info);
    }


    /**
     * 获取评论列表
     * @param $music_id
     * @param string $page
     * @param string $limit
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function GetCommentList($music_id=0,$page='1',$limit='20',$is_json=1)
    {
        $list=Db::name('comment')
            ->alias('co')
            ->join('tplay_user us','us.user_id = co.user_id','left')
            ->where('co.music_id',$music_id)
            ->where('co.status',1)
            ->page($page,$limit)
            ->select();
        if ($is_json == 1){
            return json($list);
        }else{
            return $list;
        }

    }

    /**
     * 获取评论数
     * @param $music_id
     * @return \think\response\Json
     * @throws \think\Exception
     */
    public function GetCommentCount($music_id,$is_json=1)
    {
        $num=Db::name('comment')
            ->where('music_id',$music_id)
            ->where('status',1)
            ->count('id');
        if ($is_json == 1){
            return json(['count'=>$num]);
        }else{
            return ['count'=>$num];
        }
    }
   
    /**
     * 上传附件方法
     * @param string $module
     * @param string $use
     * @param $user_id
     * @return \think\response\Json|void
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function uploadfile($user_id,$module='music',$use='comment_file')
    {
        if (empty($user_id)){
            return $this->error('参数错误');
        }
        if($this->request->file('file')){
            $file = $this->request->file('file');
        }else{
            $res['code']=1;
            $res['msg']='没有上传文件';
            return json($res);
        }
        $module = $this->request->has('module') ? $this->request->param('module') : $module;//模块
        $web_config = Db::name('webconfig')->where('web','web')->find();
        $info = $file->validate(['size'=>$web_config['file_size']*1024,'ext'=>$web_config['file_type']])->rule('date')->move(ROOT_PATH . 'public' . DS . 'uploads' . DS . $module . DS . $use);
        if($info) {
            //写入到附件表
            $data = [];
            $data['module'] = $module;
            $data['filename'] = $info->getFilename();//文件名
            $data['filepath'] = DS . 'uploads' . DS . $module . DS . $use . DS . $info->getSaveName();//文件路径
            $data['fileext'] = $info->getExtension();//文件后缀
            $data['filesize'] = $info->getSize();//文件大小
            $data['create_time'] = time();//时间
            $data['uploadip'] = $this->request->ip();//IP
            //上传人
            $data['user_id'] = $user_id;
            //if($data['module'] = 'admin') {
            //通过后台上传的文件直接审核通过
            $data['status'] = 1;
            //$data['admin_id'] = $data['user_id'];
            $data['audit_time'] = time();
            //}
            $data['use'] = $this->request->has('use') ? $this->request->param('use') : $use;//用处
            $res['id'] = Db::name('attachment')->insertGetId($data);
            $res['src'] = DS . 'uploads' . DS . $module . DS . $use . DS . $info->getSaveName();
            $res['code'] = 2;
            //header('Content-Type:application/json');//这个类型声明非常关键
            //return json_encode($res);
            return $res['src'];
        } else {
            // 上传失败获取错误信息
            return $this->aerror('上传失败：'.$file->getError());
        }
    }

 


}
