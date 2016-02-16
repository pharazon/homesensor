#!/usr/bin/perl -w

use DBI;
#use Text::ParseWords

# Perl trim function to remove whitespace from the start and end of the string
sub trim($)
{
	my $string = shift;
	$string =~ s/^\s+//;
	$string =~ s/\s+$//;
	return $string;
}

$dbname = "Lampo";
$dbuser = "root";
$dbpasswd = "";
$dbh = DBI->connect("DBI:mysql:$dbname", $dbuser, $dbpasswd)
    or die "can't connect: $DBI::errstr\n";

$sth = $dbh->prepare("SELECT sensorid,type,Anturi FROM Anturit");
$sth->execute() or die "\n";

while(@sensors = $sth->fetchrow_array) {
	$hardwareId = $sensors[0];
	$type = $sensors[1];
	$id = $sensors[2];
	$value = "";
	open(TEMPERATURE, "/digitemp/" . $hardwareId . "/" . $type);
	if (fileno TEMPERATURE) {
		$str = <TEMPERATURE>;
		chomp $str;
		$value = trim($str);
		close TEMPERATURE;
	}
#	print($id. " ");
#	print($value . " ");
#	print($hardwareId . " \n");

	if ($value && $value != 85) {
		$query = "INSERT INTO Mittaukset (id,Aika,Anturi,Lampotila) VALUES (0, NOW(), $id, $value)";
#		print("$query\n");
		$dbh->do($query);
	}
}
$dbh->disconnect;
exit(0);
