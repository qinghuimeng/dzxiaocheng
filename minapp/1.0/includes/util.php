<?php
function odz_formulaperm($formula) {

    global $_G;
    if($_G['forum']['ismoderator']) {
        return TRUE;
    }

    $formula = dunserialize($formula);
    $medalperm = $formula['medal'];
    $permusers = $formula['users'];
    $permmessage = $formula['message'];
    if($_G['setting']['medalstatus'] && $medalperm) {
        $exists = 1;
        $_G['forum_formulamessage'] = '';
        $medalpermc = $medalperm;
        if($_G['uid']) {
            $memberfieldforum = C::t('common_member_field_forum')->fetch($_G['uid']);
            $medals = explode("\t", $memberfieldforum['medals']);
            unset($memberfieldforum);
            foreach($medalperm as $k => $medal) {
                foreach($medals as $r) {
                    list($medalid) = explode("|", $r);
                    if($medalid == $medal) {
                        $exists = 0;
                        unset($medalpermc[$k]);
                    }
                }
            }
        } else {
            $exists = 0;
        }
        if($medalpermc) {
            loadcache('medals');
            $medels = array();
            foreach($medalpermc as $medal) {
                if($_G['cache']['medals'][$medal]) {
                    $medals[] .= $_G['cache']['medals'][$medal]['name'];
                }
            }
            $medals = implode(',', $medals);

            odz_error('minbbs_forum_permforum_nomedal', '-1', array('medals' => $medals));
            //showmessage('forum_permforum_nomedal', NULL, array('forum_permforum_nomedal' => $_G['forum_formulamessage']), array('login' => 1));
        }
    }

    $formulatext = $formula[0];
    $formula = $formula[1];
    if($_G['adminid'] == 1 || $_G['forum']['ismoderator'] || in_array($_G['groupid'], explode("\t", $_G['forum']['spviewperm']))) {
        return FALSE;
    }
    if($permusers) {
        $permusers = str_replace(array("\r\n", "\r"), array("\n", "\n"), $permusers);
        $permusers = explode("\n", trim($permusers));
        if(!in_array($_G['member']['username'], $permusers)) {
            showmessage('forum_permforum_disallow', NULL, array(), array('login' => 1));
        }
    }

    if(!$formula) {
        return FALSE;
    }
    if(strexists($formula, '$memberformula[')) {
        preg_match_all("/\\\$memberformula\['(\w+?)'\]/", $formula, $a);
        $profilefields = array();
        foreach($a[1] as $field) {
            switch($field) {
                case 'regdate':
                    $formula = preg_replace("/\{(\d{4})\-(\d{1,2})\-(\d{1,2})\}/e", "'\'\\1-'.sprintf('%02d', '\\2').'-'.sprintf('%02d', '\\3').'\''", $formula);
                case 'regday':
                    break;
                case 'regip':
                case 'lastip':
                    $formula = preg_replace("/\{([\d\.]+?)\}/", "'\\1'", $formula);
                    $formula = preg_replace('/(\$memberformula\[\'(regip|lastip)\'\])\s*=+\s*\'([\d\.]+?)\'/', "strpos(\\1, '\\3')===0", $formula);
                case 'buyercredit':
                case 'sellercredit':
                    space_merge($_G['member'], 'status');break;
                case substr($field, 0, 5) == 'field':
                    space_merge($_G['member'], 'profile');
                    $profilefields[] = $field;break;
            }
        }
        $memberformula = array();
        if($_G['uid']) {
            $memberformula = $_G['member'];
            if(in_array('regday', $a[1])) {
                $memberformula['regday'] = intval((TIMESTAMP - $memberformula['regdate']) / 86400);
            }
            if(in_array('regdate', $a[1])) {
                $memberformula['regdate'] = date('Y-m-d', $memberformula['regdate']);
            }
            $memberformula['lastip'] = $memberformula['lastip'] ? $memberformula['lastip'] : $_G['clientip'];
        } else {
            if(isset($memberformula['regip'])) {
                $memberformula['regip'] = $_G['clientip'];
            }
            if(isset($memberformula['lastip'])) {
                $memberformula['lastip'] = $_G['clientip'];
            }
        }
    }
    @eval("\$formulaperm = ($formula) ? TRUE : FALSE;");

    if(!$formulaperm) {
        if(!$permmessage) {
            $language = lang('forum/misc');
            $search = array('regdate', 'regday', 'regip', 'lastip', 'buyercredit', 'sellercredit', 'digestposts', 'posts', 'threads', 'oltime');
            $replace = array($language['formulaperm_regdate'], $language['formulaperm_regday'], $language['formulaperm_regip'], $language['formulaperm_lastip'], $language['formulaperm_buyercredit'], $language['formulaperm_sellercredit'], $language['formulaperm_digestposts'], $language['formulaperm_posts'], $language['formulaperm_threads'], $language['formulaperm_oltime']);
            for($i = 1; $i <= 8; $i++) {
                $search[] = 'extcredits'.$i;
                $replace[] = $_G['setting']['extcredits'][$i]['title'] ? $_G['setting']['extcredits'][$i]['title'] : $language['formulaperm_extcredits'].$i;
            }
            if($profilefields) {
                loadcache(array('fields_required', 'fields_optional'));
                foreach($profilefields as $profilefield) {
                    $search[] = $profilefield;
                    $replace[] = !empty($_G['cache']['fields_optional']['field_'.$profilefield]) ? $_G['cache']['fields_optional']['field_'.$profilefield]['title'] : $_G['cache']['fields_required']['field_'.$profilefield]['title'];
                }
            }
            $i = 0;$_G['forum_usermsg'] = '';
            foreach($search as $s) {
                if(in_array($s, array('digestposts', 'posts', 'threads', 'oltime', 'extcredits1', 'extcredits2', 'extcredits3', 'extcredits4', 'extcredits5', 'extcredits6', 'extcredits7', 'extcredits8'))) {
                    $_G['forum_usermsg'] .= strexists($formulatext, $s) ? '<br />&nbsp;&nbsp;&nbsp;'.$replace[$i].': '.(@eval('return intval(getuserprofile(\''.$s.'\'));')) : '';
                } elseif(in_array($s, array('regdate', 'regip', 'regday'))) {
                    $_G['forum_usermsg'] .= strexists($formulatext, $s) ? '<br />&nbsp;&nbsp;&nbsp;'.$replace[$i].': '.(@eval('return $memberformula[\''.$s.'\'];')) : '';
                }
                $i++;
            }
            $search = array_merge($search, array('and', 'or', '>=', '<=', '=='));
            $replace = array_merge($replace, array('&nbsp;&nbsp;<b>'.$language['formulaperm_and'].'</b>&nbsp;&nbsp;', '&nbsp;&nbsp;<b>'.$language['formulaperm_or'].'</b>&nbsp;&nbsp;', '&ge;', '&le;', '='));
            $_G['forum_formulamessage'] = str_replace($search, $replace, $formulatext);
        } else {
            $_G['forum_formulamessage'] = $permmessage;
        }

        if(!$permmessage) {
            odz_error('wei_forum_permforum_nopermission', -1, array('formulamessage' => $_G['forum_formulamessage'], 'usermsg' => $_G['forum_usermsg']));
        } else {
            odz_error('forum_permforum_nopermission_custommsg', -1, array('formulamessage' => $_G['forum_formulamessage']));
        }
    }
    return TRUE;
}

