<?php

class Thread_Service{

    public $input_threads;
    public $posttables;
    public $attachment_pid;
    public $postlist;

    public $templatename;
    public $threadsort;
    public $threadsort_result;

    public $picsize;


    public function __construct(){

        $this->reset();
    }


    /**
     * 选取模式，如果是分类信息，则获取分类信息结果
     * @return [type] [description]
     */
    public function check_show_mode(){

        if(empty($this->setting['threadsort_type'])){
            $this->mode = 1;
            return $this->mode;
        }

        $fid = $this->setting['fid'];
        $query = DB::query("SELECT * FROM minbbs_card_threadsort WHERE fid = $fid");

        while ($row = DB::fetch($query)) {
            $row['fields'] = unserialize($row['fields']);
            $this->sortinfo[$row['sortid']] = $row;
        }
        $this->mode = 2;


        return $this->mode;
    }



    public function reset(){
        $this->input_threads = array();
        $this->output_threads = array();
        $this->posttables = array();
        $this->attachment_pid = array();
        $this->threadsort = array();
        $this->postlist = array();
        $this->setting = array(
            'image_size' => false,
            'image_num' => 3,
        );
        $this->picsize = array(
            7 => array('sigle' =>array(420, 276), 'many' => array(300, 195)),
            8 => array('sigle' =>array(285, 255), 'many' => array(285, 255)),
            9 => array('sigle' =>array(285, 255), 'many' => array(285, 255))
        );
        $this->typeoption = array();
        $this->map = array('uid' => 'authorid');
    }

    /**
     * 写入数据
     * @param  array $thread 从数据库中取出的thread
     * @return
     */
    public function input_thread($thread){
        $this->input_threads[$thread['tid']] = $thread;
        $this->posttables[$thread['posttableid']][] = $thread['tid'];
        if($thread['sortid'] != 0){
            $this->threadsort[$thread['sortid']][] = $thread['tid'];
        }

        unset($thread['tid']);

    }


    /**
     * 获取tid对应的post并提出有附件的pid
     * @return [type] [description]
     */
    private function get_tid_post(){

        foreach($this->posttables as $tableid => $tids) {

            //过滤已经获取的图片
            foreach($tids as $k => $tid){
                if(isset($this->tidvalues[$tid]['pics'])){
                    unset($tids[$k]);
                }
            }

            if(empty($tids)){
                continue;
            }


            foreach(C::t('forum_post')->fetch_all_by_tid($tableid, $tids, true, '', 0, 0, 1) as $post) {

                $this->postlist[$post['tid']] = $post;

                //判断是否有附件图片
                if($this->input_threads[$post['tid']]['attachment'] > 0){
                    $this->attachment_pid[$post['tid']] = $post['pid'];
                }
            }
        }
    }

    /**
     * 提取附件中的图片
     * @return [type] [description]
     */
    public function get_attachment_images(){
       global $_G;



        foreach($this->attachment_pid as $tid => $pids){

            $this->input_threads[$tid]['pics'] = array();

            foreach(C::t('forum_attachment_n')->fetch_all_by_id('tid:'.$tid, 'pid', $pids, '', array(1,-1)) as $attach) {

                //满足图片需求则退出
                if(count($this->input_threads[$tid]['pics']) >= $this->setting['image_num']){
                    continue;
                }

                //获取图片宽高
                $attach['url'] = ($attach['remote'] ? $_G['setting']['ftp']['attachurl'] : $_G['setting']['attachurl']).'forum/';
                $attachurl = $attach['url'].$attach['attachment'].($attach['thumb'] ? '.thumb.jpg' : '');
                $urlparts = parse_url($attachurl);

                $width = 328;
                $height = 328;
                if(empty($urlparts['host'])) {
                    $target = DISCUZ_ROOT.$attachurl;
                    if(file_exists($target) && $size = getimagesize($target)) {
                        $width = $size[0];
                        $height = $size[1];
                    }
                    $this->input_threads[$tid]['need_thumb'][$attach['aid']] = 1;
                    $url = $attach['aid'];
                }else{
                    $url = $attachurl;
                }




                //根据设置确定是否返回宽高
                if($this->setting['image_size']){
                    $this->input_threads[$tid]['pics'][] = array(
                        'width' => $width,
                        'height' => $height,
                        'url' => $url
                    );
                }else{
                    $this->input_threads[$tid]['pics'][] = $url;
                }
            }

        }

    }





    public function set($key, $value){
        $this->setting[$key] = $value;
    }

