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
$json = '{"id":"skarbowa","street":"Skarbowa","mainLightingClass":"ME4A","desc":"Ulica Skarbowa","lampArrangement":"SINGLE_SIDED_RIGHT","roadSection":[{"id":"skarbowa-droga","idx":0,"type":"road","numberOfLanes":1,"widthStart":7,"widthEnd":7,"elevationStart":2,"elevationEnd":2,"roadSurfaceId":"rs-asphalt-R3","lightingClassId":"ME4A"},{"id":"skarbowa-chodnik","idx":1,"type":"walkway","numberOfLanes":2,"widthStart":7,"widthEnd":7,"elevationStart":2,"elevationEnd":2,"roadSurfaceId":"rs-asphalt-R3","lightingClassId":"ME4A"}],"coordinates":[{"lat":"50.06385293831357","lon":"19.92663413851398","elev":"","order":"1","group":"1"},{"lat":"50.06321586815241","lon":"19.92672533362049","elev":"","order":"2","group":"1"},{"lat":"50.06300236166496","lon":"19.926805799890936","elev":"","order":"3","group":"1"}]}';
$insideArray = '{"roadSegment":['.$json.']}';
$jtp = new JsonToPostgres("./lib/config.json");
echo $insideArray."<br /><br />";
$sqls = $jtp->createSqlStatements($insideArray);

foreach($sqls as $sql){
    echo $sql . '</br>';
}


