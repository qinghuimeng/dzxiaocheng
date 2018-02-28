<?php
if($_G['pa_count'] < 2) {
    odz_error('parameters_error', -112);
}
include  'includes/functions/attachment.php';
//禁止发言：如果用户被禁止发言，则客户端也同步禁止 4：禁止发言 5：禁止访问 6：禁止ip 8：等待验证用户 9：限制会员
if($_G['groupid']==4){
    odz_error('minbbs_group_nopermission_speak');
}
//判断是否来自biu
if($_GET['is_biu']==1){
   $_GET['fid']=$_G['forum']['fid']=$_G['action']['fid'] = $_G['fid']=$_G['minbbs_config']['share_fid'];
}
$_GET['action'] = 'reply';
$_GET['tid'] = (int)$_GET['tid'];
$devicetype = 0;
if($_GET['platform']=='ios'){$devicetype=1;}
if($_GET['platform']=='android'){$devicetype=2;}
require 'includes/post.php';
require_once libfile('function/forumlist');

$replytouid = 0; // 被回复用户UID，用于发送回复提醒通知到用户手机端
$isfirstpost = 0;
$showthreadsorts = 0;
$quotemessage = '';
if($special == 5) {
    $debate = array_merge($thread, daddslashes(C::t('forum_debate')->fetch($_G['tid'])));
    $firststand = C::t('forum_debatepost')->get_firststand($_G['tid'], $_G['uid']);
    $stand = $firststand ? $firststand : intval($_GET['stand']);

    if($debate['endtime'] && $debate['endtime'] < TIMESTAMP) {
            odz_error('debate_end');
    }
}

if(!$_G['uid']) {
	odz_error('replyperm_login_nopermission');
}

if(empty($thread)) {
	odz_error('thread_nonexistence');
} elseif($thread['price'] > 0 && $thread['special'] == 0 && !$_G['uid']) {
	odz_error('group_nopermission', -1, array('grouptitle' => $_G['group']['grouptitle']));
}
checklowerlimit('reply', 0, 1, $_G['forum']['fid']);

