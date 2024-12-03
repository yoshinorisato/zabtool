<?php 

$comboIdx = isset($_REQUEST["comboIdx"])?$_REQUEST["comboIdx"]:"";
$minDate = isset($_REQUEST["jquery-ui-datepicker-from"])?$_REQUEST["jquery-ui-datepicker-from"]:"";
$maxDate = isset($_REQUEST["jquery-ui-datepicker-to"])?$_REQUEST["jquery-ui-datepicker-to"]:"";
//session_start();

if( $comboIdx == 1 )
	include("getAlarmAll.php");
elseif($comboIdx == 17 )
	include("getMonthAlerm.php");
else
	echo "Report Error !!  Please confirm your input data.";
