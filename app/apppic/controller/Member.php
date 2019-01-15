<?php
namespace app\apppic\controller;

use think\Db;
use think\Request;
use think\Page;
use think\Session;

class Member extends Index {

    // 发布反馈信息
    function sendfeedback() {
        $openid = $this->request->has('openid') ? $this->request->param('openid') : 0;
        if (! $openid) {
            $this->returnMessage['code'] = 'error';
            $this->returnMessage['message'] = '没有获取到openid';
            return json($this->returnMessage);
            die();
        }
        $mid = Db::name('member')->where('openid', $openid)->value('id');
        $feedback = $this->request->has('feedback') ? $this->request->param('feedback') : 0;
        if (! $feedback) {
            $this->returnMessage['code'] = 'error';
            $this->returnMessage['message'] = '没有输入反馈信息';
            return json($this->returnMessage);
            die();
        }
        $res = Db::name('feedback')->insert([
            'mid' => $mid,
            'feedback' => $feedback,
            'create_time' => time()
        ]);
        if ($res) {
            $this->returnMessage['code'] = 'success';
            $this->returnMessage['message'] = '反馈成功';
            return json($this->returnMessage);
            die();
        } else {
            $this->returnMessage['code'] = 'error';
            $this->returnMessage['message'] = '反馈失败';
            return json($this->returnMessage);
            die();
        }
    }

    // 发送短信验证码
    function sms() {
        $mobile = $this->request->param('mobile');
        if (empty($mobile)) {
            return $this->returnMessage('error', '手机号码不能为空！');
        }
        if (! preg_match("/^1[34578]\d{9}$/", $mobile)) {
            $this->returnMessage['code'] = 'error';
            $this->returnMessage['message'] = '手机号格式错误';
            return json($this->returnMessage);
        }
        $verify_code = mt_rand(111111, 999999);
        // dump($articlename);die;
        $res = abc($mobile, $verify_code);

        if ($res->Code != 'OK') {
            $this->returnMessage['return_code'] = 'error';
            $this->returnMessage['return_message'] = '发送失败！';
            $this->returnMessage['bbb'] = $res;
        } else {
            session('verify_code' . $mobile, $verify_code);
            $this->returnMessage['return_code'] = 'success';
            $this->returnMessage['return_message'] = '发送成功！';
            $this->returnMessage['aaa'] = session('verify_code' . $mobile);
        }

        return json($this->returnMessage);
    }

    // 绑定手机号
    function bindingtel() {
        $openid = $this->request->has('openid') ? $this->request->param('openid') : 0;

        if (! $openid) {
            $this->returnMessage['code'] = 'error';
            $this->returnMessage['message'] = '没有获取到openid';
            return json($this->returnMessage);
            die();
        }
        $mobile = Db::name('member')->where('openid', $openid)->value('mobile');
        if ($mobile) {
            $this->returnMessage['code'] = 2;
            $this->returnMessage['message'] = '该用户已经绑定手机号';
            return json($this->returnMessage);
            die();
        }
        $tel = $this->request->has('tel') ? $this->request->param('tel') : 0;

        if (! $tel) {
            $this->returnMessage['code'] = 'error';
            $this->returnMessage['message'] = '没有获取到手机号';
            return json($this->returnMessage);
            die();
        }
        if (! preg_match("/^1[34578]\d{9}$/", $tel)) {
            $this->returnMessage['code'] = 'error';
            $this->returnMessage['message'] = '手机号格式错误';
            return json($this->returnMessage);
            die();
        }
        $verify_code = $this->request->has('verify_code') ? $this->request->param('verify_code') : 0;
        $sessioncode = $this->request->has('sessioncode') ? $this->request->param('sessioncode') : '';//服务器端缓存的验证码
        //$sessioncode = session('verify_code' . $tel);
        if ($verify_code != $sessioncode) {
            $this->returnMessage['code'] = 'error';
            $this->returnMessage['message'] = '手机验证码错误';
            $this->returnMessage['verify_code'] = $sessioncode;
            $this->returnMessage['verify_code2'] = $verify_code;
            return json($this->returnMessage);
            die();
        }
        $res = Db::name('member')->where('openid', $openid)->update([
            'mobile' => $tel
        ]);
        if ($res !== false) {
            $this->returnMessage['code'] = 'success';
            $this->returnMessage['message'] = '绑定成功';
            $this->returnMessage['verify_code'] = $sessioncode;
            $this->returnMessage['verify_code2'] = $verify_code;
            return json($this->returnMessage);
            die();
        } else {
            $this->returnMessage['code'] = 'error';
            $this->returnMessage['message'] = '绑定失败';
            $this->returnMessage['verify_code'] = $sessioncode;
            $this->returnMessage['verify_code2'] = $verify_code;
            return json($this->returnMessage);
            die();
        }
    }

    // 编辑昵称
    function editnickname() {
        $openid = $this->request->has('openid') ? $this->request->param('openid') : 0;
        if (! $openid) {
            $this->returnMessage['code'] = 'error';
            $this->returnMessage['message'] = '没有获取到openid';
            return json($this->returnMessage);
            die();
        }
        $nickname = Db::name('member')->where('openid', $openid)->value('nickname');
        if ($nickname) {
            $this->returnMessage['code'] = 'success';
            $this->returnMessage['message'] = '成功';
            $this->returnMessage['nickname'] = $nickname;
        } else {
            $this->returnMessage['code'] = 'error';
            $this->returnMessage['message'] = '没有获取到用户昵称';
        }
        return json($this->returnMessage);
    }

    // 用户查看消息
    function lookmessage() {
        $openid = $this->request->has('openid') ? $this->request->param('openid') : 0;
        if (! $openid) {
            $this->returnMessage['code'] = 'error';
            $this->returnMessage['message'] = '没有获取到openid';
            return json($this->returnMessage);
            die();
        }
        $mid = Db::name('member')->where('openid', $openid)->value('id');
        $id = $this->request->has('id') ? $this->request->param('id') : 0; // 消息id
        if (! $id) {
            $this->returnMessage['code'] = 'error';
            $this->returnMessage['message'] = '没有获取到消息id';
            return json($this->returnMessage);
            die();
        }
        $res = Db::name('messages')->where('mid', $mid)
            ->where('id', $id)
            ->update([
                'is_look' => 1
            ]);
        if ($res) {
            $this->returnMessage['code'] = 'success';
            $this->returnMessage['message'] = '成功，该消息标记为已读。';
        }
        return json($this->returnMessage);
    }

    // 判断是否有未读消息
    function unread() {
        $openid = $this->request->has('openid') ? $this->request->param('openid') : 0;
        if (! $openid) {
            $this->returnMessage['code'] = 'error';
            $this->returnMessage['message'] = '没有获取到openid';
            return json($this->returnMessage);
            die();
        }
        $mid = Db::name('member')->where('openid', $openid)->value('id');
        $data = Db::name('messages')->where('mid', $mid)
            ->where('is_look', 0)
            ->find();
        if ($data) {
            $this->returnMessage['code'] = 'success';
            $this->returnMessage['message'] = '有未读消息';
        } else {
            $this->returnMessage['code'] = 'error';
            $this->returnMessage['message'] = '没有未读消息';
        }
        return json($this->returnMessage);
    }

