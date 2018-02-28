<?php

if(!defined('IN_DISCUZ')) {

	exit('Access Denied');

}



class logging_ctl {



	function logging_ctl() {

		require_once libfile('function/misc');

		loaducenter();

	}



	function logging_more($questionexist) {

		global $_G;

		odz_error('login_question_empty', -499);

	}



	function on_login() {

        global $_G;
        if($_G['uid']) {
            // TODO 当需要限制重复登录时启用以下代码
            // odz_result(array('uid'=>$_G['uid']), 'duplicate_login', -31);
        }



		$from_connect = $this->setting['connect']['allow'] && !empty($_GET['from']) ? 1 : 0;

		$seccodecheck = $from_connect ? false : $this->setting['seccodestatus'] & 2;

		$seccodestatus = !empty($_GET['lssubmit']) ? false : $seccodecheck;

		$invite = getinvite();



                // 执行会员登录流程

		$username = trim($_GET['username']);

		if(!($_G['member_loginperm'] = logincheck($username))) {

			showmessage('login_strike');

		}



		if($_GET['fastloginfield']) {

			$_GET['loginfield'] = $_GET['fastloginfield'];

		}

                $_G['uid'] = $_G['member']['uid'] = 0;

                $_G['username'] = $_G['member']['username'] = $_G['member']['password'] = '';

                if(!$_GET['password'] || $_GET['password'] != addslashes($_GET['password'])) {

                        showmessage('profile_passwd_illegal');

                }

                $result = userlogin($username, $_GET['password'], $_GET['questionid'], $_GET['answer'],  'auto', $_G['clientip']);

                $uid = $result['ucresult']['uid'];



                // 手机号码登录

                if($result['ucresult']['uid'] <= 0 && preg_match('/^(\+)?(86)?0?1\d{10}$/', trim($username))) {



                        $result = userlogin($username, $_GET['password'], $_GET['questionid'], $_GET['answer'], 'mobile', $_G['clientip']);





                }



                if(!empty($_GET['lssubmit']) && ($result['ucresult']['uid'] == -3 || $seccodecheck && $result['status'] > 0) && $result['ucresult']['uid'] < 1) {

                        $_GET['username'] = $result['ucresult']['username'];

                        $this->logging_more($result['ucresult']['uid'] == -3);

                }



                if($result['status'] == -1) {

                        if(!$this->setting['fastactivation']) {

                                $auth = authcode($result['ucresult']['username']."\t".FORMHASH, 'ENCODE');

                                showmessage('location_activation', 'member.php?mod='.$this->setting['regname'].'&action=activation&auth='.rawurlencode($auth).'&referer='.rawurlencode(dreferer()), array(), array('location' => true));

                        } else {

                                $init_arr = explode(',', $this->setting['initcredits']);

                                $groupid = $this->setting['regverify'] ? 8 : $this->setting['newusergroupid'];



                                C::t('common_member')->insert($uid, $result['ucresult']['username'], md5(random(10)), $result['ucresult']['email'], $_G['clientip'], $groupid, $init_arr);

                                $result['member'] = getuserbyuid($uid);

                                $result['status'] = 1;

                        }

                }



		if($result['status'] > 0) {

			if($this->extrafile && file_exists($this->extrafile)) {

				require_once $this->extrafile;

			}

			setloginstatus($result['member'], $_GET['cookietime'] ? 2592000 : 0);

			checkfollowfeed();

			C::t('common_member_status')->update($_G['uid'], array('lastip' => $_G['clientip'], 'lastvisit' =>TIMESTAMP, 'lastactivity' => TIMESTAMP));

//			$ucsynlogin = $this->setting['allowsynlogin'] ? uc_user_synlogin($_G['uid']) : '';

			if($invite['id']) {

				$result = C::t('common_invite')->count_by_uid_fuid($invite['uid'], $uid);

				if(!$result) {

					C::t('common_invite')->update($invite['id'], array('fuid'=>$uid, 'fusername'=>$_G['username']));

					updatestat('invite');

				} else {

					$invite = array();

				}

			}



			if($invite['uid']) {

				require_once libfile('function/friend');

				friend_make($invite['uid'], $invite['username'], false);

				dsetcookie('invite_auth', '');

				if($invite['appid']) {

					updatestat('appinvite');

				}

			}



			// 登录团购系统
			//团购接口定义
			$setting = get_minbbs_setting(array('groupbuy_switch'));
			if($setting['groupbuy_switch']){
			    define('ODZ_TUANGOU_API',$_G['baseurl'].'tuan/');
			}
			if(defined('ODZ_TUANGOU_API')) {

				// 加密登录参数

				require_once 'includes/Mcrypt3Des.php';

				$e = new Mcrypt3Des();



				$ttresult = odz_http_request(ODZ_TUANGOU_API.'minbbs_api.php?mod=index&code=Login_done', 'POST', $e->encrypt(json_encode(array('username'=>odz_encode($_GET['username']), 'password'=>$_GET['password']))));

				is_string($ttresult) && $ttresult = json_decode($e->dencrypt($ttresult), true);

				if(!isset($ttresult['code']) || $ttresult['code'] != '0') {

					odz_error('teambuy_login_failed');

				}

			}

			// 登录成功

			$result_data = array(

				'uid' => (string)$_G['uid'],

				'username' => $result['member']['username'],

				'avatar' => avatar($result['member']['uid'], 'middle', true),

                'auth' => authcode("{$result[member][password]}\t{$result[member][uid]}", 'ENCODE', '', 31536000), // 登录令牌有效期(31536000=1年)

				'credits'=>$_G['member']['credits'],//积分

				'grouptitle'=>strip_tags($_G['group']['grouptitle']),//头衔

			);



			//资料是否完善



			if($result['member']['username'] != ''){

				$uid = $result['member']['uid'];

				$user_info = DB::fetch_first('SELECT gender,mobile,affectivestatus,birthyear,birthmonth,birthday FROM '.DB::table('common_member_profile').' WHERE uid='.$uid);



				$sightml= DB::fetch_first('SELECT sightml FROM '.DB::table('common_member_field_forum').' WHERE uid = '.$uid);

				$regdate = DB::fetch_first('SELECT regdate FROM '.DB::table('common_member').' WHERE uid = '.$uid);

				//$mobiles	= DB::fetch_first('SELECT mobile FROM '.DB::table('minbbs_mobile').' WHERE uid = '.$uid);

				$result_data['regdate'] = date('Y-m-d',$regdate['regdate']);

				$result_data['sightml'] = $sightml['sightml']?$sightml['sightml']:odz_lang('no_message_left');

				$result_data['mobile']	= !empty($user_info['mobile']) ? $user_info['mobile'] : odz_lang('no_binding');

				$result_data['gender'] = (string)$user_info['gender'];

				$result_data['affectivestatus'] = $user_info['affectivestatus']?$user_info['affectivestatus']:odz_lang('secrect');

				$result_data['birth'] = $user_info['birthyear'].'-'.add_num_zero($user_info['birthmonth']).'-'.add_num_zero($user_info['birthday']);

				$result_data['sightml2'] = '222';



				//if($user_info['birthyear'] != 0){

					$result_data['isperfect'] = "1";

				//}else{

					//$result_data['isperfect'] = "0";

				//}

			}





                        //QQ第三方登录

            if($_GET['qq_openid']){

		    	if(in_array('qqconnect', $_G['setting']['plugins']['available'])) {

                    $current_connect_member = C::t('#qqconnect#common_member_connect')->fetch($uid);

                    $conispublishfeed = 0;

                    $conispublisht = 0;

                    if(empty($current_connect_member)) {

                        C::t('#qqconnect#common_member_connect')->insert(

                               array(

                                        'uid' => $uid,

                                        'conuin' => $_GET['con_request_conuin'],

                                        'conuinsecret' => $_GET['con_request_conuin_secret'],

                                        'conopenid' => $_GET['qq_openid'],

                                        'conispublishfeed' => $conispublishfeed,

                                        'conispublisht' => $conispublisht,

                                        'conisregister' => 0,

                                        'conisfeed' => 1,

                                        'conisqqshow' => $_GET['con_request_isqqshow'],

                                )

                        );

                    } else{
						//
            //             C::t('#qqconnect#common_member_connect')->update($uid,
						//
						// 	 array(
						//
						// 		'conuin' => $_GET['con_request_conuin'],
						//
						// 		'conuinsecret' => $_GET['con_request_conuin_secret'],
						//
						// 		'conopenid' => $_GET['qq_openid'],
						//
						// 		'conispublishfeed' => $conispublishfeed,
						//
						// 		'conispublisht' => $conispublisht,
						//
						// 		'conisregister' => 0,
						//
						// 		'conisfeed' => 1,
						//
						// 		'conisqqshow' => $_GET['con_request_isqqshow'],
						//
						// 	)
						//
						// );

						showmessage('qq_isbind');

                    }

					// $qq_relation='qq_ok';
					//
          //           C::t('common_member')->update($uid, array('conisbind' => '1'));
					//
          //           C::t('#qqconnect#common_connect_guest')->delete($_GET['con_request_conopenid']);

		    	} else {

		    		$member_uid = DB::result_first('SELECT uid  FROM '.DB::table('minbbs_connect')." WHERE openid = '$_GET[qq_openid]'");

		            $data=array('uid' => $uid, 'openid' => $_GET['qq_openid'],'type'=>'qq');

		            if($member_uid){

		                showmessage('member_qq_relation');

		            }else{

		                if(DB::insert('minbbs_connect',$data)){

		//                                    if($_GET['latitude']&&$_GET['longitude']){

		//                                        $data1=array('uid' => $uid, 'latitude' => $_GET['latitude'],'longitude' => $_GET['longitude']);

		//                                        DB::insert('minbbs_connect',$data1);

		//                                    }

		                    $qq_relation='qq_ok';

		                }else{

		                    $qq_relation='qq_erorr';

		                }

		            }

		    	}

            }

			// 团购系统登录令牌

			$ttauth = '';

			if(isset($ttresult) && isset($ttresult['res'])) {

				$ttauth = $ttresult['res']['ttauth'];

			}

			$result_data['ttauth'] = $ttauth;

			odz_stats(array('typeid'=>3, 'action'=>'login'));

			odz_result($result_data);



		} else {

			$password = preg_replace("/^(.{".round(strlen($_GET['password']) / 4)."})(.+?)(.{".round(strlen($_GET['password']) / 6)."})$/s", "\\1***\\3", $_GET['password']);

			$errorlog = dhtmlspecialchars(

				TIMESTAMP."\t".

				($result['ucresult']['username'] ? $result['ucresult']['username'] : $_GET['username'])."\t".

				$password."\t".

				"Ques #".intval($_GET['questionid'])."\t".

				$_G['clientip']);

			writelog('illegallog', $errorlog);

			loginfailed($_GET['username']);

			// 返回登录失败的具体原因

			switch ($result['ucresult']['uid']) {

				case -1:

					$fmsg = 'username_nonexistence';

					break;

				case -2:

					$fmsg = 'password_error';

					break;

				case -3:

				default:

					$fmsg = $result['ucresult']['uid'] == '-3' ? (empty($_GET['questionid']) || $answer == '' ? 'login_question_empty' : 'login_question_invalid') : 'login_invalid';

			}

			if($_G['member_loginperm'] > 1) {

				showmessage($fmsg, '', array('loginperm' => $_G['member_loginperm'] - 1));

			} elseif($_G['member_loginperm'] == -1) {

				showmessage('login_password_invalid');

			} else {

				showmessage('login_strike');

			}

		}



	}



