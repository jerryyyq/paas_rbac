<?php
// 作者：杨玉奇
// nginx1.14 + php7.2 安装配置参考： https://blog.csdn.net/Ersan_Yi/article/details/82107552
// 运行时错误在： /var/log/nginx
//
//
// 命令行调试： $ php paas_rbac.php debug
// php 命令行交互测试：$ php -a
// require 'vendor/autoload.php';

//require_once('./yyq_frame.php');
//require_once('./yyq_frame_config.php');
require_once('./paas_rbac_db.php');

define( 'COOKIE_OVER_TIME', 86400 );         // session 与 cookie 过期时间：1 天过期








?>