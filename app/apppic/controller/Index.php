<?php
/**
 * 用户相关
 * @author 李财
 * Time: 2018-9-6
 */
namespace app\apppic\controller;

use think\Db;
use think\Request;
use think\Controller;
use think\Session;
class Index extends Common
{

	//小程序地址信息获取 省
	function getprovince(){
		$code = $this->request->param('code') ? $this->request->param('code') : 0;

		/*if(!$code){
                $this->returnMessage['code']='error';
                $this->returnMessage['message']='没有获取到code';
                return json($this->returnMessage);
        }*/
		$a=file_get_contents('http://japi.zto.cn/zto/api_utf8/baseArea?msg_type=GET_AREA&data='.$code);
		$c=json_decode($a);
		$d=$c->result;
		$e=object_to_array($d);
		if($e){
		$this->returnMessage['code'] = 'success';
        $this->returnMessage['message'] = '成功';
        $this->returnMessage['data'] = $e;     
		}else{
		$this->returnMessage['code']='error';
        $this->returnMessage['message']='没有获取到数据';	
		}
		return json($this->returnMessage);
		
	}
	//小程序地址获取 市
	function getcity(){
		$province_code = $this->request->param('province_code') ? $this->request->param('province_code') : 0;
		if(!$province_code){
                $this->returnMessage['code']='error';
                $this->returnMessage['message']='没有获取到code';
                return json($this->returnMessage);
        }
		$a=file_get_contents('http://japi.zto.cn/zto/api_utf8/baseArea?msg_type=GET_AREA&data='.$province_code);
		$c=json_decode($a);
		$d=$c->result;
		$e=object_to_array($d);
		if($e){
			$this->returnMessage['code'] = 'success';
	        $this->returnMessage['message'] = '成功';
	        $this->returnMessage['data'] = $e;     
		}else{
			$this->returnMessage['code']='error';
	        $this->returnMessage['message']='没有获取到数据';	
		}
		return json($this->returnMessage);
	}
	//小程序地址获取 县
	function getcounty(){
		$city_code = $this->request->param('city_code') ? $this->request->param('city_code') : 0;
		if(!$city_code){
                $this->returnMessage['code']='error';
                $this->returnMessage['message']='没有获取到code';
                return json($this->returnMessage);
        }
		$a=file_get_contents('http://japi.zto.cn/zto/api_utf8/baseArea?msg_type=GET_AREA&data='.$city_code);
		$c=json_decode($a);
		$d=$c->result;
		$e=object_to_array($d);
		if($e){
			$this->returnMessage['code'] = 'success';
	        $this->returnMessage['message'] = '成功';
	        $this->returnMessage['data'] = $e;     
		}else{
			$this->returnMessage['code']='error';
	        $this->returnMessage['message']='没有获取到数据';	
		}
		return json($this->returnMessage);
	}
	function wxaddress(){
		$code = $this->request->param('code') ? $this->request->param('code') : 0;
		$a=file_get_contents('http://japi.zto.cn/zto/api_utf8/baseArea?msg_type=GET_AREA&data='.$code);
		$c=json_decode($a);
		$d = $c->result;
		$e=object_to_array($d);	
		return json($c);
		/*foreach($d as $k =>$v){
			Db::name("province")->insert(['name'=>$v->fullName,'code'=>$v->code]);		
		}*/
	}
	//市
	/*function wxaddress(){
		$sheng = Db::name('province')->field('code')->select();
		foreach($sheng as $k => $v){
		$a=file_get_contents('http://japi.zto.cn/zto/api_utf8/baseArea?msg_type=GET_AREA&data='.$v['code']);
		$c=json_decode($a);
		$d = $c->result;
			foreach($d as $kk => $vv){
				Db::name('city')->insert(['city_name'=>$vv->fullName,'code'=>$vv->code,'pid'=>$v['code']]);
			}
		}
	}*/
	//县  失败。。
	/*function wxaddress(){
		$city = Db::name('city')->field('code')->select();
		//dump($city);die;
		foreach($city as $k => $v){
		$a=file_get_contents('http://japi.zto.cn/zto/api_utf8/baseArea?msg_type=GET_AREA&data='.$v['code']);
		$c=json_decode($a);
		$d = $c->result;
		$e = object_to_array($d);
		//dump($e);		
			foreach($e as $kk => $vv){	
				Db::name('county')->insert(['county_name'=>$vv['fullName'],'code'=>$vv['code'],'city_code'=>$v['code']]);
			}
		}
	}*/

