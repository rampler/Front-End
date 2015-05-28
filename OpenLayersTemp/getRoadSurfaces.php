<?php
/**
 * Created by IntelliJ IDEA.
 * User: Mateusz
 * Date: 2015-05-28
 * Time: 19:27
 */

    require("getDatabase.php");

    $result = pg_query($dbconn, "SELECT id FROM roadsurface");

    $counter = 0;
    while($rek = pg_fetch_array($result)) {
        $surfaces[$counter] = $rek[0];
        $counter++;
    }

    $json['enum'] = $surfaces;

    echo json_encode($json);
?>