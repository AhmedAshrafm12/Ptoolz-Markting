<?php

namespace App\Models\order;

use App\Models\data\city;
use App\Models\data\country;
use App\Models\data\region;
use App\Models\users\customer;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;

class orderDelivery extends Model
{
    use HasFactory;
    public $table  = 'orderdelivery';
    public  $timestamps  =false;
    public $fillable = [
        "id", "country", "city", "region","address", "latitude", "longitude", "order_id", "sp_received", "customer_received", "status", "receiver_mobile", "receiver_name", "qr_code","from_location"
    ];



    static function store($item){

        $user = customer::find($item->client_id) ;
        $receiver_mobile = isset(request()->receiver_mobile) ? request()->receiver_mobile : $user->mobile;
        $receiver_name = isset(request()->receiver_name) ? request()->receiver_name : $user->full_name;

        $orderDelivery =  self::create([
            "country"=>request()->country,
             "city"=>request()->city,
             "region"=>request()->region,
             "latitude"=>request()->latitude,
             "longitude"=>request()->longitude,
             "address"=>request()->address ?? '',
             "order_id"=>$item->id,
             "receiver_mobile"=>$receiver_mobile,
             "receiver_name"=>$receiver_name,
             "qr_code"=>uniqid('#'.$item->id),
             'from_location'=>customer::find(Auth('sanctum')->user()->id)->Location->id
            ]);

        }


        static function edit(order $order){

            if(request()->delivery == 1){
               if($order->delivery){
                $order->delivery->update([
                    "country" => request()->country,
                    "city" => request()->city,
                    "region" => request()->region,
                    "latitude" => request()->latitude,
                    "longitude" => request()->longitude,
                    "address" => request()->address ?? '',
                    "receiver_mobile" => request()->receiver_mobile,
                    "receiver_name" => request()->receiver_name,
                ]);
               }
               else{
                self::create([
                    "country" => request()->country,
                    "city" => request()->city,
                    "region" => request()->region,
                    "latitude" => request()->latitude,
                    "longitude" => request()->longitude,
                    "address" => request()->address ?? '',
                    "receiver_mobile" => request()->receiver_mobile,
                    "receiver_name" => request()->receiver_name,
                    "qr_code"=>uniqid('#'.$order->id),
                    'from_location'=>customer::find(Auth('sanctum')->user()->id)->Location->id,
                    "order_id"=>$order->id,
                ]);
               }

            }
            else
            orderDelivery::where('order_id' , $order->id)->delete();




        }


public function get(){
$this->country = country::find($this->country) ?? null;
$this->city = city::find($this->city) ?? null;
$this->region = region::find($this->region)?? null;
return $this;

}


public function order(){

     return $this->belongsTo(order::class , 'order_id');

}


public function country(){
    return $this->belongsTo(country::class , 'country');
 }
 public function city(){
    return $this->belongsTo(city::class , 'city');
 }
 public function region(){
    return $this->belongsTo(region::class , 'region');
 }


}