    //获取用户信息
    function getuserinfo(){
       $openid = $this->request->param('openid') ? $this->request->param('openid') : '';
       $nickname = $this->request->param('nickname') ? $this->request->param('nickname') : '';
       $thumb_src = $this->request->param('thumb_src') ? $this->request->param('thumb_src') : '';
       $register_time=time();
      
        if(!$openid){
                $this->returnMessage['code']='error';
                $this->returnMessage['message']='没有获取到openid';
                return json($this->returnMessage);
        }
        if(!$nickname){
                $this->returnMessage['code']='error';
                $this->returnMessage['message']='没有获取到昵称';
                return json($this->returnMessage);
        }
        if(!$thumb_src){
                $this->returnMessage['code']='error';
                $this->returnMessage['message']='没有获取到头像';
                return json($this->returnMessage);
        }
        /*$fp=fopen("2.txt","a+");//fopen()的其它开关请参看相关函数
            //$str="我加我加我加加加";
            fwrite($fp,var_export($openid,true)); 
            fclose($fp);*/
        $res = Db::name('member')->where('openid',$openid)->find();

        if($res){
            $data = Db::name('member')->where('openid',$openid)->update(['nickname'=>$nickname,'thumb_src'=>$thumb_src,'register_time'=>$register_time]);
            if($data!==false){
                $this->returnMessage['code'] = 'success';
                $this->returnMessage['message'] = '成功';
                return json($this->returnMessage);
            }else{
                $this->returnMessage['code']='error';
                $this->returnMessage['message']='获取失败';
                return json($this->returnMessage);   
            }
        }else{
            //添加新用户
            $arr = array('openid'=>$openid,'nickname'=>$nickname,'thumb_src'=>$thumb_src,'register_time'=>$register_time);
            $result = Db::name('member')->insertGetId($arr);
            if($result){
                $this->returnMessage['code'] = 'success';
                $this->returnMessage['message'] = '成功';
                return json($this->returnMessage); 
            }else{
                $this->returnMessage['code']='error';
                $this->returnMessage['message']='失败';
                return json($this->returnMessage); 
            }
        }
        return json($this->returnMessage); 
    }

