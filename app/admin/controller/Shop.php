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
        
    }

    public function cjpublish()
    {
        
    }

    public function order()
    {
        $list=Db::name('order')->order('order_id','desc')->paginate('20');
        $this->assign('list',$list);
        return $this->fetch();
    }
}