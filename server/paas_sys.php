<?php
// 作者：杨玉奇
// nginx1.14 + php7.2 安装配置参考： https://blog.csdn.net/Ersan_Yi/article/details/82107552
// 运行时错误在： /var/log/nginx
//
//
// 命令行调试： $ php paas_rbac.php debug
// php 命令行交互测试：$ php -a
///////////////////////////////////////////////////////////////////////////////////////////

// require 'vendor/autoload.php';
require_once('./paas_private.php');

use minimum_frame\YLog;

// 路由函数表
$route_functions = array(
    'test',
    'login',
    'change_password',      // realize in paas_common.php
    'have_privilege',
    'have_resource_privilege',

    'privilege_all_get',
    'privilege_info_get',
    'privilege_add',
    'privilege_delete',
    'rule_all_get',
    'rule_info_get',
    'rule_add',
    'rule_delete',
    'rule_privilege_all_get',
    'rule_privilege_info_get',
    'rule_privilege_add',
    'rule_privilege_delete',
    'user_resource_rule_all_get',
    'user_resource_rule_info_get',
    'user_resource_rule_add',
    'user_resource_rule_delete',

    'user_info_get',
    'user_add',
    'user_delete',
    'user_modify',

    'enterprise_symbol_name_exist',
    'enterprise_info_get',
    'enterprise_add',
    'enterprise_delete',
    'enterprise_modify',
);

// 调用主路由函数
comm_frame_main( $route_functions );

////////////////////////////////////////// 路由接口函数实现 /////////////////////////////////////////////////
//////// 当前用户登录及权限检查 ////////

function test( $args )
{
    $result = array( 'err' => 0, 'err_msg' => '', 'data' => 'Hello Pass_rbac! you call test.' );
    $result['args'] = $args;
    return $result;
}

function login( $args )
{
    $result = comm_check_parameters( $args, array('email', 'password') );
    if( 0 != $result['err'] )
        return $result;

    $result = __do_login( 1, $args['email'], $args['password'] );
    return $result;
}

function change_password( $args )
{
    $result = comm_check_parameters( $args, array('old_password', 'new_password') );
    if( 0 != $result['err'] )
        return $result;

    if( 1 > __session_get_user_id() )
    {
        $result['err'] = -4;
        $result['err_msg'] = '请先登录';
        return $result;
    }

    $user_info = session_get_user_info();
    $id_user = db_check_user_password( $user_info['email'], $args['old_password'] );
    if( 0 < $id_user )
    {
        $result['err'] = -2;
        $result['err_msg'] = '密码错误';
        return $result;
    }
    
    $salt = '';
    $password = comm_get_password_hash( $args['new_password'], $salt );

    if( !db_set_user_password($id_user, $salt, $password) )
    {
        $result['err'] = -10;
        $result['err_msg'] = '操作失败';

    }
    
    return $result;    
}

// 检查当前用户是否有某个权限
function have_privilege( $args )
{
    $result = comm_check_parameters( $args, array('privilege_name') );
    if( 0 != $result['err'] )
        return $result;

    $result[ $args['privilege_name'] ] = __have_privilege_ex( $args['privilege_name'] );
    return $result;
}

// 检查当前用户是否有某个资源的权限
function have_resource_privilege( $args )
{
    $result = comm_check_parameters( $args, array('id_resource', 'privilege_name') );
    if( 0 != $result['err'] )
        return $result;

    $result['have'] = __have_resource_privilege_ex($args['id_resource'], $args['privilege_name']);
    return $result;
}


//////// 数据库表操作 ////////
function privilege_all_get( $args )
{
    global $g_mysql;
    $result = __check_parameters_and_privilege( $args, array(), 'privilege_manage' );
    if( 0 != $result['err'] )
        return $result;

    $result['privilege_list'] = $g_mysql->selectDataEx( 'ac_privilege' );
    return $result;
}