    //获取openid      
    function index(){
        //$openid=Session::get('openid');
        $appid='wx8f6d9c5c5e521140';
        $secret='17cf3349f92d6b4960d140e4c092898c';
        $code=$this->request->param('code') ? $this->request->param('code') : 0;
        //$code = '081ENWdR0ZZla92j1EgR0lbPdR0ENWdC';
        if(!$code){
            $this->returnMessage['code'] = 'error';
            $this->returnMessage['message'] = '没有获取到code';
            return json($this->returnMessage);die;
        }
        $url='https://api.weixin.qq.com/sns/jscode2session?appid='.$appid.'&secret='.$secret.'&js_code='.$code.'&grant_type=authorization_code';
        $datb = file_get_contents($url);
        $jdatb=json_decode($datb,true); 
            /*$fp=fopen("1.txt","a+");//fopen()的其它开关请参看相关函数
            //$str="我加我加我加加加";
            fwrite($fp,var_export($jdatb,true)); 
            fclose($fp);*/
        if(isset($jdatb['openid'])){
            //获取openid  
            $openid=$jdatb['openid'];  
           // session('openid',$openid);
            //查询是否已经登录过
            $allowFields=['id','openid'];
            $memberinfo=Db::name('member')->field($allowFields)->where("openid",$openid)->find();

            if(!$memberinfo){
                //首次登录            
                $data=array('openid'=>$openid);
               $memberid= Db::name('member')->insertGetId($data);

                $allowFields=['id','openid'];

               $memberinfo=Db::name('member')->field($allowFields)->where("id",$memberid)->find();  
                $this->returnMessage['code'] = 'success';
                $this->returnMessage['message'] = '成功';
                $this->returnMessage['data'] = $memberinfo;
                return json($this->returnMessage);die;
            }else{   

                $this->returnMessage['code'] = 'success';
                $this->returnMessage['message'] = '成功';
                $this->returnMessage['data'] = $memberinfo;
                return json($this->returnMessage);
            }
        }else{
            $this->returnMessage['code'] = 'error';
            $this->returnMessage['message'] = $jdatb;
            return json($this->returnMessage);
        }
        return json($this->returnMessage);

    }
    private function getJson($url){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); 
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE); 
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($ch);
        curl_close($ch);
        return json_decode($output, true);
    }
    //分类+文章列表
    function catelist(){
        $openid = $this->request->param('openid') ? $this->request->param('openid') : '';
        //$openid = 1;
        if(!$openid){
                $this->returnMessage['code']='error';
                $this->returnMessage['message']='没有获取到openid';
                return json($this->returnMessage);
        }
        $member_id = Db::name('member')->where('openid',$openid)->value('id');
        //获取该用户已购买的课程ID
        $paid = Db::name('member_order')->field('article_id')->where('mid',$member_id)->where('state',1)->select();

        $catelist = Db::name("article_cate")->field('id,name')->where('pid',0)->where('id <> 10')->order('sort')->select();

        $allowFields = [
            'id',
            'thumb_src',
            'title',
            'people_num',
            'stage',
            'sort',
            'tag',
            'is_try',
            'biaoqian',
        ];
        foreach($catelist as $k => $v){
            //获取二级分类下的课程
            $sonarr = Db::name('article_cate')->field('id')->where('pid',$catelist[$k]['id'])->select();
            if(!empty($sonarr)){
                foreach($sonarr as $k11 => $v11){
                    $one[] = $sonarr[$k11]['id'];
                }
                $sonids = implode(',',$one);
                $catelist[$k]['course'] = Db::name('article')->field($allowFields)->where('is_shop',1)->where('article_cate_id','in',$v['id'].','.$sonids)->order('sort desc')->limit(4)->select();
            }else{
                 $catelist[$k]['course'] = Db::name('article')->field($allowFields)->where('is_shop',1)->where('article_cate_id',$v['id'])->order('sort desc')->limit(4)->select();
            }
            
           
            foreach($catelist[$k]['course'] as $k1 => $v1){                
                foreach($paid as $kk => $vv){
                            if($v1['id']==$vv['article_id']){
                                $catelist[$k]['course'][$k1]['paid'] = 1;
                            }
                        }
            }
        }
        if($catelist){
                $this->returnMessage['code']='success';
                $this->returnMessage['message']='成功';
                $this->returnMessage ['data'] = $catelist;
        }else{
            $this->returnMessage['code'] = 'error';
            $this->returnMessage['message'] = '暂无内容';
        }
         return json($this->returnMessage);
    }
    //亲自训练营
    function Parenting(){
        //$openid = Session::get('openid');
        $openid = $this->request->param('openid') ? $this->request->param('openid') : '';
        //$openid = 1;
        if(!$openid){
                $this->returnMessage['code']='error';
                $this->returnMessage['message']='没有获取到openid';
                return json($this->returnMessage);
        }
        $member_id = Db::name('member')->where('openid',$openid)->value('id');
        //获取该用户已购买的课程ID
        $paid = Db::name('member_article')->field('article_id')->where('user_id',$member_id)->select();

        $allowFields = [
            'id',
            'thumb_src',
            'title',
            'people_num',
            'stage',
            'sort',
            'tag',
            'is_try',
        ];

        $articlelist = Db::name('article')->field($allowFields)->where('is_shop',1)->limit(4)->order('sort')->select();
        if($articlelist){
            //判断是否已购买
                foreach($articlelist as $k =>$v){
                    foreach($paid as $kk => $vv){
                        if($v['id']==$vv['article_id']){
                            $articlelist[$k]['paid'] = 1;
                        }
                    }
                }
                    $this->returnMessage['code']='success';

                    $this->returnMessage['message']='成功';

                    $this->returnMessage ['data'] = $articlelist;
        }else{
            $this->returnMessage['code'] = 'error';

            $this->returnMessage['message'] = '暂无内容';
        }
        return json($this->returnMessage);
    }
    //获取banner图
    function getBanner(){
        $openid = $this->request->param('openid') ? $this->request->param('openid') : '';
        
        if(!$openid){
                $this->returnMessage['code']='error';
                $this->returnMessage['message']='没有获取到openid';
                return json($this->returnMessage);
        }
        $member_id = Db::name('member')->where('openid',$openid)->value('id');
        //获取该用户已购买的课程ID
        $paid = Db::name('member_order')->field('article_id')->where('mid',$member_id)->where('state',1)->select();
       
        //dump($paid);
        //$openid=Session::get('openid');
        //$openid=1;//测试用
       /* if(!$openid){
            $this->returnMessage['code'] = 'error';

            $this->returnMessage['message'] = '参数错误，没有openid';

        }else{*/

            $allowFields=[
                'id',
                'thumb_src',
                'href',
                'sort',
            ];
            $banner = Db::name('banner')->field($allowFields)->order('sort')->limit(5)->select();
                //判断是否已购买
                foreach($banner as $k =>$v){
                    foreach($paid as $kk => $vv){
                        if($v['href']==$vv['article_id']){
                            $banner[$k]['paid'] = 1;
                        }
                    }
                }


            if(!$banner){
                    $this->returnMessage['code'] = 'error';

                    $this->returnMessage['message'] = '后台未添加轮播图，请前往后台添加';  
            }else{
                    $this->returnMessage['code']='success';

                    $this->returnMessage['message']='成功';

                    $this->returnMessage ['data'] = $banner;
            }
        /*}*/
         return json($this->returnMessage);
    }
    //获取分类信息
    function getCateinfo(){
        $allowFields=[
                'id',
                'thumb_src',
                'name', 
                'sort',              
            ];
        $cateinfo = Db::name('article_cate')->where('is_show',1)->where('pid',0)->field($allowFields)->order('sort')->limit(4)->select();
        if($cateinfo){
            $this->returnMessage['code'] = 'success';

            $this->returnMessage['message'] = '成功';

            $this->returnMessage['data'] = $cateinfo;
        }
        return json($this->returnMessage);
    }
    //今日推荐
    function todayRecommend(){
        $openid = $this->request->param('openid') ? $this->request->param('openid') : '';
        
        if(!$openid){
                $this->returnMessage['code']='error';
                $this->returnMessage['message']='没有获取到openid';
                return json($this->returnMessage);
        }
        $member_id = Db::name('member')->where('openid',$openid)->value('id');
        //获取该用户已购买的课程ID
        $paid = Db::name('member_order')->field('article_id')->where('mid',$member_id)->where('state',1)->select();

        $allowFields=[
                'id',
                'title',
                'description',
                'thumb_src3',
                'stage',
                'people_num',
                'video_link2',
                'biaoqian',

            ];
        $recommend = Db::name('article')->where('is_top',1)->order('update_time desc')->field($allowFields)->select();
        if($recommend){
            //判断是否已购买
                foreach($recommend as $k =>$v){
                    foreach($paid as $kk => $vv){
                        if($v['id']==$vv['article_id']){
                            $recommend[$k]['paid'] = 1;
                        }
                    }
                }
            $this->returnMessage['code'] = 'success';
            $this->returnMessage['message'] = '成功';
            $this->returnMessage['data'] = $recommend;
         }else{
            $this->returnMessage['code'] = 'error';
            $this->returnMessage['message'] = '暂无推荐内容';
         }
         return json($this->returnMessage);
    }
    //登录
    function login(){
        
        if ($this->request->isPost()) {

            $wheres = [];

            $login_username = $this->request->param('mobile');

            $login_password = $this->request->param('login_password');
            
            $wheres['mobile'] = $login_username ? $login_username : 0;

            $wheres['login_password'] = $login_password ? md5(md5($login_password)) : 0;

            $member = Db::name('member')->where($wheres)->find();

            if (!$member) {

                $this->returnMessage['code'] = 'error';

                $this->returnMessage['message'] = '用户名或密码不正确，请重试。';

            } else {
                $id=$member['id'];
                $md5_id=md5($id.'qiye');
                $qrcode="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=$md5_id";
                //生成二维码
                if($member['user_qrcode']==null){
                   if(Db::name('member')->where('id',$id)->update(['user_qrcode'=>$qrcode])<1){
                        $this->returnMessage['qrcode_message']='生成二维码失败';
                   } 
                }
                if(Db::name('member')->where('id',$id)->update(['md5_id'=>$md5_id])){
                }
                if($member['level_time']<=time()){
                    if(Db::name('member')->where($wheres)->update(['is_vip'=>0,'level'=>0])){
                    }
                }
                unset($member['login_password']);

                session('member', $member);

                $this->returnMessage['code']='success';

                $this->returnMessage['message']='登录成功。';

                $this->returnMessage ['data'] = $member;

            }

        } else {

            $this->returnMessage['code'] = 'error';

            $this->returnMessage['message'] = '请使用POST方式请求！';

        }


         return json($this->returnMessage);

       
    }

    //注册
    function register() {

        // echo session('verify_code');die;

        if (empty($this->request->param('verify_code'))) {

            $this->returnMessage['code'] = 'error';

            $this->returnMessage['message'] = '验证码不能为空！';

            return json($this->returnMessage);die;

        }

        if (empty($this->request->param('mobile')) || empty($this->request->param('login_password'))) {

            $this->returnMessage['code'] = 'error';

            $this->returnMessage['message'] = '用户名或密码不能为空！';

            return json($this->returnMessage);die;

        }



        if ($this->request->param('verify_code') != session('verify_code' . $this->request->param('mobile'))) {

            $this->returnMessage['code'] = 'error';

            $this->returnMessage['message'] = '短信验证码不正确！';

            return json($this->returnMessage);die;

        }



        // 检测用户名

        $member = Db::name('member')->where('mobile', $this->request->param('mobile'))

            ->find();

        if ($member) {

            $this->returnMessage['code'] = 'error';

            $this->returnMessage['message'] = '该手机号已被占用！';

            return json($this->returnMessage);die;

        }



        // 检测邀请人

        /*

         * $referee = Db::name('member')->where('id', $this->request->param('referee'))

         * ->find();

         * if (empty($referee)) {

         * $this->returnMessage['code'] = 'error';

         * $this->returnMessage['message'] = '不存在此邀请人！';

         * return json($this->returnMessage);

         * }

         */

        $allowFields = [

            'login_password',

            'nickname',

            'mobile',

            'referee',

            'area',

            'area_content'
        ];



        // 赋值

        foreach ($this->request->param() as $key => $value) {

            if (in_array($key, $allowFields)) {

                $insertData[$key] = $value;

            }

        }



        if (empty($this->request->param('nickname'))) {

            $insertData['nickname'] = $this->request->param('mobile');

        }



        // 重赋时间

        $insertData['register_time'] = time();

        // 密码加密

        $insertData['login_password'] = md5(md5($this->request->param('login_password')));

        if (! Db::name('member')->insert($insertData)) {

            $this->returnMessage['code'] = 'error';

            $this->returnMessage['message'] = '注册失败！';

        }else{

            $this->returnMessage['code']='success';

            $this->returnMessage['message']='注册成功';

        }

        session('verify_code' . $this->request->param('mobile'), null);

        return json($this->returnMessage);

    }

    //续费会员
    function renewVip(){

        if(!session('member')['id']){
            
            $this->returnMessage['code']='error';

            $this->returnMessage['message']='未登录';

            return json($this->returnMessage);

            die;
        }

        if(!Request::instance()->isPost()){

            $this->returnMessage['code']='error';

            $this->returnMessage['message']='请使用POST方法请求！';

        }else{

                $uid=session('member')['id'];

                $surplus_time=Db::name('member')->field('level_time')->where('id',$uid)->find();
                
                $surplus_level_time=$surplus_time['level_time']; 
                
                if($surplus_level_time<=time()){

                    $this->returnMessage['data']=['surplus_time'=>'会员已到期'];

                }else{

                    $this->returnMessage['data']=['surplus_time'=>"会员到期时间：".date('Y-m-d',$surplus_level_time)];

                }

                

                $level=$this->request->param('level');

                $level_time=$this->request->param('level_time');
                //判断续费会员类型
                if(!$level){

                    $this->returnMessage['code']='error';

                    $this->returnMessage['message']='请选择续费会员类型';

                }else if(!$level_time){

                    $this->returnMessage['code']='error';

                    $this->returnMessage['message']='请选择续费会员时间';

                }else if($level>3){

                    $this->returnMessage['code']='error';

                    $this->returnMessage['message']='会员类型不正确！请重试';

                }else{
                    /**
                     * 先判断用户会员期限是否到期
                     * 到期就把数据库中的level_time重置为当前时间
                     * 然后把请求的开通天数乘以86400加进去
                     * 修改level_time字段
                     * 
                     * 没到期就直接将数据库里的level_time字段加上请求开通天数乘以86400加上去
                     * 修改level_time字段
                     */

                    $not_surplus=Db::name('member')->field('level_time')->where('id',$uid)->find();

                    $not_surplus_time=$not_surplus['level_time'];

                    if($not_surplus_time<=time()){
                        
                        $vip_time=($level_time*86400*30)+time();

                        $insert=Db::name('member')->where('id',$uid)->update(['level_time'=>$vip_time,'level'=>$level]);

                        

                        if($insert<1){

                            $this->returnMessage['code']='error';

                            $this->returnMessage['message']='续费失败，请重试！';

                        }else{

                            $this->returnMessage['code']='success';

                            $this->returnMessage['message']='续费成功。';

                            $this->returnMessage['data']=['surplus_time'=>"会员到期时间：".date('Y-m-d',$vip_time)];

                        }

                    }else{
                        //获取续费时间
                        $vip_time=($level_time*86400*30)+$surplus_level_time;

                        $insert=Db::name('member')->where('id',$uid)->update(['level_time'=>$vip_time,'level'=>$level]);

                        if($insert<1){

                            $this->returnMessage['code']='error';

                            $this->returnMessage['message']='续费失败，请重试！';

                        }else{

                            $this->returnMessage['code']='success';

                            $this->returnMessage['message']='续费成功。';

                            $this->returnMessage['data']=['surplus_time'=>"会员到期时间：".date('Y-m-d',$vip_time)];

                        }

                    }

                }

            
            
        }

        return json($this->returnMessage);
    }

    //分享APP
    function shareApp(){

        if(!session('member')['id']){
            
            $this->returnMessage['code']='error';

            $this->returnMessage['message']='未登录';

            return json($this->returnMessage);

            die;

        }

        if(Request::instance()->isPost()||Request::instance()->isGet()){

            $this->returnMessage['code']='success';

            $this->returnMessage['message']='分享成功。';
            //返回分享地址
            $this->returnMessage['data']=['url'=>'http://pic.appudid.cn/live.html'];

        }else{
            
            $this->returnMessage['code']='error';

            $this->returnMessage['message']='请使用POST或GET方式请求！';

        }

        return json($this->returnMessage);
    }
    //修改资料
    function  editProfile(){

        if(!Request::instance()->isPost()){

            $this->returnMessage['code']='error';

            $this->returnMessage['message']='请使用POST请求';

        }else{
            $uid=session('member')['id'];

            if(!$uid){

                $this->returnMessage['code']='error';

                $this->returnMessage['message']='未获取到用户信息，请先登录';

            }else{
                
                $allowFields = [
        
                    'nickname',
        
                    'sex',

                    'company',
        
                    'company_area',

                    'user_description',

                    'wechat'
        
                ];
                $allowFields_member = [

        
                    'nickname',
        
                    'sex',

                    'company',
        
                    'company_area',
        
                    'thumb',

                    'thumb_src',

                    'user_description',

                    'wechat'
        
                ];
                $updateData=[];
                 foreach ($this->request->param() as $key => $value) {

                    if (in_array($key, $allowFields)) {

                        $updateData[$key] = $value;

                    }

                }
                if(!$updateData){
                    $this->returnMessage['code']='error';
                    $this->returnMessage['message']='修改资料不能为空！';
                    return json($this->returnMessage);die;
                }
                    $update_member=Db::name('member')->where('id',$uid)->update($updateData);
                    if($update_member>=1){
                        $member=Db::name('member')->field($allowFields_member)->where('id',$uid)->find();
                        $this->returnMessage['code']='success';
                        $this->returnMessage['message']='修改成功';
                        $this->returnMessage['data']=$member;
                    }else{
                        $this->returnMessage['code']='success';
                        $this->returnMessage['message']='已修改';
                    }
                

            }
        }
        return json($this->returnMessage);
    }
    //获取个人信息
    function userInfo(){
        $uid=session('member')['id'];
        $id=$this->request->param('id');
        if($id){
            $member_info=Db::name('member')->where('md5_id',$id)->find();
            unset($member_info['login_password']);
            $this->returnMessage['code']='success';
            $this->returnMessage['message']='获取成功';
            $this->returnMessage['data']=$member_info;
            return json($this->returnMessage);
        }
        if(!$uid){

            $this->returnMessage['code']='error';
            $this->returnMessage['message']='未登录';

        }else{
            $member=Db::name('member')->where('id',$uid)->find();
            unset($member['login_password']);
            $this->returnMessage['code']='success';
            $this->returnMessage['message']='查询成功';
            $this->returnMessage['data']=$member;
        }
        return json($this->returnMessage);
    }
    //修改头像
    function editHeadPortrait(){
        if(!Request::instance()->isPost()){
            $this->returnMessage['code']='error';
            $this->returnMessage['message']='请使用POST请求';
        }else{
            $uid=session('member')['id'];
            if(!$uid){
                $this->returnMessage['code']='error';
                $this->returnMessage['message']='未登录';
            }else{
                $thumb=$this->request->param('thumb');
                $thumb_src=$this->request->param('thumb_src');
                if(!$thumb_src||!$thumb){
                    $this->returnMessage['code']='error';
                    $this->returnMessage['message']='错误，请上传正确的资源地址';
                }else{
                    $edit=Db::name('member')->where('id',$uid)->update(['thumb'=>$thumb,'thumb_src'=>$thumb_src]);
                    $member=Db::name('member')->where('id',$uid)->find();
                    unset($member['login_password']);
                    if($edit!=0){
                        $this->returnMessage['code']='success';
                        $this->returnMessage['message']='修改成功';
                        $this->returnMessage['data']=$member;
                    }else{
                        $this->returnMessage['code']='error';
                        $this->returnMessage['message']='修改错误，请重试';
                    }
                }
            }
        }
        return json($this->returnMessage);
    }
    //修改背景图片
    function editBackground(){
        if(!Request::instance()->isPost()){
            $this->retrunMessage['code']='error';
            $this->returnMessage['message']='请使用POST请求';
        }else{
            $uid=session('member')['id'];
            if(!$uid){
                $this->retrunMessage['code']='error';
                $this->returnMessage['message']='未登录';
            }else{
                $thumb=$this->request->param('thumb');
                $thumb_src=$this->request->param('thumb_src');
                if(!$thumb_src||!$thumb){
                    $this->returnMessage['code']='error';
                    $this->returnMessage['message']='错误，请上传正确的资源地址';
                }else{
                    $edit=Db::name('member')->where('id',$uid)->update(['background_id'=>$thumb,'background'=>$thumb_src]);
                    $member=Db::name('member')->where('id',$uid)->find();
                    unset($member['login_password']);
                    if($edit!=0){
                        $this->returnMessage['code']='success';
                        $this->returnMessage['message']='修改成功';
                        $this->returnMessage['data']=$member;
                    }else{
                        $this->returnMessage['code']='error';
                        $this->returnMessage['message']='修改错误，请重试';
                    }
                }
            }
        }
        return json($this->returnMessage);
    }
    //修改密码
    function editPassword(){
        //获取用户id
        $id=session('member')['id'];
        if(!Request::instance()->isPost()){
            $this->returnMessage['code']='error';
            $this->returnMessage['message']='请求格式错误，请使用POST方式请求';
        }else{
            if(!$id){
                $this->returnMessage['code']='error';
                $this->returnMessage['message']='未登录';
            }else{
                //获取密码
                $password= $this->request->has('login_password')?md5(md5($this->request->param('login_password'))):'';
                //验证原始密码是否为空
                if($password==''){
                    $this->returnMessage['code']='error';
                    $this->returnMessage['message']='原始密码不能为空';
                    return json($this->returnMessage);die;
                }
                //验证原始密码
                if(!Db::name('member')->where(['id'=>$id,'login_password'=>$password])->find()){
                    $this->returnMessage['code']='error';
                    $this->returnMessage['message']='原始密码不正确';
                }else{
                    //验证两次输入密码
                    $update_password=$this->request->has('update_password')?md5(md5($this->request->param('update_password'))):'';
                    $update2_password=$this->request->has('update2_password')?md5(md5($this->request->param('update2_password'))):'';
                    if($update_password!==$update2_password){
                        $this->returnMessage['code']='error';
                        $this->returnMessage['message']='两次密码输入不相同';
                        return json($this->returnMessage);die;
                    }
                    if($update_password==''||$update2_password==''){
                        $this->returnMessage['code']='error';
                        $this->returnMessage['message']='密码不能为空';
                        return json($this->returnMessage);die;
                    }
                    if(strlen($this->request->param('update_password'))<6||strlen($this->request->param('update2_password'))<6){
                        $this->returnMessage['code']='error';
                        $this->returnMessage['message']='密码长度最少11位';
                        return json($this->returnMessage);die;
                    }
                    if(strlen($this->request->param('update_password'))>11||strlen($this->request->param('update2_password'))>16){
                        $this->returnMessage['code']='error';
                        $this->returnMessage['message']='密码长度最长16位';
                        return json($this->returnMessage);die;
                    }
                    if($password==$update_password||$password==$update2_password){
                        $this->returnMessage['code']='error';
                        $this->returnMessage['message']='修改的密码不能与原始密码相同';
                        return json($this->returnMessage);die;
                    }
                    //匹配正则表达式
                    if(!preg_match("/^[a-z0-9_-]{6,18}$/",$this->request->param('update_password'))||!preg_match("/^[a-z0-9_-]{6,18}$/",$this->request->param('update2_password'))){
                        $this->returnMessage['code']='error';
                        $this->returnMessage['message']='密码格式不正确';
                        return json($this->returnMessage);die;
                    }
                    //验证码确认
                    if ($this->request->param('edit_verify_code') != session('edit_verify_code' . $this->request->param('mobile'))) {
                        $this->returnMessage['code'] = 'error';
                        $this->returnMessage['message'] = '短信验证码不正确！';
                        return json($this->returnMessage);die;
                    }
                    $update=Db::name('member')->where('id',$id)->update(['login_password'=>$update2_password]);
                    if($update<1){
                        $this->returnMessage['code']='error';
                        $this->returnMessage['message']='修改失败，请重试';
                    }else{
                        $this->returnMessage['code']='success';
                        $this->returnMessage['message']='修改成功';
                    }
                    
                }
            }
        }
        
        return json($this->returnMessage);
    }
}
