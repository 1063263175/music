<?php
namespace app\apppic\controller;

use think\Db;
use think\Request;
use think\File;
use think\Validate;
use think\Session;

class Source extends Index
{   
    //素材列表
    function sourceList(){
        $page=$this->request->has('page')?$this->request->param('page', 0, 'intval' ):0;
        $list=Db::name('source')->order('sort desc')->limit("10")->page($page)->select();
        $list_page=Db::name('source')->max('id');
        $max_page=floatval($list_page/10);
        $source_cate_id=$this->request->param('source_cate_id');
        if($source_cate_id){
            $source=Db::name('source')->where('column_id',$source_cate_id)->order('sort desc')->limit("10")->page($page)->select();
            if($source){
                $this->returnMessage['code']    ='success';
                $this->returnMessage['message'] ='查询成功';
                $this->returnMessage['data']=$source;
            }else{
                $this->returnMessage['code']    ='success';
                $this->returnMessage['message'] ='查询成功';
                $this->returnMessage['data']    =$list;
                $this->returnMessage['max_page']=$max_page;
            }
        }else{
            $this->returnMessage['code']    ='success';
            $this->returnMessage['message'] ='查询成功';
            $this->returnMessage['data']    =$list;
            $this->returnMessage['max_page']=$max_page;
        }
        return json($this->returnMessage);
    }
    //素材分类
    function sourceCate(){
        $source_cate=Db::name('source_cate')->select();
        if($source_cate){
            $this->returnMessage['code']    ='success';
            $this->returnMessage['message'] ='查询成功';
            $this->returnMessage['data']    =$source_cate;
        }
        return json($this->returnMessage);
    }
    // //所属分类素材
    // function sourceBelongCate(){
    //     $source_cate_id=$this->request->has('id')?$this->request->param('id',0,'intval'):0;
    //     if(!$source_cate_id){
    //         $this->returnMessage['code']='error';
    //         $this->returnMessage['message']='期望参数不存在';
    //     }else{
    //         $source=Db::name('source')->where('column_id',$source_cate_id)->select();
    //         if(!$source){
    //             $this->returnMessage['code']='error';
    //             $this->returnMessage['message']='获取数据失败，请重试';
    //         }else{
    //             $this->returnMessage['code']='success';
    //             $this->returnMessage['message']='获取数据成功';
    //             $this->returnMessage['data']=$source;
    //         }
    //     }
    //     return json($this->returnMessage);
    // }

    //保存我的素材图片
    function preservationSource(){
        $mid    =   session('member')['id'];
        if(!$mid){
            $this->returnMessage['code']   = 'error';
            $this->returnMessage['message']= '未登录';
        }else{
            $thumb_src  =$this->request->param('img');
            $thumb_title=$this->request->has('title')?$this->request->param('title'):time();
            $create_time=time();
            $thumb      =$this->request->has('thumb')?$this->request->param('thumb',0,'intval'):0;
            if(!$thumb_src||$thumb==0){
                $this->returnMessage['code']    ='error';
                $this->returnMessage['message'] ='未获取到图片信息';
            }else{
                //查找相同数据
                if(Db::name('member_source')->where(['mid'=>$mid,'thumb'=>$thumb])->find()){
                    $this->returnMessage['code']    = 'error';
                    $this->returnMessage['message'] = '图片已存在';
                    return json($this->returnMessage);die;
                }
                //写入数据
                $source=Db::name('member_source')->insert([
                    'source_title'=>$thumb_title,
                    'thumb_src'   =>$thumb_src,
                    'create_time' =>$create_time,
                    'mid'         =>$mid,
                    'thumb'       =>$thumb
                ]);
                if($source){
                    $this->returnMessage['code']    = 'success';
                    $this->returnMessage['message'] = '保存成功';
                }else{
                    $this->returnMessage['code']    = 'error';
                    $this->returnMessage['message'] = '保存失败，请重试';
                }
            }
        }
        return json($this->returnMessage);
    }

    //查看我的保存的图片
    function mySource(){
        $mid    =   session('member')['id'];
        if(!$mid){
            $this->returnMessage['code']    = 'error';
            $this->returnMessage['message'] = '未登录';
        }else{
            $page     =$this->request->param('page',0,'intval');
            $my_source=Db::name('member_source')->where(['mid'=>$mid,'isDelete'=>0])->order('id desc')->limit('10')->page($page)->select();
            unset($my_source['isDelete']);
            $this->returnMessage['code']    ='success';
            $this->returnMessage['message'] ='查询成功';
            $this->returnMessage['data']    =$my_source;
        }
        return json($this->returnMessage);
    }

    //删除我的素材
    function deleteMySource(){
        $mid    =   session('member')['id'];
        if(!$mid){
            $this->returnMessage['code']    ='error';
            $this->returnMessage['message'] ='未登录';
        }else{
            $id = $this->request->has('id')?$this->request->param('id'):0;
            if($id==0){
                $this->returnMessage['code']    ='error';
                $this->returnMessage['message'] ='图片不存在，请重新选择';
            }else{
                $delete=Db::name('member_source')->where(['id'=>$id,'mid'=>$mid])->update(['isDelete'=>1]);
                if(!$delete){
                    $this->returnMessage['code']    ='error';
                    $this->returnMessage['message'] ='删除失败，请重试！';
                }else{
                    $this->returnMessage['code']    ='success';
                    $this->returnMessage['message'] ='删除成功';
                }
            }
        }
        return json($this->returnMessage);
    }
}