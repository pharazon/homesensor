<?
require "temperature.php";

$sensors = Sensor::get_sensor_array();

header('Content-type: application/json');
echo json_encode($sensors);

?>
