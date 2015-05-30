<?php

/**
 * Created by IntelliJ IDEA.
 * User: Mateusz
 * Date: 2015-05-29
 * Time: 22:16
 */

include('JsonToPostgres.php');

class PostgreSQLDAO
{
    private $dbconn;

    /**
     * PostgreSQLDAO constructor.
     * Kontruktor tworzy połączenie z bazą danych.
     */
    public function __construct()
    {
        $this->dbconn = pg_connect("host=localhost dbname=frontend2 user=frontend password=frontend")
        or die("Can't connect to database" . pg_last_error());
    }

    /**
     * Metoda dodaje segment drogi zapisany w json.
     * Zwracany jest true gdy operacja się powiedzie lub false jak wystąpi błąd
     * @param $json
     * @return bool
     */
    public function addRoadSegment($json){
        $jtp = new JsonToPostgres("./lib/config.json");

        $sqls = $jtp->createSqlStatements('{"roadSegment":['.json_encode($json).']}');

        $result = pg_query($this->dbconn, "BEGIN TRANSACTION");
        $haveErrors = ($result)?false:true;
        foreach ($sqls as $sql) {
            $result = pg_query($this->dbconn, $sql);
            if (!$result)
                $haveErrors = true;
        }
        $result = pg_query($this->dbconn, "COMMIT");
        $haveErrors = ($result)?$haveErrors:true;

        if($haveErrors)
            pg_query($this->dbconn,"ROLLBACK");
        return !$haveErrors;
    }

    /**
     * Metoda usuwa segment drogi o podanym id.
     * Zwracany jest true gdy operacja się powiedzie lub false jak wystąpi błąd
     * @param $id
     * @return bool
     */
    public function deleteRoadSegment($id){
        $result = pg_query($this->dbconn, "DELETE FROM roadsegment WHERE id = '".$id."'");
        $haveErrors = ($result)?false:true;
        return !$haveErrors;
    }

    /**
     * Metoda zapisuje segment drogi zapisany w json.
     * Zwracany jest true gdy operacja się powiedzie lub false jak wystąpi błąd.
     * @param $json
     * @param $oldId
     * @return bool
     */
    public function saveRoadSegment($json, $oldId){
        if($json['id'] != $oldId) {
            $haveErrors = false;
            $haveErrors = ($haveErrors || !pg_query($this->dbconn,"BEGIN TRANSACTION"));
            $haveErrors = ($haveErrors || $this->deleteRoadSegment($oldId));
            $haveErrors = ($haveErrors || $this->addRoadSegment($json));
            $haveErrors = ($haveErrors || !pg_query($this->dbconn,"COMMIT"));
            return !$haveErrors;
        }
        else {
            $haveErrors = false;
            $haveErrors = ($haveErrors || !pg_query($this->dbconn,"BEGIN TRANSACTION"));
            $haveErrors = ($haveErrors || !pg_query($this->dbconn,"update roadsegment set \"desc\" = '".$json['desc']."', mainlightingclass = '".$json['mainLightingClass']."', lamparrangement = '".$json['lampArrangement']."', street = '".$json['street']."' where id = '".$json['id']."'"));
            $haveErrors = ($haveErrors || !pg_query($this->dbconn,"delete from roadsection where roadsegmentid = '".$json['id']."'"));
            $haveErrors = ($haveErrors || !pg_query($this->dbconn,"delete from roadsegmentcoordinates where roadsegmentid = '".$json['id']."'"));
            foreach($json['coordinates'] as $coordinates) {
                $haveErrors = ($haveErrors || !pg_query($this->dbconn,"insert into roadsegmentcoordinates values ('".$json['id']."',ST_Point(".$coordinates['lon'].",".$coordinates['lat']."),".((is_numeric($coordinates['elev']))?$coordinates['elev']:"null").",".((is_numeric($coordinates['order']))?$coordinates['order']:"null").",".((is_numeric($coordinates['group']))?$coordinates['group']:"null").")"));
            }
            foreach($json['roadSection'] as $section) {
                $haveErrors = ($haveErrors || !pg_query($this->dbconn,"insert into roadsection values ('".$section['id']."','".$json['id']."','".$section['idx']."','".$section['type']."',".((is_numeric($section['widthStart']))?$section['widthStart']:"null").",".((is_numeric($section['widthEnd']))?$section['widthEnd']:"null").",".((is_numeric($section['elevationStart']))?$section['elevationStart']:"null").",".((is_numeric($section['elevationEnd']))?$section['elevationEnd']:"null").",".((is_numeric($section['numberOfLanes']))?$section['numberOfLanes']:"null").",'".$section['roadSurfaceId']."'".",'".$section['lightingClassId']."'".")"));
            }
            $haveErrors = ($haveErrors || !pg_query($this->dbconn,"COMMIT"));

            if($haveErrors)
                pg_query($this->dbconn,"ROLLBACK");
            return !$haveErrors;
        }
    }

