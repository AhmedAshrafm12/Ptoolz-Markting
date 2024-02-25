<?php
use modules\clients;
if($_SERVER["REQUEST_METHOD"] == "GET"){
   
    include "../../includes/init.php";
    include "../../modules/clients.php";
      $client = new clients($conn);
     response(true , 200 , "success" , $client->get_by_id());

     
}



?>