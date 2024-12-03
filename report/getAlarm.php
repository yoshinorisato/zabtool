<?php 

include "config.php";

//$serverIp="127.0.0.1";
$serverIp=$zabtool['SERVER_IP'];

$errorMsg = "";
$hostname = isset($_REQUEST["hostname"])?$_REQUEST["hostname"]:"";
$auth = isset($_REQUEST["auth"])?$_REQUEST["auth"]:"";
$itemkey = isset($_REQUEST["item"])?$_REQUEST["item"]:"";
$minDate = isset($_REQUEST["selecttimeFrom"])?$_REQUEST["selecttimeFrom"]:"";
$maxDate = isset($_REQUEST["selecttimeTo"])?$_REQUEST["selecttimeTo"]:"";

$value = "";
$csvData="";
//ホストIDの取得
$hostid=getHostid($hostname,$auth);


if(isset($hostid)){
	if($hostid == -1){
		echo "-1,ホストID取得失敗!!".$errorMsg;
	}
	elseif ($hostid >= 0){
		$ret = getEvent( trim($minDate),trim($maxDate),trim($hostid),trim($auth),trim($itemkey) );

		$count=0;
		//取得データの表示
		foreach ($ret->result as $value){

			// アイテムキー
			$getitemkey = $value->items[0]->key_;
			//ダブルクォーテーションの削除
			$itemkey = str_replace("\"","", trim($itemkey));

			// アイテムキーのチェック			
			if(trim($itemkey) <> $getitemkey ) {

				continue;
			}

			//$str = str_replace('<br>',"\r\n",$value);

			//発生日時
			$clock = $value->clock;
			$eventtime=date("Y-m-d H:i:s",$clock);

			//発生復旧
			$status = $value->value;
			$mess = "";
			if($status == 0){
				$mess = "復旧";
			}
			if($status == 1){
				$mess = "発生";
			}

			//トリガー名
			$comment = $value->triggers[0]->description;
			// アイテム名
			$getitemname = $value->items[0]->name;

			$count++;
			
			$csvData = $csvData.$eventtime.",".$mess .",".$comment."<br>";


		}

		//CSV作成
		if($count > 0 ){
			$csvData = $count."<br>".$csvData;
			echo $csvData;
		}
		else if ($count <= 0 ){
			$csvData = $count."<br>".$errorMsg;
			echo $csvData.$errorMsg;
		}
		//print_r($ret->result);
	}
}


//イベントリストの取得
function getEvent( $mindate,$maxdate,$hostid,$auth,$item )
{

	global $serverIp,$errorMsg;

	$minYear=date('Y',strtotime($mindate));
	$minMonth=date('m',strtotime($mindate));
	$minDay=date('d',strtotime($mindate));
	$UtimeMin = mktime(0,0,0,$minMonth,$minDay,$minYear);
	$maxYear=date('Y',strtotime($maxdate));
	$maxMonth=date('m',strtotime($maxdate));
	$maxDay=date('d',strtotime($maxdate));	
	$UtimeMax = mktime(0,0,0,$maxMonth,$maxDay,$maxYear);

	//一日足す
	$UtimeMax=$UtimeMax+86400;

	$apiUrl  ="http://$serverIp/zabbix/api_jsonrpc.php"; 
	
	$json    = array(
		"jsonrpc" => "2.0",
		"method" => "event.get",
		"auth" => $auth,
		"params" => array(
			"output"=>"extend",
			"selectItems"=> $item,
			"time_from"=> $UtimeMin,
			"time_till"=> $UtimeMax,
			"hostids"=>array( $hostid ),
			"value"=>array( 0,1 ),
			 "selectItems"=>"extend", 
			 "filter"=>array("value_changed"=>1 ),
			 "selectTriggers"=>"extend"),
		"id" => 4 );

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

		//イベント
		return $ret;
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

//ホスト名からホストIDを取得
function getHostid( $hostName, $auth )
{	
	global $serverIp,$errorMsg;

	$apiUrl  ="http://$serverIp/zabbix/api_jsonrpc.php"; 
	$json    = array("jsonrpc" => "2.0", "method" => "host.get","auth" => $auth, "params" => array("output" => "extend", "filter" => array("host" => $hostName ) ), "id" => 4);

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
		return @$ret->result[0]->hostid;
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
