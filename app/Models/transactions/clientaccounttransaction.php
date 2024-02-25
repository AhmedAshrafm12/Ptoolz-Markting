<?php

namespace App\Models\transactions;

use App\Models\offer\offer;
use App\Models\order\eductional\client_reference;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class clientaccounttransaction extends Model
{
    use HasFactory;
    public $table = 'clientaccounttransaction';
    public  $timestamps  = false;

    public $fillable = [
        "id", "offer_id", "user_id", "currency", "value", "type_id", "purchase_id", "Created_at"
    ];

    public $appends = ['total_cost', 'type', 'title'];

    public function offer()
    {
        return $this->belongsTo(offer::class, 'offer_id');
    }

    public function purchase()
    {
        return $this->belongsTo(client_reference::class, 'purchase_id');
    }


    public function getTotalCostAttribute($value)
    {
        return round($this->value);
    }
    public function getTypeAttribute($value)
    {
        if($this->type_id ==  1 || $this->type_id  ==  2)
        return 0;
        else
        return 1;
    }

    public function getTitleAttribute($value)
    {
        if ($this->type_id == 1)
            $value =  getRequestLanguage() == 'ar' ? 'دفع  طلب' : "hand over";
        elseif($this->type_id == 1)
        $value =  getRequestLanguage() == 'ar' ? 'مبلغ معلق' : "holded value";
        else
            $value =  getRequestLanguage() == 'ar' ? ' تكلفه معلقة' : "hand over";
        return $value;
    }
}
