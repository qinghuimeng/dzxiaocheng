<?php

class Threadsort_Service{

    private $debug;



    public function __construct(){

    }

    /**
     * 帖子搜索
     * @param  integer $sortid    分类id
     * @param  array $sortvalue 搜索
     * @return array            tids
     */
    public function search($sortid, $sortvalue){
        global $_G;

        loadcache(array('threadsort_option_'.$sortid));
        if(empty($_G['cache']['threadsort_option_'.$sortid])){
            return false;
        }

        $threadsort_option = $_G['cache']['threadsort_option_'.$sortid];
        foreach($sortvalue as $keyid => $value){

            if($value[0] == 'all' || $value[0] == '0' ){

                continue;
            }


            //不存在筛选项
            if(!isset($threadsort_option[$keyid])){
                $this->debug[] = 'keyid error '.$keyid;
                continue;
            }

            //单选值错误过滤
            if($threadsort_option[$keyid]['type'] != 'checkbox' && count($value) > 1){
                $this->debug[] = 'value count error '. $keyid;
                continue;
            }

            $fieldname = $threadsort_option[$keyid]['identifier'];

            //多选
            if($threadsort_option[$keyid]['type'] == 'checkbox'){
                $sql = DB::field($fieldname, '%'.implode('%', $value).'%', 'like');
            }



            //范围
            if($threadsort_option[$keyid]['type'] == 'range') {
                $value = $value[0];

                $value = explode('|', $value);
                if($value[0] == 'd') {
                    $sql = "$fieldname<".intval($value[1]);
                } elseif($value[0] == 'u') {
                    $sql = "$fieldname>".intval($value[1]);
                } else {
                    $sql = "($fieldname BETWEEN ".intval($value[0])." AND ".intval($value[1]).")";
                }
            }

            //单选
            if($threadsort_option[$keyid]['type'] == 'radio'){
                $value = $value[0];
                $sql = DB::field($fieldname, $value);
            }

            //选择
            if($threadsort_option[$keyid]['type'] == 'select'){
                $value = $value[0];
                $keys = $this->get_select_keys($threadsort_option[$keyid]['choices'], $value);
                if(empty($keys)){
                    continue;
                }

                $temp = array();
                $row = '';
                foreach($keys as $row){
                    $temp[] = DB::field($fieldname, $row);
                }

                $sql = '('.implode(' OR ', $temp).')';
            }

            $selectsql .= "AND $sql ";

        }
      // var_dump($selectsql);exit;
        $searchsorttids = C::t('forum_optionvalue')->fetch_all_tid($sortid, "WHERE 1 $selectsql ".($sortfid ? "AND fid='$sortfid'" : ''));

        $searchsorttids[] = 0;
        return $searchsorttids;

    }


    /**
     * select搜索
     * @param  array $choice     表里choic
     * @param  [type] $search_key 搜索key
     * @return [type]             [description]
     */
    private function get_select_keys($choice, $search_key){

        $select = array();

        foreach($choice as $key => $one){

            $temp = explode('.', $key);

            if($temp[0] == $search_key ){
                $select[] = $key;
            }

        }

        return $select;

    }

    /**
     * 筛选数据
     * @param  [type] $threadsorts [description]
     * @return [type]              [description]
     */
    public function get_threadsort($threadsorts){
        global $_G;
        require_once libfile('function/threadsort');

        $templatearray = $sortoptionarray = array();
        foreach($threadsorts['types'] as $stid => $sortname) {
            $temp = array();
            loadcache(array('threadsort_option_'.$stid));

            $temp['data'] = $this->format(quicksearch($_G['cache']['threadsort_option_'.$stid]));

            $temp['name'] = strip_tags($sortname);
            $temp['sortid'] = $stid;

            $sortoptionarray[] = $temp;
        }
        return $sortoptionarray;
    }

    /**
     * 筛选格式化
     * @param  [type] $sort [description]
     * @return [type]       [description]
     */
    public function format($sort){
        $return = array();
        foreach($sort as $keyid => $row){

            if(!in_array($row['type'], array('radio','checkbox','select', 'range'))){
                continue;
            }

            if($row['type'] == 'select'){
                $row['choices'] = $this->format_select($row['choices']);
            }

            $choices = $temp = array();
            $choices[] = array('fieldid' => 'all', 'fieldname' => odz_lang('unlimited'));
            foreach($row['choices'] as $choices_keyid => $value){
                $temp['fieldid']  = $choices_keyid;
                $temp['fieldname'] = $value;
                $choices[] = $temp;
            }

            $temp = array();
            $temp['keyid'] = $keyid;
            $temp['name'] = $row['title'];
            $temp['choices'] = $choices;
            $temp['multiple'] = $row['type'] == 'checkbox' ? '1' : '0';
            $return[] = $temp;
        }

        return $return;
    }