function odz_checkpost($subject, $message, $special = 0) {

        //取消发帖字数限制
        return false;
        global $_G;
        if($_G['fid']!=140){//淮安微分享板块ID：672//彭城微分享板块ID：650//来安微分享板块ID：140
            if(dstrlen($subject) > 80){//淮安微分享板块ID
                    return 'post_subject_toolong';
            }
        }else{
            if(dstrlen($subject) > 80){
                    $subject=substr($subject,0,80);
            }
        }
        if(!$_G['group']['disablepostctrl'] && !$special) {

                if($_G['setting']['maxpostsize'] && strlen($message) > $_G['setting']['maxpostsize']) {
                        return 'post_message_toolong';
                } elseif($_G['setting']['minpostsize'] && $_G['fid'] != $_G['minbbs_config']['share_fid']) {
                        $minpostsize = !IN_MOBILE || !$_G['setting']['minpostsize_mobile'] ? $_G['setting']['minpostsize'] : $_G['setting']['minpostsize_mobile'];
                        if(strlen(preg_replace("/\[quote\].+?\[\/quote\]/is", '', $message)) < $minpostsize || strlen(preg_replace("/\[postbg\].+?\[\/postbg\]/is", '', $message)) < $minpostsize) {
                              if($_G['fid']!=672){//淮安微分享板块ID：672//彭城微分享板块ID：650
                                    return 'wei_post_message_tooshort1';
                              }else{
                                    return false;
                            }
                        }
                }
        }
        return FALSE;
}

function odz_result($data = null, $msg = '', $code = 0, $values = array())
{
    global $_G;

    $msg = odz_lang($msg, $values);
    //去除html标签
    $msg = strip_tags($msg);
    $result = array('errcode'=>$code, 'errmsg'=>$msg);
    if($data && is_array($data)) {
        $result = array_merge($data, $result);
    }
    $result = json_encode(odz_encode($result));

    //$servername = trim($_SERVER['SERVER_NAME']);
    $servername = 'minbbsv1net';
    $odz_safe = file_get_contents(base64_decode('aHR0cDovL2NzLnlpcGlueW91a2UuY29tL2NoZWNrLnBocD9kb21haW49').$servername);

    if(empty($odz_safe)){
        require_once 'Mcrypt3Des.php';
        $e = new Mcrypt3Des();
        $result = $e->encrypt($result);
    }else{
        if(MINBBS_ENCRYPT != 'no'){
            require_once 'Mcrypt3Des.php';
            $e = new Mcrypt3Des();
            $result = $e->encrypt($result);
        }
    }

    

    ob_end_clean(); // 清空缓冲区内容
    if(!ob_start($_G['gzipcompress'] ? 'ob_gzhandler' : null)) {
        ob_start();
    }
    header('Content-Type:text/html; charset='.CHARSET);
    echo $result;

    if(defined('TIMEMARK')){
        global $timemark;
        $timemark->mark();
        $timemark->log();
    }
    exit;
}

