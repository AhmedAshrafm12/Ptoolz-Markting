<?php
use modules\products;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

if($_SERVER["REQUEST_METHOD"] == "GET"){
    include "../../includes/init.php";
    include "../../modules/products.php";
    
        $product = new products($conn);
        response(true, 200, "success",$product->get_by_id());
   
     
}



?>