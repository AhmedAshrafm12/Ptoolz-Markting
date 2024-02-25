<?php
use modules\clients;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
if($_SERVER["REQUEST_METHOD"] == "GET"){

     include "../../includes/init.php";
     include "../../modules/clients.php";
       try {
          JWT::decode(getToken(), new Key(seckey(), "HS256"));
          $client = new clients($conn);
          response(true, 200, "success",$client->get_by_id());
      } catch (Exception $e) {
          response(false, 401, $e->getMessage());
      }
}



?>