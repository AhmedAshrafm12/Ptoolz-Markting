<?php

namespace App\Models\transactions;

use App\Models\offer\offer;
use App\Models\order\eductional\client_reference;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class handovertransaction extends Model
{
    use HasFactory;
    public $table ='handovertransaction';
        public  $timestamps  =false;

    public $fillable=[
        "id"
        , "offer_id"
        , "sp_percentage"
        , "admin_percentage"
        , "currency_share"
        , "sp_share"
        , "admin_share"
        , "hold_value"
        , "created_at"
        ,"purchase_id"
    ];

    public function offer(){
        return $this->belongsTo(offer::class , 'offer_id');
    }

    public function purchase(){
        return $this->belongsTo(client_reference::class , 'purchase_id');
    }
}
