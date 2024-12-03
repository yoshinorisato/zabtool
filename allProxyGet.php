<?php
$errorMsg = "";

//全Zabbixプロキシを取得
function allProxyGet ($authKey)
{
	global $_SERVER_IP;
	global $authKey,$errorMsg;
	global $proxyList;
	
	include "config.php";

	$serverIp=$zabtool['SERVER_IP'];
	
	$user="admin";
	$pass = "zabbix";
	$apiUrl  ="http://${_SERVER_IP}/zabbix/api_jsonrpc.php"; 
	$name    = $user;
	$md5hash = $pass;
	$json    = array("jsonrpc" => "2.0", "method" => "host.get", "params" => array("output" => "extend", "proxy_hosts" => "true"), "auth" => $authKey, "id" => 1);
	$ch = curl_init($apiUrl);
	curl_setopt($ch,CURLOPT_URL,$apiUrl);
	curl_setopt($ch,CURLOPT_POST,1);
	curl_setopt($ch,CURLOPT_CUSTOMREQUEST, "POST");
	curl_setopt($ch,CURLOPT_POSTFIELDS, json_encode($json));
	curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
	curl_setopt($ch,CURLOPT_HTTPHEADER,array("Content-Type: application/json-rpc"));
	$ret = curl_exec($ch);
	$ret = json_decode($ret);

	//何も戻ってこなかったとき
	if(isset($ret->error))
	{
			
		//drupal_set_message('Unable to login. It seems that something is wrong here. Please contact the Site Administrator.', DRUPAL_MSG_TYPE_ERR);
		$errorMsg=$ret->error->message;
		$errorMsg=$ret->error->code . " " .$errorMsg ." ". $ret->error->data;
		return 1; //"Connection Error";
	}
	$proxyList = $ret->result;
	
#print_r("PROXYLIST ".$proxyList[0]->hostid);

//	header("Location: ".$url."index.php?setCookie=1&setSid=".$sid);

        if ($curl_errno > 0) {
		echo "ERR   ".$curl_errno;
		//エラーコードが帰ってきたとき
                $sid = $curl_errno;
        } else {
		//正常時0で返す。
                $sid = 0;
        }
	return $sid;
}
?>
