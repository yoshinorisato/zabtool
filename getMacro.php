<?php
// �z�X�gID����}�N������擾
function getMacro( $hostid, $auth )
{
	include "config.php";
	global $errorMsg;

	$serverIp=$zabtool['SERVER_IP'];
	
	$apiUrl  ="http://$serverIp/zabbix/api_jsonrpc.php";

    $json = array("jsonrpc" => "2.0", "method" => "usermacro.get", "params" => array("output" => "extend", "hostids"=> $hostid ),"auth" => $auth, "id" => 2);

	$ch = curl_init($apiUrl);
	curl_setopt($ch,CURLOPT_URL,$apiUrl);
	curl_setopt($ch,CURLOPT_POST,1);
	curl_setopt($ch,CURLOPT_CUSTOMREQUEST, "POST");
	curl_setopt($ch,CURLOPT_POSTFIELDS, json_encode($json));
	curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
	curl_setopt($ch,CURLOPT_HTTPHEADER,array("Content-Type: application/json-rpc"));
	
	$ret = curl_exec($ch);
	if (curl_errno($ch) > 0) {
		
		//echo "ERR   ".curl_errno($ch);
		$errorMsg=curl_error($ch);
        
        //�G���[�R�[�h���A���Ă����Ƃ�
        $sid = curl_errno($ch);
		return $sid ;
	}
	
	$ret = json_decode($ret);
	
	//�����߂��Ă��Ȃ������Ƃ�
	if(isset($ret->error))
	{
		//drupal_set_message('Unable to login. It seems that something is wrong here. Please contact the Site Administrator.', DRUPAL_MSG_TYPE_ERR);
		$errorMsg=$ret->error->message;
		$errorMsg=$ret->error->code . " " .$errorMsg ." ". $ret->error->data;

		return -1; //"Connection Error";
	}
	elseif(isset($ret->result))
	{
		//�z�X�gID���擾�����
		//echo "Mac ".@$ret->result[0]->templateid." END";
		//print_r(@$ret->result[0]);
		return @$ret->result;
	}

	if (curl_errno($ch) > 0){
		echo "ERR   ".curl_errno($ch);

		//�G���[�R�[�h���A���Ă����Ƃ�
    	$sid = $curl_errno;
    } else {
		//���펞0�ŕԂ��B
        $sid = 0;
    }

	return $sid;
}

?>