	function on_logout() {

		global $_G;



		$ucsynlogout = $this->setting['allowsynlogin'] ? uc_user_synlogout() : '';



		clearcookies();

		$_G['groupid'] = $_G['member']['groupid'] = 7;

		$_G['uid'] = $_G['member']['uid'] = 0;

		$_G['username'] = $_G['member']['username'] = $_G['member']['password'] = '';

		$_G['setting']['styleid'] = $this->setting['styleid'];



		odz_success();

	}



}



class register_ctl {



	var $showregisterform = 1;



	function register_ctl() {

		global $_G;

		if($_G['setting']['bbclosed']) {

			if(($_GET['action'] != 'activation' && !$_GET['activationauth']) || !$_G['setting']['closedallowactivation'] ) {

				showmessage('register_disable', NULL, array(), array('login' => 1));

			}

		}



		loadcache(array('modreasons', 'stamptypeid', 'fields_required', 'fields_optional', 'fields_register', 'ipctrl'));

		require_once libfile('function/misc');

		require_once libfile('function/profile');

		if(!function_exists('sendmail')) {

			include libfile('function/mail');

		}

		loaducenter();

	}



	function on_register(){

		global $_G;



		// if(!$this->setting['regclosed'] && (!$this->setting['regstatus'] || !$this->setting['ucactivation'])) {

		// 	if($_GET['action'] == 'activation' || $_GET['activationauth']) {

		// 		if(!$this->setting['ucactivation'] && !$this->setting['closedallowactivation']) {

		// 			showmessage('register_disable_activation');

		// 		}

		// 	} elseif(!$this->setting['regstatus']) {

		// 		showmessage(!$this->setting['regclosemessage'] ? 'register_disable' : str_replace(array("\r", "\n"), '', $this->setting['regclosemessage']));

		// 	}

		// }



		// 注册方式

		$_GET['regtype'] = trim($_GET['regtype']);



		$bbrules = & $this->setting['bbrules'];

		$bbrulesforce = & $this->setting['bbrulesforce'];

		$bbrulestxt = & $this->setting['bbrulestxt'];

		$welcomemsg = & $this->setting['welcomemsg'];

		$welcomemsgtitle = & $this->setting['welcomemsgtitle'];

		$welcomemsgtxt = & $this->setting['welcomemsgtxt'];

		$regname = $this->setting['regname'];



		if($this->setting['regverify']) {

			if($this->setting['areaverifywhite']) {

				$location = $whitearea = '';

				$location = trim(convertip($_G['clientip'], "./"));

				if($location) {

					$whitearea = preg_quote(trim($this->setting['areaverifywhite']), '/');

					$whitearea = str_replace(array("\\*"), array('.*'), $whitearea);

					$whitearea = '.*'.$whitearea.'.*';

					$whitearea = '/^('.str_replace(array("\r\n", ' '), array('.*|.*', ''), $whitearea).')$/i';

					if(@preg_match($whitearea, $location)) {

						$this->setting['regverify'] = 0;

					}

				}

			}



			if($_G['cache']['ipctrl']['ipverifywhite']) {

				foreach(explode("\n", $_G['cache']['ipctrl']['ipverifywhite']) as $ctrlip) {

					if(preg_match("/^(".preg_quote(($ctrlip = trim($ctrlip)), '/').")/", $_G['clientip'])) {

						$this->setting['regverify'] = 0;

						break;

					}

				}

			}

		}



		$invitestatus = false;

		if($this->setting['regstatus'] == 2) {

			if($this->setting['inviteconfig']['inviteareawhite']) {

				$location = $whitearea = '';

				$location = trim(convertip($_G['clientip'], "./"));

				if($location) {

					$whitearea = preg_quote(trim($this->setting['inviteconfig']['inviteareawhite']), '/');

					$whitearea = str_replace(array("\\*"), array('.*'), $whitearea);

					$whitearea = '.*'.$whitearea.'.*';

					$whitearea = '/^('.str_replace(array("\r\n", ' '), array('.*|.*', ''), $whitearea).')$/i';

					if(@preg_match($whitearea, $location)) {

						$invitestatus = true;

					}

				}

			}



			if($this->setting['inviteconfig']['inviteipwhite']) {

				foreach(explode("\n", $this->setting['inviteconfig']['inviteipwhite']) as $ctrlip) {

					if(preg_match("/^(".preg_quote(($ctrlip = trim($ctrlip)), '/').")/", $_G['clientip'])) {

						$invitestatus = true;

						break;

					}

				}

			}

		}



		$groupinfo = array();

		if($this->setting['regverify']) {

			$groupinfo['groupid'] = 8;

		} else {

			$groupinfo['groupid'] = $this->setting['newusergroupid'];

		}



		$seccodecheck = $this->setting['seccodestatus'] & 1;

		$secqaacheck = $this->setting['secqaa']['status'] & 1;

		$fromuid = !empty($_G['cookie']['promotion']) && $this->setting['creditspolicy']['promotion_register'] ? intval($_G['cookie']['promotion']) : 0;

		$username = isset($_GET['username']) ? $_GET['username'] : '';

		$bbrulehash = $bbrules ? substr(md5(FORMHASH), 0, 8) : '';

		$auth = $_GET['auth'];

		if(!$invitestatus) {

			$invite = getinvite();

		}

		$sendurl = $this->setting['sendregisterurl'] ? true : false;

		if($sendurl) {

			if(!empty($_GET['hash'])) {

				$hash = explode("\t", authcode($_GET['hash'], 'DECODE', $_G['config']['security']['authkey']));

				if(is_array($hash) && isemail($hash[0]) && TIMESTAMP - $hash[1] < 259200) {

					$sendurl = false;

				}

			}

		}



		// 执行会员注册流程

		// if(isset($_G['minbbs_config']['sendurl']) && $_G['minbbs_config']['sendurl'] && $sendurl) {

		// 	checkemail($_GET['email']);

		// 	$hashstr = urlencode(authcode("$_GET[email]\t$_G[timestamp]", 'ENCODE', $_G['config']['security']['authkey']));

		// 	$registerurl = "{$_G[baseurl]}member.php?mod=".$this->setting['regname']."&amp;hash={$hashstr}&amp;email={$_GET[email]}";

		// 	$email_register_message = lang('email', 'email_register_message', array(

		// 		'bbname' => $this->setting['bbname'],

		// 		'siteurl' => $_G['baseurl'],

		// 		'url' => $registerurl

		// 	));

		// 	if(!sendmail("$_GET[email] <$_GET[email]>", lang('email', 'email_register_subject'), $email_register_message)) {

		// 		runlog('sendmail', "$_GET[email] sendmail failed.");

		// 	}

		// 	showmessage('register_email_send_succeed', dreferer(), array('bbname' => $this->setting['bbname']), array('showdialog' => true, 'msgtype' => 3, 'closetime' => 10));

		// }



		$emailstatus = 0;

		if($this->setting['sendregisterurl'] && !$sendurl) {

			$_GET['email'] = strtolower($hash[0]);

			$this->setting['regverify'] = $this->setting['regverify'] == 1 ? 0 : $this->setting['regverify'];

			if(!$this->setting['regverify']) {

				$groupinfo['groupid'] = $this->setting['newusergroupid'];

			}

			$emailstatus = 1;

		}

//需要有效的邀请码才能注册客户端不需要这个

//		if($this->setting['regstatus'] == 2 && empty($invite) && !$invitestatus) {

//			showmessage('not_open_registration_invite');

//		}



		// 检查用户是否同意注册协议

		if($bbrules && !isset($_GET['agreebbrule'])) {

			showmessage('register_rules_agree');

		}



		$activation = array();

		if(isset($_GET['activationauth'])) {

			$activationauth = explode("\t", authcode($_GET['activationauth'], 'DECODE'));

			if($activationauth[1] == FORMHASH && !($activation = uc_get_user($activationauth[0]))) {

				showmessage('register_activation_invalid', 'member.php?mod=logging&action=login');

			}

		}



		if(!$activation) {


			loadcache('plugin');

			// 手机号码注册 存在短信通插件

			if($_GET['regtype'] == 'm' && $_G['cache']['plugin']['smstong']) {



				//引入短信通函数

				require_once(DISCUZ_ROOT.'./source/plugin/smstong/smstong.func.php');

				$mobile = trim($_GET['mobile']);

				//短信通验证码有效期

				$periodofvalidity = $_G['cache']['plugin']['smstong']['periodofvalidity'];

				//地域检测

				require_once libfile('function/misc');

				$iparea = trim(trim(convertip($_G['clientip']),'-'));

				$flag = $_G['cache']['plugin']['smstong']['nonlocalcheck']?strstr($_G['cache']['plugin']['smstong']['areavalue'], $iparea)?true:false:false;



				$mobile = trim($_GET['mobile']);

				//手机号码检测

				if(empty($mobile) || !ismobile($mobile)) {

					odz_error('error_phone_number', '-10');

				}



				//验证手机号码是否超过配置的注册数量

				$count = DB::result_first("SELECT count(mobile) FROM ".DB::table('common_member_profile')." WHERE mobile='".trim($mobile)."'");

				if($count >= $_G['cache']['plugin']['smstong']['accountlimit']) {

					odz_error('mobile_registed', '-15');

				}



				//验证码

				$verifycode = trim($_GET['code']);



				if(!empty($mobile) && empty($verifycode)) {

					odz_error('mobilereg_verifycode_empty', '-14');

				}



				$verify = DB::result_first("SELECT mobile FROM ".DB::table('common_verifycode')." WHERE mobile='$mobile' AND verifycode='$verifycode' AND getip='$_G[clientip]' AND status=1 AND dateline>'$_G[timestamp]'-$periodofvalidity");



				//验证码验证失败

// $result = DB::query("DELETE FROM ".DB::table('common_verifycode')." WHERE mobile='15150657576'");

				if (!empty($mobile) && !$verify)

				{

					odz_error('mobilereg_mobile_verifycode_invalid', '-15');


				}



				//不是验证会员，优先使用短信通会员组否则使用dz会员组

				if(($groupinfo['groupid'] != 8)) {

					$groupinfo['groupid'] = empty($_G['cache']['plugin']['smstong']['mobilegroup']) ? $this->setting['newusergroupid'] : $_G['cache']['plugin']['smstong']['mobilegroup'];

				}



			}

			$usernamelen = dstrlen($username);

			if($usernamelen < 3) {

				showmessage('profile_username_tooshort');

			} elseif($usernamelen > 15) {

				showmessage('profile_username_toolong');

			}

			if(uc_get_user(addslashes($username)) && !C::t('common_member')->fetch_uid_by_username($username) && !C::t('common_member_archive')->fetch_uid_by_username($username)) {

				if($_G['inajax']) {

					showmessage('profile_username_duplicate');

				} else {

					showmessage('register_activation_message', 'member.php?mod=logging&action=login', array('username' => $username));

				}

			}

			if($this->setting['pwlength']) {

				if(strlen($_GET['password']) < $this->setting['pwlength']) {

					showmessage('profile_password_tooshort', '', array('pwlength' => $this->setting['pwlength']));

				}

			}

			if($this->setting['strongpw']) {

				$strongpw_str = array();

				if(in_array(1, $this->setting['strongpw']) && !preg_match("/\d+/", $_GET['password'])) {

					$strongpw_str[] = lang('member/template', 'strongpw_1');

				}

				if(in_array(2, $this->setting['strongpw']) && !preg_match("/[a-z]+/", $_GET['password'])) {

					$strongpw_str[] = lang('member/template', 'strongpw_2');

				}

				if(in_array(3, $this->setting['strongpw']) && !preg_match("/[A-Z]+/", $_GET['password'])) {

					$strongpw_str[] = lang('member/template', 'strongpw_3');

				}

				if(in_array(4, $this->setting['strongpw']) && !preg_match("/[^a-zA-z0-9]+/", $_GET['password'])) {

					$strongpw_str[] = lang('member/template', 'strongpw_4');

				}

				if($strongpw_str) {

					showmessage(lang('member/template', 'password_weak').implode(',', $strongpw_str));

				}

			}

			$email = strtolower(trim($_GET['email']));

			if (empty($email)) {

				$_GET['email'] = $email = strtolower(random(6)).'@minbbsmail.com';

			}

			if(empty($this->setting['ignorepassword'])) {

				if($_GET['password'] !== $_GET['password2']) {

					showmessage('profile_passwd_notmatch');

				}



				if(!$_GET['password'] || $_GET['password'] != addslashes($_GET['password'])) {

					showmessage('profile_passwd_illegal');

				}

				$password = $_GET['password'];

			} else {

				$password = md5(random(10));

			}

		}



		$censorexp = '/^('.str_replace(array('\\*', "\r\n", ' '), array('.*', '|', ''), preg_quote(($this->setting['censoruser'] = trim($this->setting['censoruser'])), '/')).')$/i';



		if($this->setting['censoruser'] && @preg_match($censorexp, $username)) {

			showmessage('profile_username_protect');

		}



		// 注册原因

		// if($this->setting['regverify'] == 2 && !trim($_GET['regmessage'])) {

		// 	showmessage('profile_required_info_invalid');

		// }



		if($_G['cache']['ipctrl']['ipregctrl']) {

			foreach(explode("\n", $_G['cache']['ipctrl']['ipregctrl']) as $ctrlip) {

				if(preg_match("/^(".preg_quote(($ctrlip = trim($ctrlip)), '/').")/", $_G['clientip'])) {

					$ctrlip = $ctrlip.'%';

					$this->setting['regctrl'] = $this->setting['ipregctrltime'];

					break;

				} else {

					$ctrlip = $_G['clientip'];

				}

			}

		} else {

			$ctrlip = $_G['clientip'];

		}





		$setregip = null;

		if($this->setting['regfloodctrl']) {

			$regip = C::t('common_regip')->fetch_by_ip_dateline($_G['clientip'], $_G['timestamp']-86400);

			if($regip) {

				// if($regip['count'] >= $this->setting['regfloodctrl']) {

				// 	showmessage('register_flood_ctrl', NULL, array('regfloodctrl' => $this->setting['regfloodctrl']));

				// } else {

				// 	$setregip = 1;

				// }

				$setregip = 1;

			} else {

				$setregip = 2;

			}

		}



		$profile = $verifyarr = array();



		if(!$activation) {

			$uid = uc_user_register(addslashes($username), $password, $email, $questionid, $answer, $_G['clientip']);

			if($uid <= 0) {

				if($uid == -1) {

					showmessage('profile_username_illegal');

				} elseif($uid == -2) {

					showmessage('profile_username_protect');

				} elseif($uid == -3) {

					showmessage('profile_username_duplicate');

				} elseif($uid == -4) {

					showmessage('profile_email_illegal');

				} elseif($uid == -5) {

					// showmessage('profile_email_domain_illegal');

				} elseif($uid == -6) {

					showmessage('profile_email_duplicate');

				} else {

					showmessage('undefined_action');

				}

			}

		} else {

			list($uid, $username, $email) = $activation;

		}

		$_G['username'] = $username;

		if(getuserbyuid($uid, 1)) {

			if(!$activation) {

				uc_user_delete($uid);

			}

			showmessage('profile_uid_duplicate', '', array('uid' => $uid));

		}



		$password = md5(random(10));

		$secques = $questionid > 0 ? random(8) : '';



		if(isset($_POST['birthmonth']) && isset($_POST['birthday'])) {

			$profile['constellation'] = get_constellation($_POST['birthmonth'], $_POST['birthday']);

		}

		if(isset($_POST['birthyear'])) {

			$profile['zodiac'] = get_zodiac($_POST['birthyear']);

		}



		// 上传头像

		if(isset($_FILES['avatar'])) {

		    $upload = new discuz_upload();

		    $upload->init($_FILES['avatar']);

		    $attach = $upload->attach;



		    if(!$upload->error()) {

		        $upload->save();

		        if(!$upload->get_image_info($attach['target'])) {

		            @unlink($attach['target']);

		        } else {

		            $avatarbig_file = 'temp/uc_avatarbig_'.$uid.'.jpg';

		            $avatarmiddle_file = 'temp/uc_avatarmiddle_'.$uid.'.jpg';

		            $avatarsmall_file = 'temp/uc_avatarsmall_'.$uid.'.jpg';



		            require_once libfile('class/image');

		            $image = new image();

		            $image->Thumb($attach['target'], $avatarbig_file, 199, 0, 2);

		            $image->Thumb($attach['target'], $avatarmiddle_file, 95, 0, 2);

		            $image->Thumb($attach['target'], $avatarsmall_file, 48, 48, 2);

		            @unlink($attach['target']);



		            $postdata = array();



		            require_once 'includes/imageEncoder.class.php';

		            $imageEncoder = new imageEncoder();

		            $attachdir = $_G['setting']['attachdir'];



		            $postdata['avatar1'] = $imageEncoder->flashdata_encode(file_get_contents($attachdir.'./'.$avatarbig_file));

		            $postdata['avatar2'] = $imageEncoder->flashdata_encode(file_get_contents($attachdir.'./'.$avatarmiddle_file));

		            $postdata['avatar3'] = $imageEncoder->flashdata_encode(file_get_contents($attachdir.'./'.$avatarsmall_file));



		            @unlink($attachdir.'./'.$avatarbig_file);

		            @unlink($attachdir.'./'.$avatarmiddle_file);

		            @unlink($attachdir.'./'.$avatarsmall_file);

		            $imageEncoder = null;



		            // 需要使用Discuz!和UC之间的通讯密钥

		            $ucinput = authcode('uid=' . $uid

		                . '&agent=' . md5($_SERVER['HTTP_USER_AGENT'])

		                . '&time=' . time(),

		                'ENCODE', UC_KEY);



	                $posturl = UC_API . '/index.php?m=user'

	                	. '&a=rectavatar'

	                	. '&inajax=1'

	                	. '&appid=' . UC_APPID

	                	. '&agent=' . urlencode(md5($_SERVER['HTTP_USER_AGENT']))

	                	. '&input=' . urlencode($ucinput);



                	$result = odz_http_request($posturl, 'POST', $postdata);   // 返回结果中包含 success="1" 表示上传成功



                	if(preg_match('/success="1"/i', $result)) {

                		C::t('common_member')->update($uid, array('avatarstatus'=>1));

                	    odz_writelog('会员 '.$uid.' 上传头像成功');

                	} else {

                	    odz_writelog('会员 '.$uid.' 上传头像失败');

                	}

		        }

		    }



		    unset($_FILES['avatar']);

		} else {

		    odz_writelog('会员 '.$uid.' 没有选择头像文件');

		}



		if($_FILES) {

			$upload = new discuz_upload();



			foreach($_FILES as $key => $file) {

				$field_key = 'field_'.$key;

				if(!empty($_G['cache']['fields_register'][$field_key]) && $_G['cache']['fields_register'][$field_key]['formtype'] == 'file') {



					$upload->init($file, 'profile');

					$attach = $upload->attach;



					if(!$upload->error()) {

						$upload->save();



						if(!$upload->get_image_info($attach['target'])) {

							@unlink($attach['target']);

							continue;

						}



						$attach['attachment'] = dhtmlspecialchars(trim($attach['attachment']));

						if($_G['cache']['fields_register'][$field_key]['needverify']) {

							$verifyarr[$key] = $attach['attachment'];

						} else {

							$profile[$key] = $attach['attachment'];

						}

					}

				}

			}

		}



		if($setregip !== null) {

			if($setregip == 1) {

				C::t('common_regip')->update_count_by_ip($_G['clientip']);

			} else {

				C::t('common_regip')->insert(array('ip' => $_G['clientip'], 'count' => 1, 'dateline' => $_G['timestamp']));

			}

		}



		if($invite && $this->setting['inviteconfig']['invitegroupid']) {

			$groupinfo['groupid'] = $this->setting['inviteconfig']['invitegroupid'];

		}



                // 同步手机号码至用户资料

		//加入手机号码进入资料   by zlc

		if($_GET['regtype'] == 'm') {

			$profile['mobile'] =  $mobile;

		}



		$init_arr = array('credits' => explode(',', $this->setting['initcredits']), 'profile'=>$profile, 'emailstatus' => $emailstatus);



		C::t('common_member')->insert($uid, $username, $password, $email, $_G['clientip'], $groupinfo['groupid'], $init_arr);

        //QQ第三方登录

        if($_GET['qq_openid']){

        	if(in_array('qqconnect', $_G['setting']['plugins']['available'])) {

	            $current_connect_member = C::t('#qqconnect#common_member_connect')->fetch($uid);

	            $conispublishfeed = 0;

	            $conispublisht = 0;

	            if(empty($current_connect_member)) {

	                C::t('#qqconnect#common_member_connect')->insert(

	                       array(

	                                'uid' => $uid,

	                                'conuin' => $_GET['con_request_conuin'],

	                                'conuinsecret' => $_GET['con_request_conuin_secret'],

	                                'conopenid' => $_GET['qq_openid'],

	                                'conispublishfeed' => $conispublishfeed,

	                                'conispublisht' => $conispublisht,

	                                'conisregister' => 0,

	                                'conisfeed' => 1,

	                                'conisqqshow' => $_GET['con_request_isqqshow'],

	                        )

	                );

	            } else{
								 //
	              //   C::t('#qqconnect#common_member_connect')->update($uid,
								 //
	              //           array(
								 //
	              //                  'conuin' => $_GET['con_request_conuin'],
								 //
	              //                  'conuinsecret' => $_GET['con_request_conuin_secret'],
								 //
	              //                  'conopenid' => $_GET['qq_openid'],
								 //
	              //                  'conispublishfeed' => $conispublishfeed,
								 //
	              //                  'conispublisht' => $conispublisht,
								 //
	              //                  'conisregister' => 0,
								 //
	              //                  'conisfeed' => 1,
								 //
	              //                  'conisqqshow' => $_GET['con_request_isqqshow'],
								 //
	              //          )
								 //
	              //  );
								showmessage('qq_isbind');
	            }

	            // $qq_relation='qq_ok';
							//
	            // C::t('common_member')->update($uid, array('conisbind' => '1'));
							//
	            // C::t('#qqconnect#common_connect_guest')->delete($_GET['con_request_conopenid']);

	        } else {

	        	$member_uid = DB::result_first('SELECT uid  FROM '.DB::table('minbbs_connect')." WHERE openid = '$_GET[qq_openid]'");

                $data=array('uid' => $uid, 'openid' => $_GET['qq_openid'],'type'=>'qq');

                if($member_uid){

                    showmessage('member_qq_relation');

                }else{

                    if(DB::insert('minbbs_connect',$data)){

                        $qq_relation='qq_ok';

                    }else{

                        $qq_relation='qq_erorr';

                    }

                }

	        }

        }

		if($emailstatus) {

			updatecreditbyaction('realemail', $uid);

		}

		if($verifyarr) {

			$setverify = array(

				'uid' => $uid,

				'username' => $username,

				'verifytype' => '0',

				'field' => serialize($verifyarr),

				'dateline' => TIMESTAMP,

			);

			C::t('common_member_verify_info')->insert($setverify);

			C::t('common_member_verify')->insert(array('uid' => $uid));

		}



		require_once libfile('cache/userstats', 'function');

		build_cache_userstats();



		if($this->extrafile && file_exists($this->extrafile)) {

			require_once $this->extrafile;

		}



		if($this->setting['regctrl'] || $this->setting['regfloodctrl']) {

			C::t('common_regip')->delete_by_dateline($_G['timestamp']-($this->setting['regctrl'] > 72 ? $this->setting['regctrl'] : 72)*3600);

			if($this->setting['regctrl']) {

				C::t('common_regip')->insert(array('ip' => $_G['clientip'], 'count' => -1, 'dateline' => $_G['timestamp']));

			}

		}



		$regmessage = dhtmlspecialchars($_GET['regmessage']);

		if($this->setting['regverify'] == 2) {

			C::t('common_member_validate')->insert(array(

				'uid' => $uid,

				'submitdate' => $_G['timestamp'],

				'moddate' => 0,

				'admin' => '',

				'submittimes' => 1,

				'status' => 0,

				'message' => $regmessage,

				'remark' => '',

			), false, true);

			manage_addnotify('verifyuser');

		}



		setloginstatus(array(

			'uid' => $uid,

			'username' => $_G['username'],

			'password' => $password,

			'groupid' => $groupinfo['groupid'],

		), 0);

		include_once libfile('function/stat');

		updatestat('register');



		// if($invite['id']) {

		// 	$result = C::t('common_invite')->count_by_uid_fuid($invite['uid'], $uid);

		// 	if(!$result) {

		// 		C::t('common_invite')->update($invite['id'], array('fuid'=>$uid, 'fusername'=>$_G['username'], 'regdateline' => $_G['timestamp'], 'status' => 2));

		// 		updatestat('invite');

		// 	} else {

		// 		$invite = array();

		// 	}

		// }

		// if($invite['uid']) {

		// 	if($this->setting['inviteconfig']['inviteaddcredit']) {

		// 		updatemembercount($uid, array($this->setting['inviteconfig']['inviterewardcredit'] => $this->setting['inviteconfig']['inviteaddcredit']));

		// 	}

		// 	if($this->setting['inviteconfig']['invitedaddcredit']) {

		// 		updatemembercount($invite['uid'], array($this->setting['inviteconfig']['inviterewardcredit'] => $this->setting['inviteconfig']['invitedaddcredit']));

		// 	}

		// 	require_once libfile('function/friend');

		// 	friend_make($invite['uid'], $invite['username'], false);

		// 	notification_add($invite['uid'], 'friend', 'invite_friend', array('actor' => '<a href="home.php?mod=space&uid='.$invite['uid'].'" target="_blank">'.$invite['username'].'</a>'), 1);



		// 	space_merge($invite, 'field_home');

		// 	if(!empty($invite['privacy']['feed']['invite'])) {

		// 		require_once libfile('function/feed');

		// 		$tite_data = array('username' => '<a href="home.php?mod=space&uid='.$_G['uid'].'">'.$_G['username'].'</a>');

		// 		feed_add('friend', 'feed_invite', $tite_data, '', array(), '', array(), array(), '', '', '', 0, 0, '', $invite['uid'], $invite['username']);

		// 	}

		// 	if($invite['appid']) {

		// 		updatestat('appinvite');

		// 	}

		// }



		if($welcomemsg && !empty($welcomemsgtxt)) {

			$welcomemsgtitle = replacesitevar($welcomemsgtitle);

			$welcomemsgtxt = replacesitevar($welcomemsgtxt);

			if($welcomemsg == 1) {

				$welcomemsgtxt = nl2br(str_replace(':', '&#58;', $welcomemsgtxt));

				notification_add($uid, 'system', $welcomemsgtxt, array('from_id' => 0, 'from_idtype' => 'welcomemsg'), 1);

			} elseif($welcomemsg == 2) {

				sendmail_cron($email, $welcomemsgtitle, $welcomemsgtxt);

			} elseif($welcomemsg == 3) {

				sendmail_cron($email, $welcomemsgtitle, $welcomemsgtxt);

				$welcomemsgtxt = nl2br(str_replace(':', '&#58;', $welcomemsgtxt));

				notification_add($uid, 'system', $welcomemsgtxt, array('from_id' => 0, 'from_idtype' => 'welcomemsg'), 1);

			}

		}



		if($fromuid) {

			updatecreditbyaction('promotion_register', $fromuid);

			dsetcookie('promotion', '');

		}

		dsetcookie('loginuser', '');

		dsetcookie('activationauth', '');

		dsetcookie('invite_auth', '');



		$url_forward = dreferer();

		$refreshtime = 3000;

		switch($this->setting['regverify']) {

			case 1:

				$idstring = random(6);

				$authstr = $this->setting['regverify'] == 1 ? "$_G[timestamp]\t2\t$idstring" : '';

				C::t('common_member_field_forum')->update($_G['uid'], array('authstr' => $authstr));

				// $verifyurl = "{$_G[baseurl]}member.php?mod=activate&amp;uid={$_G[uid]}&amp;id=$idstring";

				// $email_verify_message = lang('email', 'email_verify_message', array(

				// 	'username' => $_G['member']['username'],

				// 	'bbname' => $this->setting['bbname'],

				// 	'siteurl' => $_G['baseurl'],

				// 	'url' => $verifyurl

				// ));

				// if(!sendmail("$username <$email>", lang('email', 'email_verify_subject'), $email_verify_message)) {

				// 	runlog('sendmail', "$email sendmail failed.");

				// }

				$message = 'register_email_verify';

				$locationmessage = 'register_email_verify_location';

				$refreshtime = 10000;

				break;

			case 2:

				$message = 'register_manual_verify';

				$locationmessage = 'register_manual_verify_location';

				break;

			default:

				$message = 'register_succeed';

				$locationmessage = 'register_succeed_location';

				break;

		}





		//用户验证

		$member = getuserbyuid($uid);

		// if($member && $member['groupid'] == 8) {

			$member = array_merge(C::t('common_member_field_forum')->fetch($member['uid']), $member);

			$newgroup = C::t('common_usergroup')->fetch_by_credits($member['credits']);

			C::t('common_member')->update($member['uid'], array('groupid' => $newgroup['groupid'], 'emailstatus' => '1'));

			C::t('common_member_field_forum')->update($member['uid'], array('authstr' => ''));

		// }



		// 注册成功，返回注册用户UID

		// odz_stats(array('typeid'=>3, 'action'=>'register'));

		odz_result(array('uid'=>$uid,'qq_relation'=>!empty($qq_relation)?$qq_relation:''));

	}



}

?>
