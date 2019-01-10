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
        $list=Db::name('user')->paginate('20');
        $this->assign('list',$list);
        return $this->fetch();
    }
}