<?
class Sensor
{
	public $id;
	public $name;
	public $value;
	public $type;
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
	$s->type = 'temperature';
	$sensors[$i] = $s;
}

echo json_encode($sensors);

?>
