<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use modules\products;

error_reporting(1);
if($_SERVER["REQUEST_METHOD"] == "POST"){
   
    include "../../includes/init.php";
    include "../../modules/products.php";
      try {
         JWT::decode(getToken(), new Key(seckey(), "HS256"));
         $product = new products($conn);
         
         $product->store();

     } catch (Exception $e) {
         response(false, 401, $e->getMessage());
     }
      
     
}



?>