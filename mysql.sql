
DROP TABLE IF EXISTS `wxp_user`;
CREATE TABLE `wxp_user` (
  `id` int(11) NOT NULL auto_increment,
  `user_id` varchar(250) NOT NULL,
  `user_name` varchar(250) NOT NULL,
  `user_pw` varchar(250) NOT NULL,
  `user_status`enum('1','0') NOT NULL,
  `user_type` tinyint(3) unsigned NOT NULL default 2 COMMENT '用户类型，1是后台管理员，2是普通商户',
  PRIMARY KEY (`id`),
  UNIQUE KEY user_id (user_id)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='用户表';

INSERT INTO `wxp_user` VALUES (1, 'admin', '管理员',  '21232f297a57a5a743894a0e4a801fc3', '1', 1);
