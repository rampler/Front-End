<?php
/**
 * Created by IntelliJ IDEA.
 * User: Mateusz
 * Date: 2015-05-11
 * Time: 11:38
 */
    include(__DIR__ . '/' . 'lib' . '/' . 'JsonToPostgres.php' );

    error_reporting(E_ERROR | E_PARSE);

    require("getDatabase.php");

    $jtp = new JsonToPostgres("./lib/config.json");

    $sqls = $jtp->createSqlStatements('{"roadSegment":['.json_encode($_POST['json']).']}');

    $result = pg_query($dbconn, "BEGIN TRANSACTION");
    $haveErrors = ($result)?false:true;
        foreach ($sqls as $sql) {
            $result = pg_query($dbconn, $sql);
            if (!$result)
                $haveErrors = true;
        }
    $result = pg_query($dbconn, "COMMIT");
    $haveErrors = ($result)?$haveErrors:true;

    if(!$haveErrors)
        echo '{"type":"success","message":"<strong>Sukces!</strong> Pomyślnie dodano segment drogi!"}';
    else
        echo '{"type":"danger","message":"Błąd podczas dodawania drogi!"}';
?>