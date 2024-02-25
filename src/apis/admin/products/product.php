<?php
use modules\products;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

if($_SERVER["REQUEST_METHOD"] == "GET"){
    include "../../includes/init.php";
    include "../../modules/products.php";
     try {
        JWT::decode(getToken(), new Key(seckey(), "HS256"));
        $product = new products($conn);
        response(true, 200, "success",$product->get_by_id());
    } catch (Exception $e) {
        response(false, 401, $e->getMessage());
    }
     
}



?>