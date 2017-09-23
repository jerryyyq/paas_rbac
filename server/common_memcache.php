<?php
// by yangyuqi at 2017-07-09

include_once('svr_config.php');

$g_memcache = null;

function mem_get_connect( )
{
    global $g_memcache;
    if( !$g_memcache )
    {
        $g_memcache = new Memcache;
        $g_memcache->connect(MEMCACHE_IP, MEMCACHE_PORT) or die ('Could not connect');
    }

    return $g_memcache;
}

function mem_get_value( $mkey )
{
    return mem_get_connect()->get($mkey);
}

function mem_set_value($mkey, $value, $expire = 3600)
{
    return mem_get_connect()->set($mkey, $value, 0, $expire);
}

function mem_close( )
{
    global $g_memcache;
    if( $g_memcache )
    {
        $g_memcache->close();
        $g_memcache = null;
    }
}

// mem_set_value( 'test', array('a', 'b') );
// mem_set_value( 'test', 'jkjkjkjkjk' );
// echo mem_get_value( 'test' );

?>