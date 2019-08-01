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
    'website_symbol_name_exist',
    'website_info_get',
    'website_add',
    'website_delete',
    'website_modify',
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

    $enterprise_info = $g_mysql->selectOne( 'ac_enterprise', array('symbol_name'), array($args['symbol_name']) );
    if( 1 > count($enterprise_info) )
    {
        $result['err'] = -11;
        $result['err_msg'] = '企业不存在';
        return $result;   
    }
    $_SESSION['id_enterprise'] = $enterprise_info['id_enterprise'];
    setcookie('id_enterprise', $_SESSION['id_enterprise'], time() + COOKIE_OVER_TIME);

    $result = __do_login( LOGIN_TYPE_ENTERPRISE, $args['email'], $args['password'] );
    return $result;
}

function website_symbol_name_exist( $args )
{
    global $g_mysql;
    $result = comm_check_parameters( $args, array('symbol_name') );
    if( 0 != $result['err'] )
        return $result;
    
    $result['symbol_name'] = $args['symbol_name'];
    $result['exist'] = 0;
    $website_info = $g_mysql->selectOne( 'ac_website', array('symbol_name'), array($args['symbol_name']) );
    if( 0 < count($website_info) )
        $result['exist'] = 1;

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

function website_add( $args )
{
    $result = __check_parameters_and_privilege( $args, array('id_website', 'name', 'symbol_name'), 'website_add' );
    if( 0 != $result['err'] )
        return $result;

    $website_info = db_get_some_table_info( 'website', 'symbol_name', $args['symbol_name'], 'id_website' );
    if( 0 < intval($website_info['id_website']) )
    {
        $result['err'] = -102;
        $result['err_msg'] = '符号名已存在，请换一个';
        return $result;
    }

    $result['id_website'] = db_insert_data_ex( 'website', $args, 'id_website' );

    // 创建站点 user 表和 ac_user_rule 表
    if( !db_create_website_user_tables( $result['id_website'] ) )
    {
        @db_delete_data( 'website', 'id_website=?', array($result['id_website']) );

        $result['err'] = -103;
        $result['err_msg'] = '创建相关表失败';
        return $result;
    }

    // 添加操作日志
    db_add_admin_operation_log( $_SESSION['id_user'], $_SESSION['user_info']['id_enterprise'], 
        'website_add', $result['id_website'], 310, '添加站点：symbol_name = ' . $args['symbol_name'] );

    return $result;
}

function website_delete( $args )
{
    $result = __check_parameters_and_privilege( $args, array('id_website'), 'website_delete' );
    if( 0 != $result['err'] )
        return $result;

    // 不存在，直接返回成功
    $website_info = db_get_some_table_info( 'website', 'symbol_name', '', 'id_website', $args['id_website'] );
    if( 1 > int($website_info['id_website']) )
        return $result;

    if( !db_delete_data( 'website', 'id_website=?', array($args['id_website']) ) )
    {
        $result['err'] = -103;
        $result['err_msg'] = '操作失败';
        return $result;       
    }

    // 添加操作日志
    db_add_admin_operation_log( $_SESSION['id_user'], $_SESSION['user_info']['id_enterprise'], 'website_delete', 
        $args['id_website'], 311, '删除站点：symbol_name = '. $website_info['symbol_name'] );
    return $result;    
}

function website_modify( $args )
{
    $result = __check_parameters_and_privilege( $args, array('symbol_name', 'real_name'), 'website_modify' );
    if( 0 != $result['err'] )
        return $result;

    $other_website_info = db_get_other_object_info( 'website', 'symbol_name', $args['symbol_name'], 'id_website', $args['id_website'] );
    if( 0 < int($other_website_info['id_website']) )
    {
        $result['err'] = -102;
        $result['err_msg'] = 'symbol_name 已存在，请换一个';
        return $result;
    }

    if( !db_update_data_ex( 'website', $args, 'id_website' ) )
    {
        $result['err'] = -103;
        $result['err_msg'] = '操作失败';
    }

    // 添加操作日志
    db_add_admin_operation_log( $_SESSION['id_user'], $_SESSION['user_info']['id_enterprise'], 'website_modify', 
        $args['id_website'], 312, '修改站点信息：symbol_name = ' . $args['symbol_name'] );
    return $result;
}




?>