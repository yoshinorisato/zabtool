<?php 

$comboIdx = isset($_REQUEST["comboIdx"])?$_REQUEST["comboIdx"]:"";
$minDate = isset($_REQUEST["jquery-ui-datepicker-from"])?$_REQUEST["jquery-ui-datepicker-from"]:"";
$maxDate = isset($_REQUEST["jquery-ui-datepicker-to"])?$_REQUEST["jquery-ui-datepicker-to"]:"";
//session_start();

if( $comboIdx == 1 )
	include("getAlarmAll.php");
elseif($comboIdx == 2 )
	include("getAlarmICMP.php");
elseif($comboIdx == 3 )
	include("getAlarmMemory.php");
elseif($comboIdx == 4 )
	include("getAlarmLinkUp.php");
elseif($comboIdx == 5 )
	include("getAlarmLinkDown.php");
elseif($comboIdx == 6 )
	include("getAlarmColdStart.php");
elseif($comboIdx == 7 )
	include("getAlarmCPU.php");
elseif($comboIdx == 8 )
	include("getAlarmTEMP.php");
elseif($comboIdx == 9 )
	include("getAlarmBandUseIn.php");
elseif($comboIdx == 10 )
	include("getAlarmBandUseOut.php");
elseif($comboIdx == 11 )
	include("getAlarmStorm.php");
elseif($comboIdx == 12 )
	include("getAlarmConfigChg.php");
elseif($comboIdx == 13 )
	include("getAlarmAPIFUP.php");
elseif($comboIdx == 14 )
	include("getAlarmAPIFDOWN.php");
elseif($comboIdx == 15 )
	include("getAlarmMajor.php");
elseif($comboIdx == 16 )
	include("getAlarmMinor.php");
elseif($comboIdx == 17 )
	include("getMonthAlerm.php");
else
	echo "Report Error !!  Please confirm your input data.";
