<?php
require "temperature.php";

$sensors = Sensor::get_sensor_array();
$now = date("D, d M Y H:i:s T");

$output = "<?xml version=\"1.0\"?>
<rss version=\"2.0\">
  <channel>
  <title>Lämpötila</title>
  <image>http://192.168.1.2/lampotila/graph.php</image>
  <pubDate>$now</pubDate>
  <lastBuildDate>$now</lastBuildDate>
  <ttl>5</ttl>\n";
            
foreach ($sensors as $sensor)
{
    $output .= "  <item>
    <title>".$sensor->name."</title>
    <description>".$sensor->value."</description>
    <link>http://192.168.1.2/lampotila/graph.php?".htmlspecialchars("sensor[]=$sensor->id")."</link>
    <sensor>".$sensor->id."</sensor>
  </item>\n";
}
$output .= "</channel></rss>\n";
header("Content-Type: text/xml;charset=utf-8");
echo $output;
?>
