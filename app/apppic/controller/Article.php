<?php
namespace app\apppic\controller;

use think\Db;
use think\Request;
use think\File;
use think\Validate;
use think\Session;
use think\Page;

class Article extends Index
{
    //点击课程增加观看次数
    function click(){
        //视频id
        $id = $this->request->param('id') ? $this->request->param('id') : 0;
        if(!$id){
            $this->returnMessage['code'] = 'error';
            $this->returnMessage['message'] = '没有获取到视频id';
            return json($this->returnMessage);die;
        }
        $res = Db::name('video')->where('id',$id)->setInc('click');
        if($res!==false){
            $this->returnMessage['code'] = 'success';
            $this->returnMessage['message']= '观看次数已加一';
        }else{
            $this->returnMessage['code'] = 'error';
            $this->returnMessage['message'] = '失败';
        }
        return json($this->returnMessage);
    }
    //免费观看列表
    function free_list(){

        $list=Db::name('article')->select();

        $this->assign('list',$list);

        return $this->fetch();

    }
    function free_video_list(){
        $allowFields=[
            'id',
            'title',
            'description',
            'content',
            'thumb_src',
            'video_link2',
        ];
        $free_video_list=Db::name('article')->field($allowFields)->select();
        if ($free_video_list){
            $this->returnMessage['code'] = 'success';
            $this->returnMessage['message'] = '成功';
            $this->returnMessage['data'] = $free_video_list;
        }
        return json($this->returnMessage);
    }
    //指定免费课程点进去
    function free_video_list1(){
        $id = $this->request->param('id') ? $this->request->param('id') : 0;
        $allowFields=[
            'id',
            'title',
            'description',
            'content',
            'thumb_src',
            'video_link2',
        ];
        $free_video_list=Db::name('article')->where('id',$id)->field($allowFields)->find();
        if ($free_video_list){
            $this->returnMessage['code'] = 'success';
            $this->returnMessage['message'] = '成功';
            $this->returnMessage['data'] = $free_video_list;
        }
        return json($this->returnMessage);
    }
    //免费观看详情包含评论
    function free_video(){
        $id = $this->request->param('id') ? $this->request->param('id') : 0;
        $allowFields=[
            'a.id',
            'a.title',
            'a.description',
            'a.video_link2',
            'b.create_time',
            'b.content',
        ];
        $free_video=Db::name('article')->alias('a')
            ->join('comment b','a.id=b.article_id')
            ->field($allowFields)
            ->where('a.id',$id)
            ->select();
        if ($free_video) {
            $this->returnMessage['code'] = 'success';
            $this->returnMessage['message'] = '成功';
            $this->returnMessage['data'] = $free_video;
        }
        return json($this->returnMessage);
    }
    //小节详情
    function video_info(){
        $id = $this->request->param('id') ? $this->request->param('id') : 0;
        if(!$id){
            $this->returnMessage['code'] = 'error';
            $this->returnMessage['message'] = '没有获取到视频id';
            return json($this->returnMessage);die;
        }
        $allowFields=[
            'id',
            'title',
            'thumb_src',
            'play_url',
        ];
        $video_info=Db::name('video')->where('id',$id)->field($allowFields)->select();
        if ($video_info) {
            $this->returnMessage['code'] = 'success';
            $this->returnMessage['message'] = '成功';
            $this->returnMessage['data'] = $video_info;
        }
        return json($this->returnMessage);
    }

    //开课后的课程列表
    function article_list(){
        $article_id = $this->request->param('article_id') ? $this->request->param('article_id') : 0;
        if(!$article_id){
            $this->returnMessage['code'] = 'error';
            $this->returnMessage['message'] = '没有获取到课程id';
            return json($this->returnMessage);die;
        }
        $res = Db::name('article')->where('id',$article_id)->find();
        if($res['start_date']){
            $start_date=strtotime($res['start_date']);
            //判断是否开课
            if($start_date<time()){
                //开课
                $allowFields = [
                    'id',
                    'title',
                    'thumb_src2',
                    'play_url',
                    'article_id',
                    'sort',
                    'click',
                    'time_length',
                ];
                $video = Db::name('video')->field($allowFields)->where('article_id',$article_id)->order('sort')->select();
                foreach($video as $k => $v){
                    //获取每一节课的讨论数
                    $video[$k]['comment_num'] = Db::name('comment')->where('video_id', $v['id'])->where('is_show',1)->count();
                }
                if($video){
                    //获取该课程中所有的讨论数
                    $comment_total_num = Db::name('comment')->where('article_id', $article_id)->where('video_id > 0')->where('is_show',1)->count();


                    $this->returnMessage['code'] = 'success';
                    $this->returnMessage['message']= '成功';
                    $this->returnMessage['data'] = $video;
                    $this->returnMessage['comment_total_num'] = $comment_total_num;
                }else{
                    $this->returnMessage['code'] = 'error';
                    $this->returnMessage['message'] = '未找到课程视频内容';
                    return json($this->returnMessage);die;
                }
            }else{
                //未开课
                $this->returnMessage['code'] = 'error';
                $this->returnMessage['message'] = '该课程还未开课';
                return json($this->returnMessage);die;
            }
        }else{
            $this->returnMessage['code'] = 'error';
            $this->returnMessage['message'] = '没有获取到课程开课时间';
            return json($this->returnMessage);die;
        }
        return json($this->returnMessage);
    }

