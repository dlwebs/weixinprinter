<?php
//公共配置
$common_config = include APP_PATH.'Common/Conf/config.php';

//私有配置
$private_config = array(
                        'LAYOUT_ON' => true,
                        'URL_ROUTER_ON' => true,
                        'URL_CASE_INSENSITIVE' =>true,
                        'URL_ROUTE_RULES' => array(
                                                  'api/:token' => 'Weixin/index',
                                                  'zoom/:uid' => 'Weixin/zoom',
                                                  'printer/:atcode' => 'Index/index',
                                                  )
                        );

return array_merge($common_config, $private_config);
