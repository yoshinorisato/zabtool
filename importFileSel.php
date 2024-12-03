<?php
$officeItemList = isset($_REQUEST["officeItemList"])?$_REQUEST["officeItemList"]:"";
//error_log($officeItemList);

require_once ('./auth_zabbixAPI.php');

global $_SERVER_IP;
global $authKey,$errorMsg;
global $proxyList;
//認証
$retmsg = auth_zabbixAPI();
if( $retmsg  != 0){
	echo "CODE:". $retmsg." ".$errorMsg;
	return;
}

create_host($authKey);	

$proxyList = array();

//include("./allProxyGet.php");
//include("./importDB.php");

//ホストの登録
function create_host($auth)
{
	
	//インポート画面の取得 
	$ret=file_get_contents("http://127.0.0.1/zabtool/import.php?auth=.$auth",true);
	echo $ret;

	//importDB.php

//	$ret = allProxyGet($authKey);
//	print_r( $proxyList);
}
?>
