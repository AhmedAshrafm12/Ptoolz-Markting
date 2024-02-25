<?php
use modules\clients;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
if($_SERVER["REQUEST_METHOD"] == "POST"){
     include "../../includes/init.php";
     include "../../modules/clients.php";
       try {
          JWT::decode(getToken(), new Key(seckey(), "HS256"));
          $client = new clients($conn);
          
      $client->delete();

      } catch (Exception $e) {
          response(false, 401, $e->getMessage());
      }
     
}



?>