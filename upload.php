<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>	CSVインポート確認</title>
<LINK href="css/menu.css" type=text/css rel=stylesheet>
<SCRIPT src="js/script.js" type=text/javascript></SCRIPT>
</head>
<body>

<SCRIPT LANGUAGE="JavaScript">

<!--
WriteMenu(1)
//-->
</SCRIPT>
<div>
<p>
<?php


$titleline = "";
if($_POST["titleline"] == "1"){
	$titleline = " (タイトル行あり) ";
}

if (is_uploaded_file($_FILES["upfile"]["tmp_name"])) {

	//アップするファイル
	$upfilename = "files/".$_FILES["upfile"]["name"];

	//ファイル名をS-JISにエンコード
	//$upfilename = mb_convert_encoding($upfilename,"SJIS","AUTO");

	//ファイルをアップロードする
	if (move_uploaded_file($_FILES["upfile"]["tmp_name"], $upfilename )) {
		chmod($upfilename, 0600);

		print "<TABLE border=\"1\"  >";
		print "
		<TR bgcolor=\"skyblue\">

		<TH nowrap rowspan=\"2\">0.種別</TH>
		<TH nowrap rowspan=\"2\">1.ホストID</TH>
		<TH nowrap colspan=\"2\" >2.ホスト名(表示名)</TH>
		<TH nowrap rowspan=\"2\" >3.グループ名</TH>
		<TH nowrap rowspan=\"2\" >4.IPアドレス AGENT/SNMP</TH>
		<TH nowrap rowspan=\"2\" >5.テンプレート</TH>
		<TH nowrap colspan=\"2\" >6.プロキシ名</TH>
		<TH nowrap colspan=\"2\" >7.有効/無効</TH>
		<TH nowrap colspan=\"2\" >8.インターフェイス</TH>
		<TH nowrap colspan=\"2\" >9.拠点名</TH>
		<TH nowrap colspan=\"2\" >10.カスタマ名</TH>
		<TH nowrap colspan=\"2\" >11.機種名</TH>
		<TH nowrap colspan=\"2\" >12.実IP</TH>
		<TH nowrap colspan=\"2\" >13.予備1</TH>
		<TH nowrap colspan=\"2\" >14.予備2</TH>
		<TH nowrap colspan=\"2\" >15.予備3</TH>
		<TH nowrap colspan=\"2\" >16.予備4</TH>
		<TH nowrap colspan=\"2\" >17.予備5</TH>
		<TH nowrap rowspan=\"2\" >18.マクロ１</TH>
		<TH nowrap rowspan=\"2\" >19.マクロ１の値</TH>
		<TH nowrap rowspan=\"2\" >20.マクロ２</TH>
		<TH nowrap rowspan=\"2\" >21.マクロ２の値</TH>
		<TH nowrap rowspan=\"2\" >22.マクロ３</TH>
		<TH nowrap rowspan=\"2\" >23.マクロ３の値</TH>
		<TH nowrap rowspan=\"2\" >24.マクロ４</TH>
		<TH nowrap rowspan=\"2\" >25.マクロ４の値</TH>
		<TH nowrap rowspan=\"2\" >26.マクロ５</TH>
		<TH nowrap rowspan=\"2\" >27.マクロ５の値</TH>
		<TH nowrap rowspan=\"2\" >備考</TH>
		</TR>
		<TR>
		<TH>旧</TH>
		<TH bgcolor=\"yellow\">新</TH>
		<TH>旧</TH>
		<TH bgcolor=\"yellow\">新</TH>
		<TH>旧</TH>
		<TH bgcolor=\"yellow\">新</TH>
		<TH>旧</TH>
		<TH bgcolor=\"yellow\">新</TH>
		<TH>旧</TH>
		<TH bgcolor=\"yellow\">新</TH>
		<TH>旧</TH>
		<TH bgcolor=\"yellow\">新</TH>
		<TH>旧</TH>
		<TH bgcolor=\"yellow\">新</TH>
		<TH>旧</TH>
		<TH bgcolor=\"yellow\">新</TH>
		<TH>旧</TH>
		<TH bgcolor=\"yellow\">新</TH>
		<TH>旧</TH>
		<TH bgcolor=\"yellow\">新</TH>
		<TH>旧</TH>
		<TH bgcolor=\"yellow\">新</TH>
		<TH>旧</TH>
		<TH bgcolor=\"yellow\">新</TH>
		<TH>旧</TH>
		<TH bgcolor=\"yellow\">新</TH>
		</TR>
       ";
		//ファイルの行数を取得
    	$file = fopen($upfilename,"r");

	    $count = 0;
    	while(!feof($file)){
			$csvb= fgets($file);
			if($csvb == "" ){
				break;
			}

			// 文字コードの変換
			$csv = mb_convert_encoding($csvb,"UTF-8", "SJIS,EUC-JP");

			//ダブルクォーテーションの削除
			$word = str_replace("\"","", $csv);

			//シングルクォーテーションの削除
			$word = str_replace("'","", $word);

		   	//csvファイルを配列に格納します。
			$strArray= explode(",", $word);

			//空行は飛ばし
			if ( count($strArray) <= 1 ){continue;}
			
			//タイトル行があった場合とばす
			if ( $_POST["titleline"] == "1" && $count == 0 ){
				$count++;
				continue;
			}
			
			
			//passもしくは"設定種別"は無視
			if ( $strArray[0] == "pass" or $strArray[0] == "設定種別" ) {continue;}
			else if ( trim($strArray[0]) == "add"){
				print "<TD bgcolor= \"cyan\">新規作成</TD>";
				//追加
			 	$jstr=creHost($strArray, $count);
			}else if ( trim($strArray[0]) == "chg" or trim($strArray[0]) == "mod" ){
				print "<TD bgcolor= \"yellow\">変更</TD>";
				//変更
				$jstr=modHost($strArray, $count);
			}else if ( trim($strArray[0]) == "del" ){
				print "<TD bgcolor= \"red\" >削除</TD>";
				//削除	
				$jstr=deleteHost($strArray, $count);
			}else if ( trim($strArray[0]) == "sub" ){
				//print "<TD>削除</TD>";
				//追加インターフェース
				//$jstr=modHost($strArray, $count);
			}else {
				$count--;
			}

    		print "</TR>";
			$count++;
	    }

		if($_POST["titleline"] == "1"){
			$count--;
		}
		print '</TABLE>';

	    echo $_FILES["upfile"]["name"] .$titleline." ( 対象".$count."件 ) をインポートしますか？";
		echo '<form method="POST" name="form1" action="./importDB.php">
		  <input type="hidden" name="filename" value='.$_FILES["upfile"]["name"].'>
		  <input type="hidden" name="titleline" value='.$_POST["titleline"].'>
          <td><input type="submit" value="インポート">
	        <input value=" 戻る " onclick="location.href=\'./import.php\'" type="button"></td>
    	  </form>';
		fclose($file);
	} else {
    	echo $_FILES["upfile"]["name"];
    	echo "ファイルをアップロードできません。";
		echo '<br><br><input value=" 戻る " onclick="location.href=\'./import.php\'" type="button">';
  	}
} else {
  	echo "ファイルが選択されていません。";
	echo '<br><br><input value=" 戻る " onclick="location.href=\'./import.php\'" type="button">';
}