    /**
     * 选择格式化
     * @param  [type] $choices [description]
     * @return [type]          [description]
     */
    private function format_select($choices){
        $return = array();
        foreach($choices as $key => $value){
            if(strpos($key, '.') === false){
                $return[$key] = $value;
            }
        }

        return $return;
    }



    public function get_tid_value($tids, $settings){

        foreach($tids as $sortid => $tids){

            if(empty($settings[$sortid])){
                continue;
            }

            //需要获取的字段
            $fields_optionids = $settings[$sortid]['fields'];

            if(isset($fields_optionids['pics']) && $fields_optionids['pics'] == 0){
                unset($fields_optionids['pics']);
            }

            $tids_sql = implode(',', $tids);
            $optionids_sql = implode(',', $fields_optionids);
            //$field_map = array_flip($fields_optionids);

            $field_map = array();
            foreach($fields_optionids as $fieldname => $optionid){
                $field_map[$optionid][] = $fieldname;
                if($optionid == 0){
                    $field_map['empty'][] = $fieldname;
                }
            }


            $sql = "SELECT tid,value,identifier,unit,v.optionid,type,rules FROM ".DB::table('forum_typeoptionvar')." AS v
                    LEFT JOIN ".DB::table('forum_typeoption')." AS o
                    ON v.optionid = o.optionid
                    WHERE v.tid IN($tids_sql) AND v.optionid IN ($optionids_sql)
                    ";
            $result = DB::fetch_all($sql);


            foreach($result as $row){
                foreach($field_map[$row['optionid']] as $fieldname){
                    $tidvalue[$row['tid']][$fieldname] = strval($this->get_field_value($row['value'], $row['type'], $row['rules'], $row['optionid'], $row['unit']));
                }

                if(isset($field_map['empty'])){
                    foreach($field_map['empty'] as $fieldname){
                        $tidvalue[$row['tid']][$fieldname] = '';
                    }
                }
            }
        }

        return $tidvalue;
    }


    public function get_field_value($value, $type, $rules, $optionid, $unit = ''){
        static $choice = array();

        if(empty($value)){
            return '';
        }


        if($type == 'radio' || $type == 'select'){

            if(empty($rules)){
                return '';
            }

            if(!isset($choice[$optionid])){
                $rules = unserialize($rules);
                $temp  = explode("\n", $rules['choices']);
                $rules = array();
                foreach($temp as $row){
                    list($k, $v) = explode("=", $row);
                    $rules[trim($k)] = trim($v);
                }
                $choice[$optionid] = $rules;
            }

            return $choice[$optionid][$value];
        }

        if($type == 'checkbox'){



            if(empty($rules)){
                return '';
            }


            if(!isset($choice[$optionid])){
                $rules = unserialize($rules);
                $temp  = explode("\n", $rules['choices']);
                $rules = array();
                foreach($temp as $row){
                    list($k, $v) = explode("=", $row);
                    $rules[trim($k)] = trim($v);
                }
                $choice[$optionid] = $rules;
            }

            $arr = explode("\t", $value);
            return $choice[$optionid][$arr[0]];
        }


        if($type == 'image'){
            global $_G;
            $value = dunserialize($value);

            if(empty($value['url'])){
                return array();
            }
            $aid = $value['aid'];
            if($attach = C::t('forum_attachment_n')->fetch('aid:'.$aid, $aid, array(1, -1))) {
                //获取图片宽高
                $attach['url'] = ($attach['remote'] ? $_G['setting']['ftp']['attachurl'] : $_G['setting']['attachurl']).'forum/';
                $attachurl = $attach['url'].$attach['attachment'].($attach['thumb'] ? '.thumb.jpg' : '');

            }
            $attachurl = $_G['discuz_url'].$attachurl;
            return array($attachurl);
        }

        if(!is_array($value) && !empty($unit)) {
            $value .= ' ' . $unit;
        }

        return $value;

    }


}
