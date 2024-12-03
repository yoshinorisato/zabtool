<?php
function imputHost( $hostj, $auth )
{
	include "config.php";
	global $authKey,$errorMsg;
   	$result="";
	$serverIp = $zabtool['SERVER_IP'];
	if(!isset($hostj)){
		$errorMsg="Get proxyHostName Empty!!";
		return -1;
	}

    //ホストＩＤの取得
	$apiUrl  ="http://${serverIp}/zabbix/api_jsonrpc.php"; 


//print_r($hostj);

	$ch = curl_init($apiUrl);

	curl_setopt($ch,CURLOPT_URL,$apiUrl);
	curl_setopt($ch,CURLOPT_POST,1);
	curl_setopt($ch,CURLOPT_CUSTOMREQUEST, "POST");
	curl_setopt($ch,CURLOPT_POSTFIELDS, json_encode($hostj));
	curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
	curl_setopt($ch,CURLOPT_HTTPHEADER,array("Content-Type: application/json-rpc"));

	//登録の実行
	$ret = curl_exec($ch);
	if (curl_errno($ch) > 0) {
		echo "ERR   ".curl_errno($ch);
		$errorMsg=curl_error($ch);

    	//エラーコードが帰ってきたとき
    	$sid = curl_errno($ch);
		return $sid ;
	}

	$obj = json_decode($ret,true);
	if (!isset($obj)){
		$errorMsg="登録結果が受け取れませんでした。";
		return -1;
	}

	//エラーが返ってきたとき
	if (isset($obj['error']['code'])){
    		$errorMsg= "{$obj['error']['code']} : {$obj['error']['message']} : {$obj['error']['data']}";
       		return -1;
   	}
    return $obj['result']['hostids'][0];
}
?>
