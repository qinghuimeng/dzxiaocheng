<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * 文件上传处理模块
 */
class Upload extends MO_Controller {
	/**
	 * @var 文件上传目录
	*/
	var $upload_dir = 'data/attachments/upload';
	function __construct() {
		parent::__construct();
                $this->load->helper('attachment');
	}
	function wallpaper() {//壁纸上传处理函数
            if (!isset($_FILES['Filedata']) ||$_FILES['Filedata']['error'] !== UPLOAD_ERR_OK){ 
                exit;
            }
            $view_path=$this->upload_dir.'/wallpaper/'.date('Ym');
            if(!file_exists($view_path)){//检查并且创建文件
                    $this->make_dir($view_path);
            }
            $origin_name = $_FILES['Filedata']['name'];
            $uploaded_file=$_FILES['Filedata']['tmp_name'];//必须是Filedata，才能获取file的信息
            $fileext = $this->_get_extension($origin_name);//获取文件扩展名
            $filename = $this->_generate_name($fileext);// 生成随机文件名
            $move_to_file=$view_path.'/'.$filename;
            if (!move_uploaded_file($uploaded_file, $move_to_file)) {
                    // TODO 需进行错误处理
                    exit;
            }
//            @unlink($_FILES['Filedata']['tmp_name']);
            $thumb_url = $view_path.'/'.$filename.'.406.'.$fileext;
            $thumbname=$filename.'.406.'.$fileext;
            $ret = create_thumb($move_to_file, $thumb_url, 406, 720, false, true);
            if ($ret['code'] != 0) {
                    $thumbname = '';
            }
            $result = array(
                    'img_path' => '/'.$view_path.'/'.$filename,
                    'img_name' => date('Ym').'/'.$filename,
                    'img_thumb' => date('Ym').'/'.$thumbname
            );
            echo json_encode($result);
	}
       
