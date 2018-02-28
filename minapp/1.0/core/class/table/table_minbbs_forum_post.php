<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: table_forum_post.php 30080 2012-05-09 08:19:20Z liulanbo $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_minbbs_forum_post extends table_forum_post
{
	public function __construct() {

		$this->_table = 'forum_post';
		$this->_pk    = 'pid';
		$this->_pre_cache_key = 'minbbs_';

		parent::__construct();
	}
	public function fetch_all_by_tid_user_check($tableid, $tid, $start, $end,$ordertype = 0) {
		$storeflag = false;
		if($this->_allowmem) {
			if($ordertype != 1 && $start == 1 && $maxposition > ($end - $start)) {
				$data = $this->fetch_cache($tid, $this->_pre_cache_key.'tid_');
				if($data && count($data) == ($end - $start)) {
					$delauthorid = $this->fetch_cache('delauthorid');
					$updatefid = $this->fetch_cache('updatefid');
					$delpid = $this->fetch_cache('delpid');
					foreach($data as $k => $post) {
						if(in_array($post['pid'], $delpid) || $post['invisible'] < 0 || in_array($post['authorid'], $delauthorid)) {
							$data[$k]['invisible'] = 0;
							$data[$k]['authorid'] = 0;
							$data[$k]['useip'] = '';
							$data[$k]['dateline'] = 0;
							$data[$k]['pid'] = 0;
							$data[$k]['message'] = lang('forum/misc', 'post_deleted');
						}
						if(isset($updatefid[$post['fid']]) && $updatefid[$post['fid']]['dateline'] > $post['dateline']) {
							$data[$k]['fid'] = $updatefid[$post['fid']]['fid'];
						}
					}
					return $data;
				}
				$storeflag = true;
			}
		}

		$data = DB::fetch_all('SELECT * FROM %t as a LEFT JOIN %t as b ON a.authorid = b.uid WHERE b.username <> \'\' AND tid=%d  ORDER BY position'.($ordertype == 1 ? ' DESC' : '')." LIMIT $start,$end", 
								array(self::get_tablename($tableid), 'common_member', $tid, $start, $end), 'pid');
		if($storeflag) {
			$this->store_cache($tid, $data, $this->_cache_ttl, $this->_pre_cache_key.'tid_');
		}
		return $data;
	}

}
?>