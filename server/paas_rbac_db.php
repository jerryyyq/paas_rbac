<?php
require_once('./yyq_frame.php');

define( 'CREATE_USER_TABLE', "CREATE TABLE `user_%s` (
  `id_user` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(45) DEFAULT NULL COMMENT '用户名',
  `email` varchar(45) DEFAULT NULL,
  `mobile` varchar(45) DEFAULT NULL,
  `salt` varchar(128) DEFAULT NULL,
  `password` varchar(512) DEFAULT NULL,
  `real_name` varchar(128) DEFAULT NULL COMMENT '真实姓名',
  `pen_name` varchar(128) DEFAULT NULL COMMENT '笔名，只有成为作者才有效。',
  `state` int(11) DEFAULT '0' COMMENT '用户的状态：例如是否激活、注销等等',
  `id_channel` int(11) DEFAULT '0' COMMENT '是从哪个渠道加过来的。0 为非渠道用户。',
  `oauth_platform_type` varchar(128) DEFAULT NULL COMMENT '第三方登录平台类型。‘’ 和 ‘0’ 表示没有第三方登录平台关联帐号；‘1’ 是微信 unionid；‘2’是微信 openid；''3''是 QQ；‘4’是新浪；',
  `wx_unionid` varchar(128) DEFAULT NULL,
  `wx_openid` varchar(128) DEFAULT NULL,
  `qq_openid` varchar(128) DEFAULT NULL,
  `sina_openid` varchar(128) DEFAULT NULL,
  `token` varchar(512) DEFAULT NULL COMMENT '用于跨站点统一登录，无此需求可以忽略。',
  `token_create_time` datetime DEFAULT NULL COMMENT 'token 创建时间',
  `registe_date` datetime DEFAULT CURRENT_TIMESTAMP COMMENT '注册时间',
  `email_verify_state` int(11) DEFAULT '0' COMMENT '邮件地址校验状态。0=未校验；1=校验成功；2=已发校验码。',
  `email_verify_code` varchar(128) DEFAULT NULL COMMENT '邮件地址校验码。',
  `email_verify_code_send_time` datetime DEFAULT NULL COMMENT '邮件地址校验码发送时间。',
  `mobile_verify_state` int(11) DEFAULT '0' COMMENT '手机校验状态。0=未校验；1=校验成功；2=已发校验码。',
  `mobile_verify_code` varchar(128) DEFAULT NULL COMMENT '手机校验码。',
  `mobile_verify_code_send_time` datetime DEFAULT NULL COMMENT '手机校验码发送时间。',
  PRIMARY KEY (`id_user`),
  UNIQUE KEY `email_UNIQUE` (`email`),
  UNIQUE KEY `mobile_UNIQUE` (`mobile`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;" );

define( 'CREATE_USER_RULE_TABLE', "CREATE TABLE `ac_user_rule_%s` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `id_user` int(11) DEFAULT NULL,
    `id_rule` int(11) DEFAULT NULL,
    `description` varchar(256) DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `index2` (`id_user`,`id_rule`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;" );


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

function db_get_user_all_info( $table_name, $primary_key_name, $id_user, $wx_unionid = '', $email = '' )
{
    $sql = "SELECT * FROM {$table_name} WHERE ";

    $bind_param = array();
    if( 0 < intval($id_user) )
    {
        $sql = $sql . $primary_key_name . ' = ?';
        $bind_param[0] = $id_user;
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

function db_get_user_info( $table_name, $primary_key_name, $id_user, $wx_unionid = '', $email = '' )
{
    $user_info = db_get_user_all_info( $table_name, $primary_key_name, $id_user, $wx_unionid, $email);
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
    $sql = "SELECT C.* FROM ac_rule_resource_privilege AS C, {$table_name} AS D 
            WHERE C.id_rule = D.id_rule AND D.{$primary_key_name} = ?";
    
    $bind_param = array( $id_user );

    $rows = db_select_data($sql, $bind_param);

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