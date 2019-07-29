<?php
// 导出数据库：
// cd ~/work/paas_rbac/server
// mysqldump -u root -p paas_rbac > paas_rbac_20190728.sql;
//
// require_once('./yyq_frame.php');
require 'vendor/autoload.php';


// 如果需要使用数据库，可以在这里配置
$g_mysql = comm_create_default_mysql( 'localhost', 'paas_rbac', 'root', 'yyqet' );


// 返回值：0 == 用户不存在；大于 0 == 用户 id；-1 == 口令错误；-2 == 帐号未激活
function db_check_user_password( $table_name, $primary_key_name, $email, $password )
{
    global $g_mysql;
    $sql = "SELECT id_user,name,email,salt,password,state FROM ac_user WHERE email = ? OR mobile = ? OR name = ? LIMIT 1";
    $bind_param = array($email, $email, $email);
    $rows = $g_mysql->selectData($sql, $bind_param);
    if( !isset($rows[0]) )
        return 0;

    $user = $rows[0];
    if( $user['salt'] == '' && $user['password'] == '' && $password == '' )
        return (int)$user['id_user'];

    $password_hash = comm_get_password_hash( $password, $user['salt'] );
    if( $user['password'] != $password_hash )
        return -1;

    if( intval($user['state']) != 1 )
        return -2;

    return (int)$user['id_user']; 
}

function db_get_user_all_info( $table_name, $primary_key_name, $id_user, $wx_openid = '', $email = '' )
{
    global $g_mysql;

    $fields = array();
    $bind_param = array();
    if( 0 < intval($id_user) )
    {
        $fields[0] = $primary_key_name;
        $bind_param[0] = $id_user;
    }
    else if( 0 < strlen($wx_openid) )
    {
        $fields[0] = 'weixinopenid';
        $bind_param[0] = $wx_openid;
    }
    else if( 0 < strlen($email) )
    {
        $fields[0] = 'email';
        $bind_param[0] = $email;
    }    

    $rows = $g_mysql->selectDataEx($table_name, $fields, $bind_param);
    if( !isset($rows[0]) )
        return array($primary_key_name => 0);

    return $rows[0];
}

function db_get_user_info( $table_name, $primary_key_name, $id_user, $wx_openid = '', $email = '' )
{
    $user_info = db_get_user_all_info( $table_name, $primary_key_name, $id_user, $wx_openid, $email);
    unset( $user_info['salt'] );
    unset( $user_info['password'] );

    return $user_info;
}

function db_set_user_password( $table_name, $primary_key_name, $id_user, $salt, $password )
{
    return db_update_data( $table_name, array('salt', 'password'), 
        $primary_key_name . '= ?', array($salt, $password, $id_user) );
}

// 查看是否有其他用户存在同样的值
function db_get_other_object_info( $table_name, $field_name, $field_value, $primary_key_name, $self_primary_key_value )
{
    $sql = "SELECT * FROM {$table_name} WHERE {$field_name} = ? AND {$primary_key_name} != ?";
    $rows = db_select_data( $sql, array($field_value, $self_primary_key_value) );
    if( !isset($rows[0]) )
        return array($primary_key_name => 0);

    unset( $rows[0]['salt'] );
    unset( $rows[0]['password'] );
    return $rows[0];    
}

function db_get_some_table_info( $table_name, $field_name, $field_value, $primary_key_name, $primary_key_value = 0 )
{
    $sql = "SELECT * FROM {$table_name} WHERE {$field_name} = ? LIMIT 1";
    $bind_param = array( $field_value );
    if( 1 > strlen($field_value) )
    {
        $sql = "SELECT * FROM {$table_name} WHERE {$primary_key_name} = ? LIMIT 1";
        $bind_param = array( $primary_key_value );
    }

    $rows = db_select_data($sql, $bind_param);
    if( !isset($rows[0]) )
        return array( $primary_key_name => 0 );

    return $rows[0];
}

function db_delete_rule( $id_rule )
{
    $sql = "DELETE A.*, B.* FROM ac_rule AS A, ac_rule_resource_privilege AS B
         WHERE B.id_rule = A.id_rule AND A.id_rule = ?";
    $stmt = NULL;
    return db_execute_sql($stmt, $sql, array( $id_rule ) );
}

function db_get_user_resource_privilege( $table_name, $primary_key_name, $id_user )
{
    global $g_mysql;
    $sql = "SELECT C.* FROM ac_rule_resource_privilege AS C, {$table_name} AS D 
            WHERE C.id_rule = D.id_rule AND D.{$primary_key_name} = ?";
    
    $bind_param = array( $id_user );

    $rows = $g_mysql->$g_mysql($sql, $bind_param);

    $user_privilege['resource_privilege'] = $rows;

    $privilege = [];
    foreach( $rows as $row )
    {
        $privilege[] = $row['id_privilege'];
    }

    $user_privilege['privileges'] = db_expand_all_privilege( $privilege );

    return $user_privilege;
}

// 展开权限：添加所有的子权限。只做一级子权限查找，不会递归查找孙权限。
// 入参为：[id_privilege, id_privilege...]
// 返回值为：ac_privilege 表的多条记录
function db_expand_all_privilege( $privilege )
{
    $id_privilege_str = implode(',', $privilege);
    $sql = "SELECT * FROM ac_privilege WHERE id_privilege IN ({$id_privilege_str}) OR id_father IN ({$id_privilege_str})";

    return db_select_data($sql);
}

function db_add_admin_operation_log( $id_admin, $id_enterprise, $action, $target_id, $target_type, $description )
{
    return db_insert_data( 'admin_operation_log', 
        array('id_admin', 'id_enterprise', 'action', 'target_id', 'target_type', 'description'),
        array($id_admin, $id_enterprise, $action, $target_id, $target_type, $description) );
}

function db_create_website_user_tables( $id_website )
{
    $sql_array = array();
    $sql_array[] = sprintf( CREATE_USER_TABLE, $id_website );
    $sql_array[] = sprintf( CREATE_USER_RULE_TABLE, $id_website );

    return db_do_transaction( $sql_array );
}

?>