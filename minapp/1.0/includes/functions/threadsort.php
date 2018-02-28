<?php

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

function odz_showsorttemplate($sortid, $fid, $sortoptionarray, $templatearray, $threadlist, $threadids = array(), $sortmode = false) {
	global $_G;

	$searchtitle = $searchvalue = $searchunit = $stemplate = $searchtids = $sortlistarray = $skipaids = $sortdata = array();

	$sortthreadlist = array();
	foreach(C::t('forum_typeoptionvar')->fetch_all_by_search($sortid, $fid, $threadids) as $sortthread) {
		$optionid = $sortthread['optionid'];
		$sortid = $sortthread['sortid'];
		$tid = $sortthread['tid'];
		$arrayoption = $sortoptionarray[$sortid][$optionid];
		if($sortoptionarray[$sortid][$optionid]['subjectshow']) {
			$_G['optionvaluelist'][$sortid][$tid][$arrayoption['identifier']]['title'] = $arrayoption['title'];
			$_G['optionvaluelist'][$sortid][$tid][$arrayoption['identifier']]['unit'] = $arrayoption['unit'];
			if(in_array($arrayoption['type'], array('radio', 'checkbox', 'select'))) {
				if($arrayoption['type'] == 'checkbox') {
					foreach(explode("\t", $sortthread['value']) as $choiceid) {
						$sortthreadlist[$tid][$arrayoption['title']] .= $arrayoption['choices'][$choiceid].'&nbsp;';
						$_G['optionvaluelist'][$sortid][$tid][$arrayoption['identifier']]['value'] .= $arrayoption['choices'][$choiceid].'&nbsp;';
					}
				} elseif($arrayoption['type'] == 'select') {
					// $sortthreadlist[$tid][$arrayoption['title']] = $_G['optionvaluelist'][$sortid][$tid][$arrayoption['identifier']]['value'] = $arrayoption['choices'][$sortthread['value']]['content'];
					$sortthreadlist[$tid][$arrayoption['identifier']] = $_G['optionvaluelist'][$sortid][$tid][$arrayoption['identifier']]['value'] = $arrayoption['choices'][$sortthread['value']]['content'];
				} else {
					// $sortthreadlist[$tid][$arrayoption['title']] = $_G['optionvaluelist'][$sortid][$tid][$arrayoption['identifier']]['value'] = $arrayoption['choices'][$sortthread['value']];
					$sortthreadlist[$tid][$arrayoption['identifier']] = $_G['optionvaluelist'][$sortid][$tid][$arrayoption['identifier']]['value'] = $arrayoption['choices'][$sortthread['value']];
				}
			} elseif($arrayoption['type'] == 'image') {
				$imgoptiondata = dunserialize($sortthread['value']);
				if(empty($templatearray[$sortid])) {
					$maxwidth = $arrayoption['maxwidth'] ? 'width="'.$arrayoption['maxwidth'].'"' : '';
					$maxheight = $arrayoption['maxheight'] ? 'height="'.$arrayoption['maxheight'].'"' : '';
					// $sortthreadlist[$tid][$arrayoption['title']] = $_G['optionvaluelist'][$sortid][$tid][$arrayoption['identifier']]['value'] = $imgoptiondata['url'] ? "<img src=\"$imgoptiondata[url]\" onload=\"thumbImg(this)\" $maxwidth $maxheight border=\"0\">" : '';
					$sortthreadlist[$tid][$arrayoption['identifier']] = $_G['optionvaluelist'][$sortid][$tid][$arrayoption['identifier']]['value'] = $imgoptiondata['url'] ? "<img src=\"$imgoptiondata[url]\" onload=\"thumbImg(this)\" $maxwidth $maxheight border=\"0\">" : '';
				} else {
					$sortthread['value'] = '';
					if($imgoptiondata['aid']) {
						// $sortthread['value'] = getforumimg($imgoptiondata['aid'], 0, 120, 120);
						$sortthread['value'] = $imgoptiondata['url'];
					} elseif($imgoptiondata['url']) {
						$sortthread['value'] = $imgoptiondata['url'];
					}
					// $sortthreadlist[$tid][$arrayoption['title']] = $_G['optionvaluelist'][$sortid][$tid][$arrayoption['identifier']]['value'] = $sortthread['value'] ? $sortthread['value'] : './static/image/common/nophotosmall.gif';
					$sortthreadlist[$tid][$arrayoption['identifier']] = $_G['optionvaluelist'][$sortid][$tid][$arrayoption['identifier']]['value'] = $sortthread['value'] ? $sortthread['value'] : './static/image/common/nophotosmall.gif';
				}
			} else {
				// $sortthreadlist[$tid][$arrayoption['title']] = $_G['optionvaluelist'][$sortid][$tid][$arrayoption['identifier']]['value'] = $sortthread['value'] ? $sortthread['value'] : $arrayoption['defaultvalue'];
				$sortthreadlist[$tid][$arrayoption['identifier']] = $_G['optionvaluelist'][$sortid][$tid][$arrayoption['identifier']]['value'] = $sortthread['value'] ? $sortthread['value'] : $arrayoption['defaultvalue'];
			}
			$sortthreadlist[$tid]['sortid'] = $sortid;
			$sortthreadlist[$tid]['expiration'] = $sortthread['expiration'] && $sortthread['expiration'] <= TIMESTAMP ? 1 : 0;
		}
	}

	return $sortthreadlist;
}

