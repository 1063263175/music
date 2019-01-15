<?php
namespace app\apppic\controller;

use think\Db;
use think\File;
use think\Request;
use think\Session;
use think\Validate;

class Setting extends Index
{
    //获取用户已设置图片
    public function setImg()
    {
        if (!session('member')['id']) {

            $this->returnMessage['code']    = 'error';
            $this->returnMessage['message'] = '未登录';
            return json($this->returnMessage);
            die;
        }
        if (Request::instance()->isPost()) {
            $uid = session('member')['id'];

            // 获取图片
            $file = $this->request->param('img_src');
            //判断是否为新建分组
            if ($this->request->has('group_id')) {
                $group_id = $this->request->param('group_id');
            } else if (!Db::name('pic')->field('group_id')->where('uid', $uid)->find()) {
                $group_id = 1;
            } else {
                $group    = Db::name('pic')->where('uid', $uid)->max('group_id');
                $group_id = $group + 1;
            }
            if ($file) {
                    $allowFields = [
                        'uid',

                        'creat_time',

                        'img_src',

                        'update_time',

                        'img_title',
                        'group_id',
                        'group_title',
                    ];
                    foreach ($this->request->param() as $key => $value) {
                        if (in_array($key, $allowFields)) {
                            $insertData[$key] = $value;
                        }
                    }
                    $insertData['uid'] = $uid;
                    //重新设置时间
                    $insertData['creat_time'] = time();
                    //获取图片分组id
                    $insertData['group_id'] = $group_id;
                    if ($group_id) {
                        if (!Db::name('pic')->where('group_id', $group_id)->insert($insertData)) {
                            $this->returnMessage['code'] = 'error';

                            $this->returnMessage['message'] = '上传失败，请重试！';

                        } else {

                            $pic = Db::name('pic')->field($allowFields)->where([
                                'uid'      => $uid,
                                'group_id' => $group_id,
                            ])->order("id desc")->select();

                            $this->returnMessage['code']    = "success";
                            $this->returnMessage['message'] = '上传成功';

                            $this->returnMessage['data']            = ['group_id' => $group_id];
                            $this->returnMessage['data']['img_src'] = $insertData['img_src'];
                        }
                    } else {
                        if (!Db::name('pic')->insert($insertData)) {
                            $this->returnMessage['code'] = 'error';

                            $this->returnMessage['message'] = '上传失败，请选择分组！';

                        }
                    }

                } else {
                // 上传失败
                $this->returnMessage['code']    = 'error';
                $this->returnMessage['message'] = '上传失败，请重试';
            }

        } else {
            $this->returnMessage['code']    = 'error';
            $this->returnMessage['message'] = '请使用POST方式请求';
        }
        return json($this->returnMessage);
    }

    //图片分组
    public function img_Group()
    {

        if (Request::instance()->isGet() || Request::instance()->isPost()) {
            $uid = session('member')['id'];
            /**
             * 判断是否传入分组ID
             * 未传入：返回用户所有分组信息
             * 传入：返回当前分组id信息
             */
            $page = $this->request->has('page') ? $this->request->param('page', 0, 'intval') : 1;
            $id   = $this->request->param('id');
            if ($id) {
                $member_img                     = Db::name('pic')->group('group_id')->where(['uid'=> $id,'isDelet'=>0])->order('id desc')->limit('10')->page($page)->select();
                $this->returnMessage['code']    = 'success';
                $this->returnMessage['message'] = '获取成功';
                $this->returnMessage['data']    = $member_img;
                return json($this->returnMessage);die;
            }
            $group_id=$this->request->param('group_id');
            if($id&&$group_id){
                $allowFields = [
                    'id',
                    'group_id',
                    'group_title',
                    'creat_time',
                    'update_time',
                    'img_src',
                ];
                //查找分组信息
                $group = Db::name('pic')->field($allowFields)->group('group_id')->where(['id' => $id, 'isDelet' => 0])->order('group_id desc')->limit('10')->page($page)->select();

                $this->returnMessage['code']    = 'success';
                $this->returnMessage['message'] = '查询成功';
                $this->returnMessage['data']    = $group;
                return json($this->returnMessage);die;
            }
            if (!$this->request->param('group_id')) {
                $allowFields = [
                    'id',
                    'group_id',
                    'group_title',
                    'creat_time',
                    'update_time',
                    'img_src',
                ];
                //查找分组信息
                $group = Db::name('pic')->field($allowFields)->group('group_id')->where(['uid' => $uid, 'isDelet' => 0])->order('group_id desc')->limit('10')->page($page)->select();

                $this->returnMessage['code']    = 'success';
                $this->returnMessage['message'] = '查询成功';
                $this->returnMessage['data']    = $group;

            } else {
                $group_id    = $this->request->param('group_id');
                $allowFields = [
                    'uid',
                    'group_id',
                    'group_title',
                    'creat_time',
                    'update_time',
                    'img_src',
                ];
                //查找当前分组id信息
                $group = Db::name('pic')->field($allowFields)->where([
                    'uid'      => $uid,
                    'group_id' => $group_id,
                    'isDelet'  => 0,
                ])->limit('10')->page($page)->select();

                $this->returnMessage['code'] = 'success';

                $this->returnMessage['message'] = '查询成功';

                $this->returnMessage['data'] = $group;

            }
        } else {
            $this->returnMessage['code']    = 'error';
            $this->returnMessage['message'] = '请使用get方式请求。';
        }
        return json($this->returnMessage);
    }

