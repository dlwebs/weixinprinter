
DROP TABLE IF EXISTS `wxp_system`;
CREATE TABLE `wxp_system` (
  `system_id` tinyint(4) NOT NULL auto_increment,
  `system_name` varchar(50) NOT NULL COMMENT '系统名称',
  `system_desc` varchar(2000) NOT NULL COMMENT '系统描述',
  `system_subscribe` varchar(200) NOT NULL COMMENT '关注公众号后发送的欢迎消息',
  `system_sendimg` varchar(200) NOT NULL COMMENT '发送资源后的回复消息',
  `system_printimg` varchar(200) NOT NULL COMMENT '打印图片后的回复消息',
  `system_user` varchar(50) NOT NULL COMMENT '系统设置的用户，关联user表user_id字段',
  PRIMARY KEY (`system_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='系统信息表';



DROP TABLE IF EXISTS `wxp_user`;
CREATE TABLE `wxp_user` (
  `id` int(11) NOT NULL auto_increment,
  `user_id` varchar(50) NOT NULL COMMENT '用户ID',
  `user_name` varchar(50) NOT NULL COMMENT '用户名',
  `user_pw` varchar(50) NOT NULL COMMENT '用户密码',
  `user_regdate` datetime NOT NULL COMMENT '用户注册日期',
  `user_status` enum('1','0') NOT NULL COMMENT '用户状态，1是启用，0是停用',
  `user_weixin` varchar(50) NOT NULL COMMENT '普通微信用户Token，用来识别普通微信用户',
  `user_follow` enum('unsubscribe','subscribe') NOT NULL COMMENT '用户关注公众号状态，unsubscribe是未关注，subscribe是已关注',
  PRIMARY KEY (`id`),
  UNIQUE KEY user_id (user_id)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='用户表';

INSERT INTO `wxp_user` VALUES (1, 'admin', '管理员',  '21232f297a57a5a743894a0e4a801fc3', now(), '1', '', 'unsubscribe');



DROP TABLE IF EXISTS `wxp_group`;
CREATE TABLE `wxp_group` (
  `group_id` tinyint(4) NOT NULL auto_increment,
  `group_name` varchar(50) NOT NULL COMMENT '组名称',
  `group_status` enum('1','0') NOT NULL COMMENT '组状态，1是启用，0是停用',
  `group_auth` char(80) NOT NULL COMMENT '组拥有的规则id， 多个规则","隔开',
  PRIMARY KEY (`group_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='用户组表';

INSERT INTO `wxp_group` VALUES (1, '超级管理员', '1', 1);



DROP TABLE IF EXISTS `wxp_usergroup`;
CREATE TABLE `wxp_usergroup` (
  `uid` varchar(50) NOT NULL COMMENT '用户ID，关联user表的user_id字段',
  `gid` tinyint(4) unsigned NOT NULL COMMENT '组ID，关联group表的group_id字段',
  UNIQUE KEY `uid_gid` (`uid`,`gid`),  
  KEY `uid` (`uid`), 
  KEY `gid` (`gid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='用户与组关联表';

INSERT INTO `wxp_usergroup` VALUES ('admin', 1);



DROP TABLE IF EXISTS `wxp_authrule`;
CREATE TABLE `wxp_authrule` (  
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,  
  `name` char(80) NOT NULL DEFAULT '' COMMENT '规则唯一标识',  
  `title` char(20) NOT NULL DEFAULT '' COMMENT '规则中文名称',  
  `type` tinyint(1) NOT NULL DEFAULT '1' COMMENT '',    
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态：为1正常，为0禁用',  
  `condition` char(100) NOT NULL DEFAULT '' COMMENT '规则表达式，为空表示存在就验证，不为空表示按照条件验证',
  PRIMARY KEY (`id`),  
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='权限表';

INSERT INTO `wxp_authrule` VALUES (1, 'all', '所有权限', 1, 1, '');



DROP TABLE IF EXISTS `wxp_resource`;
CREATE TABLE `wxp_resource` (
  `resource_id` int(11) NOT NULL auto_increment,
  `resource_content` varchar(500) NOT NULL COMMENT '资源内容，对于图片来说是图片链接，对于视频来说是视频缩略图的媒体id',
  `resource_mediaid` varchar(100) NOT NULL COMMENT '视频或图片消息媒体id，可以调用多媒体文件下载接口拉取数据',
  `resource_type` enum('1','2') NOT NULL COMMENT '资源类型，1是视频，2是图片',
  `resource_status` enum('1','2','3') NOT NULL COMMENT '资源状态，1是审核中，2是审核通过，3是审核未通过',
  `resource_print` enum('1','2') NOT NULL COMMENT '资源打印状态，1是未打印，2是已打印',
  `resource_date` datetime NOT NULL COMMENT '资源上传日期',
  `resource_checkdate` datetime NOT NULL COMMENT '资源审核通过日期',
  `resource_user` varchar(50) NOT NULL COMMENT '资源上传者，关联user表的user_id字段',
  `resource_checker` varchar(50) NOT NULL COMMENT '资源审核者，关联user表的user_id字段',
  `resource_weixin` varchar(50) NOT NULL COMMENT '资源是哪个公众号的，关联weixin表weixin_token字段',
  `resource_printer` varchar(50) NOT NULL COMMENT '资源的消费码',
  PRIMARY KEY (`resource_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='资源表';



DROP TABLE IF EXISTS `wxp_printer`;
CREATE TABLE `wxp_printer` (
  `printer_id` int(11) NOT NULL auto_increment,
  `printer_name` varchar(50) NOT NULL COMMENT '打印机名称',
  `printer_code` varchar(20) NOT NULL COMMENT '打印机消费码前缀',
  `printer_type` enum('1','2') NOT NULL COMMENT '打印机终端类型，1是横屏，2是竖屏',
  `printer_weixin` varchar(50) NOT NULL COMMENT '公众号帐号，关联weixin表weixin_token字段',
  `printer_template` tinyint (4) unsigned NOT NULL COMMENT '打印机模板，关联template表template_id字段',
  PRIMARY KEY (`printer_id`),
  UNIQUE KEY printer_code (printer_code)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='设备表';



DROP TABLE IF EXISTS `wxp_printertpl`;
CREATE TABLE `wxp_printertpl` (
  `printertpl_id` int(11) NOT NULL auto_increment,
  `printertpl_content` varchar(500) NOT NULL COMMENT '设备内容，video和image时是视频和图片的地址，word时是广告文本',
  `printertpl_type` enum('video','image','word') NOT NULL COMMENT '设备内容类型，video是视频，image是图片，word是广告词',
  `printertpl_num` tinyint (4) unsigned NOT NULL COMMENT '内容位置',
  `printertpl_printer` int(11) unsigned NOT NULL COMMENT '设备ID，关联printer表printer_id字段',
  PRIMARY KEY (`printertpl_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='设备内容表';



DROP TABLE IF EXISTS `wxp_printcode`;
CREATE TABLE `wxp_printcode` (
  `printcode_id` int(11) NOT NULL auto_increment,
  `p_code_number` varchar(50) NOT NULL COMMENT '打印机生成的可供使用的消费码',
  `p_status` enum('0','1') NOT NULL COMMENT '消费码状态，0是未使用，1是已使用',
  PRIMARY KEY (`printcode_id`), 
  KEY `p_code_number` (`p_code_number`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='打印机消费码表';



DROP TABLE IF EXISTS `wxp_weixin`;
CREATE TABLE `wxp_weixin` (
  `weixin_id` smallint(6) NOT NULL auto_increment,
  `weixin_number` varchar(50) NOT NULL COMMENT '公众号原始id',
  `weixin_name` varchar(50) NOT NULL COMMENT '公众号名称',
  `weixin_callbackurl` varchar(200) NOT NULL COMMENT '回调地址',
  `weixin_token` varchar(50) NOT NULL COMMENT 'token',
  `weixin_imgcode` varchar(50) NOT NULL COMMENT '公众号帐号二维码',
  `weixin_appid` varchar(50) NOT NULL COMMENT 'appid',
  `weixin_appsecret` varchar(50) NOT NULL COMMENT 'appsecret',
  `weixin_userid` varchar(50) NOT NULL COMMENT '用户ID，关联wxp_user表user_id字段',
  `weixin_regdate` datetime NOT NULL COMMENT '公众号添加时间',
  PRIMARY KEY (`weixin_id`),
  UNIQUE KEY weixin_token (weixin_token)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='公众号表';



DROP TABLE IF EXISTS `wxp_template`;
CREATE TABLE `wxp_template` (
  `template_id` tinyint (4) NOT NULL auto_increment,
  `template_name` varchar(50) NOT NULL COMMENT '模板显示名称',
  `template_code` varchar(20) NOT NULL COMMENT '模板代码，模板显示名称的拼音',
  `template_pic` varchar(50) NOT NULL COMMENT '模板缩略图',
  `template_image` tinyint (4) unsigned NOT NULL COMMENT '模板拥有图片个数',
  `template_video` tinyint (4) unsigned NOT NULL COMMENT '模板拥有视频个数',
  `template_word` tinyint (4) unsigned NOT NULL COMMENT '模板拥有广告词框个数',
  PRIMARY KEY (`template_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='模板表';