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
                                                  ),
                        'AUTH_NAME' => array(
                                            'Admin-Auth-list' => '查看权限列表',
                                            'Admin-Auth-refresh' => '刷新权限列表',
                                            'Admin-Index-index' => '进入首页',
                                            'Admin-Index-login' => '登录页面',
                                            'Admin-Index-logout' => '登录操作',
                                            'Admin-Index-dologin' => '注销操作',
                                            'Admin-User-list' => '查看用户列表',
                                            'Admin-User-add' => '添加用户',
                                            'Admin-User-moduser' => '修改用户',
                                            'Admin-User-deluser' => '删除用户',
                                            'Admin-User-save' => '保存用户信息',
                                            )
                        );

return array_merge($common_config, $private_config);
