<?php

namespace App\Models\order;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class photogrphyUpdates extends Model
{
    use HasFactory;
    public $table  = 'photogrphy_updates';
    public  $timestamps  =false;
    public $fillable = [
        "id", "sp_arrival","sp_arrival_at" , "in_way" , "in_way_at" ,  "qr_code", "order_id" , 'sp_arrival_at'
    ];



    static function store($item){

        $updates =  self::create([
             "order_id"=>$item->id,
             "qr_code"=>uniqid('#'.$item->id),
            ]);

        }

        public function order()
        {
            return $this->belongsTo(order::class, 'order_id');
        }




// public function get(){
// $this->country = country::find($this->country) ?? null;
// $this->city = city::find($this->city) ?? null;
// $this->region = region::find($this->region)?? null;
// return $this;

// }


}
