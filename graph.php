<?php
$rrdFile = dirname(__FILE__) . "/speed.rrd";
$outputPngFile = dirname(__FILE__) . "/speed.png";

$creator = new RRDCreator($rrdFile, "now -10d", 500);
$creator->addDataSource("speed:COUNTER:600:U:U");
$creator->addArchive("AVERAGE:0.5:1:24");
$creator->addArchive("AVERAGE:0.5:6:10");
$creator->save();

$updater = new RRDUpdater($rrdFile);
$updater->update(array("speed" => "12345"), "920804700");
$updater->update(array("speed" => "12357"), "920805000");

$graphObj = new RRDGraph($outputPngFile);
$graphObj->setOptions(
    array(
        "--start" => "920804400",
        "--end" => 920808000,
        "--vertical-label" => "m/s",
        "DEF:myspeed=$rrdFile:speed:AVERAGE",
        "CDEF:realspeed=myspeed,1000,*",
        "LINE2:realspeed#FF0000"
    )
);
$graphObj->save();
?> 
