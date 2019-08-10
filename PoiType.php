<?php
namespace wcx;

/**
 * A Point of Interest Type
 * @author Dimitrios Kastrinakis
 *
 */
class PoiType {

    private $id;

    private $typename;

    private function __construct($id, $typename) {
        $this->id = $id;
        $this->typename = $typename;
    }

    public function getId() {
        return $this->id;
    }

    public function getTypeName() {
        return $this->typename;
    }

    public static function get(DBConnection $dbc, $typename) {
        $sql_sel = "SELECT id, typename FROM poi_types WHERE typename = :typename";
        $query = $dbc->getConnection()->prepare($sql_sel);
        $query->bindValue(':typename', $typename, \PDO::PARAM_STR);  
        $query->execute();
        $result = $query->fetchAll(\PDO::FETCH_ASSOC);
        return count($result) ? new PoiType($result[0]['id'], $result[0]['typename']) : null;
    }
}