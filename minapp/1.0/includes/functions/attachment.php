<?php
 
require DISCUZ_ROOT.'/source/class/class_image.php';

class minbbs_image extends image{
	function Watermark($source, $target = '', $type = 'forum') {
        
		$return = $this->init('watermask', $source, $target);
		if($return <= 0) {
			return $this->returncode($return);
		}
	
		if(!$this->param['watermarkstatus'][$type] || ($this->param['watermarkminwidth'][$type] && $this->imginfo['width'] <= $this->param['watermarkminwidth'][$type] && $this->param['watermarkminheight'][$type] && $this->imginfo['height'] <= $this->param['watermarkminheight'][$type])) {
			return $this->returncode(0);
		}
 
		$this->param['watermarkfile'][$type] =DISCUZ_ROOT.'./static/image/common/'.($this->param['watermarktype'][$type] == 'png' ? 'watermark.png' : 'watermark.gif');
        $this->param['watermarktext']['fontpath'][$type]=DISCUZ_ROOT.$this->param['watermarktext']['fontpath'][$type];   
		if(!is_readable($this->param['watermarkfile'][$type]) || ($this->param['watermarktype'][$type] == 'text' && (!file_exists($this->param['watermarktext']['fontpath'][$type]) || !is_file($this->param['watermarktext']['fontpath'][$type])))) {
			return $this->returncode(-3);
		}
		$return = !$this->libmethod ? $this->Watermark_GD($type) : $this->Watermark_IM($type);
	
		return $this->sleep($return);
	}
	
}