    /**
     * Metoda sprawdza czy dany id segmentu drogi istnieje już w bazie
     * @param $id
     * @return bool
     */
    public function isRoadSegmentIdExist($id) {
        $result = pg_query($this->dbconn, "select id from roadsegment where id='$id'");
        $counter = count(pg_fetch_array($result))-1;
        return ($counter != 0)? true : false;
    }

    /**
     * Metoda zwraca listę wszystkich id powierzchni drogi w bazie
     * @return mixed
     */
    public function getRoadSurfaces() {
        $result = pg_query($this->dbconn, "SELECT id FROM roadsurface");
        $counter = 0; $surfaces = null;
        while($rek = pg_fetch_array($result)) {
            $surfaces[$counter] = $rek[0];
            $counter++;
        }
        $json['enum'] = $surfaces;
        return $json;
    }

    /**
     * Metoda zwraca listę wszystkich id klas oświetleniowych w bazie
     * @return mixed
     */
    public function getLightningClasses() {
        $result = pg_query($this->dbconn, "SELECT id FROM lightingclass");
        $counter = 0; $classes = null;
        while($rek = pg_fetch_array($result)) {
            $classes[$counter] = $rek[0];
            $counter++;
        }
        $json['enum'] = $classes;
        return $json;
    }

    /**
     * Funkcja wyszukuje najbliższy segment drogi
     *
     * @param $lat - szerokość geograficzna
     * @param $lon  - długość geograficzna
     * @param $distance - maksymalny dystans poszukiwań
     * @return null - jeżeli nie znajdzie segmentu, json - jeżeli znajdzie
     */
    public function getNearestRoadSegment($lat, $lon, $distance)
    {
        $result = pg_query($this->dbconn, "select * from roadsegment where id in ( SELECT roadsegmentid  FROM roadsegmentcoordinates group by roadsegmentid having ST_Distance(ST_TRANSFORM(ST_MakeLine(coordinates::geometry),2163), ST_TRANSFORM(ST_GeomFromText('POINT(" . $lon . " " . $lat . ")',4326),2163)) < " . $distance . " order by ST_Distance(ST_TRANSFORM(ST_MakeLine(coordinates::geometry),2163), ST_TRANSFORM(ST_GeomFromText('POINT(" . $lon . " " . $lat . ")',4326),2163)) asc limit 1)");

        $roadSegment = null;
        while ($rek = pg_fetch_array($result)) {
            $roadSegment['id'] = $rek['id'];
            $roadSegment['street'] = $rek['street'];
            $roadSegment['mainLightingClass'] = $rek['mainlightingclass'];
            $roadSegment['desc'] = $rek['desc'];
            $roadSegment['lampArrangement'] = $rek['lamparrangement'];
        }

        if ($roadSegment != null) {
            $result = pg_query($this->dbconn, "select * from roadsection where roadsegmentid = '" . $roadSegment['id'] . "'");

            $sectionsArray = array();
            while ($rek = pg_fetch_array($result)) {
                $section['id'] = $rek['id'];
                $section['idx'] = ($rek['idx']) ? $rek['idx'] : "0";
                $section['type'] = $rek['type'];
                $section['widthStart'] = $rek['widthstart'];
                $section['widthEnd'] = $rek['widthend'];
                $section['elevationStart'] = $rek['elevationstart'];
                $section['elevationEnd'] = $rek['elevationend'];
                $section['roadSurfaceId'] = $rek['roadsurfaceid'];
                $section['lightingClassId'] = $rek['lightingclassid'];
                $section['numberOfLanes'] =  $rek['numberoflanes'];
                $sectionsArray[] = $section;
            }
            $roadSegment['roadSection'] = $sectionsArray;

            $result = pg_query($this->dbconn, "select ST_X(coordinates::geometry) as lon, ST_Y(coordinates::geometry) as lat, elev, \"order\", \"group\" from roadsegmentcoordinates where roadsegmentid = '" . $roadSegment['id'] . "'");

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
            return $roadSegment;
        }
        return null;
    }
}