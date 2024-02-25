<?php

namespace App\Models\transactions;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class chargetransaction extends Model
{
    use HasFactory;
    public $table = 'chargetransaction';
    public  $timestamps  =false;

    public $fillable = [
        'user_id' ,
        'value',
            ]
;

protected $appends = ['title' , 'total_cost' , 'type'];
public function getTitleAttribute($value)
{
    $value =  getRequestLanguage() == 'ar' ? 'عملية شحن' : "charge";
   return $value;
}
public function getTotalCostAttribute($value)
{
    return round($this->value);;
}
public function getTypeAttribute($value)
{
    return 1;
}

}
