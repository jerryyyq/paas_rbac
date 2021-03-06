<?php
require_once('./paas_rbac_db.php');

define( 'COOKIE_OVER_TIME', 86400 );         // session 与 cookie 过期时间：1 天过期

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


//////////////////////// session 代码 ///////////////////////////
function __session_set_user_info( $user_info, $user_privilege )
{
    $_SESSION['user_info'] = $user_info;
    $_SESSION['user_privilege'] = $user_privilege;

    $_SESSION['id_user'] = $user_info['id_user'];
    setcookie('id_user', $_SESSION['id_user'], time() + COOKIE_OVER_TIME);
}

function &__session_get_user_info( $id_user = 0 )
{
    if( 0 < intval($id_user) and intval($_SESSION['id_user']) != intval($id_user) )
        return array('id_user' => 0);

    return $_SESSION['user_info'];
}

function __session_get_user_id( )
{
    return isset($_SESSION['id_user']) ? intval($_SESSION['id_user']) : 0;
}

// login_type: 1 system_admin, 2 enterprise_admin, 3 user
function __do_login( $login_type, $email, $password )
{
    global $g_debug;
    $result = array('err' => 0, 'err_msg' => '', 'user_info' => array() );

    $id_user = db_check_user_password( $email, $password );
    if( 0 < $id_user )
    {
        $user_info = db_get_user_info( $id_user );
        if( $g_debug )
        {
            print_r( $user_info );
        }
        $result['user_info'] = $user_info;

        // 获得权限信息
        $result['user_privilege'] = db_get_user_resource_privilege( $result['user_info']['id_user'], $login_type );

        // 存入 session
        $_SESSION['login_type'] = $login_type;
        __session_set_user_info( $user_info, $result['user_privilege'] );
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

function __have_privilege( $user_privilege, $privilege_name, $id_resource = 0 )
{
    foreach( $user_privilege as $key => $row )
    {
        if( $row['name'] === $privilege_name && (0 === $id_resource || 0 === intval($row['id_resource']) || $row['id_resource'] === $id_resource) )
        {
            if( $g_debug )
            {
                echo 'privilege: ' . json_encode($row);
            }

            return $row;
        }
    }

    return array('id_privilege' => 0);
}
// 检查当前用户是否有某个权限
function __have_privilege_ex( $privilege_name )
{
    $privilege = __have_privilege($_SESSION['user_privilege'], $privilege_name);
    if( 1 > $privilege['id_privilege'] )
        return false;
    else
        return true;
}

function __have_resource_privilege( $user_privilege, $id_resource, $privilege_name )
{
    global $g_debug;
    $privilege = __have_privilege( $user_privilege, $privilege_name, $id_resource );

    if( 1 > $privilege['id_privilege'] )
        return false;

    return true;
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

function __check_parameters_and_resource_privilege( $args, $mast_exist_parameters, $id_resource, $privilege_name )
{
    $result = comm_check_parameters( $args, $mast_exist_parameters );
    if( 0 != $result['err'] )
        return $result;

    // 检查当前管理员是否有权限
    if( !__have_resource_privilege_ex( $id_resource, $privilege_name ) )
    {
        $result['err'] = -2;
        $result['err_msg'] = '没有相应权限';
    }
    
    return $result;
}

// 获得一个用户可以管理的某类资源的列表
function __get_privilege_resource_list( $privilege_name, $resource_type )
{
    global $g_mysql;
    if( RESOURCE_TYPE_ENTERPRISE !== $resource_type && RESOURCE_TYPE_WEB != $resource_type )
        return $resource_id_list;

    $resource_id_list = array();
    foreach( $_SESSION['user_privilege'] as $privilege )
    {
        if( $privilege_name === $privilege['name'] && $resource_type === intval($privilege['resource_type']) && 0 === intval($privilege['id_resource']) )
        {
            $all_id_list = array();
            // 需要获得所有这个类型的资源的 id 列表
            $sql = "SELECT id_enterprise FROM ac_enterprise";
            if( RESOURCE_TYPE_WEB === $resource_type )
                $sql = "SELECT id_website FROM ac_website";

            $rows = $g_mysql->SelectData($sql);
            foreach( $rows as $row )
            {
                $all_id_list[] = $row[0];
            }

            return $all_id_list;
        }

        if( $privilege_name === $privilege['name'] && $resource_type === intval($privilege['resource_type']) )
        {
            $resource_id_list[] = intval($privilege['id_resource']);
        }
    }

    return $resource_id_list;
}

// 修改用户信息，会自动过滤掉 salt, password
function __modify_user_info( $user_info )
{
    global $g_mysql;
    $result = array('err' => 0, 'err_msg' => '', 'user_info' => array() );

    if( isset($user_info['email']) )
    {
        $other_user_info = db_get_other_object_info( 'ac_user', 'email', $user_info['email'], 'id_user', $user_info['id_user'] );
        if( 0 < int($other_user_info['id_user']) )
        {
            $result['err'] = -102;
            $result['err_msg'] = 'email 已存在，请换一个';
            return $result;
        }
    }

    if( isset($user_info['mobile']) )
    {
        $other_user_info = db_get_other_object_info( 'ac_user', 'mobile', $user_info['mobile'], 'id_user', $user_info['id_user'] );
        if( 0 < int($other_user_info['id_user']) )
        {
            $result['err'] = -102;
            $result['err_msg'] = 'mobile 已存在，请换一个';
            return $result;
        }
    }

    unset( $user_info['salt'] );
    unset( $user_info['password'] );

    if( !$g_mysql->updateDataEx( 'ac_user', $user_info, 'id_user' ) )
    {
        $result['err'] = -103;
        $result['err_msg'] = '操作失败';
    }
    
    return $result;
}

function __user_add( $args, $resource_type, $id_resource )
{
    $user_info = db_get_user_info( 0, '', $args['email'] );
    if( 0 < int($user_info['id_user']) )
    {
        $result['err'] = -102;
        $result['err_msg'] = 'email 已存在，请换一个';
        return $result;
    }

    // 计算 password
    $args['salt'] = '';
    $args['password'] = comm_get_password_hash( $args['password'], $args['salt'] );

    // 加入数据库
    $args['state'] = 1;
    $args['resource_type'] = $resource_type;
    $args['id_resource'] = $id_resource;
    $args['email_verify_state'] = 1;
    $args['mobile_verify_state'] = 1;
    $args['wx_unionid'] = '';
    $args['wx_openid'] = '';
    $result['id_user'] = $g_mysql->insertDataEx( $table_name, $args, 'id_user' ); 
    
    return $result;
}

function __user_delete( $id_user )
{
    $g_mysql;
    if( !$g_mysql->deleteData( 'ac_user', 'id_user=?', array($id_user) ) )
    {
        $result['err'] = -103;
        $result['err_msg'] = '操作失败';
        return $result;       
    }

    // 删除 ac_user_resource_rule
    if( !$g_mysql->deleteData( 'ac_user_resource_rule', 'id_user=?', array($id_user) ) )
    {
        comm_get_default_log()->logError( 'delete ac_user_resource_rule Fail! id_user = ' . $id_user );
    }

    return $result;
}

function __check_user_resource_id( $user_info, $resource_type, $id_resource )
{
    $result = array('err' => 0, 'err_msg' => '');

    if( $resource_type != $user_info['resource_type'] )
    {
        $result['err'] = -106;
        $result['err_msg'] = '用户类型不匹配';
        return $result;
    }    
    
    if( $user_info['id_resource'] != $_SESSION['id_enterprise'] )
    {
        $result['err'] = -107;
        $result['err_msg'] = '用户资源 id 与登录资源 id 不匹配';
    }

    return $result;
}

function __check_user_belong_enterprise( $args, $result )
{
    if( $args['id_enterprise'] != $_SESSION['id_enterprise'] )
    {
        $result['err'] = -105;
        $result['err_msg'] = '企业 id 与登录企业不匹配';
        return $result;
    }

    // 用户不存在
    $user_info = db_get_user_info( $args['id_user'] );
    if( 1 > int($user_info['id_user']) )
    {
        $result['err'] = -102;
        $result['err_msg'] = '用户不存在';
        return $result;
    }

    $result['user_info'] = $user_info;

    // 检查目标用户是否是该企业下的管理员
    $result = __check_user_resource_id($user_info, RESOURCE_TYPE_ENTERPRISE, $_SESSION['id_enterprise']);
    return $result;
}

?>