// 帖子点评
if($_G['setting']['commentnumber'] && !empty($_GET['comment'])) {
	$post = C::t('forum_post')->fetch('tid:'.$_G['tid'], $_GET['pid']);
	if(!$post) {
		showmessage('post_nonexistence', NULL);
	}
	if($thread['closed'] && !$_G['forum']['ismoderator'] && !$thread['isgroup']) {
		odz_error('post_thread_closed');
	} elseif(!$thread['isgroup'] && $post_autoclose = checkautoclose($thread)) {
		odz_error($post_autoclose, -1, array('autoclose' => $_G['forum']['autoclose']));
	} elseif(checkflood()) {
		odz_error('post_flood_ctrl', -1, array('floodctrl' => $_G['setting']['floodctrl']));
	} elseif(checkmaxperhour('pid')) {
		odz_error('post_flood_ctrl_posts_per_hour', -1, array('posts_per_hour' => $_G['group']['maxpostsperhour']));
	}
	$commentscore = '';
	if(!empty($_GET['commentitem']) && !empty($_G['uid']) && $post['authorid'] != $_G['uid']) {
		foreach($_GET['commentitem'] as $itemk => $itemv) {
			if($itemv !== '') {
				$commentscore .= strip_tags(trim($itemk)).': <i>'.intval($itemv).'</i> ';
			}
		}
	}
	$comment = cutstr(($commentscore ? $commentscore.'<br />' : '').censor(trim(dhtmlspecialchars($_GET['message'])), '***'), 200, ' ');
	if(!$comment) {
		odz_error('post_sm_isnull');
	}
	C::t('forum_postcomment')->insert(array(
		'tid' => $post['tid'],
		'pid' => $post['pid'],
		'author' => $_G['username'],
		'authorid' => $_G['uid'],
		'dateline' => TIMESTAMP,
		'comment' => $comment,
		'score' => $commentscore ? 1 : 0,
		'useip' => $_G['clientip'],
	));
	C::t('forum_post')->update('tid:'.$_G['tid'], $_GET['pid'], array('comment' => 1));
	!empty($_G['uid']) && updatepostcredits('+', $_G['uid'], 'reply', $_G['fid']);
	if(!empty($_G['uid']) && $_G['uid'] != $post['authorid']) {
		notification_add($post['authorid'], 'pcomment', 'comment_add', array(
			'tid' => $_G['tid'],
			'pid' => $_GET['pid'],
			'subject' => $thread['subject'],
			'from_id' => $_G['tid'],
			'from_idtype' => 'pcomment',
			'commentmsg' => cutstr(str_replace(array('[b]', '[/b]', '[/color]'), '', preg_replace("/\[color=([#\w]+?)\]/i", "", $comment)), 200)
		));
	}
	update_threadpartake($post['tid']);
	$pcid = C::t('forum_postcomment')->fetch_standpoint_by_pid($_GET['pid']);
	$pcid = $pcid['id'];
	if(!empty($_G['uid']) && $_GET['commentitem']) {
            $totalcomment = array();
            foreach(C::t('forum_postcomment')->fetch_all_by_pid_score($_GET['pid'], 1) as $comment) {
                $comment['comment'] = addslashes($comment['comment']);
                if(strexists($comment['comment'], '<br />')) {
                    if(preg_match_all("/([^:]+?):\s<i>(\d+)<\/i>/", $comment['comment'], $a)) {
                        foreach($a[1] as $k => $itemk) {
                                $totalcomment[trim($itemk)][] = $a[2][$k];
                        }
                    }
                }
            }
            $totalv = '';
            foreach($totalcomment as $itemk => $itemv) {
                    $totalv .= strip_tags(trim($itemk)).': <i>'.(floatval(sprintf('%1.1f', array_sum($itemv) / count($itemv)))).'</i> ';
            }
            if($pcid) {
                C::t('forum_postcomment')->update($pcid, array('comment' => $totalv, 'dateline' => TIMESTAMP + 1));
            } else {
                C::t('forum_postcomment')->insert(array(
                    'tid' => $post['tid'],
                    'pid' => $post['pid'],
                    'author' => '',
                    'authorid' => '-1',
                    'dateline' => TIMESTAMP + 1,
                    'comment' => $totalv
                ));
            }
	}
	C::t('forum_postcache')->delete($post['pid']);
	odz_success('comment_add_succeed');
}
// 微分享回复
if(isset($_GET['repquoteid']) && $_GET['repquoteid'] == intval($_GET['repquoteid'])) {
    $thaquote = C::t('forum_post')->fetch('tid:'.$_G['tid'], $_GET['repquote']);
    if(!($thaquote && ($thaquote['invisible'] == 0 || $thaquote['authorid'] == $_G['uid'] && $thaquote['invisible'] == -2))) {
        $thaquote = array();
    }
    if($thaquote['tid'] != $_G['tid']) {
        odz_error('reply_quotepost_error');
    }
    if(!$thaquote['first']) {
        $time = dgmdate($thaquote['dateline']);
        $quotemessage = messagecutstr($thaquote['message'], 100);
        $message = implode("\n", array_slice(explode("\n", $message), 0, 3));

        $thaquote['useip'] = substr($thaquote['useip'], 0, strrpos($thaquote['useip'], '.')) . '.x';
        if ($thaquote['author'] && $thaquote['anonymous']) {
            $thaquote['author'] = lang('forum/misc', 'anonymoususer');
        } elseif (!$thaquote['author']) {
            $thaquote['author'] = lang('forum/misc', 'guestuser') . ' ' . $thaquote['useip'];
        } else {
            $thaquote['author'] = $thaquote['author'];
        }

        $post_reply_quote = lang('forum/misc', 'post_reply_quote', array('author' => $thaquote['author'], 'time' => $time));
        $message = "[quote][size=2][url=forum.php?mod=redirect&goto=findpost&pid=$_GET[repquote]&ptid={$_G['tid']}][color=#999999]{$post_reply_quote}[/color][/url][/size]\n{$quotemessage}[/quote]\n\n" . $message;
    }
}
// 引用回复
if(!empty($_GET['repquote']) && $_GET['repquote'] == intval($_GET['repquote'])) {
    $thaquote = C::t('forum_post')->fetch('tid:'.$_G['tid'], $_GET['repquote']);
    if(!($thaquote && ($thaquote['invisible'] == 0 || $thaquote['authorid'] == $_G['uid'] && $thaquote['invisible'] == -2))) {
        $thaquote = array();
    }
    if($thaquote['tid'] != $_G['tid']) {
        odz_error('reply_quotepost_error');
    }
    if(!$thaquote['first']) {
        $time = dgmdate($thaquote['dateline']);
        $quotemessage = messagecutstr($thaquote['message'], 100);
        $message = implode("\n", array_slice(explode("\n", $message), 0, 3));

        $thaquote['useip'] = substr($thaquote['useip'], 0, strrpos($thaquote['useip'], '.')) . '.x';
        if ($thaquote['author'] && $thaquote['anonymous']) {
            $thaquote['author'] = lang('forum/misc', 'anonymoususer');
        } elseif (!$thaquote['author']) {
            $thaquote['author'] = lang('forum/misc', 'guestuser') . ' ' . $thaquote['useip'];
        } else {
            $thaquote['author'] = $thaquote['author'];
        }
        $post_reply_quote = lang('forum/misc', 'post_reply_quote', array('author' => $thaquote['author'], 'time' => $time));
        $message = "[quote][size=2][url=forum.php?mod=redirect&goto=findpost&pid=$_GET[repquote]&ptid={$_G['tid']}][color=#999999]{$post_reply_quote}[/color][/url][/size]\n{$quotemessage}[/quote]\n\n" . $message;
        $replytouid = $thaquote['authorid'];
    }
}
if(!$replytouid) {
    $replytouid = $_G['forum_thread']['authorid'];
}
if($special == 127) {
	$postinfo = C::t('forum_post')->fetch_threadpost_by_tid_invisible($_G['tid']);
	$sppos = strrpos($postinfo['message'], chr(0).chr(0).chr(0));
	$specialextra = substr($postinfo['message'], $sppos + 3);
}
if(getstatus($thread['status'], 3)) {
	$rushinfo = C::t('forum_threadrush')->fetch($_G['tid']);
	if($rushinfo['creditlimit'] != -996) {
		$checkcreditsvalue = $_G['setting']['creditstransextra'][11] ? getuserprofile('extcredits'.$_G['setting']['creditstransextra'][11]) : $_G['member']['credits'];
		if($checkcreditsvalue < $rushinfo['creditlimit']) {
			$creditlimit_title = $_G['setting']['creditstransextra'][11] ? $_G['setting']['extcredits'][$_G['setting']['creditstransextra'][11]]['title'] : lang('forum/misc', 'credit_total');
			odz_error('post_rushreply_creditlimit', -1, array('creditlimit_title' => $creditlimit_title, 'creditlimit' => $rushinfo['creditlimit']));
		}
	}
}

