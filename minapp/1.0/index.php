<?php
 

define('IN_DISCUZ', true);
//define('MINBBS_DEBUG', true);
//define('TIMEMARK', true);

if(defined('__DIR__')){
    define('DISCUZ_ROOT', dirname(dirname(__DIR__)).'/');
}else{
    define('DISCUZ_ROOT', dirname(dirname(dirname(__FILE__))).'/');
}
define('MINBBS_ENCRYPT', 'no');
define('MINBBS_ROOT',dirname(__FILE__).'/');

//定义两个测试常量，用以控制返回结果是否加密和调试模式，方便测试 
if($_REQUEST['debug_key'] = 'minbbs###!!!!2015887'){
    !empty($_REQUEST['minbbs_debug']) && define('MINBBS_DEBUG', true);
    !empty($_REQUEST['minbbs_encrypt']) && define('MINBBS_ENCRYPT', 'no');
}

require_once 'config.php';
require_once 'includes/util.php';
require_once 'core/function.php';
require_once 'core/class.php';

$mod = isset($_REQUEST['mod']) ? trim($_REQUEST['mod']) : '';
if(empty($mod)) {
    $mod = 'index';
}

if(defined('TIMEMARK')){
    require_once 'timemark.php';
    $timemark = new TimeMark;
    $timemark->setName($mod);
    $timemark->mark();
}

require_once 'includes/common.php';

if(in_array($mod, $modules['forum'])) {
    define('CURSCRIPT', 'forum');
} elseif(in_array($mod, $modules['member'])) {
    define('CURSCRIPT', 'member');
} elseif(in_array($mod, $modules['home'])) {
    define('CURSCRIPT', 'home');
} elseif(in_array($mod, $modules['misc'])) {
    define('CURSCRIPT', 'misc');
}

$cachelist = array();
if(defined('CURSCRIPT') && is_file('includes/initializes/'.CURSCRIPT.'.php')) {
    include 'includes/initializes/'.CURSCRIPT.'.php';
}
C::app()->cachelist = $cachelist;
C::app()->init();


$url = parse_url($_G['siteurl']);
$_G['minbbs_config'] = include('config_env.php');

$biu_fid = get_minbbs_setting('biu_data_source');
$_G['minbbs_config']['share_fid']=isset($biu_fid['biu_data_source'])?$biu_fid['biu_data_source']:'';
//避免带目录路径
$url = parse_url($_G['siteurl']);
$_G['baseurl'] = $url['scheme'].'://'.$url['host'].substr($url['path'], 0, strpos($url['path'], 'minapp') - 1) . '/';
$_G['discuz_url'] = $_G['baseurl'];

//后台版本确定，兼容1.5
if(isset($_G['minbbs_config']['version_type']) && $_G['minbbs_config']['version_type'] == 'get'){
    $version_arr = explode('.', $_GET['version']);
    $_G['minbbs_config']['version'] = (int)$version_arr[0] == 2 && (int)$version_arr[1] >= 0 && (int)$version_arr[2] >= 0 ? '2.0' : '1.5';
}
//连续登陆天数
if($_G['uid']){
date_default_timezone_set('Asia/Shanghai'); 
$lasttime = time();
$today_date = strtotime(date("Y-m-d"));
$is_insert = DB::fetch_first("select * from ".DB::table("minbbs_logindays")." where uid = ".$_G['uid']);
    if($is_insert){
        if($today_date - strtotime(date("Y-m-d,",$is_insert['last_logintime']))  !=0){
            if($today_date - strtotime(date("Y-m-d,",$is_insert['last_logintime']))  == 86400){
                DB::update("minbbs_logindays",array('uid'=>$_G['uid'],'login_days'=>$is_insert['login_days'] + 1,'last_logintime'=>$lasttime),'uid='.$_G['uid']);
            }else{
                DB::update("minbbs_logindays",array('uid'=>$_G['uid'],'login_days'=>1,'last_logintime'=>$lasttime),'uid='.$_G['uid']);
            }
        }
    }else{
        DB::insert("minbbs_logindays",array('uid'=>$_G['uid'],'login_days'=>1,'last_logintime'=>$lasttime));
    }
}
//禁止提交的内容中包含 emoji 表情
defined('EMOJI_EXISTS') && odz_error('emoji_not_supported');

if(file_exists('modules/'.$mod.'.php')) {
    include 'modules/'.$mod.'.php';
} else {
    odz_error('module_not_found', -111);
}

?>