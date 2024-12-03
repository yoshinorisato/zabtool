<?php

require 'ZabbixApi.class.php';

try {
    $hostid='10345';
    $itemid='31582';
    $history=0; // 0 - float; 1 - string; 2 - log; 3 - integer; 4 - text.

    $api = new ZabbixApi('http://hostname/api_jsonrpc.php', 'your user name', 'your uesrs password');
    $res = $api->historyGet(
      array(
        'history' => $history,
        'output' => 'extend',
        'hostids' => array (
          $hostid
        ),
        'itemids' => array (
          $itemid
        )
        #,'limit' => 10
      )
    );
    foreach ($res as $result) {
      $value = $result->value;
      $clock = $result->clock;
      echo $value . "," . date("Y/m/d H:i:s", intval($clock)) . "\n";
    }
} catch (Exception $e) {
    echo $e->getMessage() . "\n";
}

?>

