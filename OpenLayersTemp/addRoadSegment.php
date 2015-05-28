<?php
/**
 * Created by IntelliJ IDEA.
 * User: Mateusz
 * Date: 2015-05-11
 * Time: 11:38
 */
    include(__DIR__ . '/' . 'lib' . '/' . 'JsonToPostgres.php' );

    error_reporting(E_ERROR | E_PARSE);

    $dbconn = pg_connect("host=localhost dbname=frontend2 user=frontend password=frontend")
    or die("Can't connect to database".pg_last_error());

    $jtp = new JsonToPostgres("./lib/config.json");

    $sqls = $jtp->createSqlStatements(json_encode($_POST['json']));

    $haveErrors = false;
        foreach ($sqls as $sql) {
            $result = pg_query($dbconn, $sql);
            if (!$result)
                $haveErrors = true;
        }

    if(!$haveErrors)
        echo '{"type":"success","message":"<strong>Sukces!</strong> Pomyślnie dodano segment drogi!"}';
    else
        echo '{"type":"danger","message":"Błąd podczas dodawania drogi!"}';
?>