<?php
require "temperature.php";

function is_ip_private($ip)
{
    return !filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE);
}

$sensors = Sensor::get_sensor_array();
$now = date("D, d M Y H:i:s T");

$clientip = $_SERVER['REMOTE_ADDR'];
if (isset($_SERVER['SERVER_NAME']))
    $ip = $_SERVER['SERVER_NAME'];
elseif (!is_ip_private($clientip) and !is_ip_private($_SERVER['SERVER_ADDR']))
    $ip = $_SERVER['SERVER_ADDR'];
elseif (!is_ip_private($clientip) and is_ip_private($_SERVER['SERVER_ADDR']))
    $ip = file_get_contents("http://ifconfig.me/ip");
else
    $ip = $_SERVER['SERVER_ADDR'];

$path = $dirpath = substr($_SERVER['REQUEST_URI'],1,strrpos($_SERVER['REQUEST_URI'],'/')-1);

$output = "<?xml version=\"1.0\"?>
<rss version=\"2.0\">
  <channel>
  <title>Lämpötila</title>
  <image>http://".$ip."/".$path."/graph.php</image>
  <pubDate>$now</pubDate>
  <lastBuildDate>$now</lastBuildDate>
  <ttl>5</ttl>\n";
            
foreach ($sensors as $sensor)
{
    $output .= "  <item>
    <title>".$sensor->name."</title>
    <description>".$sensor->value."</description>
    <link>http://".$ip."/".$path."/graph.php?".htmlspecialchars("sensor[]=$sensor->id")."</link>
    <sensor>".$sensor->id."</sensor>
  </item>\n";
}
$output .= "</channel></rss>\n";
header("Content-Type: text/xml;charset=utf-8");
echo $output;
?>
