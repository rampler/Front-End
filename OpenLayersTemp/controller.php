<?php
/**
 * Created by IntelliJ IDEA.
 * User: Mateusz
 * Date: 2015-05-29
 * Time: 22:42
 */

include(__DIR__ . '/' . 'lib' . '/' . 'PostgreSQLDAO.php');

error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors', 1);

$dao = new PostgreSQLDAO();

switch ($_GET['action']) {
    case "addRoadSegment":
        $json = $_POST['json'];
        if(!$dao->isRoadSegmentIdExist($json['id'])) {
            if ($dao->addRoadSegment($json))
                echo '{"type":"success","message":"<strong>Sukces!</strong> Pomyślnie dodano segment drogi!"}';
            else
                echo '{"type":"danger","message":"Błąd podczas dodawania drogi!"}';
        }
        else
            echo '{"type":"danger","message":"Segment drogi o podanym id już istnieje!"}';
        break;

    case "saveRoadSegment":
        $json = $_POST['json'];
        $oldId = $_POST['oldId'];
        if($json['id'] == $oldId || !$dao->isRoadSegmentIdExist($json['id'])) {
            if ($dao->saveRoadSegment($json, $oldId))
                echo '{"type":"success","message":"<strong>Sukces!</strong> Pomyślnie zaktualizowano segment drogi!"}';
            else
                echo '{"type":"danger","message":"Błąd podczas zapisu segmentu drogi!"}';
        }
        else
            echo '{"type":"danger","message":"Segment drogi o podanym id już istnieje!"}';
        break;

    case "deleteRoadSegment":
        if ($dao->deleteRoadSegment($_POST['roadSegmentId']))
            echo '{"type":"success","message":"<strong>Sukces!</strong> Pomyślnie usunięto segment drogi!"}';
        else
            echo '{"type":"danger","message":"Błąd podczas usuwania drogis!"}';
        break;

    case "getNearestRoadSegment":
        $clickedPointLat = $_GET['lat'];
        $clickedPointLon = $_GET['lon'];
        $searchingDistance = $_GET['dist'];

        //Walidacja pól przed rozpoczęciem działań
        if ($clickedPointLat != null && $clickedPointLon != null && $searchingDistance != null && preg_match('/^[0-9]+([.][0-9]+){0,1}$/', $clickedPointLat) && preg_match('/^[0-9]+([.][0-9]+){0,1}$/', $clickedPointLat) && preg_match('/^[0-9]+([.][0-9]+){0,1}$/', $clickedPointLat)) {
            $roadSegmentJson = $dao->getNearestRoadSegment($clickedPointLat, $clickedPointLon, $searchingDistance);
            if ($roadSegmentJson)
                echo json_encode($roadSegmentJson);
            else
                echo '{"error":"Nie znaleziono segmentu drogi w pobliżu wskazanego miejsca"}';
        } else
            echo '{"error":"Błędne parametry zapytania"}';
        break;

    case "getRoadSurfaces":
        echo json_encode($dao->getRoadSurfaces());
        break;

    case "getLightningClasses":
        echo json_encode($dao->getLightningClasses());
        break;
}
