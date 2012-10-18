<?php

$tempDir = '/tmp/tempdata/';

if (!file_exists($tempDir)) {
  mkdir ( $tempDir,0755);
}

$hostname = "localhost";
$database = "Lampo";
$username = "root";
$password = "";

mysql_connect($hostname, $username, $password) or
    die("Could not connect: " . mysql_error());
mysql_select_db($database);


$GNUPLOT = '/usr/bin/gnuplot';

class PGData {
    var $filename; // Name of the data file. Can be explicitly specified or automatically generated 
    var $DataList; // This is only useful when $filename is not specified

    /**
     * static method to initialize a data object from an external data file
     * the object is just a wrapper to the file
     **/
    function createFromFile($filename, $legend = '')  {
        $Data = new PGData($legend);
        if (!file_exists($filename) || !is_readable($filename)) {
            print "Error: $filename is not a readable datafile!\n";
            return NULL;
        }
        $Data->filename = $filename;
        return $Data;
    }

    function addDataEntry( $entry ) {
        if (!$filename) {
            $this->DataList[] = $entry;
        } else {
            print "Error: Cannot add an entry into file content [ $this->filename ] !\n";
        }
    }

    function dumpIntoFile( $filename='' ) {
        if ($this->filename) {
            print "Error: Data file exists [ $this->filename ] !\n";
            return;
        }
        global $tempDir;
        if (!$filename) {
            // generate a file name
            $filename = tempnam($tempDir, "data");
            global $toRemove;
            $toRemove[] = $filename;
        }
        $fp = fopen($filename, 'w');
        foreach( $this->DataList as $entry ) fwrite($fp, implode("\t", $entry)."\n");
        fclose($fp);
        $this->filename = $filename; // no longer changeable
    }
}


class GNUPlot {
    var $ph = NULL;
    var $toRemove;
    var $plot;
    var $splot;
    var $command;
    var $plotcommand;
    var $termcommand;
    var $width = 640;
    var $height = 480;
    var $format = 'svg';

    function GNUPlot() {
        $this->toRemove = array();
        $this->plot = 'plot';
        $this->splot = 'splot';
        $this->command = array();
        $this->plotcommand = array();
    }

    function set2DLabel($labeltext, $x, $y, $justify='', $pre='', $extra='' ) {
        // $justify =  {left | center | right}
        // $pre = { first|second|graph|screen }

        $this->exe( "set label \"". $labeltext ."\" at $pre $x,$y $extra\n");
    }

    function setRange( $dimension, $min, $max, $extra='' ) { 
        // $dimension = x, y, z ...... 
        if (!$dimension) $dimension = 'x'; 
        $this->exe( "set ${dimension}range [$min:$max] $extra\n"); 
    } 

    // low level set command 
    function set( $toSet ) { 
        $this->exe( "set $toSet\n"); 
    } 

    function setTitle( $title, $extra='' ) { 
        $this->exe( "set title \"$title\" $extra\n"); 
    } 

    // Set label for each axis 
    function setDimLabel( $dimension, $text, $extra='' ) { 
        // $dimension = x, y, z ...... 
        $this->exe( "set ${dimension}label \"$text\" $extra\n"); 
    } 

    function setTics( $dimension, $option ) { 
        // $dimension = x, y, z ...... 
        $this->exe( "set ${dimension}tics $option \n" ); 
    } 
     
    function setSize( $x, $y, $extra='' ) {
        if (is_numeric($x) or is_int($x)) {
            $this->width = $x;
        }
        if (is_numeric($y) or is_int($y)) {
            $this->height = $y;
        }
        $this->setTerm();
    }
    
    function getWidth() {
        return $this->width;
    }

    function getHeight() {
        return $this->height;
    }

    function setTerm($format = '') {
        if ($format == 'svg' or $format == 'png') {
            $this->format = $format;
        }
        $term = $this->format;
        if ($this->format == 'png') {
            $term = 'pngcairo transparent';
        }
        $this->termcommand = "set term $term size $this->width,$this->height\n";
    }

    function getTerm($req = 'array') {
        if ($req == 'array')
            return array("format" => $this->format, "width" => $this->width, "height" => $this->height);
        if ($req == 'format')
            return $this->format;    
        if ($req == 'width')
            return $this->width;    
        if ($req == 'height')
            return $this->height;    
    } 