if($thread['closed'] && !$_G['forum']['ismoderator'] && !$thread['isgroup']) {
	odz_error('post_thread_closed');
} elseif(!$thread['isgroup'] && $post_autoclose = checkautoclose($thread)) {
	odz_error($post_autoclose, -1, array('autoclose' => $_G['forum']['autoclose']));
} if(trim($subject) == '' && trim($message) == '' && $thread['special'] != 2) {
	odz_error('post_sm_isnull');
} elseif($post_invalid = odz_checkpost($subject, $message, $special == 2 && $_G['group']['allowposttrade'])) {
	odz_error($post_invalid, -1, array('minpostsize' => $_G['setting']['minpostsize'], 'maxpostsize' => $_G['setting']['maxpostsize']));
} elseif(checkflood()) {
	odz_error('post_flood_ctrl', -1, array('floodctrl' => $_G['setting']['floodctrl']));
} elseif(checkmaxperhour('pid')) {
	odz_error('post_flood_ctrl_posts_per_hour', -1, array('posts_per_hour' => $_G['group']['maxpostsperhour']));
}
if(!empty($_GET['trade']) && $thread['special'] == 2 && $_G['group']['allowposttrade']) {

	$item_price = floatval($_GET['item_price']);
	$item_credit = intval($_GET['item_credit']);
	if(!trim($_GET['item_name'])) {
		odz_error('trade_please_name');
	} elseif($_G['group']['maxtradeprice'] && $item_price > 0 && ($_G['group']['mintradeprice'] > $item_price || $_G['group']['maxtradeprice'] < $item_price)) {
		odz_error('trade_price_between', -1, array('mintradeprice' => $_G['group']['mintradeprice'], 'maxtradeprice' => $_G['group']['maxtradeprice']));
	} elseif($_G['group']['maxtradeprice'] && $item_credit > 0 && ($_G['group']['mintradeprice'] > $item_credit || $_G['group']['maxtradeprice'] < $item_credit)) {
		odz_error('trade_credit_between', -1, array('mintradeprice' => $_G['group']['mintradeprice'], 'maxtradeprice' => $_G['group']['maxtradeprice']));
	} elseif(!$_G['group']['maxtradeprice'] && $item_price > 0 && $_G['group']['mintradeprice'] > $item_price) {
		odz_error('trade_price_more_than', -1, array('mintradeprice' => $_G['group']['mintradeprice']));
	} elseif(!$_G['group']['maxtradeprice'] && $item_credit > 0 && $_G['group']['mintradeprice'] > $item_credit) {
		odz_error('trade_credit_more_than', -1, array('mintradeprice' => $_G['group']['mintradeprice']));
	} elseif($item_price <= 0 && $item_credit <= 0) {
		odz_error('trade_pricecredit_need');
	} elseif($_GET['item_number'] < 1) {
		odz_error('tread_please_number');
	}

}

