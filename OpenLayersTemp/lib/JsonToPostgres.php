<?php

/**
 * Created by PhpStorm.
 * User: Adrian
 * Date: 2015-05-26
 * Time: 15:04
 */
class JsonToPostgres
{
    private $delimiter;
    private $configParents;
    private $configNames;
    private $srid=4326;

    function __construct($configPath = null, $delimiter = '_')
    {
        $this->delimiter = $delimiter;
        if ($configPath != null) {
            $t = json_decode(file_get_contents($configPath), true);
            $this->configParents = $t['parents'];
            $this->configNames = $t['names'];
            $this->configParents = $this->flattenArray($this->configParents);
            $this->configNames = $this->flattenArray($this->configNames);
        }

    }

    /**
     * @return mixed
     */
    public function getSrid()
    {
        return $this->srid;
    }

    /**
     * @param mixed $srid
     */
    public function setSrid($srid)
    {
        $this->srid = $srid;
    }

    function createSqlStatements($jsonString)
    {
        $decoded = json_decode($jsonString, true);
        $flat = $this->flattenArray($decoded);
        if($this->configNames!=null){
            $replaced = $this->replaceNames($flat);
            $prepared = $this->prepareArray($replaced);
        } else {
            $prepared = $this->prepareArray($flat);
        }
        $stmts = $this->makeSQLStatements($prepared);
        return $stmts;
    }

    /**
     * Przeksztalca zagniezdzona tablie asocjacyjna do jednowymiarowej tablicy asocjacyjnej z kluczami typu klucz_numer_klucz2_numer
     * @param $nestedArray
     * @param null $delimiter
     * @return array
     */
    private function flattenArray($nestedArray, $delimiter = null)
    {
        if ($delimiter) $delimiter .= $this->delimiter;
        $items = array();

        foreach ($nestedArray as $key => $value) {
            if (is_array($value))
                $items = array_merge($items, $this->flattenArray($value, $delimiter . $key));
            else
                $items[$delimiter . $key] = $value;
        }

        return $items;
    }

    /**
     * Zamienia nazwy kluczy z pliku json dla danej jednowymiarowej tablicy asocjacyjnej
     * @param $notNestedArray
     * @return mixed
     */
    private function replaceNames($notNestedArray)
    {
        $regex = '/[_][0-9]+/';
        foreach ($notNestedArray as $k => $v) {
            $noDigitsKey = preg_replace($regex, '', $k);
            foreach ($this->configNames as $p => $o) {
                $pos = strripos($noDigitsKey, $p);
                if ($pos !== false) {
                    //klucz z config=>names zawiera sie w kluczu bez numerow - zamieniamy klucze
                    $dividedOldKey = preg_split("/[_]/", $k);
                    $dividedNewKey = preg_split("/[_]/", $o);

                    $newKey = '';
                    for ($i = 0; $i < count($dividedOldKey); $i++) {
                        if (is_numeric($dividedOldKey[$i]) || !array_key_exists($i, $dividedNewKey)) {
                            //dodajemy numer do naszego nowego klucza lub dodajemy pozostalosc starego klucza
                            $dividedNewKey = $this->insertAtPosition($dividedNewKey, $i, $dividedOldKey[$i]);
                        }
                        $newKey .= $this->delimiter . $dividedNewKey[$i];
                    }
                    $newKey = substr($newKey, 1); //usuniecie pierwszego odstepu
                    unset($notNestedArray[$k]);
                    $notNestedArray[$newKey] = $v;
                }
            }
        }
        return $notNestedArray;
    }

    function prepareArray($array)
    {
        $prepared = array();
        $i = -1;
        $currTable = '';
        $currNumber = '';
        $numeric = false;
        $addParentId = false;

        //dla kazdego klucza szukamy nazwy tabeli i kolumny tak aby mozliwe bylo pozniejsze sforumolowanie zapytania sql
        foreach ($array as $k => $v) {
            $colNameIndex = strripos($k, $this->delimiter);
            $colName = substr($k, $colNameIndex + 1); //nazwa kolumny
            $rest = substr($k, 0, $colNameIndex); //reszta z nazwy klucza

            $addParentId = $this->addParentId($rest); //sprawdzamy czy dla tej tabeli konieczne jest dodanie kolumny z id rodzica (tworzenie relacji)

            $index = strripos($rest, $this->delimiter); //kolejny raz wycinamy dane z klucza
            $tableName = '';
            if ($index === false) {
                $tableName = $rest;
            } else {
                $checkMe = substr($rest, $index + 1); //sprawdzamy czy wycinek klucza jest numerem jesli tak to jest to zagniezdzona tabela
                if (is_numeric($checkMe)) {
                    if ($currNumber != $checkMe) {
                        $numeric = true;
                        $currNumber = $checkMe;
                    }

                    $rest = substr($rest, 0, $index);
                    $index = strripos($rest, $this->delimiter);
                    if ($index === false) {
                        $tableName = $rest;
                    } else {
                        $tableName = substr($rest, $index + 1);
                    }
                } else {
                    $tableName = $checkMe;
                }
            }

            if ($currTable != $tableName || $numeric) {
                $currTable = $tableName;
                $i++;
            } else {
                //ciagle dodajemy dane do tej samej tabeli
            }

            //dodanie cudzyslowow do nazw kolumn
            $colName = "\"" . strtolower($colName) . "\"";

            //dodanie apostrofow do wartosci je¿eli nie s¹ liczb¹ lub null jezeli nie podano wartoœci
            if(!$v)
                $v = "null";
            else if(!is_numeric($v))
                $v="'" . $v . "'";

            $prepared[$i][$tableName][$colName] = $v;

            if ($addParentId != false) {
                $addParentId[0] = "\"" . strtolower($addParentId[0]) . "\"";
                $prepared[$i][$tableName][$addParentId[0]] = "'".$array[$addParentId[1]]."'";
            }

            $numeric = false;
        }
        return $prepared;
    }

