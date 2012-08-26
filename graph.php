<?
require "temperature.php";

$selectRequestVariables = array("hours","sensor","width","height", "fullscreen");
foreach($selectRequestVariables as $selectRequestVar) {
    eval('$GLOBALS["'.$selectRequestVar.'"] = $'.
        $selectRequestVar.' = isset($_REQUEST["'.
        $selectRequestVar.'"]) ? $_REQUEST["'.
        $selectRequestVar.'"] : "";');
}

if (!$hours) $hours = 24;
if (!is_array($sensor)) $sensor = array(0);
if (!$width) $width = 800;
if (!$height) $height = 480;
if ($fullscreen == "") $fullscreen = false;

foreach ($sensor as $id) {
    $sensorData[] = new Temperature($id, -$hours, 'now');
}

$graph = new TemperatureGraph();
foreach ($sensorData as $s)
    $graph->addTemperatureData($s);
$graph->setFormat("svg");
$graph->setSize($width, $height);
if (!$fullscreen) {
    $graph->setTitle("Lämpötila");
    $graph->setDimLabel("y","°C");
}

header("Content-Type: image/svg+xml;charset=utf-8");
echo $graph->getData();
?>
