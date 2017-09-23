<?php
define('SERVER_DOMAIN_URL', 'https://w.xxguan.cn/'); // 'https://w.xxguan.cn/' 'https://mp.1999youlian.com/'

define('DB_HOST', '127.0.0.1');
define('DB_USER', 'root');
define('DB_PASS', 'yyqet');                 // youlian password is: root
define('DB_NAME', 'pass_rbac');           // 1999youlian
// define('APPID', 'wxc1283d537c2a0b44');   // 1999youlian
// define('APPSECRET', 'b9e8d54e9f345ad7ab3b599722573f9a');

define('MEMCACHE_IP', '127.0.0.1');
define('MEMCACHE_PORT', 11211);

define('FILE_BASE', 'D:\wwwroot\1999youlian');
define('VIPVOTE_VALUE', 1000);   // 打赏 10 元 送 1 张月票

define('WORD_PRICE_RATIO', 500); // 单价为字数除以 500

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
            $appid = 'wxf50e114faddab4bf';  //潇湘妃子服务号 baseauth 仅服务号成功，奇怪？
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
      //  $appid = 'wx2eea39825e4219bc'; //有书韵
      //  $secret = 'f5bcb3bc9c486097482da4d4accf410b';
          $appid = 'wxad3bc3201c591106'; // 有书香
          $secret = 'cf19fcea115ef40f77f311a930812189';
    }

    return array( 'appid' => $appid, 'secret' => $secret );
}

?>