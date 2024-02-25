<?php
namespace modules;

class clients{
 
    protected $conn;
    protected $table = 'clients';
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
        
        $name = filter_var($_POST['name'] , FILTER_SANITIZE_SPECIAL_CHARS);
        $address = filter_var($_POST['address'] , FILTER_SANITIZE_SPECIAL_CHARS);
        $phone= filter_var($_POST['phone'] , FILTER_SANITIZE_SPECIAL_CHARS);
        if(strlen($phone) != 11)
        $this->msg = 'phone number is must be 11';
        // elseif($this->is_exist("name",$name))
        // $this->msg = 'client name already exist';
        else{

        $upload = (upload_img("logo", '../../images/'));
        if (!$upload['success'])
            return ["success" => false, "msg" => $upload['msg']];
        else
            $logo = $upload['msg'];

        $stmt = $this->conn->prepare("INSERT INTO clients (name, phone, address , logo) VALUES (?,?,?,?)");
        
        $stmt->execute(array($name , $phone , $address , $logo));
        
        $this->success = 1;
        $this->msg ="added successfully";
        }
        
        return ["success"=>$this->success,"msg"=>$this->msg];

    }
    public function update()
    {
        $client = $this->get_by_id($_POST['id']);
        $name = filter_var($_POST['name'] , FILTER_SANITIZE_SPECIAL_CHARS);
        $address = filter_var($_POST['address'] , FILTER_SANITIZE_SPECIAL_CHARS);
        $phone= filter_var($_POST['phone'] , FILTER_SANITIZE_SPECIAL_CHARS);
        $logo = $client['logo'];


        if (isset($_FILES['logo'])) {
            $upload = (upload_img("logo", '../../images/'));
            if (!$upload['success']) {
                return ["success" => false, "msg" => $upload['msg']];
            } else {
                $logo = $upload['msg'];
            }
        }

        if(strlen($phone) > 11)
        $this->msg = 'phone number is must be 11';
        elseif($this->is_exist_except('name',$name,$_POST['id']) > 0)
        $this->msg = 'client name already exist';
        else{
        $stmt = $this->conn->prepare("UPDATE  $this->table  set  name = ? , phone = ?, address = ?, logo = ? where id = ?");
        
        $stmt->execute(array($name , $phone , $address , $logo , $_POST['id']));
        
        $this->success = 1;
        $this->msg ="updated sucessfully";
        }
        
        return ["success"=>$this->success,"msg"=>$this->msg];

    }

    public function delete()
    {
        $id =$_POST['id'] ?? 0;
        $stmt = $this->conn->prepare("DELETE FROM clients WHERE id = $id ");
        
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