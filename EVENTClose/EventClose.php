<?php
$time_from = isset($_REQUEST["datetimepicker"])?$_REQUEST["datetimepicker"]:"";
//session_start();
include "../config.php";

//未クローズイベントの取得
require_once ('../getUncloseProblem.php');

$request = json_decode(file_get_contents("php://input"), true);
$value = 0;

//認証
require_once ('../auth_zabbixAPI.php');
auth_zabbixAPI();

global $authKey,$errorMsg;

$eventList = getUncloseProblemList($authKey,$time_from);

for( $i = 1; $i < count($eventList->result)-1; $i++){
    $ee = $eventList->result[i];
    echo $ee;
}

$json = json_encode($eventList, JSON_UNESCAPED_UNICODE);

header("Content-Type: application/json; charset=UTF-8");
//echo $json;
exit;
?>
