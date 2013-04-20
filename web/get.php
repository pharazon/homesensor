<?php

$selectRequestVariables = array(
                              "fileid",
                          );
foreach ($selectRequestVariables as $selectRequestVar) {
    eval('$GLOBALS["'.$selectRequestVar.'"] = $'.
        $selectRequestVar.' = isset($_REQUEST["'.
        $selectRequestVar.'"]) ? $_REQUEST["'.
        $selectRequestVar.'"] : "";');
}

$filename = "/tmp/tempdata/temperaturepic".$fileid;
if (dirname(realpath($filename)) == "/tmp/tempdata") {
    $file = file_get_contents($filename);
    unlink($filename);
    header("Content-Type: image/png");
    echo $file;
}
