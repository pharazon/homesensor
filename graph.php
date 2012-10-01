<?
require "temperature.php";

$selectRequestVariables = array("hours","sensor","width","height", "fullscreen", "dataFormat", "imageFormat", "dateStart", "dateEnd");
foreach($selectRequestVariables as $selectRequestVar) {
    eval('$GLOBALS["'.$selectRequestVar.'"] = $'.
        $selectRequestVar.' = isset($_REQUEST["'.
        $selectRequestVar.'"]) ? $_REQUEST["'.
        $selectRequestVar.'"] : "";');
}

if (!$dataFormat) $dataFormat = 'image';
if (!$imageFormat) $imageFormat = 'svg';
if (!$hours) $hours = 24;
if (!is_array($sensor)) $sensor = array(0);
if (!$width) $width = 800;
if (!$height) $height = 480;
if ($fullscreen == "") $fullscreen = false;

foreach ($sensor as $id) {
    if (!$dateStart && !$dateEnd)
        $sensorData[] = new Temperature($id, -$hours, 'now');
    else {
        $dateStart = date_create_from_format('Y-m-d-H-i-s', $dateStart);
        $dateEnd   = date_create_from_format('Y-m-d-H-i-s', $dateEnd);
        $sensorData[] = new Temperature($id, $dateStart, $dateEnd);
    }
}

$graph = new TemperatureGraph();
foreach ($sensorData as $s)
    $graph->addTemperatureData($s);
$graph->setDataFormat($dataFormat);
$graph->setImageFormat($imageFormat);
$graph->setSize($width, $height);
if (!$fullscreen) {
    $graph->setTitle("Lämpötila");
    $graph->setDimLabel("y","°C");
}

if ($dataFormat == "image") {
    if ($imageFormat == "svg") {
        header("Content-Type: image/svg+xml;charset=utf-8");
        echo $graph->getData();
    }
    if ($imageFormat == "png") {
        header("Content-Type: image/png");
        echo $graph->getData();
    }
}

if ($dataFormat == "json") {
    echo $graph->getJSONData();
}
?>
