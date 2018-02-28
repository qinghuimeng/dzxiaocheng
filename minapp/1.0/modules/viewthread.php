<?php

if($_GET['get_uids']==1){
    $reward_view = json_encode(odz_encode(reward_info($_GET['uid'])));
    echo  $reward_view;exit;
}
require_once libfile('function/forumlist');
require_once libfile('function/discuzcode');
require_once libfile('function/post');
require_once 'includes/functions/discuzcode.php';
require_once 'includes/video.php';
odz_loadforum();
if($_GET['viewthread_share']){
	$_G['ppp'] = 11;//分享页 每页回帖数量10条
}else{
	$_G['ppp'] = 20;//每页回帖数量20条
}


$thread = & $_G['forum_thread'];
$forum = & $_G['forum'];
$page = max(1, $_G['page']);
//版块密码验证
if($_G['forum']['password']) {
    if(empty($_GET['pw'])){
            //need password
            view_pwd_showmessage();
    }
    if($_GET['pw'] != $_G['forum']['password']) {
            view_pwd_showmessage(true);
    }
}

//if($_G['setting']['cachethreadlife'] && $_G['forum']['threadcaches'] && !$_G['uid'] && $page == 1 && !$_G['forum']['special'] && empty($_GET['do']) && !defined('IN_ARCHIVER') && !defined('IN_MOBILE')) {
//	viewthread_loadcache();
//}
// xj_event报名
if(in_array('xj_event', $_G['setting']['plugins']['available'])) {
	loadcache('plugin');
	$tid = $_G['tid'];
	$uid = $_G['uid'];
        $timestamp = time();
	$extcredits = $_G['setting']['extcredits'];
	$items = DB::fetch(DB::query("SELECT * FROM ".DB::table('xj_event')." WHERE tid = '$_G[tid]'"));
	$setting = unserialize($items['setting']);
	// $event_count = DB::result_first("SELECT count(*) FROM ".DB::table('xj_eventapply')." WHERE tid = '$tid' and uid = ".$_G['uid']);
	//活动专题跳转判断
	// if(!checkmobile()){
	//   if($setting['project']['openproject'] && (($_G['groupid']>1 && $_G['uid'] != $items['uid']) || !$_G['uid'])){
	// 	  if($setting['project']['openprojectauto']){
	// 		  header("Location: plugin.php?id=xj_event:project_show&tid=$tid");
	// 	  }
	//   }
	// }
	if($items['postclass']==1){
		$postclass = lang('plugin/xj_event', 'xxhd');
		$tmp = explode("\n",$_G['cache']['plugin']['xj_event']['event_offline_class']);
		foreach($tmp as $key=>$value){
			$eventclass = explode("|",$value);
			if($eventclass[0] == $items['offlineclass']){
				break;
			}
		}
	}else{
		$postclass = lang('plugin/xj_event', 'xshd');
		$tmp = explode("\n",$_G['cache']['plugin']['xj_event']['event_online_class']);
		foreach($tmp as $key=>$value){
			$eventclass = explode("|",$value);
			if($eventclass[0] == $items['onlineclass']){
				break;
			}
		}
	}
	foreach($extcredits as $key=>$value){
		if($key == $items['use_extcredits']){
			$extcredit_title = $value['title'];
		}
	}
	$citys = $items['citys'];
	$starttime = dgmdate($items['starttime'],'dt');
	$endtime = dgmdate($items['endtime'],'dt');
	$activityexpiration = dgmdate($items['activityexpiration'],'dt');
	if(!$items['activityaid'] and $items['activityaid_url']){
		$imgurl = $items['activityaid_url'];
	}else{
		//$imgurl = $this->_getpicurl($items['activityaid'],$tid);
		$imgurl = getforumimg($items['activityaid'],0,360,230,'fixnone');
	}
	$userfield = unserialize($items['userfield']);
	$selectuserfield = unserialize($items['userfield']);
	if($selectuserfield) {
		if($selectuserfield) {
			$htmls = $settings = array();
			require_once libfile('function/profile');
			foreach($selectuserfield as $fieldid) {
				if(empty($ufielddata['userfield'])) {
					$memberprofile = C::t('common_member_profile')->fetch($_G['uid']);
					foreach($selectuserfield as $val) {
						if($val == 'birthday'){
							$ufielddata['userfield']['birthyear'] =  $memberprofile['birthyear'];
							$ufielddata['userfield']['birthmonth'] =  $memberprofile['birthmonth'];
						}
						$ufielddata['userfield'][$val] = $memberprofile[$val];
					}
					unset($memberprofile);
				}
				$html = profile_setting($fieldid, $ufielddata['userfield'], false, true);
				if($html) {
					$settings[$fieldid] = $_G['cache']['profilesetting'][$fieldid];
					$htmls[$fieldid] = $html;
				}
			}
		}
	} else {
		$selectuserfield = '';
	}

	$hg = DB::fetch_first("SELECT * FROM ".DB::table('xj_eventthread')." WHERE eid=".intval($items['eid'])." and sort=1");
	//报名通过总人数
	$applycountnumber = DB::result_first("SELECT SUM(applynumber) FROM ".DB::table('xj_eventapply')." WHERE tid='$tid' and verify=1");
	$applycountnumber = !$applycountnumber?0:$applycountnumber;
	$applycountnumberd = DB::result_first("SELECT SUM(applynumber) FROM ".DB::table('xj_eventapply')." WHERE tid='$tid' and verify=0");
	$applycountnumberd = !$applycountnumberd?0:$applycountnumberd;

	//报名时可能选择的人数
	$items['event_number_max'] = $items['event_number_max']>0?$items['event_number_max']:1;
	$applynumber = array();
	for($i=1;$i<=$items['event_number_max'];$i++){
		$applynumber[] = $i;
	}
	//报名审核状态
	$apply = DB::fetch_first("SELECT applyid,pay_state,verify,seccode FROM ".DB::table('xj_eventapply')." WHERE tid='$tid' and uid=".$_G['uid']);
	$verify = $apply['verify'];
	$pay_state = $apply['pay_state'];


	//判断是不是管理团队
	$event_admin = false;
	if($_G['username']){
	  if(in_array($_G['username'],$setting['event_admin'])){
		  $event_admin = true;
	  }
	}
	//活动管理列表
	$event_adminlist = implode(',',$setting['event_admin']);
}

$threadtableids = !empty($_G['cache']['threadtableids']) ? $_G['cache']['threadtableids'] : array();
$threadtable_info = !empty($_G['cache']['threadtable_info']) ? $_G['cache']['threadtable_info'] : array();

$archiveid = $thread['threadtableid'];
$thread['is_archived'] = $archiveid ? true : false;
$thread['archiveid'] = $archiveid;
$forum['threadtableid'] = $archiveid;
$threadtable = $thread['threadtable'];
$posttableid = $thread['posttableid'];
$posttable = $thread['posttable'];


$_G['action']['fid'] = $_G['fid'];
$_G['action']['tid'] = $_G['tid'];

if($_G['fid'] == $_G['setting']['followforumid'] && $_G['adminid'] != 1) {
    view_showmessage('post_not_found');
}

$_GET['authorid'] = !empty($_GET['authorid']) ? intval($_GET['authorid']) : 0;
$_GET['ordertype'] = !empty($_GET['ordertype']) ? intval($_GET['ordertype']) : 0;
$_GET['from'] = $_G['setting']['portalstatus'] && !empty($_GET['from']) && $_GET['from'] == 'portal' ? 'portal' : '';

$fromuid = $_G['setting']['creditspolicy']['promotion_visit'] && $_G['uid'] ? '&amp;fromuid='.$_G['uid'] : '';
$feeduid = $_G['forum_thread']['authorid'] ? $_G['forum_thread']['authorid'] : 0;
$feedpostnum = $_G['forum_thread']['replies'] > $_G['ppp'] ? $_G['ppp'] : ($_G['forum_thread']['replies'] ? $_G['forum_thread']['replies'] : 1);

if(!empty($_GET['extra'])) {
	parse_str($_GET['extra'], $extra);
	$_GET['extra'] = array();
	foreach($extra as $_k => $_v) {
		if(preg_match('/^\w+$/', $_k)) {
			if(!is_array($_v)) {
				$_GET['extra'][] = $_k.'='.rawurlencode($_v);
			} else {
				$_GET['extra'][] = http_build_query(array($_k => $_v));
			}
		}
	}
	$_GET['extra'] = implode('&', $_GET['extra']);
}
// 板块列表
$forumselect = forumselect();
$pattern = '/<optgroup.*?>/i';
$forumselect = preg_replace($pattern, '', $forumselect);
$aimgs = array();
$skipaids = array();

$thread['subjectenc'] = rawurlencode($_G['forum_thread']['subject']);
$thread['short_subject'] = cutstr($_G['forum_thread']['subject'], 52);

$_GET['extra'] = $_GET['extra'] ? rawurlencode($_GET['extra']) : '';

if(@in_array('forum_viewthread', $_G['setting']['rewritestatus'])) {
	$canonical = rewriteoutput('forum_viewthread', 1, '', $_G['tid'], 1, '', '');
} else {
	$canonical = 'forum.php?mod=viewthread&tid='.$_G['tid'];
}
$_G['forum_tagscript'] = '';

$threadsort = $thread['sortid'] && isset($_G['forum']['threadsorts']['types'][$thread['sortid']]) ? 1 : 0;
if($threadsort) {
	require_once libfile('function/threadsort');
	require_once 'includes/functions/threadsort.php';
	$threadsortshow = odz_threadsortshow($thread['sortid'], $_G['tid']);
}

if(empty($_G['forum']['allowview'])) {
	if(!$_G['forum']['viewperm'] && !$_G['group']['readaccess']) {
            //针对h5页面页面提示特殊处理
                view_showmessage('group_nopermission',array('grouptitle'=>$_G['group']['grouptitle']));
	} elseif($_G['forum']['viewperm'] && !forumperm($_G['forum']['viewperm'])) {
		view_showmessage('group_nopermission',array('grouptitle'=>$_G['group']['grouptitle']));
	}
} elseif($_G['forum']['allowview'] == -1) {
	view_showmessage('forum_access_view_disallow');
}

if($_G['forum']['formulaperm']) {
	formulaperm($_G['forum']['formulaperm']);
}

if($_G['forum']['password'] && $_G['forum']['password'] != $_G['cookie']['fidpw'.$_G['fid']]) {
	dsetcookie('fidpw'.$_G['fid'], $_G['forum']['password']);
}

if($_G['forum_thread']['readperm'] && $_G['forum_thread']['readperm'] > $_G['group']['readaccess'] && !$_G['forum']['ismoderator'] && $_G['forum_thread']['authorid'] != $_G['uid']) {
        view_showmessage('thread_nopermission',array('readperm' => $_G['forum_thread']['readperm']));
}

$usemagic = array('user' => array(), 'thread' => array());

$replynotice = getstatus($_G['forum_thread']['status'], 6);

$hiddenreplies = getstatus($_G['forum_thread']['status'], 2);

$rushreply = getstatus($_G['forum_thread']['status'], 3);

$savepostposition = getstatus($_G['forum_thread']['status'], 1);

$incollection = getstatus($_G['forum_thread']['status'], 9);

$_G['forum_threadpay'] = FALSE;
if($_G['forum_thread']['price'] > 0 && $_G['forum_thread']['special'] == 0) {
	if($_G['setting']['maxchargespan'] && TIMESTAMP - $_G['forum_thread']['dateline'] >= $_G['setting']['maxchargespan'] * 3600) {
		C::t('forum_thread')->update($_G['tid'], array('price' => 0), false, false, $archiveid);
		$_G['forum_thread']['price'] = 0;
	} else {
		$exemptvalue = $_G['forum']['ismoderator'] ? 128 : 16;
		if(!($_G['group']['exempt'] & $exemptvalue) && $_G['forum_thread']['authorid'] != $_G['uid']) {
			if(!(C::t('common_credit_log')->count_by_uid_operation_relatedid($_G['uid'], 'BTC', $_G['tid']))) {
				require_once libfile('thread/pay', 'include');
				$_G['forum_threadpay'] = TRUE;
			}
		}
	}
}