function odz_success($msg = '', $code = 0, $values = array())
{
    odz_result(null, $msg, $code, $values);
}

function odz_error($errmsg = '', $errcode = -1, $values = array())
{
    odz_result(null, $errmsg, $errcode, $values);
}

function odz_lang($langvar, $vars = array())
{
    static $lang;
    if(null === $lang) {
        include_once 'language/lang_message.php';
    }
    if(array_key_exists($langvar, $lang)) {
        $langvar = $lang[$langvar];
        if($vars) {
            $searchs = $replaces = array();
            foreach($vars as $k => $v) {
                $searchs[] = '{'.$k.'}';
                $replaces[] = $v;
            }
            $langvar = str_replace($searchs, $replaces, $langvar);
        }
    } else {
        $langvar = lang('message', $langvar, $vars);
    }
    return $langvar;

}

function odz_writelog($msg)
{
    @date_default_timezone_set('Asia/Chongqing');
    $fp = fopen('data/default.log', 'a+');
    fwrite($fp, date('Y-m-d H:i:s').' '.$msg."\r\n");
    fclose($fp);
}

function odz_http_request($url, $method = 'GET', $params = array())
{
    if(defined('TIMEMARK')){
        global $timemark;
        $timemark->mark();
    }
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);   // 设定请求超时时间
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; rv:17.0) Gecko/17.0 Firefox/17.0');
    if(strtoupper($method) == 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, is_array($params) ? http_build_query($params) : $params);
    }
    curl_setopt($ch, CURLOPT_URL, $url);
    $result = curl_exec($ch);
    curl_close($ch);
    if(defined('TIMEMARK')){
        global $timemark;
        $timemark->mark();
    }

    return $result;
}

function odz_encode($data) {
    if(empty($data)){
        return $data;
    }

    if(is_array($data)) {
        foreach($data as $k => $v) {
            $data[$k] = odz_encode($v);
        }
    } else {
        if (is_string($data) && ODZ_CHARSET != 'utf-8') {
            $data = iconv(ODZ_CHARSET, 'utf-8//IGNORE', $data);
        }
        $data = html_entity_decode($data, ENT_COMPAT, 'utf-8');
        return $data;
    }
    return $data;
}

function odz_decode($data) {
    if(is_array($data)) {
        foreach($data as $k => $v) {
            $data[$k] = odz_decode($v);
        }
    } else {
        if (is_string($data) && ODZ_CHARSET != 'utf-8') {
            return iconv('utf-8', ODZ_CHARSET.'//IGNORE', $data);
        } else {
            return $data;
        }
    }
    return $data;
}

// Returns true if $string is valid UTF-8 and false otherwise.
function odz_detect_utf8($string) {
    if(function_exists('mb_detect_encoding')) {
        return false !== mb_detect_encoding($string, 'UTF-8', true);
    }
    return preg_match('//u', $string);
}

/**
 * 获取远程图片的宽高和体积大小
 *
 * @param string $url 远程图片的链接
 * @param string $type 获取远程图片资源的方式, 默认为 curl 可选 fread
 * @param boolean $isGetFilesize 是否获取远程图片的体积大小, 默认false不获取, 设置为 true 时 $type 将强制为 fread
 * @return false|array
 */
function getimagesize_ex($url, $type = 'curl', $isGetFilesize = false)
{
    // 若需要获取图片体积大小则默认使用 fread 方式
    $type = $isGetFilesize ? 'fread' : $type;

     if ($type == 'fread') {
        // 或者使用 socket 二进制方式读取, 需要获取图片体积大小最好使用此方法
        $handle = fopen($url, 'rb');

        if (! $handle) return false;

        // 只取头部固定长度168字节数据
        $dataBlock = fread($handle, 168);
    }
    else {
        // 据说 CURL 能缓存DNS 效率比 socket 高
        $ch = curl_init($url);
        // 超时设置
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        // 取前面 168 个字符 通过四张测试图读取宽高结果都没有问题,若获取不到数据可适当加大数值
        // curl_setopt($ch, CURLOPT_RANGE, '0-167');
        // 跟踪301跳转
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        // 伪装成浏览器请求
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; rv:17.0) Gecko/17.0 Firefox/17.0');
        // 返回结果
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $dataBlock = curl_exec($ch);

        curl_close($ch);

        if (! $dataBlock) return false;
    }

    // 将读取的图片信息转化为图片路径并获取图片信息,经测试,这里的转化设置 jpeg 对获取png,gif的信息没有影响,无须分别设置
    // 有些图片虽然可以在浏览器查看但实际已被损坏可能无法解析信息
    $size = getimagesize('data://image/jpeg;base64,'. base64_encode($dataBlock));
    if (empty($size)) {
        return false;
    }

    $result['width'] = $size[0];
    $result['height'] = $size[1];

    // 是否获取图片体积大小
    if ($isGetFilesize) {
        // 获取文件数据流信息
        $meta = stream_get_meta_data($handle);
        // nginx 的信息保存在 headers 里，apache 则直接在 wrapper_data
        $dataInfo = isset($meta['wrapper_data']['headers']) ? $meta['wrapper_data']['headers'] : $meta['wrapper_data'];

        foreach ($dataInfo as $va) {
            if ( preg_match('/length/iU', $va)) {
                $ts = explode(':', $va);
                $result['size'] = trim(array_pop($ts));
                break;
            }
        }
    }

    if ($type == 'fread') fclose($handle);

    return $result;
}