function updateattach_new($modnewthreads, $tid, $pid, $attachnew, $attachupdate = array(), $uid = 0) {
	global $_G;
	$thread = C::t('forum_thread')->fetch($tid);
	$uid = $uid ? $uid : $_G['uid'];
 
	if($attachnew) {
		$newaids = array_keys($attachnew);
		$newattach = $newattachfile = $albumattach = array();
		foreach(C::t('forum_attachment_unused')->fetch_all($newaids) as $attach) {
			if($attach['uid'] != $uid && !$_G['forum']['ismoderator']) {
				continue;
			}
			$attach['uid'] = $uid;
			$newattach[$attach['aid']] = daddslashes($attach);
			if($attach['isimage']) {
				$newattachfile[$attach['aid']] = $attach['attachment'];
			}
		}
		//水印判断
		if($_G['setting']['watermarkstatus'] && empty($_G['forum']['disablewatermark']) || !$_G['setting']['thumbdisabledmobile']) {
			//require_once libfile('class/image');
			$image = new minbbs_image();
		}
		if(!empty($_GET['albumaid'])) {
			array_unshift($_GET['albumaid'], '');
			$_GET['albumaid'] = array_unique($_GET['albumaid']);
			unset($_GET['albumaid'][0]);
			foreach($_GET['albumaid'] as $aid) {
				if(isset($newattach[$aid])) {
					$albumattach[$aid] = $newattach[$aid];
				}
			}
		}
		foreach($attachnew as $aid => $attach) {
			$update = array();
			$update['readperm'] = $_G['group']['allowsetattachperm'] ? $attach['readperm'] : 0;
			$update['price'] = $_G['group']['maxprice'] ? (intval($attach['price']) <= $_G['group']['maxprice'] ? intval($attach['price']) : $_G['group']['maxprice']) : 0;
			$update['tid'] = $tid;
			$update['pid'] = $pid;
			$update['uid'] = $uid;
			$update['description'] = censor(cutstr(dhtmlspecialchars($attach['description']), 100));
			C::t('forum_attachment_n')->update('tid:'.$tid, $aid, $update);
			if(!$newattach[$aid]) {
				continue;
			}
			$update = array_merge($update, $newattach[$aid]);
			if(!empty($newattachfile[$aid])) {
				if($_G['setting']['thumbstatus'] && $_G['forum']['disablethumb']) {
					$update['thumb'] = 0;
					@unlink($_G['setting']['attachdir'].'/forum/'.getimgthumbname($newattachfile[$aid]));
					if(!empty($albumattach[$aid])) {
						$albumattach[$aid]['thumb'] = 0;
					}
				} elseif(!$_G['setting']['thumbdisabledmobile']) {
					$_daid = sprintf("%09d", $aid);
					$dir1 = substr($_daid, 0, 3);
					$dir2 = substr($_daid, 3, 2);
					$dir3 = substr($_daid, 5, 2);
					$dw = 320;
					$dh = 320;
					$thumbfile = 'image/'.$dir1.'/'.$dir2.'/'.$dir3.'/'.substr($_daid, -2).'_'.$dw.'_'.$dh.'.jpg';
					$image->Thumb($_G['setting']['attachdir'].'/forum/'.$newattachfile[$aid], $thumbfile, $dw, $dh, 'fixwr');
					$dw = 720;
					$dh = 720;
					$thumbfile = 'image/'.$dir1.'/'.$dir2.'/'.$dir3.'/'.substr($_daid, -2).'_'.$dw.'_'.$dh.'.jpg';
					$image->Thumb($_G['setting']['attachdir'].'/forum/'.$newattachfile[$aid], $thumbfile, $dw, $dh, 'fixwr');
				}

				if($_G['setting']['watermarkstatus'] && empty($_G['forum']['disablewatermark'])) {	
					
				//缩略图水印
				$fileme=$_G['setting']['attachdir'].'/forum/'.$newattachfile[$aid].'.thumb.jpg';
				if(file_exists($_G['setting']['attachdir'].'/forum/'.$newattachfile[$aid].'.thumb.jpg')){
					 
					$image->Watermark($_G['setting']['attachdir'].'/forum/'.$newattachfile[$aid].'.thumb.jpg','','forum');
				}
				//end缩略图水印
				$image->Watermark($_G['setting']['attachdir'].'/forum/'.$newattachfile[$aid], '', 'forum');
				$update['filesize'] = $image->imginfo['size'];
				}
			}
			if(!empty($_GET['albumaid']) && isset($albumattach[$aid])) {
				$newalbum = 0;
				if(!$_GET['uploadalbum']) {
					require_once libfile('function/spacecp');
					$_GET['uploadalbum'] = album_creat(array('albumname' => $_GET['newalbum']));
					$newalbum = 1;
				}
				$picdata = array(
						'albumid' => $_GET['uploadalbum'],
						'uid' => $uid,
						'username' => $_G['username'],
						'dateline' => $albumattach[$aid]['dateline'],
						'postip' => $_G['clientip'],
						'filename' => censor($albumattach[$aid]['filename']),
						'title' => censor(cutstr(dhtmlspecialchars($attach['description']), 100)),
						'type' => fileext($albumattach[$aid]['attachment']),
						'size' => $albumattach[$aid]['filesize'],
						'filepath' => $albumattach[$aid]['attachment'],
						'thumb' => $albumattach[$aid]['thumb'],
						'remote' => $albumattach[$aid]['remote'] + 2,
				);

				$update['picid'] = C::t('home_pic')->insert($picdata, 1);

				if($newalbum) {
					require_once libfile('function/home');
					require_once libfile('function/spacecp');
					album_update_pic($_GET['uploadalbum']);
				}
			}
			C::t('forum_attachment_n')->insert('tid:'.$tid, $update, false, true);
			C::t('forum_attachment')->update($aid, array('tid' => $tid, 'pid' => $pid, 'tableid' => getattachtableid($tid)));
/* 缩略图水印
        if (file_exists($_G['setting']['attachdir'] . '/forum/' . $attach['attachment'] . '.thumb.jpg'))
			{
				$image->Watermark($_G['setting']['attachdir'] . '/forum/' . $attach['attachment'] . '.thumb.jpg', '', 'forum');
			}  
*/ 
			C::t('forum_attachment_unused')->delete($aid);
		}

		if(!empty($_GET['albumaid'])) {
			$albumdata = array(
					'picnum' => C::t('home_pic')->check_albumpic($_GET['uploadalbum']),
					'updatetime' => $_G['timestamp'],
			);
			C::t('home_album')->update($_GET['uploadalbum'], $albumdata);
			require_once libfile('function/home');
			require_once libfile('function/spacecp');
			album_update_pic($_GET['uploadalbum']);
		}
		if($newattach) {
			ftpupload($newaids, $uid);
		}
	}

	if(!$modnewthreads && $newattach && $uid == $_G['uid']) {
		updatecreditbyaction('postattach', $uid, array(), '', count($newattach), 1, $_G['fid']);
	}

	if($attachupdate) {
		$attachs = C::t('forum_attachment_n')->fetch_all_by_id('tid:'.$tid, 'aid', array_keys($attachupdate));
		foreach($attachs as $attach) {
			if(array_key_exists($attach['aid'], $attachupdate) && $attachupdate[$attach['aid']]) {
				dunlink($attach);
			}
		}
		$unusedattachs = C::t('forum_attachment_unused')->fetch_all($attachupdate);
		$attachupdate = array_flip($attachupdate);
		$unusedaids = array();
		foreach($unusedattachs as $attach) {
			if($attach['uid'] != $uid && !$_G['forum']['ismoderator']) {
				continue;
			}
			$unusedaids[] = $attach['aid'];
			$update = $attach;
			$update['dateline'] = TIMESTAMP;
			$update['remote'] = 0;
			unset($update['aid']);
			if($attach['isimage'] && $_G['setting']['watermarkstatus'] && empty($_G['forum']['disablewatermark'])) {
				$image->Watermark($_G['setting']['attachdir'].'/forum/'.$attach['attachment'], '', 'forum');
				$update['filesize'] = $image->imginfo['size'];
			}
			C::t('forum_attachment_n')->update('tid:'.$tid, $attachupdate[$attach['aid']], $update);
			@unlink($_G['setting']['attachdir'].'image/'.$attach['aid'].'_100_100.jpg');
			C::t('forum_attachment_exif')->delete($attachupdate[$attach['aid']]);
			C::t('forum_attachment_exif')->update($attach['aid'], array('aid' => $attachupdate[$attach['aid']]));
			ftpupload(array($attachupdate[$attach['aid']]), $uid);
		}
		if($unusedaids) {
			C::t('forum_attachment_unused')->delete($unusedaids);
		}
	}

	$attachcount = C::t('forum_attachment_n')->count_by_id('tid:'.$tid, $pid ? 'pid' : 'tid', $pid ? $pid : $tid);
	$attachment = 0;
	if($attachcount) {
		if(C::t('forum_attachment_n')->count_image_by_id('tid:'.$tid, $pid ? 'pid' : 'tid', $pid ? $pid : $tid)) {
			$attachment = 2;
		} else {
			$attachment = 1;
		}
	} else {
		$attachment = 0;
	}
	C::t('forum_thread')->update($tid, array('attachment'=>$attachment));
	C::t('forum_post')->update('tid:'.$tid, $pid, array('attachment' => $attachment), true);

	if(!$attachment) {
		C::t('forum_threadimage')->delete_by_tid($tid);
	}
	$_G['forum_attachexist'] = $attachment;
}




