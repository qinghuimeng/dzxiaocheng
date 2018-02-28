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

class table_minbbs_invite_log extends discuz_table
{
    
	public function __construct() {

		$this->_table = 'minbbs_invite_log';
		$this->_pk    = 'id';

		parent::__construct();
	}



	public function insert_log($type, $udid, $device_type, $uid, $invite_uid = 0){
		DB::query("INSERT INTO %t (uid, invite_uid, type, udid, device_type, create_time) VALUES (%d, %d, %d, %s,%d,%d)", array($this->_table, $uid, $invite_uid, $type,$udid, $device_type, time()));
		return DB::insert_id();
	}

	public function fetch_user_by_uid($uid){
		return DB::fetch_first("SELECT * FROM %t WHERE uid =%d", array($this->_table, $uid));
	}

	public function fetch_user_by_udid_or_uid($udid, $uid){
		return DB::fetch_first("SELECT * FROM %t WHERE uid =%d OR udid=%s", array($this->_table, $uid, $udid));
	}

}

?>