

CREATE TABLE IF NOT EXISTS `pre_minbbs_invite_log` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `uid` int(10) NOT NULL,
  `invite_uid` int(10) NOT NULL,
  `device_type` tinyint(1) NOT NULL,
  `udid` varchar(30) NOT NULL,
  `type` tinyint(1) NOT NULL,
  `create_time` int(10) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
CREATE TABLE IF NOT EXISTS `pre_minbbs_invite_comments` (
  `uid` int(10) NOT NULL,
  `username` varchar(30) NOT NULL,
  `content` varchar(255) NOT NULL,
  `score` int(10) NOT NULL,
  `dateline` int(10) NOT NULL,
  PRIMARY KEY (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `pre_minbbs_invite` (
  `uid` int(10) unsigned NOT NULL,
  `username` varchar(30) NOT NULL,
  `code` char(8) NOT NULL,
  `invite_num` int(11) NOT NULL,
  `score` int(11) NOT NULL,
  `create_time` int(10) NOT NULL,
  PRIMARY KEY (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE `pre_home_follow_friend` (
  `uid` mediumint(8) UNSIGNED NOT NULL DEFAULT '0',
  `fuid` mediumint(8) UNSIGNED NOT NULL DEFAULT '0',
  `fusername` varchar(15) NOT NULL DEFAULT '',
  `dateline` int(10) UNSIGNED NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=gbk;

ALTER TABLE `pre_home_follow_friend`
  ADD PRIMARY KEY (`uid`,`fuid`),
  ADD KEY `fuid` (`fuid`),
  ADD KEY `uid` (`uid`,`dateline`);

CREATE TABLE `pre_minbbs_member_device` (
  `id` int(10) NOT NULL DEFAULT '0',
  `devicetoken` varchar(150) DEFAULT NULL,
  `platform` varchar(150) DEFAULT NULL,
  `status` int(2) DEFAULT NULL,
  `uid` int(10) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=gbk;

ALTER TABLE `pre_minbbs_member_device`
  ADD PRIMARY KEY (`id`);


CREATE TABLE IF NOT EXISTS `pre_minbbs_card_setting` (
  `key` varchar(30) NOT NULL,
  `value` text NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


insert into `pre_minbbs_card_setting` (`key`, `value`) values('biu_block_name','');
insert into `pre_minbbs_card_setting` (`key`, `value`) values('biu_data_source','');
insert into `pre_minbbs_card_setting` (`key`, `value`) values('biu_header_name','');
insert into `pre_minbbs_card_setting` (`key`, `value`) values('biu_placard','');
insert into `pre_minbbs_card_setting` (`key`, `value`) values('biu_show_rule',0);
insert into `pre_minbbs_card_setting` (`key`, `value`) values('biu_switch',0);
insert into `pre_minbbs_card_setting` (`key`, `value`) values('color_switch','white');
insert into `pre_minbbs_card_setting` (`key`, `value`) values('community_block_name','');
insert into `pre_minbbs_card_setting` (`key`, `value`) values('community_header_name','');
insert into `pre_minbbs_card_setting` (`key`, `value`) values('groupbuy_address','');
insert into `pre_minbbs_card_setting` (`key`, `value`) values('groupbuy_switch',0);
insert into `pre_minbbs_card_setting` (`key`, `value`) values('index_block_id','');
insert into `pre_minbbs_card_setting` (`key`, `value`) values('index_block_name','');
insert into `pre_minbbs_card_setting` (`key`, `value`) values('index_header_name','');
insert into `pre_minbbs_card_setting` (`key`, `value`) values('index_hot_block','0');
insert into `pre_minbbs_card_setting` (`key`, `value`) values('index_hot_news','');
insert into `pre_minbbs_card_setting` (`key`, `value`) values('index_last_news','0');
insert into `pre_minbbs_card_setting` (`key`, `value`) values('integral_article_share','');
insert into `pre_minbbs_card_setting` (`key`, `value`) values('integral_invite_code','');
insert into `pre_minbbs_card_setting` (`key`, `value`) values('integral_invite_switch','');
insert into `pre_minbbs_card_setting` (`key`, `value`) values('integral_share_switch',0);
insert into `pre_minbbs_card_setting` (`key`, `value`) values('nick_register','1');
insert into `pre_minbbs_card_setting` (`key`, `value`) values('posts_show_type','1');
insert into `pre_minbbs_card_setting` (`key`, `value`) values('qq_login','0');
insert into `pre_minbbs_card_setting` (`key`, `value`) values('recomm_forum','a:0:{}');
insert into `pre_minbbs_card_setting` (`key`, `value`) values('card_share_link','');
insert into `pre_minbbs_card_setting` (`key`, `value`) values('card_share_text','');
insert into `pre_minbbs_card_setting` (`key`, `value`) values('reward_score_type',0);
insert into `pre_minbbs_card_setting` (`key`, `value`) values('reward_switch',0);
insert into `pre_minbbs_card_setting` (`key`, `value`) values('tel_register','1');
insert into `pre_minbbs_card_setting` (`key`, `value`) values('wexin_login','0');
insert into `pre_minbbs_card_setting` (`key`, `value`) values('reward_score1','');
insert into `pre_minbbs_card_setting` (`key`, `value`) values('reward_score2','');
insert into `pre_minbbs_card_setting` (`key`, `value`) values('reward_score3','');
insert into `pre_minbbs_card_setting` (`key`, `value`) values('reward_score4','');
insert into `pre_minbbs_card_setting` (`key`, `value`) values('disclaimer','');
insert into `pre_minbbs_card_setting` (`key`, `value`) values('about_us','');
insert into `pre_minbbs_card_setting` (`key`, `value`) values('invite_rule','');
insert into `pre_minbbs_card_setting` (`key`, `value`) values('invite_user_num','1');
insert into `pre_minbbs_card_setting` (`key`, `value`) values('invite_self_num','1');
insert into `pre_minbbs_card_setting` (`key`, `value`) values('invite_open','1');
insert into `pre_minbbs_card_setting` (`key`, `value`) values('invite_comment_open','1');
insert into `pre_minbbs_card_setting` (`key`, `value`) values('invite_comments','');
insert into `pre_minbbs_card_setting` (`key`, `value`) values('invite_score_type',1);



CREATE TABLE `pre_minbbs_blackfriend` (
`uid` int(11) unsigned NOT NULL COMMENT '用户ID',
`bid` int(11) unsigned NOT NULL COMMENT '黑名单用户uid',
`dateline` int(11) unsigned NOT NULL COMMENT '时间戳'
) ENGINE=MyISAM;

CREATE TABLE `pre_forum_devicetype` (
`pid` int(11) NOT NULL,
`devicetype` tinyint(1) NOT NULL,
KEY `pid` (`pid`,`devicetype`) 
) ENGINE=MyISAM;
CREATE TABLE `pre_home_praise_total` (
`id` int(11) NOT NULL AUTO_INCREMENT,
`tid` int(11) NOT NULL,
`idtype` varchar(100) NOT NULL,
`num` int(11) NOT NULL DEFAULT '0' COMMENT '点赞总数',
PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=3;


CREATE TABLE `pre_minbbs_push_message` (
`id` int(11) NOT NULL AUTO_INCREMENT,
`tid` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '贴子id',
`name` varchar(100) NOT NULL,
`message` varchar(255) NOT NULL,
`isprise` mediumint(1) NOT NULL DEFAULT '0' COMMENT '0是否1是有(是否点赞）',
`time` int(11) NOT NULL,
`typename` char(20) NOT NULL DEFAULT '0' COMMENT '表示是贴子详情',
`types` varchar(20) NOT NULL COMMENT 'thread是指贴子详情，biuthread微分享详情',
`uid` int(11) NOT NULL DEFAULT '0' COMMENT '用户id',
`status` mediumint(1) NOT NULL DEFAULT '0' COMMENT '0是未读1是已读（状态）',
PRIMARY KEY (`id`),
KEY `uid` (`uid`) USING BTREE,
KEY `id` (`id`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=97 DEFAULT CHARSET=gbk;

CREATE TABLE `pre_minbbs_member_coordinate`(
`id` int(11) NOT NULL AUTO_INCREMENT,
`uid` int(11) NOT NULL,
`latitude` varchar(10) NOT NULL DEFAULT '' COMMENT '维度',
`longitude` varchar(10) NOT NULL DEFAULT '' COMMENT '经度',
`datetime` int(10) NOT NULL COMMENT '最后坐标时间',
PRIMARY KEY (`id`),
KEY `email` (`latitude`)
) ENGINE=MyISAM AUTO_INCREMENT=39;

CREATE TABLE `pre_minbbs_member_focus`(
`id` int(11) NOT NULL AUTO_INCREMENT,
`uid` int(11) NOT NULL COMMENT '用户id',
`fid` int(11) NOT NULL COMMENT '版块id',
PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=272;

CREATE TABLE `pre_minbbs_mobile` (
`uid` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '用户ID',
`mobile` varchar(30) NOT NULL COMMENT '手机号码',
`dateline` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '绑定日期',
PRIMARY KEY (`uid`)
) ENGINE=MyISAM;

CREATE TABLE `pre_minbbs_mobile_verify` (
`mobile` varchar(30) NOT NULL COMMENT '手机号码',
`code` varchar(10) NOT NULL COMMENT '验证码',
`tries` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '验证码生成次数',
`dateline` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '验证码生成日期',
PRIMARY KEY (`mobile`)
) ENGINE=MyISAM;

CREATE TABLE `pre_minbbs_connect` (
`id` int(11) NOT NULL AUTO_INCREMENT,
`uid` int(11) NOT NULL,
`openid` char(40) NOT NULL DEFAULT '',
`type` varchar(10) NOT NULL,
PRIMARY KEY (`id`),
KEY `email` (`openid`)
) ENGINE=MyISAM AUTO_INCREMENT=22;

CREATE TABLE `pre_home_praise` (
`praid` int(10) unsigned NOT NULL AUTO_INCREMENT,
`uid` int(10) unsigned NOT NULL DEFAULT '0',
`id` int(10) unsigned NOT NULL DEFAULT '0',
`idtype` varchar(255) NOT NULL DEFAULT '',
`spaceuid` int(10) unsigned NOT NULL DEFAULT '0',
`title` varchar(255) NOT NULL DEFAULT '',
`dateline` int(10) unsigned NOT NULL DEFAULT '0',
PRIMARY KEY (`praid`),
KEY `idtype` (`id`,`idtype`),
KEY `uid` (`uid`,`idtype`,`dateline`)
) ENGINE=MyISAM AUTO_INCREMENT=602;

 CREATE TABLE `pre_minbbs_logindays` (
  `uid` mediumint(8) NOT NULL,
  `login_days` mediumint(8) NOT NULL,
  `last_logintime` int(10) NOT NULL,
  PRIMARY KEY (`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=gbk;

