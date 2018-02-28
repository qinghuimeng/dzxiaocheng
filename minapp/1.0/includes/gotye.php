<?php
/**
 * 亲加通讯云操作类
 * 亲加即时通讯云提供了一系列的后台 API接口，这个方法很简单，必备参数只有开发者的账号和密码两个参数，
 * 而在使用过程中我们采用https的方式以保护用户的安全性。
 * 通过此类接口，可以完成一系列可选操作，诸如创建账号，修改敏感词，创建聊天室，添加好友，用户禁言等。
 * 对于绝大多数应用，我们强烈建议使用后台 API接口，这个方法是服务器对服务器之间的调用，安全体系会更好，由网络原因带来的失败可能也会更小。
 * 而对于更小的应用，或者是只需要单聊甚至单聊数据通道的应用，此类接口将作为一个可选方案，仅仅使用客户端的集成，将会更快的完成整个集成的过程。
 * @author 王宝存
 *
 * @todo DisableSay 接口调试出错，没有办法查询用户状态
 *
 */
class GoTye_ctl
{
    private $url  = "https://qplusapi.gotye.com.cn:8443/api/";
    private $adata = array("email"=>"wangzhaofei@imoopin.com","devpwd"=>"wang265113","appkey"=>"79dac4da-d291-4ea7-bada-f8e12b010003");

/*
    public function __construct($action)
    {
    	$this->setKey($params['key'], $params['pack']);
    	$this->setIv($params['iv'], $params['pack']);
    }
*/


    /**
     *  导入用户
     * @param $users array
     * @return mixed
     */
    public function ImportUsers($users)
    {
        $url=$this->getUrl("ImportUsers");
        $this->adata['users']=$users;
        return $this->http_post_data($url,$this->adata);
    }

    /**
     * 获取用户列表
     * @param int $index
     * @param int $count
     * @return mixed
     */
    public function GetUserlist($index=0,$count=20)
    {
        $url=$this->getUrl("GetUserlist");
        $this->adata['index']=$index;
        $this->adata['count']=$count;
        return $this->http_post_data($url,$this->adata);

    }

    /**
     * 添加一个好友或者黑名单
     * @param $user_account
     * @param $friend_account
     * @param int $type
     * @return mixed
     */
    public function AddBlacklist($user_account,$friend_account,$type=0){

        $url=$this->getUrl("AddBlacklist");
        $this->adata['user_account']=$user_account;
        $this->adata['friend_account']=$friend_account;
        $this->adata['type']=$type;

        return $this->http_post_data($url,$this->adata);
    }

    /**
     * 删除一个好友或者黑名单
     * @param $user_account
     * @param $friend_account
     * @param int $type
     * @return mixed
     */
    public function DelBlacklist($user_account,$friend_account,$type=0){

        $url=$this->getUrl("DelBlacklist");
        $this->adata['user_account']=$user_account;
        $this->adata['friend_account']=$friend_account;
        $this->adata['type']=$type;

        return $this->http_post_data($url,$this->adata);
    }

    /**
     * 用户禁言或者解除禁言。
     * @param $account string 禁言的用户名
     * @param int $level
     * @param int $type
     * @param bool $room_id
     * @return mixed
     * @todo times 改成必填先写个很大的时间
     */
    public function DisableSay($account,$level=1,$type=0,$times=100000000000,$room_id=0){
        $url=$this->getUrl("DisableSay");
        $this->adata['account']=$account;
        $this->adata['level']=$level;
        $this->adata['type']=$type;
        if($times) $this->adata['times']=$times;
        if($room_id) $this->adata['room_id']=$room_id;
        return $this->http_post_data($url,$this->adata);
    }