        function cp_user() {//开发者上传处理函数
            if (!isset($_FILES['Filedata']) ||$_FILES['Filedata']['error'] !== UPLOAD_ERR_OK){ 
                exit;
            }
            $view_path=$this->upload_dir.'/cp_user/'.date('Ym');
            if(!file_exists($view_path)){//检查并且创建文件
                    $this->make_dir($view_path);
            }
            $origin_name = $_FILES['Filedata']['name'];
            $uploaded_file=$_FILES['Filedata']['tmp_name'];//必须是Filedata，才能获取file的信息
            $fileext = $this->_get_extension($origin_name);//获取文件扩展名
            $filename = $this->_generate_name($fileext);// 生成随机文件名
            $move_to_file=$view_path.'/'.$filename;
            if (!move_uploaded_file($uploaded_file, $move_to_file)) {
                    // TODO 需进行错误处理
                    exit;
            }
            $result = array(
                    'img_path' => '/'.$view_path.'/'.$filename,
                    'img_name' => date('Ym').'/'.$filename,
            );
            echo json_encode($result);
	}
        function app_product() {//应用图上传处理函数
            if (!isset($_FILES['Filedata']) ||$_FILES['Filedata']['error'] !== UPLOAD_ERR_OK){ 
                exit;
            }
            $view_path=$this->upload_dir.'/app_product/'.date('Ym');
            if(!file_exists($view_path)){//检查并且创建文件
                    $this->make_dir($view_path);
            }
            $origin_name = $_FILES['Filedata']['name'];
            $uploaded_file=$_FILES['Filedata']['tmp_name'];//必须是Filedata，才能获取file的信息
//            $fileext = $this->_get_extension($origin_name);//获取文件扩展名
            $fileext='jpg';
            $filename = $this->_generate_name($fileext);// 生成随机文件名
            $move_to_file=$view_path.'/'.$filename;
            if (!move_uploaded_file($uploaded_file, $move_to_file)) {
                    // TODO 需进行错误处理
                    exit;
            }
            header('Content-Type:image/jpeg'); 
            list($width,$height,$type)=getimagesize($move_to_file); 
            $image_wp=imagecreatetruecolor($width, $height); 
            imagejpeg($image_wp,$uploaded_file,80);// imagejpeg()  jpeg格式图像
            imagedestroy($image_wp);
            $result = array(
                    'img_path' => '/'.$view_path.'/'.$filename,
                    'img_name' => date('Ym').'/'.$filename,
                    'img_thumb' => date('Ym').'/'.$filename
            );
            echo json_encode($result);
	}
        function app_product_apk() {//上传应用
           sleep(30); 
	   if (!isset($_FILES['Filedata']) ||$_FILES['Filedata']['error'] !== UPLOAD_ERR_OK){ 
                exit;
            }
            $view_path=$this->upload_dir.'/app_product_apk/'.date('Ym');
            if(!file_exists($view_path)){//检查并且创建文件
                    $this->make_dir($view_path);
            }
            $origin_name = $_FILES['Filedata']['name'];
            $uploaded_file=$_FILES['Filedata']['tmp_name'];//必须是Filedata，才能获取file的信息
            $fileext = $this->_get_extension($origin_name);//获取文件扩展名
            $filename = $this->_generate_name($fileext);// 生成随机文件名
            $move_to_file=$view_path.'/'.$filename;
            if (!move_uploaded_file($uploaded_file, $move_to_file)) {
                    // TODO 需进行错误处理
                    exit;
            }
            $result = array(
                    'img_path' => '/'.$view_path.'/'.$filename,
                    'apk_name' => date('Ym').'/'.$filename,
                    'apk_path' => base_url().$move_to_file,
                    'name'=>$filename,
            );
            echo json_encode($result);
	}
        
        
        function app_product_game_apk() {//上传应用
            if (!isset($_FILES['Filedata']) ||$_FILES['Filedata']['error'] !== UPLOAD_ERR_OK){ 
                exit;
            }
            $name = explode('_',$_FILES['Filedata']['name']);
            $name = $name[0];
            if(empty($name)){exit;}
            $view_path=$this->upload_dir.'/app_product_game_apk/'.$name;
            if(!file_exists($view_path)){//检查并且创建文件
                    $this->make_dir($view_path);
            }
            $origin_name = $_FILES['Filedata']['name'];
            $uploaded_file=$_FILES['Filedata']['tmp_name'];//必须是Filedata，才能获取file的信息
            //$fileext = $this->_get_extension($origin_name);//获取文件扩展名
            //$filename = $this->_generate_name($fileext);// 生成随机文件名
            $move_to_file=$view_path.'/'.$origin_name;
            if (!move_uploaded_file($uploaded_file, $move_to_file)) {
                    // TODO 需进行错误处理
                    exit;
            }
            $result = array(
                    'img_path' => '/'.$view_path.'/'.$origin_name,
                    'game_apk_name' => $name,
                    'game_apk_path' => $name,
            );
            echo json_encode($result);
	}
        
        
        function app_product_logo() {//应用图上传处理函数
            if (!isset($_FILES['Filedata']) ||$_FILES['Filedata']['error'] !== UPLOAD_ERR_OK){ 
                exit;
            }
            $view_path=$this->upload_dir.'/app_product/'.date('Ym');
            if(!file_exists($view_path)){//检查并且创建文件
                    $this->make_dir($view_path);
            }
            $origin_name = $_FILES['Filedata']['name'];
            $uploaded_file=$_FILES['Filedata']['tmp_name'];//必须是Filedata，才能获取file的信息
//            $fileext = $this->_get_extension($origin_name);//获取文件扩展名
            $fileext='jpg';
            $filename = $this->_generate_name($fileext);// 生成随机文件名
            $move_to_file=$view_path.'/'.$filename;
            if (!move_uploaded_file($uploaded_file, $move_to_file)) {
                    // TODO 需进行错误处理
                    exit;
            }
            header('Content-Type:image/jpeg'); 
            list($width,$height,$type)=getimagesize($move_to_file); 
            $image_wp=imagecreatetruecolor($width, $height); 
            imagejpeg($image_wp,$uploaded_file,80);// imagejpeg()  jpeg格式图像
            imagedestroy($image_wp);
            $result = array(
                    'img_path' => '/'.$view_path.'/'.$filename,
                    'img_name' => date('Ym').'/'.$filename,
            );
            echo json_encode($result);
	}
        
