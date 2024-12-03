<HTML>
<HEAD>
<SCRIPT src="jquery/jquery.js" type=text/javascript></SCRIPT>
<META http-equiv=Content-type content="text/html; charset=utf-8">
<SCRIPT src="jquery/ui/jquery-ui-1.8.18.custom.js" type=text/javascript></SCRIPT>

<LINK href="css/menu.css" type=text/css rel=stylesheet>
<SCRIPT src="js/script.js" type=text/javascript></SCRIPT>

<title>一括設定ツール　エクスポート</title>
<style>
.test2010 {margin:10px;padding:10px;}
.test2010 table {border-collapse:collapse;}
.test2010 table th {border:1px solid #999;padding:3px;background:#eee;} 
.test2010 table td {border:1px solid #999;padding:3px;} 
</style>
<SCRIPT TYPE="text/JavaScript">
function exportSubmit(obj){
	var checkBoxList = obj.elements['id[]']
	var count=0

	var aaa = obj.elements['groupList[]']

	//選択項目のチェック
	for(i=0;i<checkBoxList.length;i++){
		if(checkBoxList[i].checked){
			count++
		}
	}
	//チェックがなかったらエラー
	if(count <= 0 ){
		window.alert("出力するグループを選択してください。")
		return false
	}
	else{
		if(window.confirm( "CSVファイルへのエクスポートを行います。よろしいでしょうか?")){
			obj.action="exportFileSel.php"
			obj.submit()
		}
	}
}
</SCRIPT>
</HEAD>
<BODY>
<SCRIPT LANGUAGE="JavaScript">
<!--
WriteMenu(2)
//-->
</SCRIPT>

<div class="test2010">
<h4>取得したいグループ名を選択して実行ボタンを押して下さい。</h4>
<table class="normal">  
<tbody>
<form name= "ExportForm" >
<input type="button" value="実行" name="button1" onclick=exportSubmit(this.form) >

 <tr>    
 <th><input type="checkbox" name= "allselect" onclick="$(this.parentNode.parentNode.parentNode).find('input[type=\'checkbox\']').attr('checked', this.checked)" /></th>
 <th>ホストグループ名</th>    
 <th>グループID</th>   
 </tr>
<?php
	
	include "config.php";

	//認証
	require_once ('auth_zabbixAPI.php');

	auth_zabbixAPI();
	global $authKey,$errorMsg;
	
	//全ホストデータを取得
	require_once "getAllGroup.php";
	$retArray=getAllGroup($authKey);
	$count = 0;
	
	//グループ件数分ループ
	foreach( $retArray->result as $groupdata){
		$group 			= $groupdata->name;
		$groupid 		= $groupdata->groupid;
		print "<tr><td><input type=\"checkbox\" name=\"id[]\" value=\"${groupid}\" />";
		print "</td>";
		print "<td>${group}</td><td>${groupid}</td></tr>";
		$count++;
	}

?>

</form>
</tbody>
</table>
</BODY>
</HTML>
