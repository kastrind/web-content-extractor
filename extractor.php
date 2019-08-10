<?php
namespace wcx;

/**
 * The Extraction Loop, application entry point
 * @author Dimitrios Kastrinakis
 *
 */

require __DIR__."/config.php";
require __DIR__."/simplehtmldom/simple_html_dom.php";
require __DIR__."/DBConnection.php";
require __DIR__."/PoiType.php";
require __DIR__."/Poi.php";
require __DIR__."/WebContentSource.php";

$time_start = time();


$dbc = new DBConnection($db_host, $db_name, $db_user, $db_pass);

$sources = array();

$type = PoiType::get($dbc, "news-general");
if (!$type) die;

// SOURCES DECLARATION STARTS HERE

// repeat this pieace of code to add more sources
$source = new WebContentSource("https://www.example-news-site.gr/latest-news/", "article.teaser__article.article_left", $type);
$source->setUserAgent("Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1; .NET CLR 1.1.4322; .NET CLR 2.0.50727)");
$source->setTitleSelector("h2>a");
$source->setImageSelector("img");
$source->setLinkSelector("a");
$source->setTextSelector("p.article__leadtext");
$sources[] = $source;

// more sources here...

// SOURCES DECLARATION ENDS HERE

$pois_to_insert = [];
$pois_inserted = 0;

$report = "<html><head></head><body>";

foreach ($sources as $source) {

    $pois = $source->extract();

    $report .= "Found ".count($pois)." points of interest from source ".$source->getSourceURL()."<br/>";

    if (!count($pois)) continue;

    foreach ($pois as $poi) {
        $date_to = $poi->getExtractionDate();
        $date_from = clone $date_to;
        $date_from = $date_from->sub(new \DateInterval('P3D'));
        $relevants = WebContentSource::findRecentRelevant($dbc, $poi->getTitle(), $date_from->format(DATETIMEFORMAT), $date_to->format(DATETIMEFORMAT));
        $num_words = min( count(explode(" ", $poi->getTitle())), count(explode(" ", $relevants[0]["title"])) );
        
        //$report .= "Poi '". $poi->getTitle()."' has #".count($relevants)." similars and most similar is '".$relevants[0]["title"]."' with score ".$relevants[0]["score"].".<br/><br/>";

        // insertion criteria based on existing poi similarity
        if (count($relevants) && $relevants[0]["score"] > 0 && $relevants[0]["score"] < 10) {
            $report .= "Will insert '". $poi->getTitle()."' with #".count($relevants)." similars as it is not similar to '".$relevants[0]["title"]."' with score ".$relevants[0]["score"]." and threshold 10.<br/><br/>";
            $pois_to_insert[] = $poi;
        }else if (!count($relevants)) {
            $report .= "Will insert '". $poi->getTitle()."' with #".count($relevants).".<br/><br/>";
            $pois_to_insert[] = $poi;
        }

        $res = Poi::insertBatch($dbc, $pois_to_insert);
        if ($res) $pois_inserted += count($pois_to_insert);
        $pois_to_insert = [];

    }

}

$time_end = time();

$report .= $pois_inserted." points of interest inserted to db.<br/>";

$report .= "Operation completed in ".($time_end - $time_start)." seconds.</body></html>";
echo $report;

// optionally mail the report
// mail( "email@example.gr", "Extraction report", $report ); 
?>