if($rushreply) {
	$rewardfloor = '';
	$rushresult = $rewardfloorarr = $rewardfloorarray = array();
	$rushresult = C::t('forum_threadrush')->fetch($_G['tid']);
	if($rushresult['creditlimit'] == -996) {
		$rushresult['creditlimit'] = '';
	}
	if((TIMESTAMP < $rushresult['starttimefrom'] || ($rushresult['starttimeto'] && TIMESTAMP > $rushresult['starttimeto']) || ($rushresult['stopfloor'] && $_G['forum_thread']['replies'] + 1 >= $rushresult['stopfloor'])) && $_G['forum_thread']['closed'] == 0) {
		C::t('forum_thread')->update($_G['tid'], array('closed'=>1));
	} elseif(($rushresult['starttimefrom'] && TIMESTAMP > $rushresult['starttimefrom']) && $_G['forum_thread']['closed'] == 1) {
		if(!$rushresult['starttimeto'] && !$rushresult['stopfloor']) {
			C::t('forum_thread')->update($_G['tid'], array('closed'=>0));
		} else {
			if(($rushresult['starttimeto'] && TIMESTAMP < $rushresult['starttimeto'] && $rushresult['stopfloor'] > $_G['forum_thread']['replies'] + 1) || ($rushresult['stopfloor'] && $_G['forum_thread']['replies'] + 1 < $rushresult['stopfloor'])) {
				C::t('forum_thread')->update($_G['tid'], array('closed'=>0));
			}
		}
	}
	$rushresult['starttimefrom'] = $rushresult['starttimefrom'] ? dgmdate($rushresult['starttimefrom']) : '';
	$rushresult['starttimeto'] = $rushresult['starttimeto'] ? dgmdate($rushresult['starttimeto']) : '';
	$rushresult['creditlimit_title'] = $_G['setting']['creditstransextra'][11] ? $_G['setting']['extcredits'][$_G['setting']['creditstransextra'][11]]['title'] : lang('forum/misc', 'credit_total');
}

if($_G['forum_thread']['replycredit'] > 0) {
	$_G['forum_thread']['replycredit_rule'] = C::t('forum_replycredit')->fetch($thread['tid']);
	$_G['forum_thread']['replycredit_rule']['remaining'] = $_G['forum_thread']['replycredit'] / $_G['forum_thread']['replycredit_rule']['extcredits'];
	$_G['forum_thread']['replycredit_rule']['extcreditstype'] = $_G['forum_thread']['replycredit_rule']['extcreditstype'] ? $_G['forum_thread']['replycredit_rule']['extcreditstype'] : $_G['setting']['creditstransextra'][10] ;
}
$_G['group']['raterange'] = $_G['setting']['modratelimit'] && $adminid == 3 && !$_G['forum']['ismoderator'] ? array() : $_G['group']['raterange'];

$_G['group']['allowgetattach'] = !empty($_G['forum']['allowgetattach']) || ($_G['group']['allowgetattach'] && !$_G['forum']['getattachperm']) || forumperm($_G['forum']['getattachperm']);
$_G['group']['allowgetimage'] = !empty($_G['forum']['allowgetimage']) || ($_G['group']['allowgetimage'] && !$_G['forum']['getattachperm']) || forumperm($_G['forum']['getattachperm']);
$_G['getattachcredits'] = '';
if($_G['forum_thread']['attachment']) {
	$exemptvalue = $_G['forum']['ismoderator'] ? 32 : 4;
	if(!($_G['group']['exempt'] & $exemptvalue)) {
		$creditlog = updatecreditbyaction('getattach', $_G['uid'], array(), '', 1, 0, $_G['forum_thread']['fid']);
		$p = '';
		if($creditlog['updatecredit']) for($i = 1;$i <= 8;$i++) {
			if($policy = $creditlog['extcredits'.$i]) {
				$_G['getattachcredits'] .= $p.$_G['setting']['extcredits'][$i]['title'].' '.$policy.' '.$_G['setting']['extcredits'][$i]['unit'];
				$p = ', ';
			}
		}
	}
}

$exemptvalue = $_G['forum']['ismoderator'] ? 64 : 8;
$_G['forum_attachmentdown'] = $_G['group']['exempt'] & $exemptvalue;

$seccodecheck = ($_G['setting']['seccodestatus'] & 4) && (!$_G['setting']['seccodedata']['minposts'] || getuserprofile('posts') < $_G['setting']['seccodedata']['minposts']);
$secqaacheck = $_G['setting']['secqaa']['status'] & 2 && (!$_G['setting']['secqaa']['minposts'] || getuserprofile('posts') < $_G['setting']['secqaa']['minposts']);
$usesigcheck = $_G['uid'] && $_G['group']['maxsigsize'];

$postlist = $_G['forum_attachtags'] = $attachlist = $_G['forum_threadstamp'] = array();
$aimgcount = 0;
$_G['forum_attachpids'] = array();

if(!empty($_GET['action']) && $_GET['action'] == 'printable' && $_G['tid']) {
	require_once libfile('thread/printable', 'include');
	dexit();
}

if($_G['forum_thread']['stamp'] >= 0) {
	$_G['forum_threadstamp'] = $_G['cache']['stamps'][$_G['forum_thread']['stamp']];
}

$lastmod = viewthread_lastmod($_G['forum_thread']);

$showsettings = str_pad(decbin($_G['setting']['showsettings']), 3, '0', STR_PAD_LEFT);

$showsignatures = $showsettings{0};
$showavatars = $showsettings{1};
$_G['setting']['showimages'] = $showsettings{2};

$highlightstatus = isset($_GET['highlight']) && str_replace('+', '', $_GET['highlight']) ? 1 : 0;

$_G['forum']['allowreply'] = isset($_G['forum']['allowreply']) ? $_G['forum']['allowreply'] : '';
$_G['forum']['allowpost'] = isset($_G['forum']['allowpost']) ? $_G['forum']['allowpost'] : '';

$allowpostreply = ($_G['forum']['allowreply'] != -1) && (($_G['forum_thread']['isgroup'] || (!$_G['forum_thread']['closed'] && !checkautoclose($_G['forum_thread']))) || $_G['forum']['ismoderator']) && ((!$_G['forum']['replyperm'] && $_G['group']['allowreply']) || ($_G['forum']['replyperm'] && forumperm($_G['forum']['replyperm'])) || $_G['forum']['allowreply']);
$fastpost = $_G['setting']['fastpost'] && !$_G['forum_thread']['archiveid'] && ($_G['forum']['status'] != 3 || $_G['isgroupuser']);
$allowfastpost = $_G['setting']['fastpost'] && $allowpostreply;
if(!$_G['uid'] && ($_G['setting']['need_avatar'] || $_G['setting']['need_email'] || $_G['setting']['need_friendnum']) || !$_G['adminid'] && (!cknewuser(1) || $_G['setting']['newbiespan'] && (!getuserprofile('lastpost') || TIMESTAMP - getuserprofile('lastpost') < $_G['setting']['newbiespan'] * 60) && TIMESTAMP - $_G['member']['regdate'] < $_G['setting']['newbiespan'] * 60)) {
	$allowfastpost = false;
}
$_G['group']['allowpost'] = $_G['forum']['allowpost'] != -1 && ((!$_G['forum']['postperm'] && $_G['group']['allowpost']) || ($_G['forum']['postperm'] && forumperm($_G['forum']['postperm'])) || $_G['forum']['allowpost']);

$_G['forum']['allowpostattach'] = isset($_G['forum']['allowpostattach']) ? $_G['forum']['allowpostattach'] : '';
$allowpostattach = $allowpostreply && ($_G['forum']['allowpostattach'] != -1 && ($_G['forum']['allowpostattach'] == 1 || (!$_G['forum']['postattachperm'] && $_G['group']['allowpostattach']) || ($_G['forum']['postattachperm'] && forumperm($_G['forum']['postattachperm']))));

if($_G['group']['allowpost']) {
	$_G['group']['allowpostpoll'] = $_G['group']['allowpostpoll'] && ($_G['forum']['allowpostspecial'] & 1);
	$_G['group']['allowposttrade'] = $_G['group']['allowposttrade'] && ($_G['forum']['allowpostspecial'] & 2);
	$_G['group']['allowpostreward'] = $_G['group']['allowpostreward'] && ($_G['forum']['allowpostspecial'] & 4) && isset($_G['setting']['extcredits'][$_G['setting']['creditstrans']]);
	$_G['group']['allowpostactivity'] = $_G['group']['allowpostactivity'] && ($_G['forum']['allowpostspecial'] & 8);
	$_G['group']['allowpostdebate'] = $_G['group']['allowpostdebate'] && ($_G['forum']['allowpostspecial'] & 16);
} else {
	$_G['group']['allowpostpoll'] = $_G['group']['allowposttrade'] = $_G['group']['allowpostreward'] = $_G['group']['allowpostactivity'] = $_G['group']['allowpostdebate'] = FALSE;
}

$_G['forum']['threadplugin'] = $_G['group']['allowpost'] && $_G['setting']['threadplugins'] ? is_array($_G['forum']['threadplugin']) ? $_G['forum']['threadplugin'] : dunserialize($_G['forum']['threadplugin']) : array();

$_G['setting']['visitedforums'] = $_G['setting']['visitedforums'] && $_G['forum']['status'] != 3 ? visitedforums() : '';


$relatedthreadlist = array();
$relatedthreadupdate = $tagupdate = FALSE;
$relatedkeywords = $tradekeywords = $_G['forum_firstpid'] = '';

if(!isset($_G['cookie']['collapse']) || strpos($_G['cookie']['collapse'], 'modarea_c') === FALSE) {
	$collapseimg['modarea_c'] = 'collapsed_no';
	$collapse['modarea_c'] = '';
} else {
	$collapseimg['modarea_c'] = 'collapsed_yes';
	$collapse['modarea_c'] = 'display: none';
}

$threadtag = array();
viewthread_updateviews($archiveid);

$_G['setting']['infosidestatus']['posts'] = $_G['setting']['infosidestatus'][1] && isset($_G['setting']['infosidestatus']['f'.$_G['fid']]['posts']) ? $_G['setting']['infosidestatus']['f'.$_G['fid']]['posts'] : $_G['setting']['infosidestatus']['posts'];

$postfieldsadd = $specialadd1 = $specialadd2 = $specialextra = '';
$tpids = array();
if($_G['forum_thread']['special'] == 2) {
	if(!empty($_GET['do']) && $_GET['do'] == 'tradeinfo') {

		require_once libfile('thread/trade', 'include');
	}

	$query = C::t('forum_trade')->fetch_all_thread_goods($_G['tid']);
	foreach($query as $trade) {
		$tpids[] = $trade['pid'];
	}
	$specialadd2 = 1;

} elseif($_G['forum_thread']['special'] == 5) {
	$_GET['stand'] = isset($_GET['stand']) && in_array($_GET['stand'], array(0, 1, 2)) ? $_GET['stand'] : null;
	if(isset($_GET['stand'])) {
		$specialadd2 = 1;
		$specialextra = "&amp;stand=$_GET[stand]";
	}
}
$onlyauthoradd = $threadplughtml = '';

