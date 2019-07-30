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

// 路由函数表
$route_functions = array(
    'test',
    'login',
    'change_password',      // realize in paas_common.php
    'have_privilege',
    'have_resource_privilege',

    'privilege_info_get',
    'privilege_add',
    'privilege_delete',
    'rule_info_get',
    'rule_add',
    'rule_delete',
);

// 调用主路由函数
comm_frame_main( $route_functions );

////////////////////////////////////////// 路由接口函数实现 /////////////////////////////////////////////////
//////// 当前用户登录及权限检查 ////////

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

    $result = __do_login( 1, $args['email'], $args['password'] );
    return $result;
}

function change_password( $args )
{
    $result = comm_check_parameters( $args, array('old_password', 'new_password') );
    if( 0 != $result['err'] )
        return $result;

    if( 1 > __session_get_user_id() )
    {
        $result['err'] = -4;
        $result['err_msg'] = '请先登录';
        return $result;
    }

    $user_info = session_get_user_info();
    $id_user = db_check_user_password( $user_info['email'], $args['old_password'] );
    if( 0 < $id_user )
    {
        $result['err'] = -2;
        $result['err_msg'] = '密码错误';
        return $result;
    }
    
    $salt = '';
    $password = comm_get_password_hash( $args['new_password'], $salt );

    if( !db_set_user_password($id_user, $salt, $password) )
    {
        $result['err'] = -10;
        $result['err_msg'] = '操作失败';

    }
    
    return $result;    
}

// 检查当前用户是否有某个权限
function have_privilege( $args )
{
    $result = comm_check_parameters( $args, array('privilege_name') );
    if( 0 != $result['err'] )
        return $result;

    $result[ $args['privilege_name'] ] = __have_privilege_ex( $args['privilege_name'] );
    return $result;
}

// 检查当前用户是否有某个资源的权限
function have_resource_privilege( $args )
{
    $result = comm_check_parameters( $args, array('id_resource', 'privilege_name') );
    if( 0 != $result['err'] )
        return $result;

    $result['have'] = __have_resource_privilege_ex($args['id_resource'], $args['privilege_name']);
    return $result;
}


//////// 数据库表操作 ////////
function privilege_info_get( $args )
{
    global $g_mysql;
    $result = comm_check_parameters( $args, array('name') );
    if( 0 != $result['err'] )
        return $result;

    //$result['privilege_info'] = db_get_some_table_info( 'ac_privilege', 'name', $args['name'], 'id_privilege' );
    $result['privilege_info'] = $g_mysql->selectDataEx( 'ac_privilege', array('name'), array($args['name']) )[0];
    return $result;
}

function privilege_add( $args )
{
    global $g_mysql;
    $result = __check_parameters_and_privilege( $args, array('id_father', 'have_child', 'name', 'show_name'), 'privilege_manage' );
    if( 0 != $result['err'] )
        return $result;

    $privilege_info = $g_mysql->selectDataEx( 'ac_privilege', array('name'), array($args['name']) )[0];
    if( 0 < int($privilege_info['id_privilege']) )
    {
        $result['err'] = -102;
        $result['err_msg'] = 'name 已存在，请换一个';
        return $result;
    }

    $result['id_privilege'] =  $g_mysql->insertDataEx( 'ac_privilege', $args, 'id_privilege' );
    
    // 添加操作日志
    db_add_sys_operation_log( $_SESSION['id_user'], 'privilege_add', $result['id_privilege'], 100, '添加权限：' . $args['name'] );
    return $result;
}

