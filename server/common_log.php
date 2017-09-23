<?php
// by yangyuqi at 2017-07-09
// 这下日志文件会写到文件所在目录下的 /log/ 目录下

define("SPT", DIRECTORY_SEPARATOR);
/*
define("NOTI_FILE", dirname(__FILE__) . SPT . "log" . SPT . "noti-");
define("ITEM_FILE", dirname(__FILE__) . SPT . "log" . SPT . "item-");
define("DLOG_FILE", dirname(__FILE__) . SPT . "log" . SPT . "atch-");
define("JUMP_FILE", dirname(__FILE__) . SPT . "log" . SPT . "jump-");
define("CRASH_FILE", dirname(__FILE__) . SPT ."log" . SPT . "crash-");
*/
define('NOTI_FILE', '/tmp' . SPT . 'phplogs' . SPT . 'noti-');
define('ITEM_FILE', '/tmp' . SPT . 'phplogs' . SPT . 'item-');
define('DLOG_FILE', '/tmp' . SPT . 'phplogs' . SPT . 'atch-');
define('JUMP_FILE', '/tmp' . SPT . 'phplogs' . SPT . 'jump-');
define('CRASH_FILE', '/tmp' . SPT . 'phplogs' . SPT . 'crash-');


function comm_get_user_ip()
{
	$usrip = '';
	// iis
	if( !empty($_SERVER['REMOTE_ADDR']) )
		$usrip = $_SERVER['REMOTE_ADDR'];
	else if( !empty($_SERVER['HTTP_CLIENT_IP']) )
		$usrip = $_SERVER['HTTP_CLIENT_IP'];
	else if( !empty($_SERVER['HTTP_X_FORWARDED_FOR']) )
		$usrip = $_SERVER['HTTP_X_FORWARDED_FOR'];
	else if( !empty($HTTP_SERVER_VARS['REMOTE_ADDR']) )
		$usrip = $HTTP_SERVER_VARS['REMOTE_ADDR'];

	return $usrip;
}

function logNoti($log)
{
	logDateItem($log, NOTI_FILE);
}

function logCrash($log)
{
	logDateItem($log, CRASH_FILE);
}

function logItem($log)
{
	logDateItem($log, ITEM_FILE);
}

function logDateItem($log, $pre)
{
	$curdate = date('Y-m-d-H');
	$curtime = date('Y-m-d H:i:s');

	$conts = $curtime . ' |' . comm_get_user_ip() . '| ' . $log . "\r\n";
//	$pre = $noti?NOTI_FILE:ITEM_FILE;
	writeFile($pre . $curdate . '.log', $conts);
}

function mkdirs($dir, $mode = 0777)
{
    if (is_dir($dir) || mkdir($dir, $mode))
	    return TRUE;

    if (!mkdirs(dirname($dir), $mode))
	    return FALSE;

    return mkdir($dir, $mode);
}

function writeFile($filename, $data)
{
	$dirname = dirname($filename);
    mkdirs($dirname);
	return file_put_contents($filename, $data, FILE_APPEND);
}

// echo NOTI_FILE;
// print( NOTI_FILE );
// logCrash('ajklsajfkslj');

?>