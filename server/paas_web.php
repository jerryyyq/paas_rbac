<?php
// 作者：杨玉奇
// nginx1.14 + php7.2 安装配置参考： https://blog.csdn.net/Ersan_Yi/article/details/82107552
// 运行时错误在： /var/log/nginx
//
//
// 命令行调试： $ php paas_rbac.php debug
// php 命令行交互测试：$ php -a
// require 'vendor/autoload.php';
require_once('./paas_private.php');
require_once('./paas_common_function.php');
use minimum_frame\YLog;

// 路由函数表
$route_functions = array_merge($common_route_functions, array(
    'website_all_id_get',
    'website_info_get',

));

// 调用主路由函数
comm_frame_main( $route_functions );

////////////////////////////////////////// 路由接口函数实现 /////////////////////////////////////////////////

//////// 当前用户登录及权限检查 ////////

function login( $args )
{
    $result = comm_check_parameters( $args, array('symbol_name', 'email', 'password') );
    if( 0 != $result['err'] )
        return $result;

    $website_info = $g_mysql->selectOne( 'ac_website', array('symbol_name'), array($args['symbol_name']) );
    if( 1 > count($website_info) )
    {
        $result['err'] = -101;
        $result['err_msg'] = '网站符号名不存在';
        return $result;   
    }
    $_SESSION['id_website'] = $website_info['id_website'];
    setcookie('id_website', $_SESSION['id_website'], time() + COOKIE_OVER_TIME);

    $result = __do_login( RESOURCE_TYPE_WEB, $args['email'], $args['password'] );
    return $result;
}

function website_all_id_get( $args )
{
    $result = __check_parameters_and_resource_privilege( $args, array(), 0, 'website_read' );
    if( 0 != $result['err'] )
        return $result;

    $result['website_id_list'] = __get_privilege_resource_list( 'website_read', RESOURCE_TYPE_WEB );
    return $result;
}

function website_info_get( $args )
{
    global $g_mysql;
    $result = __check_parameters_and_resource_privilege( $args, array('id_website'), $args['id_website'], 'website_read' );
    if( 0 != $result['err'] )
        return $result;

    $result['website_info'] = $g_mysql->selectOne( 'ac_website', array('id_website'), array($args['id_website']) );
    return $result;
}



?>