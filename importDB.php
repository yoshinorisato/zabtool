<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<LINK href="css/menu.css" type=text/css rel=stylesheet >
<SCRIPT src="js/script.js" type="text/javascript"></SCRIPT>
<title>ファイルインポート</title>

</head>
<body>

<SCRIPT LANGUAGE="JavaScript">
<!--
WriteMenu(1)
//-->
</SCRIPT>
<div id="inbody">
<?php 
//fgetcsvの場合
$fileName = isset($_REQUEST["filename"])?$_REQUEST["filename"]:"";
$titleline = isset($_REQUEST["titleline"])?$_REQUEST["titleline"]:"";

//session_start();

$i=0;
$addflg = false;
$scount = 0;
$ecount = 0;

//変更フラグ
$chgflg = false;
$chg_scount = 0;
$chg_ecount = 0;

//削除
$delflg = false;
$del_scount = 0;
$del_ecount = 0;

$hostGary = array();
$hostG = "";
$file = "";

global $authKey,$errorMsg,$_LOG;


$file = fopen("files/".$fileName,"rt");
if(!$file ){

	die("<br><B>ファイルオープンエラー!!</B> <br>　ファイル名: "."${fileName}");
	error_log(date("Y/m/d g:i:s")."[ERROR importDB.php] ファイルオープンエラー\n",3,$_LOG);
	unlink("files/".$fileName);
}

while(!feof($file)){

	$csvb= fgets($file);

	if( $csvb == FALSE ){
		break;
	}

	//1行目をインサートしない
	if( trim($titleline) == "1" && $i == 0 ){
		$i += 1;
		continue;
	}

	// 文字コードの変換
	$csv = mb_convert_encoding($csvb,"UTF-8", "SJIS,EUC-JP");

	//ダブルクォーテーションの削除
	$word = str_replace("\"","", $csv);

	//シングルクォーテーションの削除
	$word = str_replace("'","", $word);

   	//csvファイルを配列に格納します
	$strArray= explode(",", $word);

	//空行は飛ばし
	if ( count($strArray) <= 1 ){continue;}

	//passもしくは"設定種別"は無視
	if ( $strArray[0] == "pass" or $strArray[0] == "設定種別" ) {continue;}
	else if ( $strArray[0] == "add"){
		//追加
	 	$jstr=creHost($strArray);
		//フラグを立てる(結果表示用)
	 	$addflg = true;
	}else if ( $strArray[0] == "chg" or $strArray[0] == "mod" ){
		//変更
		$jstr=modHost($strArray);
		$chgflg = true;
	}else if ( $strArray[0] == "del" ){
		//削除
		$jstr=deleteHost($strArray);
		$delflg = true;
	}else if ( $strArray[0] == "sub" ){
		//追加インターフェース
		$jstr=modHost($strArray);
	}
}

  	echo "<br><B>インポート完了</B></br>";
	// 登録成功数
	if ($addflg) {
	  	echo "&nbsp;&nbsp;登録<br>";
	  	echo "&nbsp;&nbsp;&nbsp;成功：".$scount . "件<br>";
	  	echo "&nbsp;&nbsp;&nbsp;失敗：".$ecount . "件<br>";
	}
	// 変更成功数
	if ($chgflg) {
	  	echo "&nbsp;&nbsp;変更<br>";
	  	echo "&nbsp;&nbsp;&nbsp;&nbsp;成功：". $chg_scount . "件<br>";
	  	echo "&nbsp;&nbsp;&nbsp;&nbsp;失敗：". $chg_ecount . "件<br>";
	}
	// 削除成功数
	if ($delflg) {
	  	echo "&nbsp;&nbsp;削除<br>";
	  	echo "&nbsp;&nbsp;&nbsp;&nbsp;成功：". $del_scount . "件<br>";
	  	echo "&nbsp;&nbsp;&nbsp;&nbsp;失敗：". $del_ecount . "件<br>";
	}
	//全体	
	$sc = $scount + $chg_scount + $del_scount ;
	$ec = $ecount + $chg_ecount + $del_ecount ;
	echo "&nbsp;&nbsp;<B>全体</B><br>";
	echo "&nbsp;&nbsp;&nbsp;&nbsp;成功：".$sc . "件<br>";
	echo "&nbsp;&nbsp;&nbsp;&nbsp;失敗：".$ec . "件<br>";

  	fclose($file);
  	unlink("files/".$fileName);

