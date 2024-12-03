<?php 

//<html>
//<head>
//<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
//<!--<script type="text/javascript" src="../js/func.js"></script>-->
//<title>イベントレポート</title>
//</head>
//<body>



$comboIdx = isset($_REQUEST["comboIdx"])?$_REQUEST["comboIdx"]:"";
$minDate = isset($_REQUEST["jquery-ui-datepicker-from"])?$_REQUEST["jquery-ui-datepicker-from"]:"";
$maxDate = isset($_REQUEST["jquery-ui-datepicker-to"])?$_REQUEST["jquery-ui-datepicker-to"]:"";
//session_start();

$i=0;
$scount = 0;
$ecount = 0;
$hostGary = array();
$hostG = "";
$file = "";
global $authKey,$errorMsg;

	//$i += 1;

	include "../config.php";
	$paramArray = array();

	//認証
	require_once ('../auth_zabbixAPI.php');
	require_once ('getEventList.php');

	auth_zabbixAPI();
	global $authKey,$errorMsg;
	global $scount, $ecount;	
	
	$retArray = array();
	
	//全グループデータを取得
	require_once "../getAllGroup.php";
	$retArray=getAllGroup($authKey);
	$totalAlarmCount = 0;

	//UTIMEの取得
	$minYear=date('Y',strtotime($minDate));
	$minMonth=date('m',strtotime($minDate));
	$minDay=date('d',strtotime($minDate));
	$UtimeMin = mktime(0,0,0,$minMonth,$minDay,$minYear);
	
	$maxYear=date('Y',strtotime($maxDate));
	$maxMonth=date('m',strtotime($maxDate));
	$maxDay=date('d',strtotime($maxDate));	
	$UtimeMax = mktime(0,0,0,$maxMonth,$maxDay,$maxYear);


	//CSVファイルに出力
	$outputFile = mb_convert_encoding("アラーム統計(MJランプ状態)".$minYear.$minMonth.$minDay."～".$maxYear.$maxMonth.$maxDay ,"SJIS","UTF-8").".csv";

	//ダイレクトにWEB上出力
	$fp = fopen('php://temp', "w") or die("OPENエラー $outputFile");

	$Ccount = 0;
	$changeCount = 0;

	//ヘッダの生成
	$csvout = "";
	$csvout = $csvout. "【 MJランプ状態 】の異常検出数\n";
	$csvout = $csvout. "(単位：件)\n";
 	$csvout = $csvout. "期間：${minDate} ～ ${maxDate} \n";
	$csvout = $csvout. "ホストグループ名";
	//一日ずつ足す
	$i = 0;
	//日付毎発生の配列
	$dateArray = array();
	//日付毎復旧の配列
	$RedateArray = array();

	while(1){

		//to日付より大きかったら抜ける
		if ($UtimeMin > $UtimeMax ){
			break;
		}

		$strDate = date('Y/m/d',$UtimeMin);
		
		$csvout = $csvout.",".$strDate.",";
		
		//判定用に配列を取得
		$dateArray[$UtimeMin] = 0;
		$RedateArray[$UtimeMin] = 0;

		$UtimeMin=$UtimeMin+86400;
		$i++;
	}

	// 改行
	$csvout = $csvout . "\n";

	//グループ件数分ループ
	foreach( $retArray->result as $groupdata){

		//配列初期化
		$ken = 0;
		foreach( $dateArray as $key => $val ){
			$ken++;
			
			$dateArray[$key]= 0;
			$RedateArray[$key] = 0;
			
			//発生復旧のタイトル
			$csvout = $csvout . ",発生,復旧";
			
			//最後のときは改行を追加
			if(count($dateArray) == $ken){
				$csvout = $csvout.",合計(発生),合計(復旧),合計" ;
				// 改行
				$csvout = $csvout . "\n";
			}
		}
		
		if ( $groupdata->groupid=="" ) {
			break;
		}
		
		$group 			= $groupdata->name;	
		$groupid 			= $groupdata->groupid;
		
		//１行目はヘッダを格納
		if ( $Ccount == 0){
			$csvout = $csvout .$group;
		}
		else{
			//２行目以降はグループ名のみ
			$csvout = $group;
		}
		
		//グループ毎にイベント検索
		$retList = getEventList( $minDate,$maxDate,$groupid, $authKey);
		$alarmCount=0;
		$repairCount=0;
		$triggername="";
		$before_value=0;
		foreach( $retList->result as $eventData){	
			
			//トリガー名を取得
			if(isset($eventData->relatedObject->description)){
				// トリガー名にMJランプ状態が含まれていればカウント
				$triggername = $eventData->relatedObject->description;
				if(!preg_match("/MJランプ状態/u", $triggername)){
					//MJランプ状態ではないとき
					continue;
				}

			
				//障害件数
				//1が障害
				//0が復旧
				//if($eventData->value == 1 && $before_value == 0){
				if($eventData->value == 1 ){
					$before_value = $eventData->value;
					// ホストグループ毎の障害件数
					$alarmCount++;

					// 全体のカウント数
					$totalAlarmCount++;
					
					//カスタマ毎に配列を作る
					$etimeUnix = $eventData->clock;
					foreach( $dateArray as $key => $val ){

						//to日付より大きかったら抜ける
						if ( $key > $etimeUnix ){
							break;
						}

						// 日付ごとに障害があるか判定
						if( $etimeUnix >= $key and $etimeUnix < $key+86400 ){
							$dateArray[$key] = $dateArray[$key]+1;
						}
					}
				}
					//復旧件数
				//if($eventData->value == 0 && $before_value == 1){
				if($eventData->value == 0 ){

					$before_value = $eventData->value;
					// ホストグループ毎の復旧件数
					$repairCount++;
					//イベントの件数
					$changeCount++;
					// 全体のカウント数
					$totalAlarmCount++;

					//対象の日付の配列に格納
					$etimeUnix = $eventData->clock;
					foreach( $dateArray as $key => $val ){

						//to日付より大きかったら抜ける
						if ( $key > $etimeUnix ){
							break;
						}

						// 日付ごとに障害があるか判定
						if( $etimeUnix >= $key and $etimeUnix < $key+86400 ){
							//復旧カウントアップ
							$RedateArray[$key] = $RedateArray[$key]+1;;
						}
					}					
				}
			}
		}
		//結果出力
		foreach( $dateArray as $key => $val ){
			$csvout = $csvout .",".$val;
			$csvout = $csvout .",".$RedateArray[$key];
			
		}
		$group_alerm_count = ($alarmCount) + ($repairCount);
		$csvout = $csvout .",".($alarmCount).",".($repairCount);
		$csvout = $csvout .",".$group_alerm_count;
		$group_alerm_count = 0;
//		print $csvout;
//		print "<br>";
		$Ccount++;
		
		$csvout = mb_convert_encoding($csvout,"SJIS","UTF-8");
		    
	    //データの出力
	    fputs($fp, $csvout."\n");
 	}
// 	fputs($fp, mb_convert_encoding( "障害件数 : ".$totalAlarmCount."件"."\n","SJIS","UTF-8"));
//  	fputs($fp, mb_convert_encoding("復旧件数 : ".$changeCount."件"."\n","SJIS","UTF-8"));

	
	//ファイルポインタを一番先頭に戻す
	rewind($fp);

	//ファイルポインタの今の位置からすべてを読み込み文字列に代入
	$csv = stream_get_contents($fp);

 	fclose($fp);
 	
	session_cache_limiter('public');
//    header('Content-Description: File Transfer');
//	header('Content-Type: application/x-csv');
//    header('Content-Disposition: attachment; filename='.$outputFile);
//    header('Content-Transfer-Encoding: binary');
//    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
//    header('Pragma: public');
//    flush();
// 	@readfile ($outputFile);

	header('Content-Disposition:attachment; filename='.$outputFile);
	header('Content-Type:application/octet-stream');
    header('Content-Length:'.strlen($csv));
    echo $csv;



?> 
