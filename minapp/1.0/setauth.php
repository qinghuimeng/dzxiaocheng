<?php

/**
 * Discuz Login for MinBBS
 */

define('ODZ_AUTHKEY', '(a,9aB\QTy{1peRH');

require_once '../../source/class/class_core.php';

C::app()->init();

$auth = isset($_SERVER['HTTP_MINBBSAUTH']) ? trim($_SERVER['HTTP_MINBBSAUTH']) : (isset($_GET['auth']) ? trim($_GET['auth']) : '');
$link = $_SERVER['HTTP_MINBBSLINK'];

$authcode = authcode($auth, 'DECODE', ODZ_AUTHKEY);
dsetcookie('auth', authcode($authcode, 'ENCODE'), $cookietime, 1, true);

header('location:'.$link);

?>
