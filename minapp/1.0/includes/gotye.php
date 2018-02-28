<?php
/**
 * �׼�ͨѶ�Ʋ�����
 * �׼Ӽ�ʱͨѶ���ṩ��һϵ�еĺ�̨ API�ӿڣ���������ܼ򵥣��ر�����ֻ�п����ߵ��˺ź���������������
 * ����ʹ�ù��������ǲ���https�ķ�ʽ�Ա����û��İ�ȫ�ԡ�
 * ͨ������ӿڣ��������һϵ�п�ѡ���������紴���˺ţ��޸����дʣ����������ң���Ӻ��ѣ��û����Եȡ�
 * ���ھ������Ӧ�ã�����ǿ�ҽ���ʹ�ú�̨ API�ӿڣ���������Ƿ������Է�����֮��ĵ��ã���ȫ��ϵ����ã�������ԭ�������ʧ�ܿ���Ҳ���С��
 * �����ڸ�С��Ӧ�ã�������ֻ��Ҫ����������������ͨ����Ӧ�ã�����ӿڽ���Ϊһ����ѡ����������ʹ�ÿͻ��˵ļ��ɣ�������������������ɵĹ��̡�
 * @author ������
 *
 * @todo DisableSay �ӿڵ��Գ���û�а취��ѯ�û�״̬
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
     *  �����û�
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
     * ��ȡ�û��б�
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
     * ���һ�����ѻ��ߺ�����
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
     * ɾ��һ�����ѻ��ߺ�����
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
     * �û����Ի��߽�����ԡ�
     * @param $account string ���Ե��û���
     * @param int $level
     * @param int $type
     * @param bool $room_id
     * @return mixed
     * @todo times �ĳɱ�����д���ܴ��ʱ��
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
     * ��ȡ�����б�
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
     * ����һ��Ⱥ��
     * @param $group_name Ⱥ������
     * @param $owner_account �������˺�
     * @param bool $group_info Ⱥ��չ��Ϣ
     * @param bool $group_head Ⱥͷ���ѡ
     * @param int $approval �������� 0Ϊ���� 1Ϊ��ҪȺ����֤
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
     * �޸�Ⱥ��Ϣ
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
     * ��ɢһ��Ⱥ
     * @param $group_id
     * @return mixed
     */
    public function DismissGroup($group_id){
        $url=$this->getUrl("DismissGroup");
        $this->adata['group_id']=$group_id;
        return $this->http_post_data($url,$this->adata);
    }
    /**
     * ��ȡȺ��Ա�б�
     * @param $group_id
     * @return mixed
     */
    public function GetGroupUserList($group_id){
        $url=$this->getUrl("GetGroupUserList");
        $this->adata['group_id']=$group_id;
        return $this->http_post_data($url,$this->adata);

    }
    /**
     * ���һ��Ⱥ��Ա
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
     * ɾ��һ��Ⱥ��Ա
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
     * ��ȡȺ�б�
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
     * ��ȡȺ�б��Ⱥ����
     * @param array $group_id_list
     * @return mixed
     */
    public function GetGroupDetail($group_id_list=array()){
        $url=$this->getUrl("GetGroupDetail");
        $this->adata['group_id_list']=$group_id_list;
        return $this->http_post_data($url,$this->adata);
    }

    /**
     * ����������
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
     * ɾ��������
     * @param $room_id
     * @return mixed
     */
    public function DeleteRoom($room_id){
        $url=$this->getUrl("DeleteRoom");
        $this->adata['room_id']=$room_id;
        return $this->http_post_data($url,$this->adata);
    }

    /**
     * ��ȡ�������б�
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
     * ������Ϣ
     * to_id ��ToType��Ӧ�������Һ�Ⱥ��дID���û���д�˺š�֧���������ͣ��˴��������Ͳ�������࣬50������Ϊ�ѣ���಻Ҫ����100�������磺[��������,����С��,�����ӡ�]
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
     * ��ȡ�����¼
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
     * ��ȡ�����������ַ
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
        $curl = curl_init(); // ����һ��CURL�Ự
        curl_setopt($curl, CURLOPT_URL, $url); // Ҫ���ʵĵ�ַ
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0); // ����֤֤����Դ�ļ��
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2); // ��֤���м��SSL�����㷨�Ƿ����
        curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']); // ģ���û�ʹ�õ������
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1); // ʹ���Զ���ת
        curl_setopt($curl, CURLOPT_AUTOREFERER, 1); // �Զ�����Referer
        curl_setopt($curl, CURLOPT_POST, 1); // ����һ�������Post����
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data); // Post�ύ�����ݰ�
        curl_setopt($curl, CURLOPT_TIMEOUT, 30); // ���ó�ʱ���Ʒ�ֹ��ѭ��
        curl_setopt($curl, CURLOPT_HEADER, 0); // ��ʾ���ص�Header��������
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); // ��ȡ����Ϣ���ļ�������ʽ����
        $tmpInfo = curl_exec($curl); // ִ�в���
        if (curl_errno($curl)) {
            echo 'Errno'.curl_error($curl);//��ץ�쳣
        }
        curl_close($curl); // �ر�CURL�Ự
        return $tmpInfo; // ��������
    }
    /**
    *  @desc ���������ľ�γ�ȼ������
    *  @param float $lat γ��ֵ
    *  @param float $lng ����ֵ
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
    *  @desc ��������
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
    *  @desc ����ʱ��
    *  @param $the_time ʱ���
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
          return $dur.'��ǰ';
        }else if($dur < 3600){
           return floor($dur/60).'����ǰ';
        }else if($dur < 86400){
            return floor($dur/3600).'Сʱǰ';
        }else if($dur < 259200){//3����
            return floor($dur/86400).'��ǰ';
        }else{
            return date("Y-m-d",$the_time);
        }
    }

    //��γ�Ȱ뾶��ȷ�󸽽��ص�
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
    //��ʽ��ʱ��
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

