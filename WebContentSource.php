<?php
namespace wcx;

/**
 * A Web Source with Points of Interest
 * @author Dimitrios Kastrinakis
 *
 */
class WebContentSource {
    
    private $sourceURL;
    
    private $selector;

    private $titleSelector;

    private $imageSelector;

    private $linkSelector;

    private $textSelector;

    private $html;

    private $pois;

    private $user_agent;

    private $rootURL;

    private $type;

    public function __construct($sourceURL, $selector, PoiType $type) {
        $this->sourceURL = $sourceURL;
        $this->selector = $selector;
        $parsed_url = parse_url($this->sourceURL);
        $this->rootURL = $parsed_url['scheme'] . "://" . $parsed_url['host'];
        $this->type = $type;
        $this->pois = array();
    }

    private function retrieve() {
        $ch = curl_init();
        $timeout = 5;
        curl_setopt($ch, CURLOPT_URL, $this->sourceURL);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        if ($this->user_agent) {
            curl_setopt($ch,CURLOPT_USERAGENT, $this->user_agent);
        }
        // get URL content
        $this->html = curl_exec($ch);
        //echo $this->html;
        // close handle to release resources
        curl_close($ch);
    }

    static $i=0;

    public function extract() {
        $this->retrieve();
        $dom = new \simple_html_dom();
        $dom->load($this->html);
        $elems = $dom->find($this->selector);

        foreach($elems as $el) {

            $poi = new Poi($el->outertext, $this->type);

            if ($this->titleSelector) {
                $poi->setTitle(trim($el->find($this->titleSelector)[0]->innertext));
                if (!$poi->getTitle()) continue;
            }
            if ($this->imageSelector) {
                $imgURL = $el->find($this->imageSelector)[0]->src;
                if (!$imgURL) {
                    $imgURL = explode(' ', $el->find($this->imageSelector)[0]->srcset)[0];
                }
                if ($imgURL) {
                    $separator = substr($imgURL, 0, 1) == '/' ? '' : '/';
                    if (!parse_url($imgURL, PHP_URL_HOST)) $imgURL = $this->rootURL . $separator . $imgURL;
                    $poi->setImage($imgURL);
                }
            }
            if ($this->linkSelector || $el->href) {
                $linkURL = $el->find($this->linkSelector)[0]->href;
                if (!$linkURL) {
                    $linkURL = $el->href;
                }
                $separator = substr($linkURL, 0, 1) == '/' ? '' : '/';
                if (!parse_url($linkURL, PHP_URL_HOST)) $linkURL = $this->rootURL . $separator . $linkURL;
                $poi->setLink($linkURL);
                if (!$poi->getLink()) continue;
            }
            if ($this->textSelector) {
                $poi->setText(trim($el->find($this->textSelector)[0]->innertext));
            }

            $poi->setExtractionDate(new \DateTime());

            $this->pois[] = $poi;
            //print_r($poi);

        }
        return $this->pois;
    }

    public function getSourceURL() {
        return $this->sourceURL;
    }

    public function getSelector() {
        return $this->selector;
    }

    public function getTitleSelector() {
        return $this->titleSelector;
    }

    public function getImageSelector() {
        return $this->imageSelector;
    }

    public function getLinkSelector() {
        return $this->linkSelector;
    }

    public function getTextSelector() {
        return $this->textSelector;
    }
    
    public function getPois() {
        return $this->pois;
    }

    public function getUserAgent() {
        return $this->user_agent;
    }

    public function getType() {
        return $this->type;
    }

    public function setSelector($selector) {
        $this->selector = $selector;
    }

    public function setTitleSelector($selector) {
        $this->titleSelector = $selector;
    }

    public function setImageSelector($selector) {
        $this->imageSelector = $selector;
    }

    public function setLinkSelector($selector) {
        $this->linkSelector = $selector;
    }

    public function setTextSelector($selector) {
        $this->textSelector = $selector;
    }

    public function setSourceURL($sourceURL) {
        $this->sourceURL = $sourceURL;
    }

    public function setUserAgent($user_agent) {
        $this->user_agent = $user_agent;
    }

    public static function findRecentRelevant(DBConnection $dbc, $title, $date_from, $date_to, PoiType $type=null) {
        $join_clause = ($type) ? " INNER JOIN poi_types pt ON pt.id = p.ptype " : "";
        $and_clause = ($type) ? " AND p.ptype = :id " : "";
        $sql_sel = "SELECT id, title, extraction_date, MATCH(title)
                   AGAINST (:title IN NATURAL LANGUAGE MODE) AS score
                   FROM pois " . $join_clause . "
                   WHERE extraction_date >= :date_from AND extraction_date <= :date_to
                   AND MATCH (title) AGAINST (:title IN NATURAL LANGUAGE MODE)
                   " . $and_clause . "
                   ORDER BY score DESC LIMIT 30";
        $query = $dbc->getConnection()->prepare($sql_sel);
        $query->bindValue(':title', $title, \PDO::PARAM_STR); 
        $query->bindValue(':date_from', $date_from, \PDO::PARAM_STR); 
        $query->bindValue(':date_to', $date_to, \PDO::PARAM_STR);
        if ($type) { $query->bindValue(':id', (int) $type->getId(), \PDO::PARAM_INT); }
        $query->execute();

        $result = $query->fetchAll(\PDO::FETCH_ASSOC);
        return $result;
    }

    public static function retrieveRecent(DBConnection $dbc, $date_from, $date_to, PoiType $type=null, $offset) {
        $join_clause = ($type) ? " INNER JOIN poi_types pt ON pt.id = p.ptype " : "";
        $and_clause = ($type) ? " AND p.ptype = :id " : "";
        $sql_sel = "SELECT title, img, link, txt, extraction_date
                    FROM `pois` p" . $join_clause . "
                    WHERE p.extraction_date >= :date_from AND p.extraction_date <= :date_to
                    " . $and_clause . "
                    ORDER BY p.extraction_date DESC LIMIT 24 OFFSET :offset";
        $query = $dbc->getConnection()->prepare($sql_sel);
        $query->bindValue(':date_from', $date_from, \PDO::PARAM_STR); 
        $query->bindValue(':date_to', $date_to, \PDO::PARAM_STR);
        $query->bindValue(':offset', (int) $offset, \PDO::PARAM_INT);
        if ($type) { $query->bindValue(':id', (int) $type->getId(), \PDO::PARAM_INT); }
        $query->execute();

        $result = $query->fetchAll(\PDO::FETCH_ASSOC);
        return $result;
    }
}
?>
