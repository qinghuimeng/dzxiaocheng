<?php
if(!$_G['uid']) {
    odz_error('not_loggedin', ODZ_ERR_LOGIN);
}
if(empty($_GET['avatar'])) {
    odz_error('image_not_choice', -14);
}


$target = DISCUZ_ROOT.'/data/cache/avatar_'.$_G['uid'];
if (!file_put_contents($target, base64_decode($_GET['avatar']))) {
    odz_error('image_save_error');
}
unset($_GET['avatar']);
$size = getimagesize($target);
if(empty($size)){
    @unlink($target);
    odz_error('image_error', -3);
  
}

$avatarbig_file = 'temp/uc_avatarbig_'.$_G['uid'].'.jpg';
$avatarmiddle_file = 'temp/uc_avatarmiddle_'.$_G['uid'].'.jpg';
$avatarsmall_file = 'temp/uc_avatarsmall_'.$_G['uid'].'.jpg';

require_once libfile('class/image');
$image = new image();
$image->Thumb($target, $avatarbig_file, 200, 250, 2);
$image->Thumb($target, $avatarmiddle_file, 120, 120, 2);
$image->Thumb($target, $avatarsmall_file, 48, 48, 2);
@unlink($target);

$postdata = array();

require_once 'includes/imageEncoder.class.php';
$imageEncoder = new imageEncoder();
$attachdir = $_G['setting']['attachdir'];

$postdata['avatar1'] = $imageEncoder->flashdata_encode(file_get_contents($attachdir.'./'.$avatarbig_file));
$postdata['avatar2'] = $imageEncoder->flashdata_encode(file_get_contents($attachdir.'./'.$avatarmiddle_file));
$postdata['avatar3'] = $imageEncoder->flashdata_encode(file_get_contents($attachdir.'./'.$avatarsmall_file));

@unlink($attachdir.'./'.$avatarbig_file);
@unlink($attachdir.'./'.$avatarmiddle_file);
@unlink($attachdir.'./'.$avatarsmall_file);

// 需要使用Discuz!和UC之间的通讯密钥
$ucinput = authcode('uid=' . $_G['uid']
    . '&agent=' . md5($_SERVER['HTTP_USER_AGENT'])
    . '&time=' . time(),
    'ENCODE', UC_KEY);

$posturl = UC_API . '/index.php?m=user'
    . '&a=rectavatar'
    . '&inajax=1'
    . '&appid=' . UC_APPID
    . '&agent=' . urlencode(md5($_SERVER['HTTP_USER_AGENT']))
    . '&input=' . urlencode($ucinput);

$result = odz_http_request($posturl, 'POST', $postdata); // 返回结果中包含 success="1" 表示上传成功
if(preg_match('/success="1"/i', $result)) {
    C::t('common_member')->update($_G['uid'], array('avatarstatus'=>1));
    odz_result(array('avatar'=>avatar($_G['uid'], 'middle', true)), 'avatar_update_succeed');
} else {
    odz_error('avatar_update_failed', -15);
}

?>