    /**
     * 获取禁言列表
     * @param int $level
     * @param int $type
     * @param int $page_no
     * @param int $page_count
     * @param int $room_id
     * @return mixed
     */
    public function GetDisableSays($level=0,$type=0,$page_no=1,$page_count=20,$room_id=0){
        $url=$this->getUrl("GetDisableSays");
        $this->adata['level']=$level;
        $this->adata['type']=$type;
        $this->adata['page_no']=$page_no;
        if($room_id) $this->adata['room_id']=$room_id;
        return $this->http_post_data($url,$this->adata,true);
    }
    /**
     * 创建一个群组
     * @param $group_name 群组名称
     * @param $owner_account 所有者账号
     * @param bool $group_info 群扩展信息
     * @param bool $group_head 群头像可选
     * @param int $approval 加入类型 0为自由 1为需要群组验证
     * @param int $owner_type
     * @return mixed
     */
    public  function CreateGroup($group_name,$owner_account,$group_head=false,$group_info=false,$approval=0,$owner_type=0){
        $url=$this->getUrl("CreateGroup");
        $this->adata['group_name']=$group_name;
        $this->adata['owner_account']=$owner_account;
        $this->adata['owner_type']=$owner_type;
        $this->adata['approval']=$approval;
        if($group_head) $this->adata['group_head']=$group_head;
        if($group_info) $this->adata['group_info']=$group_info;
        return $this->http_post_data($url,$this->adata);
    }

    /**
     * 修改群信息
     * @param $group_name
     * @param $owner_account
     * @param bool $group_head
     * @param bool $group_info
     * @param int $approval
     * @param int $owner_type
     * @return mixed
     */
    public function ModifyGroup($group_id,$group_name,$owner_account=0,$group_head=false,$group_info=false,$approval=0,$owner_type=0){
        $url=$this->getUrl("ModifyGroup");
        if($group_id) $this->adata['group_id']=$group_id;
        if($group_name) $this->adata['group_name']=$group_name;
        if($owner_account) $this->adata['owner_account']=$owner_account;
        if($owner_type) $this->adata['owner_type']=$owner_type;
        if($approval) $this->adata['approval']=$approval;
        if($group_head) $this->adata['group_head']=$group_head;
        if($group_info) $this->adata['group_info']=$group_info;
        return $this->http_post_data($url,$this->adata);
    }

    /**
     * 解散一个群
     * @param $group_id
     * @return mixed
     */
    public function DismissGroup($group_id){
        $url=$this->getUrl("DismissGroup");
        $this->adata['group_id']=$group_id;
        return $this->http_post_data($url,$this->adata);
    }
    /**
     * 获取群成员列表
     * @param $group_id
     * @return mixed
     */
    public function GetGroupUserList($group_id){
        $url=$this->getUrl("GetGroupUserList");
        $this->adata['group_id']=$group_id;
        return $this->http_post_data($url,$this->adata);

    }
    /**
     * 添加一个群成员
     * @param $group_id
     * @param $user_account
     * @return mixed
     */
    public function AddGroupMember($group_id,$user_account){
        $url=$this->getUrl("AddGroupMember");
        $this->adata['group_id']=$group_id;
        $this->adata['user_account']=$user_account;
        return $this->http_post_data($url,$this->adata);
    }

    /**
     * 删除一个群成员
     * @param $group_id
     * @param $user_account
     * @return mixed
     */
    public function DelGroupMember($group_id,$user_account){
        $url=$this->getUrl("DelGroupMember");
        $this->adata['group_id']=$group_id;
        $this->adata['user_account']=$user_account;
        return $this->http_post_data($url,$this->adata);
    }

    /**
     * 获取群列表
     * @param int $last_group_id
     * @param int $count
     * @return mixed
     */
    public function GetGroups($last_group_id=0,$count=20)
    {
        $url=$this->getUrl("GetGroups");
        $this->adata['last_group_id']=$last_group_id;
        $this->adata['count']=$count;
        return $this->http_post_data($url,$this->adata);

    }

    /**
     * 获取群列表的群详情
     * @param array $group_id_list
     * @return mixed
     */
    public function GetGroupDetail($group_id_list=array()){
        $url=$this->getUrl("GetGroupDetail");
        $this->adata['group_id_list']=$group_id_list;
        return $this->http_post_data($url,$this->adata);
    }

