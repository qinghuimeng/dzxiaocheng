<?php

if(!defined('IN_DISCUZ')) {
    exit('Access Denied');
}

$modcachelist = array(
    'ranklist' => array('forums', 'diytemplatename'),
);

if(isset($modcachelist[$mod])) {
    $cachelist = $modcachelist[$mod];
}

?>