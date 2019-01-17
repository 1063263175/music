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

}