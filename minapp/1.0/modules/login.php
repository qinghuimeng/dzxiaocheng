<?php
/*if($_G['pa_count'] < 2) {
    odz_error('parameters_error', -112);
}*/

$ctl_obj = new logging_ctl();
$ctl_obj->setting = $_G['setting'];
$ctl_obj->on_login();
?>