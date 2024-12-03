<?php
// ホストIDからインターフェイス情報を取得
function getInterface( $hostid, $auth )
{
	include "config.php";
	global $errorMsg;
	
	$serverIp=$zabtool['SERVER_IP'];
	
	$apiUrl  ="http://$serverIp/zabbix/api_jsonrpc.php";

    $json    = array("jsonrpc" => "2.0", "method" => "hostinterface.get", "params" => array("output" => "extend", "hostids"=>$hostid),"auth" => $auth, "id" => 2);
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
		//echo "HOST ".@$ret->result[0]->templateid." END";
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