//ホストの変更(インターフェースの追加)
function modHost($csv_array){

	include "config.php";
	require_once "getHostid.php";
	require_once "getProxyHostid.php";
	$interfaceArray1 = array();
	$interfaceArray2 = array();
	$paramArray = array();

	//認証
	require_once ('auth_zabbixAPI.php');
	auth_zabbixAPI();
	global $authKey,$errorMsg;
	global $chg_scount, $chg_ecount;

	//ホスト名の取得
	$csvData = trim($csv_array[1]);
	if(empty($csvData )){
		echo "<BR><font color='red' >エラー </font><BR>";
		echo "CSVファイルよりホスト名が取得できませんでした<BR><BR/>";
		error_log(date("Y/m/d g:i:s")."[ERROR importDB.php modhost] CSVファイルよりホスト名が取得できませんでした\n",3,$_LOG);
		return "";
	}

	//表示用にホスト名の取得
	$svrName=$csvData;
	
	//ホストIDを取得
	$hostid=getHostid($csvData,$authKey);

	$PARAMSary= array();
	$INVENTary= array();
	$InventF=false;
	$koumoku="";
	//入力してある項目を取得
	for( $i = 2; $i < count($csv_array)-2; $i++){
		
		print $i;
		print count($csv_array);
		
		$koumoku = trim((string)$csv_array[$i]);
		
		print $koumoku;
		switch ($i){
		case 2: // ホスト名(日本語)				

			$PARAMSary['name']=$koumoku;
			break;
		case 3: //グループ名
		case 4: //IPアドレス
		case 5: // テンプレート
			break;
		case 6: //プロキシ名
	    	$proxyid = 0;
			//空の時はPROXYなし
			if(trim($koumoku) ==""){

				//空で登録する場合はnullを登録
				$PARAMSary['proxy_hostid'] = null;
			}
			else if(trim($koumoku) !=""){

			   	// zabbix APIを使用しデータを取得
				$proxyid =get_proxy_hostid($koumoku,$authKey);

    			// プロキシのホストIDが取得できないときはプロキシなし
    			if(! (isset($proxyid))){
					echo "<br><font color='red' >エラー ：" . $svrName . "</font><BR>\n";
					echo "登録されていないPROXYです。プロキシなしで登録します。  MSG:${errorMsg} ID: ${csv_array[6]}";
					error_log(date("Y/m/d g:i:s")."[ERROR importDB.php modhost] 登録されていないPROXYです。${koumoku} \n",3,$_LOG);
					break;
				}

				$PARAMSary['proxy_hostid'] = $proxyid;
			}
			break;

		case 7: //有効/無効(デフォルト無効:1)
			$stData = 0;
			$stData = $koumoku;
			if( $koumoku == "" ){
				echo "<br><font color='red' >エラー ：" . $svrName . "</font><BR>\n";
				echo "有効/無効ステータスが取得できませんでした。無効で登録します。";
				error_log(date("Y/m/d g:i:s")."[ERROR importDB.php modhost] 有効/無効ステータスが取得できませんでした。無効で登録します。${koumoku} \n",3,$_LOG);
				$stData = 1;
			}
			$stData = strtolower($stData );
			if($stData == 'enable' || 
				$stData == 'true' || 
				$stData == '0' || 
				$stData == '有効' ){
				$stData=0;
			}
			else if($stData == 'disable' || 
					$stData == 'false' || 
					$stData == '1' || 
					$stData == '無効' ){
				$stData=1;
			}

			$PARAMSary['status']=$stData;
			break;

		case 8: // インターフェイス名
			$InventF=true;
			$INVENTary['type']=$koumoku;
			break;

		case 9: // 拠点名type_full
			$InventF=true;
			$INVENTary['type_full']=$koumoku;
			break;

		case 10: // カスタマ名name
			$InventF=true;
			$INVENTary['name']=$koumoku;
			break;
		case 11: // 機種名
			$InventF=true;
			$INVENTary['alias']=$koumoku;
			break;
		case 12: // 実IP
			$InventF=true;
			$INVENTary['os']=$koumoku;
			break;
		case 13: // 予備1
			$InventF=true;
			$INVENTary['os_full']=$koumoku;
			break;
		case 14: // 予備2
			$InventF=true;
			$INVENTary['os_short']=$koumoku;
			break;
		case 15: // 予備3
			$InventF=true;
			$INVENTary['serialno_a']=$koumoku;
			break;
		case 16: // 予備4
			$InventF=true;
			$INVENTary['serialno_b']=$koumoku;
			break;
		case 17: // 予備5
			$InventF=true;
			$INVENTary['tag']=$koumoku;
			break;

		case 18: // マクロ1
		case 19: // マクロ1値
		case 20: // マクロ2
		case 21: // マクロ2値
		case 22: // マクロ3
		case 23: // マクロ3値
		case 24: // マクロ4
		case 25: // マクロ4値
		case 26: // マクロ5
		case 27: // マクロ5値
			break;
		}

		// 設定対象のデータがない場合
		if ( !(isset($PARAMSary))){
			return "";
		}
	}
	
	//配列に代入
	$PARAMSary['hostid'] = $hostid;

	// 更新JSONを作成
	$updateArray = array(
		'jsonrpc'=>"2.0",
		'method'=>"host.update",
		'auth'=>$authKey,
		'id'=>2
	);

	$tmpArray = array();
	$tmpArray['params'] = $PARAMSary;
    $updateArray += $tmpArray;

	// インベントリ更新JSONを作成
	$InventoryError = false;
	$massupdateArray = array(
		'jsonrpc'=>"2.0",
		'method'=>"host.massUpdate",
		'auth'=>$authKey,
		'id'=>3
	);

	$tmpArray = array();
	$tmpArray['inventory'] = $INVENTary;
	$hostsarray['hostid']= $hostid;
    $tmpArray['hosts'] = $hostsarray;
    $massupdateArray['params'] = $tmpArray;

	//アップデートの実行
	require_once('imputHost.php');
	$ret = imputHost ($updateArray,$authKey);

	//エラーのチェック
	if($ret < 0  ){
		echo "<BR><font color='red' >変更エラー!! </font>   ホスト名:".$svrName."  ";
		
		echo $errorMsg."<BR>";
		$chg_ecount += 1;
	}
	else{
		require_once('massUpdate.php');
		$retInvent = massUpdate ($massupdateArray,$authKey);

		//エラーのチェック
		if($retInvent < 0){
			echo "<BR><font color='red' >変更エラー!! </font>   ホスト名:".$svrName."  ";
			echo "インベントリ情報が更新できませんでした。 ".$errorMsg."<BR>";
			error_log(date("Y/m/d g:i:s")."[ERROR importDB.php modhost] インベントリ情報が更新できませんでした。 ホスト名:".$svrName." \n",3,$_LOG);
			$chg_ecount += 1;
			$InventoryError=true;
		}

		echo "<BR><B>変更完了</B> ：" . $svrName .""."　　ホストID =".$ret."<BR>";
		$chg_scount += 1;
	}
}

