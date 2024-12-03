<?php
$sessionid = "";
$errorMsg = "";

// セッションIDの取得

$ret = getSessionid();

//認証
function getSessionid ()
{
	include "config.php";
	global $sessionid,$errorMsg;

	$serverIp=$zabtool['SERVER_IP'];
	//$serverIp='127.0.0.1';

	$user="admin";
	$pass = "zabbix";

	//$url     = variable_get('zabbix_bridge_API_url', NULL);
	//$apiUrl  = $url.ZabbixAPI::ZABBIX_API_URL;
	$apiUrl  ="http://$serverIp/zabbix/api_jsonrpc.php"; 
	$name    = $user;
	$md5hash = $pass;
	$json    = array("jsonrpc" => "2.0", "method" => "user.login", "params" => array("user" => $name, "password" => $md5hash ), "id" => 1);
	$ch = curl_init($apiUrl);
	curl_setopt($ch,CURLOPT_URL,$apiUrl);
	curl_setopt($ch,CURLOPT_POST,1);
	curl_setopt($ch,CURLOPT_CUSTOMREQUEST, "POST");
	curl_setopt($ch,CURLOPT_POSTFIELDS, json_encode($json));
	curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
	curl_setopt($ch,CURLOPT_HTTPHEADER,array("Content-Type: application/json-rpc"));

//	$curl_errno="";
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
		return 1; //"Connection Error";
	}
	elseif(isset($ret->result))
	{

		$sessionid = $ret->result;
		echo $sessionid;
	}

//	header("Location: ".$url."index.php?setCookie=1&setSid=".$sid);

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