function odz_threadsortshow($sortid, $tid) {
	global $_G;

	loadcache(array('threadsort_option_'.$sortid, 'threadsort_template_'.$sortid));
	$sortoptionarray = $_G['cache']['threadsort_option_'.$sortid];
	$templatearray = $_G['cache']['threadsort_template_'.$sortid];
	$threadsortshow = $optiondata = $searchtitle = $searchvalue = $searchunit = $memberinfofield = $_G['forum_option'] = array();
	if($sortoptionarray) {

		foreach(C::t('forum_typeoptionvar')->fetch_all_by_tid_optionid($tid) as $option) {
			$optiondata[$option['optionid']]['value'] = $option['value'];
			$optiondata[$option['optionid']]['expiration'] = $option['expiration'] && $option['expiration'] <= TIMESTAMP ? 1 : 0;
			$sortdataexpiration = $option['expiration'];
		}
		foreach($sortoptionarray as $optionid => $option) {
			$_G['forum_option'][$option['identifier']]['title'] = $option['title'];
			$_G['forum_option'][$option['identifier']]['unit'] = $option['unit'];
			$_G['forum_option'][$option['identifier']]['type'] = $option['type'];
			if(($option['expiration'] && !$optiondata[$optionid]['expiration']) || empty($option['expiration'])) {
				if(!protectguard($option['protect'])) {
					if($option['type'] == 'checkbox') {
						$_G['forum_option'][$option['identifier']]['value'] = '';
						foreach(explode("\t", $optiondata[$optionid]['value']) as $choiceid) {
							$_G['forum_option'][$option['identifier']]['value'] .= $option['choices'][$choiceid].'&nbsp;';
						}
					} elseif($option['type'] == 'radio') {
						$_G['forum_option'][$option['identifier']]['value'] = $option['choices'][$optiondata[$optionid]['value']];
					} elseif($option['type'] == 'select') {
						$tmpchoiceid = $tmpidentifiervalue = array();
						foreach(explode('.', $optiondata[$optionid]['value']) as $choiceid) {
							$tmpchoiceid[] = $choiceid;
							$tmpidentifiervalue[] = $option['choices'][implode('.', $tmpchoiceid)];
						}
						$_G['forum_option'][$option['identifier']]['value'] = implode(' &raquo; ', $tmpidentifiervalue);
						unset($tmpchoiceid, $tmpidentifiervalue);
					} elseif($option['type'] == 'image') {
                                               // unset($_G['forum_option'][$option['identifier']]);
						$imgoptiondata = dunserialize($optiondata[$optionid]['value']);
						if($imgoptiondata['aid']){
							if(strpos($imgoptiondata['url'],'http') !== false){
								$img_url = $imgoptiondata['url'];
							}else{
								$img_url = $_G['baseurl'].$imgoptiondata['url'];
							}
							$imgoptiondata['url'] = $img_url;
							$threadsortshow['sortaids'][] = $imgoptiondata['aid'];
							$_G['forum_option'][$option['identifier']]['value'] = $imgoptiondata['url'] ? "<img src=\"".$imgoptiondata['url']."\" $maxheight $maxwidth border=\"0\">" : '';
							 if(empty($templatearray['viewthread'])) {
								$maxwidth = $option['maxwidth'] ? 'width="'.$option['maxwidth'].'"' : '';
								$maxheight = $option['maxheight'] ? 'height="'.$option['maxheight'].'"' : '';
								//if(!defined('IN_MOBILE')) {
								//	$_G['forum_option'][$option['identifier']]['value'] = $imgoptiondata['url'] ? "<img src=\"".$imgoptiondata['url']."\" onload=\"thumbImg(this)\" $maxwidth $maxheight border=\"0\">" : '';
								//} else {
								//	$_G['forum_option'][$option['identifier']]['value'] = $imgoptiondata['url'] ? "<a href=\"".$imgoptiondata['url']."\" target=\"_blank\">".lang('forum/misc', 'click_view')."</a>" : '';
								//}
							 } else {
								 $_G['forum_option'][$option['identifier']]['value'] = $imgoptiondata['url'] ? $imgoptiondata['url'] : './static/image/common/nophoto.gif';
							 }
						}else{
							unset($_G['forum_option'][$option['identifier']]);
						}
					} elseif($option['type'] == 'url') {
						$_G['forum_option'][$option['identifier']]['value'] = $optiondata[$optionid]['value'] ? "<a href=\"".$optiondata[$optionid]['value']."\" target=\"_blank\">".$optiondata[$optionid]['value']."</a>" : '';
					} elseif($option['type'] == 'number') {
						$_G['forum_option'][$option['identifier']]['value'] = $optiondata[$optionid]['value'];
					} else {
						if($option['protect']['status'] && $optiondata[$optionid]['value']) {
                                                     $_G['forum_option'][$option['identifier']]['value']=$optiondata[$optionid]['value'];
//							$optiondata[$optionid]['value'] = $option['protect']['mode'] == 1 ?
//                                                                '<image src="/'.stringtopic($optiondata[$optionid]['value']).'">' : (!defined('IN_MOBILE') ?
//                                                                '<span id="sortmessage_'.$option['identifier'].'">'
//                                                                . '<a href="###" onclick="ajaxget(\'forum.php?mod=misc&action=protectsort&tid='.$tid.'&optionid='.$optionid.'\', \'sortmessage_'.$option['identifier'].'\');return false;">'.lang('forum/misc', 'click_view').'</a>'
//                                                                        . '</span>' : $optiondata[$optionid]['value']);
//                                                          '<span id="sortmessage_'.$option['identifier'].'">'
//                                                                .$optiondata[$optionid]['value']. '</span>' : $optiondata[$optionid]['value']);
//                                                        $optiondata[$optionid]['value'] = $option['protect']['mode'] == 1 ? '<image src="'.stringtopic($optiondata[$optionid]['value']).'">' : (!defined('IN_MOBILE') ? '<span id="sortmessage_'.$option['identifier'].'"><a href="###" onclick="ajaxget(\'forum.php?mod=misc&action=protectsort&tid='.$tid.'&optionid='.$optionid.'\', \'sortmessage_'.$option['identifier'].'\');return false;">'.lang('forum/misc', 'click_view').'</a></span>' : $optiondata[$optionid]['value']);
//							$_G['forum_option'][$option['identifier']]['value'] = $optiondata[$optionid]['value'] ? $optiondata[$optionid]['value'] : $option['defaultvalue'];

                                                } elseif($option['type'] == 'textarea') {
							$_G['forum_option'][$option['identifier']]['value'] = $optiondata[$optionid]['value'] ? nl2br($optiondata[$optionid]['value']) : '';
						} else {
							$_G['forum_option'][$option['identifier']]['value'] = $optiondata[$optionid]['value'] ? $optiondata[$optionid]['value'] : $option['defaultvalue'];
						}
					}

				} else {

					if(empty($option['permprompt'])) {
						$_G['forum_option'][$option['identifier']]['value'] = lang('forum/misc', 'view_noperm');
					} else {
						$_G['forum_option'][$option['identifier']]['value'] = $option['permprompt'];
					}

				}

			} else {
				$_G['forum_option'][$option['identifier']]['value'] = lang('forum/misc', 'has_expired');
			}

		}

		$typetemplate = '';
		if($templatearray['viewthread']) {
			foreach($sortoptionarray as $option) {
				$searchtitle[] = '/{('.$option['identifier'].')}/e';
				$searchvalue[] = '/\[('.$option['identifier'].')value\]/e';
				$searchvalue[] = '/{('.$option['identifier'].')_value}/e';
				$searchunit[] = '/\[('.$option['identifier'].')unit\]/e';
				$searchunit[] = '/{('.$option['identifier'].')_unit}/e';
			}
			$threadexpiration = $sortdataexpiration ? dgmdate($sortdataexpiration) : lang('forum/misc', 'never_expired');
			$typetemplate = preg_replace(array("/\{expiration\}/i"), array($threadexpiration), stripslashes($templatearray['viewthread']));
			$typetemplate = preg_replace($searchtitle, "showoption('\\1', 'title')", $typetemplate);
			$typetemplate = preg_replace($searchvalue, "showoption('\\1', 'value')", $typetemplate);
			$typetemplate = preg_replace($searchunit, "showoption('\\1', 'unit')", $typetemplate);
		}
	}
	$threadsortshow['optionlist'] = !$optionexpiration ? $_G['forum_option'] : 'expire';
	$threadsortshow['typetemplate'] = $typetemplate;
	$threadsortshow['expiration'] = dgmdate($sortdataexpiration, 'd');
	return $threadsortshow;
}
function clear_blank($str, $glue=''){
    $replace = array(" ", "\r", "\n", "\t"); return str_replace($replace, $glue, $str);
}
function odz_threadsortshow_3($sortid, $tid) {
	global $_G;
	loadcache(array('threadsort_option_'.$sortid, 'threadsort_template_'.$sortid));
	$sortoptionarray = $_G['cache']['threadsort_option_'.$sortid];
	$templatearray = $_G['cache']['threadsort_template_'.$sortid];
	$threadsortshow = $optiondata = $searchtitle = $searchvalue = $searchunit = $memberinfofield = $_G['forum_option'] = array();
	if($sortoptionarray) {
		foreach(C::t('forum_typeoptionvar')->fetch_all_by_tid_optionid($tid) as $option) {
			$optiondata[$option['optionid']]['value'] = $option['value'];
			$optiondata[$option['optionid']]['expiration'] = $option['expiration'] && $option['expiration'] <= TIMESTAMP ? 1 : 0;
			$sortdataexpiration = $option['expiration'];
		}
                $forum_option=$sortoptionarray;
//              print_R($sortoptionarray);exit;
		foreach($sortoptionarray as $optionid => $option) {
			if(($option['expiration'] && !$optiondata[$optionid]['expiration']) || empty($option['expiration'])) {
				if(!protectguard($option['protect'])) {
					if($option['type'] == 'checkbox') {
						$forum_option[$optionid]['value'] = '';
						foreach(explode("\t", $optiondata[$optionid]['value']) as $choiceid) {
							$forum_option[$optionid]['value'] .= $option['choices'][$choiceid].'&nbsp;';
						}
					} elseif($option['type'] == 'radio') {
						$forum_option[$optionid]['value'] = $option['choices'][$optiondata[$optionid]['value']];
					} elseif($option['type'] == 'select') {
						$tmpchoiceid = $tmpidentifiervalue = array();
						foreach(explode('.', $optiondata[$optionid]['value']) as $choiceid) {
							$tmpchoiceid[] = $choiceid;
							$tmpidentifiervalue[] = $option['choices'][implode('.', $tmpchoiceid)];
						}
                                                $forum_option[$optionid]['value'] = $tmpidentifiervalue;
						unset($tmpchoiceid, $tmpidentifiervalue);
					} elseif($option['type'] == 'image') {
						$imgoptiondata = dunserialize($optiondata[$optionid]['value']);
						if($imgoptiondata['aid']){
                                                    if(strpos($imgoptiondata['url'],'http') !== false){
                                                            $img_url = $imgoptiondata['url'];
                                                    }else{
                                                            $img_url = $_G['baseurl'].$imgoptiondata['url'];
                                                    }
                                                    $imgoptiondata['url'] = $img_url;
                                                    $threadsortshow['sortaids'][] = $imgoptiondata['aid'];
                                                    $forum_option[$optionid]['value'] = $imgoptiondata['url'] ? "<img src=\"".$imgoptiondata['url']."\" $maxheight $maxwidth border=\"0\">" : '';
                                                     if(empty($templatearray['viewthread'])) {
                                                            $maxwidth = $option['maxwidth'] ? 'width="'.$option['maxwidth'].'"' : '';
                                                            $maxheight = $option['maxheight'] ? 'height="'.$option['maxheight'].'"' : '';
                                                     } else {
                                                             $forum_option[$optionid]['value'] = $imgoptiondata['url'] ? $imgoptiondata['url'] : './static/image/common/nophoto.gif';
                                                     }
						}else{
                                                    unset($forum_option[$optionid]);
						}
					} elseif($option['type'] == 'url') {
						$forum_option[$optionid]['value'] = $optiondata[$optionid]['value'] ? "<a href=\"".$optiondata[$optionid]['value']."\" target=\"_blank\">".$optiondata[$optionid]['value']."</a>" : '';
					} elseif($option['type'] == 'number') {
						$forum_option[$optionid]['value'] = $optiondata[$optionid]['value'];
					} else {
                                            if($option['protect']['status'] && $optiondata[$optionid]['value']) {
                                                    $optiondata[$optionid]['value'] = $option['protect']['mode'] = $optiondata[$optionid]['value'];
                                                    $forum_option[$optionid]['value'] = $optiondata[$optionid]['value'] ? $optiondata[$optionid]['value'] : '';
                                            } elseif($option['type'] == 'textarea') {
                                                    $forum_option[$optionid]['value'] = $optiondata[$optionid]['value'] ? nl2br($optiondata[$optionid]['value']) : '';
                                            } else {
                                                    $forum_option[$optionid]['value'] = $optiondata[$optionid]['value'] ? $optiondata[$optionid]['value'] : '';
                                            }
                                       }
				} else {
					if(empty($option['permprompt'])) {
						$forum_option[$optionid]['value'] = lang('forum/misc', 'view_noperm');
					} else {
						$forum_option[$optionid]['value'] = $option['permprompt'];
					}
				}
			} else {
				$forum_option[$optionid]['value'] = lang('forum/misc', 'has_expired');
			}
		}

                foreach($forum_option as $val) {
                    if($val['defaultvalue']==null){
                        $val['defaultvalue']="";
                    }
                    if($val['profile']==null){
                        $val['profile']="";
                    }
                    if($val['type']=='radio'||$val['type']=='checkbox'||$val['type']=='select'){
                        if($val['type']=='select'){
                            if(is_array($val['value'])){
                              foreach($val['value'] as $vpl) {
                                    $val['value']=$vpl['optionid'];
                                }
                            }
                        }
                        foreach($val['choices'] as $vasl) {
                            $vp['choices'][]=$vasl;
                        }
                       $val['rules']['choices']=$vp['choices'];
                       unset($vp['choices']);
                       unset($val['choices']);
                    }
                    if($val['type']=='image'){
                        preg_match('/<img.+src=\"?(.+\.(jpg|gif|bmp|bnp|png))\"?.+>/i',$val['value'],$match);
                        $val['value']=isset($match[1])?$match[1]:"";
                    }
                    if($val['type']=='select'){
                       foreach($val['rules']['choices'] as $ks1=>$val1) {
                          $string1= $val1['optionid']."=". $val1['content'];
                          $strings['choices'][$ks1]=$string1;
                       }
                      $val['rules']=$strings;
                    }
                    $_G['forum_option'][]=$val;
                }
	}
	$threadsortshow['optionlist'] = !$optionexpiration ? $_G['forum_option'] : 'expire';
	return $threadsortshow;
}

