<?php
require_once libfile('function/forumlist');
require_once './includes/functions/forumlist.php';

// 加载自定义设置，需 minbbs_forum_switch 插件支持
@include_once DISCUZ_ROOT.'./data/cache/minbbs_forums.dat';
if(!isset($minbbs_forums)) {
    $minbbs_forums = array();
}
odz_stats(array('typeid'=>3, 'action'=>'click'));
$catlist = $forumlist = $sublist = $forumname = $collapse = $favforumlist = array();
$threads = $posts = $todayposts = $announcepm = 0;

$forums = C::t('forum_forum')->fetch_all_by_status(1);
$fids = array();
foreach($forums as $forum) {
    $fids[$forum['fid']] = $forum['fid'];
}

$forum_access = array();
if(!empty($_G['member']['accessmasks'])) {
    $forum_access = C::t('forum_access')->fetch_all_by_fid_uid($fids, $_G['uid']);
}

$forum_fields = C::t('forum_forumfield')->fetch_all($fids);

foreach($forums as $forum) {
    if($forum_fields[$forum['fid']]['fid']) {
        $forum = array_merge($forum, $forum_fields[$forum['fid']]);
    }

    //过滤外部链接
    if(!empty($forum_fields[$forum['fid']]['redirect'])){
        continue;
    }

    if($forum_access['fid']) {
        $forum = array_merge($forum, $forum_access[$forum['fid']]);
    }
    $forumname[$forum['fid']] = strip_tags($forum['name']);
    $forum['extra'] = empty($forum['extra']) ? array() : dunserialize($forum['extra']);
    if(!is_array($forum['extra'])) {
        $forum['extra'] = array();
    }

    if($forum['type'] != 'group') {

        $threads += $forum['threads'];
        $posts += $forum['posts'];
        $todayposts += $forum['todayposts'];

        if($forum['type'] == 'forum' && isset($catlist[$forum['fup']])) {
            if(odz_forum($forum)) {
                $catlist[$forum['fup']]['forums'][] = $forum['fid'];
                $forum['orderid'] = $catlist[$forum['fup']]['forumscount']++;
                $forum['subforums'] = '';
                $forumlist[$forum['fid']] = $forum;
            }

        } elseif(isset($forumlist[$forum['fup']])) {

            $forumlist[$forum['fup']]['threads'] += $forum['threads'];
            $forumlist[$forum['fup']]['posts'] += $forum['posts'];
            $forumlist[$forum['fup']]['todayposts'] += $forum['todayposts'];
            if($_G['setting']['subforumsindex'] && $forumlist[$forum['fup']]['permission'] == 2 && !($forumlist[$forum['fup']]['simple'] & 16) || ($forumlist[$forum['fup']]['simple'] & 8)) {
                $forumurl = !empty($forum['domain']) && !empty($_G['setting']['domain']['root']['forum']) ? 'http://'.$forum['domain'].'.'.$_G['setting']['domain']['root']['forum'] : 'forum.php?mod=forumdisplay&fid='.$forum['fid'];
                $forumlist[$forum['fup']]['subforums'] .= (empty($forumlist[$forum['fup']]['subforums']) ? '' : ', ').'<a href="'.$forumurl.'" '.(!empty($forum['extra']['namecolor']) ? ' style="color: ' . $forum['extra']['namecolor'].';"' : '') . '>'.$forum['name'].'</a>';
            }
        }

    } else {

        if($forum['moderators']) {
            $forum['moderators'] = moddisplay($forum['moderators'], 'flat');
        }
        $forum['forumscount'] 	= 0;
        $catlist[$forum['fid']] = $forum;

    }
}
unset($forum_access, $forum_fields);

