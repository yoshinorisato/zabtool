<?php
//parameter:  time ack
//time
$selecttime = '""';
$event = '""';
$ack = '""';

//日付From
if(isset($_REQUEST["selecttime"])){
    $selecttime = '"'.trim($_REQUEST["selecttime"],'"').'"';
}
else{
	$selecttime = "NOW-3600";
}

if(isset($_REQUEST["event"])){
        $event= '"'.trim($_REQUEST["event"],'"').'"';
}else{
	$event = "\"\"";
}


if(isset($_REQUEST["ack"])){
        $ack = '"'.trim($_REQUEST["ack"],'"').'"';
}else{
	$ack = "";
}

$outmes = exec('/usr/local/share/zabbix/externalscripts/getEventList.pl '. $selecttime. ' ' . $event . ' ' . $ack , $ret);
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
