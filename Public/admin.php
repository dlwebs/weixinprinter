<?php
header("content-type:text/html;charset=utf-8");
// 应用入口文件

// 检测PHP环境
if (version_compare(PHP_VERSION, '5.3.0', '<')) die('require PHP version > 5.3.0 !');

// 开启调试模式 建议开发阶段开启 部署阶段注释或者设为false
define('APP_DEBUG', true);

// 定义应用目录
define('APP_PATH', '../Application/');

// 定义运行时目录
define('RUNTIME_PATH', '../Runtime/');

// 绑定Home模块到当前入口文件
define('BIND_MODULE', 'Admin');

// 引入ThinkPHP入口文件
require '../ThinkPHP/ThinkPHP.php';