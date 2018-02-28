<?php



if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

function auth_verify() {
	if(isset($_GET['auth'])) {
	    $auth = daddslashes(explode("\t", authcode($_GET['auth'], 'DECODE', ODZ_AUTHKEY)));
	}
	list($discuz_pw, $discuz_uid) = empty($auth) || count($auth) < 2 ? array('', '') : $auth;

	if(!$discuz_uid) {
		odz_error('token_expire');;
	}

	$user = getuserbyuid($discuz_uid, 1);
	if(empty($user)) {
		odz_error('user_not_exists', -2);
	}
	if($user['password'] != $discuz_pw) {
		odz_error('password_changed', -3);
	}
	odz_success('operation_done');
}

auth_verify();

?>