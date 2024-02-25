<?php
namespace modules;

class event{
 
    protected $conn;
    protected $table = 'events';
    protected $msg , $success = 0;
    public function __construct($conn){
        $this->conn = $conn;
    }



    public function index()
    {
        $stmt = $this->conn->prepare("select * from $this->table");
        $stmt->execute();
        return $stmt->fetchAll() ?? false;

    }
    public function get_by_id($client_id = false)
    {  
        $id = $client_id  ??  intval($_GET['id']);

        $stmt = $this->conn->prepare("select * from $this->table where id = ? LIMIT 1");
        $stmt->execute(array($id));
        return $stmt->fetch() ?? false;

    }




    public function store()
    {
        
        $title = filter_var($_POST['name'] , FILTER_SANITIZE_SPECIAL_CHARS);
        $description = filter_var($_POST['description'] , FILTER_SANITIZE_SPECIAL_CHARS);
        if(strlen($description) < 20)
        $this->msg = 'description must be  at least 20';
        // elseif($this->is_exist("name",$name))
        // $this->msg = 'client name already exist';
        else{

        $upload = (upload_img("image", '../../images/'));
        if (!$upload['success'])
            return ["success" => false, "msg" => $upload['msg']];
        else
            $image = $upload['msg'];

        $stmt = $this->conn->prepare("INSERT INTO events (title, description, image) VALUES (?,?,?)");
        
        $stmt->execute(array($title , $description  , $image));
        
        $this->success = 1;
        $this->msg ="added successfully";
        }
        
        return ["success"=>$this->success,"msg"=>$this->msg];

    }
    public function update()
    {
        $event = $this->get_by_id($_POST['id']);
        $title = filter_var($_POST['name'] , FILTER_SANITIZE_SPECIAL_CHARS);
        $description = filter_var($_POST['description'] , FILTER_SANITIZE_SPECIAL_CHARS);
        $image = $event['image'];


        if (isset($_FILES['image'])) {
            $upload = (upload_img("logo", '../../images/'));
            if (!$upload['success']) {
                return ["success" => false, "msg" => $upload['msg']];
            } else {
                $image = $upload['msg'];
            }
        }

        if(strlen($description) < 20)
        $this->msg = 'description must be  at least 20';
        else{
        $stmt = $this->conn->prepare("UPDATE  $this->table  set title =?, description = ?, image = ?  where id = ?");
        
        $stmt->execute(array($title , $description , $image , $_POST['id']));
        
        $this->success = 1;
        $this->msg ="updated sucessfully";
        }
        
        return ["success"=>$this->success,"msg"=>$this->msg];

    }

    public function delete()
    {
        $id =$_POST['id'] ?? 0;
        $stmt = $this->conn->prepare("DELETE FROM $this->table WHERE id = $id ");
        
        $stmt->execute();
        
        $this->success = 1;
        $this->msg ="deleted sucessfully";
        
        
        return ["success"=>$this->success,"msg"=>$this->msg];

    }

    

    // public function is_exist($column , $value){
    //     $stmt = $this->conn->prepare("select * from $this->table where name = ?");
    //     $stmt->execute(array($value));
    //     return $stmt->fetchAll();

    // }
    public function is_exist_except($column , $value , $id){
        $stmt = $this->conn->prepare("select * from $this->table where $column = ? and id != ?");
        $stmt->execute(array($value , $id));
        return $stmt->rowCount();

    }
 



}