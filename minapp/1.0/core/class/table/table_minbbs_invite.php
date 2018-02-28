<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: table_home_praise.php 29149 2012-03-27 09:52:07Z chenmengshu $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_minbbs_invite extends discuz_table
{
    
	public function __construct() {

		$this->_table = 'minbbs_invite';
		$this->_pk    = 'uid';

		parent::__construct();
	}

	public function init_user($uid, $username){
	
		$user = $this->fetch_user_by_uid($uid);
		
		if(!empty($user)){
			return $user;
		}

		return $this->create_user_by_uid($uid, $username);
	}


	public function create_user_by_uid($uid, $username){

		$code_status = false;
		while(!$code_status){
			$code = $this->make_code($uid);
			$user_info = $this->fetch_user_by_code($code);
			if(empty($user_info)){
				$code_status = true;
			}
		}

	
		DB::query("INSERT INTO %t (uid, username, code, invite_num, score, create_time) VALUES (%d, %s, %s, %d, %d, %d)", array($this->_table, $uid, $username, $code, 0, 0, time()));
		
		return $this->fetch_user_by_uid($uid);
		
	}


	public function make_code($uid){
		$len = 8 - strlen($uid);
		$code = $uid.mt_rand(str_pad(1, $len, 0), str_pad(9, $len, 9));
		return $code;
	}

	public function fetch_user_by_uid($uid){
		return DB::fetch_first("SELECT * FROM %t WHERE uid =%d", array($this->_table, $uid));
	}

	public function fetch_user_by_code($code){
		return DB::fetch_first("SELECT * FROM %t WHERE code =%s", array($this->_table, $code));
	}

	public function update_invite($score, $uid){
		return DB::query('UPDATE %t SET invite_num=invite_num+1,score=score+%d WHERE uid = %d', array($this->_table, $score, $uid));
	}
 

}

?>