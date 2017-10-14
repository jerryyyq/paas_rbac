<?php
require_once('./common.php');
require_once('./paas_rbac_db.php');

define('COOKIE_OVER_TIME', 86400);         // session 与 cookie 过期时间：1 天过期

$allowed_funtion = array(
    'test',
    'sys_admin_login',
    'enterprise_admin_login',
    'user_login',

    'enterprise_add',
);

//////////////////////// 开始主功能 ///////////////////////////
ini_set('session.gc_maxlifetime', COOKIE_OVER_TIME);
session_start();
if( isset($_COOKIE['PHPSESSID']) )
    setcookie( 'PHPSESSID', session_id(), time() + COOKIE_OVER_TIME );

main();
exit( 0 );

// 主函数
function main()
{
    global $allowed_funtion;
    if( 0 == comm_make_xcros() )
        return true;

    $result = array( 'err' => 0, 'err_msg' => '', 'data' => array() );

    $api_name = $_GET['m'];
    while(true)
    {
        if( !$api_name || !in_array($api_name, $allowed_funtion) || !function_exists($api_name) )
        {
            $result['err'] = -10001;
            $result['err_msg'] = 'api_name wrong';
            break;
        }

        $params = comm_get_parameters( );
        
        try
        { 
            $result = call_user_func( $api_name, $params );
        }
        catch( xception $e )
        {
            $result['err'] = -10002;
            $result['err_msg'] = $e->getMessage();
        }

        break;
    }

    echo json_encode($result, JSON_UNESCAPED_UNICODE);
}

//////////////////////// session 代码 ///////////////////////////
function session_set_user_info( $user_info )
{
    $_SESSION['user_info'] = $user_info;
    $_SESSION['id_user'] = $user_info[0];

    setcookie('id_user', $user_info[0], time() + COOKIE_OVER_TIME);
}

function &session_get_user_info( $iduser = 0 )
{
    if( 0 < intval($iduser) && intval($_SESSION['id_user']) != intval($iduser) )
        return array('id_user'=>0);

    return $_SESSION['user_info'];
}

function session_get_user_id( $type = 2 )
{
    return isset($_SESSION['id_user']) ? intval($_SESSION['id_user']) : 0;
}

///////////////////////////////////////////////////////////////////////
// 功能实现
///////////////////////////////////////////////////////////////////////

function __do_login( $table_name, $primary_key_name, $email, $password )
{
    $id_user = db_check_user_password( $table_name, $primary_key_name, $email, $password );
    if( 0 < $id_user )
    {
        $user_info = db_get_user_info( $table_name, $primary_key_name, $id_user );

        // 存入 session
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
        $result['err'] = -1;
        $result['err_msg'] = '密码错误';
    }
    else if( -2 === $id_user )
    {
        $result['err'] = -1;
        $result['err_msg'] = '帐号未激活,请到邮箱激活';
    }

    return $result;    
}


///////////////////////////////////////////////////////////////////////
// 接口函数实现
///////////////////////////////////////////////////////////////////////
function test( $args )
{
    $result = array( 'err' => 0, 'err_msg' => '', 'data' => 'Hello Pass_rbac!' );
    $result['args'] = $args;
    return $result;
}

function sys_admin_login( $args )
{
    $result = comm_check_parameters( $args, array('email', 'password') );
    if( 0 != $result['err'] )
        return $result;

    $result = __do_login( 'sys_admin', 'id_admin', $args['email'], $args['password'] );

    // 获得权限信息
    $resource_privilege = db_get_user_resource_privilege( 'ac_sys_admin_rule', 'id_admin',  $result['user_info']['id_admin']);
    $_SESSION['resource_privilege'] = $resource_privilege;
    $result['resource_privilege'] = $resource_privilege;

    return $result;
}

function enterprise_admin_login( $args )
{
    $result = comm_check_parameters( $args, array('symbol_name', 'email', 'password') );
    if( 0 != $result['err'] )
        return $result;

    $enterprise_info = db_get_enterprise_info( $args['symbol_name'] );
    if( 1 > $enterprise_info['id_enterprise'] )
    {
        $result['err'] = -5;
        $result['err_msg'] = '企业符号名不存在';
        return $result;
    }

    $_SESSION['enterprise_info'] = $enterprise_info;

    $result = __do_login( 'enterprise_admin', 'id_admin', $args['email'], $args['password'] );

    // 获得权限信息
    $resource_privilege = db_get_user_resource_privilege( 'ac_enterprise_admin_rule', 'id_admin',  $result['user_info']['id_admin']);
    $_SESSION['resource_privilege'] = $resource_privilege;
    $result['resource_privilege'] = $resource_privilege;

    return $result;
}

function user_login( $args )
{
    $result = comm_check_parameters( $args, array('symbol_name', 'email', 'password') );
    if( 0 != $result['err'] )
        return $result;

    $website_info = db_get_website_info( $args['symbol_name'] );
    if( 1 > $website_info['id_website'] )
    {
        $result['err'] = -5;
        $result['err_msg'] = '站点符号名不存在';
        return $result;
    }

    $_SESSION['website_info'] = $website_info;

    $table_name = 'user_' . $website_info['id_website'];
    $result = __do_login( $table_name, 'id_user', $args['email'], $args['password'] );

    // 获得权限信息
    $table_name = 'ac_user_rule_' . $website_info['id_website'];
    $resource_privilege = db_get_user_resource_privilege( $table_name, 'id_user',  $result['user_info']['id_user']);
    $_SESSION['resource_privilege'] = $resource_privilege;
    $result['resource_privilege'] = $resource_privilege;

    return $result;
}

function enterprise_symbol_name_exist( $args )
{
    $result = comm_check_parameters( $args, array('symbol_name') );
    if( 0 != $result['err'] )
        return $result;
    
    $result['exist'] = 0;
    $enterprise_info = db_get_enterprise_info( $args['symbol_name'] );
    if( 0 < int($enterprise_info['id_enterprise']) )
        $result['exist'] = 1;

    return $result;
}

function enterprise_add( $args )
{
    $result = comm_check_parameters( $args, array('symbol_name', 'real_name') );
    if( 0 != $result['err'] )
        return $result;

    // 检查当前管理员是否有权限


    $enterprise_info = db_get_enterprise_info( $args['symbol_name'] );
    if( 0 < int($enterprise_info['id_enterprise']) )
    {
        $result['err'] = -2;
        $result['err_msg'] = '符号名已存在，请换一个';
        return $result;
    }

    $result['id_enterprise'] = db_enterprise_add( $args );

    // 添加操作日志
    db_add_admin_operation_log( $_SESSION['id_user'], 0, 'add', $result['id_enterprise'], 100, '添加企业：' . $args['symbol_name'] );

    return $result;
}




?>