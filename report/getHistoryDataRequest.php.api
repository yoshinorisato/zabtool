<?php
$authKey = "";
$errorMsg = "";

include "config.php";
$serverIp=$zabtool['SERVER_IP'];


$hostname = '""';
$item = '""';
$selecttimeFrom = '""';
$selecttimeTo = '""';

//ホスト名
if(isset($_REQUEST["hostname"])){
        $zabbixhost = trim($_REQUEST["hostname"],'"');
}

//Event
//"%net.if.in[\"{_NIC%"
if(isset($_REQUEST["item"])){
		$str = str_replace('$',"\\$",$_REQUEST["item"]);
        $item = trim($str,'"');
}

//日付From
if(isset($_REQUEST["selecttimeFrom"])){
        $from= trim($_REQUEST["selecttimeFrom"],'"');
}

//日付To
if(isset($_REQUEST["selecttimeTo"])){
        $to = trim($_REQUEST["selecttimeTo"],'"');
}




require_once 'getAuth_API.php';
//ZabbixAPIの認証チェック
$ret = auth_zabbixAPI($authKey,$errorMsg);

require_once 'getHostDetail.php';
$hostInf = hostInfo($authKey,$zabbixhost);
foreach ( @$hostInf[0]->interfaces as  $interface ){

	//ICMP監視のIPのみ返す
	if (@$interface->type == "1"){
		$ipaddr = @$interface->ip;
		break;
	}
}



//アイテムIDの取得
$itemids = getItemid($authKey,$zabbixhost,$item);
$itemid = $itemids[0]->itemid;
if ($itemid != "" ) {
	$get = getHistList($authKey,$itemid,$from,$to);
}

		
echo  count($get) .",".
	"".$hostInf[0]->host .",". 
	"".$ipaddr .",".
	"".@$hostInf[0]->inventory->name .",".
	"".@$hostInf[0]->inventory->type_full .",".
	"".$hostInf[0]->name .",".
	",".
	",".
	"".@$hostInf[0]->inventory->alias ."<br>";		
		
foreach ( $get as $temp ){
	if(empty($temp))
		break;
	
	echo "\"".strftime('%Y/%#m/%#d',$temp->clock) ."\",".
		"\"".$temp->value ."\",". 
		"\"".$temp->ns ."\"<br>";
	}		


//print_r( $get);
 

 
 
//ヒストリデータの取得
function getHistList ($auth,$itemid,$from,$to)
{
	global $serverIp,$authKey;
	$apiUrl  ="http://$serverIp/zabbix/api_jsonrpc.php";
	
	$json    = array("jsonrpc" => "2.0", "method" => "history.get","auth" => $auth, "params" => array("output" => "extend","history" => 3,"itemids" => $itemid,"time_from" => strtotime($from),"time_till" => strtotime($to),"sortfield" => "clock","sortorder" => "DESC" ), "id" => 4);
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
//ヒストリデータの取得
function getItemid ($auth,$host,$item)
{
	global $serverIp,$authKey,$errorMsg;
	include "config.php";

	$serverIp=$zabtool['SERVER_IP'];

	$apiUrl  ="http://$serverIp/zabbix/api_jsonrpc.php"; 
	$json    = array("jsonrpc" => "2.0", "method" => "item.get","auth" => $auth, "params" => array("host" => $host, "search" => array("key_" => $item ) ), "id" => 4);
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
		//値を返す
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