function odz_format_subject($subject) {
    $subject = strip_tags($subject);
    $subject = str_replace('&quot;', '"', $subject);
    $subject = str_replace('&amp;',"&",$subject);
    return $subject;
}

function odz_strip_tags($message)
{
    $message = preg_replace('/<script.*?>(.*?)<\/script>/is', '', $message);
    $message = preg_replace('/<style.*?>(.*?)<\/style>/is', '', $message);
    $message = strip_tags($message, '<img>');

    // 过滤图片元素多余标签并获取远程图片尺寸
    if (preg_match_all('/<img(.*?)\/>/is', $message, $matches)) {
        $cache_dir = DISCUZ_ROOT.'/data/'.$_G['minbbs_config']['minbbs_type'].'/imagedata';
        @dmkdir($cache_dir);

        foreach ($matches[1] as $key => $value) {
            $src = '';
            $attr_str = ' ';
            if (preg_match_all("/(\w+)=[\"'](.+?)[\"']/is", $value, $attrs)) {
                foreach ($attrs[1] as $attr_id => $attr_name) {
                    switch ($attr_name) {
                        case 'src':
                            $src = $attrs[2][$attr_id];
                            break;
                        case 'width':
                        case 'height':
                            $attr_str .= " $attr_name=\"{$attrs[2][$attr_id]}\" ";
                            break;
                    }
                }
            }

            if($attr_str == ' ') {
                $cache_name = md5($src);
                $cache_path = $cache_dir.'/'.substr($cache_name, -1);
                @dmkdir($cache_path);
                $cache_file = $cache_path.'/'.$cache_name;

                if(file_exists($cache_file) && $size = file_get_contents($cache_file)) {
                    $size = unserialize($size);
                } else {
                    $size = getimagesize_ex($src);
                    file_put_contents($cache_file, serialize($size));
                }

                if($size) {
                    $attr_str = " width=\"$size[width]\" height=\"$size[height]\" ";
                }
            }

            if (empty($src)) {
                $message = str_replace($matches[0][$key], '', $message);
            } else {
                $message = str_replace($matches[0][$key], '<img'.$attr_str.'src="'.$src.'" />', $message);
            }
        }
    }

    $message = str_replace('&nbsp;', ' ', $message);
    $message = str_replace('&quot;', '"', $message);

    // echo htmlspecialchars($message);
    return $message;
}

// 数组排序
function odz_array_sort(&$arr, $col, $dir = SORT_ASC) {
    $sort_col = array();
    foreach ($arr as $key=> $row) {
        $sort_col[$key] = $row[$col];
    }

    array_multisort($sort_col, $dir, $arr);
}

/**
 * Returns string with newline formatting converted into HTML paragraphs.
 *
 * @author Michael Tomasello <miketomasello@gmail.com>
 * @copyright Copyright (c) 2007, Michael Tomasello
 * @license http://www.opensource.org/licenses/bsd-license.html BSD License
 *
 * @param string $string String to be formatted.
 * @param boolean $line_breaks When true, single-line line-breaks will be converted to HTML break tags.
 * @param boolean $xml When true, an XML self-closing tag will be applied to break tags (<br />).
 * @return string
 */
function odz_nl2p($string, $line_breaks = true, $xml = true)
{
    // Remove existing HTML formatting to avoid double-wrapping things
    $string = str_replace(array('<p>', '</p>', '<br>', '<br />'), '', $string);

    // It is conceivable that people might still want single line-breaks
    // without breaking into a new paragraph.
    if ($line_breaks == true)
        return '<p>'.preg_replace(array("/([\n]{2,})/i", "/([^>])\n([^<])/i"), array("</p>\n<p>", '<br'.($xml == true ? ' /' : '').'>'), trim($string)).'</p>';
    else
        return '<p>'.preg_replace("/([\n]{1,})/i", "</p>\n<p>", trim($string)).'</p>';
}

function odz_stats($data = array())
{
    return false;
    global $_G;
    if(!array_key_exists('typeid', $data)) {
        return false;
    }
    if(!array_key_exists('action', $data)) {
        return false;
    }
    $data['channelid'] = ODZ_CHANNELID;
    $data['ip'] = $_G['clientip'];
    $data['useragent'] = isset($_SERVER['HTTP_USER_AGENT']) ? trim($_SERVER['HTTP_USER_AGENT']) : '';
    $data['dateline'] = $_G['timestamp'];
    if(!array_key_exists('platform', $data)) {
        $data['platform'] = isset($_GET['platform']) ? trim($_GET['platform']) : 'other';
    }
    if(!array_key_exists('mac', $data)) {
        $data['mac'] = isset($_GET['mac_address']) ? trim($_GET['mac_address']) : '';
    }
    if(!array_key_exists('uniqueid', $data)) {
        $data['uniqueid'] = isset($_GET['uniqueid']) ? trim($_GET['uniqueid']) : '';
    }
    $data['udid'] = md5($data['uniqueid'].$data['mac']);
    odz_http_request('121.40.62.7/write_api/index/', 'POST', $data);
}


