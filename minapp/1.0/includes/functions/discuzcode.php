<?php
function odz_parseimg($width, $height, $src, $lazyload, $pid, $extra = '') {
	global $_G;
	if(substr($src,0,6) == "static" ){
		$src = $_G['baseurl'].$src;
	}
	static $styleoutput = null;
	if($_G['setting']['domainwhitelist_affectimg']) {
		$tmp = parse_url($src);
		if(!empty($tmp['host']) && !iswhitelist($tmp['host'])) {
			return $src;
		}
	}
	if(strstr($src, 'file:') || substr($src, 1, 1) == ':') {
		return $src;
	}
	if($width > $_G['setting']['imagemaxwidth']) {
		$height = intval($_G['setting']['imagemaxwidth'] * $height / $width);
		$width = $_G['setting']['imagemaxwidth'];
		if(defined('IN_MOBILE')) {
			$extra = '';
		} else {
			$extra = 'onmouseover="img_onmouseoverfunc(this)" onclick="zoom(this)" style="cursor:pointer"';
		}
	}
	$attrsrc = !IS_ROBOT && $lazyload ? 'file' : 'src';
	$rimg_id = random(5);
	$GLOBALS['aimgs'][$pid][] = $rimg_id;
	$guestviewthumb = !empty($_G['setting']['guestviewthumb']['flag']) && empty($_G['uid']);
	$img = '';
	if($guestviewthumb) {
		if(!isset($styleoutput)) {
			$img .= guestviewthumbstyle();
			$styleoutput = true;
		}
		$img .= '<div class="guestviewthumb"><img class="img_detail" id="aimg_'.$rimg_id.'" class="guestviewthumb_cur" onclick="showWindow(\'login\', \'{loginurl}\'+\'&referer=\'+encodeURIComponent(location))" '.$attrsrc.'="{url}" border="0" alt="" />
				<br><a href="{loginurl}" onclick="showWindow(\'login\', this.href+\'&referer=\'+encodeURIComponent(location));">'.lang('forum/template', 'guestviewthumb').'</a></div>';

	} else {
		// if(defined('IN_MOBILE')) {
		// 	$img = '<img'.($width > 0 ? ' width="'.$width.'"' : '').($height > 0 ? ' height="'.$height.'"' : '').' class="img_detail" src="{url}" border="0" alt="" />';
		// } else {
		// 	$img = '<img id="aimg_'.$rimg_id.'" onclick="zoom(this, this.src, 0, 0, '.($_G['setting']['showexif'] ? 1 : 0).')" class="zoom img_detail"'.($width > 0 ? ' width="'.$width.'"' : '').($height > 0 ? ' height="'.$height.'"' : '').' '.$attrsrc.'="{url}" '.($extra ? $extra.' ' : '').'border="0" alt="" />';
		// }
        $img = '<p><img class="img_detail" src="{url}" data-original="{url}" big_url="{url}" /></p>';
	}
	$code = bbcodeurl($src, $img);
	if($guestviewthumb) {
		$code = str_replace('{loginurl}', 'member.php?mod=logging&action=login', $code);
	}
	return $code;
}
function odz_discuzcode($message, $smileyoff, $bbcodeoff, $htmlon = 0, $allowsmilies = 1, $allowbbcode = 1, $allowimgcode = 1, $allowhtml = 0, $jammer = 0, $parsetype = '0', $authorid = '0', $allowmediacode = '0', $pid = 0, $lazyload = 0, $pdateline = 0, $first = 0) {
    global $_G;
    static $authorreplyexist;

    if($pid && strpos($message, '[/password]') !== FALSE) {
        if($authorid != $_G['uid'] && !$_G['forum']['ismoderator']) {
            $message = preg_replace("/\s?\[password\](.+?)\[\/password\]\s?/ie", "parsepassword('\\1', \$pid)", $message);
            if($_G['forum_discuzcode']['passwordlock'][$pid]) {
                return '';
            }
        } else {
            $message = preg_replace("/\s?\[password\](.+?)\[\/password\]\s?/ie", "", $message);
            $_G['forum_discuzcode']['passwordauthor'][$pid] = 1;
        }
    }

    if($parsetype != 1 && !$bbcodeoff && $allowbbcode && (strpos($message, '[/code]') || strpos($message, '[/CODE]')) !== FALSE) {
        $message = preg_replace("/\s?\[code\](.+?)\[\/code\]\s?/ies", "codedisp('\\1')", $message);
    }

    $msglower = strtolower($message);

    $htmlon = $htmlon && $allowhtml ? 1 : 0;

    if(!$htmlon) {
        $message = dhtmlspecialchars($message);
    } else {
        $message = preg_replace("/<script[^\>]*?>(.*?)<\/script>/i", '', $message);
    }
    

    if($_G['setting']['plugins']['func'][HOOKTYPE]['discuzcode']) {
        $_G['discuzcodemessage'] = & $message;
        $param = func_get_args();
        hookscript('discuzcode', 'global', 'funcs', array('param' => $param, 'caller' => 'discuzcode'), 'discuzcode');
    }

    if(!$smileyoff && $allowsmilies) {
        // $message = parsesmiles($message);
        $message = odz_parsesmiles($message);
    }
      
    if($_G['setting']['allowattachurl'] && strpos($msglower, 'attach://') !== FALSE) {
        $message = preg_replace("/attach:\/\/(\d+)\.?(\w*)/ie", "parseattachurl('\\1', '\\2', 1)", $message);
    }

    if($allowbbcode) {
        if(strpos($msglower, 'ed2k://') !== FALSE) {
            $message = preg_replace("/ed2k:\/\/(.+?)\//e", "parseed2k('\\1')", $message);
        }
    }

    if(!$bbcodeoff && $allowbbcode) {
        if(strpos($msglower, '[/url]') !== FALSE) {
            $message = preg_replace("/\[url(=((https?|ftp|gopher|news|telnet|rtsp|mms|callto|bctp|thunder|qqdl|synacast){1}:\/\/|www\.|mailto:)?([^\r\n\[\"']+?))?\](.+?)\[\/url\]/ies", "parseurl('\\1', '\\5', '\\2')", $message);
        }
        if(strpos($msglower, '[/email]') !== FALSE) {
            $message = preg_replace("/\[email(=([a-z0-9\-_.+]+)@([a-z0-9\-_]+[.][a-z0-9\-_.]+))?\](.+?)\[\/email\]/ies", "parseemail('\\1', '\\4')", $message);
        }

        $nest = 0;
        while(strpos($msglower, '[table') !== FALSE && strpos($msglower, '[/table]') !== FALSE){
            $message = preg_replace("/\[table(?:=(\d{1,4}%?)(?:,([\(\)%,#\w ]+))?)?\]\s*(.+?)\s*\[\/table\]/ies", "parsetable('\\1', '\\2', '\\3')", $message);
            if(++$nest > 4) break;
        }

        $message = str_replace(array(
            '[/color]', '[/backcolor]', '[/size]', '[/font]', '[/align]', '[b]', '[/b]', '[s]', '[/s]', '[hr]', '[/p]',
            '[i=s]', '[i]', '[/i]', '[u]', '[/u]', '[list]', '[list=1]', '[list=a]',
            '[list=A]', "\r\n[*]", '[*]', '[/list]', '[indent]', '[/indent]', '[/float]'
        ), array(
            '</font>', '</font>', '</font>', '</font>', '</div>', '<strong>', '</strong>', '<strike>', '</strike>', '<hr class="l" />', '</p>', '<i class="pstatus">', '<i>',
            '</i>', '<u>', '</u>', '<ul>', '<ul type="1" class="litype_1">', '<ul type="a" class="litype_2">',
            '<ul type="A" class="litype_3">', '<li>', '<li>', '</ul>', '<blockquote>', '</blockquote>', '</span>'
        ), preg_replace(array(
            "/\[color=([#\w]+?)\]/i",
            "/\[color=((rgb|rgba)\([\d\s,]+?\))\]/i",
            "/\[backcolor=([#\w]+?)\]/i",
            "/\[backcolor=((rgb|rgba)\([\d\s,]+?\))\]/i",
            "/\[size=(\d{1,2}?)\]/i",
            "/\[size=(\d{1,2}(\.\d{1,2}+)?(px|pt)+?)\]/i",
            "/\[font=([^\[\<]+?)\]/i",
            "/\[align=(left|center|right)\]/i",
            "/\[p=(\d{1,2}|null), (\d{1,2}|null), (left|center|right)\]/i",
            "/\[float=left\]/i",
            "/\[float=right\]/i"

        ), array(
            "<font color=\"\\1\">",
            "<font style=\"color:\\1\">",
            "<font style=\"background-color:\\1\">",
            "<font style=\"background-color:\\1\">",
            "<font size=\"\\1\">",
            "<font style=\"font-size:\\1\">",
            "<font face=\"\\1\">",
            "<div align=\"\\1\">",
            "<p style=\"line-height:\\1px;text-indent:\\2em;text-align:\\3\">",
            "<span style=\"float:left;margin-right:5px\">",
            "<span style=\"float:right;margin-left:5px\">"
        ), $message));
   
        if($pid && !defined('IN_MOBILE')) {
            $message = preg_replace("/\s?\[postbg\]\s*([^\[\<\r\n;'\"\?\(\)]+?)\s*\[\/postbg\]\s?/ies", "parsepostbg('\\1', '$pid')", $message);
        } else {
            $message = preg_replace("/\s?\[postbg\]\s*([^\[\<\r\n;'\"\?\(\)]+?)\s*\[\/postbg\]\s?/is", "", $message);
        }

        if($parsetype != 1) {
            if(strpos($msglower, '[/quote]') !== FALSE) {
                $message = preg_replace("/\s?\[quote\][\n\r]*(.+?)[\n\r]*\[\/quote\]\s?/is", tpl_quote(), $message);
            }
            if(strpos($msglower, '[/free]') !== FALSE) {
                $message = preg_replace("/\s*\[free\][\n\r]*(.+?)[\n\r]*\[\/free\]\s*/is", tpl_free(), $message);
            }
        }
               
        if(!defined('IN_MOBILE')) {
            if(strpos($msglower, '[/media]') !== FALSE) {
                $message = preg_replace("/\[media=([\w,]+)\]\s*([^\[\<\r\n]+?)\s*\[\/media\]/ies", $allowmediacode ? "parsemedia('\\1', '\\2')" : "bbcodeurl('\\2', '<a href=\"{url}\" target=\"_blank\">{url}</a>')", $message);
            }
            if(strpos($msglower, '[/audio]') !== FALSE) {
                $message = preg_replace("/\[audio(=1)*\]\s*([^\[\<\r\n]+?)\s*\[\/audio\]/ies", $allowmediacode ? "parseaudio('\\2', 400)" : "bbcodeurl('\\2', '<a href=\"{url}\" target=\"_blank\">{url}</a>')", $message);
            }
            if(strpos($msglower, '[/flash]') !== FALSE) {
                $message = preg_replace("/\[flash(=(\d+),(\d+))?\]\s*([^\[\<\r\n]+?)\s*\[\/flash\]/ies", $allowmediacode ? "parseflash('\\2', '\\3', '\\4');" : "bbcodeurl('\\4', '<a href=\"{url}\" target=\"_blank\">{url}</a>')", $message);
            }
        } else {
            if(strpos($msglower, '[/media]') !== FALSE) {
                $message = preg_replace("/\[media=([\w,]+)\]\s*([^\[\<\r\n]+?)\s*\[\/media\]/is", "[media]\\2[/media]", $message);
            }
            if(strpos($msglower, '[/audio]') !== FALSE) {
                $message = preg_replace("/\[audio(=1)*\]\s*([^\[\<\r\n]+?)\s*\[\/audio\]/is", "[media]\\2[/media]", $message);
            }
            if(strpos($msglower, '[/flash]') !== FALSE) {
                $message = preg_replace("/\[flash(=(\d+),(\d+))?\]\s*([^\[\<\r\n]+?)\s*\[\/flash\]/is", "[media]\\4[/media]", $message);
            }
        }

        if($parsetype != 1 && $allowbbcode < 0 && isset($_G['cache']['bbcodes'][-$allowbbcode])) {
            $message = preg_replace($_G['cache']['bbcodes'][-$allowbbcode]['searcharray'], $_G['cache']['bbcodes'][-$allowbbcode]['replacearray'], $message);
        }

        if($parsetype != 1 && strpos($msglower, '[/hide]') !== FALSE && $pid) {
            if($_G['setting']['hideexpiration'] && $pdateline && (TIMESTAMP - $pdateline) / 86400 > $_G['setting']['hideexpiration']) {
                $message = preg_replace("/\[hide[=]?(d\d+)?[,]?(\d+)?\]\s*(.*?)\s*\[\/hide\]/is", "\\3", $message);
                $msglower = strtolower($message);
            }

            if(strpos($msglower, '[hide=d') !== FALSE) {
                $message = preg_replace("/\[hide=(d\d+)?[,]?(\d+)?\]\s*(.*?)\s*\[\/hide\]/ies", "expirehide('\\1','\\2','\\3', $pdateline)", $message);
                $msglower = strtolower($message);
            }

            if(strpos($msglower, '[hide]') !== FALSE) {
                if($authorreplyexist === null) {
                    if(!$_G['forum']['ismoderator']) {
                        if($_G['uid']) {
                            $authorreplyexist = C::t('forum_post')->fetch_pid_by_tid_authorid($_G['tid'], $_G['uid']);
                        }
                    } else {
                        $authorreplyexist = TRUE;
                    }
                }

                if($authorreplyexist) {

                    $message = preg_replace("/\[hide\]\s*(.*?)\s*\[\/hide\]/is", tpl_hide_reply(), $message);
                } else {

                    $message = preg_replace("/\[hide\](.*?)\[\/hide\]/is", odz_tpl_hide_reply_hidden(), $message);

                    $message = '<script type="text/javascript">replyreload += \',\' + '.$pid.';</script>'.$message;
                }

            }

            if(strpos($msglower, '[hide=') !== FALSE) {
                $message = preg_replace("/\[hide=(\d+)\]\s*(.*?)\s*\[\/hide\]/ies", "creditshide(\\1,'\\2', $pid, $authorid)", $message);
            }

        }
    }

    if(!$bbcodeoff) {
        if($parsetype != 1 && strpos($msglower, '[swf]') !== FALSE) {
            $message = preg_replace("/\[swf\]\s*([^\[\<\r\n]+?)\s*\[\/swf\]/ies", "bbcodeurl('\\1', ' <img src=\"'.STATICURL.'image/filetype/flash.gif\" align=\"absmiddle\" alt=\"\" /> <a href=\"{url}\" target=\"_blank\">Flash: {url}</a> ')", $message);
        }

//        if(defined('IN_MOBILE') && !defined('TPL_DEFAULT') && !defined('IN_MOBILE_API')) {
//            $allowimgcode = false;
////        }
        
        $attrsrc = !IS_ROBOT && $lazyload ? 'file' : 'src';
        if(strpos($msglower, '[/img]') !== FALSE) {
            $message = preg_replace(array(
                "/\[img\]\s*([^\[\<\r\n]+?)\s*\[\/img\]/ies",
                "/\[img=(\d{1,4})[x|\,](\d{1,4})\]\s*([^\[\<\r\n]+?)\s*\[\/img\]/ies"
            ), $allowimgcode ? array(
                "odz_parseimg(0, 0, '\\1', ".intval($lazyload).", ".intval($pid).", 'onmouseover=\"img_onmouseoverfunc(this)\" ".($lazyload ? "lazyloadthumb=\"1\"" : "onload=\"thumbImg(this)\"")."')",
                "odz_parseimg('\\1', '\\2', '\\3', ".intval($lazyload).", ".intval($pid).")"
            ) : ($allowbbcode ? array(
                (!defined('IN_MOBILE') ? "bbcodeurl('\\1', '<a href=\"{url}\" target=\"_blank\">{url}</a>')" : "bbcodeurl('\\1', '')"),
                (!defined('IN_MOBILE') ? "bbcodeurl('\\3', '<a href=\"{url}\" target=\"_blank\">{url}</a>')" : "bbcodeurl('\\3', '')"),
            ) : array("bbcodeurl('\\1', '{url}')", "bbcodeurl('\\3', '{url}')")), $message);
        }
    }

    for($i = 0; $i <= $_G['forum_discuzcode']['pcodecount']; $i++) {
        $message = str_replace("[\tDISCUZ_CODE_$i\t]", $_G['forum_discuzcode']['codehtml'][$i], $message);
    }

    unset($msglower);

    if($jammer) {
        $message = preg_replace("/\r\n|\n|\r/e", "jammer()", $message);
    }
    if($first) {
        if(helper_access::check_module('group')) {
            $message = preg_replace("/\[groupid=(\d+)\](.*)\[\/groupid\]/i", lang('forum/template', 'fromgroup').': <a href="forum.php?mod=forumdisplay&fid=\\1" target="_blank">\\2</a>', $message);
        } else {
            $message = preg_replace("/(\[groupid=\d+\].*\[\/groupid\])/i", '', $message);
        }

    }

    $message_nl2br =nl2br(str_replace(array("\t", '   ', '  '), array('&nbsp; &nbsp; &nbsp; &nbsp; ', '&nbsp; &nbsp;', '&nbsp;&nbsp;'), $message));
    $message_nl2br =preg_replace('/<\/blockquote><\/div>+(<br.*?>\s)+\s+(<br.*?>)/i', '</blockquote></div>', $message_nl2br); //过滤掉一个br标签
    $message_nl2br =preg_replace('/<\/i>+(<br.*?>\s)+\s+(<br.*?>)/i', '</i>', $message_nl2br); //过滤掉一个br标签
    /*$message_nl2br =preg_replace('/(<br.*?>\s)(\s.*)(<br.*?>)/i', '<br /><br />', $message_nl2br); //过滤掉一个br标签*/
    return $htmlon ? $message : $message_nl2br;
}