        function app_topic() {//专题上传处理函数
            if (!isset($_FILES['Filedata']) ||$_FILES['Filedata']['error'] !== UPLOAD_ERR_OK){ 
                exit;
            }
            $view_path=$this->upload_dir.'/app_topic/'.date('Ym');
            if(!file_exists($view_path)){//检查并且创建文件
                    $this->make_dir($view_path);
            }
            $origin_name = $_FILES['Filedata']['name'];
            $uploaded_file=$_FILES['Filedata']['tmp_name'];//必须是Filedata，才能获取file的信息
            $fileext = $this->_get_extension($origin_name);//获取文件扩展名
            $filename = $this->_generate_name($fileext);// 生成随机文件名
            $move_to_file=$view_path.'/'.$filename;
            if (!move_uploaded_file($uploaded_file, $move_to_file)) {
                    // TODO 需进行错误处理
                    exit;
            }
            $result = array(
                    'img_path' => '/'.$view_path.'/'.$filename,
                    'img_name' => date('Ym').'/'.$filename,
            );
            echo json_encode($result);
	}
        function care_site() {//常用网址logo上传处理函数
            if (!isset($_FILES['Filedata']) ||$_FILES['Filedata']['error'] !== UPLOAD_ERR_OK){ 
                exit;
            }
            $view_path=$this->upload_dir.'/care_site/'.date('Ym');
            if(!file_exists($view_path)){//检查并且创建文件
                    $this->make_dir($view_path);
            }
            $origin_name = $_FILES['Filedata']['name'];
            $uploaded_file=$_FILES['Filedata']['tmp_name'];//必须是Filedata，才能获取file的信息
            $fileext = $this->_get_extension($origin_name);//获取文件扩展名
            $filename = $this->_generate_name($fileext);// 生成随机文件名
            $move_to_file=$view_path.'/'.$filename;
            if (!move_uploaded_file($uploaded_file, $move_to_file)) {
                    // TODO 需进行错误处理
                    exit;
            }
            $result = array(
                    'img_path' => '/'.$view_path.'/'.$filename,
                    'img_name' => date('Ym').'/'.$filename,
            );
            echo json_encode($result);
	}
        function advert() {//广告上传处理函数
            if (!isset($_FILES['Filedata']) ||$_FILES['Filedata']['error'] !== UPLOAD_ERR_OK){ 
                exit;
            }
            $view_path=$this->upload_dir.'/advert/'.date('Ym');
            if(!file_exists($view_path)){//检查并且创建文件
                    $this->make_dir($view_path);
            }
            $origin_name = $_FILES['Filedata']['name'];
            $uploaded_file=$_FILES['Filedata']['tmp_name'];//必须是Filedata，才能获取file的信息

//            $fileext = $this->_get_extension($origin_name);//获取文件扩展名
            $fileext='jpg';
            $filename = $this->_generate_name($fileext);// 生成随机文件名
            $move_to_file=$view_path.'/'.$filename;
            if (!move_uploaded_file($uploaded_file, $move_to_file)) {
                    // TODO 需进行错误处理
                    exit;
            }
            header('Content-Type:image/jpeg'); 
            list($width,$height,$type)=getimagesize($move_to_file); 
            $image_wp=imagecreatetruecolor($width, $height); 
            imagejpeg($image_wp,$uploaded_file,80);// imagejpeg()  jpeg格式图像
            imagedestroy($image_wp);
            
            $result = array(
                   'img_path' => '/'.$view_path.'/'.$filename,
                    'img_name' => date('Ym').'/'.$filename,
            );
            echo json_encode($result);
	}
         function app_slide() {//游戏中心焦点图上传处理函数
            if (!isset($_FILES['Filedata']) ||$_FILES['Filedata']['error'] !== UPLOAD_ERR_OK){ 
                exit;
            }
            $view_path=$this->upload_dir.'/app_slide/'.date('Ym');
            if(!file_exists($view_path)){//检查并且创建文件
                    $this->make_dir($view_path);
            }
            $origin_name = $_FILES['Filedata']['name'];
            $uploaded_file=$_FILES['Filedata']['tmp_name'];//必须是Filedata，才能获取file的信息
            $fileext = $this->_get_extension($origin_name);//获取文件扩展名
            $filename = $this->_generate_name($fileext);// 生成随机文件名
            $move_to_file=$view_path.'/'.$filename;
            if (!move_uploaded_file($uploaded_file, $move_to_file)) {
                    // TODO 需进行错误处理
                    exit;
            }
            $result = array(
                    'img_path' => '/'.$view_path.'/'.$filename,
                    'img_name' => date('Ym').'/'.$filename,
            );
            echo json_encode($result);
	}
        function news_slide() {//新闻中心焦点图上传处理函数
            if (!isset($_FILES['Filedata']) ||$_FILES['Filedata']['error'] !== UPLOAD_ERR_OK){ 
                exit;
            }
            $view_path=$this->upload_dir.'/news_slide/'.date('Ym');
            if(!file_exists($view_path)){//检查并且创建文件
                    $this->make_dir($view_path);
            }
            $origin_name = $_FILES['Filedata']['name'];
            $uploaded_file=$_FILES['Filedata']['tmp_name'];//必须是Filedata，才能获取file的信息
//            $fileext = $this->_get_extension($origin_name);//获取文件扩展名
            $fileext='jpg';
            $filename = $this->_generate_name($fileext);// 生成随机文件名
            $move_to_file=$view_path.'/'.$filename;
            if (!move_uploaded_file($uploaded_file, $move_to_file)) {
                    // TODO 需进行错误处理
                    exit;
            }
            header('Content-Type:image/jpeg'); 
            list($width,$height,$type)=getimagesize($move_to_file); 
            $image_wp=imagecreatetruecolor($width, $height); 
            imagejpeg($image_wp,$uploaded_file,80);// imagejpeg()  jpeg格式图像
            imagedestroy($image_wp);
            $result = array(
                    'img_path' => '/'.$view_path.'/'.$filename,
                    'img_name' => date('Ym').'/'.$filename,
            );
            echo json_encode($result);
	}
	function ringtone() {//铃声上传处理函数
            if (!isset($_FILES['Filedata']) ||$_FILES['Filedata']['error'] !== UPLOAD_ERR_OK){ 
                exit;
            }
            $view_path=$this->upload_dir.'/ringtone/'.date('Ym');
            if(!file_exists($view_path)){//检查并且创建文件
                    $this->make_dir($view_path);
            }
            $origin_name = $_FILES['Filedata']['name'];
            $uploaded_file=$_FILES['Filedata']['tmp_name'];//必须是Filedata，才能获取file的信息
            $fileext = $this->_get_extension($origin_name);//获取文件扩展名
            $filename = $this->_generate_name($fileext);// 生成随机文件名
            $move_to_file=$view_path.'/'.$filename;
            if (!move_uploaded_file($uploaded_file, $move_to_file)) {
                    // TODO 需进行错误处理
                    exit;
            }
            
            $result = array(
//                    'img_path' => '/'.$view_path.'/'.$filename,
                    'img_path' => date('Ym').'/'.$filename,
            );
            echo json_encode($result);
	}
        function article() {//文章上传处理函数
            if (!isset($_FILES['Filedata']) ||$_FILES['Filedata']['error'] !== UPLOAD_ERR_OK){ 
                exit;
            }
            $view_path=$this->upload_dir.'/article/'.date('Ym');
            if(!file_exists($view_path)){//检查并且创建文件
                    $this->make_dir($view_path);
            }
            $origin_name = $_FILES['Filedata']['name'];
            $uploaded_file=$_FILES['Filedata']['tmp_name'];//必须是Filedata，才能获取file的信息
//            $fileext = $this->_get_extension($origin_name);//获取文件扩展名
            $fileext='jpg';
            $filename = $this->_generate_name($fileext);// 生成随机文件名
            $move_to_file=$view_path.'/'.$filename;
            if (!move_uploaded_file($uploaded_file, $move_to_file)) {
                    // TODO 需进行错误处理
                    exit;
            }
            header('Content-Type:image/jpeg'); 
            list($width,$height,$type)=getimagesize($move_to_file); 
            $image_wp=imagecreatetruecolor($width, $height); 
            imagejpeg($image_wp,$uploaded_file,80);// imagejpeg()  jpeg格式图像
            imagedestroy($image_wp);
//            @unlink($_FILES['Filedata']['tmp_name']);
            $thumb_url = $view_path.'/'.$filename.'.1116.'.$fileext;
            $thumbname=$filename.'.1116.'.$fileext;
            $ret = create_thumb($move_to_file, $thumb_url, 1116, 480);
            if ($ret['code'] != 0) {
                    $thumbname = '';
            }
            $result = array(
                    'img_path' => '/'.$view_path.'/'.$filename,
                    'img_name' => date('Ym').'/'.$filename,
                    'img_thumb' => date('Ym').'/'.$thumbname
            );
            echo json_encode($result);
	 }
         function news() {//新闻上传处理函数
            if (!isset($_FILES['Filedata']) ||$_FILES['Filedata']['error'] !== UPLOAD_ERR_OK){ 
                exit;
            }
            $view_path=$this->upload_dir.'/news/'.date('Ym');
            if(!file_exists($view_path)){//检查并且创建文件
                    $this->make_dir($view_path);
            }
            $origin_name = $_FILES['Filedata']['name'];
            $uploaded_file=$_FILES['Filedata']['tmp_name'];//必须是Filedata，才能获取file的信息
//            $fileext = $this->_get_extension($origin_name);//获取文件扩展名
            $fileext='jpg';
            $filename = $this->_generate_name($fileext);// 生成随机文件名
            $move_to_file=$view_path.'/'.$filename;
            if (!move_uploaded_file($uploaded_file, $move_to_file)) {
                    // TODO 需进行错误处理
                    exit;
            }
            header('Content-Type:image/jpeg'); 
            list($width,$height,$type)=getimagesize($move_to_file); 
            $image_wp=imagecreatetruecolor($width, $height); 
            imagejpeg($image_wp,$uploaded_file,80);// imagejpeg()  jpeg格式图像
            imagedestroy($image_wp);
//            @unlink($_FILES['Filedata']['tmp_name']);
            $thumb_url = $view_path.'/'.$filename.'.374.'.$fileext;
            $thumbname=$filename.'.374.'.$fileext;
            $ret = create_thumb($move_to_file, $thumb_url, 374, 280);
            if ($ret['code'] != 0) {
                    $thumbname = '';
            }
            $result = array(
                    'img_path' => '/'.$view_path.'/'.$filename,
                    'img_name' => date('Ym').'/'.$filename,
                    'img_thumb' => date('Ym').'/'.$thumbname
            );
            echo json_encode($result);
	}
	/**
	 * 生成随机文件名
	 * @param string $fileext 文件扩展名
	 * @return string
	 */
	function _generate_name($fileext) {
		@date_default_timezone_set('Asia/Chongqing');
		return date('YmdHis').rand(1,999).'.'.$fileext;
	}
	/**
	 * 获取文件扩展名
	 * @param string $filename 文件名
	 * @return string
	 */
	function _get_extension($filename) {
		return strtolower(end(explode('.', $filename)));
	}
}

?>
