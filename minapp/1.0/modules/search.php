<?php

if(!defined('IN_DISCUZ')) {
    exit('Access Denied');
}

$_GET['submod'] = trim($_GET['submod']);

$modarray = array('portal', 'forum', 'user');
$submod = '';

if(in_array($_GET['submod'], $modarray) || !empty($_G['setting']['search'][$_GET['submod']]['status'])) {
    $submod = $_GET['submod'];
} else {
    foreach($_G['setting']['search'] as $submod => $value) {
        if(!empty($value['status'])) {
            break;
        }
    }
}
if(empty($submod)) {
    odz_error('search_closed');
}

require_once libfile('function/search');

if($submod == 'curforum') {
    $submod = 'forum';
    $_GET['srchfid'] = array($_GET['srhid']);
} else {
    $_GET['srhid'] = 0;
}

if(is_file('includes/search/'.$submod.'.php')) {
    include 'includes/search/'.$submod.'.php';
} else {
    odz_error('module_not_found', -135);
}

?>