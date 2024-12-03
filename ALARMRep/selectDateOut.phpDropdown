<HTML>
<HEAD>
<META http-equiv=Content-type content="text/html; charset=utf-8">
<link rel="stylesheet" href="../jquery/themes/base/jquery.ui.all.css" />
<script type="text/javascript" src="../jquery/jquery.js"></script>
<script type="text/javascript" src="../jquery/ui/jquery-ui-1.8.18.custom.js"></script>
<script type="text/javascript" src="../jquery/ui/i18n/jquery.ui.datepicker-ja.js"></script>
<style>
.head {margin:10px;padding-top:10px;}
</style>
<LINK href="../css/menu.css" type=text/css rel=stylesheet>
<SCRIPT src="../js/script.js" type=text/javascript></SCRIPT>

<title>アラーム統計レポート</title>
<SCRIPT>
<!--
jQuery( function() {
    var dates = jQuery( '#jquery-ui-datepicker-from, #jquery-ui-datepicker-to' ) . datepicker( {
        showAnim: 'fadeIn',
        changeMonth: true,
        numberOfMonths: 2,
        showCurrentAtPos: 1,
        onSelect: function( selectedDate ) {
            var option = this . id == 'jquery-ui-datepicker-from' ? 'minDate' : 'maxDate',
                instance = jQuery( this ) . data( 'datepicker' ),
                date = jQuery . datepicker . parseDate(
                    instance . settings . dateFormat ||
                    jQuery . datepicker . _defaults . dateFormat,
                    selectedDate, instance . settings );
            dates . not( this ) . datepicker( 'option', option, date );
        }
    } );
} );

function disp(){

	var selectitem = document.getElementById('cbox');
	if ( selectitem.value == ""){
		alert('取得するアイテムを選択してください');
		return false;

	}
	var itemnum = selectitem.value;
	
	var date1 = document.getElementById('jquery-ui-datepicker-from');
	if ( date1.value == ""){
		date1.style.backgroundColor = "#ff0000";
		date1 = document.getElementById('jquery-ui-datepicker-to');
		if (date1.value == ""){

			date1.style.backgroundColor = "#ff0000";

			alert('開始日、終了日を入力して下さい。');
			return false;
		}
		date1.style.backgroundColor = "#ffffff";

		alert('開始日を入力して下さい。');
		return false;
	}
	date1.style.backgroundColor = "#ffffff";

	date1 = document.getElementById('jquery-ui-datepicker-to');
	if (date1.value == ""){
		date1.style.backgroundColor = "#ff0000";

		alert('終了日を入力して下さい。');
		return false;
	}
	date1.style.backgroundColor = "#ffffff";

	// 「OK」時の処理開始 ＋ 確認ダイアログの表示
	if(window.confirm('イベントリストの取得を行います。よろしいですか？')){

		document.form1.action = "getAlarmToukei.php"; 
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
WriteMenu(3)
</SCRIPT>
<div class="head">
<H4>取得する日付の範囲を選択し出力ボタンを押して下さい。<H4>
</div>
<form name="form1" method="post" enctype="multipart/form-data">
<table>
<tr>
<td >
<p id="jquery-ui-datepicker-wrap">
    日付範囲: 
    <input type="text" id="jquery-ui-datepicker-from" name="jquery-ui-datepicker-from"/>
    <label for="jquery-ui-datepicker-from">から</label>
    <input type="text" id="jquery-ui-datepicker-to" name="jquery-ui-datepicker-to"/>
    <label for="jquery-ui-datepicker-to">まで</label>
</p>
</td>
</tr>
<tr >
<td height="50">
	出力種別：
    <select id="cbox" name="comboIdx">
      <option value="1">全イベント</option>
      <option value="2">死活監視(ICMP)</option>
      <option value="3">メモリ使用率</option>
      <option value="4">LinkUp</option>
      <option value="5">LinkDown</option>
      <option value="6">ColdStart</option>
      <option value="7">CPU使用率</option>
      <option value="8">装置温度</option>
      <option value="9">帯域幅使用率(受信)</option>
      <option value="10">帯域幅使用率(送信)</option>
      <option value="11">ストーム発生</option>
      <option value="12">コンフィグ変更発生</option>
      <option value="13">AP無線IF Up</option>
      <option value="14">AP無線IF Down</option>
      <option value="15">MJランプ状態</option>
      <option value="16">MNランプ状態</option>
      <option value="17">月ごと</option>
	  </select>
	<input type="button" value="出力" onClick="disp()" >
</td>
</tr>
</table>

	

</form>
</BODY></HTML>
