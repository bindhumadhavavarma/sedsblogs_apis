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
        $stmt = $pdo->query("select * from companies");
        $companies= $stmt->fetchAll(PDO::FETCH_ASSOC);
        for($i=0;$i<count($companies);$i++){
            if($companies[$i]['department_id']==$post_data->value){
                $response['success']=false;
                $response["error"]="cannot delete department, because there are companies added under the department.";
            }
        }
        if($response['success']==true)$stmt = $pdo->query("delete from departments where sl='$post_data->value'");
    } else {
        $response['success'] = false;
        $response['error'] = "Invalid User Token. Access Forbidden.";
    }
} catch (\Error $e) {
    $response['success'] = false;
    $response["error"] = $e->getMessage();
}
echo json_encode($response);
