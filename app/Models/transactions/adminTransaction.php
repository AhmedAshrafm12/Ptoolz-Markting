<?php

namespace App\Models\transactions;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class adminTransaction extends Model
{
    use HasFactory;
    public $table = 'admintransaction';
    public  $timestamps  =false;
    public $fillable =
    [
        'offer_id',
         'currency',
         'value',
         'type_id'
    ];
}