    /**
     * 创建聊天室
     * @param $room_name
     * @param int $room_type
     * @param int $room_create_type
     * @return mixed
     */
    public function CreateRoom($room_name,$room_type=2,$room_create_type=0){
        $url=$this->getUrl("CreateRoom");
        $this->adata['room_name']=$room_name;
        $this->adata['room_type']=$room_type;
        $this->adata['room_create_type']=$room_create_type;
        return $this->http_post_data($url,$this->adata);
    }

    /**
     * 删除聊天室
     * @param $room_id
     * @return mixed
     */
    public function DeleteRoom($room_id){
        $url=$this->getUrl("DeleteRoom");
        $this->adata['room_id']=$room_id;
        return $this->http_post_data($url,$this->adata);
    }

    /**
     * 获取聊天室列表
     * @param $last_room_id
     * @return mixed
     */
    public function GetRooms($last_room_id=0,$count=20){
        $url=$this->getUrl("GetRooms");
        $this->adata['last_room_id']=$last_room_id;
        if($count)$this->adata['count']=$count;
        return $this->http_post_data($url,$this->adata);
    }

    /**
     * 发送消息
     * to_id 与ToType对应，聊天室和群填写ID，用户填写账号。支持批量发送，此处批量发送不建议过多，50个以内为佳，最多不要超过100个。例如：[“阿三”,”王小”,”锤子”]
     * @param $from
     * @param int $to_type
     * @param $to_id
     * @param $text
     * @param int $msg_type
     * @param int $save
     * @param $extra_data
     * @return mixed
     */
    public function SendMsg($from,$to_type=0,$to_id,$text,$msg_type=0,$save=1,$extra_data){
        $url=$this->getUrl("SendMsg");
        $this->adata['from']=$from;
        $this->adata['to_type']=$to_type;
        $this->adata['to_id']=$to_id;
        $this->adata['save']=$save;
        $this->adata['text']=$text;
        $this->adata['msg_type']=$msg_type;
        if($extra_data)$this->adata['extra_data']=$extra_data;
        return $this->http_post_data($url,$this->adata);
    }

