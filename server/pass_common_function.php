<?php

define( 'LOGIN_TYPE_ALL', 0 );
define( 'LOGIN_TYPE_SYSTEM', 1 );
define( 'LOGIN_TYPE_ENTERPRISE', 2 );
define( 'LOGIN_TYPE_WEB', 3 );
define( 'LOGIN_TYPE_ENTERPRISE+WEB', 4 );

// 公共路由函数表
$common_route_functions = array(
    'test',
    'login',
    'change_password',      // realize in paas_common.php
    'have_privilege',
    'have_resource_privilege'
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


?>