    //文章列表
    function articleList(){

        $list=Db::name('article')->select();

        $this->assign('list',$list);

        return $this->fetch();

    }
    //证书典礼详情图片
    function rite_dec(){
        $id = $this->request->param('id') ? $this->request->param('id') : '';
        //$note = $this->request->param('note') ? $this->request->param('note') : ''; //标识点击哪个图片。1.2.3

        if(!$id){
            $this->returnMessage['code']='error';
            $this->returnMessage['message']='参数错误，没有商品id' ;
            return json($this->returnMessage);die;
        }

        $allowFields = [
            'notice_dec',
            'opening_dec',
            'gratuation_dec',
        ];
        $rite = Db::name('member_rite')->field($allowFields)->where('article_id',$id)->find();
        if($rite){
            $this->returnMessage['code'] = 'success';
            $this->returnMessage['message']= '成功';
            $this->returnMessage['data'] = $rite;
        }else{
            $this->returnMessage['code']='error';
            $this->returnMessage['message']='没有上传该图片' ;
        }
        $this->returnMessage['data'] = $rite;
        return json($this->returnMessage);
    }
    //证书典礼 封面图 购买之后才有
    function rite(){
        /*$openid = $this->request->param('openid') ? $this->request->param('openid') : '';
        $memberinfo=Db::name('member')->where('openid',$openid)->find();*/
        //接收课程id
        $id = $this->request->param('id') ? $this->request->param('id') : '';
        if(!$id){
            $this->returnMessage['code']='error';
            $this->returnMessage['message']='参数错误，没有商品id' ;
            return json($this->returnMessage);die;
        }

        //已购买
        $allowFields = [
            'notice',
            'opening',
            'gratuation',

        ];
        $rite = Db::name('member_rite')->field($allowFields)->where('article_id',$id)->find();
        if($rite){
            $this->returnMessage['code'] = 'success';
            $this->returnMessage['message']= '成功';
            $this->returnMessage['data'] = $rite;
        }else{
            $this->returnMessage['code'] = 'error';
            $this->returnMessage['message']= '后台没有上传图片';
        }


        return json($this->returnMessage);
    }