function privilege_info_get( $args )
{
    global $g_mysql;
    $result = comm_check_parameters( $args, array('name') );
    if( 0 != $result['err'] )
        return $result;

    //$result['privilege_info'] = db_get_some_table_info( 'ac_privilege', 'name', $args['name'], 'id_privilege' );
    $result['privilege_info'] = $g_mysql->selectOne( 'ac_privilege', array('name'), array($args['name']) );
    return $result;
}

function privilege_add( $args )
{
    global $g_mysql;
    $result = __check_parameters_and_privilege( $args, array('id_father', 'have_child', 'name', 'show_name'), 'privilege_manage' );
    if( 0 != $result['err'] )
        return $result;

    $privilege_info = $g_mysql->selectOne( 'ac_privilege', array('name'), array($args['name']) );
    if( 0 < count($privilege_info) )
    {
        $result['err'] = -102;
        $result['err_msg'] = 'name 已存在，请换一个';
        return $result;
    }

    $result['id_privilege'] =  $g_mysql->insertDataEx( 'ac_privilege', $args, 'id_privilege' );
    
    // 添加操作日志
    db_add_sys_operation_log( $_SESSION['id_user'], 'privilege_add', $result['id_privilege'], 100, '添加权限：' . $args['name'] );
    return $result;
}

function privilege_delete( $args )
{
    global $g_mysql;
    $result = __check_parameters_and_privilege( $args, array('name'), 'privilege_manage' );
    if( 0 != $result['err'] )
        return $result;

    // 不存在，直接返回成功
    $privilege_info = $g_mysql->selectOne( 'ac_privilege', array('name'), array($args['name']) );
    if( 1 > count($privilege_info) )
        return $result;

    // 检查该 privilege 是否被引用
    $count = $g_mysql->getCount( 'ac_rule_privilege', 'id_privilege = ?', array($privilege_info['id_privilege']) );
    if( 0 < $count )
    {
        $result['err'] = -104;
        $result['err_msg'] = "{$args['name']} 还在被引用，不能删除";
        return $result;       
    }

    // 获得本权限及其所有子孙权限
    $one_privileges = db_expand_one_privilege(0, 0, $privilege_info['id_privilege'], true);
    $id_privilege_str = "";
    foreach( $one_privileges as $privilege )
    {
        if( 1 > strlen($id_privilege_str) )
            $id_privilege_str = $privilege['id_privilege']; 
        else
            $id_privilege_str = $id_privilege_str . ',' . $privilege['id_privilege'];
    }

    // 删除本权限及其所有子孙权限
    if( !$g_mysql->deleteData( 'ac_privilege', 'id_privilege IN (?)', array($id_privilege_str) ) )
    {
        $result['err'] = -103;
        $result['err_msg'] = '操作失败';
        return $result;       
    }

    // 添加操作日志
    db_add_sys_operation_log( $_SESSION['id_user'], 'privilege_delete', $privilege_info['id_privilege'], 101, '删除权限：' . $args['name'] );

    // 检查父权限是否还有其它子权限，没有的话要修改父权限的 have_child 值
    $one_privileges = db_expand_one_privilege(0, 0, $privilege_info['id_father'], false);
    if( 1 > count($one_privileges) )
    {
        if( !$g_mysql->updateDataEx('ac_privilege', array('id_privilege' => $privilege_info['id_father'], 'have_child' => 0), 'id_privilege') )
            comm_get_default_log()->logError( "update privilege: {$privilege_info['id_father']} have_chile => 0 Fail!" );
    }

    return $result;
}

function rule_all_get( $args )
{
    global $g_mysql;
    $result = __check_parameters_and_privilege( $args, array(), 'privilege_manage' );
    if( 0 != $result['err'] )
        return $result;

    $result['rule_list'] = $g_mysql->selectDataEx( 'ac_rule' );
    return $result;
}

function rule_info_get( $args )
{
    global $g_mysql;
    $result = comm_check_parameters( $args, array('name') );
    if( 0 != $result['err'] )
        return $result;

    $result['rule_info'] = $g_mysql->selectOne( 'ac_rule', array('name'), array($args['name']) );
    return $result;
}