    /**
     * Funnkcja ktora na podstawie pliku konfiguracyjnego json dodaje kolumny z wartoscia kolumny rodzica dla aktualnie sprawdzanej tabeli.
     * Zwraca false jesi nie jest konieczne dodanie nowej kolumny lub tablice zawieracja nazwe nowej kolumny i jej wartosc
     * @param $currentKey
     * @return array|bool
     */
    function addParentId($currentKey)
    {
        //sprawdzamy czy jest zdefiniowany plik konfiguracyjny
        if($this->configNames==null) return false;

        $regex = '/[_][0-9]+/';
        $noDigitsKey = preg_replace($regex, '', $currentKey); //usuwamy numery

        foreach ($this->configParents as $k => $v) {
            $index = strripos($k, '_'); //usuwamy ostatnia czesc klucza z pliku konfiguracyjnego, nie bedziemy jej porownywac
            $parentKey = substr($k, 0, $index);
            if ($noDigitsKey == $parentKey) {
                $dividedPathToParentId = preg_split("/[_]/", $this->configParents[$parentKey . '_' . 'parentId']);
                $dividedKey = preg_split("/[_]/", $currentKey);

                $newPathToParentId = '';
                for ($i = 0; $i < count($dividedPathToParentId); $i++) {
                    if (is_numeric($dividedKey[$i])) {
                        //dodajemy numer do naszego nowego klucza
                        $dividedPathToParentId = $this->insertAtPosition($dividedPathToParentId, $i, $dividedKey[$i]);
                    }
                    $newPathToParentId .= $this->delimiter . $dividedPathToParentId[$i];
                }

                for ($i = 0; $i < count($dividedPathToParentId); $i++) {

                }
                $newPathToParentId = substr($newPathToParentId, 1);

                $pathToChildKey = $this->configParents[$parentKey . '_childId'];
                return array($pathToChildKey, $newPathToParentId);
            }
        }
        return false;

    }

    /**
     * Funkcja ktora z podanej wczesniej przygotowanej dwuwymiarowej tablicy asocjacyjnej tworzy zapytania SQL i zwraca je w postaci tablicy
     * @param $preparedArray
     * @return array
     */
    function makeSQLStatements($preparedArray){
        $sqls = array();

        foreach($preparedArray as $p){
            foreach($p as $k => $v){
                //$k to nasz klucz - nazwa tabeli
                $colNames = '';
                $colValues = '';

                foreach($v as $a => $b){
                    $colNames = $colNames . $a . ',';
                    $colValues = $colValues . $b . ',';
                }

                //usuwamy ostatni przecinek
                $colNames = substr($colNames, 0, strlen($colNames)-1);
                $colValues = substr($colValues, 0, strlen($colValues)-1);

                $statement = $this->makeSQLStatement($colNames, $colValues, $k);
                array_push($sqls, $statement);

            }
        }
        return $sqls;
    }

    /**
     * Pomocnicza funkcja, ktora sprawdza zapytanie pod katem zmiennych lon i lat, jezeli takie znajdzie zamienia je na PostGisowy point w zapytaniu sql
     * @param $colNames
     * @param $colValues
     * @param $tableName
     * @return string
     */
    private function makeSQLStatement($colNames, $colValues, $tableName){
        $colNames = explode(',', $colNames);
        $colValues = explode(',', $colValues);
        $addPointColumn = false;
        $length = count($colNames);
        for($i=0; $i<$length; $i++){
            if($colNames[$i] == '"lon"'){
                $lon = $colValues[$i];
                unset($colNames[$i]);
                unset($colValues[$i]);
                $addPointColumn = true;
                continue;
            }
            if($colNames[$i] == '"lat"'){
                $lat = $colValues[$i];
                unset($colNames[$i]);
                unset($colValues[$i]);
                continue;
            }
        }

        if($addPointColumn){
            //wycinanie wczesniej dodanych apostrofow z lon i lat
            $lon = substr($lon, 1, strlen($lon)-2);
            $lat = substr($lat, 1, strlen($lat)-2);

            array_push($colNames, '"coordinates"');
            array_push($colValues, "ST_GeomFromText('POINT($lon $lat)', $this->srid)");
        }

        $colNames = implode(',', $colNames);
        $colValues = implode(',', $colValues);
        $statement = 'INSERT INTO ' . $tableName . ' (' . $colNames . ') VALUES (' . $colValues . ');';
        return $statement;
    }

    /**
     * Pomocnicza funkcja wstawiajaca element do tablicy w okreslonym miejscu
     * @param $array
     * @param $position
     * @param $value
     * @return array
     */
    private function insertAtPosition($array, $position, $value)
    {
        if ($position == count($array)) {
            array_push($array, $value);
            return $array;
        }
        for ($i = 0; $i < count($array); $i++) {
            if ($i == $position) {
                $firstPart = array_slice($array, 0, $i);
                $secondPart = array_slice($array, $i);
                array_push($firstPart, $value);
                return array_merge($firstPart, $secondPart);
            }
        }
    }


}