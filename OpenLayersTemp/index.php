<?php
/**
 * Created by PhpStorm.
 * User: Adrian
 * Date: 2015-05-26
 * Time: 15:08
 */

//podlaczenie biblioteki
include(__DIR__ . '/' . 'lib' . '/' . 'JsonToPostgres.php' );

//przykladowy zagniezdzony json
//$insideArray = '{"roadSegment":[{"id": 10, "comment": "komentarz1", "coordinates":[{"lat":10, "lon":20}, {"lat":30, "lon":40}]}, {"id": 11, "comment": "komentarz2", "coordinates":[{"lat":50, "lon":60}, {"lat":70, "lon":80}]}]}';
$insideArray = '{"roadSegment":[{"id":"czarnowiejska-str","street":"Czarnowiejska","mainLightningClass":"test","desc":"Opis ulicy Czarnowiejskiej","lampArrangement":"SINGLE_SIDED_RIGHT","roadSection":[{"id":"chodnik","idx":1,"type":"walkway","numberOfLanes":1,"widthStart":2,"widthEnd":2,"elevationStart":1,"elevationEnd":1,"roadSurfaceId":"test2","lightingClassId":"test3"},{"id":"droga","idx":2,"type":"road","numberOfLanes":2,"widthStart":7,"widthEnd":7,"elevationStart":1,"elevationEnd":1,"roadSurfaceId":"test4","lightingClassId":"test5"}],"coordinates":[{"lat":"53.0948074429964","lon":"17.266904661209942","elev":"","order":"1","group":"1"},{"lat":"53.055205974222815","lon":"18.826963254959942","elev":"","order":"2","group":"1"},{"lat":"51.98568357340159","lon":"18.45342809870994","elev":"","order":"3","group":"1"}]}]}';
//$forDb = '{"roadSegmentCoordinates":[{"roadSegmentId": "id1", "lon":10, "lat":20, "elev": 1.8, "order": 1, "group": 11}, {"roadSegmentId": "id2", "lon":20, "lat":30, "elev": 2.8, "order": 2, "group": 22}]}';

$jtp = new JsonToPostgres();

$sqls = $jtp->createSqlStatements($insideArray);

//$dbconn = pg_connect("host=localhost dbname=magazynPostgis user=postgres password=root")
//or die('Nie można nawiązać połączenia: ' . pg_last_error());

foreach($sqls as $sql){
    echo $sql . '</br>';
//    $result = pg_query($sql) or die('Nieprawidłowe zapytanie: ' . pg_last_error());
}


