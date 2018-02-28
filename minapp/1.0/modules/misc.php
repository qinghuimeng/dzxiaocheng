<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: misc.php 32082 2014-11-21 14:06:31Z wangxiaogang $
 */

$submod = trim($_GET['submod']);

if(is_file('includes/misc/'.$submod.'.php')) {
    include 'includes/misc/'.$submod.'.php';
} else {
    odz_error('module_not_found', -135);
}

?>