<?php

require 'includes/class/threadsort_service.php';

require 'includes/class/thread_service.php';



$fid = intval($_GET['fid']);


$forum = C::t('forum_forum')->fetch_info_by_fid($fid);



$forum['threadsorts'] = dunserialize($forum['threadsorts']);



//板块密码判断

if($forum['password']) {

    if(!$_GET['pw']){

            odz_error('need_password',400);

    }

    if($_GET['pw'] !=  $forum['password']) {

            odz_error('passwd_incorrect',300);

    }

}





//子版块判断

$subforumonly = $forum['simple'] & 1;

if($subforumonly){

	odz_error('redirect to subforum', -550);

}









//板块访问权限判断

if($_G['uid']) {

	if($_G['member']['accessmasks']) {

		$query = C::t('forum_access')->fetch_all_by_fid_uid($fid, $_G['uid']);

		$forum['allowview'] = $query[0]['allowview'];

	}

}



if($forum['viewperm'] && !forumperm($forum['viewperm']) && !$forum['allowview']) {

    odz_error('viewperm_login_nopermission', ODZ_ERR_LOGIN);

} elseif($forum['formulaperm']) {

	odz_formulaperm($forum['formulaperm']);

}











//分页

$page = intval($_GET['page']) > 0 ? intval($_GET['page']) : 1;

$pagesize = isset($_GET['pagesize']) ? intval($_GET['pagesize']) : 20;

$start = ($page - 1) * $pagesize;





//排序

$order_field_array = array('lastpost', 'dateline', 'replies', 'views');

$order_array = array('DESC', 'ASC');

//获取默认排序规则

$simplebin = sprintf('%08b', $forum['simple']);

$index =  bindec(substr($simplebin, 0, 2));

$order_filed = $order_field_array[$index];

$index = ($forum['simple'] & 32) ? 1 : 0;

$order = $order_array[$index];







$thread_sercie = new Thread_Service;







$filter = array();

//筛选模式

if(isset($_GET['sortid'])){

	$in_tids = array();

	$threadsort = new Threadsort_Service;

	$in_tids = $threadsort->search($_GET['sortid'], $_GET['sortvalue']);

	if($in_tids === false){

		odz_error('-1004', 'no sort');

	}else{

		$filter[] = 'tid IN('.implode(',', $in_tids).')';

	}

}









//精华

if(isset($_GET['digest']) && $_GET['digest'] > 0){

	$filter[] = 'digest > 0 ';

}



//主题分类id

if(isset($_GET['typeid'])){

	$filter[] = 'typeid ='.intval($_GET['typeid']);

}

//关联板块

if(!empty($forum['relatedgroup'])){

	$relatedgroup = explode(',', $forum['relatedgroup']);

}



//fid拼接

$relatedgroup[] = $fid;

$fid_sql = "fid IN(".dimplode($relatedgroup).")";





//首页返回

