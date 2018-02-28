<?php

class Focus_Service{

    private $debug;



    public function __construct(){

    }

    public static function user_focus($uid,$fid){

        if(empty($uid)){
            return 0;
        }

        $focus = DB::fetch_first('SELECT * FROM '.DB::table('minbbs_member_focus')."  WHERE uid = {$uid} AND fid = {$fid}");

        if(empty($focus)){
            return 0;
        }

        return 1;

    }

}