<?php

//グループIDの取得
$checkidList = isset($_REQUEST['id'])?$_REQUEST['id']:"";
require_once ('./auth_zabbixAPI.php');

global $_SERVER_IP;
global $authKey,$errorMsg;
global $proxyList;

//認証
$retmsg = auth_zabbixAPI();
if( $retmsg != 0){
	echo "CODE:". $retmsg." ".$errorMsg;
	return;
}

export_host($authKey,$checkidList);

$proxyList = array();

//ホスト情報のエクスポート
function export_host($auth,$checkidList)
{
	require_once ('getHostTbl.php');
	require_once ('getHostGroupid.php');
	require_once ('getInterface.php');
	require_once ('getProxyHostid.php');
	require_once ('getMacro.php');
	
	global $authKey,$errorMsg;

	//CSVファイルに出力
	$outputFile = mb_convert_encoding("ホストデータ".date('ymdHis'),"SJIS","UTF-8").".csv";
	
	//ダイレクトにWEB上出力
//	$fp = fopen('php://output', "w") or die("OPENエラー "files/".$outputFile");
	
	// 2MB以内はメモリに出力2MB以上は「/tmp」に出力sys_temp_dir
	$fp = fopen('php://temp', "r+") or die("OPENエラー $outputFile");

	//ホストテーブルの取得
	$ret=getHostTbl("",$auth,true);

	$i = 0;

	//CSVを作成してデータを出力
	foreach( $ret->result as $lineary){
		$addlist = "";
		// タイトル行の取得
		if ($i == 0){
			$addlist = mb_convert_encoding("ホストID,ホスト名,ホスト名(表示名),グループ名,IPアドレス,テンプレート,プロキシ名,有効/無効,インターフェイス名,拠点名,カスタマ名,機種,実IP,予備1,予備2,予備3,予備4,予備5,マクロ1,マクロ1の値,マクロ2,マクロ2の値,マクロ3,マクロ3の値,マクロ4,マクロ4の値,マクロ5,マクロ5の値\n","SJIS","UTF-8");
		}
		
		//ホストID追加
		$addlist =$addlist.mb_convert_encoding($lineary->hostid,"SJIS","UTF-8");
		
		//ホスト名
		$addlist =$addlist.",".mb_convert_encoding($lineary->host,"SJIS","UTF-8") .",";

		//表示名
		$addlist = $addlist.mb_convert_encoding($lineary->name,"SJIS","UTF-8") .",";
		
		//グループ名
		$groupList=$lineary->groups;
		$groupname = "";
		$cont=0;
		$addgroup = "";
		$gexist_flg = false;
		foreach ( $groupList as $groupname ){
			if ( $groupname=="" ) {
				break;
			}
			if(!($gexist_flg)){
				foreach( $checkidList as $checkedgid ){
					// 取得対象のホストかチェック
					if($groupname->groupid == $checkedgid ){
						$gexist_flg=true;
						//１番手前のループブレーク
						break 1;
					}
				}
			}
			
			// グループ名
			if($cont == 0 ){
				$addgroup = $addgroup.mb_convert_encoding($groupname->name,"SJIS","UTF-8");
			}
			else{
				$addgroup = $addgroup.";".mb_convert_encoding($groupname->name,"SJIS","UTF-8");
			}
			
			$cont = $cont + 1;
		}
		
		//選択したグループの時のみ
		if($gexist_flg){
			$addlist = $addlist.$addgroup;

			//インターフェイステーブルの取得
			$retinterface=getInterface($lineary->hostid,$auth);

			// インターフェイステーブの取得
			$cnt=0;
			foreach ( $retinterface as  $interface ){
				if ( $interface == "" || $cnt > 0 ){
					break;
				}

				//インターフェイスタイプ
//				if($interface->type == 0 ){
//					 $addlist = $addlist . mb_convert_encoding(",UNKNOWN","SJIS","UTF-8");
//				}else if($interface->type == 1 ){
//					 $addlist = $addlist. mb_convert_encoding(",AGENT","SJIS","UTF-8");
//				}else if($interface->type == 2 ){
//					 $addlist = $addlist.mb_convert_encoding(",SNMP","SJIS","UTF-8");
//				}else if($interface->type == 3 ){
//					 $addlist = $addlist.mb_convert_encoding(",IPMI","SJIS","UTF-8");
//				}else if($interface->type == 4 ){
//					 $addlist = $addlist.mb_convert_encoding(",JMX","SJIS","UTF-8");
//				}

				// IPアドレス
				$addlist = $addlist.mb_convert_encoding(",".$interface->ip,"SJIS","UTF-8");

				// DNS名
//				$addlist = $addlist.",".mb_convert_encoding($interface->dns,"SJIS","UTF-8");

				//接続方法
//				if($interface->useip == 0 ){
//					 $addlist = $addlist.mb_convert_encoding(",DNS","SJIS","UTF-8");
//				}else if($interface->useip == 1 ){
//					 $addlist = $addlist.mb_convert_encoding(",NODNS","SJIS","UTF-8");
//				}

				//Main/Slave
//				if($interface->main == 0 ){
//					//Secondary
//					 $addlist = $addlist.",Secondary";					 
//				}else if($interface->main == 1 ){
//					 $addlist = $addlist.",Primary";
//				}

				//Zabbixポート番号
//				if($interface->port == "" ){
//					//Secondary
//					$addlist = $addlist.",10050";
//				}else if($interface->port >= 0 ){
//					 $addlist = $addlist.",".$interface->port;
//				}
				$cnt=$cnt+1;
			}

			//テンプレート
			$cont = 0;
			$templeteList=$lineary->parentTemplates;
			if(@$templeteList[0]->host == "" ){
				$addlist = $addlist.",";
			}
			else{

				foreach ( $templeteList as $templete ){
					if ( $templete=="" ) {
						break;
					}
					// テンプレート名
					if($cont == 0 ){
						$addlist = $addlist.",".mb_convert_encoding($templete->host,"SJIS","UTF-8");
					}
					else{
						$addlist = $addlist.";".mb_convert_encoding($templete->host,"SJIS","UTF-8");
					}
					$cont = $cont + 1;
				}
			}

			//プロキシ名
			$retproxy = "";
			if($lineary->proxy_hostid > 0){
				$retproxy=get_proxy_hostid($lineary->proxy_hostid,$auth,true);
			}

			if($retproxy != ""){
				$addlist = $addlist.",".mb_convert_encoding($retproxy,"SJIS","UTF-8");
			}
			else{
				$addlist = $addlist.",";
			}

			//有効無効
			if($lineary->status== 0 ){
				//Secondary
				 $addlist = $addlist.",Enable";
			}else if($interface->main == 1 ){
				 $addlist = $addlist.",Disable";
			}

			//インターフェイス名
			if( @$lineary->inventory->type == "" ){
				$addlist = $addlist.",";
			}
			else{
				$addlist = $addlist.",".mb_convert_encoding($lineary->inventory->type,"SJIS","UTF-8");
			}
			//拠点名
			if( @$lineary->inventory->type_full == "" ){
				$addlist = $addlist.",";
			}
			else{
				$addlist = $addlist.",".mb_convert_encoding($lineary->inventory->type_full,"SJIS","UTF-8");
			}
			
			
			//カスタマ名
			if( @$lineary->inventory->name == "" ){
				$addlist = $addlist.",";
			}
			else{
				$addlist = $addlist.",".mb_convert_encoding($lineary->inventory->name,"SJIS","UTF-8");
			}
			
			//機種
			if( @$lineary->inventory->alias == "" ){
				$addlist = $addlist.",";
			}
			else{
				$addlist = $addlist.",".mb_convert_encoding($lineary->inventory->alias,"SJIS","UTF-8");
			}

			//実IP
			if( @$lineary->inventory->os == "" ){
				$addlist = $addlist.",";
			}
			else{
				$addlist = $addlist.",".mb_convert_encoding($lineary->inventory->os,"SJIS","UTF-8");
			}

			//予備1
			if( @$lineary->inventory->os_full == "" ){
				$addlist = $addlist.",";
			}
			else{
				$addlist = $addlist.",".mb_convert_encoding($lineary->inventory->os_full,"SJIS","UTF-8");
			}
			
			//予備2
			if( @$lineary->inventory->os_short == "" ){
				$addlist = $addlist.",";
			}
			else{
				$addlist = $addlist.",".mb_convert_encoding($lineary->inventory->os_short,"SJIS","UTF-8");
			}
			//予備3
			if( @$lineary->inventory->serialno_a == "" ){
				$addlist = $addlist.",";
			}
			else{
				$addlist = $addlist.",".mb_convert_encoding($lineary->inventory->serialno_a,"SJIS","UTF-8");
			}
			//予備4
			if( @$lineary->inventory->serialno_b == "" ){
				$addlist = $addlist.",";
			}
			else{
				$addlist = $addlist.",".mb_convert_encoding($lineary->inventory->serialno_b,"SJIS","UTF-8");
			}
			//予備5
			if( @$lineary->inventory->tag == "" ){
				$addlist = $addlist.",";
			}
			else{
				$addlist = $addlist.",".mb_convert_encoding($lineary->inventory->tag,"SJIS","UTF-8");
			}

			$retMacro = array();
			$retMacro=getMacro($lineary->hostid,$auth);
			
			foreach ( $retMacro as $macro ){
				if ( $macro=="" ) {
					break;
				}
				// マクロ
				$addlist = $addlist.",".mb_convert_encoding($macro->macro,"SJIS","UTF-8");
				$addlist = $addlist.",".mb_convert_encoding($macro->value,"SJIS","UTF-8");
				
			}

			$i = $i + 1;
			
		    //データの出力
		    fputs($fp, $addlist."\n");
		}
	}
	
	//ファイルポインタを一番先頭に戻す
	rewind($fp);

	//ファイルポインタの今の位置からすべてを読み込み文字列に代入
	$csv = stream_get_contents($fp);
	
	fclose($fp);
//メモリに出力する場合(バッファの処理が必要)
//	session_cache_limiter('public');
//    header('Content-Description: File Transfer');
//	header("Content-Type: application/x-csv");
//    header("Content-Disposition: attachment; filename=".$outputFile);
//    header("Content-Lentgth:". filesize($outputFile));
//    header('Content-Transfer-Encoding: binary');
//    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
//    header('Pragma: public');
//    ob_clean();
//    flush();
// 	@readfile ($outputFile);
	
	
	
	header('Content-Disposition:attachment; filename='.$outputFile);
	header('Content-Type:application/octet-stream');
    header('Content-Length:'.strlen($csv));
    echo $csv;

}
?>