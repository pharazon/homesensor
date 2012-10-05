<?

$tempDir = '/tmp/tempdata/';

if (!file_exists($tempDir)) {
  echo  "Creating ".$tempDir."...<p>";
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

// DONT change the code below if you dont know what you are doing 
$IDCounter = 0; 


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

    function addDataEntry( $entry ){ 
        if (!$filename) $this->DataList[] = $entry; 
            else print "Error: Cannot add an entry into file content [ $this->filename ] !\n"; 
         
    } 
     
    function dumpIntoFile( $filename='' ) { 
        if ($this->filename) { print "Error: Data file exists [ $this->filename ] !\n"; return; } 
        global $tempDir, $IDCounter; 
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

    function set2DLabel($labeltext, $x, $y, $justify='', $pre='', $extra='' )  
    { 
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
        $this->width = $x;
        $this->height = $y;
        $this->setTerm();
    }

    function setTerm($format = '') {
        if ($format != '')
            $this->format = $format;
        $this->termcommand = "set term $this->format size $this->width,$this->height\n";
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
        if (!$PGData->filename) $PGData->dumpIntoFile(); 
        if (!$PGData->filename) { print "Error: Empty dataset!\n"; return; } 

        $fn = $PGData->filename; 
        $title = $PGData->getName();
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
    
    function __construct($id, $name, $type)
    {
        $this->id = $id;
        $this->name = $name;
        $this->type = $type;
    }
    
    static function get_sensor_array()
    {
        $result = mysql_query("select Anturi, nimi, type from Anturit order by Anturi");
        $i = 0;
        while ($row = mysql_fetch_array($result))
        {
            $sensors[$i] = new Sensor($row['Anturi'], $row['nimi'], $row['type']);
            $i++;
        }
        mysql_free_result($result);
        return $sensors;
    }
    
    static function find_id($sensors, $id)
    {
        foreach($sensors as $sensor)
        {
            if ($sensor->id == $id)
                return $sensor;
        }
        return 0;
    } 

}



class Temperature extends PGData
{
    var $dateformat = 'Y-m-d H:i:s';
    var $endTime;
    var $startTime;
    var $sensor;
    var $avg;    
    var $numRows;
    var $min;
    var $max;
//    var $filename;
    var $count;
    
    function __construct($sensor, $start, $end)
    {
        global $tempDir;
        $this->filename = tempnam($tempDir, "temperaturedata");
        $this->sensor = $sensor;
        $this->setEndTime($end);
        $this->setStartTime($start);
        $this->runQuery();
    }

    function __destruct()
    {
        if (file_exists($this->filename))
            unlink($this->filename);
    }

    private function runQuery()
    {
        $query=
        "select Aika, Lampotila from Mittaukset
         where Anturi = ".$this->sensor->id." 
         and Aika between '".$this->startTime->format('Y-m-d H:i:s')."'
         and '".$this->endTime->format('Y-m-d H:i:s')."'";
        $result = mysql_query($query);
        $fp = fopen($this->filename, 'w');
	if ($fp == FALSE) die("could not open file $this->filename\n");
        while ($row = mysql_fetch_array($result))
        {
            fwrite($fp, $row['Aika']."\t".$row['Lampotila']."\n");
        }
        $this->count = mysql_num_rows($result);
        fclose($fp);
        mysql_free_result($result);

        $query=
        "select AVG(Lampotila), MIN(Lampotila), MAX(Lampotila) from Mittaukset
         where Anturi = ".$this->sensor->id." 
         and Aika between '".$this->startTime->format('Y-m-d H:i:s')."'
         and '".$this->endTime->format('Y-m-d H:i:s')."'";
        $result = mysql_query($query);
        $row = mysql_fetch_array($result);
        $this->avg = $row[0];
        $this->min = $row[1];
        $this->max = $row[2];
        mysql_free_result($result);
        
        $query= "select nimi from Anturit where Anturi = ".$this->sensor->id."";
        $result = mysql_query($query);
        $row = mysql_fetch_array($result);
        $this->name = $row[0];
        mysql_free_result($result);
    }
    
    function getAvg() { return $this->avg; }
    function getSampleCount() { return $this->count; }
    function getMin() { return $this->min; }
    function getMax() { return $this->max; }
    function setStartTime($start) {
        if ($start < 0)
        {
            $this->startTime = new DateTime("now");
            $this->startTime->modify(-$start." hour ago");
        }
        else
        {
            $this->startTime = $start;
        }

    }

    function setEndTime($end) {
        if ($end == "now")
        {
            $this->endTime = new DateTime("now");
        }
        else
        {
            $this->endTime = $end;
        }
    }

    function getStartTime($format='Y-m-d H:i:s') { return $this->startTime->format($format); }
    function getEndTime($format='Y-m-d H:i:s') { return $this->endTime->format($format); }
    function getName() { return $this->name; }

    function getDataArray()
    {
        return (array('avg' => $this->getAvg(),
                      'min' => $this->getMin(),
                      'max' => $this->getMax(),
                      'startTime' => $this->getStartTime(),
                      'endTime' => $this->getEndTime(),
                      'name' => $this->getName(),
                      'sampleCount' => $this->getSampleCount() ));
    }

}


class TemperatureGraph extends GNUPlot
{
    var $data;
    var $linewidth = 2;
    var $smooth = 'smooth csplines';
    var $dataFormat = 'json';
    
    function __construct()
    {
        parent::__construct();
        $this->exe("set timefmt \"%Y-%m-%d %H:%M:%S\"\n");
        $this->exe("set format x \"%d.%m\\\\n%H:%M\"\n");
        $this->exe("set xdata time\n");
        $this->exe("set grid xtics 0\n");
        $this->exe("set grid ytics 0\n");
    }

    function addTemperatureData($data)
    {
        $this->data[] = $data;
        if (strcmp($data->sensor->type,"temperature") == 0) 
            $this->plotData($data, 'lines', '1:3', '', "$this->smooth lw $this->linewidth" );
        elseif (strcmp($data->sensor->type,"power") == 0) {
            $this->exe("set y2tics border\n");
            $this->plotData($data, 'lines', '1:3', 'x1y2', "$this->smooth lw $this->linewidth" ); 
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
        global $tempDir;
        $filename = tempnam($tempDir, "temperaturepic");
        $this->saveGraphFile($filename);
        while (!file_exists($filename)) {;}
        $string = file_get_contents($filename);
        unlink($filename);
        return $string;
    }

    function getDataArray()
    {
        foreach ($this->data as $data)
        {
            $dataArray[] = $data->getDataArray();
        }
        
        $format = $this->getTerm('format');

        $array = array('graphImage' => $this->getData(), 'format' => 'svg', 'data' => $dataArray);

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



?>
