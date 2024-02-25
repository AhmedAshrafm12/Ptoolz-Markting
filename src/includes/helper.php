<?php
function upload_img($name, $path)
{
   // $errors = array();
   $file_size = $_FILES[$name]['size'];
   $file_tmp = $_FILES[$name]['tmp_name'];
   $file_type = $_FILES[$name]['type'];
   $file_ext = pathinfo($_FILES["$name"]['name'], PATHINFO_EXTENSION)
;
   $file_name = time().'.'.$file_ext;
   $msg = '';
   $extensions = array("jpeg", "jpg", "png");
    $success = true;
   if (in_array($file_ext, $extensions) === false) {
      $msg = "extension not allowed, please choose a JPEG or PNG file.";
      $success = false;
    
   }

   // if($file_size > 2097152){
   //    $errors[]='File size must be excately 2 MB';
   // }

   if ($success) {

      if (move_uploaded_file($file_tmp, $path . $file_name)) {
       
          $msg = $file_name;
        
      } else {
         $msg ="uploading error occured";
         $success = false;
      }
   } 
   return ["success"=>$success , "msg"=>$msg];

}
