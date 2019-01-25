<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/1/17 0017
 * Time: 16:10
 */

namespace app\admin\controller;


use think\Db;

class Quan extends Permissions
{

    public function comment()
    {
        $list=Db::name('quan_comment')->paginate(20);
        $this->assign('list',$list);
        return $this->fetch();
    }

    public function mark()
    {
        //获取id
        $id = $this->request->has('id') ? $this->request->param('id', 0, 'intval') : 0;
        $model = new \app\admin\model\Quan();
        //是正常添加操作
        if($id > 0) {
            //是修改操作
            if($this->request->isPost()) {
                //是提交操作
                $post = $this->request->post();

                //dump($post);
                //dump($id);die;
                if(false == Db::name('quan_comment')->where(['id'=>$id])->update($post)) {
                    return $this->error('提交失败');
                } else {
                    //addlog($model->id);//写入日志
                    return $this->success('提交成功','admin/quan/comment');
                }
            }
        }
    }


    public function del()
    {
        if($this->request->isAjax()) {
            $id = $this->request->has('id') ? $this->request->param('id', 0, 'intval') : 0;
            if(false == Db::name('quan_comment')->where('id',$id)->delete()) {
                return $this->error('删除失败');
            } else {
                //addlog($id);//写入日志
                return $this->success('删除成功','admin/quan/comment');
            }
        }
    }

}