<?php

class Sensor
{
	public $id;
	public $name;
	public $value;
}

$database =  "Lampo";
$dbconnect = mysql_connect(localhost, root, '');
mysql_select_db($database, $dbconnect);
$query = "select Anturi, nimi from Anturit";
$result = mysql_query($query, $dbconnect);

for ($i=0; $line = mysql_fetch_assoc($result); $i++)
{
	$s = new Sensor();
	$s->name = $line['nimi'];
	$s->id = $line['Anturi'];
	$q = mysql_query("select Lampotila from Mittaukset where Anturi =".$line['Anturi']." order by Aika desc limit 1" , $dbconnect);
	$row = mysql_fetch_row($q);
	$s->value = $row[0];
	$sensors[$i] = $s;
}

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
