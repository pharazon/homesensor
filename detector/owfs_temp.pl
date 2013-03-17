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

my $sensorid;
my $lampotila;
$dbname = "Lampo";
$dbuser = "root";
$dbpasswd = "";
$dbh = DBI->connect("DBI:mysql:$dbname", $dbuser, $dbpasswd)
    or die "can't connect: $DBI::errstr\n";

$sth = $dbh->prepare("select sensorid,type from Anturit");
$sth->execute() or die "\n";

$sensor_count = 0;
while(@asd = $sth->fetchrow_array) {
#print $asd[0];
	open(TEMPERATURE, "/digitemp/" . $asd[0] . "/" . $asd[1]);
	$str = <TEMPERATURE>;
	chomp $str;
	$lampotila[$sensor_count] = trim($str);
	$sensorid[$sensor_count] = $asd[0];
	close TEMPERATURE;
	$sensor_count++;
}


for ($i=0; $i<$sensor_count; $i++) {
    #filter out error values
    if ($sensorid[$i] ne "") { 
        if ($lampotila[$i] != 85) {
            $query = "INSERT INTO tmpmittaukset values ($lampotila[$i], '" . $sensorid[$i] . "')";
            $dbh->do($query);
        }
    }
}

$sth = $dbh->prepare("select Anturit.Anturi, tmpmittaukset.lampotila from Anturit, tmpmittaukset
where tmpmittaukset.sensorid=Anturit.sensorid
order by Anturit.Anturi");
$sth->execute()
	or die "$tuloste\n";

while(@asd= $sth->fetchrow_array) {
	$dbh->do("insert into Mittaukset (id,Aika,Anturi,Lampotila) values (0, NOW(), $asd[0], $asd[1])")
		or die "virhe: $DBI::errstr\n Tuloste: $tuloste";
}

$dbh->do("delete from tmpmittaukset");	
$dbh->disconnect;