if($page == 1 && empty($filter)){





	//板块信息处理

	$result['forum']['name'] = strip_tags($forum['name']);

	$result['forum']['des'] = empty($forum['description']) ? odz_lang('forum_des_empty') : strip_tags($forum['description']);

	$result['forum']['fid'] = $forum['fid'];


	//是否关注

	require 'includes/class/focus_service.php';

	$result['forum']['focus'] = (string)Focus_Service::user_focus($_G['uid'], $forum['fid']);







	//主题分类

	$forum['threadtypes'] = dunserialize($forum['threadtypes']);



	//存在主题分类且设置允许按主题分类浏览

	$result['threadtypes'][] = array('id' => 'all', 'name' => odz_lang('all'));

	$result['threadtypes'][] = array('id' => 'digest', 'name' => odz_lang('digest'));

	if( $forum['threadtypes']){

		foreach($forum['threadtypes']['types'] as $k => $v){

			$result['threadtypes'][] = array('id' => (string)$k, 'name' => strip_tags($v)) ;

		}



	}





	//分类筛选



	$threadsort = new Threadsort_Service;

	$result['threadsort'] = $threadsort->get_threadsort($forum['threadsorts']);



	//子版块

	$result['sublist'] = array();

	if($forum['type'] == 'forum'){

		loadcache('forums');

		foreach($_G['cache']['forums'] as $sub) {

			if($sub['type'] == 'sub' && $sub['fup'] == $fid && (!$_G['setting']['hideprivate'] || !$sub['viewperm'] || forumperm($sub['viewperm']) || strstr($sub['users'], "\t$_G[uid]\t"))) {

				if(!$sub['status']) {

					continue;

				}

				$temp['name'] = $sub['name'];

				$temp['fid'] = $sub['fid'];

				$result['sublist'][] = $temp;

				unset($temp,$sub);



			}

		}



	}







	//置顶处理

	loadcache(array('forumstick', 'globalstick'));

	$thisgid = $forum['type'] == 'forum' ? $forum['fup'] : (!empty($_G['cache']['forums'][$forum['fup']]['fup']) ? $_G['cache']['forums'][$forum['fup']]['fup'] : 0);

	if($_G['setting']['globalstick'] && $forum['allowglobalstick']) {

		$stickytids = explode(',', str_replace("'", '', $_G['cache']['globalstick']['global']['tids']));

		if(!empty($_G['cache']['globalstick']['categories'][$thisgid]['count'])) {

			$stickytids = array_merge($stickytids, explode(',', str_replace("'", '', $_G['cache']['globalstick']['categories'][$thisgid]['tids'])));

		}

	}



	if($forum['allowglobalstick']) {

		$forumstickfid = $forum['status'] != 3 ? $fid : $forum['fup'];

		if(isset($_G['cache']['forumstick'][$forumstickfid])) {

			$forumstickytids = $_G['cache']['forumstick'][$forumstickfid];

		}

		if(!empty($forumstickytids)) {

			$stickytids = array_merge($stickytids, $forumstickytids);

		}

	}



	//全局分类置顶获取

	if(!empty($stickytids) && $stickytids[0] !== ''){



		$tids = implode(',', $stickytids);

		$query = DB::query("SELECT * FROM ".DB::table('forum_thread')." WHERE tid IN($tids) ORDER BY displayorder desc, $order_filed $order");



		while ($thread = DB::fetch($query)) {

			$thread_sercie->input_thread($thread);

		}



		unset($thread,$tids,$stickytids);

	}





	//本版置顶

	$query = DB::query("SELECT * FROM ".DB::table('forum_thread')." WHERE $fid_sql AND displayorder = 1 ORDER BY displayorder desc, $order_filed $order");



	while ($thread = DB::fetch($query)) {

		$thread_sercie->input_thread($thread);

	}









}





//fid加入条件

$filter[] = $fid_sql;








$where_sql = implode(' AND ', $filter);





//获取总数

$total = DB::result_first("SELECT count(*)  FROM ".DB::table('forum_thread')." WHERE $where_sql");

$total_page = ceil($total/$pagesize);





$query = DB::query("SELECT * FROM ".DB::table('forum_thread')." WHERE $where_sql  ORDER BY $order_filed $order LIMIT $start, $pagesize");



$thread_sercie->reset();

if(!empty($forum['threadsorts'])){





	$thread_sercie->set('threadsort_type', $forum['threadsorts']);

	$thread_sercie->set('fid', $fid);

}







while ($thread = DB::fetch($query)) {
	//判断是否被移动过
	if( ! empty($thread['closed']))
	{
		$thread['tid'] = $thread['closed'];
	}
	$thread_sercie->input_thread($thread);

}
if($_GET['type'] == 'group'){
    $result['list'] = $thread_sercie->output_threads('group');
}else{
    $result['list'] = $thread_sercie->output_threads('full');
}


$result['total'] = $total_page;

odz_result($result);