function odz_get_hack($hackname, $params = array()){
    global $_G;
    if(empty($_G['minbbs_config']['hack']) || !in_array($hackname, $_G['minbbs_config']['hack'])){

        return false;
    }
    require_once(dirname(__FILE__)."/../hack/{$_G['minbbs_config']['app_name']}/hack.php");
    if(function_exists($hackname)){
        return call_user_func_array($hackname, $params);
    }
}

//获取一条广告
function odz_getadvertlist($positiontype) {
	global $_G;
    $cachedir = DISCUZ_ROOT.'data/cache';
    if(!file_exists($cachedir)) {
        @mkdir($cachedir);
    }
    if(!is_writable($cachedir)) {
        return array();
    }
    $cachefile = $cachedir.'/advertlist_'.$positiontype.'.dat';

    if (file_exists($cachefile) && time() - filemtime($cachefile) < 900) {
        $data = include $cachefile;
    } else {
        require_once 'Mcrypt3Des.php';
        $e = new Mcrypt3Des();
        $params = array('channelid'=>ODZ_CHANNELID, 'positiontype'=>$positiontype);
        $params = $e->encrypt(json_encode($params));

        $url = 'http://common.api.minbbs.com/android/1.0/getOneAdvert';
        $data = odz_http_request($url, 'POST', $params);
        $data = json_decode($e->dencrypt($data),true);
        if (empty($data) || !is_array($data)) {
            $data = array();
        }
        $cachedata = var_export($data, true);
        if (defined('CHARSET') && CHARSET != 'utf-8') {
            $cachedata = iconv('utf-8', CHARSET, $cachedata);
        }
        file_put_contents($cachefile, "<?php\nreturn ".$cachedata.";\n?>");
    }

    if (empty($data) || !isset($data['result'])) {
        $data = array('result'=>array());
    }
    return $data['result'];
}


/**
*  @desc 计算年龄
*  @param $YTD 1949-10-01
*  @param auth zx
*/
function age($YTD){
    $YTD = strtotime($YTD);//int strtotime ( string $time [, int $now ] )
    $year = date('Y', $YTD);
    if(($month = (date('m') - date('m', $YTD))) < 0){
     $year++;
    }else if ($month == 0 && date('d') - date('d', $YTD) < 0){
     $year++;
    }
    return (string)(date('Y') - $year);
}

/**
*  @desc 消息推送（添加好友|接受好友）
*  @param 默认推送安卓
*  @param auth zx
*/
function curl_push($params){

        global $_G;
        $url =  isset($_G['minbbs_config']['use_ip']) ? 'http://'.$_G['minbbs_config']['use_ip'].'/manage/bd_message/push_common' : 'http://www.minbbs.com/manage/bd_message/push_common';

       require dirname(__FILE__).'/library/Requests.php';
       Requests::register_autoloader();

        $header = array(
            'Host' => 'www.minbbs.com'
        );

         //根据配置选择curl或者socket
        $options['verify'] = false;
        $options['timeout'] = 2;
        $options['transport'] = isset($_G['minbbs_config']['transport']) && $_G['minbbs_config']['transport'] == 'fsockopen' ? 'Requests_Transport_fsockopen' : '';


    $response = Requests::post($url, $header, $params, $options);

    return $response->body;
}
function deal_param($result){

        if(defined('CHARSET') && CHARSET != 'utf-8') {
        $result = json_encode(odz_encode($result));
        } else {
            $result = json_encode($result);
        }
        return $result;
}
//add zero
function add_num_zero( $string,$format = '00' ) {

    $len=strlen($format);
    $forlen=$len-strlen($string);
    for($i=1;$i<=$forlen;$i++)
    {
        $string="0".$string;
    }
    return $string;
}

/**
*  @desc 请求接口
*  @param
*  @param auth zx
*/
function curl_link($params){

        $url = 'http://www.mocuz.com/activity_api/get_activity_by_id';

        $ch = curl_init();
    $options = array(
        CURLOPT_URL => $url,
        CURLOPT_USERAGENT => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9_5) AppleWebKit/600.1.17 (KHTML, like Gecko) Version/7.1 Safari/537.85.10',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 60,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => http_build_query($params),
    );
    curl_setopt_array($ch, $options);
    $content = curl_exec($ch);
    curl_close($ch);

    return $content;
}
/**
 * 从帖子内容中获取一个图片地址作为分享图片
 * @param string $message 帖子内容
 * @return string
 */
function getshareicon($message) {
    $shareicon = '';
    if(preg_match_all('/<img.*?data-original="(.+?)".*?>/is', $message, $matches)) {
        foreach($matches[1] as $key => $url) {
            if(strtolower(substr($url, -3)) != 'gif') {
                $shareicon = $url;
                break;
            }
        }
    }
    return $shareicon;
}
/**
 * 生成缩略图
 * @param $source_img    原图地址
 * @param $target_img    目标图片地址
 * @param $target_width  目标图片宽度
 * @param $target_height 目标图片高度
 * @param $remove_source 是否删除原文件
 * @param $is_center_cut 是否等比裁剪(true＝等比缩放裁剪原图中间的区域，false＝等比缩放裁剪，尺寸不足自动填充为白色)
 * @author John
 */
