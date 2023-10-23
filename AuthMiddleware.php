<?php
require __DIR__ . '/classes/JwtHandler.php';

class Auth extends JwtHandler
{
    protected $db;
    protected $headers;
    protected $token;

    public function __construct($db, $headers)
    {
        parent::__construct();
        $this->db = $db;
        $this->headers = $headers;
    }

    public function isValid()
    {

        if (array_key_exists('Authorization', $this->headers) && preg_match('/Bearer\s(\S+)/', $this->headers['Authorization'], $matches)) {

            $data = $this->jwtDecodeData($matches[1]);
            if (
                isset($data['data']->user_name) &&
                $user = $this->fetchUser($data['data']->user_name)
            ) :
                return [
                    "success" => true,
                    "user" =>$user,
                ];
            else :
                return [
                    "success" => false,
                    "message" => $data["message"],
                ];
            endif;
        } else {
            return [
                "success" => false,
                "message" => "Token not found in request"
            ];
        }
    }

    protected function fetchUser($user_id)
    {
        try {
            $query_stmt = $this->db->prepare("SELECT * FROM `users` WHERE `user_name`=:id");
            $query_stmt->execute(
                array(
                    ":id"=>$user_id
                )
            );
            if ($query_stmt->rowCount()) :
                return $query_stmt->fetch(PDO::FETCH_ASSOC);
            else :
                return false;
            endif;
        } catch (PDOException $e) {
            return null;
        }
    }
}