function rule_add( $args )
{
    global $g_mysql;
    $result = __check_parameters_and_privilege( $args, array('resource_type', 'name', 'show_name'), 'privilege_manage' );
    if( 0 != $result['err'] )
        return $result;

    $rule_info = $g_mysql->selectOne( 'ac_rule', array('name'), array($args['name']) );
    if( 0 < count($rule_info) )
    {
        $result['err'] = -102;
        $result['err_msg'] = 'name 已存在，请换一个';
        return $result;
    }

    $result['id_rule'] = $g_mysql->insertDataEx( 'ac_rule', $args, 'id_rule' );
    
    // 添加操作日志
    db_add_sys_operation_log( $_SESSION['id_user'], 'rule_add', $result['id_rule'], 110, '添加角色：' . $args['name'] );
    return $result;
}

function rule_delete( $args )
{
    global $g_mysql;
    $result = __check_parameters_and_privilege( $args, array('name'), 'privilege_manage' );
    if( 0 != $result['err'] )
        return $result;
    
    // 不存在，直接返回成功
    $rule_info = $g_mysql->selectOne( 'ac_rule', array('name'), array($args['name']) );
    if( 1 > count($rule_info) )
        return $result;

    // 检查该 rule 是否被引用
    $count = $g_mysql->getCount( 'ac_user_resource_rule', 'id_rule = ?', array($rule_info['id_rule']) );
    if( 0 < $count )
    {
        $result['err'] = -104;
        $result['err_msg'] = "{$args['name']} 还在被引用，不能删除";
        return $result;       
    }

    if( !$g_mysql->deleteData( 'ac_rule', 'id_rule = ?', array($rule_info['id_rule']) ) )
    {
        $result['err'] = -103;
        $result['err_msg'] = '操作失败';
        return $result;       
    }

    // 添加操作日志
    db_add_sys_operation_log( $_SESSION['id_user'], 'rule_delete', $rule_info['id_rule'], 111, '删除角色：' . $args['name'] );
    return $result;    
}

function rule_privilege_all_get( $args )
{
    global $g_mysql;
    $result = __check_parameters_and_privilege( $args, array(), 'privilege_manage' );
    if( 0 != $result['err'] )
        return $result;

    $result['rule_privilege_list'] = $g_mysql->selectDataEx( 'ac_rule_privilege' );
    return $result;
}

function rule_privilege_info_get( $args )
{
    global $g_mysql;
    $result = comm_check_parameters( $args, array('id') );
    if( 0 != $result['err'] )
        return $result;

    $result['rule_privilege_info'] = $g_mysql->selectOne( 'ac_rule_privilege', array('id'), array($args['id']) );
    return $result;
}

function rule_privilege_add( $args )
{
    global $g_mysql;
    $result = __check_parameters_and_privilege( $args, array('id_rule', 'id_privilege'), 'privilege_manage' );
    if( 0 != $result['err'] )
        return $result;

    $rule_info = $g_mysql->selectOne( 'ac_rule', array('id_rule'), array($args['id_rule']) );
    if( 1 > count($rule_info) )
    {
        $result['err'] = -102;
        $result['err_msg'] = 'id_rule 不存在，请检查';
        return $result;
    }

    $privilege_info = $g_mysql->selectOne( 'ac_privilege', 'id_privilege', $args['id_privilege'] );
    if( 1 > count($privilege_info) )    
    {
        $result['err'] = -102;
        $result['err_msg'] = 'id_privilege 不存在，请检查';
        return $result;
    }

    // 如果已存在则直接返回
    $rule_privilege_info = $g_mysql->selectOne( 'ac_rule_privilege', 
        array('id_rule', 'id_privilege'), 
        array($args['id_rule'], $args['id_privilege']) );
    if( 0 < count($rule_privilege_info) )
    {
        $result['id'] = $rule_privilege_info['id'];
        return $result;
    }

    // 插入数据
    $result['id'] = $g_mysql->insertDataEx( 'ac_rule_privilege', $args, 'id' );
    
    // 添加操作日志
    db_add_sys_operation_log( $_SESSION['id_user'], 'rule_privilege_add', $result['id'], 120, 
        '添加角色权限：id_rule = ' . $args['id_rule'] . ' id_privilege = ' . $args['id_privilege'] );
    return $result;
}

