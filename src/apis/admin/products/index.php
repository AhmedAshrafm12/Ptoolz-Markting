<?php

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use modules\products;

if ($_SERVER["REQUEST_METHOD"] == "GET") {

    include "../../includes/init.php";
    include "../../modules/products.php";

    try {
        JWT::decode(getToken(), new Key(seckey(), "HS256"));
        $product = new products($conn);
        response(true, 200, "success", $product->index());
    } catch (Exception $e) {
        response(true, 401, $e->getMessage());
    }
}
