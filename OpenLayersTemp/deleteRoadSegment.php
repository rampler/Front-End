<?php
/**
 * Created by IntelliJ IDEA.
 * User: Mateusz
 * Date: 2015-05-11
 * Time: 11:38
 */

    require("getDatabase.php");

    error_reporting(E_ERROR | E_PARSE);

    $result = pg_query($dbconn, "DELETE FROM roadsegment WHERE id = '".$_POST['roadSegmentId']."'");

    if($result)
        echo '{"type":"success","message":"<strong>Sukces!</strong> Pomyślnie usunięto segment drogi!"}';
    else
        echo '{"type":"danger","message":"Błąd podczas usuwania drogis!"}';
?>