$maxposition = 0;
if(empty($_GET['viewpid'])) {
	$disablepos = !$rushreply && C::t('forum_threaddisablepos')->fetch($_G['tid']) ? 1 : 0;
	if(!$disablepos && !in_array($_G['forum_thread']['special'], array(2,3,5))) {
            if($_G['forum_thread']['maxposition']) {
                    $maxposition = $_G['forum_thread']['maxposition'];
            } else {
                    $maxposition = C::t('forum_post')->fetch_maxposition_by_tid($posttableid, $_G['tid']);
            }
	}
	$ordertype = empty($_GET['ordertype']) && getstatus($_G['forum_thread']['status'], 4) ? 1 : $_GET['ordertype'];

	$sticklist = array();
	if($_G['forum_thread']['stickreply'] && $page == 1 && (!$_GET['authorid'] || $_GET['authorid'] == $_G['thread']['authorid'])) {
            $poststick = C::t('forum_poststick')->fetch_all_by_tid($_G['tid']);
            foreach(C::t('forum_post')->fetch_all($posttableid, array_keys($poststick)) as $post) {
                $post['position'] = $poststick[$post['pid']]['position'];
                $post['message'] = messagecutstr($post['message'], 400);
                $post['avatar'] = avatar($post['authorid'], 'small');
                $sticklist[$post['pid']] = $post;
            }
            $stickcount = count($sticklist);
	}

	if($rushreply) {
		$rushids = $rushpids = $rushpositionlist = $preg = $arr = array();
		$str = ',,';
		$preg_str = rushreply_rule($rushresult);
		if($_GET['checkrush']) {
			$maxposition = 0;
			for($i = 1; $i <= $_G['forum_thread']['replies'] + 1; $i++) {
				$str = $str.$i.',,';
			}
			preg_match_all($preg_str, $str, $arr);
			$arr = $arr[0];
			foreach($arr as $var) {
				$var = str_replace(',', '', $var);
				$rushids[$var] = $var;
			}
			$temp_reply = $_G['forum_thread']['replies'];
			$_G['forum_thread']['replies'] = $countrushpost = count($rushids) - 1;
			$rushids = array_slice($rushids, ($page - 1) * $_G['ppp'], $_G['ppp']);
			foreach(C::t('forum_post')->fetch_all_by_tid_position($posttableid, $_G['tid'], $rushids) as $post) {
				$postarr[$post['position']] = $post;
			}
		} else {
			for($i = ($page - 1) * $_G['ppp'] + 1; $i <= $page * $_G['ppp']; $i++) {
				$str = $str.$i.',,';
			}
			preg_match_all($preg_str, $str, $arr);
			$arr = $arr[0];
			foreach($arr as $var) {
				$var = str_replace(',', '', $var);
				$rushids[$var] = $var;
			}
			$_G['forum_thread']['replies'] = $_G['forum_thread']['replies'] - 1;
		}
	}
	if($_GET['authorid']) {
		$maxposition = 0;
		$_G['forum_thread']['replies'] = C::t('forum_post')->count_by_tid_invisible_authorid($_G['tid'], $_GET['authorid']);
		$_G['forum_thread']['replies']--;
		if($_G['forum_thread']['replies'] < 0) {
			view_showmessage('undefined_action');
		}
		$onlyauthoradd = 1;
	} elseif($_G['forum_thread']['special'] == 5) {
		if(isset($_GET['stand']) && $_GET['stand'] >= 0 && $_GET['stand'] < 3) {
			$_G['forum_thread']['replies'] = C::t('forum_debatepost')->count_by_tid_stand($_G['tid'], $_GET['stand']);
		} else {
			$_G['forum_thread']['replies'] = C::t('forum_post')->count_visiblepost_by_tid($_G['tid']);
			$_G['forum_thread']['replies'] > 0 && $_G['forum_thread']['replies']--;
		}
	} elseif($_G['forum_thread']['special'] == 2) {
		$tradenum = C::t('forum_trade')->fetch_counter_thread_goods($_G['tid']);
		$_G['forum_thread']['replies'] -= $tradenum;
	}
	if($maxposition) {
		$_G['forum_thread']['replies'] = $maxposition - 1;
	}
	// $_G['ppp'] = $_G['forum']['threadcaches'] && !$_G['uid'] ? $_G['setting']['postperpage'] : $_G['ppp'];
	$totalpage = ceil(($_G['forum_thread']['replies'] + 1) / $_G['ppp']);
	$page > $totalpage && $page = $totalpage;
	$_G['forum_pagebydesc'] = !$maxposition && $page > 2 && $page > ($totalpage / 2) ? TRUE : FALSE;

	if($_G['forum_pagebydesc']) {
		$firstpagesize = ($_G['forum_thread']['replies'] + 1) % $_G['ppp'];
		$_G['forum_ppp3'] = $_G['forum_ppp2'] = $page == $totalpage && $firstpagesize ? $firstpagesize : $_G['ppp'];
		$realpage = $totalpage - $page + 1;
		if($firstpagesize == 0) {
			$firstpagesize = $_G['ppp'];
		}
		$start_limit = max(0, ($realpage - 2) * $_G['ppp'] + $firstpagesize);
		$_G['forum_numpost'] = ($page - 1) * $_G['ppp'];
		if($ordertype != 1) {
		} else {
			$_G['forum_numpost'] = $_G['forum_thread']['replies'] + 2 - $_G['forum_numpost'] + ($page > 1 ? 1 : 0);
		}
	} else {
		$start_limit = $_G['forum_numpost'] = max(0, ($page - 1) * $_G['ppp']);
		if($start_limit > $_G['forum_thread']['replies']) {
			$start_limit = $_G['forum_numpost'] = 0;
			$page = 1;
		}
		if($ordertype != 1) {
		} else {
			$_G['forum_numpost'] = $_G['forum_thread']['replies'] + 2 - $_G['forum_numpost'] + ($page > 1 ? 1 : 0);
		}
	}
	$multipage = multi($_G['forum_thread']['replies'] + 1, $_G['ppp'], $page, 'forum.php?mod=viewthread&tid='.$_G['tid'].
		($_G['forum_thread']['is_archived'] ? '&archive='.$_G['forum_thread']['archiveid'] : '').
		'&amp;extra='.$_GET['extra'].
		($ordertype && $ordertype != getstatus($_G['forum_thread']['status'], 4) ? '&amp;ordertype='.$ordertype : '').
		(isset($_GET['highlight']) ? '&amp;highlight='.rawurlencode($_GET['highlight']) : '').
		(!empty($_GET['authorid']) ? '&amp;authorid='.$_GET['authorid'] : '').
		(!empty($_GET['from']) ? '&amp;from='.$_GET['from'] : '').
		(!empty($_GET['checkrush']) ? '&amp;checkrush='.$_GET['checkrush'] : '').
		(!empty($_GET['modthreadkey']) ? '&amp;modthreadkey='.rawurlencode($_GET['modthreadkey']) : '').
		$specialextra);
} else {
	$_GET['viewpid'] = intval($_GET['viewpid']);
	$pageadd = "AND p.pid='$_GET[viewpid]'";
}
$_G['forum_newpostanchor'] = $_G['forum_postcount'] = 0;
$_G['forum_onlineauthors'] = $_G['forum_cachepid'] = array();
$isdel_post = $cachepids = $postusers = $skipaids = array();

if($_G['forum_auditstatuson'] || in_array($_G['forum_thread']['displayorder'], array(-2, -3, -4)) && $_G['forum_thread']['authorid'] == $_G['uid']) {
	$visibleallflag = 1;
}
//bug修复$_G['tid']必须存在
if($maxposition&&$_G['tid']) {
	$start = ($page - 1) * $_G['ppp'] + 1;
	$end = $start + $_G['ppp'];
	if($ordertype == 1) {
		$end = $maxposition - ($page - 1) * $_G['ppp'] + ($page > 1 ? 2 : 1);
		$start = $end - $_G['ppp'] + ($page > 1 ? 0 : 1);
		$start = max(array(1,$start));
	}
	$have_badpost = $realpost = $lastposition = 0;

	foreach(C::t('forum_post')->fetch_all_by_tid_range_position($posttableid, $_G['tid'], $start, $end, $maxposition, $ordertype) as $post) {
		if($post['invisible'] != 0) {
			$have_badpost = 1;
		}
		$cachepids[$post[position]] = $post['pid'];
		$postarr[$post[position]] = $post;
		$lastposition = $post['position'];
	}
	$realpost = count($postarr);
	if($realpost != $_G['ppp'] || $have_badpost) {
		$k = 0;
		for($i = $start; $i < $end; $i ++) {
			if(!empty($cachepids[$i])) {
				$k = $cachepids[$i];
				$isdel_post[$i] = array('deleted' => 1, 'pid' => $k, 'message' => '', 'position' => $i);
			} elseif($i < $maxposition || ($lastposition && $i < $lastposition)) {
				$isdel_post[$i] = array('deleted' => 1, 'pid' => $k, 'message' => '', 'position' => $i);
			}
			$k ++;
		}
	}
	$pagebydesc = false;
}

if($_GET['checkrush'] && $rushreply) {
	$_G['forum_thread']['replies'] = $temp_reply;
}

if(!$maxposition && empty($postarr)) {
	if(empty($_GET['viewpid'])) {
        /* 屏蔽商品帖子特殊处理
        if($_G['forum_thread']['special'] == 2) {
            $postarr = C::t('forum_post')->fetch_all_tradepost_viewthread_by_tid($_G['tid'], $visibleallflag, $_GET['authorid'], $tpids, $_G['forum_pagebydesc'], $ordertype, $start_limit, ($_G['forum_pagebydesc'] ? $_G['forum_ppp2'] : $_G['ppp']));
        } elseif($_G['forum_thread']['special'] == 5) {
            $postarr = C::t('forum_post')->fetch_all_debatepost_viewthread_by_tid($_G['tid'], $visibleallflag, $_GET['authorid'], $_GET['stand'], $_G['forum_pagebydesc'], $ordertype, $start_limit, ($_G['forum_pagebydesc'] ? $_G['forum_ppp2'] : $_G['ppp']));
        } else {
            $postarr = C::t('forum_post')->fetch_all_common_viewthread_by_tid($_G['tid'], $visibleallflag, $_GET['authorid'], $_G['forum_pagebydesc'], $ordertype, $_G['forum_thread']['replies'] + 1, $start_limit, ($_G['forum_pagebydesc'] ? $_G['forum_ppp2'] : $_G['ppp']));
        }*/
        if($_G['forum_thread']['special'] == 5) {
            $postarr = C::t('forum_post')->fetch_all_debatepost_viewthread_by_tid($_G['tid'], $visibleallflag, $_GET['authorid'], $_GET['stand'], $_G['forum_pagebydesc'], $ordertype, $start_limit, ($_G['forum_pagebydesc'] ? $_G['forum_ppp2'] : $_G['ppp']));
        } else {
            $postarr = C::t('forum_post')->fetch_all_common_viewthread_by_tid($_G['tid'], $visibleallflag, $_GET['authorid'], $_G['forum_pagebydesc'], $ordertype, $_G['forum_thread']['replies'] + 1, $start_limit, ($_G['forum_pagebydesc'] ? $_G['forum_ppp2'] : $_G['ppp']));
        }
    } else {
        $post = array();
        /* 屏蔽商品帖子特殊处理
         * if($_G['forum_thread']['special'] == 2) {
            if(!in_array($_GET['viewpid'], $tpids)) {
                $post = C::t('forum_post')->fetch('tid:'.$_G['tid'],$_GET['viewpid']);
            }
        } elseif($_G['forum_thread']['special'] == 5) {
            $post = C::t('forum_post')->fetch('tid:'.$_G['tid'], $_GET['viewpid']);
            $debatpost = C::t('forum_debatepost')->fetch($_GET['viewpid']);
            if(!isset($_GET['stand']) || (isset($_GET['stand']) && ($post['first'] == 1 || $debatpost['stand'] == $_GET['stand']))) {
                $post = array_merge($post, $debatpost);
            } else {
                $post = array();
            }
            unset($debatpost);
        } else {
            $post = C::t('forum_post')->fetch('tid:'.$_G['tid'], $_GET['viewpid']);
        }*/
        if($_G['forum_thread']['special'] == 5) {
            $post = C::t('forum_post')->fetch('tid:'.$_G['tid'], $_GET['viewpid']);
            $debatpost = C::t('forum_debatepost')->fetch($_GET['viewpid']);
            if(!isset($_GET['stand']) || (isset($_GET['stand']) && ($post['first'] == 1 || $debatpost['stand'] == $_GET['stand']))) {
                $post = array_merge($post, $debatpost);
            } else {
                $post = array();
            }
            unset($debatpost);
        } else {
            $post = C::t('forum_post')->fetch('tid:'.$_G['tid'], $_GET['viewpid']);
        }


		if($post) {
			if($visibleallflag || (!$visibleallflag && !$post['invisible'])) {
				$postarr[0] = $post;
			}
		}
	}

}

