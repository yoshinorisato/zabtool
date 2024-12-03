<HTML>
<HEAD>
<META http-equiv=Content-type content="text/html; charset=utf-8">
<link rel="stylesheet" href="../css/jquery.datetimepicker.css" />
<script type="text/javascript" src="../jquery/jquery.js"></script>
<script type="text/javascript" src="../jquery/ui/jquery-ui-1.8.18.custom.js"></script>
<script type="text/javascript" src="../jquery/ui/i18n/jquery.datetimepicker.full.min.js"></script>
<style>
.head {margin:10px;padding-top:10px;}
</style>
<LINK href="../css/menu.css" type=text/css rel=stylesheet>
<SCRIPT src="../js/script.js" type=text/javascript></SCRIPT>

<title>イベントクローズ</title>
<SCRIPT>
<!--
jQuery( function() {
  $("#datetimepicker").datetimepicker();
    } );

function disp(){
	
	var date1 = document.getElementById('datetimepicker');
	if ( date1.value == ""){
		date1.style.backgroundColor = "#ff0000";

		alert('開始日を入力して下さい。');
		return false;
	}
	date1.style.backgroundColor = "#ffffff";

    document.getElementById("Result").value = "問い合わせ中です…";
    var data = {
        "code": document.getElementById("datetimepicker").value
    }
    var json = JSON.stringify(data);
    var xhr = new XMLHttpRequest();
    xhr.open("POST", "selectCloseCount.php");
    xhr.setRequestHeader("content-type", "application/x-www-form-urlencoded;charset=UTF-8");
    xhr.send(json);
    xhr.onreadystatechange = function () {
        try {
            if (xhr.readyState == 4) {
                if (xhr.status == 200) {
                    var result = JSON.parse(xhr.response);
                    document.getElementById("Result").value = result.value == 0 ? "選択してください" : result.value;
                } else {
                }
            } else {
            }
        } catch (e) {
        } 
    };

	// 「OK」時の処理開始 ＋ 確認ダイアログの表示
	if(window.confirm(date1.value + ' 以前のイベントリストのクローズを行います。よろしいですか？')){

		document.form1.action = "EventClose.php"; 
		document.form1.submit(); 
	}

	// 「キャンセル」時の処理開始
	else{
		window.alert('キャンセルされました'); // 警告ダイアログを表示
		return false;
	}
	// 「キャンセル」時の処理終了
}
-->

</SCRIPT>
</HEAD>
<BODY>
<SCRIPT LANGUAGE="JavaScript">
WriteMenu(4)
</SCRIPT>
<div class="head">
<H4>クローズするイベントを指定します。<H4>
<?php
    include "../config.php";
    
    //未クローズイベントの取得
    require_once ('../getUncloseProblem.php');
    
    //認証
    require_once ('../auth_zabbixAPI.php');
    
    auth_zabbixAPI();
    global $authKey,$errorMsg;
    
    $redis = new Redis();
    $redis->connect('127.0.0.1', 6379);
    
    $eventcnt = $redis->get('count');

    $eventcntinDB = getUncloseProblemCount($authKey);
    if($eventcntinDB != $eventcnt ){
        $eventcnt = $eventcntinDB;
    }
    $redis->set('count', $eventcnt);
    echo "現在".$eventcnt ."件の未クローズイベントがあります。";
    
    $redis->close();
?>
</div>
<form name="form1" method="post" enctype="multipart/form-data" >
<table>
<tr>
<td >
    日付範囲: 
    <input id="datetimepicker" type="text" autocomplete="off" >以前

</td>
</tr>
<tr >
<td height="50">
	<input type="button" value="削除" onClick="disp()" >
</td>

</tr>
</table>
    <input type="text" value="Some value" readonly="readonly" id="Result" />

</form>
</BODY></HTML>
