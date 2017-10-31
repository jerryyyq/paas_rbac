<?php
// Author: 杨玉奇
// email: yangyuqi@sina.com
// url: https://github.com/jerryyyq/paas_rbac
// copyright yangyuqi
// 著作权归作者 杨玉奇 所有。商业转载请联系作者获得授权，非商业转载请注明出处。
// date: 2017-09-22

define('SERVER_DOMAIN_URL', 'https://www.pass_rbac.com/');
define('LOG_LEVEL', 0);         // 0 debug; 1 info; 2 warn; 3 error; 4 fatal

define('DB_HOST', '127.0.0.1');
define('DB_USER', 'root');
define('DB_PASS', 'yyqet');
define('DB_NAME', 'paas_rbac');

define('MEMCACHE_IP', '127.0.0.1');
define('MEMCACHE_PORT', 11211);


function get_wx_appid( $httphost, $baseauth )
{
    $appid = '';
    $secret = '';

    if( empty($httphost) )
        $httphost = $_SERVER['HTTP_HOST'];

    if( $httphost == 'w.xxguan.cn' || $httphost == 'www.xxguan.cn' )
    {
        if( isset($baseauth) && $baseauth )
        {
            $appid = 'wxf50e114faddab4bf';
            $secret = '1be1a739655d2e749baef1b0315dc613';
        }
        else
        {
            $appid = 'wx2e367ec112f1b9a1';  //开放平台获取
            $secret = '26a889328c2d77d96a2affd465169601';
        }
    }
    elseif( $httphost == 'www.xxfzi.cn' )
    {
        $appid = 'wx09e553e63eaed833';
        $secret = 'f20e3f2c21cf65a8ef80ae6d4618d6c5';
    }
    elseif( $httphost == 'w.safenext.cn' )
    {
        $appid = 'wxad3bc3201c591106';
        $secret = 'cf19fcea115ef40f77f311a930812189';
    }

    return array( 'appid' => $appid, 'secret' => $secret );
}

?>