if(!empty($isdel_post)) {
	$updatedisablepos = false;
	foreach($isdel_post as $id => $post) {
		if(isset($postarr[$id]['invisible']) && ($postarr[$id]['invisible'] == 0 || $postarr[$id]['invisible'] == -3 || $visibleallflag)) {
			continue;
		}
		$postarr[$id] = $post;
		$updatedisablepos = true;
	}
	if($updatedisablepos && !$rushreply) {
		C::t('forum_threaddisablepos')->insert(array('tid' => $_G['tid']), false, true);
	}
	$ordertype != 1 ? ksort($postarr) : krsort($postarr);
}
$summary = '';
if($page == 1 && $ordertype == 1) {
	$firstpost = C::t('forum_post')->fetch_threadpost_by_tid_invisible($_G['tid']);
	if($firstpost['invisible'] == 0 || $visibleallflag == 1) {
		$postarr = array_merge(array($firstpost), $postarr);
		unset($firstpost);
	}
}
$tagnames = $locationpids = array();

foreach($postarr as $post) {
	if(($onlyauthoradd && $post['anonymous'] == 0) || !$onlyauthoradd) {
		$postusers[$post['authorid']] = array();
		if($post['first']) {
			if($ordertype == 1 && $page != 1) {
				continue;
			}
			$_G['forum_firstpid'] = $post['pid'];
			if(IS_ROBOT || $_G['adminid'] == 1) $summary = str_replace(array("\r", "\n"), '', messagecutstr(strip_tags($post['message']), 160));
			$tagarray_all = $posttag_array = array();
			$tagarray_all = explode("\t", $post['tags']);
			if($tagarray_all) {
                            foreach($tagarray_all as $var) {
                                if($var) {
                                    $tag = explode(',', $var);
                                    $posttag_array[] = $tag;
                                    $tagnames[] = $tag[1];
                                }
                            }
			}
			$post['tags'] = $posttag_array;
			if($post['tags']) {
				$post['relateitem'] = getrelateitem($post['tags'], $post['tid'], $_G['setting']['relatenum'], $_G['setting']['relatetime']);
			}
			if(!$_G['forum']['disablecollect']) {
				if($incollection) {
					$post['relatecollection'] = getrelatecollection($post['tid'], false, $post['releatcollectionnum'], $post['releatcollectionmore']);
					if($_G['group']['allowcommentcollection'] && $_GET['ctid']) {
						$ctid = dintval($_GET['ctid']);
						$post['sourcecollection'] = C::t('forum_collection')->fetch($ctid);
					}
				} else {
					$post['releatcollectionnum'] = 0;
				}
			}
		}
		$postlist[$post['pid']] = $post;
	}
}
$seodata = array('forum' => $_G['forum']['name'], 'fup' => $_G['cache']['forums'][$fup]['name'], 'subject' => $_G['forum_thread']['subject'], 'summary' => $summary, 'tags' => @implode(',', $tagnames), 'page' => intval($_GET['page']));
if($_G['forum']['status'] != 3) {
	$seotype = 'viewthread';
} else {
	$seotype = 'viewthread_group';
	$seodata['first'] = $nav['first']['name'];
	$seodata['second'] = $nav['second']['name'];
}

list($navtitle, $metadescription, $metakeywords) = get_seosetting($seotype, $seodata);
if(!$navtitle) {
	$navtitle = helper_seo::get_title_page($_G['forum_thread']['subject'], $_G['page']).' - '.strip_tags($_G['forum']['name']);
	$nobbname = false;
} else {
	$nobbname = true;
}
if(!$metakeywords) {
	$metakeywords = strip_tags($thread['subject']);
}
if(!$metadescription) {
	$metadescription = $summary.' '.strip_tags($_G['forum_thread']['subject']);
}

$postno = & $_G['cache']['custominfo']['postno'];
if($postusers) {
	$member_verify = $member_field_forum = $member_status = $member_count = $member_profile = $member_field_home = array();
	$uids = array_keys($postusers);
	$uids = array_filter($uids);
	if($_G['setting']['verify']['enabled']) {
		$member_verify = C::t('common_member_verify')->fetch_all($uids);
	}
	$member_field_forum = C::t('common_member_field_forum')->fetch_all($uids);
	$member_status = C::t('common_member_status')->fetch_all($uids);
	$member_count = C::t('common_member_count')->fetch_all($uids);
	$member_profile = C::t('common_member_profile')->fetch_all($uids);
	$member_field_home = C::t('common_member_field_home')->fetch_all($uids);
	foreach(C::t('common_member')->fetch_all($uids) as $uid => $postuser) {
		$member_field_home[$uid]['privacy'] = empty($member_field_home[$uid]['privacy']) ? array() : dunserialize($member_field_home[$uid]['privacy']);
		$postuser['memberstatus'] = $postuser['status'];
		$postuser['authorinvisible'] = $member_status[$uid]['invisible'];
		$postuser['signature'] = $member_field_forum[$uid]['sightml'];
		unset($member_field_home[$uid]['privacy']['feed'], $member_field_home[$uid]['privacy']['view'], $postuser['status'], $member_status[$uid]['invisible'], $member_field_forum[$uid]['sightml']);
		$postusers[$uid] = array_merge((array)$member_verify[$uid], (array)$member_field_home[$uid], (array)$member_profile[$uid], (array)$member_count[$uid], (array)$member_status[$uid], (array)$member_field_forum[$uid], $postuser);
		if($postusers[$uid]['regdate'] + $postusers[$uid]['oltime'] * 3600 > TIMESTAMP) {
			$postusers[$uid]['oltime'] = 0;
		}
		$postusers[$uid]['office'] = $postusers[$uid]['position'];
		unset($postusers[$uid]['position']);
	}
	unset($member_field_forum, $member_status, $member_count, $member_profile, $member_field_home);
	$_G['medal_list'] = array();
	foreach($postlist as $pid => $post) {
		if(getstatus($post['status'], 6)) {
			$locationpids[] = $pid;
		}
		$post = array_merge($postlist[$pid], (array)$postusers[$post['authorid']]);
		$post['message'] = odz_format_message($post['message']);
		$postlist[$pid] = viewthread_procpost($post, $_G['member']['lastvisit'], $ordertype, $maxposition);
	}
}

//if($locationpids) {
//	$locations = C::t('forum_post_location')->fetch_all($locationpids);
//}

//if($postlist && $rushids) {
//	foreach($postlist as $pid => $post) {
//		$post['number'] = $post['position'];
//		$postlist[$pid] = checkrushreply($post);
//	}
//}


if($_G['forum_thread']['special'] > 0 && (empty($_GET['viewpid']) || $_GET['viewpid'] == $_G['forum_firstpid'])) {
	$_G['forum_thread']['starttime'] = gmdate($_G['forum_thread']['dateline']);
	$_G['forum_thread']['remaintime'] = '';
	switch($_G['forum_thread']['special']) {
		case 1: require_once libfile('thread/poll', 'include'); break;
		// case 2: require_once libfile('thread/trade', 'include'); break;
		// case 3: require_once libfile('thread/reward', 'include'); break;
		case 4: require_once libfile('thread/activity', 'include'); break;
		// case 5: require_once libfile('thread/debate', 'include'); break;
		// case 127:
		// 	if($_G['forum_firstpid']) {
		// 		$sppos = strpos($postlist[$_G['forum_firstpid']]['message'], chr(0).chr(0).chr(0));
		// 		if(in_array('xj_event', $_G['setting']['plugins']['available'])) {
		// 			$sppos = $sppos ? $sppos : '-8';
		// 		}
		// 		$specialextra = substr($postlist[$_G['forum_firstpid']]['message'], $sppos + 3);
		// 		$postlist[$_G['forum_firstpid']]['message'] = $sppos ? substr($postlist[$_G['forum_firstpid']]['message'], 0, $sppos) : $postlist[$_G['forum_firstpid']]['message'];
		// 		if($specialextra) {
		// 			if(array_key_exists($specialextra, $_G['setting']['threadplugins'])) {
		// 				@include_once DISCUZ_ROOT.'./source/plugin/'.$_G['setting']['threadplugins'][$specialextra]['module'].'.class.php';
		// 				$classname = 'threadplugin_'.$specialextra;
		// 				if(class_exists($classname) && method_exists($threadpluginclass = new $classname, 'viewthread')) {
		// 					$threadplughtml = $threadpluginclass->viewthread($_G['tid']);
		// 				}
		// 			}
		// 		}
		// 	}
			break;
	}
}

if(empty($_GET['authorid']) && empty($postlist)) {
	if($rushreply) {
            view_showmessage('post_not_found');
	} else {
		$replies = C::t('forum_post')->count_visiblepost_by_tid($_G['tid']);
		$replies = intval($replies) - 1;
		if($_G['forum_thread']['replies'] != $replies && $replies > 0) {
			C::t('forum_thread')->update($_G['tid'], array('replies' => $replies), false, false, $archiveid);
                        view_showmessage('post_not_found');
		}
	}
}

if($_G['forum_pagebydesc'] && (!$savepostposition || $_GET['ordertype'] == 1)) {
	$postlist = array_reverse($postlist, TRUE);
}

//if(!empty($_G['setting']['sessionclose'])) {
//	$_G['setting']['vtonlinestatus'] = 1;
//}
//
//if($_G['setting']['vtonlinestatus'] == 2 && $_G['forum_onlineauthors']) {
//	foreach(C::app()->session->fetch_all_by_uid(array_keys($_G['forum_onlineauthors'])) as $author) {
//		if(!$author['invisible']) {
//			$_G['forum_onlineauthors'][$author['uid']] = 1;
//		}
//	}
//} else {
//	$_G['forum_onlineauthors'] = array();
//}

//$ratelogs = $comments = $commentcount = $totalcomment = array();
//if($_G['forum_cachepid']) {
//	foreach(C::t('forum_postcache')->fetch_all($_G['forum_cachepid']) as $postcache) {
//		if($postcache['rate']) {
//			$postcache['rate'] = dunserialize($postcache['rate']);
//			$postlist[$postcache['pid']]['ratelog'] = $postcache['rate']['ratelogs'];
//			$postlist[$postcache['pid']]['ratelogextcredits'] = $postcache['rate']['extcredits'];
//			$postlist[$postcache['pid']]['totalrate'] = $postcache['rate']['totalrate'];
//		}
//		if($postcache['comment']) {
//			$postcache['comment'] = dunserialize($postcache['comment']);
//			$commentcount[$postcache['pid']] = $postcache['comment']['count'];
//			$comments[$postcache['pid']] = $postcache['comment']['data'];
//			$totalcomment[$postcache['pid']] = $postcache['comment']['totalcomment'];
//		}
//		unset($_G['forum_cachepid'][$postcache['pid']]);
//	}
//	$postcache = $ratelogs = array();
//	if($_G['forum_cachepid']) {
//		$ratelogs = C::t('forum_ratelog')->fetch_postrate_by_pid($_G['forum_cachepid'], $postlist, $postcache, $_G['setting']['ratelogrecord']);
//	}
//	foreach($postlist as $key => $val) {
//		if(!empty($val['ratelogextcredits'])) {
//			ksort($postlist[$key]['ratelogextcredits']);
//		}
//	}
//	if($_G['forum_cachepid'] && $_G['setting']['commentnumber']) {
//		$comments = C::t('forum_postcomment')->fetch_postcomment_by_pid($_G['forum_cachepid'], $postcache, $commentcount, $totalcomment, $_G['setting']['commentnumber']);
//	}
//	foreach($postcache as $pid => $data) {
//		C::t('forum_postcache')->insert(array('pid' => $pid, 'rate' => serialize($data['rate']), 'comment' => serialize($data['comment']), 'dateline' => TIMESTAMP), false, true);
//	}
//}

