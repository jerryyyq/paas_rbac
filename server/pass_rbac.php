<?php
require_once('./common.php');
require_once('./pass_rbac_db.php');

define('COOKIE_OVER_TIME', 86400);         // session 与 cookie 过期时间：1 天过期

$allowed_funtion = array(
    'sys_admin_login',
    'enterprise_admin_login',
    'user_login',
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
function sys_admin_login( $args )
{
    $result = comm_check_parameters( $args, array('email', 'password') );
    if( 0 != $result['err'] )
        return $result;

    $id_user = db_check_user_password( 'sys_admin', 'id_admin', $args['email'], $args['password'] );
    if( 0 < $id_user )
    {
        $user_info = db_get_user_info( 'sys_admin', 'id_admin', $id_user );

        // 存入 session
        $user_info['type'] = 0;
        session_set_user_info( $user_info );

        $result['data'] = $user_info;
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



?>