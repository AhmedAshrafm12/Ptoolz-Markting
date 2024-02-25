<?php

namespace App\Models\order;

use App\Models\offer\offer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class update extends Model
{
    use HasFactory;
    public $fillable = ["id", "offer_id", "status", "file", "file_copy", "description" ,  "comment", "checked", "created_at"];
    public  $timestamps  =false;
    // public static function boot() {

	//     parent::boot();
    //     static::retrieved(function($item) {
    //         $item->file = env('UPDATES_STRORAGE_LINK').'/'.$item->file;
    //    } );

	// }
public function store(){

$validator = Validator::make(request()->all() ,
["file" => "required|mimes:".env('GLOBAL_VALID_EXTENSIONS')
, 'offer_id'=>'required' ,'description'=>'required']
,validationMessages());

// if($validator->fails())
// return apiresponse(false,200,$validator->errors()->first());

$file = upload_file("file" , 'updates' , 'order');

// if(!$file['valid'])
// return apiresponse(false,200,"select a valid file");


// $path =  Storage::disk('order')->put('/updates/'.$file['fileName'] , $file['content']);


$update = $this->create([
    "file"=>$file,
    "description"=>request()->description,
    'offer_id'=>request()->offer_id
 ]);

 $offer = offer::find(request()->offer_id);



 // notify customer

 $topic = set_user_topic($offer->order->client_id);
 $payload = set_payload(__('notifiactions.recieve_update_title'), __('notifiactions.recieve_update_body'), 'recieve_update',$offer->order->id, $offer->order->user->user_key);
 send_to_topic($topic, $payload);

 return apiresponse(true , 200 , "update added successfully");
}


public function decline(){
    $validator = Validator::make(request()->all() ,
    ["comment"=>'required']
    ,validationMessages());

    if($validator->fails())
    return apiresponse(false,200,$validator->errors()->first());

    $this->comment = request()->comment;
    $this->checked = 1;
    $this->status = 0;
    $this->save();


 // notify customer

 $topic = set_user_topic($this->offer->user_id);
 $payload = set_payload(__('notifiactions.decline_update_title'), __('notifiactions.decline_update_body'), 'decline_update',$this->offer->id, $this->offer->user->user_key);
 send_to_topic($topic, $payload);


    return apiresponse(true , 200 , 'success');

}


public function accept(){
    $this->status  = 1;
    $this->checked = 1;
    $this->save();
}



public function offer(){
    return $this->belongsTo(offer::class , 'offer_id');
}


protected $hidden = ['file_copy'];
}
