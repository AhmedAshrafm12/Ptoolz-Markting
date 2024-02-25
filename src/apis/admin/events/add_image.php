<?php

use modules\event;

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    include "../../includes/init.php";

    include "../../modules/event.php";

    $event = new event($conn);

    $event->store_image();
   

}
