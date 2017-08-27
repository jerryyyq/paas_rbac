<?php
// by yangyuqi at 2017-07-09

define("SPT", DIRECTORY_SEPARATOR);
define("NOTI_FILE", dirname(__FILE__) . SPT . "log" . SPT . "mnoti-");
define("ITEM_FILE", dirname(__FILE__) . SPT . "log" . SPT . "mitem-");
define("DLOG_FILE", dirname(__FILE__) . SPT . "log" . SPT . "match.log");
define("JUMP_FILE", dirname(__FILE__) . SPT . "log" . SPT . "mjump-");
define("CRASH_FILE", dirname(__FILE__) . SPT ."log" . SPT . "crash-");

function comm_get_user_ip()
{
	$usrip = '';
	// iis
	if( $_SERVER['REMOTE_ADDR'] )
		$usrip = $_SERVER['REMOTE_ADDR'];
	if( empty($usrip) && $_SERVER['HTTP_CLIENT_IP'] )
		$usrip = $_SERVER['HTTP_CLIENT_IP'];
	if( empty($usrip) && $_SERVER['HTTP_X_FORWARDED_FOR'] )
		$usrip = $_SERVER['HTTP_X_FORWARDED_FOR'];
	if( empty($usrip) )
		$usrip = $HTTP_SERVER_VARS['REMOTE_ADDR'];

	return $usrip;
}

function logNoti($data)
{
	logDateItem($data, NOTI_FILE);
}

function logCrash($data)
{
	logDateItem($data, CRASH_FILE);
}

function logItem($data)
{
	logDateItem($data, ITEM_FILE);
}

function logDateItem($data,$pre)
{
	$curdate = date("Y-m-d");
	$curtime = date("Y-m-d H:i:s");
	$dates = explode(" ", $curtime);
	$curdate = $dates[0];
	$conts = $curtime . "|" . comm_get_user_ip() . ">" . $data . "\r\n";
//	$pre = $noti?NOTI_FILE:ITEM_FILE;
	writeFile($pre . $curdate . ".log", $conts);
}

function mkdirs($dir, $mode = 0777)
{
    if (is_dir($dir) || @mkdir($dir, $mode)) return TRUE;
    if (!mkdirs(dirname($dir), $mode)) return FALSE;
    return @mkdir($dir, $mode);
}

function writeFile($filename, $data)
{
	$dirname = dirname($filename);
    mkdirs($dirname);
	return file_put_contents($filename, $data, FILE_APPEND);
}

?>