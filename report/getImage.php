<?php
$outfilename = "";
//$serverIp = '127.0.0.1';
include "config.php";
$serverIp=$zabtool['SERVER_IP'];

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

if(isset($_REQUEST["selecttimeFrom"])){
    $datetime = new DateTime( trim($_REQUEST["selecttimeFrom"],'"') );
    $utimeFrom = $datetime->format( 'U' );
	$selecttimeFrom =  $datetime->format('YmdHis');
}
$dat = "";
if(isset($_REQUEST["selecttimeTo"])){

    $dat = trim($_REQUEST["selecttimeTo"],'"');
	$udateTo =  date("Y-m-d",strtotime($dat . " +1 day"));
	
    $datetime = new DateTime( $udateTo );
    $utimeTo = $datetime->format( 'U' );
}

// 出力ファイル名
if(isset($_REQUEST["outfile"])){
        $outfilename = trim($_REQUEST["outfile"],'"');
}

//期間（秒数)を取得
if ( isset($utimeFrom) or isset($utimeTo) ){
	$termSec = $utimeTo - $utimeFrom;
}

//グラフIDを取得する
$ret = getGraphID($auth,$hostname,$graphname);

if(isset($ret)){
	$graphid = $ret->result[0]->graphid;
	if(isset($graphid)){
		outputImage($auth,$graphid,$selecttimeFrom,$termSec,$hostname,$outfilename);
	}
}
// グラフ名で検索ヒットしなかった場合、
elseif($ret == -2 ){
	echo "ERROR ";
}

//画像ファイルのアウトプット
function outputImage ($auth,$graphid,$selecttimeFrom,$termSec,$hostname,$outfilename){

	global $serverIp,$errorMsg;
	$sessionid = "";
	
	$fpath = 'out/'. $outfilename;	

	// セッションIDの取得
	$CMD = "/usr/bin/curl -b zbx_sessionid=$auth \"http://$serverIp/zabbix/chart2.php?graphid=$graphid&width=700&period=$termSec&stime=$selecttimeFrom&isNow=0\" > \"$fpath\"";

	$result=exec($CMD , $ret);

	error_log($CMD);

	// パスが設定されていたらファイルをダウンロードする。
	if (strlen($fpath) > 0) {

	    // ************************************************************************
	    // ファイル ダウンロード
	    // ************************************************************************

	    // ファイルの存在チェック
	    if (file_exists($fpath)) {
	    	// サイズを取得
			$file_length = filesize($fpath);
			// ファイルヘッダの設定
			header("Content-Disposition: attachment; filename=".$outfilename);

			// ファイルサイズの設定
			header("Content-Length:$file_length");
			header("Content-Type: application/octet-stream");
			header("Connection: close");
			ob_end_clean();
			readfile ($fpath);

			// ファイル消す
			unlink ($fpath);
			exit();
		}
	}
}

//グラフIDの取得
function getGraphID( $auth, $hostn,$gname)
{

global $serverIp,$errorMsg;

	$apiUrl  ="http://$serverIp/zabbix/api_jsonrpc.php";
	
	$json    = array(
		"jsonrpc" => "2.0",
		"method" => "graph.get",
		"auth" => $auth,
		"params" => array(
			"output"=>"extend",
			 "filter"=>array("host"=>$hostn,"name"=>$gname  ),
			 ),
		"id" => 14 );
	
	$ch = curl_init($apiUrl);
	curl_setopt($ch,CURLOPT_URL,$apiUrl);
	curl_setopt($ch,CURLOPT_POST,1);
	curl_setopt($ch,CURLOPT_CUSTOMREQUEST, "POST");
	curl_setopt($ch,CURLOPT_POSTFIELDS, json_encode($json));
	curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
	curl_setopt($ch,CURLOPT_HTTPHEADER,array("Content-Type: application/json-rpc"));
	
	//実行
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
		// 
		if(!isset($ret->result[0])){
			//エラー時の戻り値
			return -2;
		}
	
		//グラフID
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
?>
