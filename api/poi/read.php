<?php

namespace wcx;



header('Access-Control-Allow-Origin: https://www.newsrush.gr');

header("Content-Type: application/json; charset=UTF-8");



require __DIR__."/../../config.php";

require __DIR__."/../../simplehtmldom/simple_html_dom.php";

require __DIR__."/../../DBConnection.php";

require __DIR__."/../../PoiType.php";

require __DIR__."/../../Poi.php";

require __DIR__."/../../WebContentSource.php";



$dbc = new DBConnection($db_host, $db_name, $db_user, $db_pass);



$offset = (isset($_GET['offset'])) ? filter_var($_GET['offset'], \FILTER_SANITIZE_NUMBER_INT) : 0;



$date_to = new \DateTime();

$date_from = clone $date_to;

$date_from->sub(new \DateInterval('P10D'));

$type = PoiType::get($dbc, 'news-general');

$results = WebContentSource::retrieveRecent($dbc, $date_from->format(DATETIMEFORMAT), $date_to->format(DATETIMEFORMAT), $type, $offset);



if (count($results)) {

    http_response_code(200);

    echo json_encode($results);

}else {

    http_response_code(404);

    echo json_encode(array("message" => "No more entries."));

}

?>