    function plotData(  &$PGData, $method, $using, $axes='', $extra='' ) { 
        /** 
         * This function is for 2D plotting 
         * 
         * $method is `lines`, `points`, `linespoints`, `impulses`, `dots`, `steps`, `fsteps`,  
         *              `histeps`, errorbars, `xerrorbars`, `yerrorbars`, `xyerrorbars`, errorlines,  
         *              `xerrorlines`, `yerrorlines`, `xyerrorlines`, `boxes`, `filledcurves`,  
         *              `boxerrorbars`, `boxxyerrorbars`, `financebars`, `candlesticks`, `vectors` or pm3d  
         * 
         * $using is an expression controlling which data columns to use and how to use: 
         *             Example : $using = " 1:2 " means plotting column 2 against column 1 
         *                      $using = " ($1):($2/2)  " means use half of the value of column 2 to plot against column 1 
         *            You can introduce in more than 2 or 3 columns to enable styles like errorbars 
         **/ 
         
        $plot = $this->plot; 
        $PGData->writeDataFile(); 

        $fn = $PGData->filename; 
        $title = $PGData->getName()." (".$PGData->getUnit().")";
        if (count($this->plotcommand) == 0)
            $range =" [\"".$PGData->getStartTime()."\":\"".$PGData->getEndTime()."\"] ";
        else
            $range = '';

        if ($axes) $axes = " axes $axes "; 
        $this->plotcommand[] = " $range \"$fn\" using $using $axes title \"$title\" with $method $extra"; 
    } 

    function export( $pic_filename ) { 
        global $GNUPLOT;

        $command = "'".$this->termcommand;

        foreach ($this->command as $row)
            $command .= $row;

        $command .= $this->plot;
        foreach ($this->plotcommand as $plot)
            $command .= $plot.",";
        $command = substr($command,0,-1);
        $command .= "\n";


        $command .=   "'| $GNUPLOT > $pic_filename";
        passthru("echo ".$command);
    } 

    function exe( $command ) {
        $this->command[] = $command;
    } 
     
} 



class Sensor
{
    var $id;
    var $name;
    var $type;

    function __construct($id, $name, $type, $unit, $value)
    {
        $this->id = $id;
        $this->name = $this->str_encode_utf8($name);
        $this->type = $type;
        $this->unit = $this->str_encode_utf8($unit);
        $this->value = $value;
    }

    private function str_encode_utf8($string) {
        if (mb_detect_encoding($string, 'UTF-8', true) === FALSE) {
            $string = utf8_encode($string);
        }
        return $string; 
    }

    static function get_sensor_array()
    {
        //select Anturit.Anturi,Anturit.nimi, Mittaukset.Lampotila from  Mittaukset inner join Anturit on Anturit.Anturi = Mittaukset.Anturi where Anturit.Anturi = 3 order by Mittaukset.Aika desc limit 1;
        $result = mysql_query("SELECT Anturi, nimi, type, unit FROM Anturit ORDER BY Anturi");
        $i = 0;
        while ($row = mysql_fetch_array($result))
        {
            $q = mysql_query("SELECT Lampotila FROM Mittaukset WHERE Anturi =".mysql_escape_string($row['Anturi'])." ORDER BY Aika DESC LIMIT 1");
            $valrow = mysql_fetch_row($q);
            mysql_free_result($q);
            $value = $valrow[0];
            if (!strcmp($row['type'],"power")) {
                $value = (int) $value;
            }
            $sensors[$i] = new Sensor(
                $row['Anturi'],
                $row['nimi'],
                $row['type'],
                $row['unit'],
                $value
            );
            $i++;
        }
        mysql_free_result($result);
        return $sensors;
    }

    static function find_id($sensors, $id)
    {
        foreach($sensors as $sensor)
        {
            if ($sensor->id == $id) {
                return $sensor;
            }
        }
        return 0;
    }

}



class Temperature extends PGData
{
    protected $dateformat = 'Y-m-d H:i:s';
    protected $endTime;
    protected $startTime;
    protected $sensor    = array();
    protected $avg       = 0;
    protected $numRows;
    protected $min       = 0;
    protected $max       = 0;
    protected $latest    = 0;
    public $filename;
    protected $count     = 0;
    protected $data      = array();
    protected $histogram = False;

    function __construct($sensor, $start, $end, $histogram)
    {
        global $tempDir;
        $this->filename = tempnam($tempDir, "temperaturedata");
        $this->sensor = $sensor;
        $this->name = $this->sensor->name;
        $this->histogram = $histogram;
        $this->setEndTime($end);
        $this->setStartTime($start);
        $this->runQuery();
//        $this->writeDataFile();
    }

    function __destruct()
    {
        if (file_exists($this->filename))
            unlink($this->filename);
    }

