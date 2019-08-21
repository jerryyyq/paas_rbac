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
function db_check_user_password( $email, $password )
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

function db_get_user_all_info( $id_user, $wx_openid = '', $email = '' )
{
    global $g_mysql;

    $fields = array();
    $bind_param = array();
    if( 0 < intval($id_user) )
    {
        $fields[0] = 'id_user';
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

    $rows = $g_mysql->selectDataEx('ac_user', $fields, $bind_param);
    if( !isset($rows[0]) )
        return array('id_user' => 0);

    return $rows[0];
}

function db_get_user_info( $id_user, $wx_openid = '', $email = '' )
{
    $user_info = db_get_user_all_info( $id_user, $wx_openid, $email);
    unset( $user_info['salt'] );
    unset( $user_info['password'] );

    return $user_info;
}

function db_set_user_password( $id_user, $salt, $password )
{
    global $g_mysql;

    return $g_mysql->updateData( 'ac_user', array('salt', 'password'), array($salt, $password), 
        'id_user = ?', array($id_user) );
}

// 查看是否有其他用户存在同样的值
function db_get_other_object_info( $table_name, $field_name, $field_value, $primary_key_name, $self_primary_key_value )
{
    global $g_mysql;

    $sql = "SELECT * FROM {$table_name} WHERE {$field_name} = ? AND {$primary_key_name} != ?";
    $rows = $g_mysql->selectData( $sql, array($field_value, $self_primary_key_value) );
    if( !isset($rows[0]) )
        return array($primary_key_name => 0);

    unset( $rows[0]['salt'] );
    unset( $rows[0]['password'] );
    return $rows[0];    
}

// resource_type: 0 all, 1 system_admin, 2 enterprise_admin, 3 user
function db_get_user_resource_privilege( $id_user, $resource_type = 0 )
{
    global $g_mysql;
    $sql = "SELECT A.resource_type, A.id_resource, B.id_privilege FROM ac_user_resource_rule AS A, ac_rule_privilege AS B 
            WHERE A.id_rule = B.id_rule AND A.id_user = ?";

    if( $resource_type > 0 )
        $sql = $sql . " AND A.resource_type = {$resource_type}";

    $bind_param = array( $id_user );

    $rows = $g_mysql->selectData($sql, $bind_param);

    $result = [];
    foreach( $rows as $row )
    {
        $one_privileges = db_expand_one_privilege($row['resource_type'], $row['id_resource'], $row['id_privilege'], true);
        array_push($result, ...$one_privileges);
    }

    return $result;
}

// 展开权限：递归查找一个权限及其下的所有子孙权限。
// 入参为：id_privilege
// 返回值为：ac_privilege 表的多条记录
function db_expand_one_privilege( $resource_type, $id_resource, $id_privilege, $include_self = false )
{
    global $g_mysql;
    $result = [];

    $sql = "SELECT resource_type = {$resource_type}, id_resource = {$id_resource}, * FROM ac_privilege WHERE id_father = ?";
    $bindParam = array($id_privilege);

    if( $include_self )
    {
        $sql = "SELECT resource_type = {$resource_type}, id_resource = {$id_resource}, * FROM ac_privilege WHERE id_privilege = ? OR id_father = ?";
        $bindParam = array($id_privilege, $id_privilege);
    }

    $privileges = $g_mysql->selectData($sql, $bindParam);
    array_push($result, ...$privileges);

    foreach( $privileges as $privilege )
    {    
        if( $privilege['id_privilege'] == $id_privilege || 0 == intval($privilege['have_child']) )
            continue;

        $result_child = db_expand_one_privilege( $privilege['id_privilege'] );
        array_push($result, ...$result_child);
    }

    return $result;
}

// 展开权限：添加所有的子权限。只做一级子权限查找，不会递归查找孙权限。已废弃
// 入参为：[id_privilege, id_privilege...]
// 返回值为：ac_privilege 表的多条记录
function db_expand_all_privilege( $privilege )
{
    $id_privilege_str = implode(',', $privilege);
    $sql = "SELECT * FROM ac_privilege WHERE id_privilege IN ({$id_privilege_str}) OR id_father IN ({$id_privilege_str})";

    return db_select_data($sql);
}

function db_add_sys_operation_log( $id_user, $action, $target_id, $target_type, $description )
{
    global $g_mysql;
    return $g_mysql->insertData( 'ac_sys_operation_log', 
        array('id_user', 'action', 'target_id', 'target_type', 'description'),
        array($id_user, $action, $target_id, $target_type, $description) );
}

function db_add_enterprise_operation_log( $id_enterprise, $id_user, $action, $target_id, $target_type, $description )
{
    global $g_mysql;
    return $g_mysql->insertData( 'ac_enterprise_operation_log', 
        array('id_enterprise', 'id_user', 'action', 'target_id', 'target_type', 'description'),
        array($id_enterprise, $id_user, $action, $target_id, $target_type, $description) );
}

function db_create_website_user_tables( $id_website )
{
    $sql_array = array();
    $sql_array[] = sprintf( CREATE_USER_TABLE, $id_website );
    $sql_array[] = sprintf( CREATE_USER_RULE_TABLE, $id_website );

    return db_do_transaction( $sql_array );
}

?>