//ホストの作成
function creHost ($csv_array, $count){

	include "config.php";
	require_once "getHostTbl.php";
	require_once "getHostid.php";
	require_once "getProxyHostid.php";

	$interfaceArray1 = array();
	$interfaceArray2 = array();
	$paramArray = array();

	//認証
	require_once ('auth_zabbixAPI.php');
	require_once ('getHostGroupid.php');
	auth_zabbixAPI();
	global $authKey,$errorMsg;
	global $scount, $ecount;
	
	// ホスト名の表示
	print "<TD nowrap>${csv_array[1]}</TD>";

	//ホスト名(表示名)
	print "<TD >&nbsp</TD>";
	print "<TD nowrap>${csv_array[2]}</TD>";

	// グループ名
	//ホストグループ名を取得
	$hostGid = "";
	$hostGid=getHostGroupid($csv_array[3],$authKey);
	if ( $hostGid == -1){
		// 値が取得できませんでした。"
		print "<TD nowrap><B><i>『${csv_array[3]}』は登録されていません!!<i><B></TD>";
		error_log(date("Y/m/d g:i:s")."[ERROR upload.php]".$csv_array[1]."データベースよりホストグループ名が取得できませんでした。ホストグループ名:". $csv_array[3] ."\n",3,$_LOG);
	}else{
		print "<TD nowrap>${csv_array[3]}</TD>";
	}
	//IPアドレス
	if(trim($csv_array[4]) == ""){
		// 値が取得できませんでした。"
		print "<TD nowrap>NODATA</TD>";
	}
	else{
		print "<TD nowrap>".trim($csv_array[4])."</TD>";
	}

	//テンプレート
	$Templateid = "";
	if(trim((string)$csv_array[5])== ""){
		print "<TD nowrap>&nbsp;</TD>";
	}
	else {
		print '<TD nowrap>'.trim((string)$csv_array[5]).'</TD>';
	}

	//プロキシ名
	if(trim((string)$csv_array[6])== ""){
		print "<TD nowrap>&nbsp;</TD>";
		print "<TD nowrap>&nbsp;</TD>";
	}
	else{
		print "<TD nowrap>&nbsp;</TD>";
		print "<TD nowrap>${csv_array[6]}</TD>";
	}

	// 有効/無効
	print "<TD nowrap>&nbsp;</TD>";
	switch ( trim((string)$csv_array[7]) ){
	// 0, Enable
	case '0':
	case 'ENABLE':
	case 'Enable':
	case 'enable':
	case 'TRUE':
	case 'true':
	case '有効':
		print "<TD nowrap>有効</TD>";
		break;
	// 1, disable
	case '1':
	case 'DISABLE':
	case 'Disable':
	case 'disable':
	case 'FALSE':
	case 'false':
	case '無効':
		print "<TD nowrap>無効</TD>";
		break;
	default:
		print "<TD nowrap>&nbsp;</TD>";
	
	}

	//インターフェース名
	print "<TD nowrap>&nbsp;</TD>";
	if(trim((string)$csv_array[8])== ""){
		print "<TD nowrap>&nbsp;</TD>";
	}
	else{
		print "<TD nowrap>${csv_array[8]}</TD>";
	}

	//拠点
	print "<TD nowrap>&nbsp;</TD>";
	if(trim((string)$csv_array[9])== ""){
		print "<TD nowrap>&nbsp;</TD>";
	}
	else{
		print "<TD nowrap>${csv_array[9]}</TD>";
	}

	// カスタマ名
	print "<TD nowrap>&nbsp;</TD>";
	if(trim((string)$csv_array[10])== ""){
		print "<TD nowrap>&nbsp;</TD>";
	}
	else{
		print "<TD nowrap>${csv_array[10]}</TD>";
	}

	// 機種
	print "<TD nowrap>&nbsp;</TD>";
	if(trim((string)$csv_array[11])== ""){
		print "<TD nowrap>&nbsp;</TD>";
	}
	else{
		print "<TD nowrap>${csv_array[11]}</TD>";
	}

	// 実IP
	print "<TD nowrap>&nbsp;</TD>";
	if(trim((string)$csv_array[12])== ""){
		print "<TD nowrap>&nbsp;</TD>";
	}
	else{
		print "<TD nowrap>${csv_array[12]}</TD>";
	}

	//予備1
	print "<TD nowrap>&nbsp;</TD>";
	if(trim((string)$csv_array[13])== ""){
		print "<TD nowrap>&nbsp;</TD>";
	}
	else{
		print "<TD nowrap>${csv_array[13]}</TD>";
	}

	//予備2
	print "<TD nowrap>&nbsp;</TD>";
	if(trim((string)$csv_array[14])== ""){
		print "<TD nowrap>&nbsp;</TD>";
	}
	else{
		print "<TD nowrap>${csv_array[14]}</TD>";
	}
	
	//予備3
	print "<TD nowrap>&nbsp;</TD>";
	if(trim((string)$csv_array[15])== ""){
		print "<TD nowrap>&nbsp;</TD>";
	}
	else{
		print "<TD nowrap>${csv_array[15]}</TD>";
	}
	
	//予備4
	print "<TD nowrap>&nbsp;</TD>";
	if(trim((string)$csv_array[16])== ""){
		print "<TD nowrap>&nbsp;</TD>";
	}
	else{
		print "<TD nowrap>${csv_array[16]}</TD>";
	}
	
	//予備5
	print "<TD nowrap>&nbsp;</TD>";
	if(trim((string)$csv_array[17])== ""){
		print "<TD nowrap>&nbsp;</TD>";
	}
	else{
		print "<TD nowrap>${csv_array[17]}</TD>";
	}

	// マクロ1
	if(trim((string)$csv_array[18])== ""){
		print "<TD nowrap>&nbsp;</TD>";
	}
	else{
		print "<TD nowrap>${csv_array[18]}</TD>";
	}

	// マクロ1の値
	if(trim((string)$csv_array[19])== ""){
		print "<TD nowrap>&nbsp;</TD>";
	}
	else{
		print "<TD nowrap>${csv_array[19]}</TD>";
	}

	// マクロ2
	if(trim((string)$csv_array[20])== ""){
		print "<TD nowrap>&nbsp;</TD>";
	}
	else{
		print "<TD nowrap>${csv_array[20]}</TD>";
	}

	// マクロ2の値
	if(trim((string)@$csv_array[21])== ""){
		print "<TD nowrap>&nbsp;</TD>";
	}
	else{
		print "<TD nowrap>${csv_array[21]}</TD>";
	}

	// マクロ3
	if(trim((string)@$csv_array[22])== ""){
		print "<TD nowrap>&nbsp;</TD>";
	}
	else{
		print "<TD nowrap>${csv_array[22]}</TD>";
	}
	
	// マクロ3の値
	if(trim((string)@$csv_array[23])== ""){
		print "<TD nowrap>&nbsp;</TD>";
	}
	else{
		print "<TD nowrap>${csv_array[23]}</TD>";
	}
	// マクロ4
	if(trim((string)@$csv_array[24])== ""){
		print "<TD nowrap>&nbsp;</TD>";
	}
	else{
		print "<TD nowrap>${csv_array[24]}</TD>";
	}
	
	// マクロ4の値
	if(trim((string)@$csv_array[25])== ""){
		print "<TD nowrap>&nbsp;</TD>";
	}
	else{
		print "<TD nowrap>${csv_array[25]}</TD>";
	}
	// マクロ5
	if(trim((string)@$csv_array[26])== ""){
		print "<TD nowrap>&nbsp;</TD>";
	}
	else{
		print "<TD nowrap>${csv_array[26]}</TD>";
	}
	
	// マクロ5の値
	if(trim((string)@$csv_array[27])== ""){
		print "<TD nowrap>&nbsp;</TD>";
	}
	else{
		print "<TD nowrap>${csv_array[27]}</TD>";
	}
	
	if(trim((string)@$csv_array[28])== ""){
		print "<TD nowrap>&nbsp;</TD>";
	}
	else{
		print "<TD nowrap>マクロデータ5件以上あり</TD>";
	}
	
}
// ホストの削除
function deleteHost($csv_array, $count){

	include "config.php";
	require_once "getHostTbl.php";
	require_once "getHostid.php";
	require_once "getProxyHostid.php";

	$interfaceArray1 = array();
	$interfaceArray2 = array();
	$paramArray = array();

	//ホスト名はCSVから取得
	if(! (isset($csv_array[1])) ){
		echo "<BR><font color='red' >エラー </font><BR>\n";
		echo "CSVファイルよりホスト名が取得できませんでした<BR><BR/>";
		error_log(date("Y/m/d g:i:s")."[ERROR upload.php deleteHost]".$csv_array[1]."CSVファイルよりホスト名(英語)が取得できませんでした。ホスト名:". $csv_array[1] ."\n",3,$_LOG);
		return "";
	}

	//表示用にホスト名の取得
	$svrName=$csv_array[1];
	print "<TD>${svrName}</TD>";

}

