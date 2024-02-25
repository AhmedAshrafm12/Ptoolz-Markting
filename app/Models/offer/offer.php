<?php

namespace App\Models\offer;

use App\Models\order\order;
use App\Models\order\update;
use App\Models\users\serviceProvider;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class offer extends Model
{
    use HasFactory;
    public  $timestamps  =false;

    public $fillable = [
        "id", "code", "description", "status", "duration", "currency", "offer_cost", "delivery_cost", "delay_cost", "after_fees", "after_discount", "delivery", "created_at", "user_id", "order_id"
    ];


    public function store(){

        $validator  = Validator::make(request()->all() ,[
            'description' => "required",
            'duration' => 'required',
            'offer_cost' => 'required',
            'after_fees' => 'required',
            'order_id' => 'required',
        ]);

    if($validator->fails())
    return apiResponse(false ,200 , $validator->errors()->first());
    $order = order::find(request()->order_id);

    $delayCost = request()->delay_cost ?   request()->delay_cost  : 0;
    $deliveryCost =request()->delivery_cost ?   request()->delivery_cost  : 0;
    $delivery = request()->delivery ?   request()->delivery  : 0;
    $afterDiscount = 0;

    $offer =   self::create([
        "code"=>uniqid(),
        "user_id"=>Auth("sanctum")->user()->id,
        "order_id"=>request()->order_id,
        "description"=>request()->description,
        "duration"=>request()->duration,
        "offer_cost"=>request()->offer_cost,
        "delivery_cost"=>$deliveryCost,
        "delay_cost"=>$delayCost,
        "after_fees"=>request()->after_fees,
        "after_discount"=>$afterDiscount,
        "delivery"=>$delivery,
        "status"=>1,
        'currency'=>$order->currency
       ]);

       $offer->code =  dechex($offer->id);
      $offer->save();

      $topic = set_user_topic($order->client_id);
    //    return $topic;
      $payload = set_payload(__('notifiactions.offer_received_title') ,__('notifiactions.offer_received_body') , 'offer_received' , $order->id , $order->user->user_key);
      send_to_topic($topic , $payload);
      return apiresponse(true , 200 , 'offer added' );
    }

    public function order(){
        return $this->belongsTo(order::class , 'order_id');
    }
    public function user(){
    return $this->belongsTo(serviceprovider::class , 'user_id');
}


public function updates(){
    return $this->hasMany(update::class , 'offer_id')->orderBy('id','desc');
}
}
