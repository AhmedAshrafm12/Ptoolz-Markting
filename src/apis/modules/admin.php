<?php
namespace modules;

use Firebase\JWT\JWT;

class admin{
 
    protected $conn;
    protected $table = 'admins';
    protected $msg , $success = 0;
    public function __construct($conn){
        $this->conn = $conn;
    }



    public function login($data)
    {
        $username = filter_var($data['username'] , FILTER_SANITIZE_SPECIAL_CHARS);
        $password = filter_var($data['password'] , FILTER_SANITIZE_SPECIAL_CHARS);

        $stmt = $this->conn->prepare("select * from $this->table where username =  ? and password = ? limit 1");
        $stmt->execute(array($username ,sha1($password)));
        return $stmt->fetch() ?? false;

    }
    
 


    public function generateToken($user){
        $sec_key = 'factory211';
        $payload = array(
            "exp"=>time() + 1800 , 
            "data"=>$user
        );
         $token = JWT::encode($payload,$sec_key,"HS256");
         return $token;

    }

}