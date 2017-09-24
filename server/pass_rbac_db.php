<?php
require_once('./common.php');

// 返回值：0 == 用户不存在；大于 0 == 用户 id；-1 == 口令错误；-2 == 帐号未激活
function db_check_user_password( $table_name, $primary_key_name, $email, $password )
{
    $sql = "SELECT {$primary_key_name},name,email,salt,password,state FROM {$table_name} WHERE email = ? OR mobile = ? LIMIT 1";
    $bind_param = array($email, $email);
    $rows = db_select_data($sql, $bind_param);
    if( !isset($rows[0]) )
        return 0;

    $user = $rows[0];
    if( $user['salt'] == '' && $user['password'] == '' && $password == '' )
        return (int)$user[$primary_key_name];

    $password_hash = comm_get_password_hash( $password, $user['salt'] );
    if( $user['password'] != $password_hash )
        return -1;

    if( intval($user['state']) != 1 )
        return -2;

    if( $user['nickname'] == '' )
    {
        db_update_data( $table_name, array('name'), $primary_key_name . ' = ?', array($email, $user[$primary_key_name]) );
    }

    return (int)$user[$primary_key_name]; 
}

function db_get_user_all_info( $table_name, $primary_key_name, $iduser, $wx_unionid = '', $email = '' )
{
    $sql = "SELECT * FROM {$table_name} WHERE ";

    $bind_param = array();
    if( 0 < intval($iduser) )
    {
        $sql = $sql . $primary_key_name . ' = ?';
        $bind_param[0] = $iduser;
    }
    else if( 0 < strlen($wx_unionid) )
    {
        $sql = $sql . 'weixinopenid = ?';
        $bind_param[0] = $wx_unionid;
    }
    else if( 0 < strlen($email) )
    {
        $sql = $sql . 'email = ?';
        $bind_param[0] = $email;
    }    

    $rows = db_select_data($sql, $bind_param);
    if( !isset($rows[0]) )
        return array($primary_key_name => 0);

    return $rows[0];
}

function db_get_user_info( $table_name, $primary_key_name, $iduser, $wx_unionid = '', $email = '' )
{
    $user_info = db_get_user_all_info( $table_name, $primary_key_name, $iduser, $wx_unionid, $email);
    unset( $user_info['salt'] );
    unset( $user_info['password'] );

    return $user_info;
}

function db_get_enterprise_info( $symbol_name, $id_enterprise = 0 )
{
    $sql = "SELECT * FROM enterprise WHERE symbol_name = ? LIMIT 1";
    $bind_param = array( $symbol_name );
    if( 1 > strlen($symbol_name) )
    {
        $sql = "SELECT * FROM enterprise WHERE id_enterprise = ? LIMIT 1";
        $bind_param = array( $id_enterprise );
    }

    $rows = db_select_data($sql, $bind_param);
    if( !isset($rows[0]) )
        return array( 'id_enterprise' => 0 );

    return $rows[0];
}

function db_get_website_info( $symbol_name, $id_website = 0 )
{
    $sql = "SELECT * FROM website WHERE symbol_name = ? LIMIT 1";
    $bind_param = array( $symbol_name );
    if( 1 > strlen($symbol_name) )
    {
        $sql = "SELECT * FROM enterprise WHERE id_website = ? LIMIT 1";
        $bind_param = array( $id_website );
    }

    $rows = db_select_data($sql, $bind_param);
    if( !isset($rows[0]) )
        return array( 'id_website' => 0 );

    return $rows[0];
}

function db_get_user_resource_privilege( $table_name, $primary_key_name, $id_user )
{
    $sql = "SELECT A.* FROM ac_rule_resource_privilege AS A, {$table_name} AS B 
            WHERE A.id_rule = B.id_rule AND B.{$primary_key_name} ＝ ？";
    
    $bind_param = array( $id_user );

    $rows = db_select_data($sql, $bind_param);
    if( !isset($rows[0]) )
        return array( );

    return $rows[0];
｝

?>