    protected function runQuery()
    {
        if ($this->isHistogram())
        {
            $diff_seconds = $this->endTime->getTimestamp() - $this->startTime->getTimestamp();
            if ($diff_seconds > 7*86400) {
                $this->queryTimeIntervalValues("86400 seconds");
            } else {
                $this->queryTimeIntervalValues("3600 seconds");
            }
        } else {
            $this->queryValues();
        }
        $this->queryAvgMinMax();
    }

    protected function queryTimeIntervalValues($interval) {
        $intervalobj = DateInterval::createFromDateString($interval);
        $daterange = new DatePeriod($this->startTime, $intervalobj, $this->endTime);

        foreach ($daterange as $date) {
            $dateEnd = clone $date;
            $dateEnd = $dateEnd->modify($interval);
            $result = mysql_query (
                "SELECT
                    AVG(Lampotila)
                 FROM Mittaukset
                 WHERE
                     Anturi = ".mysql_escape_string($this->sensor->id)."
                     AND Aika
                         BETWEEN '".$date->format('Y-m-d H:i:s')."'
                         AND '".$dateEnd->format('Y-m-d H:i:s')."'");
            $row = mysql_fetch_array($result);
            $value = $row[0]*($intervalobj->format("%s")/3600);
            $this->data[] = array( $date->modify(((int)($intervalobj->format("%s"))/2)." seconds"), $value/1000);
            $this->count += mysql_affected_rows();
            mysql_free_result($result);
        }
    }

    protected function queryValues() {
        $query =
           "SELECT
                Aika,
                Lampotila
            FROM Mittaukset
            WHERE
                Anturi = ".mysql_escape_string($this->sensor->id)."
                AND Aika
                    BETWEEN '".$this->startTime->format('Y-m-d H:i:s')."'
                    AND '".$this->endTime->format('Y-m-d H:i:s')."'";

        /*
        Use unbuffered_query to speed up large queries by not loading the whole
        result to php side. Use temporary table to limit locking of database.
        Also make simple downsampling to limit memory usage both on server and client.
        */
        mysql_query ("CREATE TEMPORARY TABLE TempTable $query");
        $this->count = mysql_affected_rows();
        $result = mysql_unbuffered_query ('SELECT Aika, Lampotila FROM TempTable');
        $skiprows = floor($this->count/2000);
        if ($skiprows < 1) $skiprows = 1;
        for ($currentrow=0,$selectedrow=0; $currentrow<$this->count; $currentrow++)
        {
            $row = mysql_fetch_array($result);
            if ($currentrow == $selectedrow)
            {
                $date = date_create_from_format('Y-m-d H:i:s', $row['Aika']);
                $value = $row['Lampotila'];
                $this->data[] = array($date, $value);
                $selectedrow = $selectedrow + $skiprows;
            }
        }
        mysql_free_result($result);
        mysql_query ('DROP TABLE TempTable');
    }

    protected function queryAvgMinMax() {
        $query =
            "SELECT
                 AVG(Lampotila),
                 MIN(Lampotila),
                 MAX(Lampotila)
             FROM Mittaukset
             WHERE
                 Anturi = ".mysql_escape_string($this->sensor->id)."
                 AND Aika
                     BETWEEN '".$this->startTime->format('Y-m-d H:i:s')."'
                     AND '".$this->endTime->format('Y-m-d H:i:s')."'";
        $result = mysql_query($query);
        $row = mysql_fetch_array($result);
        $this->avg = number_format($row[0],2);
        $this->min = number_format($row[1],2);
        $this->max = number_format($row[2],2);
        mysql_free_result($result);
    }

    public function writeDataFile() {
        $fp = fopen($this->filename, 'w');
    	if ($fp == FALSE) die("could not open file $this->filename\n");
        foreach ($this->data as $data)
        {
            fwrite($fp, $data[0]->format("Y-m-d H:i:s")."\t".$data[1]."\n");
        }
        fclose($fp);
        $this->data = array();
    }

    public function filterSlidingAvg($num) {
        $sum = 0;
        $newdata = $this->data;
        for ($i=0; $i<count($this->data); $i++)
        {
            $sum = $sum + $this->data[$i][1];
            if ($i >= $num)
            {
                $sum = $sum - $this->data[$i-$num][1];
                $newdata[$i][1] = $sum/$num;
            }
        }
        $this->data = $newdata;
    }

    protected function getAvg() { return $this->avg; }
    public function getSampleCount() { return $this->count; }
    protected function getMin() { return $this->min; }
    protected function getMax() { return $this->max; }
    protected function getLatest() { return number_format($this->sensor->value, 2); }
    protected function setStartTime($start) {
        if (!is_object($start) && !$start instanceof DateTime) {
            $this->startTime = new DateTime("now");
            $this->startTime->modify(-$start." hour ago");
        } else {
            $this->startTime = $start;
        }

    }

    function setEndTime($end) {
        if ($end == "now")
        {
            $this->endTime = new DateTime("now");
        } else {
            $this->endTime = $end;
        }
    }
    function setHistogram($bool) { $this->histogram = $bool; }
    function isHistogram() { return $this->histogram; }
    function getStartTime($format='Y-m-d H:i:s') { return $this->startTime->format($format); }
    function getEndTime($format='Y-m-d H:i:s') { return $this->endTime->format($format); }
    function getName() { return $this->name; }
    function getType() { return $this->sensor->type; }
    function getUnit()
    {
        if ($this->isHistogram() and $this->getType() == "power")
            return "kWh";
        return $this->sensor->unit; }

    function getDataArray()
    {
        return (array('sensorid' => $this->sensor->id,
                      'latest' => $this->getLatest(),
                      'avg' => $this->getAvg(),
                      'min' => $this->getMin(),
                      'max' => $this->getMax(),
                      'startTime' => $this->getStartTime(),
                      'endTime' => $this->getEndTime(),
                      'name' => $this->getName(),
                      'unit' => $this->sensor->unit,
                      'type' => $this->sensor->type,
                      'histogram' => $this->histogram,
                      'sampleCount' => $this->getSampleCount() ));
    }

}


class TemperatureGraph extends GNUPlot
{
    var $data;
    var $linewidth = 2;
    var $smooth = '';//'smooth csplines';
    var $dataFormat = 'json';
    var $filename;
    
