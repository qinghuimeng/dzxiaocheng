<?php
if($_G['pa_count'] < 3) {
    odz_error('parameters_error', -112);
}

// х╥хоцэбК
$_GET['password2'] = $_GET['password'];

$ctl_obj = new register_ctl();
$ctl_obj->setting = $_G['setting'];
$ctl_obj->on_register();
?>