if($_G['forum_attachpids'] && !defined('IN_ARCHIVER')) {
	require_once libfile('function/attachment');
	require_once 'includes/functions/attachment.php';
	if(is_array($threadsortshow) && !empty($threadsortshow['sortaids'])) {
		$skipaids = $threadsortshow['sortaids'];
	}
	odz_parseattach($_G['forum_attachpids'], $_G['forum_attachtags'], $postlist, $skipaids);
}
if(empty($postlist)) {
	view_showmessage('post_not_found');
} elseif(!defined('IN_MOBILE_API')) {
	foreach($postlist as $pid => $post) {
		$postlist[$pid]['message'] = preg_replace("/\[attach\]\d+\[\/attach\]/i", '', $postlist[$pid]['message']);
	}
}
$_G['forum_thread']['heatlevel'] = $_G['forum_thread']['recommendlevel'] = 0;
if($_G['setting']['heatthread']['iconlevels']) {
	foreach($_G['setting']['heatthread']['iconlevels'] as $k => $i) {
		if($_G['forum_thread']['heats'] > $i) {
			$_G['forum_thread']['heatlevel'] = $k + 1;
			break;
		}
	}
}
//if(!empty($_G['setting']['recommendthread']['status']) && $_G['forum_thread']['recommends']) {
//	foreach($_G['setting']['recommendthread']['iconlevels'] as $k => $i) {
//		if($_G['forum_thread']['recommends'] > $i) {
//			$_G['forum_thread']['recommendlevel'] = $k+1;
//			break;
//		}
//	}
//}
$allowblockrecommend = $_G['group']['allowdiy'] || getstatus($_G['member']['allowadmincp'], 4) || getstatus($_G['member']['allowadmincp'], 5) || getstatus($_G['member']['allowadmincp'], 6);
if($_G['setting']['portalstatus']) {
	$allowpostarticle = $_G['group']['allowmanagearticle'] || $_G['group']['allowpostarticle'] || getstatus($_G['member']['allowadmincp'], 2) || getstatus($_G['member']['allowadmincp'], 3);
	$allowpusharticle = empty($_G['forum_thread']['special']) && empty($_G['forum_thread']['sortid']) && !$_G['forum_thread']['pushedaid'];
} else {
	$allowpostarticle = $allowpusharticle = false;
}

if($_G['forum_thread']['displayorder'] != -4) {
	$modmenu = array(
		'thread' => $_G['forum']['ismoderator'] || $allowblockrecommend || $allowpusharticle && $allowpostarticle,
		'post' => $_G['forum']['ismoderator'] && ($_G['group']['allowwarnpost'] || $_G['group']['allowbanpost'] || $_G['group']['allowdelpost'] || $_G['group']['allowstickreply']) || $_G['forum_thread']['pushedaid'] && $allowpostarticle || $_G['forum_thread']['authorid'] == $_G['uid']
	);
} else {
	$modmenu = array();
}

if($_G['forum']['alloweditpost'] && $_G['uid']) {
	$alloweditpost_status = getstatus($_G['setting']['alloweditpost'], $_G['forum_thread']['special'] + 1);
	if(!$alloweditpost_status) {
		$edittimelimit = $_G['group']['edittimelimit'] * 60;
	}
}

if($_G['forum_thread']['replies'] > $_G['forum_thread']['views']) {
	$_G['forum_thread']['views'] = $_G['forum_thread']['replies'];
}

require_once libfile('function/upload');
$swfconfig = getuploadconfig($_G['uid'], $_G['fid']);
$_G['forum_thread']['relay'] = 0;

if(getstatus($_G['forum_thread']['status'], 10)) {
	$preview = C::t('forum_threadpreview')->fetch($_G['tid']);
	$_G['forum_thread']['relay'] = $preview['relay'];
}

$sharetext = '';
$sharelink = $_G['baseurl'].$_G['minbbs_config']['minbbs_type'].'/index.php?mod=viewthread&tid='.$_G['forum_thread']['tid']."&viewthread_share=1&isshare=1&version=".$_G['minbbs_config']['version'];
$shareicon = '';
// 处理帖子中的附件

foreach($postlist as $pid => $post) {
//    print_R($post);exit;
//        帖子来源
//        $dateline=strtotime($post['dateline']);
//        $postlist[$pid]['dateline']=date('Y-m-d', $dateline);
	$query = DB::query('SELECT * FROM '.DB::table('forum_devicetype').' WHERE pid='.$post['pid']);
	$devicetype = DB::fetch($query);
        if($devicetype['devicetype']==1){
            $devicetype['devicetype']='iPhone';
        }else if($devicetype['devicetype']==2){
            $devicetype['devicetype']='Android';
        }else{
            $devicetype['devicetype']='';
        }
	$postlist[$pid]['devicetype'] = !empty($devicetype) ? $devicetype['devicetype'] : 0;
	if($post['first']) {
            $list_pid=$post['pid'];
            //start 贴子详情文字链接处理
            preg_match_all('/<a href=\"(.*?)\".*?>(.*?)<\/a>/i', $post['message'], $matchs);
            $tothread=tothread($matchs['1']);
            foreach($tothread as $key=>$thread_a){
                $thread_a = json_encode($thread_a);
                $post['message']=str_replace('href="'.$matchs['1'][$key].'"', "onclick='tothread(".$thread_a.")'", $post['message']);
            }
            $postlist[$pid]['message']=$post['message'];
            //end 贴子详情文字链接处理
            $postlist[$pid]['annexlist']=$annexlist;
            $sharetext = odz_lang('biu_sharetext');
            $attachmentsquery = C::t('forum_attachment_n')->fetch_all_by_id('tid:'.$_G['forum_thread']['tid'], 'tid', $_G['forum_thread']['tid']);
            foreach($attachmentsquery as $aid => $attach) {
                if ($attach['isimage'] != 0) {
                        $thumb = ($attach['remote'] ? $_G['setting']['ftp']['attachurl'] : $_G['setting']['attachurl']) . 'forum/' . $attach['attachment'] . ($attach['thumb'] ? '.thumb.jpg' : '');
                        $annexlist['thumb'][$aid] =$_G['baseurl'].$thumb;
                }
                if ($attach['isimage'] == 0) {
                        $sound = ($attach['remote'] ? $_G['setting']['ftp']['attachurl'] : $_G['setting']['attachurl']) . 'forum/' . $attach['attachment'] . ($attach['thumb'] ? '' : '');
                        $annexlist['sound'] =$_G['baseurl'].$sound;
                }
            }
            // 获取收藏判断是否收藏
            $favid = 0;
            $favorite = C::t('home_favorite')->fetch_by_id_idtype($post['tid'], 'tid', $_G['uid']);
            if($favorite) {
                $favid = $favorite['favid'];
            }
            $postlist[$pid]['favid'] = $favid;

            //start点赞功能
            if(!empty($_G['uid'])){//点赞
                $query_praid = C::t('home_praise')->fetch_by_id_idtype($_G['forum_thread']['tid'],'tid', $_G['uid']);
                $postlist[$pid]['praid'] = isset($query_praid['praid'])?$query_praid['praid']:"";
            }
            $postlist[$pid]['praisenum'] = C::t('home_praise_total')->fetch_by_id_idtype($_G['forum_thread']['tid'],'tid');
        }else{
            if(!empty($_G['uid'])){//点赞
                $query_praid = C::t('home_praise')->fetch_by_id_idtype($post['pid'],'pid', $_G['uid']);
                $postlist[$pid]['praid'] = isset($query_praid['praid'])?$query_praid['praid']:"";
            }
            $postlist[$pid]['praisenum'] = C::t('home_praise_total')->fetch_by_id_idtype($post['pid'],'pid');
        }
        if($postlist[$pid]['praisenum']>999){
            $postlist[$pid]['praisenum']="999+";
        }
        //处理禁言用户组
	if(!$_G['forum']['ismoderator'] && $_G['setting']['bannedmessages'] & 1 && (($post['authorid'] && !$post['username']) || ($_G['thread']['digest'] == 0 && ($post['groupid'] == 4 || $post['groupid'] == 5 || $post['memberstatus'] == '-1')))) {
			$message1 = lang('forum/template', 'message_banned');
	} elseif(!$_G['forum']['ismoderator'] && $post['status'] & 1) {
			$message1 = lang('forum/template', 'message_single_banned');
	} elseif($GLOBALS['needhiddenreply']) {
			$message1 = lang('forum/template', 'message_ishidden_hiddenreplies');
	} elseif($post['first'] && $_G['forum_threadpay']) {
			$message1 = lang('forum/template', 'pay_threads').' '.$GLOBALS['thread']['price'].' '.$_G['setting']['extcredits'][$_G['setting']['creditstransextra'][1]]['unit'].$_G['setting']['extcredits'][$_G['setting']['creditstransextra'][1]]['title'];
	} elseif($_G['forum_discuzcode']['passwordlock']) {
			$message1 = lang('forum/template', 'message_password_exists');
	} else {
			$message1 = '';
	}
	if($message1){
		$post['message']='<div style="color:gray;">'.$message1.'</div>';
                $postlist[$pid] = $post;
                continue;
	}
        //end//处理禁言用户组
        if(empty($post['imagelist'])) {
            continue;
        }
        $imagearr = array();
        foreach($post['imagelist'] as $aid) {
            if(!array_key_exists($aid, $post['attachments'])) {
                continue;
            }
            $attachs = $post['attachments'][$aid];
            if($_GET['ishtml']) {
                $attachs['size_ex'] = null;
            }
            if(stripos($attachs['url_ex'], 'thumb.jpg')){
                $big_url=str_replace('.thumb.jpg','',$attachs['url_ex'] );
            }

           //array_push($imagearr, "<p><img  src=\"".$attachs['url_ex']."\" class='img_detail' big_url='$big_url'".(!empty($attachs['size_ex']) ? $attachs['size_ex'] : '')." data-original=\"".$attachs['url_ex']."\" src=\"assets/images/bitmap.png\" /></p>");
			array_push($imagearr, "<p><img  src=\"".$attachs['url_ex']."\" class='img_detail' big_url='$big_url'".(!empty($attachs['size_ex']) ? $attachs['size_ex'] : '')." data-original=\"".$attachs['url_ex']."\" /></p>");
        }
        $postlist[$pid]['message'] .= "\n".implode("\n", $imagearr);
    }
    //打赏相关数据
   // $reward_view = reward_info();
   // $reward_totals = reward_totals($_G['forum_thread']['tid']);
