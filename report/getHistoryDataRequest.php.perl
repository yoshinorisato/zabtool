<?php
//parameter:  time event ack
//time
$hostname = '""';
$item = '""';
$selecttimeFrom = '""';
$selecttimeTo = '""';


//ホスト名
if(isset($_REQUEST["hostname"])){
        $hostname = '"'.trim($_REQUEST["hostname"],'"').'"';
}

//Event
//"%net.if.in[\"{_NIC%"
if(isset($_REQUEST["item"])){
		$str = str_replace('$',"\\$",$_REQUEST["item"]);
        $event = '"'.trim($str,'"').'"';
}

//日付From
if(isset($_REQUEST["selecttimeFrom"])){
        $selecttimeFrom= '"'.trim($_REQUEST["selecttimeFrom"],'"').'"';
}

//日付To
if(isset($_REQUEST["selecttimeTo"])){
        $selecttimeTo = '"'.trim($_REQUEST["selecttimeTo"],'"').'"';
}
$outmes = exec('/usr/local/share/zabbix/externalscripts/getHistoryData.pl '. $hostname.' '.$event . ' ' . $selecttimeFrom . ' ' . $selecttimeTo , $ret);
//取得データの表示
foreach ($ret as $value){
  //一行目は件数+エラー有無 
  // -1 パラメータエラー
  // 0 	該当なし ０件
  // 1以上　データあり
  //$str = str_replace('<br>',"\r\n",$value);
  echo $value . "<br>" ;
}
?>
