<?php
    include "../config.php";
    //���N���[�Y�C�x���g�̎擾
    require_once ('../getUncloseProblem.php');
    
    $request = json_decode(file_get_contents("php://input"), true);
    $value = 0;
    
    //�F��
    require_once ('../auth_zabbixAPI.php');
    auth_zabbixAPI();
    
    global $authKey,$errorMsg;
    
    $datestring =$request['code'];
        
    $result =[
        "value" => $request['code'],
    ];
    $json = json_encode($result, JSON_UNESCAPED_UNICODE);
    header("Content-Type: application/json; charset=UTF-8");
    echo $json;
    exit;
?>