//    print_R($reward_totals);exit;
    if ($_G['forum_thread']['replies'] > $_G['ppp']) {
        $replies_pagecount = @ceil($_G['forum_thread']['replies'] / $_G['ppp']);
    }
    if($replies_pagecount==''){$replies_pagecount=1;}
    //关注好友
    $follow_friend=DB::result_first('SELECT COUNT(*) FROM '.DB::table('home_follow_friend')." WHERE uid = '$_G[uid]' and fuid= ".$_G['forum_thread']['authorid']);
    //根据点赞-热门评论
    $forum_post_all = 'SELECT b.num ,a.* FROM '.DB::table('home_praise_total').' as b '
            . 'left JOIN '.DB::table('forum_post')." as a  on a.first = 0  and a.pid=b.tid"
            . " WHERE b.idtype = 'pid'and a.tid = ". $_G['forum_thread']['tid'].' '
            . ' ORDER BY b.num DESC limit 0,5 ';
    $forum_list = DB::fetch_all($forum_post_all);
    $sort = array(
        'direction' => 'SORT_DESC', //排序顺序标志 SORT_DESC 降序；SORT_ASC 升序
        'field'     => 'praisenum',       //排序字段
    );
    $arrSort = array();

    require_once libfile('function/attachment');
    require_once 'includes/functions/attachment.php';
    foreach($forum_list AS $uniqid => $rowa){
        $rowa=viewthread_procpost($rowa);
        odz_parseattach($rowa['pid'], '', $rowa);
        $first=$rowa[$rowa['pid']]['attachments'];
        $imagearr = array();
        foreach($first as $attachs) {
//         echo '<pre>';print_R($aid);exit;
           $url_ex = $attachs['url_ex'];
            if(stripos($attachs['url_ex'], 'thumb.jpg')){
                $big_url=str_replace('.thumb.jpg','',$attachs['url_ex']);
            }
            // array_push($imagearr, "<p><img  src=\"".$attachs['url_ex']."\" class='img_detail' big_url='$big_url'".(!empty($attachs['size_ex']) ? $attachs['size_ex'] : '')." data-original=\"".$attachs['url_ex']."\" src=\"assets/images/bitmap.png\" /></p>");
			 array_push($imagearr, "<p><img  src=\"".$attachs['url_ex']."\" class='img_detail' big_url='$big_url'".(!empty($attachs['size_ex']) ? $attachs['size_ex'] : '')." data-original=\"".$attachs['url_ex']."\"  /></p>");
        }

        $rowa['message'] .= "\n".implode("\n", $imagearr);
        $rowa['username']=$rowa['author'];
        $rowa['avatar'] = avatar($rowa['authorid'], 'small');
        if(!empty($_G['uid'])){//点赞
            $query_praid = C::t('home_praise')->fetch_by_id_idtype($rowa['pid'],'pid', $_G['uid']);
            $rowa['praid'] = isset($query_praid['praid'])?$query_praid['praid']:"";
        }
        $rowa['praisenum']=$rowa['num'];
        if($rowa['first']||!$rowa['praisenum']){
            continue;
        }
        $hot_replylist[]=$rowa;
        foreach($rowa AS $key=>$value){
            $arrSort[$key][$uniqid] = $value;
        }
    }
    if($sort['direction']){
        array_multisort($arrSort[$sort['field']], constant($sort['direction']), $hot_replylist);
    }


    // h5贴子信息详情页面
    //author wzf start 论坛h5
    if(!empty($_GET['item_view'])){
            //h5贴子
            $user_agent = $_SERVER['HTTP_USER_AGENT'];
            $weixin=strpos($user_agent, 'MicroMessenger');
            if($weixin){
                $weixin='report-advert';
            }else{
                $weixin='';
            }
            $forum_post =DB::fetch_first('SELECT * FROM '.DB::table('forum_post').' WHERE tid = '.$_G['tid']);
            header('Content-Type:text/html; charset='.CHARSET);
            include './template/viewthread_reply.php';
            exit;
    }
    // h5贴子信息详情分享出去的页面
    if(!empty($_GET['viewthread_share'])){

		//精彩推荐

    $time = strtotime(date('Y-m-d', strtotime('-30 days')));

    $result = DB::fetch_all("SELECT * FROM ".DB::table('forum_thread')." WHERE `fid`='".$_G['fid']."' AND `displayorder` IN('0','1','2','3','4') AND `dateline` > ".$time." ORDER BY displayorder DESC, heats DESC LIMIT 40");
			foreach($result as $key => $val){
				$result[$key]['dateline'] = date("Y-m-d H:i:s",$val['dateline']);
				$result[$key]['thumb'] = '';
				$attachmentsquery = C::t('forum_attachment_n')->fetch_all_by_id('tid:'.$val['tid'], 'tid', $val['tid']);
				foreach($attachmentsquery as $aid => $attach) {
					$result[$key]['thumb'] = '';
					if ($attach['isimage'] != 0) {
							$thumb = ($attach['remote'] ? $_G['setting']['ftp']['attachurl'] : $_G['setting']['attachurl']) . 'forum/' . $attach['attachment'] . ($attach['thumb'] ? '.thumb.jpg' : '');
							$result[$key]['thumb'] =$_G['baseurl'].$thumb;
					}

				}
			}


    if($result && count($result) > 10){
      $output = array_rand($result,10);
      foreach($output as $key => $val){
        $outputs[] = $result[$output[$key]];
      }
    }else{
      $outputs = $result;
    }

		include './template/viewthread_share.php';
		exit;
    }
    //h5贴子
    if(!empty($_GET['ishtml'])){
            $odz_advert = odz_getadvertlist('threadinfo');

            include './template/h5_view.php';
            exit;
    }
//组织返回数据
$tmp_postlist = array();
foreach($postlist as $post) {
    // 对帖子内容做最后处理
    $post['message'] = odz_strip_tags($post['message']);
	
	$post['message_all'] = $post['message'];
	
	if($post['first'] == 0){
		$post['message'] = strip_tags($post['message']);
	}
	if(strlen($post['message']) == 1){
		$post['message'] = iconv('UTF-8','GB2312','不支持查看的内容，请到网页端查看');
	}
	
    $perms = array('allowpostreply'=>false, 'alloweditpost'=>false, 'allowdelpost'=>false, 'allowpostreview'=>false);
    // 回复帖子
    if((!$_G['uid'] || $allowpostreply) && !$needhiddenreply) {
        $perms['allowpostreply'] = true;
    }
    // 编辑帖子
    if(($_G['forum']['ismoderator'] && $_G['group']['alloweditpost'] && (!in_array($post['adminid'], array(1, 2, 3)) || $_G['adminid'] <= $post['adminid']))) {
        $perms['alloweditpost'] = true;
    }
    // 删除主题
    if($modmenu['thread']) {
        $perms['allowdelpost'] = true;
    }
    // 删除帖子
    if(!$post['first'] && $modmenu['post']) {
        $perms['allowdelpost'] = true;
    }
    // 帖子点评
    if(!$_G['forum_thread']['special'] && !$rushreply && !$hiddenreplies && $_G['setting']['repliesrank']) {
        $perms['allowpostreview'] = true;
    }
    $tmp_postlist[] = array(
        'pid' => $post['pid'],
        'fid' => $post['fid'],
        'tid' => $post['tid'],
        'first' => $post['first'],
		'favid' => $post['favid'],
        'author' => $post['author'],
        'authorid' => $post['authorid'],
        'avatar' => avatar($post['authorid'], 'small', true),
        'authortitle' => strip_tags($post['authortitle']),
        'subject' => $post['subject'],
        'dateline' => $post['dateline'],
        'anonymous' => $post['anonymous'],
        'message' => odz_format_message($post['message']),
		'message_all' => odz_format_message($post['message_all']),
		'annexlist' => $post['imagelist'],
        //'attachments' => $attachments,
        // 'comments' => odz_getcomments($post['pid']),
        'floortitle' => (!empty($postno[$post['number']]) ? $postno[$post['number']] : $post['number'].odz_lang('floor_title')),
        'perms' => $perms,
		'videolist' => $post['videolist']
    );
}
unset($postlist, $comments);
$sticklist = odz_getsticks();
// 主题分类
$_G['forum_thread']['typehtml'] = '';
if ($_G['forum_thread']['typeid'] && $_G['forum']['threadtypes']['types'][$_G['forum_thread']['typeid']]) {
	$_G['forum_thread']['typehtml'] = '['.$_G['forum']['threadtypes']['types'][$_G['forum_thread']['typeid']].']';
}

/*--输出分类信息--*/
if ($threadsort && $threadsortshow && $threadsortshow['optionlist'] && $threadsortshow['optionlist'] != 'expire') {
	$typevalue['name'] = strip_tags($_G['forum']['threadsorts']['types'][$_G['forum_thread']['sortid']]);
	foreach($threadsortshow['optionlist'] as $key=>$option) {
		if ($option['type'] != 'info') {
			
			$type['title'] = $option['title'];
			$type['value'] = $option['value'] !== '' ? $option['value'].' '.$option['unit'] : '-';
		}
		$typevalue['value'][] =$type; 
	}
	
}

odz_result(array(
    'total' => ($_G['forum_thread']['replies'] > 0 ? $_G['forum_thread']['replies'] : 0) + 1,
    'perpage' => $_G['ppp'],
    'page' => $page,
    'sticklist' => $sticklist,
    'thread' => array(
        'tid' => $_G['forum_thread']['tid'],
        'fid' => $_G['forum_thread']['fid'],
        'author' => $_G['forum_thread']['author'],
        'authorid' => $_G['forum_thread']['authorid'],
        'subject' => odz_format_subject($_G['forum_thread']['subject']),
        'views' => strval($_G['forum_thread']['views']),
        'replies' => strval($_G['forum_thread']['replies']),
        'attachment' => $_G['forum_thread']['attachment'],
        'recommend_add' => $_G['forum_thread']['recommend_add'],
        'recommend_sub' => $_G['forum_thread']['recommend_sub'],
        'favtimes' => $_G['forum_thread']['favtimes'],
        'sharetimes' => $_G['forum_thread']['sharetimes'],
        'relay' => $_G['forum_thread']['relay'],
        'typehtml' => strip_tags($_G['forum_thread']['typehtml']),
        'sharelink' => $sharelink,
        'sharetext' => odz_format_sharetext($sharetext), // 分享内容
		'typevalue' => $typevalue,
	'forumname' => $forum['name']
    ),
	'postlist' => $tmp_postlist
));

/**
 * 格式化分享内容
 * @param $sharetext
 * @return mixed|string
 */
function odz_format_sharetext($sharetext) {
    $sharetext = strip_tags($sharetext);

    $sharetext = preg_replace('/(((&nbsp;|&quot;)(\s+)?)+)/', '', $sharetext);
    $sharetext = preg_replace("/[\r\n]/", '', $sharetext);
    return $sharetext;
}

function odz_getsticks()
{
    global $sticklist;

    $posts = array();
    foreach($sticklist as $post) {
        $posts[] = array(
            'pid' => $post['pid'],
            'author' => $post['author'],
            'authorid' => $post['authorid'],
            'subject' => $post['subject'],
            'dateline' => dgmdate($post['dateline']),
            'message' => $post['message'],
            'avatar' => avatar($post['authorid'], 'small', true)
        );
    }

    return $posts;
}

function odz_getcomments($pid)
{
    global $_G, $comments, $commentcount;

    $commentlimit = intval($_G['setting']['commentnumber']);

    $commentlist = array();
    if(!isset($comments[$pid])) {
        return $commentlist;
    }

    foreach($comments[$pid] as $comment) {
        $commentlist[] = array(
            'author' => $comment['author'],
            'authorid' => $comment['authorid'],
            'dateline' => dgmdate($comment['dateline']),
            'comment' => $comment['comment'],
            'avatar' => avatar($comment['authorid'], 'small', true)
        );
    }

    return array('total'=>$commentcount[$pid], 'perpage'=>$commentlimit, 'comments'=>$commentlist);
}

function viewthread_updateviews($tableid) {
	global $_G;
	if(!$_G['setting']['preventrefresh'] || $_G['cookie']['viewid'] != 'tid_'.$_G['tid']) {
		if(!$tableid && $_G['setting']['optimizeviews']) {
			if($_G['forum_thread']['addviews']) {
				if($_G['forum_thread']['addviews'] < 100) {
					C::t('forum_threadaddviews')->update_by_tid($_G['tid']);
				} else {
					if(!discuz_process::islocked('update_thread_view')) {
						$row = C::t('forum_threadaddviews')->fetch($_G['tid']);
						C::t('forum_threadaddviews')->update($_G['tid'], array('addviews' => 0));
						C::t('forum_thread')->increase($_G['tid'], array('views' => $row['addviews']+1), true);
						discuz_process::unlock('update_thread_view');
					}
				}
			} else {
				C::t('forum_threadaddviews')->insert(array('tid' => $_G['tid'], 'addviews' => 1), false, true);
			}
		} else {
			C::t('forum_thread')->increase($_G['tid'], array('views' => 1), true, $tableid);
		}
	}
	dsetcookie('viewid', 'tid_'.$_G['tid']);
}

