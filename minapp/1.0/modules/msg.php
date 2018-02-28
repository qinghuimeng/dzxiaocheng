<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: space_favorite.php 26636 2011-12-19 02:26:51Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

if(!$_G['uid']){
    echo odz_error('not_loggedin');exit;
}

odz_result(array(
	'uid'=>$_G['uid'],
));



?>