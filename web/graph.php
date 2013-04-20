<?php
require "temperature.php";

$selectRequestVariables = array(
                              "hours",
                              "sensor",
                              "width",
                              "height",
                              "fullscreen",
                              "dataFormat",
                              "imageFormat",
                              "dateStart",
                              "dateEnd",
                              "histogram",
                          );
foreach ($selectRequestVariables as $selectRequestVar) {
    eval('$GLOBALS["'.$selectRequestVar.'"] = $'.
        $selectRequestVar.' = isset($_REQUEST["'.
        $selectRequestVar.'"]) ? $_REQUEST["'.
        $selectRequestVar.'"] : "";');
}

if (!$dataFormat) $dataFormat = 'image';
if (!$imageFormat) $imageFormat = 'svg';
if (!$hours) $hours = 24;
if (!is_array($sensor)) $sensor = array(0);
if (!is_array($histogram)) $histogram = array();
if (!$width) $width = 800;
if (!$height) $height = 480;
if ($fullscreen == "") $fullscreen = false;

$sensors = Sensor::get_sensor_array();

foreach ($sensor as $id) {
    if (in_array($id, $histogram)) {
        $_histogram = True;
    } else {
        $_histogram = False;
    }

    $sensorobj = Sensor::find_id($sensors, $id);
    if (!$dateStart && !$dateEnd) {
        $sensorData[] = new Temperature($sensorobj, -$hours, 'now', $_histogram);
    } else {
        $_dateStart = date_create_from_format('Y-m-d-H-i-s', $dateStart);
        $_dateEnd   = date_create_from_format('Y-m-d-H-i-s', $dateEnd);
        $sensorData[] = new Temperature($sensorobj, $_dateStart, $_dateEnd, $_histogram);
    }
}

$graph = new TimeSeriesGraph();
foreach ($sensorData as $s) {
        $graph->addTemperatureData($s);
}

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
    header('Content-type: application/json');
    echo $graph->getJSONData();
}