$attentionon = empty($_GET['attention_add']) ? 0 : 1;
$attentionoff = empty($attention_remove) ? 0 : 1;
$heatthreadset = update_threadpartake($_G['tid'], true);

$bbcodeoff = checkbbcodes($message, !empty($_GET['bbcodeoff']));
// $smileyoff = checksmilies($message, !empty($_GET['smileyoff']));
$smileyoff = 0; // 0表示帖子内容中包含表情
$parseurloff = !empty($_GET['parseurloff']);
$htmlon = $_G['group']['allowhtml'] && !empty($_GET['htmlon']) ? 1 : 0;
$usesig = !empty($_GET['usesig']) && $_G['group']['maxsigsize'] ? 1 : 0;

$isanonymous = $_G['group']['allowanonymous'] && !empty($_GET['isanonymous'])? 1 : 0;
$author = empty($isanonymous) ? $_G['username'] : '';

if($thread['displayorder'] == -4) {
	$modnewreplies = 0;
}
$pinvisible = $modnewreplies ? -2 : ($thread['displayorder'] == -4 ? -3 : 0);
$message = preg_replace('/\[attachimg\](\d+)\[\/attachimg\]/is', '[attach]\1[/attach]', $message);
$postcomment = in_array(2, $_G['setting']['allowpostcomment']) && $_G['group']['allowcommentreply'] && !$pinvisible && !empty($_GET['reppid']) && ($nauthorid != $_G['uid'] || $_G['setting']['commentpostself']) ? messagecutstr($message, 200, ' ') : '';

//if(!empty($_GET['noticetrimstr'])) {
//	$message = $_GET['noticetrimstr']."\n\n".$message;
//	$bbcodeoff = false;
//}
$pid = insertpost(array(
	'fid' => $_G['fid'],
	'tid' => $_G['tid'],
	'first' => '0',
	'author' => $_G['username'],
	'authorid' => $_G['uid'],
	'subject' => $subject,
	'dateline' => $_G['timestamp'],
	'message' => $message,
	'useip' => $_G['clientip'],
	'invisible' => $pinvisible,
	'anonymous' => $isanonymous,
	'usesig' => $usesig,
	'htmlon' => $htmlon,
	'bbcodeoff' => $bbcodeoff,
	'smileyoff' => $smileyoff,
	'parseurloff' => $parseurloff,
	'attachment' => '0',
	'status' => (defined('IN_MOBILE') ? 8 : 0),
));
if($_G['group']['allowat'] && $atlist) {
	foreach($atlist as $atuid => $atusername) {
		notification_add($atuid, 'at', 'at_message', array('from_id' => $_G['tid'], 'from_idtype' => 'at', 'buyerid' => $_G['uid'], 'buyer' => $_G['username'], 'tid' => $_G['tid'], 'subject' => $thread['subject'], 'pid' => $pid, 'message' => messagecutstr($message, 150)));
	}
	set_atlist_cookie(array_keys($atlist));
}
$updatethreaddata = $heatthreadset ? $heatthreadset : array();
$postionid = C::t('forum_post')->fetch_maxposition_by_tid($thread['posttableid'], $_G['tid']);
$updatethreaddata[] = DB::field('maxposition', $postionid);
if(getstatus($thread['status'], 3) && $postionid) {
	$rushstopfloor = $rushinfo['stopfloor'];
	if($rushstopfloor > 0 && $thread['closed'] == 0 && $postionid >= $rushstopfloor) {
		$updatethreaddata[] = 'closed=1';
	}
}
useractionlog($_G['uid'], 'pid');

// 处理图片附件
if(isset($_GET['filedata']) && is_array($_GET['filedata'])) {
	foreach($_GET['filedata'] as $key => $value) {

	if(empty($value)){
		continue;
	}
	if($_GET['upload_files']){
		$tmp_name = $value;
	}else{
		$tmp_name = tempnam(DISCUZ_ROOT.'data/attachment/temp', 'odz');
		@file_put_contents($tmp_name, base64_decode($value));
	}
	$size = getimagesize($tmp_name);

	switch($size['mime']) {
		case 'image/png':
			$fileext = 'png';
			break;
		case 'image/gif':
			$fileext = 'gif';
			break;
		case 'image/bmp':
			$fileext = 'bmp';
			break;
		case 'image/jpeg':
			$fileext = 'jpg';
			break;
		default:
			// 跳过非图片类型的文件
//                                unset($tmp_name);
//				continue;
			$fileext = 'aac';
			break;
	}

	$_FILES['filedata'][] = array(
		'name' => random(12).'.'.$fileext,
		'type' => $size['mime'],
		'size' => filesize($tmp_name),
		'error' => 0,
		'tmp_name' => $tmp_name
	);

}
unset($_GET['filedata']);
}

    // C::t('forum_post')->update('tid:'.$_G['tid'], $pid,  array('devicetype' => $devicetype));////发帖插入客户端
	DB::query('INSERT INTO '.DB::table('forum_devicetype').' SET pid='.$pid.', devicetype='.$devicetype);