    /**
     * 获取聊天记录
     * @param $start_date
     * @param $end_date
     * @param int $index
     * @param int $count
     * @param int $receiver_type
     * @param int $receiver_id
     * @param int $sender_id
     * @return mixed
     */
    public function GetMsgHistory($start_date,$end_date,$index=1,$count=20,$receiver_type=3,$receiver_id=0,$sender_id=0){
        $url=$this->getUrl("GetMsgHistory");
        $this->adata['start_date']=$start_date;
        $this->adata['end_date']=$end_date;
        $this->adata['index']=$index;
        $this->adata['count']=$count;
        if($receiver_type<3)$this->adata['receiver_type']=$receiver_type;
        if($receiver_id)$this->adata['receiver_id']=$receiver_id;
        if($sender_id)$this->adata['sender_id']=$sender_id;

        return $this->http_post_data($url,$this->adata);
    }
   /**
     * 获取完整的请求地址
     * @param $action
     * @return string
     * @access private
     */
    private function getUrl($action)
    {
        return $this->url.$action;
    }
    function send_request($url, $params = array()) {
    $params = is_array($params) ? json_encode($params) : (string)$params;
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
    $result = curl_exec($ch);
    curl_close($ch);
    print "Raw Result: \"".$result."\"\n";
    return $result;
}
    /*
     * post json https
     */
    public function http_post_data($url, $data,$debug=false) {
        $data = json_encode($this->adata);
        if($debug)echo $data;
        $curl = curl_init(); // 启动一个CURL会话
        curl_setopt($curl, CURLOPT_URL, $url); // 要访问的地址
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0); // 对认证证书来源的检查
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2); // 从证书中检查SSL加密算法是否存在
        curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']); // 模拟用户使用的浏览器
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1); // 使用自动跳转
        curl_setopt($curl, CURLOPT_AUTOREFERER, 1); // 自动设置Referer
        curl_setopt($curl, CURLOPT_POST, 1); // 发送一个常规的Post请求
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data); // Post提交的数据包
        curl_setopt($curl, CURLOPT_TIMEOUT, 30); // 设置超时限制防止死循环
        curl_setopt($curl, CURLOPT_HEADER, 0); // 显示返回的Header区域内容
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); // 获取的信息以文件流的形式返回
        $tmpInfo = curl_exec($curl); // 执行操作
        if (curl_errno($curl)) {
            echo 'Errno'.curl_error($curl);//捕抓异常
        }
        curl_close($curl); // 关闭CURL会话
        return $tmpInfo; // 返回数据
    }
    /**
    *  @desc 根据两点间的经纬度计算距离
    *  @param float $lat 纬度值
    *  @param float $lng 经度值
    */
     function getDistance($lat1, $lng1, $lat2, $lng2)
     {
         $earthRadius = 6378137; //approximate radius of earth in meters
         /*
           Convert these degrees to radians
           to work with the formula
         */
         $lat1 = ($lat1 * pi() ) / 180;
         $lng1 = ($lng1 * pi() ) / 180;
         $lat2 = ($lat2 * pi() ) / 180;
         $lng2 = ($lng2 * pi() ) / 180;
         /*
           Using the
           Haversine formula
           http://en.wikipedia.org/wiki/Haversine_formula
           calculate the distance
         */
         $calcLongitude = $lng2 - $lng1;
         $calcLatitude = $lat2 - $lat1;
         $stepOne = pow(sin($calcLatitude / 2), 2) + cos($lat1) * cos($lat2) * pow(sin($calcLongitude / 2), 2);  $stepTwo = 2 * asin(min(1, sqrt($stepOne)));
         $calculatedDistance = $earthRadius * $stepTwo;
         return round($calculatedDistance);
     }
     /**
    *  @desc 计算年龄
    *  @param $YTD 1949-10-01
    */
     function age($YTD){
        $YTD = strtotime($YTD);//int strtotime ( string $time [, int $now ] )
        $year = date('Y', $YTD);
        if(($month = (date('m') - date('m', $YTD))) < 0){
         $year++;
        }else if ($month == 0 && date('d') - date('d', $YTD) < 0){
         $year++;
        }
        return date('Y') - $year;
   }
    /**
    *  @desc 计算时间
    *  @param $the_time 时间戳
    */
    function time_tran($the_time){
        $the_times= date("Y-m-d H:i:s",$the_time);
        $now_time = date("Y-m-d H:i:s",time()+8*60*60);
        $now_time = strtotime($now_time);
        $show_time = strtotime($the_time);
        $dur = $now_time - $show_time;
        if($dur < 0){
          return $the_times;
        }else if($dur < 60){
          return $dur.'秒前';
        }else if($dur < 3600){
           return floor($dur/60).'分钟前';
        }else if($dur < 86400){
            return floor($dur/3600).'小时前';
        }else if($dur < 259200){//3天内
            return floor($dur/86400).'天前';
        }else{
            return date("Y-m-d",$the_time);
        }
    }

    //经纬度半径精确求附近地点
    function  getAround( $lat, $lon,$raidus=18000) {

            $data           = array();
            $PI             = 3.14159265;
            $EARTH_RADIUS   = 6378137;
            $RAD            = $PI / 180.0;
            $latitude       = $lat;
            $longitude      = $lon;
            $degree         = (24901*1609)/360.0;
            $raidusMile     = $raidus;
            $dpmLat         = 1/$degree;
            $radiusLat      = $dpmLat*$raidusMile;
            $minLat         = $latitude - $radiusLat;
            $maxLat         = $latitude + $radiusLat;
            $data["maxLat"] = $maxLat;
            $data["minLat"] = $minLat;
            $mpdLng         = $degree*cos($latitude * ($PI/180));
            $dpmLng         = 1 / $mpdLng;
            $radiusLng      = $dpmLng*$raidusMile;
            $minLng         = $longitude - $radiusLng;
            $maxLng         = $longitude + $radiusLng;
            $data["maxLng"] = $maxLng;
            $data["minLng"] = $minLng;

            return $data;
    }
    //格式化时间
    function my_date_format($time,$format='Y-m-d') {
        if(empty($time)){
            return ;
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


}