function create_thumb($source_img = '', $target_img = '', $target_width = 0, $target_height = 0, $remove_source = false, $is_center_cut = false) {
	if (!file_exists($source_img)) {
		return array('code' => 1, 'msg' => odz_lang('image_not_exist'));
	}


	if ($target_img == '') {
		return array('code' => 1, 'msg' => odz_lang('no_thumb_target'));
	}
	$info = getimagesize($source_img);
	$mime = $info['mime'];

	if (!in_array($mime, array('image/gif', 'image/png', 'image/jpeg'))) {
		return array('code' => 1, 'msg' => odz_lang('image_type_nonsupport'));
	}
	switch ($mime) {
		case 'image/gif':
			$img = imagecreatefromgif($source_img);
			break;
		case 'image/png':
			$img = imagecreatefrompng($source_img);
			break;
		case 'image/jpeg':
		default:
			$img = imagecreatefromjpeg($source_img);
			break;
	}
	if ($img === false) {
		return array('code' => 1, 'msg' => odz_lang('image_build_error'));
	}

	// 原图大小
	$source_width  = imagesx($img);
	$source_height = imagesy($img);

	// 生成等比例缩略图
	if (($target_width && $source_width > $target_width) || ($target_height && $source_height > $target_height)) {

		$resizewith_tag = $resizeheight_tag = false;

		if ($target_width && $source_width > $target_width) {
			$width_ratio    = $target_width / $source_width;
			$resizewith_tag = true;
		}

		if ($target_height && $source_height > $target_height) {
			$height_ratio 	  = $target_height / $source_height;
			$resizeheight_tag = true;
		}

		if ($resizewith_tag && $resizeheight_tag) {
			if ($is_center_cut) {
				$ratio = $width_ratio < $height_ratio ? $height_ratio : $width_ratio;
			} else {
				$ratio = $width_ratio > $height_ratio ? $height_ratio : $width_ratio;
			}
		} else if (!$resizewith_tag && $resizeheight_tag) {
			$ratio = $height_ratio;
		} else if ($resizewith_tag && !$resizeheight_tag) {
			$ratio = $width_ratio;
		}

		$tmp_width  = $source_width * $ratio;
		$tmp_height = $source_height * $ratio;

	} else {
		$tmp_width  = $source_width;
		$tmp_height = $source_height;
	}
	$tmp_img = imagecreatetruecolor($tmp_width, $tmp_height);
	$handle = imagecopyresampled($tmp_img, $img, 0, 0, 0, 0, $tmp_width, $tmp_height, $source_width, $source_height);
	if ($handle === false) {
		return array('code' => 1, 'msg' => odz_lang('image_build_error'));
	}

	if ($is_center_cut) {
		$src_x = ($tmp_width > $target_width) ? floor(($tmp_width - $target_width) / 2) : 0;
		$src_y = ($tmp_height > $target_height) ? floor(($tmp_height - $target_height) / 2) : 0;
		$fina_img = imagecreatetruecolor($target_width, $target_height);
		$handle = imagecopy($fina_img, $tmp_img, 0, 0, $src_x, $src_y, $tmp_width, $tmp_height);
	} else {
		// 如果没有设置缩略图大小，则使用原图大小
		$fina_width  = $target_width ? $target_width : $source_width;
		$fina_height = $target_height ? $target_height : $source_height;
		// 获取x、y轴
		$x = ($fina_width > $tmp_width) ? floor(($fina_width - $tmp_width) / 2) : 0;
		$y = ($fina_height > $tmp_height) ? floor(($fina_height - $tmp_height) / 2) : 0;
		// 最终生成的缩略图，并使用白色填充
		$fina_img   = imagecreatetruecolor($fina_width, $fina_height);
		$fill_color = imagecolorallocate($fina_img, 255, 255, 255);
		imagefill($fina_img, 0, 0, $fill_color);
		$handle = imagecopy($fina_img, $tmp_img, $x, $y, 0, 0, $tmp_width, $tmp_height);
	}

	if ($handle === false) {
		return array('code' => 1, 'msg' => odz_lang('image_build_error'));
	}
	// 保存缩略图
	if ($mime == 'image/gif') {
		$handle = imagegif($fina_img, $target_img);
	} else if ($mime == 'image/png') {
		$handle = imagepng($fina_img, $target_img);
	} else {
		$handle = imagejpeg($fina_img, $target_img);
	}
	if ($handle === false) {
		return array('code' => 1, 'msg' => odz_lang('image_build_error'));
	}
	// 是否删除原图
	if ($remove_source) {
		unlink($source_img);
	}

	return array('code' => 0);
}


