<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/1/10 0010
 * Time: 14:35
 */

namespace app\admin\controller;

use app\admin\controller\Permissions;
use think\Db;

class User extends Permissions
{
    public function index()
    {
        $where='';
        $post = $this->request->param();
        if (isset($post['nickname']) and !empty($post['nickname'])) {
            $where['nickname'] = ['like', '%' . $post['nickname'] . '%'];
        }
        $list=Db::name('user')->where($where)->paginate('20');
        $this->assign('list',$list);
        return $this->fetch();
    }

    public function publish()
    {
        //获取菜单id
        $id = $this->request->has('user_id') ? $this->request->param('user_id', 0, 'intval') : 0;
        //是正常添加操作
        if($id > 0) {
            //是修改操作
            if($this->request->isPost()) {
                //是提交操作
                $post = $this->request->post();

                //验证菜单是否存在
                $article = Db::name('user')->where('user_id',$id)->find();
                if(empty($article)) {
                    return $this->error('id不正确');
                }

                if(false == Db::name('user')->update($post,['id'=>$id])) {
                    return $this->error('修改失败');
                } else {
                    return $this->success('修改成功','admin/user/index');
                }
            } else {
                //非提交操作
                $article = Db::name('user')->where('user_id',$id)->find();

                if(!empty($article)) {
                    $this->assign('article',$article);
                    return $this->fetch();
                } else {
                    return $this->error('id不正确');
                }
            }
        } else {

        }

    }
}