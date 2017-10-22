<?php
// by yangyuqi at 2017-07-09
// 这下日志文件会写到文件所在目录下的 /log/ 目录下
include_once('yyq_frame_config.php');

define('SPT', DIRECTORY_SEPARATOR);
/*
define("DEBUG_FILE", dirname(__FILE__) . SPT . "log" . SPT . "noti-");
*/
define('DEBUG_FILE', '/tmp' . SPT . 'phplogs' . SPT . 'debug-');
define('INFO_FILE', '/tmp' . SPT . 'phplogs' . SPT . 'info-');
define('WARN_FILE', '/tmp' . SPT . 'phplogs' . SPT . 'warn-');
define('ERROR_FILE', '/tmp' . SPT . 'phplogs' . SPT . 'error-');
define('FATAL_FILE', '/tmp' . SPT . 'phplogs' . SPT . 'fatal-');


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

function log_debug( $log )
{
	if( LOG_LEVEL > 0 )
		return;

	log_date_item($log, DEBUG_FILE);
}

function log_info( $log )
{
	if( LOG_LEVEL > 1 )
		return;

	log_date_item($log, INFO_FILE);
}

function log_warn( $log )
{
	if( LOG_LEVEL > 2 )
		return;

	log_date_item($log, WARN_FILE);
}

function log_error( $log )
{
	if( LOG_LEVEL > 3 )
		return;

	log_date_item($log, ERROR_FILE);
}

function log_fatal( $log )
{
	if( LOG_LEVEL > 4 )
		return;

	log_date_item($log, FATAL_FILE);
}

function log_date_item($log, $pre)
{
	$curdate = date('Y-m-d-H');
	$curtime = date('Y-m-d H:i:s');

	$conts = $curtime . ' |' . comm_get_user_ip() . '| ' . $log . "\r\n";
//	$pre = $noti?NOTI_FILE:ITEM_FILE;
	write_to_file($pre . $curdate . '.log', $conts);
}

function make_dirs($dir, $mode = 0777)
{
    if (is_dir($dir) || mkdir($dir, $mode))
	    return TRUE;

    if (!mkdirs(dirname($dir), $mode))
	    return FALSE;

    return mkdir($dir, $mode);
}

function write_to_file($filename, $data)
{
	$dirname = dirname($filename);
    make_dirs($dirname);
	return file_put_contents($filename, $data, FILE_APPEND);
}

?>