function odz_threadsort_validator($sortoption, $pid) {
	global $_G, $var;
	$postaction = $_G['tid'] && $pid ? "edit&tid=$_G[tid]&pid=$pid" : 'newthread';
	$_G['forum_optiondata'] = array();
	foreach($_G['forum_checkoption'] as $var => $option) {
        if($sortoption[$var] !== '' && checkemoji($sortoption[$var])) {
            odz_error('emoji_not_supported');
        }

		if($_G['forum_checkoption'][$var]['required'] && (($sortoption[$var] === '' && $_G['forum_checkoption'][$var]['type'] != 'number') || ($sortoption[$var] == '0' && $_G['forum_checkoption'][$var]['type'] == 'number'))) {
			showmessage('threadtype_required_invalid', "forum.php?mod=post&action=$postaction&fid=$_G[fid]&sortid=".$_G['forum_selectsortid'], array('typetitle' => $_G['forum_checkoption'][$var]['title']));
		} elseif($sortoption[$var] && ($_G['forum_checkoption'][$var]['type'] == 'number' && !is_numeric($sortoption[$var]) || $_G['forum_checkoption'][$var]['type'] == 'email' && !isemail($sortoption[$var]))){
			showmessage('threadtype_format_invalid', "forum.php?mod=post&action=$postaction&fid=$_G[fid]&sortid=".$_G['forum_selectsortid'], array('typetitle' => $_G['forum_checkoption'][$var]['title']));
		} elseif($sortoption[$var] && $_G['forum_checkoption'][$var]['maxlength'] && strlen($sortoption[$var]) > $_G['forum_checkoption'][$var]['maxlength']) {
			showmessage('threadtype_toolong_invalid', "forum.php?mod=post&action=$postaction&fid=$_G[fid]&sortid=".$_G['forum_selectsortid'], array('typetitle' => $_G['forum_checkoption'][$var]['title']));
		} elseif($sortoption[$var] && (($_G['forum_checkoption'][$var]['maxnum'] && $sortoption[$var] > $_G['forum_checkoption'][$var]['maxnum']) || ($_G['forum_checkoption'][$var]['minnum'] && $sortoption[$var] < $_G['forum_checkoption'][$var]['minnum']))) {
			showmessage('threadtype_num_invalid', "forum.php?mod=post&action=$postaction&fid=$_G[fid]&sortid=".$_G['forum_selectsortid'], array('typetitle' => $_G['forum_checkoption'][$var]['title']));
		} elseif($sortoption[$var] && $_G['forum_checkoption'][$var]['unchangeable'] && !($_G['tid'] && $pid)) {
			showmessage('threadtype_unchangeable_invalid', "forum.php?mod=post&action=$postaction&fid=$_G[fid]&sortid=".$_G['forum_selectsortid'], array('typetitle' => $_G['forum_checkoption'][$var]['title']));
		} elseif($sortoption[$var] && ($_G['forum_checkoption'][$var]['type'] == 'select')) {
			if($_G['forum_optionlist'][$_G['forum_checkoption'][$var]['optionid']]['choices'][$sortoption[$var]]['level'] != 1) {
				showmessage('threadtype_select_invalid', "forum.php?mod=post&action=$postaction&fid=$_G[fid]&sortid=".$_G['forum_selectsortid'], array('typetitle' => $_G['forum_checkoption'][$var]['title']));
			}
		}
		if($_G['forum_checkoption'][$var]['type'] == 'checkbox') {
			$sortoption[$var] = $sortoption[$var] ? implode("\t", $sortoption[$var]) : '';
		} elseif($_G['forum_checkoption'][$var]['type'] == 'url') {
			$sortoption[$var] = $sortoption[$var] ? (substr(strtolower($sortoption[$var]), 0, 4) == 'www.' ? 'http://'.$sortoption[$var] : $sortoption[$var]) : '';
		}

		if($_G['forum_checkoption'][$var]['type'] == 'image') {
			if($sortoption[$var]['aid']) {
				$_GET['attachnew'][$sortoption[$var]['aid']] = $sortoption[$var];
			}
			$sortoption[$var] = serialize($sortoption[$var]);
		} elseif($_G['forum_checkoption'][$var]['type'] == 'select') {
			$sortoption[$var] = censor(trim($sortoption[$var]));
		} else {
			$sortoption[$var] = dhtmlspecialchars(censor(trim($sortoption[$var])));
		}
		$_G['forum_optiondata'][$_G['forum_checkoption'][$var]['optionid']] = $sortoption[$var];
	}

	return $_G['forum_optiondata'];
}

?>