//    if($_G['groupid']==77){//淮水安澜论坛阳光纪检版块
//        $dahu_sta=strpos($message,"关注");
//        if($dahu_sta){
//            C::t('forum_thread')->update($_G['tid'], array('bzgz'=>1,'bzhf'=>0));
//        }else{
//            C::t('forum_thread')->update($_G['tid'], array('bzgz'=>0,'bzhf'=>1));
//        }
//    }

// 远程上传图片参数
$_GET['attachnew'] = array();
// 上传附件
if(isset($_FILES['filedata'])) {
    $aids = array();
    $attachment = 1;
    require_once 'includes/forum_upload.php';
    foreach($_FILES['filedata'] as $key => $file) {
        if($file['error'] == UPLOAD_ERR_OK) {
            $upload = new odz_forum_upload($file);
            if($upload->statusid > 0) {
                $aids[] = $upload->aid;
                if($upload->attach['isimage']) {
                    $attachment = 2;
                }
            }
            @unlink($file['tmp_name']);
        }
    }
    if($aids) {
        foreach($aids as $aid) {
            $_GET['attachnew'][$aid] = array('description'=>'');
            // convertunusedattach($aid, $_G['tid'], $pid);
            C::t('forum_post')->update('tid:'.$_G['tid'], $pid, array('attachment'=>$attachment));
            C::t('forum_thread')->update($_G['tid'], array('attachment'=>$attachment));
        }
    }
}
$nauthorid = 0;
if(!empty($_GET['noticeauthor']) && !$isanonymous && !$modnewreplies) {
	list($ac, $nauthorid) = explode('|', authcode($_GET['noticeauthor'], 'DECODE'));
	if($nauthorid != $_G['uid']) {
		if($ac == 'q') {
			notification_add($nauthorid, 'post', 'reppost_noticeauthor', array(
				'tid' => $thread['tid'],
				'subject' => $thread['subject'],
				'fid' => $_G['fid'],
				'pid' => $pid,
				'from_id' => $pid,
				'from_idtype' => 'quote',
			));
		} elseif($ac == 'r') {
			notification_add($nauthorid, 'post', 'reppost_noticeauthor', array(
				'tid' => $thread['tid'],
				'subject' => $thread['subject'],
				'fid' => $_G['fid'],
				'pid' => $pid,
				'from_id' => $thread['tid'],
				'from_idtype' => 'post',
			));
		}
	}
	if($postcomment) {
		$rpid = intval($_GET['reppid']);
		if($rpost = C::t('forum_post')->fetch('tid:'.$thread['tid'], $rpid)) {
			if(!$rpost['first']) {
				C::t('forum_postcomment')->insert(array(
					'tid' => $thread['tid'],
					'pid' => $rpid,
					'rpid' => $pid,
					'author' => $_G['username'],
					'authorid' => $_G['uid'],
					'dateline' => TIMESTAMP,
					'comment' => $postcomment,
					'score' => 0,
					'useip' => $_G['clientip'],
				));
				C::t('forum_post')->update('tid:'.$thread['tid'], $rpid, array('comment' => 1));
				C::t('forum_postcache')->delete($rpid);
			}
		}
		unset($postcomment);
	}
}

if($thread['authorid'] != $_G['uid'] && getstatus($thread['status'], 6) && empty($_GET['noticeauthor']) && !$isanonymous && !$modnewreplies) {
	$thapost = C::t('forum_post')->fetch_threadpost_by_tid_invisible($_G['tid'], 0);
	notification_add($thapost['authorid'], 'post', 'reppost_noticeauthor', array(
		'tid' => $thread['tid'],
		'subject' => $thread['subject'],
		'fid' => $_G['fid'],
		'pid' => $pid,
		'from_id' => $thread['tid'],
		'from_idtype' => 'post',
	));
}
$feedid = 0;
if(helper_access::check_module('follow') && !empty($_GET['adddynamic']) && !$isanonymous) {
	require_once libfile('function/discuzcode');
	require_once libfile('function/followcode');
	$feedcontent = C::t('forum_threadpreview')->count_by_tid($thread['tid']);
	$firstpost = C::t('forum_post')->fetch_threadpost_by_tid_invisible($thread['tid']);

	if(empty($feedcontent)) {
		$feedcontent = array(
			'tid' => $thread['tid'],
			'content' => followcode($firstpost['message'], $thread['tid'], $pid, 1000),
		);
		C::t('forum_threadpreview')->insert($feedcontent);
		C::t('forum_thread')->update_status_by_tid($thread['tid'], '512');
	} else {
		C::t('forum_threadpreview')->update_relay_by_tid($thread['tid'], 1);
	}
	$notemsg = cutstr($message, 140);
	$followfeed = array(
		'uid' => $_G['uid'],
		'username' => $_G['username'],
		'tid' => $thread['tid'],
		'note' => followcode($notemsg, $thread['tid'], $pid, 0, false),
		'dateline' => TIMESTAMP
	);
	$feedid = C::t('home_follow_feed')->insert($followfeed, true);
	C::t('common_member_count')->increase($_G['uid'], array('feeds'=>1));
}