function viewthread_procpost($post, $lastvisit, $ordertype, $maxposition = 0) {
	global $_G, $rushreply;
	if(!$_G['forum_newpostanchor'] && $post['dateline'] > $lastvisit) {
		$post['newpostanchor'] = '<a name="newpost"></a>';
		$_G['forum_newpostanchor'] = 1;
	} else {
		$post['newpostanchor'] = '';
	}

	$post['lastpostanchor'] = ($ordertype != 1 && $_G['forum_numpost'] == $_G['forum_thread']['replies']) || ($ordertype == 1 && $_G['forum_numpost'] == $_G['forum_thread']['replies'] + 2) ? '<a name="lastpost"></a>' : '';

	if($_G['forum_pagebydesc']) {
		if($ordertype != 1) {
			$post['number'] = $_G['forum_numpost'] + $_G['forum_ppp2']--;
		} else {
			$post['number'] = $post['first'] == 1 ? 1 : ($_G['forum_numpost'] - 1) - $_G['forum_ppp2']--;
		}
	} else {
		if($ordertype != 1) {
			$post['number'] = ++$_G['forum_numpost'];
		} else {
			$post['number'] = $post['first'] == 1 ? 1 : --$_G['forum_numpost'];
			$post['number'] = $post['number'] - 1;
		}
	}

	if($maxposition) {
		$post['number'] = $post['position'];
	}
	$_G['forum_postcount']++;

	$post['dbdateline'] = $post['dateline'];
	if($_GET['viewthread_share']){
		if($post['first']){
			$post['dateline'] = date("Y-m-d",$post['dateline']);
		}else{
			$post['dateline'] = date("Y-m-d H:i:s",$post['dateline']);
		}
	}else{
		$post['dateline'] = dgmdate($post['dateline'], 'u', '9999', getglobal('setting/dateformat').' H:i:s');
	}

	$post['groupid'] = $_G['cache']['usergroups'][$post['groupid']] ? $post['groupid'] : 7;

	if($post['username']) {
		$_G['forum_onlineauthors'][$post['authorid']] = 0;
		$post['usernameenc'] = rawurlencode($post['username']);
		$post['readaccess'] = $_G['cache']['usergroups'][$post['groupid']]['readaccess'];
		if($_G['cache']['usergroups'][$post['groupid']]['userstatusby'] == 1) {
			$post['authortitle'] = $_G['cache']['usergroups'][$post['groupid']]['grouptitle'];
			$post['stars'] = $_G['cache']['usergroups'][$post['groupid']]['stars'];
		}
		$post['upgradecredit'] = false;
		if($_G['cache']['usergroups'][$post['groupid']]['type'] == 'member' && $_G['cache']['usergroups'][$post['groupid']]['creditslower'] != 999999999) {
			$post['upgradecredit'] = $_G['cache']['usergroups'][$post['groupid']]['creditslower'] - $post['credits'];
		}

		$post['taobaoas'] = addslashes($post['taobao']);
		$post['regdate'] = dgmdate($post['regdate'], 'd');
		$post['lastdate'] = dgmdate($post['lastvisit'], 'd');
		$post['authoras'] = !$post['anonymous'] ? ' '.addslashes($post['author']) : '';

		if($post['medals']) {
                        loadcache('medals');
                        foreach($post['medals'] = explode("\t", $post['medals']) as $key => $medalid) {
                                list($medalid, $medalexpiration) = explode("|", $medalid);
                                if(isset($_G['cache']['medals'][$medalid]) && (!$medalexpiration || $medalexpiration > TIMESTAMP)) {
                                        $post['medals'][$key] = $_G['cache']['medals'][$medalid];
                                        $post['medals'][$key]['medalid'] = $medalid;
                                        $_G['medal_list'][$medalid] = $_G['cache']['medals'][$medalid];
                                } else {
                                        unset($post['medals'][$key]);
                                }
                        }
		}

		$post['avatar'] = avatar($post['authorid']);
		$post['groupicon'] = $post['avatar'] ? g_icon($post['groupid'], 1) : '';
		$post['banned'] = $post['status'] & 1;
		$post['warned'] = ($post['status'] & 2) >> 1;

	} else {
		if(!$post['authorid']) {
			$post['useip'] = substr($post['useip'], 0, strrpos($post['useip'], '.')).'.x';
		}
	}
	$post['attachments'] = array();
	$post['imagelist'] = $post['attachlist'] = '';
	if($post['attachment']) {
		if($_G['group']['allowgetattach'] || $_G['group']['allowgetimage']) {
			$_G['forum_attachpids'][] = $post['pid'];
			$post['attachment'] = 0;
			if(preg_match_all("/\[attach\](\d+)\[\/attach\]/i", $post['message'], $matchaids)) {
				$_G['forum_attachtags'][$post['pid']] = $matchaids[1];
			}
		} else {
			$post['message'] = preg_replace("/\[attach\](\d+)\[\/attach\]/i", '', $post['message']);
		}
	}

	if($_G['setting']['ratelogrecord'] && $post['ratetimes']) {
		$_G['forum_cachepid'][$post['pid']] = $post['pid'];
	}
	if($_G['setting']['commentnumber'] && ($post['first'] && $_G['setting']['commentfirstpost'] || !$post['first']) && $post['comment']) {
		$_G['forum_cachepid'][$post['pid']] = $post['pid'];
	}
	$post['allowcomment'] = $_G['setting']['commentnumber'] && in_array(1, $_G['setting']['allowpostcomment']) && ($_G['setting']['commentpostself'] || $post['authorid'] != $_G['uid']) &&
		($post['first'] && $_G['setting']['commentfirstpost'] && in_array($_G['group']['allowcommentpost'], array(1, 3)) ||
		(!$post['first'] && in_array($_G['group']['allowcommentpost'], array(2, 3))));
	$forum_allowbbcode = $_G['forum']['allowbbcode'] ? -$post['groupid'] : 0;
	$post['signature'] = $post['usesig'] ? ($_G['setting']['sigviewcond'] ? (strlen($post['message']) > $_G['setting']['sigviewcond'] ? $post['signature'] : '') : $post['signature']) : '';
	if(!defined('IN_ARCHIVER')) {
            $post['message'] = preg_replace('/\[appid\].*?\[\/appid\]/', '', $post['message']);
            // 解析帖子中的视频地址
            $post['videolist'] = array();
            $videourllist = array();
            if(preg_match_all('/\[(flash|media|audio)[^\]]*?\]([\s\S]+?)\[\/\1\]/is', $post['message'], $matches)) {
                foreach($matches[2] as $key => $videourl) {
                    $videourllist[] = $videourl = urlencode($videourl);
                    $post['message'] = str_replace($matches[0][$key], '#########', $post['message']);
                }
            }
            $jammerflag = 0; // 是否启动帖子干扰码
            $post['message'] = odz_discuzcode($post['message'], $post['smileyoff'], $post['bbcodeoff'], $post['htmlon'] & 1, $_G['forum']['allowsmilies'], $forum_allowbbcode, ($_G['forum']['allowimgcode'] && $_G['setting']['showimages'] ? 1 : 0), $_G['forum']['allowhtml'], $jammerflag, 0, $post['authorid'], $_G['cache']['usergroups'][$post['groupid']]['allowmediacode'] && $_G['forum']['allowmediacode'], $post['pid'], false, $post['dbdateline']);
            //贴子图片超链接屏蔽
            $post['message'] = preg_replace('/<a href=\"(.*?)\" target=\"_blank\"><img/', '<img', $post['message']);
            //签到链接详情过滤
            $post['message'] = preg_replace('/<font color=\"darkorange">(.*?)<\/font><\/a><font/', '</a><font', $post['message']);
            //电话号码处理
//             $regxArr = array(
//                 'sj'  =>  '/(\+?86-?)?(18|15|13)[0-9]{9}/i',
//                 'tel' =>  '/(010|02\d{1}|0[3-9]\d{2})-\d{7,9}(-\d+)?/i',
//                 '400' =>  '/400(-\d{3,4}){2}/i',
//             );
//             // $videourllist = array();
//             foreach($regxArr as $regx){
//                 if(preg_match_all("$regx", $post['message'], $mobiles)){
//                     foreach($mobiles[0] as $vl){
//                         $numbers[]=$vl;
//                     }
//                 }
//             }

//             foreach($numbers as $key => $val) {
//                         $numberhtml =<<<html
//                             <a href=tel:$val>$val</a>
// html;
//                         if($val!=$number){
//                             $post['message'] = str_replace($val, $numberhtml, $post['message']);
//                         }
//                         $number=$val;
//             }
            //电话号码处理end
            // 视频处理
            if(preg_match_all('/#########/', $post['message'], $matches)){
            	$loding = odz_lang('loding');
            	foreach($matches[0] as $key => $val) {
            		$videohtml =<<<html
                                    <div class="videowrap">
                                    <div class="videocontainer"  videourl="{$videourllist[$key]}"><div class="video_text">{$loding}...</div></div></div>
html;
            		$post['message'] = preg_replace('/#########/', $videohtml, $post['message'],true);
            	}
            }
        // $post['message'] = odz_strip_tags($post['message']);
            if($post['first']) {
                    $_G['relatedlinks'] = '';
                    $relatedtype = !$_G['forum_thread']['isgroup'] ? 'forum' : 'group';
                    if(!$_G['setting']['relatedlinkstatus']) {
                            $_G['relatedlinks'] = get_related_link($relatedtype);
                    } else {
                            $post['message'] = parse_related_link($post['message'], $relatedtype);
                    }
            }
	}

	// 解析帖子内容中的soso表情
	$post['message'] = preg_replace("/\{:soso_((e\d+)|_\d+_\d):\}/e", 'odz_soso_smiles("\\1")', $post['message']);
    // 回复贴字体样式过滤
    $post['message'] = reply_filter_style($post);
	$_G['forum_firstpid'] = intval($_G['forum_firstpid']);
	$post['custominfo'] = viewthread_custominfo($post);
	$post['mobiletype'] = getstatus($post['status'], 4) ? base_convert(getstatus($post['status'], 10).getstatus($post['status'], 9).getstatus($post['status'], 8), 2, 10) : 0;
        return $post;
}


function viewthread_loadcache() {
	global $_G;
	$_G['forum']['livedays'] = ceil((TIMESTAMP - $_G['forum']['dateline']) / 86400);
	$_G['forum']['lastpostdays'] = ceil((TIMESTAMP - $_G['forum']['lastthreadpost']) / 86400);
	$threadcachemark = 100 - (
	$_G['forum']['displayorder'] * 15 +
	$_G['thread']['digest'] * 10 +
	min($_G['thread']['views'] / max($_G['forum']['livedays'], 10) * 2, 50) +
	max(-10, (15 - $_G['forum']['lastpostdays'])) +
	min($_G['thread']['replies'] / $_G['setting']['postperpage'] * 1.5, 15));
	if($threadcachemark < $_G['forum']['threadcaches']) {

		$threadcache = getcacheinfo($_G['tid']);

		if(TIMESTAMP - $threadcache['filemtime'] > $_G['setting']['cachethreadlife']) {
			@unlink($threadcache['filename']);
			define('CACHE_FILE', $threadcache['filename']);
		} else {
			readfile($threadcache['filename']);

			viewthread_updateviews($_G['forum_thread']['threadtableid']);
			$_G['setting']['debug'] && debuginfo();
			$_G['setting']['debug'] ? die('<script type="text/javascript">document.getElementById("debuginfo").innerHTML = " '.($_G['setting']['debug'] ? 'Updated at '.gmdate("H:i:s", $threadcache['filemtime'] + 3600 * 8).', Processed in '.$debuginfo['time'].' second(s), '.$debuginfo['queries'].' Queries'.($_G['gzipcompress'] ? ', Gzip enabled' : '') : '').'";</script>') : die();
		}
	}
}

function viewthread_lastmod(&$thread) {
	global $_G;
	if(!$thread['moderated']) {
		return array();
	}
	$lastmod = array();
	$lastlog = C::t('forum_threadmod')->fetch_by_tid($thread['tid']);
	if($lastlog) {
		$lastmod = array(
					'moduid' => $lastlog['uid'],
					'modusername' => $lastlog['username'],
					'moddateline' => $lastlog['dateline'],
					'modaction' => $lastlog['action'],
					'magicid' => $lastlog['magicid'],
					'stamp' => $lastlog['stamp'],
					'reason' => $lastlog['reason']
				);
	}
	if($lastmod) {
		$modactioncode = lang('forum/modaction');
		$lastmod['modusername'] = $lastmod['modusername'] ? $lastmod['modusername'] : 'System';
		$lastmod['moddateline'] = dgmdate($lastmod['moddateline'], 'u');
		$lastmod['modactiontype'] = $lastmod['modaction'];
		if($modactioncode[$lastmod['modaction']]) {
			$lastmod['modaction'] = $modactioncode[$lastmod['modaction']].($lastmod['modaction'] != 'SPA' ? '' : ' '.$_G['cache']['stamps'][$lastmod['stamp']]['text']);
		} elseif(substr($lastmod['modaction'], 0, 1) == 'L' && preg_match('/L(\d\d)/', $lastmod['modaction'], $a)) {
			$lastmod['modaction'] = $modactioncode['SLA'].' '.$_G['cache']['stamps'][intval($a[1])]['text'];
		} else {
			$lastmod['modaction'] = '';
		}
		if($lastmod['magicid']) {
			loadcache('magics');
			$lastmod['magicname'] = $_G['cache']['magics'][$lastmod['magicid']]['name'];
		}
	} else {
		C::t('forum_thread')->update($thread['tid'], array('moderated' => 0), false, false, $thread['threadtableid']);
		$thread['moderated'] = 0;
	}
	return $lastmod;
}

