<?php
namespace wcx;

/**
 * A Point of Interest
 * @author Dimitrios Kastrinakis
 *
 */
class Poi {

    private $title;

    private $image;

    private $link; 

    private $text;

    private $raw;

    private $extractionDate;

    private $type;

    public function __construct($raw, PoiType $type) {
        $this->raw = $raw;
        $this->type = $type;
    }

    public function insert(DBConnection $dbc) {
        $stmt = $dbc->getConnection()->prepare("INSERT INTO pois (title, img, link, txt, raw_content, extraction_date, ptype) VALUES (:title, :img, :link, :txt, :raw_content, :extraction_date, :ptype)");
        $title = $image = $link = $text = $raw = $xdate_str = $ptype = null;
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':img', $image);
        $stmt->bindParam(':link', $link);
        $stmt->bindParam(':txt', $text);
        $stmt->bindParam(':raw_content', $raw);
        $stmt->bindParam(':extraction_date', $xdate_str);
        $stmt->bindParam(':ptype', $ptype);

        try {
            $title = $this->title;
            $image = $this->image;
            $link = $this->link;
            $text = $this->text;
            $raw = $this->raw;
            $xdate_str = $this->extractionDate->format("Y-m-d H:i:s");
            $ptype = $this->type->getId();
            return $stmt->execute();
            //print_r($dbc->getConnection()->errorInfo());

        } catch (\PDOException $e) {
            print "Error!: " . $e->getMessage() . "<br/>";
            return false;
        }

    }

    public static function insertBatch(DBConnection $dbc, $pois) {
        $stmt = $dbc->getConnection()->prepare("INSERT INTO pois (title, img, link, txt, raw_content, extraction_date, ptype) VALUES (:title, :img, :link, :txt, :raw_content, :extraction_date, :ptype)");
        $title = $image = $link = $text = $raw = $xdate_str = $ptype = null;
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':img', $image);
        $stmt->bindParam(':link', $link);
        $stmt->bindParam(':txt', $text);
        $stmt->bindParam(':raw_content', $raw);
        $stmt->bindParam(':extraction_date', $xdate_str);
        $stmt->bindParam(':ptype', $ptype);

        foreach($pois as $poi) {

            try {
                $title = $poi->getTitle();
                $image = $poi->getImage();
                $link = $poi->getLink();
                $text = $poi->getText();
                $raw = $poi->getRaw();
                $xdate_str = $poi->getExtractionDate()->format("Y-m-d H:i:s");
                $ptype = $poi->getType()->getId();
                $stmt->execute();
                //print_r($dbc->getConnection()->errorInfo());
    
            } catch (\PDOException $e) {
                print "Error!: " . $e->getMessage() . "<br/>";
                return false;
            }

        }
        return true;
    }

    public function getTitle() {
        return $this->title;
    }

    public function getImage() {
        return $this->image;
    }

    public function getLink() {
        return $this->link;
    }

    public function getText() {
        return $this->text;
    }

    public function getRaw() {
        return $this->raw;
    }

    public function getExtractionDate() {
        return $this->extractionDate;
    }

    public function getType() {
        return $this->type;
    }

    public function setTitle($title) {
        $this->title = $title;
    }

    public function setImage($image) {
        $this->image = $image;
    }

    public function setLink($link) {
        $this->link = $link;
    }

    public function setText($text) {
        $this->text = $text;
    }

    public function setExtractionDate(\DateTime $date) {
        $this->extractionDate = $date;
    }

}
?>