<?php

namespace App\Models\transactions;

use App\Models\offer\offer;
use App\Models\order\eductional\client_reference;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class spaccounttransaction extends Model
{
    use HasFactory;
    public $table = 'spaccounttransaction';
    public  $timestamps  =false;
    public $fillable = [
        "id", "offer_id", "purchase_id" ,"user_id", "currency", "value", "type_id", "created_at"
    ] ;

    public $appends = ['total_cost', 'type' , 'title'];

    public function offer()
    {
        return $this->belongsTo(offer::class, 'offer_id');
    }

    public function purchase()
    {
        return $this->belongsTo(client_reference::class, 'purchase_id');
    }


    public function getTitleAttribute($value)
    {
        if($this->type_id == 1)
        $value =  getRequestLanguage() == 'ar' ? 'عملية تحصيل' : "hand over";
        else
        $value =  getRequestLanguage() == 'ar' ? 'عملية تحصيل' : "hand over";
       return $value;
    }

    public function getTotalCostAttribute($value)
    {
        return round($this->value);
    }
    public function getTypeAttribute($value)
    {
        if($this->type_id == 1)
        return 1;
        else
        return 0;
    }
}
