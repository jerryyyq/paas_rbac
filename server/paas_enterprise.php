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

    'enterprise_admin_all_get',
    'enterprise_admin_info_get',
    'enterprise_admin_add',
    'enterprise_admin_delete',
    'enterprise_admin_modify',

    'admin_resource_rule_all_get',
    'admin_resource_rule_info_get',
    'admin_resource_rule_add',
    'admin_resource_rule_delete',

  
    
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
        $result['err'] = -101;
        $result['err_msg'] = '企业符号名不存在';
        return $result;   
    }
    $_SESSION['id_enterprise'] = $enterprise_info['id_enterprise'];
    setcookie('id_enterprise', $_SESSION['id_enterprise'], time() + COOKIE_OVER_TIME);

    $result = __do_login( RESOURCE_TYPE_ENTERPRISE, $args['email'], $args['password'] );
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
    global $g_mysql;
    $result = __check_parameters_and_resource_privilege( $args, array('id_enterprise', 'name', 'symbol_name'), 0, 'website_add' );
    if( 0 != $result['err'] )
        return $result;

    if( $args['id_enterprise'] != $_SESSION['id_enterprise'] )
    {
        $result['err'] = -105;
        $result['err_msg'] = '企业 id 与登录企业不匹配';
        return $result;
    }

    $website_info = $g_mysql->selectOne( 'ac_website', array('symbol_name'), array($args['symbol_name']) );
    if( 0 < count($website_info) )
    {
        $result['err'] = -102;
        $result['err_msg'] = '符号名已存在，请换一个';
        return $result;
    }

    $result['id_website'] = $g_mysql->insertDataEx( 'ac_website', $args, 'id_website' );

    // 添加操作日志
    db_add_enterprise_operation_log( $_SESSION['id_user'], $_SESSION['id_enterprise'], 
        'website_add', $result['id_website'], 310, '添加站点：symbol_name = ' . $args['symbol_name'] );

    return $result;
}

function website_delete( $args )
{
    global $g_mysql;
    $result = __check_parameters_and_resource_privilege( $args, array('id_website'), $args['id_website'], 'website_delete' );
    if( 0 != $result['err'] )
        return $result;

    // 不存在，直接返回成功
    $website_info = $g_mysql->selectOne( 'ac_website', array('id_website'), array($args['id_website']) );
    if( 1 > count($website_info) )
        return $result;

    if( $website_info['id_enterprise'] != $_SESSION['id_enterprise'] )
    {
        $result['err'] = -105;
        $result['err_msg'] = '企业 id 与登录企业不匹配';
        return $result;
    }

    if( !$g_mysql->deleteData( 'ac_website', 'id_website=?', array($args['id_website']) ) )
    {
        $result['err'] = -103;
        $result['err_msg'] = '操作失败';
        return $result;       
    }

    // 添加操作日志
    db_add_enterprise_operation_log( $_SESSION['id_user'], $_SESSION['id_enterprise'], 'website_delete', 
        $args['id_website'], 311, '删除站点：symbol_name = '. $website_info['symbol_name'] );
    return $result;    
}

function website_modify( $args )
{
    $result = __check_parameters_and_resource_privilege( $args, array('id_website'), $args['id_website'], 'website_modify' );
    if( 0 != $result['err'] )
        return $result;

    if( isset($args['symbol_name']) )
    {
        $other_website_info = db_get_other_object_info( 'ac_website', 'symbol_name', $args['symbol_name'], 'id_website', $args['id_website'] );
        if( 0 < int($other_website_info['id_website']) )
        {
            $result['err'] = -102;
            $result['err_msg'] = 'symbol_name 已存在，请换一个';
            return $result;
        }
    }

    if( $website_info['id_enterprise'] != $_SESSION['id_enterprise'] )
    {
        $result['err'] = -105;
        $result['err_msg'] = '企业 id 与登录企业不匹配';
        return $result;
    }

    if( !$g_mysql->updateDataEx( 'website', $args, 'id_website' ) )
    {
        $result['err'] = -103;
        $result['err_msg'] = '操作失败';
    }

    // 添加操作日志
    db_add_enterprise_operation_log( $_SESSION['id_user'], $_SESSION['id_enterprise'], 'website_modify', 
        $args['id_website'], 312, '修改站点信息： symbol_name = ' . $args['symbol_name'] );
    return $result;
}

