<?php

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use modules\products;

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    include "../../includes/init.php";
    include "../../modules/products.php";

    
        $product = new products($conn);
        response(true, 200, "success", $product->search($_POST['value']));
   
}
