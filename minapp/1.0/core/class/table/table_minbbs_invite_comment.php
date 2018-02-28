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

class table_minbbs_invite_comment extends discuz_table
{
    
	public function __construct() {

		$this->_table = 'minbbs_invite_comments';
		$this->_pk    = 'uid';

		parent::__construct();
	}



	public function insert_comment($uid, $username, $content, $score){
		DB::query("INSERT INTO %t (uid, username, content, score, dateline) VALUES (%d, %s, %s, %d, %d)", array($this->_table, $uid, $username, $content, $score, time()));
	}


	public function fetch_comments($start = 0, $limit = 20){
		return DB::fetch_all("SELECT * FROM %t ORDER BY dateline DESC limit %d,%d", array($this->_table, $start, $limit));
	}


	
}

?>