    //        拼团
//   1：点击开团，获取商品名称，商品价格进行支付。
//   2:生成订单
//   3:加个字段>支付个数，支付成功后生成订单对该字段加1，同时这个订单展示在页面，开团人的头像，昵称，订单有效期剩余时间
//   4:点击去凑团，页面：商品缩略图，商品标题，商品原价，商品拼团价，差几人 拼团，剩余时间，已参团人物头像，昵称，创建订单的时间
//   5:点击我要参团：获取商品名称，商品价格进行支付，支付成功后支付个数加1
//   6：支付成功后进行判断，如果支付个数大于3，返回数据拼团成功，同时改变订单状态变为成功，代表用户成功获得该课程
//        开团
    function shop_group(){
        //接收商品id
        $id = $this->request->param('id') ? $this->request->param('id') : '';
        if(!$id){
            $this->returnMessage['code']='error';
            $this->returnMessage['message']='参数错误，没有课程id' ;
            return json($this->returnMessage);die;
        }
        $openid = $this->request->param('openid') ? $this->request->param('openid') : '';
        if(!$openid){
            $this->returnMessage['code']='error';
            $this->returnMessage['message']='没有获取到openid';
            return json($this->returnMessage);die;
        }
        $addid = $this->request->param('addid') ? $this->request->param('addid') : 0;
        //用户信息
        $memberinfo=Db::name('member')->where('openid',$openid)->find();
        //课程信息
        $articleinfo=Db::name('article')->where('id',$id)->find();
        $order_id=date('Ymdhis').rand(1000,9999);
        $data=array(
            'mid'=>$memberinfo['id'],
            'balance'=>$articleinfo['is_tag'],//拼团价格
            'create_time'=>time(),
            'end_time'=>time()+86400,
            'state'=>0,//时候付款 0未付
            'is_pin'=>1,//是拼团订单
            'order_id'=>$order_id,//订单号
            'article_id'=>$id,//课程ID
            'addid'=>$addid,//地址
        );
        $oid=Db::name('member_order')->insertGetId($data);
        if($oid){
            $this->returnMessage['code']='success';
            $this->returnMessage['message']='成功';
            $this->returnMessage['data']=$oid;
        }
        return json($this->returnMessage);
    }
    //点击参团去参团订单详情
    public function go_group(){
        $id = $this->request->param('id') ? $this->request->param('id') : '';
        if(!$id){
            $this->returnMessage['code']='error';
            $this->returnMessage['message']='参数错误，订单id' ;
            return json($this->returnMessage);die;
        }
        $openid = $this->request->param('openid') ? $this->request->param('openid') : '';
        if(!$openid){
            $this->returnMessage['code']='error';
            $this->returnMessage['message']='没有获取到openid';
            return json($this->returnMessage);die;
        }
//        判断订单几个人了，如果3个人则不能参团或者不显示  is_num=3
        $order_info=Db::name('member_order')->where('id',$id)->find();
//        if ($order_info['is_num']=3){
//            $this->returnMessage['code'] = 'error';
//            $this->returnMessage['message'] = '拼团已结束';
//            return json($this->returnMessage);
//            die();
//        }
        $child_order=Db::name('member_order')->where('oid',$order_info['id'])->select();
        foreach ($child_order as $k=>$v){
            $child_order[$k]['nickname']=Db::name('member')->where('id',$child_order[$k]['mid'])->value('nickname');
            $child_order[$k]['thumb_src']=Db::name('member')->where('id',$child_order[$k]['mid'])->value('thumb_src');
        }
        if ($order_info){
            $memberinfo=Db::name('member')->where('id',$order_info['mid'])->field('nickname,thumb_src')->find();
            $articleinfo=Db::name('article')->where('id',$order_info['article_id'])->field('thumb_src,title,tag,is_tag')->find();
            $this->returnMessage['code']='success';
            $this->returnMessage['order_info'] = $order_info;
            $this->returnMessage['memberinfo'] = $memberinfo;
            $this->returnMessage['articleinfo'] = $articleinfo;
            $this->returnMessage['child_order'] = $child_order;
            $this->returnMessage['message'] = '成功';
        }else{
            $this->returnMessage['code'] = 'error';
            $this->returnMessage['message'] = '信息错误·';
        }
        return json($this->returnMessage);
    }
    //我要参团
    function add_group(){
        //获取订单id
        $id = $this->request->param('id') ? $this->request->param('id') : '';
        if(!$id){
            $this->returnMessage['code']='error';
            $this->returnMessage['message']='参数错误，订单id' ;
            return json($this->returnMessage);die;
        }
        $openid = $this->request->param('openid') ? $this->request->param('openid') : '';
        if(!$openid){
            $this->returnMessage['code']='error';
            $this->returnMessage['message']='没有获取到openid';
            return json($this->returnMessage);die;
        }
        $addid = $this->request->param('addid') ? $this->request->param('addid') : 0;
//        查这个拼团订单及已经付款的订单
        $cha=Db::name('member_order')->where('id',$id)->find();
        if ($cha['is_num']<3){
            $memberinfo=Db::name('member')->where('openid',$openid)->find();
            $articleinfo=Db::name('article')->where('id',$cha['article_id'])->find();
            $order_id=date('Ymdhis').rand(1000,9999);
            $data=array(
                'mid'=>$memberinfo['id'],
                'balance'=>$articleinfo['is_tag'],
                'create_time'=>time(),
                'end_time'=>time()+86400,
                'state'=>0,
                'is_pin'=>1,//是拼团订单
                'order_id'=>$order_id,
                'article_id'=>$articleinfo['id'],
                'oid'=>$cha['id'],
                'addid'=>$addid,//地址
            );
            $oid=Db::name('member_order')->insertGetId($data);
            if($oid){
                $this->returnMessage['code']='success';
                $this->returnMessage['message']='成功';
                $this->returnMessage['data']=$oid;
            }
        }else{
            $this->returnMessage['code'] = 'error';
            $this->returnMessage['message'] = '拼团人数已足够';
        }
        return json($this->returnMessage);
    }
    //我要参团
//    function add_group1(){
////        获取课程id
//        $id = $this->request->param('id') ? $this->request->param('id') : '';
//        if(!$id){
//            $this->returnMessage['code']='error';
//            $this->returnMessage['message']='参数错误，没有课程id' ;
//            return json($this->returnMessage);die;
//        }
//        $openid = $this->request->param('openid') ? $this->request->param('openid') : '';
//        if(!$openid){
//            $this->returnMessage['code']='error';
//            $this->returnMessage['message']='没有获取到openid';
//            return json($this->returnMessage);die;
//        }
//        $addid = $this->request->param('addid') ? $this->request->param('addid') : 0;
//        $cha=Db::name('member_order')->where('article_id',$id)->where('is_pin',1)->find();
//        //查找字段，看差多少人
//        if ($cha['is_num']<3){
//            $memberinfo=Db::name('member')->where('openid',$openid)->find();
//            $articleinfo=Db::name('article')->where('id',$id)->find();
//            $order_id=date('Ymdhis').rand(1000,9999);
//            $data=array(
//                'mid'=>$memberinfo['id'],
//                'balance'=>$articleinfo['is_tag'],
//                'create_time'=>time(),
//                'state'=>0,
//                'order_id'=>$order_id,
//                'article_id'=>$id,
//                'oid'=>$cha['id'],
//                'addid'=>$addid,//地址
//            );
//            $oid=Db::name('member_order')->insertGetId($data);
//            if($oid){
//                $this->returnMessage['code']='success';
//                $this->returnMessage['message']='成功';
//                $this->returnMessage['data']=$oid;
//            }
//        }else{
//            $this->returnMessage['code'] = 'error';
//            $this->returnMessage['message'] = '拼团人数已足够';
//        }
//        return json($this->returnMessage);
//    }
    //点击确认支付 生成预支付订单
    function prepayOrder(){
        //接收商品id
        $id = $this->request->param('id') ? $this->request->param('id') : 0;
        $addid = $this->request->param('addid') ? $this->request->param('addid') : 0;
        if(!$id){
            $this->returnMessage['code']='error';
            $this->returnMessage['message']='没有获取到商品id';
            return json($this->returnMessage);die;
        }

        $openid = $this->request->param('openid') ? $this->request->param('openid') : '';
        if(!$openid){
            $this->returnMessage['code']='error';
            $this->returnMessage['message']='没有获取到openid';
            return json($this->returnMessage);die;
        }
        //查找优惠券列表是否有对应商品优惠券
        $sub_price=Db::name('coupon')->where('usearticle',$id)->find();
        if ($sub_price['sub_money']>0){
            $memberinfo=Db::name('member')->where('openid',$openid)->find();
            $articleinfo=Db::name('article')->where('id',$id)->find();
            $order_id=date('Ymdhis').rand(1000,9999);
            $data=array(
                'mid'=>$memberinfo['id'],
                'balance'=>$articleinfo['tag']-$sub_price['sub_money'],
                'create_time'=>time(),
                'state'=>0,
                'order_id'=>$order_id,
                'article_id'=>$id,
                'addid'=>$addid,
            );
            $oid=Db::name('member_order')->insertGetId($data);
            if($oid){
                $this->returnMessage['code']='success';
                $this->returnMessage['message']='成功';
                $this->returnMessage['data']=$oid;
            }
        }
        //没有优惠券
        else{
            $memberinfo=Db::name('member')->where('openid',$openid)->find();
            $articleinfo=Db::name('article')->where('id',$id)->find();
            $order_id=date('Ymdhis').rand(1000,9999);
            $data=array(
                'mid'=>$memberinfo['id'],
                'balance'=>$articleinfo['tag'],
                'create_time'=>time(),
                'state'=>0,
                'order_id'=>$order_id,
                'article_id'=>$id,
                'addid'=>$addid,
            );
            $oid=Db::name('member_order')->insertGetId($data);
            if($oid){
                $this->returnMessage['code']='success';
                $this->returnMessage['message']='成功';
                $this->returnMessage['data']=$oid;
            }
        }
        return json($this->returnMessage);
    }
    //点击购买 返回用户信息
    function orderInfo(){
        //接收商品id
        $id = $this->request->param('id') ? $this->request->param('id') : 0;
        if($id){
            $allowFields = [
                'title',
                'tag',
            ];
            $orderinfo = Db::name('article')->field($allowFields)->where('id',$id)->find();
            if($orderinfo){
                $this->returnMessage['code']='success';
                $this->returnMessage['message']='成功';
                $this->returnMessage['data']=$orderinfo;
            }
        }else{
            $this->returnMessage['code'] = 'error';
            $this->returnMessage['message'] = '参数错误';
        }
        return json($this->returnMessage);
    }
    //点击购买，返回拼团商品信息
    function orderInfo1(){
        $id = $this->request->param('id') ? $this->request->param('id') : 0;
        if($id){
            $allowFields = [
                'title',
                'is_tag',
            ];
            $orderinfo = Db::name('article')->field($allowFields)->where('id',$id)->find();
            if($orderinfo){
                $this->returnMessage['code']='success';
                $this->returnMessage['message']='成功';
                $this->returnMessage['data']=$orderinfo;
            }
        }else{
            $this->returnMessage['code'] = 'error';
            $this->returnMessage['message'] = '参数错误';
        }
        return json($this->returnMessage);
    }
    //获取拼团客户的信息以及还差几人拼团
    //拼团后获取拼团的订单，inser  oid  根据oid来判断是否是tong一个订单
    function member_info(){
        //获取当前课程id
        $id = $this->request->param('id') ? $this->request->param('id') : 0;
        $allowFields1=[
            'id',
            'title',
            'tag',
            'is_tag',
            'sub_price',
            'description',
            'thumb_src',
            'create_time',
            'stage',
            'is_shop',
            'video_link2',
        ];
        $article=Db::name('article')->field($allowFields1)->where('id',$id)->find();
        //根据课程id查询订单is_pin=1的拼团订单
//        $member_info1=Db::name('')
//        $mid=Db::name('member_order')->where('article_id',$id)->where('is_pin',1)->find();
        $mid=Db::name('member_order')->where('article_id',$id)->where('is_pin',1)->where('state',2)->order('id desc')->select();
        foreach ($mid as $k=>$v){
            $mid[$k]['nickname']=Db::name('member')->where('id',$mid[$k]['mid'])->value('nickname');
            $mid[$k]['thumb_src']=Db::name('member')->where('id',$mid[$k]['mid'])->value('thumb_src');
        }
//        //差几个人
//        $allowFields = [
//            'nickname',
//            'thumb_src',
//        ];
//        $member_info=Db::name('member')->field($allowFields)->where('id',$mid['mid'])->select();
        if($mid){
            $this->returnMessage['code'] = 'success';
            $this->returnMessage['message']= '成功';
//            $this->returnMessage['data'] = $member_info;
            $this->returnMessage['mid']=$mid;
//            $this->returnMessage['article']=$article;
        }else{
            $this->returnMessage['code'] = 'error';
            $this->returnMessage['message']= '没有获取到';
        }
        return json($this->returnMessage);

    }
    //课程简介
    function introduction(){
        //$openid = Session::get('openid');
        $openid = $this->request->param('openid') ? $this->request->param('openid') : '';
        //$openid = 1;//测试用
        $memberinfo=Db::name('member')->where('openid',$openid)->find();
        //接收商品id
        $id = $this->request->param('id') ? $this->request->param('id') : '';
        if(!$id){
            $this->returnMessage['code']='error';
            $this->returnMessage['message']='参数错误，没有课程id' ;
            return json($this->returnMessage);die;
        }
        $articleinfo = Db::name('article')->where('id',$id)->find();
        if(!$articleinfo){
            $this->returnMessage['code']='error';
            $this->returnMessage['message']='参数错误，没有找到该课程' ;
            return json($this->returnMessage);die;
        }
        //判断是否已经购买
        $info=Db::name('member_order')->where('mid',$memberinfo['id'])->where('article_id',$id)->where('state',1)->find();
        if(!$info){

            //未购买
            $allowFields = [
                'id',
                'thumb_src3',
                'title',
                'description',
                'tag',
                'is_tag',
                'start_date',
                'stage',
                'people_num',
                'content',
                'is_try',
                'video_link2',
                'biaoqian',
                'is_shop',
            ];
            $introduction = Db::name('article')->field($allowFields)->where('id',$id)->find();
            if($introduction){
                $this->returnMessage['code'] = 'success';
                $this->returnMessage['message']= '成功,未购买该商品。';
                //$introduction['content']=stripslashes($introduction['content']);
                $this->returnMessage['data'] = $introduction;


            }
        }else{
            //已购买
            $allowFields = [
                'id',
                'thumb_src3',
                'title',
                'description',
                'tag',
                'is_tag',
                'start_date',
                'stage',
                'people_num',
                'content',
                'is_try',
                'video_link2',
                'discusstitle',
                'discussdes',
                'discusscontent',
                'discussauthor',
                'discusspic',
                'biaoqian',
            ];
            $introduction = Db::name('article')->field($allowFields)->where('id',$id)->find();
            if($introduction){
                $num = Db::name('comment')->where('article_id',$id)->count();
                if($num){
                    $introduction['num']=$num;
                }else{
                    $introduction['num']=0;
                }
                $this->returnMessage['code'] = 'success';
                $this->returnMessage['message']= '成功，已购买该商品。';
                $this->returnMessage['data'] = $introduction;
            }

        }
        return json($this->returnMessage);
    }
    function test(){
        $a ='<p style=\"text-align: center;\"><img src=\"http://gynx.appudid.cn/ueditor/php/upload/image/20181027/1540605173324973.jpg\" title=\"1540605173324973.jpg\" alt=\"99999990003159820_1_o.jpg\"/></p>';
        $a=stripslashes($a);
        dump(json($a));
    }
    //二维数组转字符串
    function arr2str ($arr)
    {
        foreach ($arr as $v)
        {
            $v = join(",",$v); //可以用implode将一维数组转换为用逗号连接的字符串
            $temp[] = $v;
        }
        $t="";
        foreach($temp as $v){
            $t.="'".$v."'".",";
        }
        $t=substr($t,0,-1);
        return $t;
    }
    //返回当前分类下文章列表
    function getCatelist(){
        //$openid = Session::get('openid');
        //$openid =1;//测试用
        $openid = $this->request->param('openid') ? $this->request->param('openid') : '';
        if(!$openid){
            $this->returnMessage['code']='error';
            $this->returnMessage['message']='没有获取到openid';
            return json($this->returnMessage);die;
        }

        $member_id = Db::name('member')->where('openid',$openid)->value('id');
        //            判断用户是否是老师
        //获取该用户已购买的课程ID
        $paid = Db::name('member_order')->field('article_id')->where('mid',$member_id)->where('state',1)->select();

        $page       = $this->request->has('page')?$this->request->param('page',0,'intval'):1;
        //获取分类id
        $article_cate_id    = $this->request->param('article_cate_id');
        //$is_all    = $this->request->param('is_all')?$this->request->param('is_all'):1;
        if($article_cate_id==10){
            //全部
            //获取最大分页码
            $teacher_id=Db::name('member')->where('openid',$openid)->value('is_teacher');
            if ($teacher_id<1){
                $art_list   = Db::name('article')->where('is_open',0)->where('is_shop',1)->count();
            }else{
                $art_list   = Db::name('article')->count();
            }
            $max_page   = ceil($art_list/10);
            //训练营分类名称
            $cate_name = Db::name('article_cate')->where('id',$article_cate_id)->value('name');
            $allowFields = [
                'id',
                'title',
                'thumb_src',
                'is_try',
                'stage',
                'people_num',
                'sort',
                'content',
                'biaoqian',
                'tag',
            ];
            if ($teacher_id<1){
                $list       = Db::name('article')->where('is_shop',1)->field($allowFields)->order('sort desc')->limit(10)->page($page)->select();
            }else{
                $list       = Db::name('article')->field($allowFields)->order('sort desc')->limit(10)->page($page)->select();
            }
            if($list){
                //判断是否已购买
                foreach($list as $k =>$v){
                    foreach($paid as $kk => $vv){
                        if($v['id']==$vv['article_id']){
                            $list[$k]['paid'] = 1;
                        }
                    }
                }

                $this->returnMessage['code']='success';
                $this->returnMessage['message']='成功';
                $this->returnMessage['data']=$list;
                $this->returnMessage['max_page']=$max_page;
                $this->returnMessage['art_list']=$art_list;
                $this->returnMessage['cate_name']=$cate_name;

            }else{
                $this->returnMessage['code']='error';
                $this->returnMessage['message']='暂无内容';

            }
        }else{
            //查询分类下所有信息
            $sonidarr = Db::name('article_cate')->field('id')->where('pid',$article_cate_id)->select();

            if(!empty($sonidarr)){

                foreach($sonidarr as $k => $v){
                    $one[] = $sonidarr[$k]['id'];
                }
                $sonids = implode(',',$one);

            }


            //获取最大分页码
            if(!empty($sonids)){

                $art_list   = Db::name('article')->where('is_shop',1)->where('article_cate_id','in',$article_cate_id.','.$sonids)->count();
            }else{
                $art_list   = Db::name('article')->where('is_shop',1)->where('article_cate_id',$article_cate_id)->count();
            }
            $max_page   = ceil($art_list/10);
            //训练营分类名称
            $cate_name = Db::name('article_cate')->where('id',$article_cate_id)->value('name');
            $allowFields = [
                'id',
                'title',
                'thumb_src',
                'is_try',
                'stage',
                'people_num',
                'sort',
                'tag',
                'biaoqian',
            ];
            if(!empty($sonids)){
                $list       = Db::name('article')->where('is_shop',1)->where('article_cate_id','in',$article_cate_id.','.$sonids)->field($allowFields)->order('sort desc')->limit('10')->page($page)->select();
            }else{
                $list       = Db::name('article')->where('is_shop',1)->where('article_cate_id',$article_cate_id)->field($allowFields)->order('sort desc')->limit('10')->page($page)->select();
            }
            if($list){
                //判断是否已购买
                foreach($list as $k =>$v){
                    foreach($paid as $kk => $vv){
                        if($v['id']==$vv['article_id']){
                            $list[$k]['paid'] = 1;
                        }
                    }
                }
                $this->returnMessage['code']='success';
                $this->returnMessage['message']='成功';
                $this->returnMessage['data']=$list;
                $this->returnMessage['max_page']=$max_page;
                $this->returnMessage['art_list']=$art_list;
                $this->returnMessage['cate_name']=$cate_name;

            }else{
                $this->returnMessage['code']='error';
                $this->returnMessage['message']='暂无内容';

            }
        }

        return json($this->returnMessage);
    }

