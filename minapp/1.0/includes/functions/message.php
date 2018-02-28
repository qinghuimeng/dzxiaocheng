<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: function_message.php 29236 2012-03-30 05:34:47Z chenmengshu $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

function dshowmessage($message, $url_forward = '', $values = array(), $extraparam = array(), $custom = 0) {
	global $_G, $show_message;
//	$_G['messageparam'] = func_get_args();
//	if(empty($_G['inhookscript']) && defined('CURMODULE')) {
//		hookscript(CURMODULE, $_G['basescript'], 'messagefuncs', array('param' => $_G['messageparam']));
//	}
	if($extraparam['break']) {
		return;
	}


	if($custom) {
//		$alerttype = 'alert_info';
//		$show_message = $message;
//		include template('common/showmessage');
//		dexit();

        odz_error($message);
	}


	$vars = explode(':', $message);
	if(count($vars) == 2) {
		$show_message = lang('plugin/'.$vars[0], $vars[1], $values);
	} else {
		$show_message = lang('message', $message, $values);
	}


    odz_error($show_message);
}

?>