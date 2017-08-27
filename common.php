<?php
// by yangyuqi at 2017-07-09

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

include_once('common_log.php');
include_once('common_memcache.php');
include_once('common_db.php');

date_default_timezone_set('Asia/Shanghai');

define('PBKDF2_ITERATIONS', 1000);
define('PBKDF2_LENGTH', 512);


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