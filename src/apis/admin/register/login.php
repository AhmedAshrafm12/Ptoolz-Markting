<?php
use modules\admin;
use Firebase\JWT\JWT;
if($_SERVER["REQUEST_METHOD"] == "POST"){
    require '../../vendor/autoload.php';
    include "../../includes/init.php";
    include "../../modules/admin.php";
    $admin = new admin($conn);
   $data = json_decode(file_get_contents('php://input'), true);

     $response = $admin->login($data);
     if($response)
     echo json_encode(array("data"=>$admin->generateToken($response) , "success"=>true));
     else 
     echo json_encode(array("data"=>"wrong credentials" , "success"=>false));
     
}



?>