    // 最终版我的消息
    // 最终版我的消息
    function mymessageend() {
        $openid = $this->request->has('openid') ? $this->request->param('openid') : 0;
        if (! $openid) {
            $this->returnMessage['code'] = 'error';
            $this->returnMessage['message'] = '没有获取到openid';
            return json($this->returnMessage);
            die();
        }
        $mid = Db::name('member')->where('openid', $openid)->value('id');
        if (! $mid) {
            $this->returnMessage['code'] = 'error';
            $this->returnMessage['message'] = '没有找到用户的信息';
            return json($this->returnMessage);
            die();
        }
        // 获取老师的mid
        $teacherids = Db::name('member')->field('id')
            ->where('is_teacher', 1)
            ->select();
        if (! $teacherids) {
            $this->returnMessage['code'] = 'error';
            $this->returnMessage['message'] = '没有找到老师的信息';
            return json($this->returnMessage);
            die();
        }
        $messageids = Db::name('comment')->field('id')
            ->where('mid', $mid)
            ->where('is_show', 1)
            ->select();

        if (! $messageids) {
            $this->returnMessage['code'] = 'error';
            $this->returnMessage['message'] = '没有找到用户讨论的信息';
            return json($this->returnMessage);
            die();
        }
        $messagearr = [];
        // 获取我的讨论
        $taolun = Db::name('comment')->where('mid', $mid)
            ->field('id,article_id,mid,create_time,content,src,is_show,video_id,is_free,is_mp3,is_mp4')
            ->where('is_show', 1)
            ->order('id desc')
            ->select();

        foreach ($taolun as $k => $v) {
            $taolun[$k]['huifu'] = Db::name('comment')->where('pid', $v['id'])->value('content');
            $taolun[$k]['teacherid'] = Db::name('comment')->where('pid', $v['id'])->value('mid');
            $taolun[$k]['thumb_src'] = Db::name('member')->where('id', $taolun[$k]['teacherid'])->value('thumb_src');
            $taolun[$k]['nickname'] = Db::name('member')->where('id', $taolun[$k]['teacherid'])->value('nickname');
            $taolun[$k]['create_time_teacher'] = Db::name('comment')->where('pid', $v['id'])->value('create_time');
        }

        // 获取后台发送的我的消息
        $allowFields = [
            'create_time',
            'message',
            'teacherid'
        ];
        $mymessage = Db::name('messages')->where('mid', $mid)
            ->field($allowFields)
            ->select();
        foreach ($mymessage as $k => $v) {
            $mymessage[$k]['thumb_src'] = Db::name('member')->where('id', $v['teacherid'])->value('thumb_src');
            $mymessage[$k]['nickname'] = Db::name('member')->where('id', $v['teacherid'])->value('nickname');
            $mymessage[$k]['create_time'] = date('m-d', $v['create_time']);
        }

        $this->returnMessage['code'] = 'success';
        $this->returnMessage['message'] = '讨论信息+我的消息。成功';
        $this->returnMessage['data'] = $taolun;
        $this->returnMessage['data2'] = $mymessage;
        return json($this->returnMessage);
        die();
    }

    // 我的消息
    function mymessage() {
        $openid = $this->request->has('openid') ? $this->request->param('openid') : 0;
        if (! $openid) {
            $this->returnMessage['code'] = 'error';
            $this->returnMessage['message'] = '没有获取到openid';
            return json($this->returnMessage);
            die();
        }
        $mid = Db::name('member')->where('openid', $openid)->value('id');
        $allowFields = [
            'id',
            'message',
            'is_look'
        ];

        $data = Db::name('messages')->where('mid', $mid)
            ->field($allowFields)
            ->order('create_time desc')
            ->select();
        /*
         * foreach($data as $k => $v){
         * if($v['is_look']==0){
         * $this->returnMessage['redpoint'] = 1; //判断是否有未读消息
         * }
         * }
         */

        if ($data) {
            $this->returnMessage['code'] = 'success';
            $this->returnMessage['message'] = '成功';
            $this->returnMessage['data'] = $data;
        }
        return json($this->returnMessage);
    }

    // 点赞
    function zan() {
        $comment_id = $this->request->has('comment_id') ? $this->request->param('comment_id') : 0;
        $openid = $this->request->has('openid') ? $this->request->param('openid') : 0;
        if (! $openid) {
            $this->returnMessage['code'] = 'error';
            $this->returnMessage['message'] = '没有获取到openid';
            return json($this->returnMessage);
            die();
        }
        $mid = Db::name('member')->where('openid', $openid)->value('id');
        if (! $comment_id) {
            $this->returnMessage['code'] = 'error';
            $this->returnMessage['message'] = '没有获取到comment_id';
            return json($this->returnMessage);
            die();
        }
        $info = Db::name('comment_zan')->where('mid', $mid)
            ->where('comment_id', $comment_id)
            ->find();
        if ($info) {
            $this->returnMessage['code'] = 'error';
            $this->returnMessage['message'] = '已经点过赞了。';
            return json($this->returnMessage);
            die();
        }
        $data = [
            'mid' => $mid,
            'comment_id' => $comment_id
        ];
        $res = Db::name('comment_zan')->insert($data);
        if ($res) {
            $this->returnMessage['code'] = 'success';
            $this->returnMessage['message'] = '点赞成功';
        } else {
            $this->returnMessage['code'] = 'error';
            $this->returnMessage['message'] = '点赞失败';
        }
        return json($this->returnMessage);
    }

    // 讨论个数
    function discussNum() {
        $article_id = $this->request->has('article_id') ? $this->request->param('article_id') : 0;
        if (! $article_id) {
            $this->returnMessage['code'] = 'error';
            $this->returnMessage['message'] = '没有获取到article_id';
            return json($this->returnMessage);
            die();
        }
        $num = Db::name('comment')->where('article_id', $article_id)
            ->where('is_show', 1)
            ->count();
        if ($num) {
            $this->returnMessage['code'] = 'success';
            $this->returnMessage['message'] = '成功';
            $this->returnMessage['data'] = $num;
        } else {
            $this->returnMessage['code'] = 'error';
            $this->returnMessage['message'] = '没有讨论信息';
        }
        return json($this->returnMessage);
    }

    // 班群讨论列表
    function discussList() {
        $video_id = $this->request->has('video_id') ? $this->request->param('video_id') : 0;
        $openid = $this->request->has('openid') ? $this->request->param('openid') : 0;
        if (! $openid) {
            $this->returnMessage['code'] = 'error';
            $this->returnMessage['message'] = '没有获取到openid';
            return json($this->returnMessage);
            die();
        }
        $mid = Db::name('member')->where('openid', $openid)->value('id');
        // $page = $this->request->has('page') ? $this->request->param('page') : 1;
        if (! $video_id) {
            $this->returnMessage['code'] = 'error';
            $this->returnMessage['message'] = '没有获取到video_id';
            return json($this->returnMessage);
            die();
        }
        $is_free=$this->request->has('is_free') ? $this->request->param('is_free') : 0;
        $allowFields = [
            'co.id',
            'co.is_best',
            'co.is_top',
            'co.create_time',
            'co.zan',
            'co.content',
            'co.pid',
            'co.src',
            'co.video_id',
            'co.video_url',
            'co.is_mp3',
            'co.is_mp4',
            'm.thumb_src',
            'm.nickname',
            'm.is_teacher'
        ];

        $res = Db::name('comment')->alias('co')
            ->join('member m', 'co.mid=m.id')
            ->field($allowFields)
            ->order('co.is_top desc,co.is_best desc,co.id desc')
            ->where('co.is_show', 1)
            ->where('co.video_id', $video_id)
            ->select();

        // 判断是否已经点赞
        if ($res) {
            foreach ($res as $k => $v) {
                $comment_zan = Db::name('comment_zan')->where('mid', $mid)->select();
                foreach ($comment_zan as $k2 => $v2) {
                    if ($v['id'] == $v2['comment_id']) {
                        $res[$k]['is_zan'] = 1;
                    }
                }
            }

            $result = $this->GetTree($res);
            $this->returnMessage['code'] = 'success';
            $this->returnMessage['message'] = '成功';
            $this->returnMessage['data'] = $result;
        } else {
            $this->returnMessage['code'] = 'error';
            $this->returnMessage['message'] = '没有获取到讨论信息';
        }
        return json($this->returnMessage);
    }
    function free_com(){
        $article_id=$this->request->has('article_id') ? $this->request->param('article_id') : 0;
        $openid = $this->request->has('openid') ? $this->request->param('openid') : 0;
        if (! $openid) {
            $this->returnMessage['code'] = 'error';
            $this->returnMessage['message'] = '没有获取到openid';
            return json($this->returnMessage);
            die();
        }
        $mid = Db::name('member')->where('openid', $openid)->value('id');
        $allowFields=[
            'co.id',
            'co.is_best',
            'co.is_top',
            'co.create_time',
            'co.zan',
            'co.content',
            'co.pid',
            'co.src',
            'co.video_id',
            'co.video_url',
            'co.is_free',
            'co.is_mp3',
            'co.is_mp4',
            'm.thumb_src',
            'm.nickname',
            'm.is_teacher'
        ];
        $res = Db::name('comment')->alias('co')
            ->join('member m', 'co.mid=m.id')
            ->field($allowFields)
            ->order('co.is_top desc,co.is_best desc,co.id desc')
            ->where('co.article_id', $article_id)
            ->where('co.is_free', 1)
            ->where('co.is_show', 1)
            ->select();
        if ($res) {
            foreach ($res as $k => $v) {
                $comment_zan = Db::name('comment_zan')->where('mid', $mid)->select();
                foreach ($comment_zan as $k2 => $v2) {
                    if ($v['id'] == $v2['comment_id']) {
                        $res[$k]['is_zan'] = 1;
                    }
                }
            }

            $result = $this->GetTree($res);
            $this->returnMessage['code'] = 'success';
            $this->returnMessage['message'] = '成功';
            $this->returnMessage['data'] = $result;
        } else {
            $this->returnMessage['code'] = 'error';
            $this->returnMessage['message'] = '没有获取到讨论信息';
        }
        return json($this->returnMessage);
    }
    function sort3wei($array) {
        foreach ($array as $key => $val) {
            // dump($array[$key]);die;
            $new_array = array();
            $sort_array = array();
            foreach ($val['children'] as $k => $v) {
                $sort_array[$k] = $v['id'];
            }
            asort($sort_array); // 降序使用 arsort();
            reset($sort_array);
            // dump($sort_array);die;
            foreach ($sort_array as $k => $v) {
                $new_array[$k] = $array[$key]['children'][$k];
            }
            // dump($new_array);die;
            $array[$key]['children'] = $new_array;
            // dump($array);die;
        }
        return $array;
    }