function viewthread_custominfo($post) {
	global $_G;

	$types = array('left', 'menu');
	foreach($types as $type) {
		if(!is_array($_G['cache']['custominfo']['setting'][$type])) {
			continue;
		}
		$data = '';
		foreach($_G['cache']['custominfo']['setting'][$type] as $key => $order) {
			$v = '';
			if(substr($key, 0, 10) == 'extcredits') {
				$i = substr($key, 10);
				$extcredit = $_G['setting']['extcredits'][$i];
				$v = '<dt>'.($extcredit['img'] ? $extcredit['img'].' ' : '').$extcredit['title'].'</dt><dd>'.$post['extcredits'.$i].' '.$extcredit['unit'].'</dd>';
			} elseif(substr($key, 0, 6) == 'field_') {
				$field = substr($key, 6);
				if(!empty($post['privacy']['profile'][$field])) {
					continue;
				}
				require_once libfile('function/profile');
				$v = profile_show($field, $post);
				if($v) {
					$v = '<dt>'.$_G['cache']['custominfo']['profile'][$key][0].'</dt><dd title="'.dhtmlspecialchars(strip_tags($v)).'">'.$v.'</dd>';
				}
			} elseif($key == 'creditinfo') {
				$v = '<dt>'.lang('space', 'viewthread_userinfo_buyercredit').'</dt><dd><a href="home.php?mod=space&uid='.$post['uid'].'&do=trade&view=eccredit#buyercredit" target="_blank" class="vm"><img src="'.STATICURL.'image/traderank/seller/'.countlevel($post['buyercredit']).'.gif" /></a></dd>';
				$v .= '<dt>'.lang('space', 'viewthread_userinfo_sellercredit').'</dt><dd><a href="home.php?mod=space&uid='.$post['uid'].'&do=trade&view=eccredit#sellercredit" target="_blank" class="vm"><img src="'.STATICURL.'image/traderank/seller/'.countlevel($post['sellercredit']).'.gif" /></a></dd>';
			} else {
				switch($key) {
					case 'uid': $v = $post['uid'];break;
					case 'posts': $v = '<a href="home.php?mod=space&uid='.$post['uid'].'&do=thread&type=reply&view=me&from=space" target="_blank" class="xi2">'.$post['posts'].'</a>';break;
					case 'threads': $v = '<a href="home.php?mod=space&uid='.$post['uid'].'&do=thread&type=thread&view=me&from=space" target="_blank" class="xi2">'.$post['threads'].'</a>';break;
					case 'doings': $v = '<a href="home.php?mod=space&uid='.$post['uid'].'&do=doing&view=me&from=space" target="_blank" class="xi2">'.$post['doings'].'</a>';break;
					case 'blogs': $v = '<a href="home.php?mod=space&uid='.$post['uid'].'&do=blog&view=me&from=space" target="_blank" class="xi2">'.$post['blogs'].'</a>';break;
					case 'albums': $v = '<a href="home.php?mod=space&uid='.$post['uid'].'&do=album&view=me&from=space" target="_blank" class="xi2">'.$post['albums'].'</a>';break;
					case 'sharings': $v = '<a href="home.php?mod=space&uid='.$post['uid'].'&do=share&view=me&from=space" target="_blank" class="xi2">'.$post['sharings'].'</a>';break;
					case 'friends': $v = '<a href="home.php?mod=space&uid='.$post['uid'].'&do=friend&view=me&from=space" target="_blank" class="xi2">'.$post['friends'].'</a>';break;
					case 'follower': $v = '<a href="home.php?mod=follow&do=follower&uid='.$post['uid'].'" target="_blank" class="xi2">'.$post['follower'].'</a>';break;
					case 'following': $v = '<a href="home.php?mod=follow&do=following&uid='.$post['uid'].'" target="_blank" class="xi2">'.$post['following'].'</a>';break;
					case 'digest': $v = $post['digestposts'];break;
					case 'credits': $v = $post['credits'];break;
					case 'readperm': $v = $post['readaccess'];break;
					case 'regtime': $v = $post['regdate'];break;
					case 'lastdate': $v = $post['lastdate'];break;
					case 'oltime': $v = $post['oltime'].' '.lang('space', 'viewthread_userinfo_hour');break;
				}
				if($v !== '') {
					$v = '<dt>'.lang('space', 'viewthread_userinfo_'.$key).'</dt><dd>'.$v.'</dd>';
				}
			}
			$data .= $v;
		}
		$return[$type] = $data;
	}
	return $return;
}
function countlevel($usercredit) {
	global $_G;

	$rank = 0;
	if($usercredit){
		foreach($_G['setting']['ec_credit']['rank'] AS $level => $credit) {
			if($usercredit <= $credit) {
				$rank = $level;
				break;
			}
		}
	}
	return $rank;
}
function remaintime($time) {
	$days = intval($time / 86400);
	$time -= $days * 86400;
	$hours = intval($time / 3600);
	$time -= $hours * 3600;
	$minutes = intval($time / 60);
	$time -= $minutes * 60;
	$seconds = $time;
	return array((int)$days, (int)$hours, (int)$minutes, (int)$seconds);
}

function getrelateitem($tagarray, $tid, $relatenum, $relatetime, $relatecache = '', $type = 'tid') {
	$tagidarray = $relatearray = $relateitem = array();
	$updatecache = 0;
	$limit = $relatenum;
	if(!$limit) {
		return '';
	}
	foreach($tagarray as $var) {
		$tagidarray[] = $var['0'];
	}
	if(!$tagidarray) {
		return '';
	}
	if(empty($relatecache)) {
		$thread = C::t('forum_thread')->fetch($tid);
		$relatecache = $thread['relatebytag'];
	}
	if($relatecache) {
		$relatecache = explode("\t", $relatecache);
		if(TIMESTAMP > $relatecache[0] + $relatetime * 60) {
			$updatecache = 1;
		} else {
			if(!empty($relatecache[1])) {
				$relatearray = explode(',', $relatecache[1]);
			}
		}
	} else {
		$updatecache = 1;
	}
	if($updatecache) {
		$query = C::t('common_tagitem')->select($tagidarray, $tid, $type, '', '', $limit, 0, '<>');
		foreach($query as $result) {
			if($result['itemid']) {
				$relatearray[] = $result['itemid'];
			}
		}
		if($relatearray) {
			$relatebytag = implode(',', $relatearray);
		}
		C::t('forum_thread')->update($tid, array('relatebytag'=>TIMESTAMP."\t".$relatebytag));
	}


	if(!empty($relatearray)) {
		foreach(C::t('forum_thread')->fetch_all_by_tid($relatearray) as $result) {
			if($result['displayorder'] >= 0) {
				$relateitem[] = $result;
			}
		}
	}
	return $relateitem;
}

function rushreply_rule () {
	global $rushresult;
	if(!empty($rushresult['rewardfloor'])) {
		$rushresult['rewardfloor'] = preg_replace('/\*+/', '*', $rushresult['rewardfloor']);
		$rewardfloorarr = explode(',', $rushresult['rewardfloor']);
		if($rewardfloorarr) {
			foreach($rewardfloorarr as $var) {
				$var = trim($var);
				if(strlen($var) > 1) {
					$var = str_replace('*', '[^,]?[\d]*', $var);
				} else {
					$var = str_replace('*', '\d+', $var);
				}
				$preg[] = "(,$var,)";
			}
			$preg_str = "/".implode('|', $preg)."/";
		}
	}
	return $preg_str;
}

function checkrushreply($post) {
	global $_G, $rushids;
	if($_GET['authorid']) {
		return $post;
	}
	if(in_array($post['number'], $rushids)) {
		$post['rewardfloor'] = 1;
	}
	return $post;
}

// 解析帖子内容中的soso表情
function odz_soso_smiles($smilieid = '', $maxsmilies = -1, $pid = 0) {
	static $smiliecount;
	$imgsrc = '';
	$pid = intval($pid);
	$maxsmilies = intval($maxsmilies);
	$smilieid = $smiliekey = (string) $smilieid;
	$imgid = "soso_{$smilieid}";
	if($maxsmilies == 0) {
		return "{:soso_$smilieid:}";
	}
	if(strpos($smilieid, '_') === 0) {
		$realsmilieid = $smiliekey = substr($smilieid, 1, -2);
		$serverid = intval(substr($smilieid, -1));
		$imgsrc = "http://imgstore0{$serverid}.cdn.sogou.com/app/a/100520032/{$realsmilieid}";
	} elseif(strpos($smilieid, 'e') === 0) {
		$imgsrc = "http://imgstore01.cdn.sogou.com/app/a/100520032/{$smilieid}";
	} else {
		return "{:soso_$smilieid:}";
	}
	if($maxsmilies > 0) {
		if(!isset($smiliecount)) {
			$smiliecount = array();
		}


		$smiliekey =  $pid.'_'.$smiliekey;

		if(empty($smiliecount[$smiliekey])) {
			$smiliecount[$smiliekey] = 1;
		} else {
			$smiliecount[$smiliekey]++;
		}
		if($smiliecount[$smiliekey] > $maxsmilies) {
			return "{:soso_$smilieid:}";
		}
	}
	return "<img src=\"{$imgsrc}\" height=\"20\" width=\"20\" />";
	// return "<img src=\"{$imgsrc}\" />";
}

function odz_format_message($message) {
    preg_match_all('/^(.*?)$[\r\n]?/m', $message, $matches);

    $arr = array();
    $last = '';
    foreach($matches[1] as $key => $line) {
        $line = trim($line);
        if(!$line && !$last) {
            continue;
        }
        array_push($arr, $line);
        $last = $line;
    }

    $message = join("\r\n", $arr);
    return $message;
}
//处理贴子详情链接
function tothread($url) {
    foreach($url as $key=> $val){
        $arr = explode("/",$val);
        $tothread=end($arr);
        $arr1 = explode("-",$tothread);
        if($arr1['0']=='thread'&&is_numeric($arr1['1'])){
           $thread_all[$key]['typenum']= 0;
           $thread_all[$key]['tid']= $arr1['1'];
        }
        if($arr1['0']=='forum'&&is_numeric($arr1['1'])){
           $thread_all[$key]['typenum']= 1;
           $thread_all[$key]['fid']= $arr1['1'];
           $forums = C::t('forum_forum')->fetch_all_name_by_fid($arr1['1']);
           foreach($forums as $forum) {
                    $name= $forum['name'];
            }
           $thread_all[$key]['name']=$name;
        }
        $bh=strstr($arr1['0'],'tid');
        if($bh){
            $arr2 = explode("&",$arr1['0']);
            $arr3 = explode("=",$arr2['1']);
            $thread_all[$key]['typenum']= 0;
            $thread_all[$key]['tid']= $arr3['1'];
        }
    }
    return $thread_all;
}
//用户等级
function h5_showstars($num) {
	global $_G;
	$return = '';
	$alt = 'alt="Rank: '.$num.'"';
	if(empty($_G['setting']['starthreshold'])) {
		for($i = 0; $i < $num; $i++) {
			$return .= '<img src="../../'.$_G['style']['imgdir'].'/star_level1.gif" '.$alt.' />';
		}
	} else {
		for($i = 3; $i > 0; $i--) {
			$numlevel = intval($num / pow($_G['setting']['starthreshold'], ($i - 1)));
			$num = ($num % pow($_G['setting']['starthreshold'], ($i - 1)));
			for($j = 0; $j < $numlevel; $j++) {
				$return .= '<img src="../../'.$_G['style']['imgdir'].'/star_level'.$i.'.gif" '.$alt.' />';
			}
		}
	}
	return $return;
}

//回复贴统一字体格式
function reply_filter_style($post)
{
    if( ! $post['first'] )
    {
        $post['message'] = strip_tags($post['message'],'<img>,<a>,<div>,<em>,<br/>,<br>,<p>');
        // $post['message'] = preg_replace("/((<br \/>)[\r\t\n]*)+/","<br /><br />",$post['message']);

    }
    return $post['message'];
}



?>
