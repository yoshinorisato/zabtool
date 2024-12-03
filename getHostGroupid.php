<?php
function getHostGroupid( $hostGName, $auth )
{
	include "config.php";

    $result=null;

	$serverIp = $zabtool['SERVER_IP'];
	if(!isset($hostGName)){
		$errorMsg="Get HostGroupName Empty!!";
		return -1;
	}

    //グループIDの取得
	$result=exec("curl -d '{\"jsonrpc\":\"2.0\",\"method\":\"hostgroup.get\",\"id\":2,\"params\":{\"output\":\"extend\",\"filter\":{\"name\": \"${hostGName}\"}},\"auth\":\"${auth}\"}' -H \"Content-Type: application/json-rpc\" http://${serverIp}/zabbix/api_jsonrpc.php", $ret);
	$obj = json_decode($result);
	
	if (!isset($obj)){
		$errorMsg="Get Null!! Object Failure!! ";
		return -1;	
	}
	if (isset($obj->error)){
        $errorMsg= "{$obj->error[0]->code} : {$obj->error[0]->message} : {$obj->error[0]->data}";
		echo $errorMsg;
        return -1;
    }
    if (!(isset($obj->result['0']->groupid)) OR $obj->result['0']->groupid == ""){
        $errorMsg= "DBよりグループが取得できませんでした。".${hostGName};
    	return -1;
    } 
        //print("OUTPUT---------------".@$obj->result['0']->name."--------END");

    return @$obj->result['0']->groupid;
}
?>
