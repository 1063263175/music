<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/1/10 0010
 * Time: 16:40
 */

namespace app\admin\controller;

use app\admin\controller\Permissions;
use think\Db;

class Shop extends Permissions
{
    public function choujiang()
    {
        $list=Db::name('jiang')->paginate(20);
        $this->assign('list',$list);
        return $this->fetch();
    }

    public function cjpublish()
    {
        //获取菜单id
        $id = $this->request->has('jiang_id') ? $this->request->param('jiang_id', 0, 'intval') : 0;
        //是正常添加操作
        if($id > 0) {
            //是修改操作
            if($this->request->isPost()) {
                //是提交操作
                $post = $this->request->post();
                //验证  唯一规则： 表名，字段名，排除主键值，主键名
                $validate = new \think\Validate([
                    ['title', 'require', '名称不能为空'],
                    ['jiang_number', 'require', '抽奖个数不能为空'],
                    ['kai_time', 'require', '截止时间不能为空'],
                ]);
                //验证部分数据合法性
                if (!$validate->check($post)) {
                    $this->error('提交失败：' . $validate->getError());
                }
                $post['kai_time']=strtotime($post['kai_time']);
                //验证菜单是否存在
/*                $cate = Db->where('id',$id)->find();
                if(empty($cate)) {
                    return $this->error('id不正确');
                }*/
                if(false == Db::name('jiang')->where(['jiang_id'=>$id])->update($post)) {
                    return $this->error('修改失败');
                } else {
                    return $this->success('修改成功','admin/shop/choujiang');
                }
            } else {
                //非提交操作
                $cate=Db::name('jiang')->where('jiang_id',$id)->find();
                $music=Db::name('music')->field('music_id,name')->select();
                $this->assign('cate',$cate);
                if(!empty($cate)) {
                    $this->assign('music',$music);
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
                    ['title', 'require', '名称不能为空'],
                    ['jiang_number', 'require', '抽奖个数不能为空'],
                    ['kai_time', 'require', '截止时间不能为空'],
                ]);
                //验证部分数据合法性
                if (!$validate->check($post)) {
                    $this->error('提交失败：' . $validate->getError());
                }
                $post['add_time']=time();
                $post['kai_time']=strtotime($post['kai_time']);
                if(false == Db::name('jiang')->insert($post)) {
                    return $this->error('添加失败');
                } else {
                    return $this->success('添加成功','admin/shop/choujiang');
                }
            } else {
                //非提交操作
                $music=Db::name('music')->field('music_id,name')->select();
                $this->assign('music',$music);
                return $this->fetch();
            }
        }
    }


    /*
     * 开奖
     * */
    public function kai()
    {
        $jiang_id=input('jiang_id');
        $jiang=Db::name('jiang')->where('jiang_id',$jiang_id)->find();
        if ($jiang['status']==0){
            $code_list=Db::name('jiang_code')->where('jiang_id',$jiang_id)->select();
            $code_count=count($code_list);

            //dump($code_list);
            //dump($code_count);die;
            if ($code_count>$jiang['jiang_number']){
                $res_code=array_rand($code_list,$jiang['jiang_number']);
            }else{
                $res_code=$code_count;
            }
            if (is_array($res_code)){
                foreach ($res_code as $k=>$v){
                    //echo '12';
                    $data[$k]=[
                        'jiang_code'=>$code_list[$v]['jiang_code'],
                        'jiang_id'=>$jiang_id
                    ];
                }
                //dump($data);
                Db::name('jiang_res')->insertAll($data);

            }elseif (count($res_code)==1&$res_code>0){
                Db::name('jiang_res')
                    ->insert([
                        'jiang_code'=>$code_list[$res_code]['jiang_code'],
                        'jiang_id'=>$jiang_id
                    ]);
            }
            //die;
            Db::name('jiang')->where('jiang_id',$jiang_id)->update(['status'=>1]);
            //die;
            return $this->success('开奖成功','admin/shop/choujiang');
        }else{
            return $this->error('参数错误');
        }
    }

    public function order()
    {
        $list=Db::name('order')->order('order_id','desc')->paginate('20');
        $this->assign('list',$list);
        return $this->fetch();
    }
}