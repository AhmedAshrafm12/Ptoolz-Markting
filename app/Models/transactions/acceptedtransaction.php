<?php

namespace App\Models\transactions;

use App\Models\offer\offer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class acceptedtransaction extends Model
{
    use HasFactory;
    public $table = 'acceptedtransaction';
    public  $timestamps  =false;

    public $fillable = [
        "id",
        "offer_id",
         "sp_percentage",
         "admin_percentage",
         "currency",
         "sp_share",
         "admin_share",
         "created_at"
    ];


    public function offer(){
        return $this->belongsTo(offer::class , 'offer_id');
    }

}