function rule_privilege_delete( $args )
{
    $result = __check_parameters_and_privilege( $args, array('id'), 'privilege_manage' );
    if( 0 != $result['err'] )
        return $result;

    // 不存在，直接返回成功
    $rule_privilege_info = $g_mysql->selectOne( 'ac_rule_privilege', 'id', $args['id'] );
    if( 1 > count($rule_privilege_info) )
        return $result;

    if( !$g_mysql->deleteData( 'ac_rule_privilege', 'id=?', array($args['id']) ) )
    {
        $result['err'] = -103;
        $result['err_msg'] = '操作失败';
        return $result;       
    }

    // 添加操作日志
    db_add_sys_operation_log( $_SESSION['id_user'], 'rule_privilege_delete', $args['id'], 121, 
        '删除角色权限：id_rule = ' . $rule_privilege_info['id_rule'] . ' id_privilege = ' . $rule_privilege_info['id_privilege']  );
    return $result;
}

function user_resource_rule_all_get( $args )
{
    global $g_mysql;
    $result = __check_parameters_and_privilege( $args, array(), 'user_rule' );
    if( 0 != $result['err'] )
        return $result;
    
    if(isset($args['id_user']))
        $result['user_resource_rule_list'] = $g_mysql->selectDataEx( 'ac_user_resource_rule', array('id_user'), array($args['id_user']) );
    else
        $result['user_resource_rule_list'] = $g_mysql->selectDataEx( 'ac_user_resource_rule' );
    return $result;
}

function user_resource_rule_info_get( $args )
{
    global $g_mysql;
    $result = comm_check_parameters( $args, array('id') );
    if( 0 != $result['err'] )
        return $result;
    
    $result['user_resource_rule_info'] = $g_mysql->selectOne( 'ac_user_resource_rule', array('id'), array($args['id']) );
    return $result;
}

function user_resource_rule_add( $args )
{
    global $g_mysql;
    $result = __check_parameters_and_privilege( $args, array('id_user', 'id_rule', 'resource_type', 'id_resource'), 'user_rule' );
    if( 0 != $result['err'] )
        return $result;

    // 如果已存在则直接返回
    $user_rule_info = $g_mysql->selectOne( 'ac_user_resource_rule',
        array('id_user', 'id_rule', 'resource_type', 'id_resource'),
        array($args['id_user'], $args['id_rule'], $args['resource_type'], $args['id_resource']) );
    if( 0 < $user_rule_info['id'] )
    {
        $result['id'] = $user_rule_info['id'];
        return $result;
    }

    $user_info = $g_mysql->selectOne( 'ac_user', array('id_user'), array($args['id_user']) );
    if( 1 > count($user_info) )
    {
        $result['err'] = -102;
        $result['err_msg'] = 'id_user 不存在，请检查';
        return $result;
    }

    $rule_info = $g_mysql->selectOne( 'ac_rule', array('id_rule'), array($args['id_rule']) );
    if( 1 > count($rule_info) )
    {
        $result['err'] = -102;
        $result['err_msg'] = 'id_rule 不存在，请检查';
        return $result;
    }

    // 资源是否存在
    if( 1 < intval($args['resource_type']) )
    {
        $table_name = 'ac_enterprise';
        $primary_key_name = 'id_enterprise';
        if( 2 === intval($args['resource_type']) )
        {
            $table_name = 'ac_website';
            $primary_key_name = 'id_website';
        }

        $resource_info = $g_mysql->selectOne( $table_name, array($primary_key_name), array($args['id_resource']) );
        if( 1 > count($resource_info) )
        {
            $result['err'] = -102;
            $result['err_msg'] = 'id_resource 不存在，请检查';
            return $result;
        }
    }

    // 插入数据
    $result['id'] = $g_mysql->insertDataEx( 'ac_user_resource_rule', $args, 'id' );
    
    // 添加操作日志
    db_add_sys_operation_log( $_SESSION['id_user'], 'user_resource_rule_add', 
        $result['id'], 150, '添加用户角色：' . json_encode($args) );
    return $result;
}

