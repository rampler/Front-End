<?php
/**
 * Created by IntelliJ IDEA.
 * User: Mateusz
 * Date: 2015-05-07
 * Time: 19:38
 */

    $dbconn = pg_connect("host=localhost dbname=frontend user=frontend password=frontend")
    or die("Can't connect to database".pg_last_error());

    $clickedPointLat = $_GET['lat'];
    $clickedPointLon = $_GET['lon'];
    $searchingDistance = $_GET['dist'];

    //Walidacja p�l przed rozpocz�ciem dzia�a�
    if (preg_match('/^[0-9]+([.][0-9]+){0,1}$/', $clickedPointLat) && preg_match('/^[0-9]+([.][0-9]+){0,1}$/', $clickedPointLat) && preg_match('/^[0-9]+([.][0-9]+){0,1}$/', $clickedPointLat) ) {
        $result = pg_prepare($dbconn, "roadSegmentQuery", 'select * from roadsegment where id in (select roadsegmentid from roadsegmentcoordinates where point('.$clickedPointLat.','.$clickedPointLon.') <-> coordinates < '.$searchingDistance.' order by point('.$clickedPointLat.','.$clickedPointLon.') <-> coordinates asc limit 1)');
        $result = pg_execute($dbconn, "roadSegmentQuery", array());

        $roadSegment = null;
        while($rek = pg_fetch_array($result)) {
            $roadSegment['id'] = $rek['id'];
            $roadSegment['street'] = $rek['street'];
            $roadSegment['mainLightningClass'] = $rek['mainLightningClass'];
            $roadSegment['desc'] = $rek['desc'];
            $roadSegment['lampArrangement'] = $rek['lampArrangement'];
        }

        $result = pg_prepare($dbconn, "roadSectionQuery", "select * from roadsection where roadsegmentid = '".$roadSegment['id']."'");
        $result = pg_execute($dbconn, "roadSectionQuery", array());

        $sectionsArray = array();
        while($rek = pg_fetch_array($result)) {
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

        $result = pg_prepare($dbconn, "coordinatesQuery", "select * from roadsegmentcoordinates where roadsegmentid = '".$roadSegment['id']."'");
        $result = pg_execute($dbconn, "coordinatesQuery", array());

        $coordinatesArray = array();
        while($rek = pg_fetch_array($result)) {
            $coordinates['coordinates'] = $rek['coordinates']; //TODO zamiana na lat i lon
            $coordinates['elev'] = $rek['elev'];
            $coordinates['order'] = $rek['order'];
            $coordinates['group'] = $rek['group'];
            $coordinatesArray[] = $coordinates;
        }

        $roadSegment['coordinates'] = $coordinatesArray;

//        print_r($roadSegment); //TODO zwraca� segment w json
        echo json_encode($roadSegment);
    } else {
        echo 'B��dne parametry zapytania'; //TODO zwraca� JSON b��du
    }


?>