// 组织返回数据
$tmp_catlist = array();
foreach($catlist as $key => $tmp_cat) {
    // 控制分区显示隐藏
    if(!empty($minbbs_forums) && !odz_procforum($tmp_cat)) {
        continue;
    }

    $tmp_forumlist = array();
    foreach($tmp_cat['forums'] as $forumid) {
        $forum = $forumlist[$forumid];

        // 控制版块显示隐藏
        if(!empty($minbbs_forums) && !odz_procforum($forum)) {
            continue;
        }
        if (empty($forum['icon'])) {
            $forum['icon'] = $_G['siteurl'].'assets/img/forum'.(empty($forum['folder']) ? '' : '_new').'.png?v='.ODZ_IMGHASH;
        }
        $description = empty($forum['description']) ? odz_lang('forum_desc_empty') : strip_tags($forum['description']);
        $description = str_replace('&nbsp;', " ", $description);

        //根据配置正则替换板块名称
        $forumname = strip_tags($forum['name']);
        if(isset($_G['minbbs_config']['forumname_replace']) && !empty($_G['minbbs_config']['forumname_replace'][0]) && !empty($_G['minbbs_config']['forumname_replace'][0]) ){
            $forumname = preg_replace($_G['minbbs_config']['forumname_replace'][0], $_G['minbbs_config']['forumname_replace'][1], $forumname);
        }
        $tmp_forum = array(
            'fid' => $forum['fid'],
            'name' => $forumname,
            'displayorder' => $forum['displayorder'],
            'icon' => str_replace(' ', '%20', trim($forum['icon'])),
            'description' => empty($forum['description']) ? odz_lang('forum_desc_empty') : strip_tags($forum['description']),
            'threads' => $forum['threads'],
            'posts' => $forum['posts'],
//            'todayposts' => '0',
         //   'showsubforum' => $showsubforum, // 是否直接显示子版块
            'lastpost' => array(
                'author' => $forum['lastpost']['authorusername'],
                'dateline' => strip_tags($forum['lastpost']['dateline']),
                'subject' => $forum['lastpost']['subject'],
                'tid' => (int) $forum['lastpost']['tid']
            )
        );
        if((isset($_G['minbbs_config']['version']) && $_G['minbbs_config']['version'] == '3.0') ||
            (isset($_G['minbbs_config']['showposts']) && $_G['minbbs_config']['showposts'] === true)
            ){
            $tmp_forum['todayposts'] = $forum['todayposts'];
        }


        $tmp_forumlist[] = $tmp_forum;
        odz_array_sort($tmp_forumlist, 'displayorder');
    }
    if(empty($tmp_forumlist)){
        continue;
    }
    $cat = array(
        'fid' => $tmp_cat['fid'],
        'name' => strip_tags($tmp_cat['name']),
        'forumlist' => $tmp_forumlist,
        'displayorder' => $tmp_cat['displayorder']
    );
    $tmp_catlist[] = $cat;
    odz_array_sort($tmp_catlist, 'displayorder');
}
unset($catlist, $forumlist);

//我关注的版块 zhangxin
if(!empty($_G['uid'])){
        $focus_data = DB::fetch_all('SELECT fid FROM '.DB::table('minbbs_member_focus')." WHERE uid = '$_G[uid]'");
        if(!empty($focus_data)){
                foreach($focus_data as $k=>$v){
                        $focus_fid[] = $v['fid'];
                }
        }
}
$focus_data = $focus_fid?$focus_fid:array();

$myfocus = array();
if(!empty($tmp_catlist)){
        foreach($tmp_catlist as $k=>$v){
                foreach($v['forumlist'] as $k1=>$v1){
                        if(in_array($v1['fid'],$focus_data)){
                                $tmp_catlist[$k]['forumlist'][$k1]['focus'] = '1';
                                $v1['focus'] = '1';
                                $myfocus[] = $v1;
                        }else{
                                $tmp_catlist[$k]['forumlist'][$k1]['focus'] = '0';
                                $v1['focus'] = '0';
                        }
                        $recommend_data[] = $v1;
                        //$focus_nums = DB::fetch_all('SELECT count(*) FROM '.DB::table('minbbs_member_focus')." WHERE fid = '$v1[fid]'");
                        //$tmp_catlist[$k]['forumlist'][$k1]['focus_nums'] = (string)$focus_nums[0]['count(*)'];
                }
        }
}



odz_result(array('catlist'=>$tmp_catlist,'myfocus'=>$myfocus));

function odz_procforum(&$forum) {
    global $minbbs_forums;
    $fid = $forum['fid'];
    if(!isset($minbbs_forums[$fid]) || empty($minbbs_forums[$fid]['show'])) {
        return false;
    }
    $forum['displayorder'] = $minbbs_forums[$fid]['order'];
    $forum['name'] = !empty($minbbs_forums[$fid]['name']) ? $minbbs_forums[$fid]['name'] : $forum['name'];
    return true;
}
?>
