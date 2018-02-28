<?php

//发帖页面返回版块分类

$fid = intval($_GET['fid']);
$forum = C::t('forum_forum')->fetch_info_by_fid($fid);

	//主题分类

	$forum['threadtypes'] = dunserialize($forum['threadtypes']);



	//存在主题分类且设置允许按主题分类浏览

	if( $forum['threadtypes']){

		foreach($forum['threadtypes']['types'] as $k => $v){

			$result['threadtypes'][] = array('id' => (string)$k, 'name' => strip_tags($v)) ;

		}

	}


odz_result($result);