function odz_tpl_hide_reply_hidden() {
    global $_G;
    $html = '<div class="locked">';
    if($_G['uid']){
        $html .= $_G['username'];
    }else{
        $html .= odz_lang('guest');
    }

    $html .= ','.odz_lang('post_hide_reply_hidden');
    $html .= '</div>';
    return $html;
}

function odz_parsesmiles(&$message) {
	global $_G;
	static $enablesmiles;
	if($enablesmiles === null) {
		$enablesmiles = false;
		if(!empty($_G['cache']['smilies']) && is_array($_G['cache']['smilies'])) {
			foreach($_G['cache']['smilies']['replacearray'] AS $key => $smiley) {
                // minbbs 表情缩放到合适大小
                $attrs = '';
                if(0 === strpos($smiley, 'mocs_')) {
                    $attrs = ' style="height:24px;width:24px;"';
                }

				$_G['cache']['smilies']['replacearray'][$key] = '<img'.$attrs.' src="'.STATICURL.'image/smiley/'.$_G['cache']['smileytypes'][$_G['cache']['smilies']['typearray'][$key]]['directory'].'/'.$smiley.'" smilieid="'.$key.'" border="0" alt="" />';
			}
			$enablesmiles = true;
		}
	}
	$enablesmiles && $message = preg_replace($_G['cache']['smilies']['searcharray'], $_G['cache']['smilies']['replacearray'], $message, $_G['setting']['maxsmilies']);
	return $message;
}