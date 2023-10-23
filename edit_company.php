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
    $auth_data = $auth->isValid();
    if ($auth_data['success']) {
        $stmt = $pdo->prepare("update companies set company_name=:company_name,department_id=:department_id,url=:url,email=:email,phone=:phone,address=:address,address2=:address2,address3=:address3,address4=:address4,address5=:address5,subject_address=:subject_address where sl = :sl");
        $stmt->execute(
            array(
                ":company_name" => $post_data->company_name,
                ":department_id"=>$post_data->department->value,
                ":url" => $post_data->url,
                ":email" => $post_data->email,
                ":phone" => $post_data->phone,
                ":address" => $post_data->address,
                ":address2" => $post_data->address2,
                ":address3" => $post_data->address3,
                ":address4" => $post_data->address4,
                ":address5" => $post_data->address5,
                ":subject_address" => $post_data->subject_address,
                ":sl" => $post_data->sl
            )
        );
    } else {
        $response['success'] = false;
        $response['error'] = "Invalid User Token. Access Forbidden.";
    }
} catch (\Error $e) {
    $response['success'] = false;
    $response["error"] = $e->getMessage();
}
echo json_encode($response);