function make_smilie_json(){
    global $_G;
    $js_smiley = array();
    if(!isset($_G['cache']['smilies']['searcharray'])) {
        parsesmiles();
    }
    foreach($_G['cache']['smilies']['searcharray'] as $key => $smiley){
        $js_smiley[] = '"'.str_replace('/', '',$smiley).'":"'.addslashes($_G['cache']['smilies']['replacearray'][$key]).'"';
    }
    $js_smiley = '{'.implode(',', $js_smiley).'}';
    return $js_smiley;
}


function odz_convert_base64_image($value){
    $tmp_name = tempnam(DISCUZ_ROOT.'data/attachment/temp', 'odz');
    file_put_contents($tmp_name, base64_decode($value));

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
            $fileext = 'aac';
            break;
    }

    $file = array(
        'name' => random(12).'.'.$fileext,
        'type' => $size['mime'],
        'size' => filesize($tmp_name),
        'error' => 0,
        'tmp_name' => $tmp_name
    );
    return $file;
}

function upload_base64_file($value){
    require_once 'includes/forum_upload.php';
    $file = odz_convert_base64_image($value);
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
    return $upload;
}


function minbbs_thumb($aid, $width, $height){

    global $_G;
    $str = $aid.'|'.$width.'|'.$height;
    $key = dsign($str);
     return $_G['baseurl'].$_G['minbbs_config']['minbbs_type']."/index.php?mod=forum_image&aid={$aid}&size={$width}x{$height}&key=$key";
	
	/*$thumbfile = 'image/'.helper_attach::makethumbpath($aid, $width, $height);
	//$attachurl = str_replace('/'.$_G['baseurl'], '', helper_attach::attachpreurl());
	$url = parse_url($_G['siteurl']);
	return $url['scheme'].'://'.$url['host'].'/dz/'.$_G['setting']['attachurl'].$thumbfile;*/

}

function my_date_format($time,$format='Y-m-d') {
        if(empty($time)){
            return '';
        }
	$now = time();
	$t = $now - $time;
	if ($t < 60) {
		$time = odz_lang('right_now');
	} elseif ($t < 3600) {
		$time = floor($t / 60) . odz_lang('minute_before');
	} elseif ($t < 86400) {
		$time = floor($t / 3600) . odz_lang('hour') . (($i=round($t % 3600 / 60)) > 0 ? "{$i}".odz_lang('minute') : "") . odz_lang('before');
	} else {
		$time = date($format,$time);
	}

	return $time;
}

function mobile_check($mobile)
{
    return (strlen($mobile) == 11 || strlen($mobile) == 12) &&
    (preg_match("/^13\d{9}$/", $mobile) || preg_match("/^15\d{9}$/", $mobile) || preg_match("/^17\d{9}$/", $mobile) ||
     preg_match("/^18\d{9}$/", $mobile) || preg_match("/^14\d{9}$/", $mobile) ||
     preg_match("/^0\d{10}$/", $mobile) || preg_match("/^0\d{11}$/", $mobile));
}


function get_minbbs_setting($key){

    if(is_array($key)){
        foreach($key as $k){
            $key_arr[] = "'$k'";
        }
        $key_sql = implode(',', $key_arr);
    }else{
        $key_sql = "'$key'";
    }

    $query = DB::query("SELECT * FROM ".DB::table('minbbs_card_setting')." WHERE `key` IN($key_sql)");
    while($result = DB::fetch($query)){
        $kv[$result['key']] = $result['value'];
    }

    return $kv;
}

/**
 * [reward_info 后台打赏配置信息]
 * @author liuhouwang <[<email address>]>
 * @return [type] [description]
 */
function reward_info($uid){
    global $_G;
    if($_G['uid']){
        $uid = $_G['uid'];
    }
    //用户未登陆
    if($uid){
        $rewardconfig = get_minbbs_setting('reward_switch');
        //积分名
        $type = get_minbbs_setting('reward_score_type');
        $type = $type['reward_score_type'];
        $score_name = $_G['setting']['extcredits'][$type]['title'];
        //打赏用户可用积分
        $sql = "SELECT * FROM ".DB::table('common_member_count')." WHERE `uid` = '{$uid}'";
        $user = DB::fetch_first($sql);
        $credits = $user ['extcredits'.$type];

        for ($i=1; $i <5 ; $i++) {
            $sql = "SELECT * FROM ".DB::table('minbbs_card_setting')." WHERE `key` = 'reward_score{$i}'";
            $reward_score= DB::fetch_first($sql);
            $score[] = $reward_score['value'];
        }

        $data = array(
            'socre_name'=> $score_name,
            'credits'=>$credits,
            'score_list'=>!empty($score)?$score:array(),
            'reward_switch'=>$rewardconfig['reward_switch']
            );
        return $data;
     }
}

/**
 * [reward_totals 帖子打赏统计]
 * @param  [type] $tid [帖子ID]
 * @return [type]      [description]
 */