    function GetTree($data) {
        $ret = array();

        foreach ($data as $k => $v) {
            if ($v['pid'] == 0) {

                foreach ($data as $k1 => $v1) {
                    if ($v1['pid'] == $v['id']) {

                        /*
                         * foreach($data as $k2 => $v2){
                         * if($v2['pid']==$v1['id']){
                         *
                         * $v1['children'][]=$v2;
                         * }
                         * }
                         */

                        $v['children'][] = $v1;
                    }
                }
                $ret[] = $v;
            }
        }

        return $ret;
    }

    function GetTree1($arr1 , $pid , $step) {
        static $tree;
        foreach ($arr1 as $key => $val) {
            if ($val['pid'] == $pid) {
                $flg = str_repeat('', $step);
                $val['content'] = $flg . $val['content'];
                $tree[] = $val;
                $this->GetTree($arr1, $val['id'], $step + 1);
            }
        }

        return $tree;
    }
    // 班群回复
    function replyPost() {
        $article_id = $this->request->has('article_id') ? $this->request->param('article_id') : 0;
        $video_id = $this->request->has('video_id') ? $this->request->param('video_id') : 0;
        $openid = $this->request->has('openid') ? $this->request->param('openid') : 0;
        $content = $this->request->has('content') ? $this->request->param('content') : 0;
        $discussid = $this->request->has('discussid') ? $this->request->param('discussid') : 0;
        if (! $content) {
            $this->returnMessage['code'] = 'error';
            $this->returnMessage['message'] = '内容不能为空';
            return json($this->returnMessage);
            die();
        }
        if (! $openid) {
            $this->returnMessage['code'] = 'error';
            $this->returnMessage['message'] = '没有获取到openid';
            return json($this->returnMessage);
            die();
        }
        if (! $article_id) {
            $this->returnMessage['code'] = 'error';
            $this->returnMessage['message'] = '没有获取到article_id';
            return json($this->returnMessage);
            die();
        }
//p
        if (! $discussid) {
            $this->returnMessage['code'] = 'error';
            $this->returnMessage['message'] = '没有获取到回复信息id';
            return json($this->returnMessage);
            die();
        }
        $one = Db::name('comment')->where('article_id', $article_id)
            ->where('id', $discussid)
            ->find();
        if (! $one) {
            $this->returnMessage['code'] = 'error';
            $this->returnMessage['message'] = '参数错误';
            return json($this->returnMessage);
            die();
        }
        $mid = Db::name('member')->where('openid', $openid)->value('id');
        // 判断是否是老师 只有老师有权限回复讨论
        $is_teacher = Db::name('member')->where('openid', $openid)->value('is_teacher');
        if ($is_teacher == 0) {
            $this->returnMessage['code'] = 'error';
            $this->returnMessage['message'] = '只有老师可以回复';
            return json($this->returnMessage);
            die();
        }
        $time = date('m-d', time());
        $data = [
            'article_id' => $article_id,
            'video_id' => $video_id,
            'mid' => $mid,
            'pid' => $discussid,
            'create_time' => $time,
            'content' => $content
        ];
        $res = Db::name('comment')->insert($data);
        if ($res) {
            $this->returnMessage['code'] = 'success';
            $this->returnMessage['message'] = '成功';
        } else {
            $this->returnMessage['code'] = 'error';
            $this->returnMessage['message'] = '失败';
        }
        return json($this->returnMessage);
    }

    // 点击课程视频，返回讨论条数
    function comment_num() {
        $video_id = $this->request->has('video_id') ? $this->request->param('video_id') : '';
        if (! $video_id) {
            $this->returnMessage['code'] = 'error';
            $this->returnMessage['message'] = '没有获取到video_id';
            return json($this->returnMessage);
            die();
        }
        $num = Db::name('comment')->where('video_id', $video_id)
            ->where('is_show', 1)
            ->count();
        if ($num !== false) {
            $this->returnMessage['code'] = 'success';
            $this->returnMessage['message'] = '成功';
            $this->returnMessage['data'] = $num;
        } else {
            $this->returnMessage['code'] = 'error';
            $this->returnMessage['message'] = '失败';
        }
        return json($this->returnMessage);
    }
//免费课程下评论回复
    function replyPost1() {
        $article_id = $this->request->has('article_id') ? $this->request->param('article_id') : 0;
//        $video_id = $this->request->has('video_id') ? $this->request->param('video_id') : 0;
        $openid = $this->request->has('openid') ? $this->request->param('openid') : 0;
        $content = $this->request->has('content') ? $this->request->param('content') : 0;
        $discussid = $this->request->has('discussid') ? $this->request->param('discussid') : 0;
        if (! $content) {
            $this->returnMessage['code'] = 'error';
            $this->returnMessage['message'] = '内容不能为空';
            return json($this->returnMessage);
            die();
        }
        if (! $openid) {
            $this->returnMessage['code'] = 'error';
            $this->returnMessage['message'] = '没有获取到openid';
            return json($this->returnMessage);
            die();
        }
        if (! $article_id) {
            $this->returnMessage['code'] = 'error';
            $this->returnMessage['message'] = '没有获取到article_id';
            return json($this->returnMessage);
            die();
        }
        if (! $discussid) {
            $this->returnMessage['code'] = 'error';
            $this->returnMessage['message'] = '没有获取到回复信息id';
            return json($this->returnMessage);
            die();
        }
        $one = Db::name('comment')->where('article_id', $article_id)
            ->where('id', $discussid)
            ->find();
        if (! $one) {
            $this->returnMessage['code'] = 'error';
            $this->returnMessage['message'] = '参数错误';
            return json($this->returnMessage);
            die();
        }
        $mid = Db::name('member')->where('openid', $openid)->value('id');
        // 判断是否是老师 只有老师有权限回复讨论
        $is_teacher = Db::name('member')->where('openid', $openid)->value('is_teacher');
        if ($is_teacher == 0) {
            $this->returnMessage['code'] = 'error';
            $this->returnMessage['message'] = '只有老师可以回复';
            return json($this->returnMessage);
            die();
        }
        $time = date('m-d', time());
        $data = [
            'article_id' => $article_id,
//            'video_id' => $video_id,
            'mid' => $mid,
            'pid' => $discussid,
            'create_time' => $time,
            'content' => $content,
            'is_free' => 1,
        ];
        $res = Db::name('comment')->insert($data);
        if ($res) {
            $this->returnMessage['code'] = 'success';
            $this->returnMessage['message'] = '成功';
        } else {
            $this->returnMessage['code'] = 'error';
            $this->returnMessage['message'] = '失败';
        }
        return json($this->returnMessage);
    }
    // 班群讨论发布
    function discussPost1() {
        $article_id = $this->request->has('article_id') ? $this->request->param('article_id') : '';
        $openid = $this->request->has('openid') ? $this->request->param('openid') : '';
        $content = $this->request->has('content') ? $this->request->param('content') : '';
        $pic_url = $this->request->has('pic_url') ? $this->request->param('pic_url') : '';
        $is_mp3 = $this->request->has('is_mp3') ? $this->request->param('is_mp3') : '';
        $is_mp4 = $this->request->has('is_mp4') ? $this->request->param('is_mp4') : '';


        if (! $content) {
            $this->returnMessage['code'] = 'error';
            $this->returnMessage['message'] = '内容不能为空';
            return json($this->returnMessage);
            die();
        }
        if (! $openid) {
            $this->returnMessage['code'] = 'error';
            $this->returnMessage['message'] = '没有获取到openid';
            return json($this->returnMessage);
            die();
        }
        if (! $article_id) {
            $this->returnMessage['code'] = 'error';
            $this->returnMessage['message'] = '没有获取到article_id';
            return json($this->returnMessage);
            die();
        }


        $mid = Db::name('member')->where('openid', $openid)->value('id');
        $time = date('m-d', time());
        $data = [
            'article_id' => $article_id,
            'mid' => $mid,
            'create_time' => $time,
            'content' => $content,
            'src' => $pic_url,
            'is_mp3' => $is_mp3,
            'is_mp4' => $is_mp4,
            'is_free' => 1,

        ];
        $res = Db::name('comment')->insertGetId($data);
        if ($res) {
            $this->returnMessage['code'] = 'success';
            $this->returnMessage['message'] = '成功';
        } else {
            $this->returnMessage['code'] = 'error';
            $this->returnMessage['message'] = '失败';
        }
        return json($this->returnMessage);
    }

