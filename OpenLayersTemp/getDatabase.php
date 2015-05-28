<?php
/**
 * Created by IntelliJ IDEA.
 * User: Mateusz
 * Date: 2015-05-28
 * Time: 19:50
 */

    $dbconn = pg_connect("host=localhost dbname=frontend2 user=frontend password=frontend")
    or die("Can't connect to database".pg_last_error());

//    throw new Exception("UNAUTHORIZED");
?>