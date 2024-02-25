<?php

use modules\event;

error_reporting(1);
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    include "../../includes/connect.php";
    include  "../../includes/helper.php";

    include "../../modules/event.php";

    $event = new event($conn);

    $response = $event->store();
    return $response;

}