function odz_parseattach($attachpids, $attachtags, &$postlist, $skipaids = array()) {
    global $_G;
    if(!$attachpids) {
        return;
    }
    $attachpids = is_array($attachpids) ? $attachpids : array($attachpids);
    $attachexists = FALSE;
    $skipattachcode = $aids = $payaids = $findattach = array();
    foreach(C::t('forum_attachment_n')->fetch_all_by_id('tid:'.$_G['tid'], 'pid', $attachpids) as $attach) {
        $attachexists = TRUE;
        if($skipaids && in_array($attach['aid'], $skipaids)) {
            $skipattachcode[$attach[pid]][] = "/\[attach\]$attach[aid]\[\/attach\]/i";
            continue;
        }
        $attached = 0;
        $extension = strtolower(fileext($attach['filename']));
        $attach['ext'] = $extension;
        $attach['imgalt'] = $attach['isimage'] ? strip_tags(str_replace('"', '\"', $attach['description'] ? $attach['description'] : $attach['filename'])) : '';
        $attach['attachicon'] = attachtype($extension."\t".$attach['filetype']);
        $attach['attachsize'] = sizecount($attach['filesize']);
        if($attach['isimage'] && !$_G['setting']['attachimgpost']) {
            $attach['isimage'] = 0;
        }
        $attach['attachimg'] = $attach['isimage'] && (!$attach['readperm'] || $_G['group']['readaccess'] >= $attach['readperm']) ? 1 : 0;
        if($attach['attachimg']) {
            $GLOBALS['aimgs'][$attach['pid']][] = $attach['aid'];
        }
        if($attach['price']) {
            if($_G['setting']['maxchargespan'] && TIMESTAMP - $attach['dateline'] >= $_G['setting']['maxchargespan'] * 3600) {
                C::t('forum_attachment_n')->update('tid:'.$_G['tid'], $attach['aid'], array('price' => 0));
                $attach['price'] = 0;
            } elseif(!$_G['forum_attachmentdown'] && $_G['uid'] != $attach['uid']) {
                $payaids[$attach['aid']] = $attach['pid'];
            }
        }
        $attach['payed'] = $_G['forum_attachmentdown'] || $_G['uid'] == $attach['uid'] ? 1 : 0;
        $attach['url'] = ($attach['remote'] ? $_G['setting']['ftp']['attachurl'] : $_G['setting']['attachurl']).'forum/';
        $attach['dbdateline'] = $attach['dateline'];
        $attach['dateline'] = dgmdate($attach['dateline'], 'u');

        $attach['size_ex'] = '';
        $attach['url_ex'] = '';

        $attachurl = $attach['url'].$attach['attachment'].($attach['thumb'] ? '.thumb.jpg' : '');
        $urlparts = parse_url($attachurl);
        // Ö»»ñÈ¡±¾µØ¸½¼þÍ¼Æ¬µÄ³ß´ç
        if(empty($urlparts['host'])) {
            $target = DISCUZ_ROOT.$attachurl;
            $target_url = $attachurl;
            if(is_file($target) && $size = getimagesize($target)) {
                $attach['size_ex'] = $size[3];
                $attach['width'] = $size[0];
                $attach['height'] = $size[1];
            }
            $attachurl = $_G['baseurl'].$attachurl;
			//$url = parse_url($_G['siteurl']);
			//$attachurl = $url['scheme'].'://'.$url['host'].'/dz/'.$attachurl;
        }else{
            $attach['width'] = 328;
            $attach['height'] = 328;
        }
        $attach['url_ex'] = $attachurl;
        $attach['url_host_ex'] = $target_url;
        
        $postlist[$attach['pid']]['attachments'][$attach['aid']] = $attach;
        if(!defined('IN_MOBILE_API') && !empty($attachtags[$attach['pid']]) && is_array($attachtags[$attach['pid']]) && in_array($attach['aid'], $attachtags[$attach['pid']])) {
            $findattach[$attach['pid']][$attach['aid']] = "/\[attach\]$attach[aid]\[\/attach\]/i";
            $attached = 1;
        }

        if(!$attached) {
            if($attach['isimage']) {
                $postlist[$attach['pid']]['imagelist'][] = $attach['aid'];
                $postlist[$attach['pid']]['imagelistcount']++;
                if($postlist[$attach['pid']]['first']) {
                    $GLOBALS['firstimgs'][] = $attach['aid'];
                }
            } else {
                if(!$_G['forum_skipaidlist'] || !in_array($attach['aid'], $_G['forum_skipaidlist'])) {
                    $postlist[$attach['pid']]['attachlist'][] = $attach['aid'];
                }
            }
        }
        $aids[] = $attach['aid'];
    }
    if($aids) {
        $attachs = C::t('forum_attachment')->fetch_all($aids);
        foreach($attachs as $aid => $attach) {
            if($postlist[$attach['pid']]) {
                $postlist[$attach['pid']]['attachments'][$attach['aid']]['downloads'] = $attach['downloads'];
            }
        }
    }
    if($payaids) {
        foreach(C::t('common_credit_log')->fetch_all_by_uid_operation_relatedid($_G['uid'], 'BAC', array_keys($payaids)) as $creditlog) {
            $postlist[$payaids[$creditlog['relatedid']]]['attachments'][$creditlog['relatedid']]['payed'] = 1;
        }
    }
    if(!empty($skipattachcode)) {
        foreach($skipattachcode as $pid => $findskipattach) {
            foreach($findskipattach as $findskip) {
                $postlist[$pid]['message'] = preg_replace($findskip, '', $postlist[$pid]['message']);
            }
        }
    }

    if($attachexists) {
        foreach($attachtags as $pid => $aids) {
            if($findattach[$pid]) {
                foreach($findattach[$pid] as $aid => $find) {
                    // $postlist[$pid]['message'] = preg_replace($find, attachinpost($postlist[$pid]['attachments'][$aid], $postlist[$pid]), $postlist[$pid]['message'], 1);
                    $postlist[$pid]['message'] = preg_replace($find, odz_attachinpost($postlist[$pid]['attachments'][$aid], $postlist[$pid]), $postlist[$pid]['message']);
                    $postlist[$pid]['message'] = preg_replace($find, '', $postlist[$pid]['message']);
                }
            }
        }
        // echo '<pre>';echo htmlspecialchars($postlist[$pid]['message']);echo '</pre>';
        // var_dump($postlist);
    } else {
        loadcache('posttableids');
        $posttableids = $_G['cache']['posttableids'] ? $_G['cache']['posttableids'] : array('0');
        foreach($posttableids as $id) {
            C::t('forum_post')->update($id, $attachpids, array('attachment' => '0'), true);
        }
    }
}

