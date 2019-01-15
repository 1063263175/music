<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/1/4
 * Time: 11:32
 */

namespace app\admin\controller;

use app\admin\controller\Permissions;
use app\model\Music as MusicModel;
use app\model\Sing;
use think\Session;
use think\Db;
class Music extends Permissions
{
    public function index()
    {
        $model=new MusicModel;
        $list=$model->GetMusicPage('20');
        $this->assign('list',$list);
        return $this->fetch();
    }

    public function publish()
    {
        //获取id
        $id = $this->request->has('music_id') ? $this->request->param('music_id', 0, 'intval') : 0;
        $model = new MusicModel;
        //是正常添加操作
        if($id > 0) {
            //是修改操作
            if($this->request->isPost()) {
                //是提交操作
                $post = $this->request->post();
                //验证  唯一规则： 表名，字段名，排除主键值，主键名
                $validate = new \think\Validate([
                    ['name', 'require', '标题不能为空'],
                    ['thumb', 'require', '请上传缩略图'],
                ]);
                //验证部分数据合法性
                if (!$validate->check($post)) {
                    $this->error('提交失败：' . $validate->getError());
                }

                //设置修改人
                //$post['edit_admin_id'] = Session::get('admin');
                /*$post['money']=$post['money']*100;
                $post['vip_money']=$post['vip_money']*100;*/
                $post['time']=strtotime($post['time']);
                $post['end_time']=strtotime($post['end_time']);
                if(false == $model->allowField(true)->save($post,['music_id'=>$id])) {
                    return $this->error('修改失败');
                } else {
                    return $this->success('修改成功','admin/music/index');
                }
            } else {
                //非提交操作
                $article = $model->where('music_id',$id)->find();
                if(!empty($article)) {
                    $this->assign('article',$article);
                    return $this->fetch();
                } else {
                    return $this->error('id不正确');
                }
            }
        } else {
            //是新增操作
            if($this->request->isPost()) {
                //是提交操作
                $post = $this->request->post();
                //验证  唯一规则： 表名，字段名，排除主键值，主键名
                $validate = new \think\Validate([
                    ['name', 'require', '标题不能为空'],
                    ['thumb', 'require', '请上传缩略图'],
                ]);
                //验证部分数据合法性
                if (!$validate->check($post)) {
                    $this->error('提交失败：' . $validate->getError());
                }
                //设置创建人
                $post['admin_id'] = Session::get('admin');
                $post['time']=strtotime($post['time']);
               /* $post['money']=$post['money'];
                $post['vip_money']=$post['vip_money']*100;*/
                //设置修改人
                //$post['edit_admin_id'] = $post['admin_id'];
                if(false == $model->allowField(true)->save($post)) {
                    return $this->error('添加失败');
                } else {
                    return $this->success('添加成功','admin/music/index');
                }
            } else {
                //非提交操作

                return $this->fetch();
            }
        }

    }

    public function is_top()
    {
        if($this->request->isPost()){
            $post = $this->request->post();
            if(false == Db::name('music')->where('music_id',$post['id'])->update(['is_top'=>$post['is_top']])) {
                return $this->error('设置失败');
            } else {
                return $this->success('设置成功','admin/music/index');
            }
        }
    }

    public function delete()
    {
        if($this->request->isAjax()) {
            $id = $this->request->has('id') ? $this->request->param('id', 0, 'intval') : 0;
            if(false == Db::name('music')->where('music_id',$id)->delete()) {
                return $this->error('删除失败');
            } else {
                return $this->success('删除成功','admin/music/index');
            }
        }
    }

    public function sing()
    {
        $model=new Sing();
        $list=$model->GetSingList();
        $this->assign('list',$list);
        return $this->fetch();
    }

    public function singpublish()
    {
        //获取id
        $id = $this->request->has('sing_id') ? $this->request->param('sing_id', 0, 'intval') : 0;
        $model = new Sing;
        //是正常添加操作
        if($id > 0) {
            //是修改操作
            if($this->request->isPost()) {
                //是提交操作
                $post = $this->request->post();
                //验证  唯一规则： 表名，字段名，排除主键值，主键名
                $validate = new \think\Validate([
                    ['title', 'require', '标题不能为空'],
                    ['thumb', 'require', '请上传缩略图'],
                ]);
                //验证部分数据合法性
                if (!$validate->check($post)) {
                    $this->error('提交失败：' . $validate->getError());
                }

                //设置修改人
                //$post['edit_admin_id'] = Session::get('admin');
                //$post['time']=strtotime($post['time']);
                if(false == $model->allowField(true)->save($post,['sing_id'=>$id])) {
                    return $this->error('修改失败');
                } else {
                    return $this->success('修改成功','admin/music/sing');
                }
            } else {
                //非提交操作
                $music=Db::name('music')->select();
                $this->assign('music',$music);
                $article = $model->where('sing_id',$id)->find();
                if(!empty($article)) {
                    $this->assign('article',$article);
                    return $this->fetch();
                } else {
                    return $this->error('id不正确');
                }
            }
        } else {
            //是新增操作
            if($this->request->isPost()) {
                //是提交操作
                $post = $this->request->post();
                //验证  唯一规则： 表名，字段名，排除主键值，主键名
                $validate = new \think\Validate([
                    ['title', 'require', '标题不能为空'],
                    ['thumb', 'require', '请上传缩略图'],
                ]);
                //验证部分数据合法性
                if (!$validate->check($post)) {
                    $this->error('提交失败：' . $validate->getError());
                }
                //设置创建人
                
                //设置修改人
                //$post['edit_admin_id'] = $post['admin_id'];
                if(false == $model->allowField(true)->save($post)) {
                    return $this->error('添加失败');
                } else {
                    return $this->success('添加成功','admin/music/sing');
                }
            } else {
                //非提交操作
                $music=Db::name('music')->select();
                $this->assign('music',$music);
                return $this->fetch();
            }
        }

    }

    public function singdel()
    {
        if($this->request->isAjax()) {
            $id = $this->request->has('id') ? $this->request->param('id', 0, 'intval') : 0;
            if(false == Db::name('sing')->where('sing_id',$id)->delete()) {
                return $this->error('删除失败');
            } else {
                return $this->success('删除成功','admin/music/sing');
            }
        }
    }
    
    
}