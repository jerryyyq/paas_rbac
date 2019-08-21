<?php

define( 'RESOURCE_TYPE_ALL', 0 );
define( 'RESOURCE_TYPE_SYSTEM', 1 );
define( 'RESOURCE_TYPE_ENTERPRISE', 2 );
define( 'RESOURCE_TYPE_WEB', 3 );
define( 'RESOURCE_TYPE_ENTERPRISE+WEB', 4 );

// 公共路由函数表
$common_route_functions = array(
    'test',
    'login',
    'change_password',      // realize in paas_common.php
    'have_privilege',
    'have_resource_privilege',

    'privilege_all_get',
    'privilege_info_get',
    'rule_all_get',
    'rule_info_get',
    'rule_privilege_all_get',
    'rule_privilege_info_get',

    'goods_all_type_get',
    'goods_all_id_get',
    'goods_all_info_get',
    'goods_info_get',
);

function test( $args )
{
    $result = array( 'err' => 0, 'err_msg' => '', 'data' => 'Hello Pass_rbac! you call test.' );
    $result['args'] = $args;
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

function privilege_all_get( $args )
{
    global $g_mysql;
    $result = __check_parameters_and_privilege( $args, array(), 'privilege_manage' );
    if( 0 != $result['err'] )
        return $result;

    $result['privilege_list'] = $g_mysql->selectDataEx( 'ac_privilege' );
    return $result;
}

function privilege_info_get( $args )
{
    global $g_mysql;
    $result = comm_check_parameters( $args, array('name') );
    if( 0 != $result['err'] )
        return $result;

    //$result['privilege_info'] = db_get_some_table_info( 'ac_privilege', 'name', $args['name'], 'id_privilege' );
    $result['privilege_info'] = $g_mysql->selectOne( 'ac_privilege', array('name'), array($args['name']) );
    return $result;
}

function rule_all_get( $args )
{
    global $g_mysql;
    $result = __check_parameters_and_privilege( $args, array(), 'privilege_manage' );
    if( 0 != $result['err'] )
        return $result;

    $result['rule_list'] = $g_mysql->selectDataEx( 'ac_rule' );
    return $result;
}

function rule_info_get( $args )
{
    global $g_mysql;
    $result = comm_check_parameters( $args, array('name') );
    if( 0 != $result['err'] )
        return $result;

    $result['rule_info'] = $g_mysql->selectOne( 'ac_rule', array('name'), array($args['name']) );
    return $result;
}

function rule_privilege_all_get( $args )
{
    global $g_mysql;
    $result = __check_parameters_and_privilege( $args, array(), 'privilege_manage' );
    if( 0 != $result['err'] )
        return $result;

    $result['rule_privilege_list'] = $g_mysql->selectDataEx( 'ac_rule_privilege' );
    return $result;
}

function rule_privilege_info_get( $args )
{
    global $g_mysql;
    $result = comm_check_parameters( $args, array('id') );
    if( 0 != $result['err'] )
        return $result;

    $result['rule_privilege_info'] = $g_mysql->selectOne( 'ac_rule_privilege', array('id'), array($args['id']) );
    return $result;
}

function goods_all_type_get( $args )
{
    global $g_mysql;
    $result = comm_check_parameters( $args, array(id_website) );
    if( 0 != $result['err'] )
        return $result;

    $result['goods_type_list'] = $g_mysql->selectDataEx( 'ac_goods_type', array('id_website'), array($args['id_website']) );
    return $result;
}

function goods_all_id_get( $args )
{
    global $g_mysql;
    $result = comm_check_parameters( $args, array(id_website) );
    if( 0 != $result['err'] )
        return $result;

    $sql = "SELECT id_goods FROM ac_goods WHERE id_website = ?";
    $bind_param = array( $args['id_website'] );
    if( isset($args['id_goods_type']) )
    {
        $sql = "SELECT id_goods FROM ac_goods WHERE id_website = ? AND id_goods_type = ?";
        $bind_param = array( $args['id_website'], $args['id_goods_type'] );
    }

    $all_id_list = array();
    $rows = $g_mysql->selectData( $sql, $bind_param );
    foreach( $rows as $row )
    {
        $all_id_list[] = $row[0];
    }

    $result['goods_id_list'] = $all_id_list;
    return $result;
}

function goods_all_info_get( $args )
{
    global $g_mysql;
    $result = comm_check_parameters( $args, array(id_website) );
    if( 0 != $result['err'] )
        return $result;

    if( isset($args['id_goods_type']) )
        $result['goods_info_list'] = $g_mysql->selectDataEx( 'ac_goods', array('id_website', 'id_goods_type'), array($args['id_website'], $args['id_goods_type']) );
    else
        $result['goods_info_list'] = $g_mysql->selectDataEx( 'ac_goods', array('id_website'), array($args['id_website']) );

    return $result;
}

function goods_info_get( $args )
{
    global $g_mysql;
    $result = comm_check_parameters( $args, array(id_goods) );
    if( 0 != $result['err'] )
        return $result;

    $result['goods_info'] = $g_mysql->selectOne( 'ac_goods', array('id_goods'), array($args['id_goods']) );
    return $result;
}


?>