function enterprise_admin_all_get( $args )
{
    $result = __check_parameters_and_resource_privilege( $args, array('id_enterprise'), $args['id_enterprise'], 'enterprise_admin_read' );
    if( 0 != $result['err'] )
        return $result;

    if( $args['id_enterprise'] != $_SESSION['id_enterprise'] )
    {
        $result['err'] = -105;
        $result['err_msg'] = '企业 id 与登录企业不匹配';
        return $result;
    }

    $result['enterprise_admin_list'] = $g_mysql->selectDataEx( 'ac_user', 
            array('resource_type', 'id_resource'), array(RESOURCE_TYPE_ENTERPRISE, $args['id_enterprise']) );
            
    return $result;
}

function enterprise_admin_info_get( $args )
{
    $result = __check_parameters_and_resource_privilege( $args, array('id_enterprise', 'id_user'), $args['id_enterprise'], 'enterprise_admin_read' );
    if( 0 != $result['err'] )
        return $result;

    if( $args['id_enterprise'] != $_SESSION['id_enterprise'] )
    {
        $result['err'] = -105;
        $result['err_msg'] = '企业 id 与登录企业不匹配';
        return $result;
    }

    $result['enterprise_admin_info'] = $g_mysql->selectOne( 'ac_user', 
            array('resource_type', 'id_resource', 'id_user'), array(RESOURCE_TYPE_ENTERPRISE, $args['id_enterprise'], $args['id_user']) );
            
    return $result;
}

function enterprise_admin_add( $args )
{
    $result = __check_parameters_and_resource_privilege( $args, array('id_enterprise', 'name', 'email', 'mobile', 'password'), $args['id_enterprise'], 'enterprise_admin_add' );
    if( 0 != $result['err'] )
        return $result;

    if( $args['id_enterprise'] != $_SESSION['id_enterprise'] )
    {
        $result['err'] = -105;
        $result['err_msg'] = '企业 id 与登录企业不匹配';
        return $result;
    }

    $result = __user_add( $args, RESOURCE_TYPE_ENTERPRISE, $args['id_enterprise'] );
    if( 0 != $result['err'] )
        return $result;

    // 添加操作日志
    db_add_enterprise_operation_log( $_SESSION['id_user'], $_SESSION['id_enterprise'], 'enterprise_admin_add', 
        $result['id_user'], 320, '添加企业管理员：email = ' . $args['email'] );

    return $result;
}

function enterprise_admin_delete( $args )
{
    $g_mysql;
    $result = __check_parameters_and_resource_privilege( $args, array('id_enterprise', 'id_user'), $args['id_enterprise'], 'enterprise_admin_delete' );
    if( 0 != $result['err'] )
        return $result;

    $result = __check_user_belong_enterprise( $args, $result );
    if( 0 != $result['err'] )
        return $result;

    $result = __user_delete( $user_info['id_user'] );
    if( 0 != $result['err'] )
        return $result;

    // 添加操作日志
    db_add_enterprise_operation_log( $_SESSION['id_user'], $_SESSION['id_enterprise'], 'enterprise_admin_delete', 
        $args['id_user'], 321, '删除企业管理员： email = ' . $user_info['email'] );
    return $result;
}

function enterprise_admin_modify( $args )
{
    $g_mysql;
    $result = __check_parameters_and_resource_privilege( $args, array('id_enterprise', 'id_user'), $args['id_enterprise'], 'enterprise_admin_modify' );
    if( 0 != $result['err'] )
        return $result;

    $result = __check_user_belong_enterprise( $args, $result );
    if( 0 != $result['err'] )
        return $result;

    $result = __modify_user_info( $args );
    if( 0 != $result['err'] )
        return $result;

    // 添加操作日志
    db_add_enterprise_operation_log( $_SESSION['id_user'], $_SESSION['id_enterprise'], 'enterprise_admin_modify', 
        $args['id_user'], 322, '修改企业管理员信息： ' . json_encode($args) );
    return $result;
}