    //返回文章列表
    function getArticleList(){
        //获取分页
        $page       = $this->request->has('page')?$this->request->param('page',0,'intval'):1;
        //获取分页数据
        $list       = Db::name('article')->limit('10')->page($page)->select();
        //获取最大分页码
        $art_list   = Db::name('article')->count();
        $max_page   = ceil($art_list/10);
        //获取分类id
        $article_cate_id    = $this->request->param('article_cate_id');
        //获取分类数据
        $cate_article       = Db::name('article')->where('article_cate_id',$article_cate_id)->limit('10')->page($page)->select();
        //获取搜索关键词
        $keywords           = $this->request->has('keywords')?'%'.$this->request->param('keywords') . '%':null;
        //获取搜索词相关数据
        if($keywords){
            //搜索关键词相关数据
            $article=Db::name('article')->where('title','like',$keywords)->limit('10')->page($page)->select();
            $this->returnMessage['code']='success';
            $this->returnMessage['message']='查询成功';
            $this->returnMessage['data']=$article;
            return json($this->returnMessage);
            die;
        }
        if($article_cate_id){
            //搜索分类下所属数据
            $this->returnMessage['code']='success';
            $this->returnMessage['message']='获取成功';
            $this->returnMessage['data']=$cate_article;
        }else{
            //显示所有数据
            $this->returnMessage['code']='success';
            $this->returnMessage['message']='获取成功';
            $this->returnMessage['data']=$list;
            $this->returnMessage['max_page']=$max_page;
        }
        return json($this->returnMessage);

    }
    //文章内容
    function article(){

        $id=$this->request->param('id');

        $list=Db::name('article')->where('id',$id)->find();

        $this->assign('list',$list);

        return $this->fetch();

    }
    //返回文章内容
    function getArticle(){
        $uid=session('member')['id'];
        //获取文章id
        $id=$this->request->param('id');
        $member_article=Db::name('member_article')->where(['article_id'=>$id,'user_id'=>$uid,'isDelet'=>0])->find();
        $article=Db::name('article')->where('id',$id)->find();

        if(!$id){
            $this->returnMessage['code']='error';
            $this->returnMessage['message']='错误，期望参数不存在，返回主页。';
        }else{
            if($member_article){
                $this->returnMessage['article_statu']=1;
                $this->returnMessage['member_article_ctime']=$member_article['create_time'];
            }else{
                $this->returnMessage['article_statu']=0;
            }
            $this->returnMessage['code']='success';
            $this->returnMessage['message']='获取成功';
            $this->returnMessage['data']=$article;
        }

        return json($this->returnMessage);
    }
    //文章分类
    function articleCate(){
        $article_cate=Db::name('article_cate')->select();
        if($article_cate){
            $this->returnMessage['code']='success';
            $this->returnMessage['message']='查询成功';
            $this->returnMessage['data']=$article_cate;
        }
        $article_cate_id=$this->request->param('article_cate_id');
        if($article_cate_id){
            $article=Db::name('article')->where('article_cate_id',$article_cate_id)->select();
            if($article){
                $this->returnMessage['code']='success';
                $this->returnMessage['message']='查询成功';
                $this->returnMessage['data']=$article;
            }
        }
        return json($this->returnMessage);
    }
    //变为我的
    function articleFission(){
        $article_id=$this->request->param('article_id');
        if(!$article_id){
            $this->returnMessage['code']='error';
            $this->returnMessage['message']='期望参数不存在';
        }else{
            $uid=session('member')['id'];
            $is_member=Db::name('member')->where('id',$uid)->find();
            if($is_member['is_vip']==0){
                $this->returnMessage['code']='error';
                $this->returnMessage['message']='还未开通会员';
                return json($this->returnMessage);die;
            }
            $user_qrcode=session('member')['user_qrcode'];
            $member_qrcode=session('member')['member_qrcode'];
            $insertData=[];
            $allowsFields=[
                'title',
                'tag',
                'description',
                'article_cate_id',
                'thumb',
                'thumb_src',
                'content',
                'create_time'
            ];
            $article=Db::name('article')->field($allowsFields)->where('id',$article_id)->find();
            if(!$article){
                $this->returnMessage['code']='error';
                $this->returnMessage['message']='文章不存在';
            }else{
                foreach ($article as $key => $value) {

                    if (in_array($key, $allowsFields)) {

                        $insertData[$key] = $value;

                    }

                }
                $insertData['article_id']=$article_id;
                $insertData['user_id']=$uid;
                $insertData['create_time']=time();
                $insertData['user_qrcode']=$user_qrcode;
                $insertData['member_qrcode']=$member_qrcode;
                $member_article=Db::name('member_article')->where(['article_id'=>$article_id,'user_id'=>$uid,'isDelet'=>0])->find();
                if($member_article){
                    $this->returnMessage['code']='success';
                    $this->returnMessage['message']='已设置';
                }else{
                    if(Db::name('member_article')->insert($insertData)<1){
                        $this->returnMessage['code']='error';
                        $this->returnMessage['message']='错误，请重试';
                    }else{
                        $this->returnMessage['code']='success';
                        $this->returnMessage['message']='成功';
                    }
                }

            }

        }
        return json($this->returnMessage);
    }
    // //所属分类
    // function belongCate(){
    //     $article_cate_id=$this->request->has('id')?$this->request->param('id',0,'intval'):0;
    //     if(!$article_cate_id){
    //         $this->returnMessage['code']='error';
    //         $this->returnMessage['message']='错误！期望参数不存在';
    //     }else{
    //         $article=Db::name('article')->where('article_cate_id',$article_cate_id)->select();
    //         if(!$article){
    //             $this->returnMessage['code']='error';
    //             $this->returnMessage['message']='获取数据失败，请重试';
    //         }else{
    //             $this->returnMessage['code']='success';
    //             $this->returnMessage['message']='获取数据成功';
    //             $this->returnMessage['data']=$article;
    //         }
    //     }
    //     return json($this->returnMessage);
    // }

