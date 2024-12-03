<?php 

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
	$minDay=date('d',strtotime('first day of '.$minYear.'-'.$minMonth));
	$UtimeMin = mktime(0,0,0,$minMonth,$minDay,$minYear);
	$minDate = date('Y/m/d',$UtimeMin);
	
	$maxYear=date('Y',strtotime($maxDate));
	$maxMonth=date('m',strtotime($maxDate));
	$maxDay=date('d',strtotime('last day of '.$maxYear.'-'.$maxMonth));
	$UtimeMax = mktime(0,0,0,$maxMonth,$maxDay,$maxYear);
	$maxDate = date('Y/m/d 23:59:59',$UtimeMax);


	//CSVファイルに出力
	$outputFile = mb_convert_encoding("アラーム統計(月毎のアラーム)".$minYear.$minMonth.$maxDay."～".$maxYear.$maxMonth.$maxDay ,"SJIS","UTF-8").".csv";

	//ダイレクトにWEB上出力
	$fp = fopen('php://temp', "w") or die("OPENエラー $outputFile");

	$Ccount = 0;
	$changeCount = 0;

	//ヘッダの生成
	$csvout = "";
	$csvout = $csvout. "【 月毎 】の異常検出数\n";
	$csvout = $csvout. "(単位：件)\n";
 	$csvout = $csvout. "期間： ".date('Y/m月',$UtimeMin)." ～ ".date('Y/m月',$UtimeMax)." \n";
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
		
		//$UtimeMin = date("Y/m/d", strtotime("+1 month"));
		$tmpYear=date('Y',strtotime($strDate));
		$tmpMonth=date('m',strtotime($strDate." +1 month"));
		$tmpDay=date('d',strtotime('first day of '.$tmpYear.'-'.$tmpMonth));
		$UtimeMin = mktime(0, 0, 0, $tmpMonth , $tmpDay, $tmpYear);

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
			$csvout = $csvout . ",ICMP,ICMP以外";
			
			//最後のときは改行を追加
			if(count($dateArray) == $ken){
				$csvout = $csvout.",合計(ICMP),合計(ICMP以外),合計" ;
				// 改行
				$csvout = $csvout . "\n";
			}
		}
		
		if ( $groupdata->groupid=="" ) {
			break;
		}
		
		$group 			= $groupdata->name;	
		$groupid 			= $groupdata->groupid;
		
//if($groupid != '9'){
//	continue;
//}

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
				// トリガー名にICMPが含まれていればカウント
				$triggername = $eventData->relatedObject->description;
				if(!preg_match("/ICMP/", $triggername)){
					//ICMPではないとき
					if($eventData->value == 1 ){
						$before_value = $eventData->value;

						// ホストグループ毎の障害件数
						$repairCount++;

						// 全体のカウント数
						//$totalAlarmCount++;
						
						//カスタマ毎に配列を作る
						$etimeUnix = $eventData->clock;
						foreach( $dateArray as $key => $val ){
							
							$KeytimePlusOne = mktime(0,0,0,date('m',strtotime(date('Y/m/d',$key).' +1 month')),
							date('d',$key),date('Y',$key));
							//次の月の初日より大きかったら抜ける{
							if ( $key > $etimeUnix ){
								break;
							}
									
							// 日付ごとに障害があるか判定
							if( $etimeUnix >= $key and $etimeUnix < $KeytimePlusOne ){
								$RedateArray[$key] = $RedateArray[$key]+1;;
							}
						}
					}
					
				}
				//障害件数
				//1が障害
				//0が復旧
				else{
					if($eventData->value == 1 ){
						$before_value = $eventData->value;
						// ホストグループ毎の障害件数
						$alarmCount++;

						// 全体のカウント数
						$totalAlarmCount++;
						
						//カスタマ毎に配列を作る
						$etimeUnix = $eventData->clock;
						foreach( $dateArray as $key => $val ){

							$KeytimePlusOne = mktime(0,0,0,date('m',strtotime(date('Y/m/d',$key).' +1 month')),
							date('d',$key),date('Y',$key));
							//echo "SS ".date('Y/m/d',$key)."  ==  ".date('Y/m/d',$KeytimePlusOne);
							//次の月の初日より大きかったら抜ける
							if ( $key > $etimeUnix ){
								break;
							}
							
		
							// 日付ごとに障害があるか判定
							if( $etimeUnix >= $key and $etimeUnix < $KeytimePlusOne ){
								$dateArray[$key] = $dateArray[$key]+1;
							}
						}
					}
				}
					//復旧件数
//				if($eventData->value == 0 ){

//					$before_value = $eventData->value;
					// ホストグループ毎の復旧件数
//					$repairCount++;
					//イベントの件数
//					$changeCount++;
					// 全体のカウント数
//					$totalAlarmCount++;

					//対象の日付の配列に格納
//					$etimeUnix = $eventData->clock;
//					foreach( $dateArray as $key => $val ){

						//to日付より大きかったら抜ける
//						if ( $key > $etimeUnix ){
//							break;
//						}

						// 日付ごとに障害があるか判定
//						if( $etimeUnix >= $key and $etimeUnix < $KeytimePlusOne  ){
							//復旧カウントアップ
//							$RedateArray[$key] = $RedateArray[$key]+1;;
//						}
//					}					
//				}
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

		//print "<br>";
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


	header('Content-Disposition:attachment; filename='.$outputFile);
	header('Content-Type:application/octet-stream');
    header('Content-Length:'.strlen($csv));
	
    echo $csv;

	


?> 