function user_resource_rule_delete( $args )
{
    global $g_mysql;
    $result = __check_parameters_and_privilege( $args, array('id'), 'user_rule' );
    if( 0 != $result['err'] )
        return $result;

    // 不存在，直接返回成功
    $user_rule_info = $g_mysql->selectOne( 'ac_user_resource_rule', array('id'), array($args['id']) );
    if( 1 > count($user_rule_info) )
        return $result;

    if( !$g_mysql->deleteData( 'ac_user_resource_rule', 'id=?', array($args['id']) ) )
    {
        $result['err'] = -103;
        $result['err_msg'] = '操作失败';
        return $result;       
    }

    // 添加操作日志
    db_add_sys_operation_log( $_SESSION['id_user'], 'user_rule_delete', 
        $args['id'], 151, '删除用户角色：' . json_encode($args) );
    return $result;
}

function user_info_get( $args )
{
    $g_mysql;
    $result = __check_parameters_and_privilege( $args, array('id_user'), 'user_read' );
    if( 0 != $result['err'] )
        return $result;
    
    $result['user_info'] = $g_mysql->selectOne( 'ac_user', array('id_user'), array($args['id_user']) );
    return $result;
}

function user_add( $args )
{
    $g_mysql;
    $result = __check_parameters_and_privilege( $args, array('name', 'email', 'mobile', 'password'), 'user_add' );
    if( 0 != $result['err'] )
        return $result;

    $user_info = db_get_user_info( 0, '', $args['email'] );
    if( 0 < int($user_info['id_user']) )
    {
        $result['err'] = -102;
        $result['err_msg'] = 'email 已存在，请换一个';
        return $result;
    }

    // 计算 password
    $args['salt'] = '';
    $args['password'] = comm_get_password_hash( $args['password'], $args['salt'] );

    // 加入数据库
    $args['state'] = 1;
    $args['email_verify_state'] = 1;
    $args['mobile_verify_state'] = 1;
    $args['wx_unionid'] = '';
    $args['wx_openid'] = '';
    $result['id_user'] = $g_mysql->insertDataEx( $table_name, $args, 'id_user' );

    // 添加操作日志
    db_add_sys_operation_log( $_SESSION['id_user'], 'user_add', 
        $result['id_user'], 220, '添加用户：email = ' . $args['email'] );

    return $result;
}

function user_delete( $args )
{
    $g_mysql;
    $result = __check_parameters_and_privilege( $args, array('id_user'), 'user_delete' );
    if( 0 != $result['err'] )
        return $result;

    // 不存在，直接返回成功
    $user_info = db_get_user_info( $args['id_user'] );
    if( 1 > int($user_info['id_user']) )
        return $result;

    if( !$g_mysql->deleteData( 'ac_user', 'id_user=?', array($args['id_user']) ) )
    {
        $result['err'] = -103;
        $result['err_msg'] = '操作失败';
        return $result;       
    }

    // 删除 ac_user_resource_rule
    if( !$g_mysql->deleteData( 'ac_user_resource_rule', 'id_user=?', array($args['id_user']) ) )
    {
        comm_get_default_log()->logError( 'delete ac_user_resource_rule Fail! id_user = ' . $args['id_user'] );
    }

    // 添加操作日志
    db_add_sys_operation_log( $_SESSION['id_user'], 'user_delete', 
        $args['id_user'], 221, '删除用户： email = ' . $user_info['email'] );
    return $result;
}

function user_modify( $args )
{
    $g_mysql;
    $result = __check_parameters_and_privilege( $args, array('id_user'), 'user_modify' );
    if( 0 != $result['err'] )
        return $result;

    $result = __modify_user_info( $args );
    if( 0 != $result['err'] )
        return $result;

    // 添加操作日志
    db_add_sys_operation_log( $_SESSION['id_user'], 'user_modify', 
        $args['id_user'], 222, '修改用户信息： ' . json_encode($args) );
    return $result;
}