if($thread['replycredit'] > 0 && !$modnewreplies && $thread['authorid'] != $_G['uid'] && $_G['uid']) {
	$replycredit_rule = C::t('forum_replycredit')->fetch($_G['tid']);
	if(!empty($replycredit_rule['times'])) {
		$have_replycredit = C::t('common_credit_log')->count_by_uid_operation_relatedid($_G['uid'], 'RCA', $_G['tid']);
		if($replycredit_rule['membertimes'] - $have_replycredit > 0 && $thread['replycredit'] - $replycredit_rule['extcredits'] >= 0) {
			$replycredit_rule['extcreditstype'] = $replycredit_rule['extcreditstype'] ? $replycredit_rule['extcreditstype'] : $_G['setting']['creditstransextra'][10];
			if($replycredit_rule['random'] > 0) {
				$rand = rand(1, 100);
				$rand_replycredit = $rand <= $replycredit_rule['random'] ? true : false ;
			} else {
				$rand_replycredit = true;
			}
			if($rand_replycredit) {
				updatemembercount($_G['uid'], array($replycredit_rule['extcreditstype'] => $replycredit_rule['extcredits']), 1, 'RCA', $_G[tid]);
				C::t('forum_post')->update('tid:'.$_G['tid'], $pid, array('replycredit' => $replycredit_rule['extcredits']));
				$updatethreaddata[] = DB::field('replycredit', $thread['replycredit'] - $replycredit_rule['extcredits']);
			}
		}
	}
}

if($special == 5) {
	if(!$firststand) {
		C::t('forum_debate')->update_debaters($_G['tid'], $stand);
	} else {
		$stand = $firststand;
	}
	C::t('forum_debate')->update_replies($_G['tid'], $stand);
	C::t('forum_debatepost')->insert(array(
	    'tid' => $_G['tid'],
	    'pid' => $pid,
	    'uid' => $_G['uid'],
	    'dateline' => $_G['timestamp'],
	    'stand' => $stand,
	    'voters' => 0,
	    'voterids' => '',
	));
}

($_G['group']['allowpostattach'] || $_G['group']['allowpostimage']) && ($_GET['attachnew'] || $special == 2 && $_GET['tradeaid']) && updateattach_new($thread['displayorder'] == -4 || $modnewreplies, $_G['tid'], $pid, $_GET['attachnew']);

$result = array(
    'pid'=>$pid
);

$replymessage = odz_lang('post_reply_succeed');
if($special == 2 && $_G['group']['allowposttrade'] && $thread['authorid'] == $_G['uid'] && !empty($_GET['trade']) && !empty($_GET['item_name'])) {
	require_once libfile('function/trade');
	trade_create(array(
		'tid' => $_G['tid'],
		'pid' => $pid,
		'aid' => $_GET['tradeaid'],
		'item_expiration' => $_GET['item_expiration'],
		'thread' => $thread,
		'discuz_uid' => $_G['uid'],
		'author' => $author,
		'seller' => empty($_GET['paymethod']) && $_GET['seller'] ? dhtmlspecialchars(trim($_GET['seller'])) : '',
		'item_name' => $_GET['item_name'],
		'item_price' => $_GET['item_price'],
		'item_number' => $_GET['item_number'],
		'item_quality' => $_GET['item_quality'],
		'item_locus' => $_GET['item_locus'],
		'transport' => $_GET['transport'],
		'postage_mail' => $_GET['postage_mail'],
		'postage_express' => $_GET['postage_express'],
		'postage_ems' => $_GET['postage_ems'],
		'item_type' => $_GET['item_type'],
		'item_costprice' => $_GET['item_costprice'],
		'item_credit' => $_GET['item_credit'],
		'item_costcredit' => $_GET['item_costcredit']
	));
	$replymessage = 'trade_add_succeed';
	if(!empty($_GET['tradeaid'])) {
		convertunusedattach($_GET['tradeaid'], $_G['tid'], $pid);
	}
}

