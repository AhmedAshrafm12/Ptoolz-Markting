<?php
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use modules\products;
if($_SERVER["REQUEST_METHOD"] == "POST"){

     include "../../includes/init.php";
     include "../../modules/products.php";
       try {
          JWT::decode(getToken(), new Key(seckey(), "HS256"));
          $product = new products($conn);
 
         $product->delete();
       
      } catch (Exception $e) {
          response(false, 401, $e->getMessage());
      }
}



?>