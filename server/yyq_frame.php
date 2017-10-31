<?php
// Author: 杨玉奇
// email: yangyuqi@sina.com
// url: https://github.com/jerryyyq/paas_rbac
// copyright yangyuqi
// 著作权归作者 杨玉奇 所有。商业转载请联系作者获得授权，非商业转载请注明出处。
// date: 2017-09-22

/** php.ini 配置
* 1 使用 PDO
* extension=php_pdo_mssql.dll 去掉前面的 ";" 号
* 
* 2 打开php的安全模式
* safe_mode = on
* safe_mode_gid = off
* safe_mode_exec_dir = 我们网页目录，例如：D:/usr/www
*
* 3 控制php脚本能访问的目录
* open_basedir = D:/usr/www
*
* 4 关闭危险函数
* disable_functions = system,passthru,exec,shell_exec,popen,phpinfo
*
* 5 关闭PHP版本信息在http头中的泄漏
* expose_php = Off
*
* 6 防止SQL注入，自动把提交的查询进行转换，例如：把 ' 转为 \'等
* magic_quotes_gpc = On
*
* 7 禁止错误提示
* display_errors = Off
* log_errors = On
* error_log = D:/usr/local/apache2/logs/php_error.log
*
*/

include_once('yyq_frame_log.php');
include_once('yyq_frame_memcache.php');
include_once('yyq_frame_db.php');

date_default_timezone_set('Asia/Shanghai');

define('PBKDF2_ITERATIONS', 1000);
define('PBKDF2_LENGTH', 512);


// 框架运行主函数
function yyq_frame_main( $allowed_funtion )
{
    if( 0 == comm_make_xcros() )
        return true;

    $result = array( 'err' => 0, 'err_msg' => '', 'data' => array() );
    if( !isset($_GET['m']) )
    {
        $result['err'] = -10001;
        $result['err_msg'] = 'parameter wrong';
        echo json_encode($result, JSON_UNESCAPED_UNICODE);
        return true;
    }

    $api_name = $_GET['m'];
    while(true)
    {
        if( !$api_name || !in_array($api_name, $allowed_funtion) || !function_exists($api_name) )
        {
            $result['err'] = -10002;
            $result['err_msg'] = 'api_name wrong';
            break;
        }

        $params = comm_get_parameters( );
        // if( 1 > count($params) )
        //     log_warn( 'parameter args is empty.' );

        try
        { 
            $result = call_user_func( $api_name, $params );
        }
        catch( xception $e )
        {
            $result['err'] = -10003;
            $result['err_msg'] = $e->getMessage();
        }

        break;
    }

    echo json_encode($result, JSON_UNESCAPED_UNICODE);
}



/* ------- 所有的参数都封装到 json 串中，GET 为 args 参数，POST 时直接为 body，也可以为 args 参数 ----------
* comm_get_parameters 既可以获取 GET 时的 parameters 参数，也可以获取 POST 上来的参数
* 
* ------- 所有的应答返回值都封装到 json 串中，{"err":0, "err_msg":"", "data":{}} ----------
* err 为应答码，0 表示成功，其它值表示失败
* err_msg 为具体错误信息
* data 为返回的数据
*/
function comm_get_parameters( )
{
    if( $raw_arg = @$_REQUEST['args'] )
    {
        // 获取 url 后面的参数
        $url_decode_arg = urldecode($raw_arg);

        $input = str_replace('\\', '', $url_decode_arg);
    }
    else
    {
        // 获取 body 中的参数
        $input = @file_get_contents('php://input');
    }

    log_debug( 'input is: ' . $input );
    $params = json_decode($input, true);
    return $params; 
}

/* ---------- 检查是否需要的参数都存在 ------------
* $params 为收到的参数
* $mast_exist_parameters 为一个必须存在的变量名数组
* ----------- 返回 [err:0, err_msg:'', data:[]] ------------
* 如果所有必须的参数都存在，err 为 0, data 为 NULL
* 如果缺少参数，err 为 -1, data 为 缺少的参数名数组
*/
function comm_check_parameters( $args, $mast_exist_parameters )
{
    $result = array('err' => 0, 'err_msg' => '', 'data' => array() );
    foreach( $mast_exist_parameters as $k => $v )
    {
        if( !isset($args[$v]) )
            $result['data'][] = $v;
    }

    if( 0 < count($result['data']) )
    {
        $result['err'] = -1;
        $result['err_msg'] = 'Parameter incomplete';
    }

    return $result;
}


function comm_generate_guid()
{
    if( function_exists('com_create_guid') )
    {
        return com_create_guid();
    }
    else
    {
        mt_srand( (double)microtime() * 10000 );        // optional for php 4.2.0 and up.
        $charid = strtoupper( md5(uniqid(rand(), true)) );
        $hyphen = chr( 45 );      // '-'
        $uuid = substr( $charid, 0, 8 ) . $hyphen
            .substr( $charid, 8, 4 ) . $hyphen
            .substr( $charid, 12, 4 ) . $hyphen
            .substr( $charid, 16, 4 ) . $hyphen
            .substr( $charid, 20, 12 );

        return $uuid;
    }
}

function comm_get_password_hash( $password, &$salt )
{
    if( 1 > strlen($salt) )
    {
        $salt = mcrypt_create_iv(22, MCRYPT_DEV_URANDOM);
        $salt = base64_encode($salt);
        $salt = str_replace('+', '.', $salt);
    }

    return hash_pbkdf2('sha512', $password, $salt, PBKDF2_ITERATIONS, PBKDF2_LENGTH);    
}

// 跨域检查 pls move to common.php
function comm_make_xcros()
{
    $referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : false;
    if( $referer )
    {
        $urls = parse_url($referer);
        $url = $urls['scheme'] . '://' . $urls['host'];
        isset($urls['port']) ? $url .= ':' . $urls['port'] : '';
    }
    else
    {
        $url = '*';
    }
    
    if( $_SERVER['REQUEST_METHOD'] == 'OPTIONS' )
    {
        header('HTTP/1.1 204 No Content');
        header('Access-Control-Allow-Origin: ' . $url);
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
        header('Access-Control-Allow-Headers: Keep-Alive,User-Agent,X-Requested-With,If-Modified-Since,Cache-Control,Content-Length,Content-Type');
        header('Access-Control-Max-Age: 1728000');
        header('Content-Length: 0');
        return 0;
    }

    header('Access-Control-Allow-Origin: ' . $url);     // 跨域访问
    header('Access-Control-Allow-Credentials: true');
    return 1;
}

function comm_generate_verify_code($len)
{
    $chars_array = array(
        '0', '1', '2', '3', '4', '5', '6', '7', '8', '9',
        'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k',
        'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v',
        'w', 'x', 'y', 'z', 'A', 'B', 'C', 'D', 'E', 'F', 'G',
        'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R',
        'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z',
    );
    $charsLen = count($chars_array) - 1;

    $outputstr = '';
    for( $i=0; $i<$len; $i++ )
    {
        $outputstr .= $chars_array[mt_rand(0, $charsLen)];
    }

    return $outputstr;
}

?>