if($specialextra) {
    @include_once DISCUZ_ROOT.'./source/plugin/'.$_G['setting']['threadplugins'][$specialextra]['module'].'.class.php';
    $classname = 'threadplugin_'.$specialextra;
    if(class_exists($classname) && method_exists($threadpluginclass = new $classname, 'newreply_submit_end')) {
            $threadpluginclass->newreply_submit_end($_G['fid'], $_G['tid']);
    }
}

$_G['forum']['threadcaches'] && deletethreadcaches($_G['tid']);
include_once libfile('function/stat');
updatestat($thread['isgroup'] ? 'grouppost' : 'post');

$param = array('fid' => $_G['fid'], 'tid' => $_G['tid'], 'pid' => $pid, 'from' => $_GET['from'], 'sechash' => !empty($_GET['sechash']) ? $_GET['sechash'] : '');
if($feedid) {
	$param['feedid'] = $feedid;
}
dsetcookie('clearUserdata', 'forum');

if($modnewreplies) {
	updatemoderate('pid', $pid);
	unset($param['pid']);
	if($updatethreaddata) {
		C::t('forum_thread')->update($_G['tid'], $updatethreaddata, false, false, 0, true);
	}
	C::t('forum_forum')->update_forum_counter($_G['fid'], 0, 0, 1, 1);
	$url = empty($_POST['portal_referer']) ? ("forum.php?mod=viewthread&tid={$thread[tid]}") :  $_POST['portal_referer'];
	manage_addnotify('verifypost');
	if(!isset($inspacecpshare)) {
	    odz_result($result, 'post_reply_mod_succeed', '888');
	}
} else {
    $devicelist = DB::fetch_all("SELECT devicetoken,platform FROM %t WHERE uid = %d AND status = 1", array('minbbs_member_device', $replytouid));
    if(count($devicelist)) {
        $result['pushstatus'] = 1;
        $result['devicelist'] = base64_encode(json_encode($devicelist));
    }

	//回复提醒
    $nid = intval($_GET['repquote']);
    if($nid) {
        $idtype = 'pid';
    } else {
        $nid = $_G['tid'];
        $idtype = 'tid';
    }
	require 'includes/functions/function_push.php';
	push_reply($nid,$idtype,0,$_GET['message']);

	$fieldarr = array(
		'lastposter' => array($author),
		'replies' => 1
	);
	if($thread['lastpost'] < $_G['timestamp']) {
		$fieldarr['lastpost'] = array($_G['timestamp']);
	}
	$row = C::t('forum_threadaddviews')->fetch($_G['tid']);
	if(!empty($row)) {
		C::t('forum_threadaddviews')->update($_G['tid'], array('addviews' => 0));
		$fieldarr['views'] = $row['addviews'];
	}
	$updatethreaddata = array_merge($updatethreaddata, C::t('forum_thread')->increase($_G['tid'], $fieldarr, false, 0, true));
	if($thread['displayorder'] != -4) {
		updatepostcredits('+', $_G['uid'], 'reply', $_G['fid']);
		if($_G['forum']['status'] == 3) {
			if($_G['forum']['closed'] > 1) {
				C::t('forum_thread')->increase($_G['forum']['closed'], $fieldarr, true);
			}
			C::t('forum_groupuser')->update_counter_for_user($_G['uid'], $_G['fid'], 0, 1);
			C::t('forum_forumfield')->update($_G['fid'], array('lastupdate' => TIMESTAMP));
			require_once libfile('function/grouplog');
			updategroupcreditlog($_G['fid'], $_G['uid']);
		}
		$lastpost = "$thread[tid]\t$thread[subject]\t$_G[timestamp]\t$author";
		C::t('forum_forum')->update($_G['fid'], array('lastpost' => $lastpost));
		C::t('forum_forum')->update_forum_counter($_G['fid'], 0, 1, 1);
		if($_G['forum']['type'] == 'sub') {
			C::t('forum_forum')->update($_G['forum']['fup'], array('lastpost' => $lastpost));
		}
	}
	$feed = array();
	if(!isset($_GET['addfeed'])) {
		$space = array();
		space_merge($space, 'field_home');
		$_GET['addfeed'] = $space['privacy']['feed']['newreply'];
	}
	if(!empty($_GET['addfeed']) && $_G['forum']['allowfeed'] && !$isanonymous) {
		if($special == 2 && !empty($_GET['trade'])) {
			$feed['icon'] = 'goods';
			$feed['title_template'] = 'feed_thread_goods_title';
			if($_GET['item_price'] > 0) {
				if($_G['setting']['creditstransextra'][5] != -1 && $_GET['item_credit']) {
					$feed['body_template'] = 'feed_thread_goods_message_1';
				} else {
					$feed['body_template'] = 'feed_thread_goods_message_2';
				}
			} else {
				$feed['body_template'] = 'feed_thread_goods_message_3';
			}
			$feed['body_data'] = array(
				'itemname'=> "<a href=\"forum.php?mod=viewthread&do=tradeinfo&tid=$_G[tid]&pid=$pid\">$_GET[item_name]</a>",
				'itemprice'=> $_GET['item_price'],
				'itemcredit'=> $_GET['item_credit'],
				'creditunit'=> $_G['setting']['extcredits'][$_G['setting']['creditstransextra'][5]]['unit'].$_G['setting']['extcredits'][$_G['setting']['creditstransextra'][5]]['title'],
			);
			if($_GET['tradeaid']) {
				$feed['images'] = array(getforumimg($_GET['tradeaid']));
				$feed['image_links'] = array("forum.php?mod=viewthread&do=tradeinfo&tid=$_G[tid]&pid=$pid");
			}
		} elseif($special == 3 && $thread['authorid'] != $_G['uid']) {
			$feed['icon'] = 'reward';
			$feed['title_template'] = 'feed_reply_reward_title';
			$feed['title_data'] = array(
				'subject' => "<a href=\"forum.php?mod=viewthread&tid=$_G[tid]\">$thread[subject]</a>",
				'author' => "<a href=\"home.php?mod=space&uid=$thread[authorid]\">$thread[author]</a>"
			);
		} elseif($special == 5 && $thread['authorid'] != $_G['uid']) {
			$feed['icon'] = 'debate';
			if($stand == 1) {
				$feed['title_template'] = 'feed_thread_debatevote_title_1';
			} elseif($stand == 2) {
				$feed['title_template'] = 'feed_thread_debatevote_title_2';
			} else {
				$feed['title_template'] = 'feed_thread_debatevote_title_3';
			}
			$feed['title_data'] = array(
				'subject' => "<a href=\"forum.php?mod=viewthread&tid=$_G[tid]\">$thread[subject]</a>",
				'author' => "<a href=\"home.php?mod=space&uid=$thread[authorid]\">$thread[author]</a>"
			);
		} elseif($thread['authorid'] != $_G['uid']) {
			$post_url = "forum.php?mod=redirect&goto=findpost&pid=$pid&ptid=$_G[tid]";

			$feed['icon'] = 'post';
			$feed['title_template'] = !empty($thread['author']) ? 'feed_reply_title' : 'feed_reply_title_anonymous';
			$feed['title_data'] = array(
				'subject' => "<a href=\"$post_url\">$thread[subject]</a>",
				'author' => "<a href=\"home.php?mod=space&uid=$thread[authorid]\">$thread[author]</a>"
			);
			if(!empty($_G['forum_attachexist'])) {
				$imgattach = C::t('forum_attachment_n')->fetch_max_image('tid:'.$_G['tid'], 'pid', $pid);
				$firstaid = $imgattach['aid'];
				unset($imgattach);
				if($firstaid) {
					$feed['images'] = array(getforumimg($firstaid));
					$feed['image_links'] = array($post_url);
				}
			}
		}
		$feed['title_data']['hash_data'] = "tid{$_G[tid]}";
		$feed['id'] = $pid;
		$feed['idtype'] = 'pid';
		if($feed['icon']) {
			postfeed($feed);
		}
	}
	$page = getstatus($thread['status'], 4) ? 1 : @ceil(($thread['special'] ? $thread['replies'] + 1 : $thread['replies'] + 2) / $_G['ppp']);
	if($updatethreaddata) {
		C::t('forum_thread')->update($_G['tid'], $updatethreaddata, false, false, 0, true);
	}
	if($special == 2 && !empty($_GET['continueadd'])) {
	    odz_result($result, $replymessage);
		//showmessage('post_reply_succeed', "forum.php?mod=post&action=reply&fid={$_G[forum][fid]}&firstpid=$pid&tid={$thread[tid]}&addtrade=yes", $param, array('header' => true));
	} else {
		$url = empty($_POST['portal_referer']) ? "forum.php?mod=viewthread&tid={$thread[tid]}&pid=$pid&page=$page&extra=$extra#pid$pid" : $_POST['portal_referer'];
	}
        $forum_post =DB::fetch_first('SELECT * FROM '.DB::table('forum_post').' WHERE pid = '.$result['pid']);
        $result["page"] =ceil($forum_post['position']/20);
	if(!isset($inspacecpshare)) {
	    odz_result($result, $replymessage);
		//showmessage($replymessage, $url, $param);
	}
}
?>
