<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>Valitse</title>
</head>
<body>
    <form action="grapher3.php">
    
<?
if (!mysql_connect(localhost, 'root', '')) {
    echo  "Connection failed!<p>";
    echo mysql_errno(). ": ".mysql_error(). "<BR>";
    echo  "<p>";
}

if (!mysql_select_db( "Lampo")) {
    echo  "Could not connect to 'Lampo' database!<p>";
    echo mysql_errno(). ": ".mysql_error(). "<BR>";
    echo  "<p>";
}

$q = "select Anturi, nimi from Anturit";
$v = mysql_query($q);

while ($data=mysql_fetch_row($v)) {
    echo "<input type=\"checkbox\" checked name=\"anturi[]\" value=$data[0]>Anturi $data[0]: $data[1]<br>\n";
}

    
$pvm = getdate(date(U));
$pvm2 = getdate(date(U)-60*60*24);
echo "<table><tr><td>Ensimmäinen piste: </td><td>";
echo "<select name=\"apaiva\">\n";

//Aloitusaika
for ($i=1;$i<=31;$i++) {
    if ($i != $pvm2[mday]) {
        echo "<option name=$i>$i\n";
    } else {
        echo "<option selected name=$i>$i\n";
    }
}
echo "</select>\n";
echo "<select name=\"akk\">\n";
for ($i=1;$i<=12;$i++) {
    if ($i != $pvm2[mon]) {
        echo "<option name=$i>$i\n";
    } else {
        echo "<option selected name=$i>$i\n";
    }
}
echo "</select>\n";
echo "<input name=\"avuosi\" size=4 value=$pvm2[year]>\n";

echo "<select name=\"atunti\">\n";
for ($i=0;$i<=23;$i++) {
    if ($i != $pvm2[hours]) {
        echo "<option name=$i>$i\n";
    } else {
        echo "<option selected name=$i>$i\n";
    }
}
echo "</select>";


echo "<select name=\"amin\">\n";
for ($i=0;$i<=59;$i++) {
    if ($i != $pvm2[minutes]) {
        echo "<option name=$i>$i\n";
    } else {
        echo "<option selected name=$i>$i\n";
    }
}
echo "</select>";



echo "<br>\n";



//Lopetusaika
echo "</td></tr><tr><td>Viimeinen piste: </td><td>";
echo "<select name=\"lpaiva\">\n";
for ($i=1;$i<=31;$i++) {
    if ($i != $pvm[mday]) {
        echo "<option name=$i>$i\n";
    } else {
        echo "<option selected name=$i>$i\n";
    }
}
echo "</select>\n";
echo "<select name=\"lkk\">\n";
for ($i=1;$i<=12;$i++) {
    if ($i != $pvm[mon]) {
        echo "<option name=$i>$i\n";
    } else {
        echo "<option selected name=$i>$i\n";
    }
}

echo "</select>\n";
echo "<input name=\"lvuosi\" size=4 value=$pvm[year]>\n";
                                                         ;

echo "<select name=\"ltunti\">\n";
for ($i=0;$i<=23;$i++) {
    if ($i != $pvm[hours]) {
        echo "<option name=$i>$i\n";
    } else {
        echo "<option selected name=$i>$i\n";
    }
}
echo "</select>";


echo "<select name=\"lmin\">\n";
for ($i=0;$i<=59;$i++) {
    if ($i != $pvm[minutes]) {
        echo "<option name=$i>$i\n";
    } else {
        echo "<option selected name=$i>$i\n";
    }
}
echo "</select></td></tr></table>";


?>
<br>
<input type="submit">

</form>
<form action="karvo.php">
<select name="vuosi[]" multiple>
<?
$date = getdate(time());
$vuosi = $date["year"];

for($i=2011; $i<=$vuosi; $i++) {
    print "<option>$i</option>\n";
}


?>

</select>
<input type="submit">
</form>


<form action="karvok.php">
<select name="aika[]" multiple>
<?

$timestamp = mktime(0,0,0,6,1,2011);

for($i=2011; $i<=$vuosi; $i++) {
    for($j=1; $j<=12; $j++) {
        print "<option>$j, $i</option>\n";
    }
}
?>
</select>
<input type="submit">
</form>

</body>
</html>











    