//ホストの変更(インターフェースの追加)
function modHost($csv_array, $count){

	include "config.php";
	require_once "getHostTbl.php";
	require_once "getHostid.php";
	require_once "getProxyHostid.php";

	$interfaceArray1 = array();
	$interfaceArray2 = array();
	$paramArray = array();

	//認証
	require_once ('auth_zabbixAPI.php');
	auth_zabbixAPI();
	global $authKey,$errorMsg;
	global $scount, $ecount;

	//ホスト名DBからはとらない
	if(! (isset($csv_array[1])) ){
		echo "<BR><font color='red' >エラー </font><BR>\n";
		echo "CSVファイルよりホスト名が取得できませんでした<BR><BR/>";
		error_log(date("Y/m/d g:i:s")."[ERROR upload.php modHost]".$csv_array[1]."CSVファイルよりホスト名(英語)が取得できませんでした。ホスト名:". $csv_array[1] ."\n",3,$_LOG);
		return "";
	}

	//表示用にホスト名の取得
	$svrName=$csv_array[1];

	//ホストIDを取得
	$hostid=getHostid($csv_array[1],$authKey);

	print "<TD>${svrName}</TD>";

	$orgDataArray= getHostTbl($csv_array[1],$authKey);
	$HostTblAry = $orgDataArray->result;
	// DBよりホストが取得できなかったらtrue
	$getHostErrorFlg = false;
	
	// 0以下だったらホストが存在しない
	if(count($HostTblAry) <= 0){
		print "<font color='red' >".$count."行目 "."エラー ：" . $svrName . "</font><BR>\n";
		print " 変更対象ホストがDBより取得できませんでした。";
		error_log(date("Y/m/d g:i:s")."[ERROR upload.php modHost]".$csv_array[1]."変更対象ホストホスト名(英語)がDBより取得できませんでした。ホスト名:". $csv_array[1] ."ホストテーブル件数:".count($HostTblAry) ."\n",3,$_LOG);
		$getHostErrorFlg=true;
	}

	$PARAMSary= array();
	$INVENTary= array();
	$InventF=false;
	$koumoku="";
	$kounokuname = "";

	//入力してある項目を取得
	for( $i = 2; $i < count($csv_array); $i++){
		switch ($i){
		case 2: // ホスト名(日本語)

			$PARAMSary['name']=$csv_array[$i];
			$kounokuname="ホスト名(日本語)";
			if($getHostErrorFlg) {
				// 変更対象のホストが存在しないとき
				$orgName = "<B><i>ホストが取得できませんでした。</i></B>";
				print "<TD nowrap >${orgName}</TD>";
				print "<TD bgcolor=\"yellow\" nowrap >${csv_array[$i]}</TD>";
				// 変更ホストが取得できないときはループ抜ける
				break 2;
				
			}
			else{
				// 変更対象のホストが存在する時
				$orgName = $orgDataArray->result[0]->name;
			}

			print "<TD nowrap >${orgName}</TD>";
			print "<TD bgcolor=\"yellow\" nowrap >${csv_array[2]}</TD>";
			break;
		case 3: //グループ名
		case 4: //IPアドレス
		case 5://テンプレート
			print "<TD >&nbsp</TD>";
			break;
		case 6: //プロキシ名
	    	
	    	$proxyid = 0;
			
			//プロキシID
			//空だったら空に変更
			//IDが取得できなかったら変更しない。
			if ($csv_array[$i] == "" ){
				$newproxy_hostid = $csv_array[$i];
				$PARAMSary['proxy_hostid'] = trim($csv_array[$i]);
			}else{
				// プロキシIDを取得
				$proxyid =get_proxy_hostid(trim($csv_array[$i]),$authKey);
				// プロキシのホストIDが取得できないときは変更しない
				if(! (isset($proxyid))){
					echo "<font color='red' >エラー ：" . $svrName . "</font><BR>\n";
					echo "DBよりProxyIDが取得できませんでした。Proxyは変更されません。  MSG:${errorMsg} ID: ${csv_array[$i]}<br><br/>";
					error_log(date("Y/m/d g:i:s")."[ERROR upload.php modHost]".$csv_array[1]."DBよりProxyIDが取得できませんでした。Proxyは変更されません。プロキシ名:". $csv_array[$i] ."MSG:".$errorMsg ."\n",3,$_LOG);
					$newproxy_hostid = "変更なし(空)";
				}
				else{
					$newproxy_hostid = $csv_array[$i];
					$PARAMSary['proxy_hostid'] = trim($csv_array[$i]);
				}
			}

			//現プロキシ名を取得
			$orgproxyid =$orgDataArray->result[0]->proxy_hostid;

		    $orgproxyname = get_proxy_hostid($orgproxyid,$authKey,true);
		 
		    print "<TD nowrap>${orgproxyname}</TD>";
			print "<TD bgcolor=\"yellow\" nowrap>${newproxy_hostid}</TD>";
			break;
			
		case 7: //有効/無効
			$orgstatus =$orgDataArray->result[0]->status;

			//元データの取得
			if($orgstatus == 0 ){
				//0:Enable
				$orgstatusWord = "有効";
			}
			else if($orgstatus == 1 ){
				//1:disable
				$orgstatusWord = "無効";
			}

			print "<TD nowrap >${orgstatusWord}</TD>";
			//新データの取得

			if($csv_array[$i] == "" ) {
				echo "<font color='red' >エラー ：" . $svrName . "</font><BR>\n";
				print "CSVファイルよりステータスが取得できませんでした。無効で登録します。ホスト名： ${csv_array[1]}";
				error_log(date("Y/m/d g:i:s")."[ERROR upload.php modHost]".$csv_array[1].
				"CSVファイルよりステータスが取得できませんでした。無効で登録します。ホスト名:". $csv_array[1] ."MSG:".$csv_array[$i] ."\n",3,$_LOG);
				$csv_array[$i] = 1;
			}

			$PARAMSary['status']=trim($csv_array[7]);

			$statusWord = "";
			if(strcasecmp($PARAMSary['status'],"0") == 0 || 
			   strcasecmp($PARAMSary['status'],"有効") == 0 || 
			   strcasecmp($PARAMSary['status'],"true") == 0 || 
			   strcasecmp($PARAMSary['status'],"enable") == 0 ){
				//0:Enable
				$statusWord = "有効";
			}
			else if(strcasecmp($PARAMSary['status'] , "1"  ) == 0 || 
					strcasecmp($PARAMSary['status'] , "無効") == 0 || 
					strcasecmp($PARAMSary['status'] , "false") == 0 || 
					strcasecmp($PARAMSary['status'] , "disable") == 0 ){
				//1:disable

				$statusWord = "無効";
			}

			if($statusWord == "" ){
				$statusWord = "変更なし(空)";
			}

			
			print "<TD bgcolor=\"yellow\" nowrap >${statusWord}</TD>";
			break;
			
		case 8: // インターフェイス名
			//インベントリ情報が存在したらTRUEにする
			$InventF=true;
			//元データの取得
			$orgtype =@$orgDataArray->result[0]->inventory->type;
			echo "<TD nowrap>$orgtype</TD>";
			
			//新たに登録するデータの取得
			$INVENTary['type']=$csv_array[$i];
			$newtype = $csv_array[$i];
		    echo "<TD bgcolor=\"yellow\" nowrap >$newtype</TD>";
			break;
			
		case 9: // 拠点名type_full
			$InventF=true;
			
			//元データの取得
			$orgtype_full =@$orgDataArray->result[0]->inventory->type_full;
			echo "<TD nowrap>$orgtype_full</TD>";

			$INVENTary['type_full']=$csv_array[$i];
			$newtype_full=$csv_array[$i];
			echo "<TD bgcolor=\"yellow\" nowrap >${newtype_full}</TD>";
			break;

		case 10: // カスタマ名name
			$InventF=true;
			//元データの取得
			$orgname =@$orgDataArray->result[0]->inventory->name;
			echo "<TD nowrap >$orgname</TD>";

			$INVENTary['name']=$csv_array[$i];
			echo "<TD bgcolor=\"yellow\" nowrap >${INVENTary['name']}</TD>";
			break;

		case 11: // 機種alias
			$InventF=true;
			//元データの取得
			$orgname =@$orgDataArray->result[0]->inventory->alias;
			echo "<TD nowrap >$orgname</TD>";

			$INVENTary['alias']=$csv_array[$i];
			echo "<TD bgcolor=\"yellow\" nowrap >${INVENTary['alias']}</TD>";
			break;
		case 12: // os実IP
			$InventF=true;
			//元データの取得
			$orgname =@$orgDataArray->result[0]->inventory->os;
			echo "<TD nowrap >$orgname</TD>";

			$INVENTary['os']=$csv_array[$i];
			echo "<TD bgcolor=\"yellow\" nowrap >${INVENTary['os']}</TD>";
			break;
		case 13: // 予備1
			$InventF=true;
			//元データの取得
			$orgname =@$orgDataArray->result[0]->inventory->os_full;
			echo "<TD nowrap >$orgname</TD>";

			$INVENTary['os_full']=$csv_array[$i];
			echo "<TD bgcolor=\"yellow\" nowrap >${INVENTary['os_full']}</TD>";
			break;
		case 14: // 予備2
			$InventF=true;
			//元データの取得
			$orgname =@$orgDataArray->result[0]->inventory->os_short;
			echo "<TD nowrap >$orgname</TD>";

			$INVENTary['os_short']=$csv_array[$i];
			echo "<TD bgcolor=\"yellow\" nowrap >${INVENTary['os_short']}</TD>";
			break;
		case 15: // 予備3
			$InventF=true;
			//元データの取得
			$orgname =@$orgDataArray->result[0]->inventory->serialno_a;
			echo "<TD nowrap >$orgname</TD>";

			$INVENTary['serialno_a']=$csv_array[$i];
			echo "<TD bgcolor=\"yellow\" nowrap >${INVENTary['serialno_a']}</TD>";
			break;
		case 16: // 予備4
			$InventF=true;
			//元データの取得
			$orgname =@$orgDataArray->result[0]->inventory->serialno_b;
			echo "<TD nowrap >$orgname</TD>";

			$INVENTary['serialno_b']=$csv_array[$i];
			echo "<TD bgcolor=\"yellow\" nowrap >${INVENTary['serialno_b']}</TD>";
			break;
		case 17: // 予備5
			$InventF=true;
			//元データの取得
			$orgname =@$orgDataArray->result[0]->inventory->tag;
			echo "<TD nowrap >$orgname</TD>";

			$INVENTary['tag']=$csv_array[$i];
			echo "<TD bgcolor=\"yellow\" nowrap >${INVENTary['tag']}</TD>";
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
			print '<TD nowrap>&nbsp</TD>';
			break;
		case 27: // マクロ5値
			print '<TD nowrap>&nbsp</TD></TR>';
			break;
		}
		// 設定対象のデータがない場合
		if ( !(isset($PARAMSary))){
			return "";
		}
	}
}

?></p>
</div>
</body></html>
