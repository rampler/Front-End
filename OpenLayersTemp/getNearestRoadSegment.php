<?php
/**
 * Created by IntelliJ IDEA.
 * User: Mateusz
 * Date: 2015-05-07
 * Time: 19:38
 */

    $dbconn = pg_connect("host=localhost dbname=frontend2 user=frontend password=frontend")
    or die("Can't connect to database".pg_last_error());

    $clickedPointLat = $_GET['lat'];
    $clickedPointLon = $_GET['lon'];
    $searchingDistance = $_GET['dist'];

    //Walidacja pól przed rozpoczęciem działań
    if ($clickedPointLat != null && $clickedPointLon != null && $searchingDistance != null && preg_match('/^[0-9]+([.][0-9]+){0,1}$/', $clickedPointLat) && preg_match('/^[0-9]+([.][0-9]+){0,1}$/', $clickedPointLat) && preg_match('/^[0-9]+([.][0-9]+){0,1}$/', $clickedPointLat) ) {
//        $startTime = round(microtime(true)*1000); //TODO - test time
        $result = pg_prepare($dbconn, "roadSegmentQuery", 'select * from roadsegment where id in (select roadsegmentid from roadsegmentcoordinates where point('.$clickedPointLat.','.$clickedPointLon.') <-> coordinates < '.$searchingDistance.' order by point('.$clickedPointLat.','.$clickedPointLon.') <-> coordinates asc limit 1)');
        $result = pg_execute($dbconn, "roadSegmentQuery", array());

        $roadSegment = null;
        while($rek = pg_fetch_array($result)) {
            $roadSegment['id'] = $rek['id'];
            $roadSegment['street'] = $rek['street'];
            $roadSegment['mainLightningClass'] = $rek['mainlightingclass'];
            $roadSegment['desc'] = $rek['desc'];
            $roadSegment['lampArrangement'] = $rek['lamparrangement'];
        }

        if($roadSegment != null) {
            $result = pg_prepare($dbconn, "roadSectionQuery", "select * from roadsection where roadsegmentid = '" . $roadSegment['id'] . "'");
            $result = pg_execute($dbconn, "roadSectionQuery", array());

            $sectionsArray = array();
            while ($rek = pg_fetch_array($result)) {
                $section['id'] = $rek['id'];
                $section['idx'] = $rek['idx'];
                $section['type'] = $rek['type'];
                $section['widthStart'] = $rek['widthstart'];
                $section['widthEnd'] = $rek['widthend'];
                $section['elevationStart'] = $rek['elevationstart'];
                $section['elevationEnd'] = $rek['elevationend'];
                $section['roadSurfaceId'] = $rek['roadsurfaceid'];
                $section['lightingClassId'] = $rek['lightingclassid'];
                $section['numberOfLines'] = null; //TODO - kolejny request
                $sectionsArray[] = $section;
            }
            $roadSegment['roadSection'] = $sectionsArray;

            $result = pg_prepare($dbconn, "coordinatesQuery", "select * from roadsegmentcoordinates where roadsegmentid = '" . $roadSegment['id'] . "'");
            $result = pg_execute($dbconn, "coordinatesQuery", array());

            $coordinatesArray = array();
            while ($rek = pg_fetch_array($result)) {
                $coordinates['lat'] = strtok(substr($rek['coordinates'], 1, strlen($rek['coordinates']) - 2), ",");
                $coordinates['lon'] = strtok(",");
                $coordinates['elev'] = $rek['elev'];
                $coordinates['order'] = $rek['order'];
                $coordinates['group'] = $rek['group'];
                $coordinatesArray[] = $coordinates;
            }
//        echo (round(microtime(true)*1000)-$startTime); //TODO - test time

            $roadSegment['coordinates'] = $coordinatesArray;
            echo json_encode($roadSegment);
        }
        else
            echo '{"error":"Nie znaleziono segmentu drogi w pobliżu wskazanego miejsca"}';
    } else
        echo '{"error":"Błędne parametry zapytania"}';


?>