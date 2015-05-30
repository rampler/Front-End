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
     * Kontruktor tworzy po³¹czenie z baz¹ danych.
     */
    public function __construct()
    {
        $this->dbconn = pg_connect("host=localhost dbname=frontend2 user=frontend password=frontend")
        or die("Can't connect to database" . pg_last_error());
    }

    /**
     * Metoda dodaje segment drogi zapisany w json.
     * Zwracany jest true gdy operacja siê powiedzie lub false jak wyst¹pi b³¹d
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

        return !$haveErrors;
    }

    /**
     * Metoda usuwa segment drogi o podanym id.
     * Zwracany jest true gdy operacja siê powiedzie lub false jak wyst¹pi b³¹d
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
     * Zwracany jest true gdy operacja siê powiedzie lub false jak wyst¹pi b³¹d.
     * @param $json
     * @param $oldId
     * @return bool
     */
    public function saveRoadSegment($json, $oldId){
        //TODO
        $result = $this->deleteRoadSegment($oldId);
        $result2 = $this->addRoadSegment($json);
        return ($result && $result2);
    }

    /**
     * Metoda zwraca listê wszystkich id powierzchni drogi w bazie
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
     * Metoda zwraca listê wszystkich id klas oœwietleniowych w bazie
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
     * Funkcja wyszukuje najbli¿szy segment drogi
     *
     * @param $lat - szerokoœæ geograficzna
     * @param $lon  - d³ugoœæ geograficzna
     * @param $distance - maksymalny dystans poszukiwañ
     * @return null - je¿eli nie znajdzie segmentu, json - je¿eli znajdzie
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
                $section['numberOfLanes'] = 0; //TODO - kolejny request
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