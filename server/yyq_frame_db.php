<?php
/**
* Author: yangyuqi
* Date: 2017/08/27
*/

include_once('yyq_frame_config.php');
include_once('yyq_frame_log.php');

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
    global $debug;
    if( $debug )
    {
        echo $sql, "\n";
        print_r($bind_param);
    }

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

function db_delete_data($table, $where, $values)
{
    $sql = "DELETE FROM {$table} WHERE {$where}";
    $stmt = NULL;
    return db_execute_sql($stmt, $sql, $values );
}

function db_update_data_ex( $table, $row, $primary_key_name )
{
    $where = $primary_key_name . ' = ?';
    $fields = [];
    $values = [];

    foreach( $row as $key => $value )
    {
        if( $key === $primary_key_name )
            continue;

        $fields[] = $key;
        $values[] = $value;
    }

    return db_update_data( $table, $fields, $where, $values );
}

function db_insert_data_ex( $table, &$row, $primary_key_name )
{
    $fields = [];
    $values = [];

    foreach( $row as $key => $value )
    {
        if( $key === $primary_key_name )
            continue;

        $fields[] = $key;
        $values[] = $value;
    }

    $row[$primary_key_name] = db_insert_data( $table, $fields, $values );
    return $row[$primary_key_name];
}


function db_do_transaction( $sql_array )
{
    try
    {
        $conn = db_get_Connect();
        $conn->beginTransaction();

        foreach( $sql_array as $sql )
        {
            $affected_rows = $conn->exec( $sql );
            if(!$affected_rows)
                throw new PDOException('执行：'. $sql . ' 失败。');
        }

        return $conn->commit();
    }
    catch(PDOException $ex)
    {
        $conn->rollBack();
        log_error( 'db_do_transaction except: ' . $ex->getMessage() );
        return false;
    }     
}