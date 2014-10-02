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
                                            )
                        );

return array_merge($common_config, $private_config);