    //获取热门文章
    function hotArticle(){
        $page=$this->request->has('page')?$this->request->param('page',0,'intval'):1;
        $hot_article=Db::name('article')->where('is_top',1)->limit('10')->page($page)->select();
        if(!$hot_article){
            $this->returnMessage['code']='success';
            $this->returnMessage['message']='暂无热门文章';
        }else{
            $this->returnMessage['code']='success';
            $this->returnMessage['message']='查询成功';
            $this->returnMessage['data']=$hot_article;
        }
        return json($this->returnMessage);
    }

    //修改二维码
    function updateMemberQrcode(){
        $member_qrcode=$this->request->param('member_qrcode');
        $id=session('member')['id'];
        if(!$member_qrcode){
            $this->returnMessage['code']='error';
            $this->returnMessage['message']='请先选择二维码';
        }else{
            if(Db::name('member')->field('member_qrcode')->where(['id'=>$id])->find()){
                if(Db::name('member')->where(['id'=>$id])->update(['member_qrcode'=>$member_qrcode])){
                    $this->returnMessage['code']='success';
                    $this->returnMessage['message']='修改成功';
                }

            }else{
                if(Db::name('member')->where(['id'=>$id])->insert(['member_qrcode'=>$member_qrcode])){
                    $this->returnMessage['code']='success';
                    $this->returnMessage['message']='修改成功';
                }
            }
        }
        return json($this->returnMessage);
    }
}