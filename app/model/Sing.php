<?php
namespace app\model;

use think\Db;
use think\Model;

class Sing extends Model
{
    public function GetSingList($pagelimit=20)
    {
        return Db::name('sing')
            ->alias('si')
            ->join('music mu','mu.music_id = si.music_id','left')
            ->field('si.*,mu.name as music_name')
            ->paginate($pagelimit);
    }

    public function GetSingPage()
    {
        
    }



}