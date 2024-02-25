<?php
namespace modules;

class event{
 
    protected $conn;
    protected $table = 'events';
    protected $event_gallary = 'gallary';
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
    public function get_by_id($event = null)
    {  
        $id = $event  ??  intval($_GET['id']);

        $stmt = $this->conn->prepare("select * from $this->table where id = ? LIMIT 1");
        $stmt->execute(array($id));
        $event = $stmt->fetch() ?? false;
        if($event){
            $stmt = $this->conn->prepare("select * from $this->event_gallary where event_id = ? ");
            $stmt->execute(array($id));
            $gallary = $stmt->fetchAll();
            $event['gallary'] = $gallary ?? [];
        }
       
        return $event;


    }




    public function store()
    {
        
        $title = filter_var($_POST['title'] , FILTER_SANITIZE_SPECIAL_CHARS);
        $description = filter_var($_POST['description'] , FILTER_SANITIZE_SPECIAL_CHARS);
        $at = $_POST['at'];
        if(strlen($description) < 20)
        return  response(false, 200,  'description must be  at least 20');
        // elseif($this->is_exist("name",$name))
        // $this->msg = 'client name already exist';
        else{

        $upload = (upload_img("image", '../../images/events/'));
        if (!$upload['success'])
          return response(false, 200,  $upload['msg']);
        else
            $image = $upload['msg'];

        $stmt = $this->conn->prepare("INSERT INTO events (title, description, image , at) VALUES (?,?,?,?)");
        
        $stmt->execute(array($title , $description  , $image , $at));
        
        $this->success = 1;
        $this->msg ="added successfully";
        }
        
        return  response($this->success, 200, $this->msg);

    }
    public function update()
    {
   $event = $this->get_by_id($_POST['id']);
        $title = filter_var($_POST['name'] , FILTER_SANITIZE_SPECIAL_CHARS);
        $at = $_POST['at'] ;
        $description = filter_var($_POST['description'] , FILTER_SANITIZE_SPECIAL_CHARS);
        $image = $event['image'];


        if (isset($_FILES['image'])) {
            $upload = (upload_img("image", '../../images/events/'));
            if (!$upload['success']) {
                return response(false, 200,  $upload['msg']);
            } else {
                $image = $upload['msg'];
            }
        }

        if(strlen($description) < 20)
        return  response(false, 200,  'description must be  at least 20');
        else{
        $stmt = $this->conn->prepare("UPDATE  $this->table  set title =?, description = ?, image = ? , at=?  where id = ?");
        
        $stmt->execute(array($title , $description , $image ,$at, $_POST['id']));
        
        $this->success = 1;
        $this->msg ="updated sucessfully";
        }
        
        return  response($this->success, 200, $this->msg);

    }

 

    public function delete()
    {
        $id =$_POST['id'] ?? 0;
        $stmt = $this->conn->prepare("DELETE FROM $this->table WHERE id = $id ");
        
        $stmt->execute();
        
        $this->success = 1;
        $this->msg ="deleted sucessfully";
        
        
        return  response($this->success, 200, $this->msg);

    }

    
    public function store_image(){
        $event_id = $_POST['id'] ?? 0;
        $upload = (upload_img("image", '../../images/events/'));
        if (!$upload['success'])
          return response(false, 200,  $upload['msg']);
        else
        {
        $image = $upload['msg'];
        $stmt = $this->conn->prepare("INSERT INTO $this->event_gallary (image,event_id) VALUES (?,?)");
        $stmt->execute(array($image,$event_id));
        return response("success", 200, "uploaded successfully");

        }

    }
    

    public function is_exist($column , $value){
        $stmt = $this->conn->prepare("select * from $this->table where $column = ?");
        $stmt->execute(array($value));
        return $stmt->rowCount();

    }
    public function is_exist_except($column , $value , $id){
        $stmt = $this->conn->prepare("select * from $this->table where $column = ? and id != ?");
        $stmt->execute(array($value , $id));
        return $stmt->rowCount();

    }
 



}