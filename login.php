<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: access");
header("Access-Control-Allow-Methods: POST");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require __DIR__ . '/classes/Database.php';
require __DIR__ . '/classes/JwtHandler.php';

function msg($success, $status, $message, $extra = [])
{
    return array_merge([
        'success' => $success,
        'status' => $status,
        'message' => $message
    ], $extra);
}

$db_connection = new Database();
$pdo = $db_connection->dbConnection();

$data = json_decode(file_get_contents("php://input"));
$returnData = [];

if ($_SERVER["REQUEST_METHOD"] != "POST") :
    $returnData = msg(false, 404, 'Page Not Found!asd');

elseif (
    !isset($data->username)
    || !isset($data->password)
    || empty(trim($data->username))
    || empty(trim($data->password))
) :

    $fields = ['fields' => ['username', 'password']];
    $returnData = msg(false, 422, 'Please Fill in all Required Fields!', $fields);

else :
    $username = trim($data->username);
    $password = trim($data->password);

    if (strlen($password) < 8) :
        $returnData = msg(false, 422, 'Your password must be at least 8 characters long!');

    else :
        try {

            $query_stmt = $pdo->prepare("SELECT * FROM `users` WHERE `user_name`=:username");
            $query_stmt->execute(
                array(":username" => $username)
            );

            if ($query_stmt->rowCount()) :
                $row = $query_stmt->fetch(PDO::FETCH_ASSOC);

                $check_password = ($password == $row['password']);

                if ($check_password) :
                            $jwt = new JwtHandler();
                            $token = $jwt->jwtEncodeData(
                                'http://localhost/FLICKAPIS/',
                                array("user_name" => $row['user_name'])
                            );
    
                            $returnData = [
                                'success' => true,
                                'message' => "you have successfully logged in",
                                'token' => $token,
                                'user_name'=>$row['user_name'],
                                "privilege"=>$row['privilege']
                            ];

                else :
                    $returnData = msg(false, 422, 'Invalid Password!');
                endif;


            else :
                $returnData = msg(false, 422, 'Invalid Username!');
            endif;
        } catch (PDOException $e) {
            $returnData = msg(false, 500, $e->getMessage());
        }

    endif;

endif;

echo json_encode($returnData);
