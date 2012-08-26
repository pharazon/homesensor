<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0//EN"> 
<html>
<head>
<title>Graph Results</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
</head>
<BODY Bgcolor="ffffff" link="#003cc3" alink="#003cc3" vlink="#003cc3">
<center>
<?

$selectRequestVariables = array("anturi","amin","atunti","apaiva","akk","avuosi","lmin","ltunti","lpaiva","lkk","lvuosi");
foreach($selectRequestVariables as $selectRequestVar) {
    // split across lines for readability
    eval('$GLOBALS["'.$selectRequestVar.'"] = $'.
        $selectRequestVar.' = isset($_REQUEST["'.
        $selectRequestVar.'"]) ? $_REQUEST["'.
        $selectRequestVar.'"] : "";');
}
//
  $aika1 = microtime();

if (!$anturi) {
  $anturi[0] = 0;
}

function lisaanolla($luku, $pituus) {
  while (strlen($luku) < $pituus) {
    $luku = "0".$luku;
  }
  return $luku;
}

if ($apaiva > 0) {
  $lkk = lisaanolla($lkk, 2);
  $lpaiva = lisaanolla($lpaiva, 2);
  $akk = lisaanolla($akk, 2);
  $apaiva = lisaanolla($apaiva, 2);
  $atunti = lisaanolla($atunti, 2);
  $ltunti = lisaanolla($ltunti, 2);
  $amin = lisaanolla($amin, 2);
  $lmin = lisaanolla($lmin, 2);
  $loppuaika = array($lvuosi,$lkk,$lpaiva,$ltunti,$lmin);
  $alkuaika = array($avuosi,$akk,$apaiva,$atunti,$amin);
  $sekunteja = mktime($ltunti, $lmin, 0, $lkk, $lpaiva, $lvuosi) - mktime($atunti, $amin, 0, $akk, $apaiva, $avuosi);
  $num = $sekunteja/(60*60);
}


$maara = count($anturi);



if (!mysql_connect('localhost', 'root', '')) {
  echo  "Connection failed!<p>";
  echo mysql_errno(). ": ".mysql_error(). "<BR>";
  echo  "<p>";
}

if (!mysql_select_db( "Lampo")) {
  echo  "Could not connect to 'web' database!<p>";
  echo mysql_errno(). ": ".mysql_error(). "<BR>";
  echo  "<p>";
}


function haedata($anturi, $aika1, $aika2 ) {
  $query=
    "select Aika , Lampotila from Mittaukset
    where Anturi = $anturi
    and Aika between '$aika1[0]-$aika1[1]-$aika1[2] $aika1[3]:$aika1[4]:15'
    and '$aika2[0]-$aika2[1]-$aika2[2] $aika2[3]:$aika2[4]:15'";
    
    if (!($id=mysql_query( "$query"))) {
      echo  "Error with query:";
      echo mysql_errno(). ": ".mysql_error(). "<BR>";
      echo  "<p>";
    }
  
  $rows=mysql_num_rows($id);
  return array ($id, $rows);
}

function sluku() {
  $ext = md5 (uniqid (rand()));
  return $ext;
}

function tempdata($id, $ext, $sensor, $i) {
  if (!file_exists( "/tmp/tempdata")) {
    echo  "Creating /tmp/tempdata...<p>";
    mkdir ( "/tmp/tempdata",0755);
  }
  chdir ( "/tmp/tempdata");
  
  if (!$data_fp=fopen( "/tmp/tempdata/data.$ext", "w")) {
    echo  "Couldn't open /tmp/tempdata/data.$ext for writing!\n";
  }

  $begin = mysql_fetch_row($id);
  $begin = $begin[0];
  mysql_data_seek($id, mysql_num_rows($id)-1);
  $end = mysql_fetch_row($id);
  $end = $end[0];
  mysql_data_seek($id, 0);
//  $lista = array_pad(array(), 10 ,$begin[1]);
  while ($data=mysql_fetch_row($id)) {
    // if (!$rownum) $begin=$data[0];
/*    $lkarvo = 0;
    array_unshift($lista, $data[1]);
    array_pop($lista);
    for ($i=0; $i<count($lista); $i++) {
      $lkarvo = $lkarvo + $lista[$i];;
    }
    $lkarvo = $lkarvo / count($lista);  */
    $temp .= $data[0]."\t".$data[1]."\n";
    //$temp .= $data[0]."\t".$lkarvo."\n";
    // $end=$data[0];
    // $rownum++;
  }

  fputs ($data_fp,$temp);
  fclose ($data_fp);
  return array($begin, $end);
}

function teeplot($ext, $plot, $vari, $title, $alku, $loppu, $i) {
  if ($i == 0){
    $plot.= "[\"$alku\":\"$loppu\"] \"/tmp/tempdata/data.$ext\" using 1:3 smooth csplines title \"$title\" with lines lw 2,";
  } else {
    $plot.= " \"/tmp/tempdata/data.$ext\" using 1:3 smooth csplines title \"$title\" with lines lw 2,";
  }
  return $plot;
}

function keskiarvo($anturi, $aika1, $aika2) {
  $q = "select AVG(Lampotila), MIN(Lampotila), MAX(Lampotila) from Mittaukset
    where Anturi = $anturi
    and Aika between '$aika1[0]-$aika1[1]-$aika1[2] $aika1[3]:$aika1[4]:15'
    and '$aika2[0]-$aika2[1]-$aika2[2] $aika2[3]:$aika2[4]:15'";
    
    $v = mysql_query($q);
  $lampo = mysql_fetch_row($v);
  return array($lampo[0], $lampo[1], $lampo[2]);
}

