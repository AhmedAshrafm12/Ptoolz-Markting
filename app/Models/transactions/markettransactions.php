<?php

namespace App\Models\transactions;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class markettransactions extends Model
{
    use HasFactory;
    public $table ='markettransactions';
    public $timestamps = false ;

    protected $fillable  = [
        "sp_id", "marketer_id", "marketer_share", "leader_share", "created_at"
    ];

    public $appends = ['total_cost', 'type', 'title'];


    public function getTitleAttribute($value)
    {

        $value =  getRequestLanguage() == 'ar' ? 'عملية تحصيل' : "hand over";
       return $value;
    }

    public function getTotalCostAttribute($value)
    {
        if($this->marketer_id != auth_user()->id)
        return round($this->leader_share);

        return round($this->marketer_share);
    }
    public function getTypeAttribute($value)
    {
        return 1;
    }
}
