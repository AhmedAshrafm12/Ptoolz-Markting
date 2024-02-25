<?php

namespace modules;

class products
{

    protected $conn;
    protected $table = 'products';
    protected $msg, $success = 0;
    public function __construct($conn)
    {
        $this->conn = $conn;
    }



    public function index()
    {
        $stmt = $this->conn->prepare("select * from $this->table");
        $stmt->execute();
        return $stmt->fetchAll() ?? false;
    }




    public function store()
    {

        $name = filter_var($_POST['name'], FILTER_SANITIZE_SPECIAL_CHARS);
        $carton_dimensions = filter_var($_POST['carton_dimensions'], FILTER_SANITIZE_SPECIAL_CHARS);
        $pcs_per_carton = filter_var($_POST['pcs_per_carton'], FILTER_SANITIZE_SPECIAL_CHARS);
        $color = filter_var($_POST['color'], FILTER_SANITIZE_SPECIAL_CHARS);
        $material = filter_var($_POST['material'], FILTER_SANITIZE_SPECIAL_CHARS);
        $volume = filter_var($_POST['volume'], FILTER_SANITIZE_SPECIAL_CHARS);

        $upload = (upload_img("image", '../../images/'));
        if (!$upload['success'])
            return ["success" => false, "msg" => $upload['msg']];
        else
            $img = $upload['msg'];

        $stmt = $this->conn->prepare("INSERT INTO $this->table (name,  carton_dimensions, pcs_per_carton, color, material, image, volume ) VALUES (?,?,?,?,?,?,?)");

        $stmt->execute(array($name, $carton_dimensions, $pcs_per_carton, $color, $material, $img, $volume));

        $this->success = 1;
        $this->msg = "added sucessfully";


        return ["success" => $this->success, "msg" => $this->msg];
    }

    public function get_by_id($product_id  = false)
    {
        $id = $product_id ??  intval($_GET['id']);
        $stmt = $this->conn->prepare("select * from $this->table where id = ? LIMIT 1");
        $stmt->execute(array($id));
        return $stmt->fetch() ?? false;
    }


    public function update()
    {
        $product = $this->get_by_id($_POST['id']);
    
        $name = filter_var($_POST['name'], FILTER_SANITIZE_SPECIAL_CHARS);
        $carton_dimensions = filter_var($_POST['carton_dimensions'], FILTER_SANITIZE_SPECIAL_CHARS);
        $pcs_per_carton = filter_var($_POST['pcs_per_carton'], FILTER_SANITIZE_SPECIAL_CHARS);
        $color = filter_var($_POST['color'], FILTER_SANITIZE_SPECIAL_CHARS);
        $material = filter_var($_POST['material'], FILTER_SANITIZE_SPECIAL_CHARS);
        $volume = filter_var($_POST['volume'], FILTER_SANITIZE_SPECIAL_CHARS);
        $img = $product['image'];


        if (isset($_FILES['image'])) {
            $upload = (upload_img("image", '../../images/'));
            if (!$upload['success']) {
                return ["success" => false, "msg" => $upload['msg']];
            } else {
                $img = $upload['msg'];
            }
        }

        $stmt = $this->conn->prepare("UPDATE  products  set  name = ?,  carton_dimensions = ?, pcs_per_carton = ?, color = ?, material = ?, image = ?, volume = ? where id = ?");

         $stmt->execute(array($name, $carton_dimensions, $pcs_per_carton, $color, $material, $img, $volume, $_POST['id']));

        $this->success = 1;
        $this->msg = "updated sucessfully";



        return ["success" => $this->success, "msg" => $this->msg];
    }
    public function delete()
    {
        $id =  $_POST['id'];
        $stmt = $this->conn->prepare("DELETE FROM $this->table WHERE id =$id");

        $stmt->execute();

        $this->success = 1;
        $this->msg = "deleted sucessfully";


        return ["success" => $this->success, "msg" => $this->msg];
    }



    public function is_exist($column, $value)
    {
        $stmt = $this->conn->prepare("select * from $this->table where $column = $value");
        $stmt->execute();
        return $stmt->rowCount();
    }
    public function is_exist_except($column, $value, $id)
    {
        $stmt = $this->conn->prepare("select * from $this->table where $column = $value and id != $id");
        $stmt->execute();
        return $stmt->rowCount();
    }
}
