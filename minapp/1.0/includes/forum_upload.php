<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: forum_upload.php 29250 2012-03-31 01:54:28Z chenmengshu $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class odz_discuz_upload extends discuz_upload {

	function is_upload_file($source) {
		// TODO 跳过 is_uploaded_file 验证，可能会存在安全问题
		// return $source && ($source != 'none') && (is_uploaded_file($source) || is_uploaded_file(str_replace('\\\\', '\\', $source)));
		return $source && ($source != 'none');
	}

	function save_to_local($source, $target) {
		if(!odz_discuz_upload::is_upload_file($source)) {
			$succeed = false;
		}elseif(@copy($source, $target)) {
			$succeed = true;
		// TODO 跳过 is_uploaded_file 验证，可能存在安全问题
		// }elseif(function_exists('move_uploaded_file') && @move_uploaded_file($source, $target)) {
		// 	$succeed = true;
		}elseif (@is_readable($source) && (@$fp_s = fopen($source, 'rb')) && (@$fp_t = fopen($target, 'wb'))) {
			while (!feof($fp_s)) {
				$s = @fread($fp_s, 1024 * 512);
				@fwrite($fp_t, $s);
			}
			fclose($fp_s); fclose($fp_t);
			$succeed = true;
		}
		if($succeed)  {
			$this->errorcode = 0;
			@chmod($target, 0644); @unlink($source);
		} else {
			$this->errorcode = 0;
		}

		return $succeed;
	}
}

class odz_forum_upload {

	var $uid;
	var $aid;
	var $statusid;
	var $attach;
	var $error_sizelimit;

	function odz_forum_upload($attach) {
		global $_G;

		$this->uid = (int) $_G['uid'];
		$this->aid = 0;

		// $upload = new discuz_upload();
		$upload = new odz_discuz_upload();
		$upload->init($attach, 'forum');
		$this->attach = &$upload->attach;

		if($upload->error()) {
			return $this->uploadmsg(2);
		}

		$allowupload = !$_G['group']['maxattachnum'] || $_G['group']['maxattachnum'] && $_G['group']['maxattachnum'] > getuserprofile('todayattachs');;
		if(!$allowupload) {
			//return $this->uploadmsg(6);
		}

		if($_G['group']['attachextensions'] && (!preg_match("/(^|\s|,)".preg_quote($upload->attach['ext'], '/')."($|\s|,)/i", $_G['group']['attachextensions']) || !$upload->attach['ext'])) {
			//return $this->uploadmsg(1);
		}

		if(empty($upload->attach['size'])) {
			return $this->uploadmsg(2);
		}

		$_G['group']['maxattachsize'] = 2097152; // 论坛附件大小限制(2097152=2M)

		if($_G['group']['maxattachsize'] && $upload->attach['size'] > $_G['group']['maxattachsize']) {
			$this->error_sizelimit = $_G['group']['maxattachsize'];
			//return $this->uploadmsg(3);
		}

		loadcache('attachtype');
		if($_G['fid'] && isset($_G['cache']['attachtype'][$_G['fid']][$upload->attach['ext']])) {
			$maxsize = $_G['cache']['attachtype'][$_G['fid']][$upload->attach['ext']];
		} elseif(isset($_G['cache']['attachtype'][0][$upload->attach['ext']])) {
			$maxsize = $_G['cache']['attachtype'][0][$upload->attach['ext']];
		}
		if(isset($maxsize)) {
			if(!$maxsize) {
				$this->error_sizelimit = 'ban';
				//return $this->uploadmsg(4);
			} elseif($upload->attach['size'] > $maxsize) {
				$this->error_sizelimit = $maxsize;
				//return $this->uploadmsg(5);
			}
		}

		if($upload->attach['size'] && $_G['group']['maxsizeperday']) {
			$todaysize = getuserprofile('todayattachsize') + $upload->attach['size'];
			if($todaysize >= $_G['group']['maxsizeperday']) {
				$this->error_sizelimit = 'perday|'.$_G['group']['maxsizeperday'];
				//return $this->uploadmsg(11);
			}
		}
		updatemembercount($_G['uid'], array('todayattachs' => 1, 'todayattachsize' => $upload->attach['size']));
		$upload->save();
		if($upload->error() == -103) {
			return $this->uploadmsg(8);
		} elseif($upload->error()) {
			return $this->uploadmsg(9);
		}
		$thumb = $remote = $width = 0;
		if($_GET['type'] == 'image' && !$upload->attach['isimage']) {
			return $this->uploadmsg(7);
		}
		if($upload->attach['isimage']) {
			if($_G['setting']['showexif']) {
				require_once libfile('function/attachment');
				$exif = getattachexif(0, $upload->attach['target']);
			}
			if($_G['setting']['thumbsource'] || $_G['setting']['thumbstatus']) {
				require_once libfile('class/image');
				$image = new image;
			}
			if($_G['setting']['thumbsource'] && $_G['setting']['sourcewidth'] && $_G['setting']['sourceheight']) {
				$thumb = $image->Thumb($upload->attach['target'], '', $_G['setting']['sourcewidth'], $_G['setting']['sourceheight'], 1, 1) ? 1 : 0;
				$width = $image->imginfo['width'];
				$upload->attach['size'] = $image->imginfo['size'];
			}
			if($_G['setting']['thumbstatus']) {
				$thumb = $image->Thumb($upload->attach['target'], '', $_G['setting']['thumbwidth'], $_G['setting']['thumbheight'], $_G['setting']['thumbstatus'], 0) ? 1 : 0;
				$width = $image->imginfo['width'];
			}
			if($_G['setting']['thumbsource'] || !$_G['setting']['thumbstatus']) {
				list($width) = @getimagesize($upload->attach['target']);
			}
		}
		if($_GET['type'] != 'image' && $upload->attach['isimage']) {
			$upload->attach['isimage'] = -1;
		}
		$this->aid = $aid = getattachnewaid($this->uid);
		$insert = array(
			'aid' => $aid,
			'dateline' => $_G['timestamp'],
			'filename' => censor($upload->attach['name']),
			'filesize' => $upload->attach['size'],
			'attachment' => $upload->attach['attachment'],
			'isimage' => $upload->attach['isimage'],
			'uid' => $this->uid,
			'thumb' => $thumb,
			'remote' => $remote,
			'width' => $width,
		);
		C::t('forum_attachment_unused')->insert($insert);
		if($upload->attach['isimage'] && $_G['setting']['showexif']) {
			C::t('forum_attachment_exif')->insert($aid, $exif);
		}
		$this->uploadmsg(0);
	}

	function uploadmsg($statusid) {
		global $_G;
		$this->statusid = $statusid ? -$statusid : $this->aid;
	}
}

?>