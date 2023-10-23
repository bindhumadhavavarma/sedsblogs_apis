<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: access");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require __DIR__ . '/classes/Database.php';
require __DIR__ . '/AuthMiddleware.php';

$response['success'] = true;
try {
    $db_connection = new Database();
    $allHeaders = getallheaders();
    $post_data = json_decode(file_get_contents("php://input"));
    $pdo = $db_connection->dbConnection();
    $auth = new Auth($pdo, $allHeaders);
    $auth_data=$auth->isValid();
    if($auth_data['success']){
        $stmt=$pdo->prepare("insert into companies (company_name,url,department_id,email,phone,address,address2,address3,address4,address5,subject_address) values(:company_name,:url,:department_id,:email,:phone,:address,:address2,:address3,:address4,:address5,:subject_address)");
        $stmt->execute(
            array(
                ":company_name"=>$post_data->company_name,
                ":url"=>$post_data->url,
                ":department_id"=>$post_data->department->value,
                ":email"=>$post_data->email,
                ":phone"=>$post_data->phone,
                ":address"=>$post_data->address,
                ":address2"=>$post_data->address2,
                ":address3"=>$post_data->address3,
                ":address4"=>$post_data->address4,
                ":address5"=>$post_data->address5,
                ":subject_address"=>$post_data->subject_address,
            )
            );
    }
    else{
        $response['success']=false;
        $response['error']="Invalid User Token. Access Forbidden.";
    }
} catch (\Error $e) {
    $response['success'] = false;
    $response["error"] = $e->getMessage();
}
echo json_encode($response);
