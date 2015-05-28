<?php
/**
 * Created by IntelliJ IDEA.
 * User: Mateusz
 * Date: 2015-05-07
 * Time: 19:38
 */

    require("getDatabase.php");

    $clickedPointLat = $_GET['lat'];
    $clickedPointLon = $_GET['lon'];
    $searchingDistance = $_GET['dist'];

    //Walidacja pól przed rozpoczęciem działań
    if ($clickedPointLat != null && $clickedPointLon != null && $searchingDistance != null && preg_match('/^[0-9]+([.][0-9]+){0,1}$/', $clickedPointLat) && preg_match('/^[0-9]+([.][0-9]+){0,1}$/', $clickedPointLat) && preg_match('/^[0-9]+([.][0-9]+){0,1}$/', $clickedPointLat) ) {
        $result = pg_prepare($dbconn, "roadSegmentQuery", "select * from roadsegment where id in ( SELECT roadsegmentid  FROM roadsegmentcoordinates group by roadsegmentid having ST_Distance(ST_TRANSFORM(ST_MakeLine(coordinates::geometry),2163), ST_TRANSFORM(ST_GeomFromText('POINT(".$clickedPointLon." ".$clickedPointLat.")',4326),2163)) < ".$searchingDistance." order by ST_Distance(ST_TRANSFORM(ST_MakeLine(coordinates::geometry),2163), ST_TRANSFORM(ST_GeomFromText('POINT(".$clickedPointLon." ".$clickedPointLat.")',4326),2163)) asc limit 1)");
        $result = pg_execute($dbconn, "roadSegmentQuery", array());

        $roadSegment = null;
        while($rek = pg_fetch_array($result)) {
            $roadSegment['id'] = $rek['id'];
            $roadSegment['street'] = $rek['street'];
            $roadSegment['mainLightingClass'] = $rek['mainlightingclass'];
            $roadSegment['desc'] = $rek['desc'];
            $roadSegment['lampArrangement'] = $rek['lamparrangement'];
        }

        if($roadSegment != null) {
            $result = pg_prepare($dbconn, "roadSectionQuery", "select * from roadsection where roadsegmentid = '" . $roadSegment['id'] . "'");
            $result = pg_execute($dbconn, "roadSectionQuery", array());

            $sectionsArray = array();
            while ($rek = pg_fetch_array($result)) {
                $section['id'] = $rek['id'];
                $section['idx'] = ($rek['idx'])?$rek['idx']:"0";
                $section['type'] = $rek['type'];
                $section['widthStart'] = $rek['widthstart'];
                $section['widthEnd'] = $rek['widthend'];
                $section['elevationStart'] = $rek['elevationstart'];
                $section['elevationEnd'] = $rek['elevationend'];
                $section['roadSurfaceId'] = $rek['roadsurfaceid'];
                $section['lightingClassId'] = $rek['lightingclassid'];
                $section['numberOfLanes'] = 0; //TODO - kolejny request
                $sectionsArray[] = $section;
            }
            $roadSegment['roadSection'] = $sectionsArray;

            $result = pg_prepare($dbconn, "coordinatesQuery", "select ST_X(coordinates::geometry) as lon, ST_Y(coordinates::geometry) as lat, elev, \"order\", \"group\" from roadsegmentcoordinates where roadsegmentid = '" . $roadSegment['id'] . "'");
            $result = pg_execute($dbconn, "coordinatesQuery", array());

            $coordinatesArray = array();
            while ($rek = pg_fetch_array($result)) {
                $coordinates['lat'] = $rek['lat'];
                $coordinates['lon'] = $rek['lon'];
                $coordinates['elev'] = $rek['elev'];
                $coordinates['order'] = $rek['order'];
                $coordinates['group'] = $rek['group'];
                $coordinatesArray[] = $coordinates;
            }

            $roadSegment['coordinates'] = $coordinatesArray;
            echo json_encode($roadSegment);

        }
        else
            echo '{"error":"Nie znaleziono segmentu drogi w pobliżu wskazanego miejsca"}';
    } else
        echo '{"error":"Błędne parametry zapytania"}';


?>