function admin_resource_rule_all_get( $args )
{
    $g_mysql;
    $result = __check_parameters_and_resource_privilege( $args, array('id_enterprise', 'id_user'), $args['id_enterprise'], 'enterprise_admin_rule' );
    if( 0 != $result['err'] )
        return $result;

    $result = __check_user_belong_enterprise( $args, $result );
    if( 0 != $result['err'] )
        return $result;

    $result['admin_resource_rule_list'] = $g_mysql->selectDataEx( 'ac_user_resource_rule', array('id_user'), array($args['id_user']) );
    return $result;
}

function admin_resource_rule_info_get( $args )
{
    global $g_mysql;
    $result = __check_parameters_and_resource_privilege( $args, array('id_enterprise', 'id_user', 'id'), $args['id_enterprise'], 'enterprise_admin_rule' );
    if( 0 != $result['err'] )
        return $result;

    $result = __check_user_belong_enterprise( $args, $result );
    if( 0 != $result['err'] )
        return $result;
    
    $result['admin_resource_rule_info'] = $g_mysql->selectOne( 'ac_user_resource_rule', array('id', 'id_user'), array($args['id'], $args['id_user']) );
    return $result;
}

function admin_resource_rule_add( $args )
{
    global $g_mysql;
    $result = __check_parameters_and_resource_privilege( $args, array('id_user', 'id_rule', 'resource_type', 'id_resource'), $args['id_enterprise'], 'enterprise_admin_rule' );
    if( 0 != $result['err'] )
        return $result;

    $result = __check_user_belong_enterprise( $args, $result );
    if( 0 != $result['err'] )
        return $result;

    // 如果已存在则直接返回
    $user_rule_info = $g_mysql->selectOne( 'ac_user_resource_rule',
        array('id_user', 'id_rule', 'resource_type', 'id_resource'),
        array($args['id_user'], $args['id_rule'], $args['resource_type'], $args['id_resource']) );
    if( 0 < $user_rule_info['id'] )
    {
        $result['id'] = $user_rule_info['id'];
        return $result;
    }

    $rule_info = $g_mysql->selectOne( 'ac_rule', array('id_rule'), array($args['id_rule']) );
    if( 1 > count($rule_info) )
    {
        $result['err'] = -102;
        $result['err_msg'] = 'id_rule 不存在，请检查';
        return $result;
    }

    // 资源是否存在
    if( 1 < intval($args['resource_type']) )
    {
        $table_name = 'ac_enterprise';
        $primary_key_name = 'id_enterprise';
        if( 2 === intval($args['resource_type']) )
        {
            $table_name = 'ac_website';
            $primary_key_name = 'id_website';
        }

        $resource_info = $g_mysql->selectOne( $table_name, array($primary_key_name), array($args['id_resource']) );
        if( 1 > count($resource_info) )
        {
            $result['err'] = -102;
            $result['err_msg'] = 'id_resource 不存在，请检查';
            return $result;
        }
    }

    // 插入数据
    $result['id'] = $g_mysql->insertDataEx( 'ac_user_resource_rule', $args, 'id' );
    
    // 添加操作日志
    db_add_enterprise_operation_log( $_SESSION['id_user'], $_SESSION['id_enterprise'], 'admin_resource_rule_add', 
        $result['id'], 350, '添加用户角色：' . json_encode($args) );
    return $result;
}

function admin_resource_rule_delete( $args )
{
    global $g_mysql;
    $result = __check_parameters_and_resource_privilege( $args, array('id_enterprise', 'id_user', 'id'), $args['id_enterprise'], 'enterprise_admin_rule' );
    if( 0 != $result['err'] )
        return $result;

    // 不存在，直接返回成功
    $user_rule_info = $g_mysql->selectOne( 'ac_user_resource_rule', array('id'), array($args['id']) );
    if( 1 > count($user_rule_info) )
        return $result;

    $result = __check_user_belong_enterprise( $args, $result );
    if( 0 != $result['err'] )
        return $result;

    if( !$g_mysql->deleteData( 'ac_user_resource_rule', 'id=?', array($args['id']) ) )
    {
        $result['err'] = -103;
        $result['err_msg'] = '操作失败';
        return $result;       
    }

    // 添加操作日志
    db_add_enterprise_operation_log( $_SESSION['id_user'], $_SESSION['id_enterprise'], 'admin_rule_delete', 
        $args['id'], 351, '删除用户角色：' . json_encode($args) );
    return $result;
}

?>