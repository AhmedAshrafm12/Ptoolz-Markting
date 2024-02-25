<?php

namespace App\Http\Controllers\customer;

use App\Models\data\city;
use App\Models\data\country;
use Illuminate\Http\Request;
use App\Models\data\paperSize;
use App\Models\data\paperType;
use App\Models\data\pictureSize;
use App\Models\data\paperBinding;
use App\Models\data\printingSide;
use App\Models\data\printingColor;
use App\Http\Controllers\Controller;
use App\Models\data\accessorycategory;
use App\Models\data\camera;
use App\Models\data\occasion;

class orderDetailsController extends Controller
{

    public function papersize(){
        return apiresponse(true , 200 , 'success' , paperSize::all());
    }
     public function papertype(){
        if(isset(request()->for_size))
        return apiresponse(true , 200 , 'success' , paperType::where('forSize' , request()->for_size)->get());

        return apiresponse(true , 200 , 'success' , paperType::all());
    }
    public function paperBinding(paperSize $paperSize){
        return apiresponse(true , 200 , 'success' , $paperSize->paperBinding);
    }
    public function printingColor(){
        return apiresponse(true , 200 , 'success' , printingColor::all());
    }
    public function printingSide(){
        return apiresponse(true , 200 , 'success' , printingSide::all());
    }
    public function picturesize(){
        return apiresponse(true , 200 , 'success' , pictureSize::all());
    }
    public function country(){
        return apiresponse(true , 200 , 'success' , country::all());
    }
    public function city(country $country){

        return apiresponse(true , 200 , 'success' , $country->cities);
    }
    public function region(city $city){
        return apiresponse(true , 200 , 'success' , $city->regions);
    }


    public function camera_accessories(camera $camera){
        $accessorycategories  = accessorycategory::all();
        $data = [];
        $accessoyList = [];

        foreach($accessorycategories as $element)
        {
        $category = accessorycategory::find($element->id);
        $accessoyList = [];
           foreach($element->accessories as $accessory){
           if($camera->accessories->contains($accessory)){
             array_push($accessoyList , $accessory);
        }

           }
           $category->accessories =  $accessoyList;
        array_push($data , $category);
        }
        return apiresponse(true,200,'success',$data);
 }

 public function camera(){
    return apiresponse(true , 200 , 'success' , camera::all());

 }
 public function occassion(){
    return apiresponse(true , 200 , 'success' , occasion::orderBy('id','DESC')->get());

 }

}
