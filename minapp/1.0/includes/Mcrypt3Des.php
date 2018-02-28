<?php

if(!defined('IN_DISCUZ')) {
    exit('Access Denied');
}

/**
 * Mcrypt3Des加解密类
 * @author John
 */
class Mcrypt3Des {

    private $crypt_key = '636d82b614235f1bcfd08969'; // 加密key

    public function __construct($param = array()) {
        if(array_key_exists('key', $param)) {
            $this->crypt_key = $param['key'];
        }
    }

    private function PaddingPKCS7($data)
    {
        $block_size = mcrypt_get_block_size('tripledes', 'cbc');
        $padding_char = $block_size - (strlen($data) % $block_size);
        $data .= str_repeat(chr($padding_char), $padding_char);
        return $data;
    }

    private function UnPaddingPKCS7($text)
    {
        $pad = ord($text{strlen($text) - 1});
        if ($pad > strlen($text)) {
            return false;
        }
        if (strspn($text, chr($pad), strlen($text) - $pad) != $pad) {
            return false;
        }
        return substr($text, 0, -1 * $pad);
    }

    /**
     * 加密
     * @access public
     * @param  $data  要加密的数据
     * @author John
     */
    public function encrypt($data) {

        // 打开加密模式和算法
        $td = mcrypt_module_open(MCRYPT_3DES, '', MCRYPT_MODE_ECB, '');
        // 设置初始化向量的大小
        $size = mcrypt_enc_get_iv_size($td);
        // 创建初始化向量
        $iv = mcrypt_create_iv($size, MCRYPT_RAND);
        // 初始化
        mcrypt_generic_init($td, $this->crypt_key, $iv);
        // 加密
        $data = $this->PaddingPKCS7($data);
        $encode_data = mcrypt_generic($td, $data);
        // 对加密模块进行清理工作，即结束处理 
        mcrypt_generic_deinit($td);
        // 关闭加密模块
        mcrypt_module_close($td);

        return base64_encode($encode_data);
    }

    /**
     * 解密
     * @access public
     * @param  $data  要解密的数据
     * @author John
     */
    public function dencrypt($encode_data) {

        $encode_data = base64_decode($encode_data);
        // 打开解密模式和算法
        $td = mcrypt_module_open(MCRYPT_3DES, '', MCRYPT_MODE_ECB, '');
        // 设置初始化向量大小
        $size = mcrypt_enc_get_iv_size($td);
        // 创建初始化向量
        $iv = mcrypt_create_iv($size, MCRYPT_RAND);
        // 初始化
        mcrypt_generic_init($td, $this->crypt_key, $iv);
        // 解密
        $data = trim(mdecrypt_generic($td, $encode_data));
        $data = $this->UnPaddingPKCS7($data);
        // 对加密模块进行清理工作
        mcrypt_generic_deinit($td);
        // 关闭解密模块
        mcrypt_module_close($td);

        return $data;
    }
}

?>