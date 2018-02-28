<?php 
 /* 论坛数据接口配置文件*/ 
 define('ODZ_IMGHASH', '20150104105100');
 // 缓存标识，当需要更新接口返回的图片内容时修改此参数即可 
 define('ODZ_AUTHKEY', '(a,9aB\QTy{1peRH');
// 加密令牌，请勿随意修改，否则会导致客户端用户登录状态丢失需要重新登录 
 define('ODZ_CHARSET', 'utf-8'); //如果论坛编码为gbk，此处填写gbk即可，如果为utf-8，则填写UTF-8，注意大小写,如果编码为utf-8,请进入language目录，将utf8目录下的lang_message.php复制替换到language目录下，替换源文件
// 论坛所用字符集编码 
 define('ODZ_NEWS_BLOCK_ID', 0);
// 论坛首页新闻调用模块ID ，此处可忽略
 define('JPUSH_APPKEY',    '14fc9f39e9b13ce4e9da0f13');
// 论坛首页新闻调用模块ID ，此处可忽略
 define('ODZ_CHANNELID',    '138');
// 团购系统接口地址，此处可忽略
 define('JPUSH_APPSECRET', '921f4b7494785f82fc78f6a5'); ?>