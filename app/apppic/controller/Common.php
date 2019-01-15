<?php
namespace app\apppic\controller;

use think\Db;
use \think\Controller;
use \think\Session;

class Common extends Controller
{
    //通用图片上传
    public function upload($module = 'apppic', $use = 'apppic_thumb')
    {
        if ($this->request->file('file')) {
            $file = $this->request->file('file');
        } else {
            $res['code'] = 'error';
            $res['msg']  = '没有上传文件';
            
            return json($res);
            
        }
        $module     = $this->request->has('module') ? $this->request->param('module') : $module; //模块
        $web_config = Db::name('webconfig')->where('web', 'web')->find();
        $info       = $file->validate(['size' => $web_config['file_size'] * 1024, 'ext' => $web_config['file_type']])->rule('date')->move(ROOT_PATH . 'public' . DS . 'uploads' . DS . $module . DS . $use);
        if ($info) {
            $temp_file_path = ROOT_PATH . 'public' . DS . 'uploads' . DS . $module . DS . $use . DS . $info->getSaveName();
            //var_dump($temp_file_path);die;
            //写入到附件表
            $data                = [];
            $data['module']      = $module;
            $data['filename']    = $info->getFilename(); //文件名
            $data['filepath']    = $this->oss('uploads' . DS . $module . DS . $use . DS . $info->getSaveName(), $temp_file_path); //文件路径
            $data['fileext']     = $info->getExtension(); //文件后缀
            $data['filesize']    = $info->getSize(); //文件大小
            $data['create_time'] = time(); //时间
            $data['uploadip']    = $this->request->ip(); //IP
            $data['user_id']     = Session::has('admin') ? Session::get('admin') : 0;
            if ($data['module'] = 'admin') {
                //通过后台上传的文件直接审核通过
                $data['status']     = 1;
                $data['admin_id']   = $data['user_id'];
                $data['audit_time'] = time();
            }
            $data['use']    = $this->request->has('use') ? $this->request->param('use') : $use; //用处
            $res['code']    = 'success';
            $res['message'] = '上传成功';
            $res['id']      = Db::name('attachment')->insertGetId($data);
            $res['src']     = 'http://'.$data['filepath'];

            // addlog($res['id']);//记录日志
            return json($res);
        } else {
            // 上传失败获取错误信息
            $res['code']    = 'error';
            $res['message'] = '图片大小超过限制';
            return json($res);
        }
    }
  
        //通用图片上传
    public function uploadPic($module = 'apppic', $use = 'apppic_thumb')
    {
        if ($this->request->file('file')) {
            $file = $this->request->file('file');
        } else {
            $res['code'] = 'error';
            $res['msg']  = '没有上传文件';
            
            return json($res);
            
        }
    
        $module     = $this->request->has('module') ? $this->request->param('module') : $module; //模块
        $web_config = Db::name('webconfig')->where('web', 'web')->find();
        $info       = $file->validate(['size' => $web_config['file_size'] * 1024, 'ext' => $web_config['file_type']])->rule('date')->move(ROOT_PATH . 'public' . DS . 'uploads' . DS . $module . DS . $use);
        if ($info) {
            $temp_file_path = ROOT_PATH . 'public' . DS . 'uploads' . DS . $module . DS . $use . DS . $info->getSaveName();
            //var_dump($temp_file_path);die;
            //写入到附件表
            $data                = [];
            $data['module']      = $module;
            $data['filename']    = $info->getFilename(); //文件名
            $data['filepath']    = $this->oss('uploads' . DS . $module . DS . $use . DS . $info->getSaveName(), $temp_file_path); //文件路径
            $data['fileext']     = $info->getExtension(); //文件后缀
            $data['filesize']    = $info->getSize(); //文件大小
            $data['create_time'] = time(); //时间
            $data['uploadip']    = $this->request->ip(); //IP
            $data['user_id']     = Session::has('admin') ? Session::get('admin') : 0;
            if ($data['module'] = 'admin') {
                //通过后台上传的文件直接审核通过
                $data['status']     = 1;
                $data['admin_id']   = $data['user_id'];
                $data['audit_time'] = time();
            }
            $data['use']    = $this->request->has('use') ? $this->request->param('use') : $use; //用处
            $res['code']    = 'success';
            $res['message'] = '上传成功';
            $res['id']      = Db::name('attachment')->insertGetId($data);
            $res['src']     = 'http:' . DS .  DS . ''.$data['filepath'];

            // addlog($res['id']);//记录日志
            return json($res);
        } else {
            // 上传失败获取错误信息
            $res['code']    = 'error';
            $res['message'] = '文件操作出错，请重试';
            return json($res);
        }
    }
    public function oss($filename, $filebase)
    {
        if(!file_exists($filebase)){
            return false;
        }

        include_once '../extend/OSS/OssClient.php';
        $alioss = config('alioss');
        try {
            $newoss = new \OSS\OssClient($alioss['acckeyid'], $alioss['acckeysecret'], $alioss['endpoint'], $alioss['custom']);
        } catch (OssException $e) {
            return $e->getMessage();
        }

        try {
            $newoss->uploadFile($alioss['bucket'], $filename, $filebase);
        } catch (OssException $e) {
            return $e->getMessage();
        }

        $imgUrl = $alioss['endpoint'] . '/' . $filename;
        return $imgUrl;

    }
}
