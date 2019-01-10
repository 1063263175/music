<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/18
 * Time: 16:23
 */

namespace app\admin\controller;

use app\admin\controller\Permissions;
use think\Db;

class Adv extends Permissions
{
    public function adv_class()
    {
        $list=Db::name('adv_class')->select();
        $this->assign('list',$list);
        return $this->fetch();
    }


    public function class_publish()
    {
        //获取管理员id
        $id = $this->request->has('id') ? $this->request->param('id', 0, 'intval') : 0;
        if($id > 0) {
            //是修改操作
            if($this->request->isPost()) {
                //是提交操作
                $post = $this->request->post();
                //验证  唯一规则： 表名，字段名，排除主键值，主键名
                $validate = new \think\Validate([
                    ['name', 'require', '不能为空'],
                ]);
                //验证部分数据合法性
                if (!$validate->check($post)) {
                    $this->error('提交失败：' . $validate->getError());
                }
                $post['id']=$id;
                if(false == Db::name('adv_class')->update($post)) {
                    return $this->error('修改失败');
                } else {
                    return $this->success('修改成功','admin/adv/adv_class');
                }
            } else {
                //非提交操作
                $cate = Db::name('adv_class')->where('id',$id)->find();
                $this->assign('cate',$cate);
                return $this->fetch();
            }
        } else {
            //是新增操作
            if($this->request->isPost()) {
                //是提交操作
                $post = $this->request->post();
                //验证  唯一规则： 表名，字段名，排除主键值，主键名
                $validate = new \think\Validate([
                    ['name', 'require', '用户名'],

                ]);
                //验证部分数据合法性
                if (!$validate->check($post)) {
                    $this->error('提交失败：' . $validate->getError());
                }
                if(false == Db::name('adv_class')->insert($post)) {
                    return $this->error('添加失败');
                } else {
                    return $this->success('添加成功','admin/adv/adv_class');
                }
            } else {
                //非提交操作
                return $this->fetch();
            }
        }
    }

    public function adv()
    {
        $list=Db::name('adv')->paginate('20');
        $this->assign('list',$list);
        return $this->fetch();
    }

    public function publish()
    {
        //获取菜单id
        $id = $this->request->has('id') ? $this->request->param('id', 0, 'intval') : 0;
        //是正常添加操作
        if($id > 0) {
            //是修改操作
            if($this->request->isPost()) {
                //是提交操作
                $post = $this->request->post();
                //验证  唯一规则： 表名，字段名，排除主键值，主键名
                $validate = new \think\Validate([
                    ['title', 'require', '标题不能为空'],
                    ['class', 'require', '请选择分类'],
                    ['img', 'require', '请上传图片'],
                ]);
                //验证部分数据合法性
                if (!$validate->check($post)) {
                    $this->error('提交失败：' . $validate->getError());
                }
                $post['id']=$id;
                if(false == Db::name('adv')->update($post)) {
                    return $this->error('修改失败');
                } else {
                    return $this->success('修改成功','admin/adv/adv');
                }
            } else {
                //非提交操作
                $article = Db::name('adv')->where('id',$id)->find();
                $cates = Db::name('adv_class')->select();
                $this->assign('cates',$cates);
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
                    ['class', 'require', '请选择分类'],
                    ['img', 'require', '请上传图片'],
                ]);
                //验证部分数据合法性
                if (!$validate->check($post)) {
                    $this->error('提交失败：' . $validate->getError());
                }
                if(false == Db::name('adv')->insert($post)) {
                    return $this->error('添加失败');
                } else {

                    return $this->success('添加成功','admin/adv/adv');
                }
            } else {
                //非提交操作
                $cates = Db::name('adv_class')->select();
                $this->assign('cates',$cates);
                return $this->fetch();
            }
        }
    }

    public function delete()
    {
        if($this->request->isAjax()) {
            $id = $this->request->has('id') ? $this->request->param('id', 0, 'intval') : 0;
            if(false == Db::name('adv')->where('id',$id)->delete()) {
                return $this->error('删除失败');
            } else {
                return $this->success('删除成功','admin/adv/adv');
            }
        }
    }
}