    // 班群讨论发布
    function discussPost() {
        $article_id = $this->request->has('article_id') ? $this->request->param('article_id') : '';
        $video_id = $this->request->has('video_id') ? $this->request->param('video_id') : '';
        $openid = $this->request->has('openid') ? $this->request->param('openid') : '';
        $content = $this->request->has('content') ? $this->request->param('content') : '';
        $pic_url = $this->request->has('pic_url') ? $this->request->param('pic_url') : '';
        $is_mp3 = $this->request->has('is_mp3') ? $this->request->param('is_mp3') : '';
        $is_mp4 = $this->request->has('is_mp4') ? $this->request->param('is_mp4') : '';


        if (! $content) {
            $this->returnMessage['code'] = 'error';
            $this->returnMessage['message'] = '内容不能为空';
            return json($this->returnMessage);
            die();
        }
        if (! $openid) {
            $this->returnMessage['code'] = 'error';
            $this->returnMessage['message'] = '没有获取到openid';
            return json($this->returnMessage);
            die();
        }
        if (! $article_id) {
            $this->returnMessage['code'] = 'error';
            $this->returnMessage['message'] = '没有获取到article_id';
            return json($this->returnMessage);
            die();
        }
        if (! $video_id) {
            $this->returnMessage['code'] = 'error';
            $this->returnMessage['message'] = '没有获取到video_id';
            return json($this->returnMessage);
            die();
        }

        $mid = Db::name('member')->where('openid', $openid)->value('id');
        $time = date('m-d', time());
        $data = [
            'article_id' => $article_id,
            'mid' => $mid,
            'create_time' => $time,
            'content' => $content,
            'src' => $pic_url,
            'video_id' => $video_id,
            'is_mp3' => $is_mp3,
            'is_mp4' => $is_mp4,

        ];
        $res = Db::name('comment')->insertGetId($data);
        if ($res) {
            $this->returnMessage['code'] = 'success';
            $this->returnMessage['message'] = '成功';
        } else {
            $this->returnMessage['code'] = 'error';
            $this->returnMessage['message'] = '失败';
        }
        return json($this->returnMessage);
    }

    // 获取用户名称信息
    function getNameInfo() {
        $nickname = $this->request->param('nickname') ? $this->request->param('nickname') : '';

        $openid = $this->request->param('openid') ? $this->request->param('openid') : '';

        if ($nickname && $openid) {
            $res = Db::name('member')->where('openid', $openid)->update([
                'nickname' => $nickname
            ]);
            if ($res) {
                $this->retrunMessage['code'] = 'success';
                $this->returnMessage['message'] = '成功';
            } else {
                $this->returnMessage['code'] = 'error';
                $this->returnMessage['message'] = '失败';
            }
        } else {
            $this->returnMessage['code'] = 'error';
            $this->returnMessage['message'] = '缺少参数';
        }
        return json($this->returnMessage);
    }

    // 获取用户头像信息
    function getPicInfo() {
        $thumb_src = $this->request->param('thumb_src') ? $this->request->param('thumb_src') : '';
        $openid = $this->request->param('openid') ? $this->request->param('openid') : '';

        if ($thumb_src && $openid) {
            $res = Db::name('member')->where('openid', $openid)->update([
                'thumb_src' => $thumb_src
            ]);
            if ($res) {
                $this->retrunMessage['code'] = 'success';
                $this->returnMessage['message'] = '成功';
            } else {
                $this->returnMessage['code'] = 'error';
                $this->returnMessage['message'] = '失败';
            }
        } else {
            $this->returnMessage['code'] = 'error';
            $this->returnMessage['message'] = '缺少参数';
        }
        return json($this->returnMessage);
    }

    // 返回用户头像
    function pushPicInfo() {
        $openid = $this->request->param('openid') ? $this->request->param('openid') : '';
        if (! $openid) {
            $this->returnMessage['code'] = 'error';
            $this->returnMessage['message'] = '没有获取到orderid';
            return json($this->returnMessage);
            die();
        }
        $src = Db::name('member')->where('openid', $openid)->value('thumb_src');
        if ($src) {
            $this->retrunMessage['code'] = 'success';
            $this->returnMessage['message'] = '成功';
            $this->returnMessage['data'] = $src;
        } else {
            $this->returnMessage['code'] = 'error';
            $this->returnMessage['message'] = '没有获取到头像信息';
        }
        return json($this->returnMessage);
    }

    // 返回用户昵称
    function pushNameInfo() {
        $openid = $this->request->param('openid') ? $this->request->param('openid') : '';
        if (! $openid) {
            $this->returnMessage['code'] = 'error';
            $this->returnMessage['message'] = '没有获取到orderid';
            return json($this->returnMessage);
            die();
        }
        $name = Db::name('member')->where('openid', $openid)->value('nickname');
        if ($name) {
            $this->retrunMessage['code'] = 'success';
            $this->returnMessage['message'] = '成功';
            $this->returnMessage['data'] = $name;
        } else {
            $this->returnMessage['code'] = 'error';
            $this->returnMessage['message'] = '没有获取到昵称信息';
        }
        return json($this->returnMessage);
    }