//ホストの追加  
function creHost($csv_array){
	include "config.php";
	require_once('getHostGroupid.php');	
	require_once('getTemplateid.php');
	require_once('getProxyHostid.php');

	//認証
	require_once ('auth_zabbixAPI.php');
	auth_zabbixAPI();
	global $authKey,$errorMsg;
	global $scount, $ecount;
	
	// テンプレートエラーが出力された場合エラーにする
	$templError = false;
	
	//ホスト名
	if(! (isset($csv_array[1])) Or trim($csv_array[1]) == "" ){
		echo "<BR><font color='red' >登録エラー </font><BR>\n";
		echo "CSVファイルよりホスト名が取得できませんでした<br><br/>";
		error_log(date("Y/m/d g:i:s")."[ERROR importDB.php crehost] CSVファイルよりホスト名が取得できませんでした ホスト名:".$csv_array[1]." \n",3,$_LOG);
		return "";
	}

	//表示用にホスト名の取得
	$svrName=trim($csv_array[1]);

	//ホスト名(表示用)
	if(! (isset($csv_array[2])) Or trim($csv_array[2]) == "" ){
		echo "<BR><font color='red' >登録エラー ：" . $svrName . "</font><BR>\n";
		echo "CSVファイルよりホスト名(表示用)が取得できませんでした<br><br/>";
		error_log(date("Y/m/d g:i:s")."[ERROR importDB.php crehost] CSVファイルよりホスト名(表示用)が取得できませんでした ホスト名:".$csv_array[2]." \n",3,$_LOG);
		return "";
	}

	//ホストグループ名
	if(! (isset($csv_array[3])) Or trim($csv_array[3] == "") ){
		echo "<BR><font color='red' >登録エラー ：" . $svrName . "</font><BR>\n";
		echo "CSVファイルよりホストグループ名が取得できませんでした<br><br/>";
		error_log(date("Y/m/d g:i:s")."[ERROR importDB.php crehost] CSVファイルよりホストグループ名が取得できませんでした ホスト名:".$csv_array[3]." \n",3,$_LOG);
		return "";
	}

	//配列を取得
	$hostG="";
	$groupID="";
	$groupCnt=0;
	$hostGArray = array();
	$hostGroupArray = array();
	$hostGAry = explode(";", $csv_array[3]);
	foreach ( $hostGAry as  $hostG ){
		if ( $hostG == "" ) {
			break;
		}

		//グループ名からグループIDを取得
		$groupID= getHostGroupid(trim($hostG),$authKey);

		//エラー出力
		if( $groupID == -1 ) {
			echo "<BR>".$svrName . " " . $hostG . " ";
			echo "グループ名( ".$hostG." )が取得できませんでした。".$errorMsg;
			error_log(date("Y/m/d g:i:s")."[ERROR importDB.php crehost] グループ名( ".$hostG." )が取得できませんでした。".$csv_array[3]." \n",3,$_LOG);
			break;
		}
		else{
			$hostGroupArray['groupid']= trim($groupID);
			array_push($hostGArray,$hostGroupArray);
		}
	}
	//IPアドレス
	if(! (isset($csv_array[4])) Or trim($csv_array[4]) == ""){
		echo "<BR><font color='red' >登録エラー ：" . $svrName . "</font><BR>\n";
		echo "CSVファイルよりIPアドレスが取得できませんでした<br><br/>";
		error_log(date("Y/m/d g:i:s")."[ERROR importDB.php crehost] CSVファイルよりIPアドレスが取得できませんでした".$csv_array[4]." \n",3,$_LOG);
		return "";
	}


	//テンプレート
	if(! (isset($csv_array[5])) Or trim($csv_array[5]) == ""){
		echo "<BR><font color='red' >登録エラー ：" . $svrName . "</font><BR>\n";
		echo "CSVファイルよりリンクしているテンプレートが取得できませんでした<br><br/>";
		error_log(date("Y/m/d g:i:s")."[ERROR importDB.php crehost] CSVファイルよりリンクしているテンプレートが取得できませんでした".$csv_array[5]." \n",3,$_LOG);
		return "";
	}

	$temp="";
	$tempId="";
    $tempIdArray = array();
	$tempAry = array();
	$templatesArray = explode(";", $csv_array[5]);

	foreach ( $templatesArray as  $temp ){
		if(empty($temp))
			break;
		
		//テンプレートからテンプレートIDを取得
		$tempId=getTemplateid(trim($temp),$authKey);

		//エラー出力
		if( $tempId == -1 ) {
			echo "<BR><font color='red' >登録エラー ：" . $svrName . "</font><BR>\n";
			echo "<BR>".$svrName . " ";
			echo "テンプレートID( ".${temp}." )が取得できませんでした。".$errorMsg."<br><br/>";
			error_log(date("Y/m/d g:i:s")."[ERROR importDB.php crehost] テンプレートID( ".${temp}." )が取得できませんでした。".$errorMsg." " .$csv_array[5]." \n",3,$_LOG);
			$ecount += 1;
			return;
			
		}else{
			$tempAry['templateid']= trim($tempId);
			array_push($tempIdArray,$tempAry);
		}
	}

	//プロキシ
	$proxyid = 0;
	$csvData = $csv_array[6];
	if(empty($csvData)){
       	echo "<BR><font color='red' >登録エラー ：" . $svrName . "</font><BR>\n";
       	echo "CSVファイルよりZabbixProxy名が取得できませんでした  ZabbixProxyなしで登録します。 ID: ${csv_array[11]}<br><br/>";
      		error_log(date("Y/m/d g:i:s")."[ERROR importDB.php crehost] CSVファイルよりZabbixProxy名が取得できませんでした   ID: ${csv_array[11]} \n",3,$_LOG);
    }
	else{
		// zabbix APIを使用しデータを取得
		$proxyid =get_proxy_hostid(trim($csv_array[6]),$authKey);

	}

	// プロキシのホストIDが取得できないときはプロキシなし
	if(! (isset($proxyid)) Or trim($proxyid) == ""){
  		echo "<BR><font color='red' >登録エラー ：" . $svrName . "</font><BR>\n";
   		echo "DBよりProxyIDが取得できませんでした。プロキシなしで登録します。  MSG:${errorMsg} ID: ${csv_array[6]}<br><br/>";
   		error_log(date("Y/m/d g:i:s")."[ERROR importDB.php crehost]". $svrName ." DBよりProxyIDが取得できませんでした。プロキシなしで登録します。  MSG:${errorMsg} ID: ${csv_array[6]} \n",3,$_LOG);
	}

	//有効/無効
	if(! (isset($csv_array[7])) Or trim($csv_array[7]) == ""){
        echo "<BR><font color='red' >登録エラー ：" . $svrName . "</font><BR>\n";
        echo "有効/無効ステータスが取得できませんでした。無効で登録します。<br><br/>";
        error_log(date("Y/m/d g:i:s")."[ERROR importDB.php crehost]". $svrName ." 有効/無効ステータスが取得できませんでした。無効で登録します。  MSG:${errorMsg} ID: ${csv_array[7]} \n",3,$_LOG);
		$status = 1;
    }
	
	switch (trim((string)$csv_array[7])){
		// 0, Enable
		case '0':
		case 'Enable':
		case 'enable':
		case 'ENABLE':
		case 'true':
		case 'TRUE':
		case '有効':

			$status = 0;
			break;
		// 1, disable
		case '1':
		case 'DISABLE':
		case 'Disable':
		case 'disable':
		case 'false':
		case 'FALSE':
		case '無効':
			$status = 1;
			break;
	}

	$csv_array[7] = (string)$status;



	//マクロ1
	$macroArray = array();

	if( (isset($csv_array[18])) and trim($csv_array[18]) != "" ){
		//マクロ1の値
		if(!(isset($csv_array[19])) and trim($csv_array[19]) == "" ){
			echo "<BR><font color='red' >登録エラー ：" . $svrName . "</font><BR>\n";
			echo "CSVファイルよりマクロ1が取得できませんでした<br><br/>";
		}
		else{
			$tmparray = array(
				// 'macro'=>'{$'.trim($csv_array[18]).'}',
				'macro'=>trim($csv_array[18]),
				'value'=>trim($csv_array[19])
			);
			array_push($macroArray, $tmparray);
		}
	}

	//マクロ2
	if( (isset($csv_array[20])) and trim($csv_array[20]) != "" ){
		//マクロ2の値
		if(!(isset($csv_array[21])) and trim($csv_array[21]) == "" ){
			echo "<BR><font color='red' >登録エラー ：" . $svrName . "</font><BR>\n";
			echo "CSVファイルよりマクロ2が取得できませんでした<br><br/>";
		}
		else{
			$tmparray = array(
				'macro'=>trim($csv_array[20]),
				'value'=>trim($csv_array[21])
			);
			array_push($macroArray, $tmparray);
		}
	}

	//マクロ3
	if( (isset($csv_array[22])) and trim($csv_array[22]) != ""  ){

		//マクロ3の値
		if(!(isset($csv_array[23])) and trim($csv_array[23]) == "" ){
			echo "<BR><font color='red' >登録エラー ：" . $svrName . "</font><BR>\n";
			echo "CSVファイルよりマクロ3が取得できませんでした<br><br/>";
		}
		else{
			$tmparray = array(
				'macro'=>trim($csv_array[22]),
				'value'=>trim($csv_array[23])
			);
			array_push($macroArray, $tmparray);
		}
	}

	//マクロ4
	if( (isset($csv_array[24]))  && trim($csv_array[24]) != ""  ){

		//マクロ4の値
		if(!(isset($csv_array[25])) and trim($csv_array[25]) == "" ){
			echo "<BR><font color='red' >登録エラー ：" . $svrName . "</font><BR>\n";
			echo "CSVファイルよりマクロ4が取得できませんでした<br><br/>";
		}
		else{
			$tmparray = array(
				'macro'=>trim($csv_array[24]),
				'value'=>trim($csv_array[25])
			);
			array_push($macroArray, $tmparray);
		}
	}

	//マクロ5
	if( (isset($csv_array[26]))  && trim($csv_array[26]) != ""  ){

		//マクロ5の値
		if(!(isset($csv_array[27])) and trim($csv_array[27]) == "" ){

			echo "<BR><font color='red' >登録エラー ：" . $svrName . "</font><BR>\n";
			echo "CSVファイルよりマクロ5が取得できませんでした<br><br/>";
		}
		else{
			$tmparray = array(
				'macro'=>trim($csv_array[26]),
				'value'=>trim($csv_array[27])
			);
			array_push($macroArray, $tmparray);
		}
	}

	//マクロ5以降
	if( count($csv_array) > 27 ){

		$tempAry = array();
		
		// ループにより取得
		for( $i=28; $i< count($csv_array); $i++){
		
			if( (! (isset($csv_array[$i]))) || trim($csv_array[$i]) == ""  ){

				break;
			}

			if( (isset($csv_array[$i]))  && trim($csv_array[$i]) != ""  ){
				
				//マクロの値の取得
				if( (! (isset($csv_array[$i+1]))) || trim($csv_array[$i + 1]) == ""  ){

					echo "<BR><font color='red' >登録警告!! ：" . $svrName . "</font><BR>\n";
					$col= $i -27;
					echo "CSVファイルよりマクロ名". $csv_array[$i] ."の値が取得できませんでした<br><br/>";
					
				}
				else{
					$tmparray = array(
						'macro'=>trim($csv_array[$i]),
						'value'=>trim($csv_array[$i+1])
					);
					array_push($macroArray, $tmparray);
				}
				$i = $i+1;
			}
		}
	}
	
	$ret="";

	//ICMP用とSNMP用
	$interfaceArray1 = array(
		array('type'=> 1,  							// インターフェイスタイプAgent固定1
			'ip'=> trim($csv_array[4]),				// IPアドレス
			'dns'=> "",								// DNS名
        	'useip'=> 1,							// 接続方法 IP固定
        	'main'=> 1,								// Main/Slave PRIMARY固定
        	'port'=> 10050 ),							// ポート番号 0固定
        array('type'=> 2,							// インターフェイスタイプSNMP固定2
			'ip'=> trim($csv_array[4]),				// IPアドレス
			'dns'=> "",					           	// DNS名の登録
        	'useip'=> 1,				          	// 接続方法 IP固定
        	'main'=> 1, 					        // Main/Slave PRIMARY固定
        	'port'=>161)                            // ポート番号 0固定
	);

	//Zabbixプロキシが存在しなかったら項目自体をなくす。
	if($proxyid != 0 ) {
	
		//プロキシあるとき	
		$paramArray = array(
			'host'=> trim($csv_array[1]),
			'name'=> trim($csv_array[2]),
			'proxy_hostid'=> trim($proxyid),
			'status'=> trim($csv_array[7])
		);
	}
	else{
		//プロキシ無いとき
		$paramArray = array(
			'host'=> trim($csv_array[1]),
			'name'=> trim($csv_array[2]),
			'status'=> trim($csv_array[7])
		);
	}

	$inventArray = array(
		'type'=> trim($csv_array[8]),
		'type_full'=> trim($csv_array[9]),	
		'name'=> trim($csv_array[10]),
		'alias'=> trim($csv_array[11]),
		'os'=> trim($csv_array[12]),
		'os_full'=> trim($csv_array[13]),
		'os_short'=> trim($csv_array[14]),
		'serialno_a'=> trim($csv_array[15]),
		'serialno_b'=> trim($csv_array[16]),
		'tag'=> trim($csv_array[17])
	);

	//グループＩＤを設定
	if( !(is_array($hostGArray) && empty($hostGArray)) ){
		$tmpArray = array();
		$tmpArray['groups'] = $hostGArray;
	    $paramArray += $tmpArray;
	}
	
	$tmpArray = array();

	//インターフェース情報を設定
	$tmpArray['interfaces'] = $interfaceArray1;
	//$tmpArray['interfaces'] = $interfaceArray2;
	//print_r($tmpArray);
   	$paramArray += $tmpArray;

	if( is_array($tempIdArray) ){
		$tmpArray = array();
		//テンプレート情報を設定
		$tmpArray['templates'] = $tempIdArray;
	    $paramArray += $tmpArray;
	}

	$tmpArray = array();

	//インベントリ情報の設定
	$tmpArray['inventory'] = $inventArray;
    $paramArray += $tmpArray;

	//マクロ情報の設定
	$tmpArray['macros'] = $macroArray;
    $paramArray += $tmpArray;

	$jsonArray = array(
		'jsonrpc'=>"2.0",
		'method'=>"host.create",
		'auth'=>$authKey,
		'id'=>1
	);

	$tmpArray = array();
	$tmpArray['params'] = $paramArray;

   	$jsonArray += $tmpArray;

   	//登録の実行
	require_once('imputHost.php');
	$ret = imputHost ($jsonArray,$authKey);

	//エラーのチェック
	if($ret < 0){
		echo "<BR><font color='red' >登録エラー!! </font>   ホスト名:".$svrName."  ";
		echo $errorMsg."<BR>";
		$ecount += 1;
	}
	else{
		//print_r($paramArray);
		echo "<BR><B>登録完了</B> ：" . $svrName ."　　ホストID =".$ret."<BR>";
		$scount += 1;
	}
}