/**
 * ½âÎöÌû×ÓÄÚÈÝÖÐµÄ¸½¼þ
 * @param array $attach ¸½¼þ
 * @param array $post Ìû×Ó
 * @return string
 */
function odz_attachinpost($attach, $post) {
    global $_G;

    //$attachurl = $attach['url'].$attach['attachment'].($attach['thumb'] ? '.thumb.jpg' : '');
    //$urlparts = parse_url($attachurl);
    //$attributes = ' ';
    //// Ö»»ñÈ¡±¾µØ¸½¼þÍ¼Æ¬µÄ³ß´ç
    //if(empty($urlparts['host'])) {
    //    $target = DISCUZ_ROOT.$attachurl;
    //    if(is_file($target) && $size = getimagesize($target)) {
    //        $attributes = sprintf(' %s ', $size[3]);
    //    }
    //    $attachurl = $_G['baseurl'].$attachurl;
    //}

    //$return = sprintf("<p><img%ssrc=\"%s\" /></p>\n", $attributes, $attachurl);
    if(stripos($attach['url_ex'], 'thumb.jpg')){
        $big_url=str_replace('.thumb.jpg','',$attach['url_ex'] );
    }
   // $return = "<p><img class='img_detail' big_url='$big_url'".(!empty($attach['size_ex']) ? $attach['size_ex'] : '').' data-original="'.$attach['url_ex'].'" src="assets/images/bitmap.png" /></p>'."\n";
	$return = "<p><img src=\"".$attach['url_ex']."\" class='img_detail' big_url='$big_url'".(!empty($attach['size_ex']) ? $attach['size_ex'] : '').' data-original="'.$attach['url_ex'].'" /></p>'."\n";

    return $return;
}

?>