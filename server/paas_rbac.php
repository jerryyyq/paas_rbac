<?php
// 作者：杨玉奇
// 命令行调试： $ php paas_rbac.php debug
// php 命令行交互测试：$ php -a


require_once('./common.php');
require_once('./paas_rbac_db.php');

define('COOKIE_OVER_TIME', 86400);         // session 与 cookie 过期时间：1 天过期

$allowed_funtion = array(
    'test',
    'sys_admin_login',
    'enterprise_admin_login',
    'user_login',
    'have_privilege',
    'have_resource_privilege',
    'sys_admin_add',
    'enterprise_admin_add',
    'user_add',
    'change_password',

    'enterprise_symbol_name_exist',
    'enterprise_add',
);

//////////////////////// 开始主功能 ///////////////////////////
ini_set('session.gc_maxlifetime', COOKIE_OVER_TIME);
session_start();
if( isset($_COOKIE['PHPSESSID']) )
    setcookie( 'PHPSESSID', session_id(), time() + COOKIE_OVER_TIME );

$debug = in_array('debug', $argv);

if( !$debug )
{
    main();
    exit( 0 );
}

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
    $_SESSION['id_user'] = reset( $user_info );

    setcookie('id_user', $_SESSION['id_user'], time() + COOKIE_OVER_TIME);
}

function &session_get_user_info( $iduser = 0 )
{
    if( 0 < intval($iduser) && intval($_SESSION['id_user']) != intval($iduser) )
        return array('id_user' => 0);

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
    global $debug;
    $id_user = db_check_user_password( $table_name, $primary_key_name, $email, $password );
    if( 0 < $id_user )
    {
        $user_info = db_get_user_info( $table_name, $primary_key_name, $id_user );
        if( $debug )
        {
            print_r( $user_info );
        }

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

function __have_privilege( $user_privilege, $privilege_name )
{
    foreach( $user_privilege['privileges'] as $key => $row )
    {
        if( $row['name'] === $privilege_name )
            return (int)$key;
    }

    return -1;
}

function __have_resource_privilege( $user_privilege, $id_resource, $privilege_name )
{
    global $debug;
    $key = __have_privilege( $user_privilege, $privilege_name );
    if( 0 > $key )
        return false;

    $privilege = $user_privilege['privileges'][$key];
    if( $debug )
    {
        echo 'privilege: ';
        print_r($privilege);
    }

    foreach( $user_privilege['resource_privilege'] as $row )
    {
        if( $row['id_resource'] === $id_resource and 
            ( $row['id_privilege'] === $privilege['id_privilege'] or $row['id_privilege'] === $privilege['id_father'] )
        )
        {
            return true;
        }   
    }

    return false;
}

function __check_parameters_and_privilege( $args, $mast_exist_parameters, $privilege_name )
{
    $result = comm_check_parameters( $args, $mast_exist_parameters );
    if( 0 != $result['err'] )
        return $result;

    // 检查当前管理员是否有权限
    if( !have_privilege( $privilege_name ) )
    {
        $result['err'] = -2;
        $result['err_msg'] = '没有相应权限';
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
    $user_privilege = db_get_user_resource_privilege( 'ac_sys_admin_rule', 'id_admin',  $result['user_info']['id_admin']);
    $_SESSION['user_privilege'] = $user_privilege;
    $result['user_privilege'] = $user_privilege;

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
        $result['err'] = -101;
        $result['err_msg'] = '企业符号名不存在';
        return $result;
    }

    $_SESSION['enterprise_info'] = $enterprise_info;

    $result = __do_login( 'enterprise_admin', 'id_admin', $args['email'], $args['password'] );

    // 获得权限信息
    $user_privilege = db_get_user_resource_privilege( 'ac_enterprise_admin_rule', 'id_admin',  $result['user_info']['id_admin']);
    $_SESSION['user_privilege'] = $user_privilege;
    $result['user_privilege'] = $user_privilege;

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
        $result['err'] = -101;
        $result['err_msg'] = '站点符号名不存在';
        return $result;
    }

    $_SESSION['website_info'] = $website_info;

    $table_name = 'user_' . $website_info['id_website'];
    $result = __do_login( $table_name, 'id_user', $args['email'], $args['password'] );

    // 获得权限信息
    $table_name = 'ac_user_rule_' . $website_info['id_website'];
    $user_privilege = db_get_user_resource_privilege( $table_name, 'id_user',  $result['user_info']['id_user']);
    $_SESSION['user_privilege'] = $user_privilege;
    $result['user_privilege'] = $user_privilege;

    return $result;
}

// 检查当前用户是否有某个权限
function have_privilege( $privilege_name )
{
    $key = __have_privilege($_SESSION['user_privilege'], $privilege_name);
    if( 0 > $key )
        return false;
    else
        return true;
}

// 检查当前用户是否有某个资源的权限
function have_resource_privilege( $id_resource, $privilege_name )
{
    return __have_resource_privilege($_SESSION['user_privilege'], $id_resource, $privilege_name);
}

function sys_admin_add( $args )
{
    $result = __check_parameters_and_privilege( $args, array('name', 'email', 'mobile', 'password'), 'sys_admin_add' );
    if( 0 != $result['err'] )
        return $result;

    $admin_info = db_get_user_info( 'sys_admin', 'id_admin', 0, '', $args['email'] );
    if( 0 < int($admin_info['id_admin']) )
    {
        $result['err'] = -102;
        $result['err_msg'] = 'email 已存在，请换一个';
        return $result;
    }

    // 计算 password
    $args['salt'] = '';
    $args['password'] = comm_get_password_hash( $args['password'], $args['salt'] );

    // 加入数据库
    $result['id_admin'] = db_insert_data_ex( 'sys_admin', $args, 'id_admin' );
    
    // 添加操作日志
    db_add_admin_operation_log( $_SESSION['id_user'], 0, 'sys_admin_add', $result['id_admin'], 100, '添加系统管理员：' . $args['email'] );

    return $result;
}

function enterprise_admin_add( $args )
{

}

function user_add( $args )
{

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
    $result = __check_parameters_and_privilege( $args, array('symbol_name', 'real_name'), 'enterprise_add' );
    if( 0 != $result['err'] )
        return $result;

    $enterprise_info = db_get_enterprise_info( $args['symbol_name'] );
    if( 0 < int($enterprise_info['id_enterprise']) )
    {
        $result['err'] = -102;
        $result['err_msg'] = '符号名已存在，请换一个';
        return $result;
    }

    $result['id_enterprise'] = db_insert_data_ex( 'enterprise', $args, 'id_enterprise' );

    // 添加操作日志
    db_add_admin_operation_log( $_SESSION['id_user'], 0, 'enterprise_add', $result['id_enterprise'], 200, '添加企业：' . $args['symbol_name'] );

    return $result;
}



///////////////////////////////////////////////////////////////////////
// test code
///////////////////////////////////////////////////////////////////////
if( $debug )
{
    $result = sys_admin_login( array('email' => 'admin@system', 'password' => '') );
    print_r($result);

    echo '是否具有权限 enterprise_add：', have_privilege( 'enterprise_add' ), "\n";
    echo '是否具有资源权限 0, enterprise_add：', have_resource_privilege( 0, 'enterprise_add' ), "\n";
    
}
?>