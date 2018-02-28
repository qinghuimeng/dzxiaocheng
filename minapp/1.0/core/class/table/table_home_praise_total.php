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

class table_home_praise_total extends discuz_table
{
	public function __construct() {
		$this->_table = 'home_praise_total';
		parent::__construct();
	}

    public function fetch_by_id_idtype($tid, $idtype) {
        return DB::result_first("SELECT num FROM %t WHERE tid=%d AND idtype=%s ", array($this->_table, $tid, $idtype));
	}
}

?>