    //分组或图片删除
    public function deletImg()
    {
        if (!session('member')['id']) {

            $this->returnMessage['code']    = 'error';
            $this->returnMessage['message'] = '未登录';
            return json($this->returnMessage);
            die;
        }
        if (!Request::instance()->isPost()) {
            $this->returnMessage['code']    = 'error';
            $this->returnMessage['message'] = '请使用POST方式';
        } else {
            $group_id=$this->request->has('group_id')?$this->request->param('group_id'):0;
            $id      =$this->request->has('id')?$this->request->param('id'):0;
            if($group_id){
                $delete=Db::name('pic')->where('group_id',$group_id)->update(['isDelet'=>1]);
                if($delete){
                    $this->returnMessage['code']='success';
                    $this->returnMessage['message']='删除成功';
                }else{
                    $this->returnMessage['code']='error';
                    $this->returnMessage['message']='删除失败,文件不存在或已删除'; 
                }
                return json($this->returnMessage);die;
            }
            if(!$group_id && $id){
                $delete=Db::name('pic')->where('md5_id',$id)->update(['isDelet'=>1]);
                if($delete){
                    $this->returnMessage['code']='success';
                    $this->returnMessage['message']='删除成功';
                }else{
                    $this->returnMessage['code']='error';
                    $this->returnMessage['message']='删除失败,文件不存在或已删除'; 
                }
                return json($this->returnMessage);die;
            }
            if($group_id && $id){
                $delete=Db::name('pic')->where(['md5_id'=>$id,'group_id'=>$group_id])->update(['isDelet'=>1]);
                if($delete){
                    $this->returnMessage['code']='success';
                    $this->returnMessage['message']='删除成功';
                }else{
                    $this->returnMessage['code']='error';
                    $this->returnMessage['message']='删除失败,文件不存在或已删除'; 
                }
                return json($this->returnMessage);die;
            }

        }
        return json($this->returnMessage);
    }
    //分享图片
    public function shareImg()
    {
        if (!session('member')['id']) {

            $this->returnMessage['code']    = 'error';
            $this->returnMessage['message'] = '未登录';
            return json($this->returnMessage);

            die;
        }
        $uid         = session('member')['id'];
        $group_id    = $this->request->param('group_id');
        $allowFields = [
            'uid',
            'img_src',
            'group_id',
            'group_title',
        ];

        $pic        = Db::name('pic')->field($allowFields)->where(['uid' => $uid, 'group_id' => $group_id])->select();
        $insertData = [];
        foreach ($pic as $k => $v) {

            $insertData[$k] = $v;

        }
        if (!$group_id) {
            $this->returnMessage['code']    = 'error';
            $this->returnMessage['message'] = '分享失败';
        } else {
            if (Db::name('share')->where(['uid' => $uid, 'group_id' => $group_id])->find()) {
                $this->returnMessage['code']      = 'success';
                $this->returnMessage['message']   = '分享成功';
                $this->returnMessage['share_url'] = "http://pic.appudid.cn/apppic/setting/imgShare/uid/$uid/group_id/$group_id";
            } else {

                $share = Db::name('share')->insertAll($insertData);

                if ($share < 1) {
                    $this->returnMessage['code']    = 'error';
                    $this->returnMessage['message'] = '请先上传图片';
                } else {
                    $this->returnMessage['code']      = 'success';
                    $this->returnMessage['message']   = '分享成功';
                    $this->returnMessage['share_url'] = "http://pic.appudid.cn/apppic/setting/imgShare/uid/$uid/group_id/$group_id";
                }
            }

        }
        return json($this->returnMessage);
    }
    //用户查看分享
    public function checkShare()
    {

        if (!session('member')['id']) {

            $this->returnMessage['code']    = 'error';
            $this->returnMessage['message'] = '未登录';

        } else {
            $uid = $this->request->param('uid');
            if (!$uid) {
                $this->returnMessage['code']    = 'error';
                $this->returnMessage['message'] = '未获取到数据';
                return json($this->returnMessage);
                die;
            }
            // $allowFields=[
            //     'id',
            //     'nickname',
            //     'mobile',
            //     'thumb_src',
            //     'sex',
            //     'area'
            // ];
            $allowFields_pic = [
                'uid',
                'img_src',
                'group_id',
                'group_title',
            ];
            // $member=Db::name('member')->field($allowFields)->where('id',$uid)->find();
            $share = Db::name('share')->field($allowFields_pic)->where(['md5_id' => $uid])->select();

            $this->returnMessage['code']    = 'success';
            $this->returnMessage['message'] = '查询成功';
            // $this->returnMessage['data']=$member;
            $this->returnMessage['data'] = $share;

        }

        return json($this->returnMessage);
    }
    //分享图片显示
    public function imgShare()
    {
        $uid      = $this->request->param('uid');
        $group_id = $this->request->param('group_id');
        $share    = Db::name('share')->where(['md5_id' => $uid, 'group_id' => $group_id])->select();
        $this->assign('share', $share);

        return $this->fetch();
    }
}