function currentTemperature($anturi, $aika1, $aika2) {
  $q = "SELECT Lampotila FROM Mittaukset
    WHERE Anturi = $anturi
    AND Aika BETWEEN '$aika1[0]-$aika1[1]-$aika1[2] $aika1[3]:$aika1[4]:15'
    AND '$aika2[0]-$aika2[1]-$aika2[2] $aika2[3]:$aika2[4]:15'
    ORDER BY Aika DESC
    LIMIT 1";
    
    $v = mysql_query($q);
  $lampo = mysql_fetch_row($v);
  return $lampo[0];
}


$plot = "plot ";

for ($i=0;$i<=$maara-1;$i++) {
  $ext[$i] = sluku();
  list ($id, $rows[$i]) = haedata($anturi[$i], $alkuaika, $loppuaika);
  if ($i == 0) {
    $q = "select Anturi, nimi from Anturit order by Anturi";
    $qid = mysql_query($q);
  }
  mysql_data_seek($qid,$anturi[$i]);
  $title =  mysql_fetch_row($qid);
  list($begin[$i], $end[$i]) = tempdata($id, $ext[$i], $sensor, $i);
  $plot = teeplot($ext[$i], $plot, $i+7, $title[1], $begin[$i], $end[$i], $i);
  mysql_free_result($id);
  list ($keskilampo[$i], $minlampo[$i], $maxlampo[$i]) = keskiarvo($anturi[$i], $alkuaika, $loppuaika);
}

$plot = substr($plot,0,strlen($plot)-1);

function gnuplot($plot, $ext, $paate) {
  
  if ($paate == "png") {
    $command =    "echo -e 'set term png size 800,480\n";
  } elseif ($paate == "svg") {
    $command =    "echo -e 'set term svg size 800,480\n";
  } else {
    $command =    "echo -e 'set term postscript landscape enhanced color dashed defaultplex \"Helvetica\" 10\n";
  }
  
//  $command .=  "set size 1.3,1\n";
  $command .=  "set ylabel \"\°C\"\n";
  $command .=  "set title \"Lämpötila\"\n";
  $command .=  "set grid ytics 0\n";
  $command .=  "set grid xtics 0\n";
  $command .=  "set timefmt \"%Y-%m-%d %H:%M:%S\"\n";
  $command .=  "set format x \"%d.%m\\\\n%H:%M\"\n";
  $command .=  "set xdata time\n";
  $command .=  "set timestamp \"%d.%m.%Y %H:%M:%S\"\n";
  $command .= $plot;
  $command .=   "'| /usr/bin/gnuplot >/var/www/lampotila/tempgif/$ext.$paate";

// print $command."\n";
  passthru($command);
  //print $command;
  
  //  if ($DEBUG) {
  //     echo  "$xtics<br>\n";
  //  }
}
$paate="svg";
gnuplot($plot, $ext[0], $paate);
//gnuplot($plot, $ext[0], "ps");

if ($paate == "png")
  echo  "<img src=\"tempgif/$ext[0].$paate\" >\n";
if ($paate == "svg")
  echo  "<embed src=\"tempgif/$ext[0].$paate\" type=\"image/svg+xml\" >\n";

echo  "<table width=80% cellspacing=0 cellpadding=2 border=0>\n";

for ($i=0;$i<$maara;$i++) {
  mysql_data_seek($qid,$anturi[$i]);
  $title =  mysql_fetch_row($qid);
  $Title = ucfirst($title[1]);
  /*if(is_long($i/2)) {
   $vari = "silver";
   } else {
   $vari = "gray";
   } */
  echo  "<tr><th>$Title</th><td>Alku: $begin[$i]</td><td>Loppu: $end[$i]</td></tr>\n";
  echo  "<tr><td></td><td>Keskiarvo: $keskilampo[$i]</td><td>Alkiota: $rows[$i] Nyt: ".currentTemperature($i,$alkuaika,$loppuaika)."</td></tr>\n";
  echo  "<tr><td></td><td>Minimi: $minlampo[$i]</td><td>Maksimi: $maxlampo[$i]</td></tr>\n";
}

echo  "<tr><td></td>\n";
echo  "<td align=right></td>";
echo  "</tr>";
echo  "</table>\n";
//echo  "<a href=\"tempgif/$ext[0].ps\">Kuva PostScript-tiedostona</a><br><br>";

for ($i=0;$i<$maara;$i++){
//  unlink ( "/tmp/tempdata/data.$ext[$i]");
}


$aika2 = microtime();

function parsiaika($aika) {
  $mikro = substr($aika,0,10);
  $sekunti = substr($aika,11);
  $dsek = 100000000*$mikro;
  $dsek = lisaanolla($dsek, 8);
  $desaika = $sekunti.".".$dsek;
  return $desaika;
}

$a1 = parsiaika($aika1);
$a2 = parsiaika($aika2);
$ero = $a2 - $a1;
//print $ero."<br>\n";
$ero = round($ero*100)/100;
echo "Suoritusaika: $ero sekuntia<br>";
//print $a1."<br>\n";
//print $a2."<br>\n";
//print $aika1."<br>\n";
//print $aika2."<br>\n";

?>
</center>

</body>
</html>

    
