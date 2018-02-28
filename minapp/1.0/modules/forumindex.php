<?php

$page = intval($_GET['page']) > 0 ? intval($_GET['page']) : 1;
$pagesize = isset($_GET['pagesize']) ? intval($_GET['pagesize']) : 20;
$start = ($page - 1) * $pagesize;

//排序判断
$default_order = get_minbbs_setting('posts_show_type');
$default_order = $default_order['posts_show_type'];
if($default_order == 1){
  $default_order = "dateline";
}else{
  $default_order = "lastpost";
}
$order = isset($_GET['order']) && in_array($_GET['order'], array('dateline', 'lastpost')) ? $_GET['order'] : $default_order;

//帖子流排除版块
$not_in_fid = get_minbbs_setting('index_remove_block_id');
$not_in_fid = $not_in_fid['index_remove_block_id'];
if(!empty($not_in_fid)){
  $sql = " fid NOT IN ($not_in_fid)";
}else{
  $sql = " 1=1 ";
}
//获取总数
$total = DB::result_first("SELECT count(*)  FROM ".DB::table('forum_thread')." WHERE $sql  AND displayorder >= 0");
$total_page = ceil($total/$pagesize);

$query = DB::query("SELECT * FROM ".DB::table('forum_thread')." WHERE $sql AND displayorder >= 0 ORDER BY $order DESC LIMIT $start,$pagesize");


require 'includes/class/thread_service.php';
$thread_sercie = new Thread_Service;

while($thread = DB::fetch($query)){
  //判断是否被移动过 lhw
  if($thread['closed'] > 1)
  {
      $thread['tid'] = $thread['closed'];
  }
	$thread_sercie->input_thread($thread);
}

$threads = $thread_sercie->output_threads('full');

$result['order'] = $order;
$result['list'] = $threads;
$result['total'] = $total_page;
odz_result($result);
