<?php
use modules\event;
if($_SERVER["REQUEST_METHOD"] == "POST"){
   
    include "../../includes/connect.php";
    include "../../modules/event.php";
    $event = new event($conn);

     $response = $event->delete();
     return $response;

     
}



?>