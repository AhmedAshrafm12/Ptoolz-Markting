<?php

namespace App\Models\transactions;

use App\Models\offer\offer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class holdtransaction extends Model
{
    use HasFactory;
    public $table = 'holdtransaction';
    public  $timestamps  =false;

    public $fillable=[
        "id",
         "offer_id",
         "currency",
         "value",
         "holded",
         "created_at"
    ];


    public function offer(){
        return $this->belongsTo(offer::class , 'offer_id');
    }
}