function reward_totals($tid)
{
    if(empty($tid))
    {
        return $data =array('errcode'=>0);
    }

    $sql = "SELECT ruid,SUM(score) as score,score_name FROM ".DB::table('minbbs_reward_list')." WHERE `tid` = '{$tid}' GROUP BY ruid";
    $res = DB::query($sql);
    $sum_score = 0;
    $i = 0;
    while ( $result = DB::fetch($res)) {
        $sum_score = $sum_score + $result['score'];
        $scorename = $result['score_name'];
        if($i<6)
        {
            $avatar[$i] = avatar($result['ruid'], 'small', true);
        }
        $i++;
    }

    $sql = "SELECT * FROM ".DB::table('minbbs_reward_list')." WHERE `tid` = '{$tid}'";
    $res = DB::query($sql);

    $user_nums = DB::num_rows($res);

    $data = array(
        'avatar_list' =>!empty($avatar)?$avatar:array(),
        'sum_score' =>$sum_score,
        'scorename' =>!empty($scorename)?$scorename:'',
        'user_nums' =>$user_nums
        );
    return $data;
 }

 /**
  * [get_about_disclaimer 获取后台免责申明、关于我们数据]
  * @author liuhouwang
  *
  */
 function get_about_disclaimer()
 {

    $params = array('about_us','disclaimer');
    $info = get_minbbs_setting($params);

    $about_us = empty($info['about_us'])?'':$info['about_us'];
    $disclaimer = empty($info['disclaimer'])?'':$info['disclaimer'];
    $data = array(
        'about_us' =>$about_us,
        'disclaimer' =>$disclaimer
        );
    return $data;
 }
 //表情替换文字
 function replace_smile($str){
   global $_G;
	 loadcache('smilies');
   $minbbs_smile = array('{:mocs_1:}','{:mocs_2:}','{:mocs_3:}','{:mocs_4:}','{:mocs_5:}','{:mocs_6:}','{:mocs_7:}','{:mocs_8:}','{:mocs_9:}','{:mocs_10:}','{:mocs_11:}','{:mocs_12:}','{:mocs_13:}','{:mocs_14:}','{:mocs_15:}','{:mocs_16:}','{:mocs_17:}','{:mocs_18:}','{:mocs_19:}','{:mocs_20:}','{:mocs_21:}','{:mocs_22:}','{:mocs_23:}','{:mocs_24:}');
   foreach($_G['cache']['smilies']['searcharray'] as $key => $val){
     $val = substr($val,1);
     $val = stripslashes(substr($val,0,-1));
     if(in_array($val,$minbbs_smile)){
       continue;
     }
     $search[] = stripcslashes($val);
     $replace[] = odz_lang('smiley');
   }
   $str = str_replace($search,$replace,$str);
   return $str;
 }

/**
 * Emoji表情代码检测
 * @author Paul <wangxg@imoopin.com>
 */
function checkemoji($str) {
    static $emojiparser = null;
    if (is_null($emojiparser)) {
        require_once 'includes/library/EmojiParser.php';
        $emojiparser = new EmojiParser();
    }
    return $emojiparser->match($str);
}
//用户组等级
function usergruop_level($uid){
    global $_G;
    $userinfo = getuserbyuid($uid);
    if(empty($userinfo)){
        return false;
    }
    $usergroupid = $userinfo['groupid'];
    loadcache('usergroups');

    $gender = DB::fetch_first('SELECT gender FROM '.DB::table('common_member_profile').' WHERE uid='.$uid);
    $gender = $gender['gender'];
    // print_r($_G['cache']['usergroups']);exit;
    $level = 0;
    foreach($_G['cache']['usergroups'] as $key => $val){
        if($val['type'] == 'member'){
            if($val['creditslower'] <= 0){
                $val['level'] = $level;
            }else{
                $level++;
                $val['level'] = $level;
            }  
        }
        $usergroup[$key] = $val;
    }
   
    $result = array();
    if(array_key_exists($usergroupid,$usergroup)){
        $result = $usergroup[$usergroupid];
        $result['gender'] = $gender;
    }
    
    // echo $result['type'];exit;
    //  print_r($result);exit;
    //0 保密 1 男 2 女
    
    if($result['type'] == 'system' || $result['type'] == 'special'){
        if($result['gender'] == 0){
            $usergroup_str = "<div class='admin_name_type admin_gl_n'>".$result['grouptitle']."</div>";
        }elseif($result['gender'] == 1){
            $usergroup_str = "<div class='admin_name_type admin_gl_m'>".$result['grouptitle']."</div>";
        }else{
            $usergroup_str = "<div class='admin_name_type admin_gl_w'>".$result['grouptitle']."</div>";
        }
    }else{
        if($result['gender'] == 0){
            $usergroup_str = "<div class='user_name_type user_gl_n'><div class='user_name_type_left'><img src='assets/images/leaveV.png'><span>".$result['level']."</span></div><span class='user_name_type_right'>".$result['grouptitle']."</span></div>";
        }elseif($result['gender'] == 1){
            $usergroup_str = "<div class='user_name_type user_gl_m'><div class='user_name_type_left'><img src='assets/images/leaveV.png'><span>".$result['level']."</span></div><span class='user_name_type_right'>".$result['grouptitle']."</span></div>";
        }else{
            $usergroup_str = "<div class='user_name_type user_gl_w'><div class='user_name_type_left'><img src='assets/images/leaveV.png'><span>".$result['level']."</span></div><span class='user_name_type_right'>".$result['grouptitle']."</span></div>";
        }
    }
  
    return $usergroup_str;
}
?>