function enterprise_symbol_name_exist( $args )
{
    global $g_mysql;
    $result = comm_check_parameters( $args, array('symbol_name') );
    if( 0 != $result['err'] )
        return $result;
    
    $result['symbol_name'] = $args['symbol_name'];
    $result['exist'] = 0;
    $enterprise_info = $g_mysql->selectOne( 'ac_enterprise', array('symbol_name'), array($args['symbol_name']) );
    if( 0 < count($enterprise_info) )
        $result['exist'] = 1;

    return $result;
}

function enterprise_info_get( $args )
{
    global $g_mysql;
    $result = __check_parameters_and_resource_privilege( $args, array('id_enterprise'), $args['id_enterprise'], 'enterprise_read' );
    if( 0 != $result['err'] )
        return $result;

    $result['enterprise_info'] = $g_mysql->selectOne( 'ac_enterprise', array('id_enterprise'), array($args['id_enterprise']) );
    return $result;
}

function enterprise_add( $args )
{
    global $g_mysql;
    $result = __check_parameters_and_resource_privilege( $args, array('symbol_name', 'real_name'), $args['id_enterprise'], 'enterprise_add' );
    if( 0 != $result['err'] )
        return $result;

    $enterprise_info = $g_mysql->selectOne( 'ac_enterprise', 'symbol_name', $args['symbol_name'] );
    if( 0 < count($enterprise_info) )
    {
        $result['err'] = -102;
        $result['err_msg'] = '符号名已存在，请换一个';
        return $result;
    }

    $result['id_enterprise'] = $g_mysql->insertDataEx( 'ac_enterprise', $args, 'id_enterprise' );

    // 添加操作日志
    db_add_sys_operation_log( $_SESSION['id_user'], 'enterprise_add', 
        $result['id_enterprise'], 300, '添加企业： symbol_name = ' . $args['symbol_name'] );

    return $result;
}

function enterprise_delete( $args )
{
    global $g_mysql;
    $result = __check_parameters_and_resource_privilege( $args, array('id_enterprise'), $args['id_enterprise'], 'enterprise_delete' );
    if( 0 != $result['err'] )
        return $result;

    // 不存在，直接返回成功
    $enterprise_info = $g_mysql->selectOne( 'ac_enterprise', array('id_enterprise'), array($args['id_enterprise']) );
    if( 1 > count($enterprise_info) )
        return $result;

    if( !$g_mysql->deleteData( 'ac_enterprise', 'id_enterprise=?', array($args['id_enterprise']) ) )
    {
        $result['err'] = -103;
        $result['err_msg'] = '操作失败';
        return $result;       
    }

    // 添加操作日志
    db_add_sys_operation_log( $_SESSION['id_user'], 'enterprise_delete', 
        $args['id_enterprise'], 301, '删除企业： symbol_name = ' . $enterprise_info['symbol_name'] );
    return $result;    
}

function enterprise_modify( $args )
{
    global $g_mysql;
    $result = __check_parameters_and_resource_privilege( $args, array('id_enterprise'), $args['id_enterprise'], 'enterprise_modify' );
    if( 0 != $result['err'] )
        return $result;

    if( isset($user_info['symbol_name']) )
    {
        $other_enterprise_info = db_get_other_object_info( 'ac_enterprise', 'symbol_name', $args['symbol_name'], 'id_enterprise', $args['id_enterprise'] );
        if( 0 < int($other_enterprise_info['id_enterprise']) )
        {
            $result['err'] = -102;
            $result['err_msg'] = 'symbol_name 已存在，请换一个';
            return $result;
        }
    }

    if( !$g_mysql->updateDataEx( 'ac_enterprise', $args, 'id_enterprise' ) )
    {
        $result['err'] = -103;
        $result['err_msg'] = '操作失败';
    }

    // 添加操作日志
    db_add_sys_operation_log( $_SESSION['id_user'], 'enterprise_modify', 
        $args['id_enterprise'], 302, '修改企业信息： ' . json_encode($args) );
    return $result;
}

?>