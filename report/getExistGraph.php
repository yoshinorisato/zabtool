<?php
$outfilename = "";

include "config.php";
global $errorMsg;

$serverIp=$zabtool['SERVER_IP'];
//$serverIp = '127.0.0.1';
	
$auth = isset($_REQUEST["auth"])?$_REQUEST["auth"]:"";

if(isset($_REQUEST["hostname"])){
        $hostname = trim($_REQUEST["hostname"],'"');
}

//グラフ名の取得
if(isset($_REQUEST["graphname"])){
	//UTF-8のurlエンコードがされているので、普通にデコード
	$str = urldecode($_REQUEST["graphname"]);	
	$graphname = trim($str,'"');
}

//グラフIDを取得する
$ret = getExistGraph($auth,$hostname,$graphname);
//存在しなかったらFALSE
if ( $ret == "" Or !(isset($ret)) ) {
	
	print "FALSE";
}
else{
	print "TRUE";
}



//グラフIDの取得
function getExistGraph( $auth, $hostn,$gname)
{

global $serverIp,$errorMsg;

	$apiUrl  ="http://$serverIp/zabbix/api_jsonrpc.php";
	
	$json    = array(
		"jsonrpc" => "2.0",
		"method" => "graph.exists",
		"auth" => $auth,
		"params" => array(
			"host"=>$hostn,
			 "name"=>$gname
			 ),
		"id" => 11 );
	
	$ch = curl_init($apiUrl);
	curl_setopt($ch,CURLOPT_URL,$apiUrl);
	curl_setopt($ch,CURLOPT_POST,1);
	curl_setopt($ch,CURLOPT_CUSTOMREQUEST, "POST");
	curl_setopt($ch,CURLOPT_POSTFIELDS, json_encode($json));
	curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
	curl_setopt($ch,CURLOPT_HTTPHEADER,array("Content-Type: application/json-rpc"));
	
	$ret = curl_exec($ch);

error_log( json_encode($json));

	if (curl_errno($ch) > 0) {
	
		//echo "ERR   ".curl_errno($ch);
		$errorMsg=curl_error($ch);
    	//エラーコードが帰ってきたとき
    	$sid = curl_errno($ch);
		return $sid ;
	}

	$ret = json_decode($ret);

	//何も戻ってこなかったとき
	if(isset($ret->error))
	{
		$errorMsg=$ret->error->message;
		$errorMsg=$ret->error->code . " " .$errorMsg ." ". $ret->error->data;

		return -1; //"Connection Error";
	}
	elseif(isset($ret->result))
	{

		//IDが取得される
		return @$ret->result;
	}
	
	if (curl_errno($ch) > 0){
		echo "ERR   ".curl_errno($ch);
		//エラーコードが帰ってきたとき
    	$sid = $curl_errno;
    } else {
		//正常時0で返す。
       	$sid = 0;
    }

	return $sid;
}
?>
