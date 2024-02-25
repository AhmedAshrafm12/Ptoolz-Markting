<?php

namespace App\Models\order\eductional;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class academicpermission extends Model
{
    use HasFactory;
    public $table = 'academicpermission';
    public  $timestamps  =false;

    public $fillable =
    [
        'user_id' ,
        'checked' ,
        'status'
    ];
}
