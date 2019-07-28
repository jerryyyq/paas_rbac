<?php
// 作者：杨玉奇
// nginx1.14 + php7.2 安装配置参考： https://blog.csdn.net/Ersan_Yi/article/details/82107552
// 运行时错误在： /var/log/nginx
//
//
// 命令行调试： $ php paas_rbac.php debug
// php 命令行交互测试：$ php -a
///////////////////////////////////////////////////////////////////////////////////////////

require 'vendor/autoload.php';
use minimum_frame\YLog;

define( 'COOKIE_OVER_TIME', 86400 );         // session 与 cookie 过期时间：1 天过期


// 路由函数表
$route_functions = array(
    'test',
    'test2'
);

// 如果需要设置时区，可以在这里调用
date_default_timezone_set('Asia/Shanghai');

// 如果需要使用数据库，可以在这里配置
comm_create_default_mysql( 'localhost', 'paas_rbac', 'root', '$password' );

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

// 调用主路由函数
comm_frame_main( $route_functions );

///////////////////////////////////////////////////////////////////////////////////////////

function test( $args )
{
    $result = comm_check_parameters( $args, array('email', 'password', 'signature') );
    if( 0 != $result['err'] )
        return $result;

    // 有签名参数，校验签名值
    if( !comm_check_args_signature( $args )
    {
        $result['err'] = -4;
        $result['err_msg'] = 'Signature 校验失败';
        return $result;
    }

    // do something...

    // 使用数据库
    $mysql = comm_get_default_mysql();
    $users = $mysql->selectDataEx( 'user', array('id', 'name'), array(1, 'yyq') );

    // 使用 Memcache
    comm_get_default_memcache()->setValue('users', $users);

    // 使用 Log
    comm_get_default_log()->setLogLevel( YLog::LEVEL_WARN );

    return $result;
}






?>