function privilege_delete( $args )
{
    global $g_mysql;
    $result = __check_parameters_and_privilege( $args, array('name'), 'privilege_manage' );
    if( 0 != $result['err'] )
        return $result;

    // 不存在，直接返回成功
    $privilege_info = $g_mysql->selectDataEx( 'ac_privilege', array('name'), array($args['name']) )[0];
    if( 1 > int($privilege_info['id_privilege']) )
        return $result;

    // 获得本权限及其所有子孙权限
    $one_privileges = db_expand_one_privilege(0, 0, $privilege_info['id_privilege'], true);
    $id_privilege_str = "";
    foreach( $one_privileges as $privilege )
    {
        if( 1 > strlen($id_privilege_str) )
            $id_privilege_str = $privilege['id_privilege']; 
        else
            $id_privilege_str = $id_privilege_str . ',' . $privilege['id_privilege'];
    }

    // 删除本权限及其所有子孙权限
    if( !$g_mysql->deleteData( 'ac_privilege', 'id_privilege IN (?)', array($id_privilege_str) ) )
    {
        $result['err'] = -103;
        $result['err_msg'] = '操作失败';
        return $result;       
    }

    // 添加操作日志
    db_add_sys_operation_log( $_SESSION['id_user'], 'privilege_delete', $privilege_info['id_privilege'], 101, '删除权限：' . $args['name'] );

    // 检查父权限是否还有其它子权限，没有的话要修改父权限的 have_child 值
    $one_privileges = db_expand_one_privilege(0, 0, $privilege_info['id_father'], false);
    if( 1 > count($one_privileges) )
    {
        if( !$g_mysql->updateDataEx('ac_privilege', array('id_privilege' => $privilege_info['id_father'], 'have_child' => 0), 'id_privilege') )
            comm_get_default_log()->logError( "update privilege: {$privilege_info['id_father']} have_chile => 0 Fail!" );
    }

    return $result;
}

function rule_info_get( $args )
{
    global $g_mysql;
    $result = comm_check_parameters( $args, array('name') );
    if( 0 != $result['err'] )
        return $result;

    $result['rule_info'] = $g_mysql->selectDataEx( 'ac_rule', array('name'), array($args['name']) )[0];
    return $result;
}

function rule_add( $args )
{
    global $g_mysql;
    $result = __check_parameters_and_privilege( $args, array('resource_type', 'name', 'show_name'), 'privilege_manage' );
    if( 0 != $result['err'] )
        return $result;

    $rule_info = $g_mysql->selectDataEx( 'ac_rule', array('name'), array($args['name']) )[0];
    if( 0 < int($rule_info['id_rule']) )
    {
        $result['err'] = -102;
        $result['err_msg'] = 'name 已存在，请换一个';
        return $result;
    }

    $result['id_rule'] = $g_mysql->insertDataEx( 'ac_rule', $args, 'id_rule' );
    
    // 添加操作日志
    db_add_sys_operation_log( $_SESSION['id_user'], 'rule_add', $result['id_rule'], 110, '添加角色：' . $args['name'] );
    return $result;
}

function rule_delete( $args )
{
    global $g_mysql;
    $result = __check_parameters_and_privilege( $args, array('name'), 'privilege_manage' );
    if( 0 != $result['err'] )
        return $result;
    
    // 不存在，直接返回成功
    $rule_info = $g_mysql->selectDataEx( 'ac_rule', array('name'), array($args['name']) )[0];
    if( 1 > int($rule_info['id_rule']) )
        return $result;

    // 检查该 rule 是否被引用
    $count = db_get_count( 'ac_user_resource_rule', 'id_rule = ?', array($rule_info['id_rule']) );
    if( 0 < $count )
    {
        $result['err'] = -104;
        $result['err_msg'] = "{$args['name']} 还在被引用，不能删除";
        return $result;       
    }

    if( !$g_mysql->deleteData( 'ac_rule', 'id_rule = ?', array($rule_info['id_rule']) ) )
    {
        $result['err'] = -103;
        $result['err_msg'] = '操作失败';
        return $result;       
    }

    // 添加操作日志
    db_add_sys_operation_log( $_SESSION['id_user'], 'rule_delete', $rule_info['id_rule'], 111, '删除角色：' . $args['name'] );
    return $result;    
}



?>