    // 物流详情
    function logistics() {
        $orderid = $this->request->param('orderid') ? $this->request->param('orderid') : '';
        if (! $orderid) {
            $this->returnMessage['code'] = 'error';
            $this->returnMessage['message'] = '没有获取到orderid';
            return json($this->returnMessage);
            die();
        }
        $allowFields = [
            'mo.order_id',
            'ma.name',
            'ma.tel',
            'ma.province',
            'ma.city',
            'ma.county',
            'ma.address',
            'mo.type',
            'mo.danhao'
        ];
        $res = Db::name('member_order')->alias('mo')
            ->join('member_address ma', 'mo.addid=ma.id')
            ->field($allowFields)
            ->where('mo.id', $orderid)
            ->find();
        if ($res['type']) {
            // 查询物流详情

            $host = "https://ali-deliver.showapi.com";
            $path = "/showapi_expInfo";
            $method = "GET";
            $appcode = "f5f24bd31d084759b29b378b76f4021e";
            $headers = array();
            array_push($headers, "Authorization:APPCODE " . $appcode);
            $querys = "com=auto&nu=" . $res['danhao'];
            $bodys = "";
            $url = $host . $path . "?" . $querys;

            $curl = curl_init();
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($curl, CURLOPT_FAILONERROR, false);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_HEADER, false);
            if (1 == strpos("$" . $host, "https://")) {
                curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
            }
            // var_dump(curl_exec($curl));die;
            // 处理返回的json数据
            $arr = json_decode(stripslashes(curl_exec($curl)), true);
            $w = $arr['showapi_res_body']['data'];
            if (empty($w)) {
                $w = '暂无快递信息';
            }

            $this->retrunMessage['code'] = 'success';
            $this->returnMessage['message'] = '成功';
            $this->returnMessage['data'] = $res;
            $this->returnMessage['wuliu'] = $w;
        } else {
            $this->returnMessage['code'] = 'error';
            $this->returnMessage['message'] = '没有获取到物流信息';
        }
        return json($this->returnMessage);
    }

    // 物流
    function wuliu($wuliuid) {
        $host = "https://ali-deliver.showapi.com";
        $path = "/showapi_expInfo";
        $method = "GET";
        $appcode = "f5f24bd31d084759b29b378b76f4021e";
        $headers = array();
        array_push($headers, "Authorization:APPCODE " . $appcode);
        $querys = "com=auto&nu=" . $wuliuid;
        $bodys = "";
        $url = $host . $path . "?" . $querys;

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_FAILONERROR, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, true);
        if (1 == strpos("$" . $host, "https://")) {
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        }
        var_dump(curl_exec($curl));
        // return curl_exec($curl);
    }

    // 删除地址
    function deleteAdd() {
        // 操作
        $addressid = $this->request->param('addressid') ? $this->request->param('addressid') : '';
        if (! $addressid) {
            $this->returnMessage['code'] = 'error';
            $this->returnMessage['message'] = '没有获取到addressid';
            return json($this->returnMessage);
            die();
        }
        $addinfo = Db::name('member_address')->where('id', $addressid)->find();
        $res = Db::name('member_address')->where('id', $addressid)->delete();
        if ($addinfo['is_default'] == 1) {
            // 删除的是默认地址 将id最小的设置为默认
            $next = Db::name('member_address')->where('mid', $addinfo['mid'])
                ->order('create_time')
                ->limit(1)
                ->find();
            if ($next) {
                Db::name('member_address')->where('id', $next['id'])->update([
                    'is_default' => 1
                ]);
            }
        }
        if ($res !== false) {
            $this->returnMessage['code'] = 'success';
            $this->returnMessage['message'] = '删除成功';
        } else {
            $this->returnMessage['code'] = 'error';
            $this->returnMessage['message'] = '删除失败';
        }
        return json($this->returnMessage);
    }

    // 编辑地址提交
    function editAddPost() {
        $openid = $this->request->param('openid') ? $this->request->param('openid') : '';
        $mid = Db::name('member')->where('openid', $openid)->value('id');
        if (! $openid) {
            $this->returnMessage['code'] = 'error';
            $this->returnMessage['message'] = '没有获取到openid';
            return json($this->returnMessage);
            die();
        }
        if (! $mid) {
            $this->returnMessage['code'] = 'error';
            $this->returnMessage['message'] = '没有找到该用户信息';
            return json($this->returnMessage);
            die();
        }
        // 修改操作
        $addressid = $this->request->param('addressid') ? $this->request->param('addressid') : '';
        if (! $addressid) {
            $this->returnMessage['code'] = 'error';
            $this->returnMessage['message'] = '没有获取到addressid';
            return json($this->returnMessage);
            die();
        }
        $addinfo = Db::name('member_address')->where('id', $addressid)->find();
        $name = $this->request->param('name') ? $this->request->param('name') : $addinfo['name'];
        $tel = $this->request->param('tel') ? $this->request->param('tel') : $addinfo['tel'];
        if (! preg_match('/^1([0-9]{9})/', $tel)) {
            $this->returnMessage['code'] = 'error';
            $this->returnMessage['message'] = '电话号码错误';
            return json($this->returnMessage);
            die();
        }

        $province = $this->request->param('province') ? $this->request->param('province') : $addinfo['province'];
        $city = $this->request->param('city') ? $this->request->param('city') : $addinfo['city'];
        $county = $this->request->param('county') ? $this->request->param('county') : $addinfo['county'];
        $address = $this->request->param('address') ? $this->request->param('address') : $addinfo['address'];
        $is_default = $this->request->param('is_default') ? $this->request->param('is_default') : $addinfo['is_default'];
        if ($is_default == 1) {
            // 修改默认地址
            $defaultadd = Db::name('member_address')->where('mid', $mid)
                ->where('is_default', 1)
                ->find();

            Db::name('member_address')->where('id', $defaultadd['id'])->update([
                'is_default' => 2
            ]);
        }
        $editvalue = [
            'name' => $name,
            'tel' => $tel,
            'province' => $province,
            'city' => $city,
            'county' => $county,
            'address' => $address,
            'is_default' => $is_default
        ];
        $edit_result = Db::name('member_address')->where('id', $addressid)->update($editvalue);
        if ($edit_result !== false) {
            $this->returnMessage['code'] = 'success';
            $this->returnMessage['message'] = '修改成功';
        }
        return json($this->returnMessage);
    }

    // 编辑地址 页面显示
    function editAdd() {
        // 显示页面
        $addressid = $this->request->param('addressid') ? $this->request->param('addressid') : '';
        if (! $addressid) {
            $this->returnMessage['code'] = 'error';
            $this->returnMessage['message'] = '没有获取到addressid';
            return json($this->returnMessage);
            die();
        }
        $allowFields = [
            'name',
            'tel',
            'province',
            'city',
            'county',
            'address',
            'is_default'
        ];
        $addressInfo = Db::name('member_address')->field($allowFields)
            ->where('id', $addressid)
            ->select();
        if ($addressInfo) {
            $this->returnMessage['code'] = 'success';
            $this->returnMessage['message'] = '成功';
            $this->returnMessage['data'] = $addressInfo;
        }

        return json($this->returnMessage);
    }

    // 选择收货地址
    function addressList() {
        $addressid = $this->request->param('addressid') ? $this->request->param('addressid') : ''; // 当前的地址id
        $openid = $this->request->param('openid') ? $this->request->param('openid') : '';
        $mid = Db::name('member')->where('openid', $openid)->value('id');
        if (! $openid) {
            $this->returnMessage['code'] = 'error';
            $this->returnMessage['message'] = '没有获取到openid';
            return json($this->returnMessage);
            die();
        }

        $addressList = Db::name('member_address')->where('mid', $mid)
            ->order('is_default')
            ->select();
        foreach ($addressList as $k => $v) {
            if ($addressid == $v['id']) {
                $addressList[$k]['is_used'] = 1;
            } else {
                $addressList[$k]['is_used'] = 0;
            }
        }
        if ($addressList) {
            $this->returnMessage['code'] = 'success';
            $this->returnMessage['message'] = '成功';
            $this->returnMessage['data'] = $addressList; // 收货地址信息列表
        } else {
            $this->returnMessage['code'] = 'success';
            $this->returnMessage['message'] = '暂无地址';
        }
        return json($this->returnMessage);
    }

    // 添加收货地址
    function addAddress() {
        /*
         * $id = $this->request->param('id') ? $this->request->param('id') : '';
         *
         * if(!$id){
         * $this->returnMessage['code'] ='error';
         * $this->returnMessage['message'] ='没有获取到课程id';
         * return json($this->returnMessage);die;
         * }
         */
        $openid = $this->request->param('openid') ? $this->request->param('openid') : '';
        if (! $openid) {
            $this->returnMessage['code'] = 'error';
            $this->returnMessage['message'] = '没有获取到openid';
            return json($this->returnMessage);
            die();
        }
        $mid = Db::name('member')->where('openid', $openid)->value('id');
        if (! $mid) {
            $this->returnMessage['code'] = 'error';
            $this->returnMessage['message'] = '没有找到该用户信息';
            return json($this->returnMessage);
            die();
        }
        $name = $this->request->param('name') ? $this->request->param('name') : '';
        if (! $name) {
            $this->returnMessage['code'] = 'error';
            $this->returnMessage['message'] = '没有获取到name';
            return json($this->returnMessage);
            die();
        }

        $province = $this->request->param('province') ? $this->request->param('province') : '';
        if (! $province) {
            $this->returnMessage['code'] = 'error';
            $this->returnMessage['message'] = '没有获取到省信息';
            return json($this->returnMessage);
            die();
        }
        $city = $this->request->param('city') ? $this->request->param('city') : '';
        if (! $city) {
            $this->returnMessage['code'] = 'error';
            $this->returnMessage['message'] = '没有获取到市信息';
            return json($this->returnMessage);
            die();
        }
        $county = $this->request->param('county') ? $this->request->param('county') : '';
        if (! $county) {
            $this->returnMessage['code'] = 'error';
            $this->returnMessage['message'] = '没有获取到县信息';
            return json($this->returnMessage);
            die();
        }
        $tel = $this->request->param('tel') ? $this->request->param('tel') : '';
        if (! preg_match('/^1([0-9]{9})/', $tel)) {
            $this->returnMessage['code'] = 'error';
            $this->returnMessage['message'] = '电话号码错误';
            return json($this->returnMessage);
            die();
        }

        // $add = $this->request->param('add') ? $this->request->param('add') : '';
        $address = $this->request->param('address') ? $this->request->param('address') : '';
        if (! $address) {
            $this->returnMessage['code'] = 'error';
            $this->returnMessage['message'] = '没有获取到详细信息';
            return json($this->returnMessage);
            die();
        }
        $is_default = $this->request->param('is_default') ? $this->request->param('is_default') : '';
        /*
         * $fp=fopen("1.txt","a+");//fopen()的其它开关请参看相关函数
         * //$str="我加我加我加加加";
         * fwrite($fp,var_export($jdatb,true));
         * fclose($fp);
         */
        if ($is_default == 1 && $mid) {
            // 修改该用户的所有地址改成非默认地址
            Db::name('member_address')->where('mid', $mid)->update([
                'is_default' => 2
            ]);
        }
        $a = array(
            'openid' => $openid,
            'mid' => $mid,
            'name' => $name,
            'province' => $province,
            'city' => $city,
            'county' => $county,
            'tel' => $tel,
            'address' => $address,
            'is_default' => $is_default
        );
        $fp = fopen("3.txt", "a+"); // fopen()的其它开关请参看相关函数
        fwrite($fp, var_export($a, true));
        fclose($fp);
        $addinfom = Db::name('member_address')->where('mid', $mid)
            ->where('is_default', 1)
            ->find();
        if (! $addinfom) {
            // 如果没有默认地址，那这一条就是默认地址。
            $is_default = 1;
        }
        $addvalue = [
            'name' => $name,
            'tel' => $tel,
            'address' => $address,
            'province' => $province,
            'city' => $city,
            'county' => $county,
            'is_default' => $is_default,
            'create_time' => time(),
            'mid' => $mid
        ];
        $result = Db::name('member_address')->insertGetId($addvalue);

        if ($result) {
            $this->returnMessage['code'] = 'success';
            $this->returnMessage['message'] = '成功';
            $this->returnMessage['insert_result'] = '地址添加成功';
            // $this->returnMessage['data']=$articleinfo;
        } else {
            $this->returnMessage['code'] = 'error';
            $this->returnMessage['message'] = '添加失败';
            return json($this->returnMessage);
        }

        $allowFields = [
            'goods_name',
            'title'
        ];
        // 显示商品名称
        /*
         * $articleinfo = Db::name('article')->field($allowFields)->where('id',$id)->select();
         * if($articleinfo){
         * $this->returnMessage['code']='success';
         * $this->returnMessage['message']='成功';
         * $this->returnMessage['data']=$articleinfo;
         * }
         */
        return json($this->returnMessage);
    }

    // 显示地址信息
    function showaddress() {
        $openid = $this->request->param('openid') ? $this->request->param('openid') : '';
        $mid = Db::name('member')->where('openid', $openid)->value('id');
        $memberinfo = Db::name('member')->where('openid', $openid)->find();
        if (! $openid) {
            $this->returnMessage['code'] = 'error';
            $this->returnMessage['message'] = '没有获取到openid';
            return json($this->returnMessage);
            die();
        }
        $addid = $this->request->param('addid') ? $this->request->param('addid') : ''; // 所选地址ID
        /*
         * if(!$addid){
         * $this->returnMessage['code'] ='error';
         * $this->returnMessage['message'] ='没有获取到地址ID';
         * return json($this->returnMessage);die;
         * }
         */
        $id = $this->request->param('id') ? $this->request->param('id') : '';
        if (! $id) {
            $this->returnMessage['code'] = 'error';
            $this->returnMessage['message'] = '没有获取到课程id';
            return json($this->returnMessage);
            die();
        }
        if ($addid) {
            $addinfo = Db::name('member_address')->where('mid', $mid)
                ->where('id', $addid)
                ->find();
            if (! $addinfo) {
                $allowFields = [
                    'goods_name',
                    'title'
                ];
                // 显示商品名称
                $articleinfo = Db::name('article')->field($allowFields)
                    ->where('id', $id)
                    ->select();
                if ($articleinfo) {
                    //判断是否绑定手机号
                    if($memberinfo['mobile']){
                        $this->returnMessage['is_mobile'] = 1;
                    }else{
                        $this->returnMessage['is_mobile'] = 0;
                    }
                    $this->returnMessage['code'] = 'success';
                    $this->returnMessage['message'] = '成功';
                    $this->returnMessage['data'] = $articleinfo;
                }
                // $this->returnMessage['addinfo']='该用户没有该地址信息';
            } else {
                $allowFields = [
                    'goods_name',
                    'title'
                ];
                // 显示商品名称
                $articleinfo = Db::name('article')->field($allowFields)
                    ->where('id', $id)
                    ->select();
                if ($articleinfo) {
                    //判断是否绑定手机号
                    if($memberinfo['mobile']){
                        $this->returnMessage['is_mobile'] = 1;
                    }else{
                        $this->returnMessage['is_mobile'] = 0;
                    }
                    $this->returnMessage['code'] = 'success';
                    $this->returnMessage['message'] = '成功';
                    $this->returnMessage['data'] = $articleinfo;
                }
                $this->returnMessage['addinfo'] = $addinfo;
            }
        } else {
            $addinfo = Db::name('member_address')->where('mid', $mid)
                ->where('is_default', 1)
                ->find();
            if ($addinfo) {
                $this->returnMessage['addinfo'] = $addinfo;
                $allowFields = [
                    'goods_name',
                    'title'
                ];
                // 显示商品名称
                $articleinfo = Db::name('article')->field($allowFields)
                    ->where('id', $id)
                    ->select();
                if ($articleinfo) {
                    //判断是否绑定手机号
                    if($memberinfo['mobile']){
                        $this->returnMessage['is_mobile'] = 1;
                    }else{
                        $this->returnMessage['is_mobile'] = 0;
                    }
                    $this->returnMessage['code'] = 'success';
                    $this->returnMessage['message'] = '成功';
                    $this->returnMessage['data'] = $articleinfo;
                }
            } else {
                $allowFields = [
                    'goods_name',
                    'title'
                ];
                // 显示商品名称
                $articleinfo = Db::name('article')->field($allowFields)
                    ->where('id', $id)
                    ->select();
                if ($articleinfo) {
                    //判断是否绑定手机号
                    if($memberinfo['mobile']){
                        $this->returnMessage['is_mobile'] = 1;
                    }else{
                        $this->returnMessage['is_mobile'] = 0;
                    }
                    $this->returnMessage['code'] = 'success';
                    $this->returnMessage['message'] = '没有默认地址信息，请添加默认地址。';
                    $this->returnMessage['data'] = $articleinfo;
                }
            }
        }
        return json($this->returnMessage);
    }

    // 点击购买-显示添加地址表单 显示商品相关信息
    function addressForm() {
        $openid = $this->request->param('openid') ? $this->request->param('openid') : '';

        $mid = Db::name('member')->where('openid', $openid)->value('id');
        if (! $openid) {
            $this->returnMessage['code'] = 'error';
            $this->returnMessage['message'] = '没有获取到openid';
            return json($this->returnMessage);
            die();
        }
        $id = $this->request->param('id') ? $this->request->param('id') : '';
        if (! $id) {
            $this->returnMessage['code'] = 'error';
            $this->returnMessage['message'] = '没有获取到课程id';
            return json($this->returnMessage);
            die();
        }
        // 获取该用户的默认地址
        $allowFields2 = [
            'id',
            'name',
            'tel',
            'province',
            'city',
            'county',
            'address',
            'is_default'
        ];
        /*
         * $default = Db::name('member_address')->field($allowFields2)->where('mid',$mid)->where('is_default',1)->find();
         * if(!$default){
         * //显示默认地址
         * $this->returnMessage['code'] ='error';
         * $this->returnMessage['message'] ='没有默认地址,显示添加表单。';
         * return json($this->returnMessage);
         * }
         */
        $allowFields = [
            'goods_name',
            'title',
            'is_shi'
        ];
        // 显示商品名称
        $articleinfo = Db::name('article')->field($allowFields)
            ->where('id', $id)
            ->select();
        if ($articleinfo) {
            $this->returnMessage['code'] = 'success';
            $this->returnMessage['message'] = '成功';
            $this->returnMessage['data'] = $articleinfo;
            // $this->returnMessage['default']=$default;
        }

        return json($this->returnMessage);
    }

    // 我的-已购买的
    function Purchased() {
        $openid = $this->request->param('openid') ? $this->request->param('openid') : '';
        /*
         * $fp=fopen("4.txt","a+");//fopen()的其它开关请参看相关函数
         *
         * fwrite($fp,var_export($openid,true));
         * fclose($fp);
         */
        $page = $this->request->has('page') ? $this->request->param('page', 0, 'intval') : 1;
        if (! $openid) {
            $this->returnMessage['code'] = 'error';
            $this->returnMessage['message'] = '未登录';
            return json($this->returnMessage);
        } else {
            $member_info = Db::name('member')->where('openid', $openid)->find();
            /*
             * $allowFields = [
             * 'mo.article_id',
             * 'a.title',
             * 'a.thumb_src',
             * 'mo.create_time',
             * ];
             */
            $member_article = Db::name('member_order')->alias('mo')
                ->join('article a', 'a.id=mo.article_id')
                ->field('a.id as article_id,a.thumb_src,a.title,a.tag,mo.id,mo.order_id,mo.state,mo.pay_time')
                ->where('mo.mid', $member_info['id'])
                ->where('mo.state', 1)
                ->order('mo.create_time desc')
                ->limit(15)
                /*->group('a.id')去重*/
                ->page($page)
                ->select();

            if (! $member_article) {
                $this->returnMessage['code'] = 'error';
                $this->returnMessage['message'] = '没有购买任何产品';
            } else {
                $this->returnMessage['code'] = 'success';
                $this->returnMessage['message'] = '成功';
                $this->returnMessage['data'] = $member_article;
            }
        }
        return json($this->returnMessage);
    }

    // 我的订单
    function myOrder() {
        // $openid = Session::get('openid');
        $openid = $this->request->param('openid') ? $this->request->param('openid') : '';
        // $openid = 1;//测试用
        $page = $this->request->has('page') ? $this->request->param('page', 0, 'intval') : 1;
        if (! $openid) {
            $this->returnMessage['code'] = 'error';
            $this->returnMessage['message'] = '未登录';
        } else {
            $member_info = Db::name('member')->where('openid', $openid)->find();
            $order_list = Db::name('member_order')->alias('mo')
                ->join('article a', 'mo.article_id=a.id')
                ->field('a.thumb_src,a.title,a.tag,mo.id,mo.order_id,mo.state,mo.pay_time')
                ->where('mo.mid', $member_info['id'])
                ->where('mo.state', 1)
                ->limit(15)
                ->page($page)
                ->select();
            if (! $order_list) {
                $this->returnMessage['code'] = 'error';
                $this->returnMessage['message'] = '没有任何订单';
            } else {
                $this->returnMessage['code'] = 'success';
                $this->returnMessage['message'] = '成功';
                $this->returnMessage['data'] = $order_list;
            }
        }
        return json($this->returnMessage);
    }

    // 开通会员
    function openMember() {
        $mid = session('member')['id'];
        if (! $mid) {
            $this->returnMessage['code'] = 'error';
            $this->returnMessage['message'] = '未登录';
        } else {
            if (! Request::instance()->isPost()) {
                $this->returnMessage['code'] = 'error';
                $this->returnMessage['message'] = '请使用POST方式请求';
            } else {
                $create_time = time();
                $notes = '开通会员';
                $tolevel = $this->request->has('tolevel') ? $this->request->param('tolevel') : 1;
                $member_level = Db::name('member_level')->where('level', $tolevel)->find();
                $money = $member_level['need_money'];
                $member_order = Db::name('member_level_order')->insert([
                    'mid' => $mid,
                    'tolevel' => $tolevel,
                    'create_time' => $create_time,
                    'money' => $money,
                    'notes' => $notes
                ]);
                $member = Db::name('member_level_order')->max('id');
                if (! $member_order < 0) {
                    $this->returnMessage['code'] = 'error';
                    $this->returnMessage['message'] = '创建订单失败';
                } else {
                    $this->returnMessage['code'] = 'success';
                    $this->returnMessage['message'] = '创建成功，转到支付';
                    $this->returnMessage['data'] = [
                        'oid' => $member
                    ];
                }
            }
        }
        return json($this->returnMessage);
    }

    // 续费会员
    function reviewMember() {
        $mid = session('member')['id'];
        if (! $mid) {
            $this->returnMessage['code'] = 'error';
            $this->returnMessage['message'] = '未登录';
        } else {
            if (! Request::instance()->isPost()) {
                $this->returnMessage['code'] = 'error';
                $this->returnMessage['message'] = '请使用POST方式请求';
            } else {
                $level = session('member')['level'];
                $create_time = time();
                $notes = '续费会员';
                $money = 10;
                $member_order = Db::name('member_level_order')->insert([
                    'mid' => $mid,
                    'create_time' => $create_time,
                    'money' => $money,
                    'notes' => $notes,
                    'tolevel' => $level
                ]);
                $member = Db::name('member_level_order')->max('id');
                if (! $member_order < 0) {
                    $this->returnMessage['code'] = 'error';
                    $this->returnMessage['message'] = '创建订单失败';
                } else {
                    $this->returnMessage['code'] = 'success';
                    $this->returnMessage['message'] = '创建成功，转到支付';
                    $this->returnMessage['data'] = [
                        'oid' => $member
                    ];
                }
            }
        }
        return json($this->returnMessage);
    }

    // 余额充值
    function recharge() {
        $mid = session('member')['id'];
        if (! $mid) {
            $this->returnMessage['code'] = 'error';
            $this->returnMessage['message'] = '未登录';
        } else {
            if (! Request::instance()->isPost()) {
                $this->returnMessage['code'] = 'error';
                $this->returnMessage['message'] = '请使用POST方式请求';
            } else {
                $create_time = time();
                $balance = $this->request->has('balance') ? $this->request->param('balance') : 0;
                $notes = '余额充值' . '+' . $balance;
                if ($balance < 1) {
                    $this->returnMessage['code'] = 'error';
                    $this->returnMessage['message'] = '错误，充值金额不得小于1￥';
                }
                $member_order = Db::name('member_balance_order')->insert([
                    'mid' => $mid,
                    'balance' => $balance,
                    'create_time' => $create_time,
                    'notes' => $notes
                ]);
                $member = Db::name('member_balance_order')->max('id');
                if (! $member_order < 0) {
                    $this->returnMessage['code'] = 'error';
                    $this->returnMessage['message'] = '创建订单失败';
                } else {
                    $this->returnMessage['code'] = 'success';
                    $this->returnMessage['message'] = '创建成功，转到支付';
                    $this->returnMessage['data'] = [
                        'oid' => $member
                    ];
                }
            }
        }
        return json($this->returnMessage);
    }

    // 我的文章
    function myArticle() {
        $uid = session('member')['id'];
        $id = $this->request->param('id');
        $page = $this->request->has('page') ? $this->request->param('page', 0, 'intval') : 1;
        if ($id) {
            $member_article = Db::name('member_article')->where([
                'user_id' => $id,
                'isDelet' => 0
            ])
                ->order('id desc')
                ->limit('10')
                ->page($page)
                ->select();
            $this->returnMessage['code'] = 'success';
            $this->returnMessage['message'] = '获取成功';
            $this->returnMessage['data'] = $member_article;
            return json($this->returnMessage);
            die();
        }
        $article = Db::name('member_article')->where([
            'user_id' => $uid,
            'isDelet' => 0
        ])
            ->order('id desc')
            ->limit('10')
            ->page($page)
            ->select();
        $this->returnMessage['code'] = 'success';
        $this->returnMessage['message'] = '获取成功';
        $this->returnMessage['data'] = $article;

        return json($this->returnMessage);
    }

    // 我的文章内容
    function myArticlePage() {
        $uid = session('member')['id'];
        if (! $uid) {
            $this->returnMessage['code'] = 'error';
            $this->returnMessage['message'] = '未登录';
        } else {
            $article_id = $this->request->param('id');
            $article = Db::name('member_article')->where([
                'id' => $article_id,
                'user_id' => $uid
            ])->find();
            if (! $article) {
                $this->returnMessage['code'] = 'error';
                $this->returnMessage['message'] = '错误，未查找到文章信息';
            } else {
                $this->returnMessage['code'] = 'success';
                $this->returnMessage['message'] = '查询成功';
                $this->returnMessage['data'] = $article;
            }
        }
        return json($this->returnMessage);
    }

    // 删除文章
    function deleteArticle() {
        $uid = session('member')['id'];
        $article_id = $this->request->param('article_id');
        if (! $article_id) {
            $this->returnMessage['code'] = 'error';
            $this->returnMessage['message'] = '文章不存在';
        } else {
            $delete_article = Db::name('member_article')->where([
                'article_id' => $article_id,
                'user_id' => $uid
            ])->update([
                'isDelet' => 1
            ]);
            if ($delete_article < 1) {
                $this->returnMessage['code'] = 'error';
                $this->returnMessage['message'] = '删除失败，文章已经删除或不存在';
            } else {
                $this->returnMessage['code'] = 'success';
                $this->returnMessage['message'] = '删除成功';
            }
        }
        return json($this->returnMessage);
    }

    // 账户明细
    function accountDetails() {
        $id = session('member')['id'];
        $page = $this->request->has('page') ? $this->request->param('page', 0, 'intval') : 1;
        if (! $id) {
            $this->returnMessage['code'] = 'error';
            $this->returnMessage['message'] = '未登录';
        } else {
            $order = Db::name('member_order')->where('mid', $id)
                ->order('id desc')
                ->limit('10')
                ->page($page)
                ->select();
            $this->returnMessage['code'] = 'success';
            $this->returnMessage['message'] = '查询成功';
            $this->returnMessage['data'] = $order;
        }
        return json($this->returnMessage);
    }

    // 会员等级信息
    function memberLevelInfo() {
        $info = Db::name('member_level')->select();
        if (! $info) {
            $this->returnMessage['code'] = 'error';
            $this->returnMessage['message'] = '获取数据出错，请重试';
        } else {
            $this->returnMessage['code'] = 'success';
            $this->returnMessage['message'] = '获取成功';
            $this->returnMessage['data'] = $info;
        }
        return json($this->returnMessage);
    }
    //优惠券信息
    function couponList(){
        $allowfield=[
            'co.id',
            'co.name',
            'co.usetime',
            'co.usearticle',
            'co.sub_money',
            'm.title',
//            'max_money',
        ];
        $couponList=Db::name('coupon co')
            ->join('article m','co.usearticle=m.id')
            ->field($allowfield)
            ->order('co.id desc')
            ->where('co.is_show',1)
            ->select();
        $this->returnMessage['code']='success';
        $this->returnMessage['message']='获取成功';
        $this->returnMessage['data']=$couponList;
        return json($this->returnMessage);
    }
    //领取优惠券
    function addCoupon()
    {
        $openid = $this->request->param('openid') ? $this->request->param('openid') : '';
        if (!$openid) {
            $this->returnMessage['code'] = 'error';
            $this->returnMessage['message'] = '没有获取到openid';
            return json($this->returnMessage);
            die();
        }
        $mid = Db::name('member')->where('openid', $openid)->value('id');
        if (!$mid) {
            $this->returnMessage['code'] = 'error';
            $this->returnMessage['message'] = '没有找到该用户信息';
            return json($this->returnMessage);
            die();
        }
        $cid = $this->request->param('id') ? $this->request->param('id') : '';//优惠券ID
        if (!$cid) {
            $this->returnMessage['code'] = 'error';
            $this->returnMessage['message'] = '没有获取到优惠券信息';
            return json($this->returnMessage);
            die();
        }
        $usearticle = Db::name('coupon')->where('id', $cid)->value('usearticle');
        $data = [
            'openid' => $openid,
            'cid' => $cid,
            'usearticle' => $usearticle,
            'time' => time()
        ];
        $result = Db::name('couponuse')->insertGetId($data);
        if ($result) {
            $this->returnMessage['code'] = 'success';
            $this->returnMessage['message'] = '领取成功';
        } else {
            $this->returnMessage['code'] = 'error';
            $this->returnMessage['message'] = '领取失败';
            return json($this->returnMessage);
        }
        return json($this->returnMessage);
    }
    //个人中心我的拼团
    public function my_group(){
        $openid = $this->request->param('openid') ? $this->request->param('openid') : '';
        if (!$openid) {
            $this->returnMessage['code'] = 'error';
            $this->returnMessage['message'] = '没有获取到openid';
            return json($this->returnMessage);
            die();
        }
        $mid = Db::name('member')->where('openid', $openid)->value('id');
        if (!$mid) {
            $this->returnMessage['code'] = 'error';
            $this->returnMessage['message'] = '没有找到该用户信息';
            return json($this->returnMessage);
            die();
        }
        //查找我的拼团订单
        //我的拼团+article表联合查询
        $allowFields=[
            "a.id",
            "a.mid",
            "a.balance",
            "a.create_time",
            "a.pay_time",
            "a.state",
            "a.order_id",
            "a.article_id",
            "a.addid",
            "a.type",
            "a.message",
            "a.danhao",
            "a.is_pin",
            "a.is_num",
            "a.oid",
            "b.tag",
            "b.is_tag",
            "b.sub_price",
            "b.description",
            "b.thumb_src",
            "b.video_link2",
        ];
        $my_group=Db::name('member_order')->alias('a')
                ->join('article b','a.article_id=b.id')
                ->field($allowFields)
                ->order('a.id desc')
                ->where('a.mid',$mid)
                ->where('a.is_pin',1)
                ->select();
        if ($my_group) {
            $this->returnMessage['code'] = 'success';
            $this->returnMessage['data'] = $my_group;
            $this->returnMessage['message'] = '成功';
        } else {
            $this->returnMessage['code'] = 'error';
            $this->returnMessage['message'] = '失败';
        }
        return json($this->returnMessage);
    }
    //拼团成功
    public function other_group(){
        $openid = $this->request->param('openid') ? $this->request->param('openid') : '';
        if (!$openid) {
            $this->returnMessage['code'] = 'error';
            $this->returnMessage['message'] = '没有获取到openid';
            return json($this->returnMessage);
            die();
        }
        $mid = Db::name('member')->where('openid', $openid)->value('id');
        if (!$mid) {
            $this->returnMessage['code'] = 'error';
            $this->returnMessage['message'] = '没有找到该用户信息';
            return json($this->returnMessage);
            die();
        }
        //拼团成功
        $success_group=Db::name('member_order')->where('mid',$mid)->where('is_pin',1)->where('state',1)->select();
        foreach ($success_group as $k=>$v){
            $success_group[$k]['description']=Db::name('article')->where('id',$success_group[$k]['article_id'])->value('description');
            $success_group[$k]['thumb_src']=Db::name('article')->where('id',$success_group[$k]['article_id'])->value('thumb_src');
        }
        if ($success_group){
            $this->returnMessage['code'] = 'success';
            $this->returnMessage['success_group']=$success_group;
            $this->returnMessage['message'] = '成功';
        }else {
            $this->returnMessage['code'] = 'error';
            $this->returnMessage['message'] = '失败';
        }
        return json($this->returnMessage);
    }
    //代付款
    function wait_group(){
        $openid = $this->request->param('openid') ? $this->request->param('openid') : '';
        if (!$openid) {
            $this->returnMessage['code'] = 'error';
            $this->returnMessage['message'] = '没有获取到openid';
            return json($this->returnMessage);
            die();
        }
        $mid = Db::name('member')->where('openid', $openid)->value('id');
        if (!$mid) {
            $this->returnMessage['code'] = 'error';
            $this->returnMessage['message'] = '没有找到该用户信息';
            return json($this->returnMessage);
            die();
        }
        $wait_group=Db::name('member_order')->where('mid',$mid)->where('is_pin',1)->where('state',0)->select();
        foreach ($wait_group as $k=>$v){
            $wait_group[$k]['description']=Db::name('article')->where('id',$wait_group[$k]['article_id'])->value('description');
            $wait_group[$k]['thumb_src']=Db::name('article')->where('id',$wait_group[$k]['article_id'])->value('thumb_src');
        }
        if ($wait_group){
            $this->returnMessage['code'] = 'success';
            $this->returnMessage['wait_group'] = $wait_group;
            $this->returnMessage['message'] = '成功';
        }else {
            $this->returnMessage['code'] = 'error';
            $this->returnMessage['message'] = '失败';
        }
        return json($this->returnMessage);
    }
    //拼团中
    function ing_group(){
        $openid = $this->request->param('openid') ? $this->request->param('openid') : '';
        if (!$openid) {
            $this->returnMessage['code'] = 'error';
            $this->returnMessage['message'] = '没有获取到openid';
            return json($this->returnMessage);
            die();
        }
        $mid = Db::name('member')->where('openid', $openid)->value('id');
        if (!$mid) {
            $this->returnMessage['code'] = 'error';
            $this->returnMessage['message'] = '没有找到该用户信息';
            return json($this->returnMessage);
            die();
        }
        $ing_group=Db::name('member_order')->where('mid',$mid)->where('is_pin',1)->where('state',2)->select();
        foreach ($ing_group as $k=>$v){
            $ing_group[$k]['description']=Db::name('article')->where('id',$ing_group[$k]['article_id'])->value('description');
            $ing_group[$k]['thumb_src']=Db::name('article')->where('id',$ing_group[$k]['article_id'])->value('thumb_src');
        }
        if ($ing_group){
            $this->returnMessage['code'] = 'success';
            $this->returnMessage['ing_group'] = $ing_group;
            $this->returnMessage['message'] = '成功';
        }else {
            $this->returnMessage['code'] = 'error';
            $this->returnMessage['message'] = '失败';
        }
        return json($this->returnMessage);
    }
}