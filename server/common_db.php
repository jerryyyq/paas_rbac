<?php
/**
* Author: yangyuqi
* Date: 2017/08/27
*/

include_once('svr_config.php');

$pdo = null;

function db_get_Connect( )
{
    global $pdo;
    if( null == $pdo )
    {
        $pdo = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASS); 
        $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->exec('set names utf8');   // youlian is: 'set names gbk'  // 'set names utf8'
    }

    return $pdo;
}

function db_close_Connect( )
{
    global $pdo;
    $pdo = null;
}

function db_execute_sql(&$stmt, $sql, $bind_param = array() )
{
    // echo $sql;
    // print_r($bind_param);

    $stmt = db_get_Connect()->prepare($sql);
    $bool = false;
    if( 0 < count($bind_param) )
        $bool = $stmt->execute( $bind_param );
    else
        $bool = $stmt->execute( );

    return $bool;
}

function db_select_data( $sql, $bind_param = array() )
{
    $stmt = NULL;
    db_execute_sql($stmt, $sql, $bind_param);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function db_update_data( $table, $fields, $where, $values )
{
    $sql = "UPDATE {$table} SET";
    for( $i = 0; $i < count($fields); $i++ )
    {
        if( 0 === $i )
            $sql = $sql . " {$fields[$i]} = ?";
        else
            $sql = $sql . ", {$fields[$i]} = ?";
    }

    $sql = $sql . " WHERE {$where}";

    $stmt = NULL;
    return db_execute_sql($stmt, $sql, $values);
}

function db_insert_data( $table, $fields, $values )
{
    $sql = "INSERT INTO {$table} SET";
    for( $i = 0; $i < count($fields); $i++ )
    {
        if( 0 === $i )
            $sql = $sql . " {$fields[$i]} = ?";
        else
            $sql = $sql . ", {$fields[$i]} = ?";
    }

    $stmt = NULL;
    db_execute_sql($stmt, $sql, $values);

    return db_get_Connect()->lastInsertId();
}
