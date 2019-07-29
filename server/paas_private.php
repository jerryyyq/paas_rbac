<?php
require_once('./paas_rbac_db.php');

// 如果需要设置时区，可以在这里调用
date_default_timezone_set('Asia/Shanghai');

// 如果需要使用数据库，可以在这里配置
// comm_create_default_mysql( 'localhost', 'paas_rbac', 'root', 'yyqet' ); // 已在 pass_rbac_db.php 中调用

// 如果需要使用 Memcache，可以在这里配置。本例设置默认过期时间为 1 天
// comm_create_default_memcache( $hostIP, 11211, 24 * 3600 );

// 设置：将 调用方法、参数、返回值 写入日志
comm_set_run_config( array('log_io' => true) );

// 设置：允许跨域访问
// comm_set_run_config( array('cross_origin' => true) );

// 设置：以宽松模式检查 SQL 语句
comm_set_run_config( array('sql_injecte_loose' => true) );

// 如果需要 session 需要把这行写到 comm_frame_main 函数前；如果不需要可以不写。
session_start();


// login_type: 0 system_admin, 1 enterprise_admin, 2 user
function __do_login( $login_type, $email, $password )
{
    global $g_debug, $g_mysql;
    $result = array('err' => 0, 'err_msg' => '', 'user_info' => array() );

    $id_user = db_check_user_password( $table_name, $primary_key_name, $email, $password );
    if( 0 < $id_user )
    {
        $user_info = db_get_user_info( $table_name, $primary_key_name, $id_user );
        if( $g_debug )
        {
            print_r( $user_info );
        }

        // 存入 session
        $user_info['table_name'] = $table_name;
        $user_info['primary_key_name'] = $primary_key_name;
        $user_info['type'] = 2;
        if( 'sys_admin' === $table_name )
            $user_info['type'] = 0;
        else if( 'enterprise_admin' === $table_name )
            $user_info['type'] = 1;

        session_set_user_info( $user_info );

        $result['user_info'] = $user_info;
    }
    else if( 0 === $id_user )
    {
        $result['err'] = -1;
        $result['err_msg'] = '用户名不存在';
    }
    else if( -1 === $id_user )
    {
        $result['err'] = -2;
        $result['err_msg'] = '密码错误';
    }
    else if( -2 === $id_user )
    {
        $result['err'] = -3;
        $result['err_msg'] = '帐号未激活,请到邮箱激活';
    }

    return $result;    
}

function __have_privilege( $user_privilege, $privilege_name )
{
    foreach( $user_privilege['privileges'] as $key => $row )
    {
        if( $row['name'] === $privilege_name )
            return (int)$key;
    }

    return -1;
}
// 检查当前用户是否有某个权限
function __have_privilege_ex( $privilege_name )
{
    $key = __have_privilege($_SESSION['user_privilege'], $privilege_name);
    if( 0 > $key )
        return false;
    else
        return true;
}

function __have_resource_privilege( $user_privilege, $id_resource, $privilege_name )
{
    global $g_debug;
    $key = __have_privilege( $user_privilege, $privilege_name );
    if( 0 > $key )
        return false;

    $privilege = $user_privilege['privileges'][$key];
    if( $g_debug )
    {
        echo 'privilege: ';
        print_r($privilege);
    }

    foreach( $user_privilege['resource_privilege'] as $row )
    {
        if( $row['id_resource'] === $id_resource and 
            ( $row['id_privilege'] === $privilege['id_privilege'] or $row['id_privilege'] === $privilege['id_father'] ) )
        {
            return true;
        }   
    }

    return false;
}

// 检查当前用户是否有某个资源的权限
function __have_resource_privilege_ex( $id_resource, $privilege_name )
{
    return __have_resource_privilege($_SESSION['user_privilege'], $id_resource, $privilege_name);
}

function __check_parameters_and_privilege( $args, $mast_exist_parameters, $privilege_name )
{
    $result = comm_check_parameters( $args, $mast_exist_parameters );
    if( 0 != $result['err'] )
        return $result;

    // 检查当前管理员是否有权限
    if( !__have_privilege_ex( $privilege_name ) )
    {
        $result['err'] = -2;
        $result['err_msg'] = '没有相应权限';
    }
    
    return $result;
}

// 修改用户信息，会自动过滤掉 salt, password
function __modify_user_info( $table_name, $primary_key_name, $user_info )
{
    $result = array('err' => 0, 'err_msg' => '', 'user_info' => array() );
    $other_user_info = db_get_other_object_info( 'sys_admin', 'email', $user_info['email'], 'id_admin', $user_info['id_admin'] );
    if( 0 < int($other_user_info['id_admin']) )
    {
        $result['err'] = -102;
        $result['err_msg'] = 'email 已存在，请换一个';
        return $result;
    }

    $other_user_info = db_get_other_object_info( 'sys_admin', 'mobile', $user_info['mobile'], 'id_admin', $user_info['id_admin'] );
    if( 0 < int($other_user_info['id_admin']) )
    {
        $result['err'] = -102;
        $result['err_msg'] = 'mobile 已存在，请换一个';
        return $result;
    }

    unset( $user_info['salt'] );
    unset( $user_info['password'] );

    if( !db_update_data_ex( 'sys_admin', $user_info, 'id_admin' ) )
    {
        $result['err'] = -103;
        $result['err_msg'] = '操作失败';
    }
    
    return $result;
}

?>