<?php
// 作者：杨玉奇
// nginx1.14 + php7.2 安装配置参考： https://blog.csdn.net/Ersan_Yi/article/details/82107552
// 运行时错误在： /var/log/nginx
//
//
// 命令行调试： $ php paas_rbac.php debug
// php 命令行交互测试：$ php -a
///////////////////////////////////////////////////////////////////////////////////////////

// require 'vendor/autoload.php';
require_once('./paas_private.php');

use minimum_frame\YLog;

define( 'COOKIE_OVER_TIME', 86400 );         // session 与 cookie 过期时间：1 天过期


// 路由函数表
$route_functions = array(
    'test',
    'login'
);

// 调用主路由函数
comm_frame_main( $route_functions );

///////////////////////////////////////////////////////////////////////////////////////////

function test( $args )
{
    $result = array( 'err' => 0, 'err_msg' => '', 'data' => 'Hello Pass_rbac! you call test.' );
    $result['args'] = $args;
    return $result;
}

function login( $args )
{
    $result = comm_check_parameters( $args, array('email', 'password') );
    if( 0 != $result['err'] )
        return $result;

    $result = __do_login( 'sys_admin', 'id_admin', $args['email'], $args['password'] );

    // 获得权限信息
    $user_privilege = db_get_user_resource_privilege( 'ac_sys_admin_rule', 'id_admin',  $result['user_info']['id_admin']);
    $_SESSION['user_privilege'] = $user_privilege;
    $result['user_privilege'] = $user_privilege;

    return $result;
}




?>