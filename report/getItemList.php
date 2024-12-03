<?php
$authKey = "";
$errorMsg = "";

include "config.php";
//$serverIp='127.0.0.1';
$serverIp=$zabtool['SERVER_IP'];


if(isset($_REQUEST["hostname"])){
        $hostname = trim($_REQUEST["hostname"]);
}else{
	return -1;
}

require_once 'getAuth_API.php';

//ZabbixAPIの認証チェック
$ret = auth_zabbixAPI($authKey,$errorMsg);


//アイテム一覧の取得
$get = getItemList($authKey,$hostname);

foreach ( $get as  $temp ){
	if(empty($temp))
		break;
	
	echo "\"".$temp->itemid ."\";". 
		"\"".$temp->name ."\";". 
		"\"".$temp->key_ ."\";".
		"\"".$temp->delay ."\";".
		//ステータス0有効1無効
		"\"".$temp->status ."\"<br>";
}

//print $get.",".$authKey;

//ホスト名からホストデータ
function getItemList ($auth, $hostname )
{
    global $errorMsg,$serverIp;
	
	$apiUrl  ="http://$serverIp/zabbix/api_jsonrpc.php"; 
	$json    = array("jsonrpc" => "2.0", "method" => "item.get","auth" => $auth, "params" => array("output" => "extend","filter"=>array("host" =>"$hostname"),"sortfield" => "name","sortorder" => "ASC" ), "id" => 1);
	$ch = curl_init($apiUrl);
	curl_setopt($ch,CURLOPT_URL,$apiUrl);
	curl_setopt($ch,CURLOPT_POST,1);
	curl_setopt($ch,CURLOPT_CUSTOMREQUEST, "POST");
	curl_setopt($ch,CURLOPT_POSTFIELDS, json_encode($json));
	curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
	curl_setopt($ch,CURLOPT_HTTPHEADER,array("Content-Type: application/json-rpc"));

	$ret = curl_exec($ch);

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
		//drupal_set_message('Unable to login. It seems that something is wrong here. Please contact the Site Administrator.', DRUPAL_MSG_TYPE_ERR);
		$errorMsg=$ret->error->message;
		$errorMsg=$ret->error->code . " " .$errorMsg ." ". $ret->error->data;

		return -1; //"Connection Error";
	}
	elseif(isset($ret->result))
	{

		//ホストIDが取得される
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
