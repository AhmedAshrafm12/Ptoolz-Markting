<?php

namespace App\Http\Controllers\registration;

use IDAnalyzer\CoreAPI;
use App\Models\offer\offer;
use App\Models\order\update;
use Illuminate\Http\Request;
use App\Models\data\category;
use App\Models\order\cameracart;
use App\Models\order\orderDelivery;
use App\Http\Controllers\Controller;
use App\Http\Controllers\coreApi as ControllersCoreApi;
use App\Models\data\accessorycategory;
use App\Models\order\photogrphyUpdates;
use App\Models\transactions\holdtransaction;
use App\Models\transactions\acceptedtransaction;
use App\Models\transactions\handovertransaction;
use App\Models\transactions\spaccounttransaction;
use App\Models\transactions\clientaccounttransaction;

class dataController extends Controller
{

  public function catgeory(){
    $categories = category::orderBy('arrange' , 'asc')->get();
   $categories_out = [];
   foreach($categories as $category)
   {
    if($category->id != 6)
    $categories_out[]=$category;
   }
    return apiresponse(true,200,'success',$categories_out);
  }




  public function clean_forign(){

    $frontImage = upload_file('frontImage' , 'frontImages'  , 'order');


    $backImage = upload_file('backImage' , 'backImages'  , 'order');



    $url = 'https://ptoolzapiV2.ptoolz.com/ptoolz/storage/order/';
    $coreapi = new ControllersCoreApi("u3pcbrZ3LCbNRCEXvUIZHOy4YpreN03w", "US");
    $coreapi->enableAuthentication(true, 'quick');
    $result = $coreapi->scan ($url . 'frontImages'. '/' . $frontImage,'',$url . 'backImages'. '/' . $backImage,);

    $data_result = $result['result'];
    $authentication_result = $result['authentication'];
    $face_result = $result['face'];

    // Print result
    echo("Hello your name is {$data_result['firstName']} {$data_result['lastName']}<br>");

    // Parse document authentication results
    if($authentication_result){
        if($authentication_result['score'] > 0.5) {
            echo("The document uploaded is authentic<br>");
        }else if($authentication_result['score'] > 0.3){
            echo("The document uploaded looks little bit suspicious<br>");
        }else{
            echo("The document uploaded is fake<br>");
        }
    }
    // Parse face verification results
    if($face_result){
        if($face_result['error']){
            // View complete error codes under API reference: https://developer.idanalyzer.com/coreapi.html
            echo("Face verification failed! Code: {$face_result['error']}, Reason: {$face_result['error_message']}<br>");
        }else{
            if($face_result['isIdentical'] === true){
                echo("Great! Your photo looks identical to the photo on document<br>");
            }else{
                echo("Oh no! Your photo looks different to the photo on document<br>");
            }
            echo("Similarity score: {$face_result['confidence']}<br>");
        }
    }

  }



public function verfyIdentity()
{


}

}
