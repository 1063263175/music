<?php
namespace app\model;

use think\Db;
use think\Model;

class Music extends Model
{
    public function GetMusicPage($page)
    {
        return Db::name('music')->paginate($page);
    }


    
}