<?php

function get_proxy_hostid( $proxyhostName, $auth ,$nameflg = false)
{
	include "config.php";
    $result="";
	$serverIp = $zabtool['SERVER_IP'];
	if(!isset($proxyhostName)){
		$errorMsg="Get proxyHostName Empty!!";
		return -1;
	}
	
    //プロキシホストＩＤの取得
	$apiUrl  ="http://${serverIp}/zabbix/api_jsonrpc.php"; 
	if(! $nameflg ){
		$json    = array("jsonrpc" => "2.0", "method" => "proxy.get", "params" => array("output" => "extend", "search" => array("host"=>$proxyhostName)),"auth" => $auth, "id" => 2);
	}
	elseif( $nameflg ){
		// 現プロキシIDからプロキシ名を取得
		//$json    = array("jsonrpc" => "2.0", "method" => "proxy.get", "params" => array("output" => "extend", "search" => array("proxyid"=>$proxyhostName)),"auth" => $auth, "id" => 2);
		$json    = array("jsonrpc" => "2.0", "method" => "proxy.get", "params" => array("output" => "extend", "filter" => array("hostid"=>$proxyhostName)),"auth" => $auth, "id" => 2);
	}
	//print_r($json);
	$ch = curl_init($apiUrl);

	curl_setopt($ch,CURLOPT_URL,$apiUrl);
	curl_setopt($ch,CURLOPT_POST,1);
	curl_setopt($ch,CURLOPT_CUSTOMREQUEST, "POST");
	curl_setopt($ch,CURLOPT_POSTFIELDS, json_encode($json));
	curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
	curl_setopt($ch,CURLOPT_HTTPHEADER,array("Content-Type: application/json-rpc"));

	$ret = curl_exec($ch);
   	if (curl_errno($ch) > 0) {
		echo "ERR   ".curl_errno($ch);
		$errorMsg=curl_error($ch);
        	//エラーコードが帰ってきたとき
        	$sid = curl_errno($ch);
		return $sid;
   	}

	$obj = json_decode($ret);
	if (!isset($obj)){
		$errorMsg="Get Null!! Object Failure!! ";
		return -1;
	}
	if (isset($obj->error)){
       	$errorMsg= "{$obj->error[0]->code} : {$obj->error[0]->message} : {$obj->error[0]->data}";
		echo $errorMsg;
       	return -1;
   	}

   	//print("OUTPUT-PROXYID----------------".$obj->result[0]->host."--------END");
	if(! $nameflg){
		$reData = @$obj->result[0]->proxyid;
	}
	else{
		$reData = @$obj->result[0]->host;
		
	}
	return $reData;
}
?>