    /**
     * 输出数据
     * @return [type] [description]
     */
    public function output_threads($template){

        $this->templatename = $template;

        //选取模板
        if(!is_array($template)){
            include MINBBS_ROOT.'includes/define.php';
            $template = $thread_template[$template];
        }

        //判断是否有分类信息，以及提取分类信息
        $tidvalues = array();
        if($this->check_show_mode() == 2){

            $threadsort = new Threadsort_Service;
            $this->tidvalues = $threadsort->get_tid_value($this->threadsort, $this->sortinfo);

        }

        if(in_array('pics', $template)){

            $this->get_tid_post();
            $this->get_attachment_images();
        }




        $result = $this->format_threads($template, $this->tidvalues);



        return $this->output_threads;
    }




    /**
     * 格式化数据
     * @param  [type] $template [description]
     * @return [type]           [description]
     */
    public function format_threads($template, $tidmerge = array()){

        $this->output_threads = array();
        foreach($this->input_threads as $thread){

            $temp = array();
            $temp['template'] = isset($this->sortinfo[$thread['sortid']]['template']) ? $this->sortinfo[$thread['sortid']]['template'] : 7;

            foreach($template as $key){
                $temp[$key] = $this->key_map($thread, $key, $temp['template']);
            }

            if(isset($tidmerge[$thread['tid']])){
                $temp = array_merge($temp, $tidmerge[$thread['tid']]);
            }






            $this->output_threads[] = $temp;
        }
        //print_r($this->output_threads);exit;
        return $this->output_threads;
    }

    /**
     * 映射thread数据
     */
    public function key_map($thread, $key, $template){
        global $_G;

        $map_key = isset($this->map[$key]) ? $this->map[$key] : $key;

      
        switch ($key) {

            case 'avatar':
                return avatar($thread['authorid'], 'small', true);
                break;
            case 'time':
                return dgmdate($thread['dateline'], 'mu');
                break;
            case 'likes' :
                return (string)intval(C::t('home_praise_total')->fetch_by_id_idtype($thread['tid'],'tid'));
                break;
            case 'forumname':
                loadcache('forums');
                return strip_tags($_G['cache']['forums'][$thread['fid']]['name']);
                break;
			case 'message':
                $post = C::t('forum_post')->fetch_all_by_tid('',$thread['tid'],true,'',0,1,1); //获取帖子的first post，获取的格式为{'pid'=>'id:id,message:message,subject:subject....'}
				$pid = array_keys($post); //重新排序获取主键,即pid
				
				$message=preg_replace('/\[(flash|media|audio|img|attach)[^\]]*?\]([\s\S]+?)\[\/\1\]/is','',$post[$pid[0]]['message']); //第一道过滤，去除媒体及附件标签（此处连同标签内的内容也一起去除）
				$message = preg_replace(
							array('/&amp;(#\d{3,5};)/', "/\[hide=?\d*\](.*?)\[\/hide\]/is", "/\[\/?\w+=?.*?\]/"),
							array('&\\1','<b>**** Hidden Message *****</b>',''),
							str_replace(
							array('&', '"', '<', '>', "\t", '   ', '  '),
							array('&amp;', '&quot;', '&lt;', '&gt;', '&nbsp; &nbsp; &nbsp; &nbsp; ', '&nbsp; &nbsp;', '&nbsp;&nbsp;'),$message)); //第二道过滤，去除所有UBB标签
				$message = str_replace(array(" ","　","\t","\n","\r"),array("","","","",""),$message);//第三道过滤，去除换行符\r\n等标签

				if(mb_strlen($message) > 40){
					return mb_substr($message,0,40).'...';
				}else{
					return $message;
				}
				return strip_tags($post[$pid[0]]['message']);
                break;
			case 'views':
				foreach(C::t('forum_threadaddviews')->fetch_all($thread['tid']) as  $value) {
					$thread['views'] += $value['addviews'];
				}
				return $thread['views'];
				break;

            case 'pics' :


                if(!isset($thread['pics']) || empty($thread['pics'])){
                    return array();
                }

                if(count($thread['pics']) == 1){

                    $sizeinfo = $this->picsize[$template]['sigle'];

                }

                if(count($thread['pics']) > 1){
                    $sizeinfo = $this->picsize[$template]['many'];
                }

                foreach($thread['pics'] as $pic_key => $aid){
                    if(isset($thread['need_thumb'][$aid])){
                        $thread['pics'][$pic_key] = minbbs_thumb($aid, $sizeinfo[0], $sizeinfo[1]);

                    }
                }
                return $thread['pics'];       
            default:
                return $thread[$map_key];
                break;
        }
    }



}
