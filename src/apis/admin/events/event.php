<?php
use modules\event;
if($_SERVER["REQUEST_METHOD"] == "GET"){
   
    include "../../includes/connect.php";
    include "../../modules/event.php";
    $event = new event($conn);
    response(true,200,"success",$event->get_by_id());

     
}



?>