    function __construct()
    {
        parent::__construct();
        $this->exe("set timefmt \"%Y-%m-%d %H:%M:%S\"\n");
        $this->exe("set format x \"%d.%m\\\\n%H:%M\"\n");
        $this->exe("set xdata time\n");
        $this->exe("set grid xtics\n");
        $this->exe("set grid ytics\n");
        $this->exe("set style fill transparent solid 0.5\n");
        global $tempDir;
        $this->filename = tempnam($tempDir, "temperaturepic");
    }

    function __destruct()
    {
        if (file_exists($this->filename) and $this->getTerm('format') == 'svg' )
            unlink($this->filename);
    }

    function addTemperatureData($data)
    {
        $this->data[] = $data;
    }
    
    function plot()
    {
        $plotOrder = array();
        foreach ($this->data as $data)
        {
            if ($data->getType() == "power" or $data->isHistogram())
                array_unshift($plotOrder, $data);
            else
                $plotOrder[] = $data;
        }

        foreach ($plotOrder as $data)
        {
            if ($data->isHistogram())
                $type = "boxes";
            else
            {
                $data->filterSlidingAvg(5);
                $type = "lines";
            }

            if ($data->getSampleCount() == 0) return;
            if ($data->getType() == "temperature")
            {
                $this->plotData($data, $type, '1:3', '', "$this->smooth lw $this->linewidth" );
            }
            elseif ($data->getType() == "power")
            {
                $linewidth = ($data->isHistogram() ? 1 : $this->linewidth);
                $this->exe("set y2tics border\n");
                $this->plotData($data, $type, '1:3', 'x1y2', "$this->smooth lw $linewidth" ); 
            }
        }
    }
    function setLineWidth($width)
    {
        $this->linewidth = $width;
    }

    function setImageFormat($format)
    {
        $this->setTerm($format);
    }

    function setDataFormat($format)
    {
        $this->dataFormat = $format;
    }

    function setSmooth($bool)
    {
        if ($bool == true)
            $this->smooth = 'smooth csplines';
        else
            $this->smooth = '';
    }

    function getData()
    {
        $this->plot();
        $this->saveGraphFile($this->filename);
        if ($this->getTerm('format') == 'svg') {
            while (!file_exists($this->filename)) {;}
            $string = file_get_contents($this->filename);
            return $string;
        } else {
            return substr(basename($this->filename),14) ;
        }
    }

    function getDataArray()
    {
        foreach ($this->data as $data)
        {
            $dataArray[] = $data->getDataArray();
        }

        $format = $this->getTerm('format');

        $array = array(
                     'graphImage' => $this->getData(),
                     'format' => $format,
                     'data' => $dataArray
                 );

        return $array;
    }

    function getJSONData()
    {
        return json_encode($this->getDataArray());
    }

    function saveGraphFile($filename)
    {
        $this->export($filename);
    }
}
