<?php

/**
 * 微分享模块回复或点赞提醒
 *
 * @param int $id 帖子ID
 * @param string $idtype 帖子ID类型(tid,pid)
 * @param int $praid 点赞ID
 * @param int $message 提醒内容,为空则调用主题帖内容
 */
function push_reply($id,$idtype='tid',$praid=0,$message='') {
    global $_G;
    if($idtype=='pid'){
      $thapost = C::t('forum_post')->fetch(0,$id, 0);  
    }else{
      $thapost = C::t('forum_post')->fetch_threadpost_by_tid_invisible($id);
      $idtype='tid';
      $id=$_G['tid']?$_G['tid']:$id;
    }
    $uid =$thapost['authorid'];
    if(empty($message)){
        $message = $thapost['message'];
    }
    require_once libfile('function/discuzcode');
    $message = cutstr(strip_tags(discuzcode($message,0,1)), 140);
    //插入数据
    if($thapost['fid']==$_G['minbbs_config']['share_fid']&&$_G['username']){
        C::t('minbbs_push_message')->insert(array(
            'tid' => $thapost['tid'],
            'name' => $_G['username'],
            'uid' => $uid,
            'time' => TIMESTAMP,
            'types' => $idtype,
            'typename' => 'thread_info',
            'isprise' => $praid,
            'message' => $message,
        ));
    }
}

function push_baidu($alert, $uid, $tid){
    $params = array(
        'channelid'=>ODZ_CHANNELID,
        'alert'=>$alert,
        'uid'=>$uid,
        'custom_content'=>json_encode(array(
        'ios'=>array('typename'=>'thread_info','id'=>$tid,'mode' => 1,'types' => $_GET['types']),
        'android'=>array('extras'=>array('content'=>array('id'=>$tid),'typename'=>"thread_info",'types' => $_GET['types'])))),
    ); 
    curl_push($params);
}


function push_jiguang($alert, $uid, $tid){
     global $_G;
     $alias = array($uid);
     $extras_ios = array(
        'typename' => 'thread_info',
        'types' => $_GET['types'],
        'mode' => 1,
        'id' => $tid
    );
    $extras_android = array(
        'typename' => 'thread_info',
        'types' => $_GET['types'],
        'mode' => 1,
        'content' => array(
            'id' => $tid
        )
    );

    $params = array(
        'platform' => 'all',
        'audience' => array(
            'alias' => $alias
        ),
        'notification' => array(
            'alert' => $alert,
            'ios' => array(
                'sound' => 'default',
                'badge' => '+1',
                'extras' => $extras_ios
            ),
            'android' => array(
                'extras' => $extras_android
            ),
        ),
        'options' => array(
            'apns_production' => true
        )
    );
    $data = array(
            'appkey' => JPUSH_APPKEY, 
            'appsecret' => JPUSH_APPSECRET, 
            'params' => json_encode($params));
    $url =  isset($_G['minbbs_config']['use_ip']) ? 'http://'.$_G['minbbs_config']['use_ip'].'/requests/get_params' : 'http://www.minbbs.com/requests/get_params';

    require dirname(__FILE__).'/../library/Requests.php';
    Requests::register_autoloader();

    $header = array(
        'Host' => 'www.minbbs.com'
    );
    
    //根据配置选择curl或者socket
    $options['verify'] = false;
    $options['timeout'] = 2;
    $options['transport'] = isset($_G['minbbs_config']['transport']) && $_G['minbbs_config']['transport'] == 'fsockopen' ? 'Requests_Transport_fsockopen' : '';
    
   
    $response = Requests::post($url, $header, $data, $options);     
    xylog($response->body);

}

if(!function_exists('xylog')) {
    function xylog($log) {
        file_put_contents(DISCUZ_ROOT.'/data/xylog.txt',
            date('Y-m-d H:i:s').' '.$log."\n",
            FILE_APPEND);
    }
}
?>