<?
require "temperature.php";

$sensors = Sensor::get_sensor_array();
echo json_encode($sensors);

?>
