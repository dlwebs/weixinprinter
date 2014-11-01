<?php
//公共配置
$common_config = include APP_PATH.'Common/Conf/config.php';

//私有配置
$private_config = array(
                        'LAYOUT_ON' => true,
                        'URL_ROUTER_ON' => true,
                        'URL_CASE_INSENSITIVE' =>true,
                        'URL_ROUTE_RULES' => array(
                                                  'moduser/:userid' => 'User/moduser',
                                                  'deluser/:userid' => 'User/deluser',
                                                  'modgroup/:groupid' => 'Group/modgroup',
                                                  'delgroup/:groupid' => 'Group/delgroup',
                                                  'modweixin/:wxid' => 'Weixin/modweixin',
                                                  'delweixin/:wxid' => 'Weixin/delweixin',
                                                  'modprinter/:printerid' => 'Printer/modprinter',
                                                  'delprinter/:printerid' => 'Printer/delprinter',
												  'gettemplatenum/:templateid' => 'Printer/gettemplatenum',
                                                  'detail/:resourceid' => 'Resource/detail',
                                                  'modtpl/:tid' => 'Template/modtpl',
                                                  'deltpl/:tid' => 'Template/deltpl',
                                                  'setting/:userid' => 'System/setting',
                                                  ),
                        'AUTH_NAME' => array(
                                            'Admin-Auth-list' => '查看权限列表',
                                            'Admin-Auth-refresh' => '刷新权限列表',
                                            'Admin-Index-index' => '进入后台首页',
                                            'Admin-Index-login' => '后台登录页面',
                                            'Admin-Index-logout' => '后台登录操作',
                                            'Admin-Index-dologin' => '后台注销操作',
                                            'Admin-User-list' => '查看用户列表',
                                            'Admin-User-add' => '添加用户',
                                            'Admin-User-moduser' => '修改用户',
                                            'Admin-User-deluser' => '删除用户',
                                            'Admin-User-save' => '保存用户信息',
                                            'Admin-Group-list' => '查看组列表',
                                            'Admin-Group-add' => '添加组',
                                            'Admin-Group-modgroup' => '修改组',
                                            'Admin-Group-delgroup' => '删除组',
                                            'Admin-Group-save' => '保存组信息',
                                            'Admin-System-setting' => '系统信息设定',
                                            'Admin-Weixin-list' => '微信公众号列表',
                                            'Admin-Weixin-add' => '添加微信公众号',
                                            'Admin-Weixin-save' => '保存公众号信息',
                                            'Admin-Printer-list' => '设备列表',
                                            'Admin-Printer-add' => '添加设备',
                                            'Admin-Printer-save' => '保存设备',
                                            'Admin-Resource-list' => '已打印列表',
											'Admin-Resource-manage' => '资源管理列表',
											'Admin-Resource-verify' => '资源审核',
                                            'Admin-Printer-modprinter' => '修改打印机',
                                            'Admin-Printer-delprinter' => '删除打印机',
                                            'Admin-Resource-detail' => '资源详情',
                                            'Admin-Weixin-modweixin' => '修改微信',
                                            'Admin-Weixin-delweixin' => '删除微信',
                                            'Admin-Weixin-fans' => '粉丝列表',
                                            'Admin-Template-save' => '保存模板',
                                            'Admin-Template-deltpl' => '删除模板',
                                            'Admin-Template-modtpl' => '修改模板',
                                            'Admin-Template-add' => '添加模板',
                                            'Admin-Template-list' => '模板列表',
                                            'Admin-System-list' => '系统设置列表',
                                            'Admin-Printer-gettemplatenum' => '设备选择模板'
                                            )
                        );

return array_merge($common_config, $private_config);