//削除
function deleteHost($csv_array)
{
	include "config.php";
	require_once ('getHostid.php') ;

	$paramArray = array();

	//認証
	require_once ('auth_zabbixAPI.php');
	auth_zabbixAPI();
	global $authKey,$errorMsg;
	global $del_scount, $del_ecount;
	//ホスト名の取得
	if(! (isset($csv_array[1])) ){
		echo "<BR><font color='red' >削除エラー </font><BR>\n";
		echo "CSVファイルからホスト名が取得できませんでした delHost<BR>";
		error_log(date("Y/m/d g:i:s")."[ERROR importDB.php deleteHost] 削除CSVファイルからホスト名が取得できませんでした\n",3,$_LOG);
		$del_ecount += 1;
		return "";
	}

	//表示用にホスト名の取得
	$svrName=$csv_array[1];
	
	//ホストIDを取得
	$hostid=getHostid($csv_array[1],$authKey);
	if( !(isset($hostid)) ){
		echo "<BR><font color='red' >削除エラー!! </font>   ホスト名:".$svrName;
		echo "データベースに存在しません。ホスト名：${svrName} (delHost) <BR>";
		error_log(date("Y/m/d g:i:s")."[ERROR importDB.php deleteHost] データベースに存在しません。ホスト名：${svrName} \n",3,$_LOG);
		$del_ecount += 1;
		return -1;
	}

	require_once('delHost.php');
	$ret = delHost($authKey,$hostid);
	if( $ret < 0 ){
		echo "<BR>削除 ：" .$svrName ."エラー   $errorMsg<BR>";
		$del_ecount += 1;
	}
	else{
		echo "<BR><B>削除完了</B> ：".$svrName."<BR>";
		$del_scount += 1;
	}
}

?> 
<br>
<input value="再度インポート" onclick="location.href='importFileSel.php'" type="button"></td>
</div>

</body>
</html>
