<?php

namespace App\Models\markting;

use App\Models\users\marketer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class marketerserviceprovider extends Model
{
    use HasFactory;
    public $table = 'marketerserviceprovider';
    public $timestamps = false;

   public $fillable = [
     "marketer_id", "sp_id"
   ];
    static function store($item , $sp_id){

        self::create(
            [
                "marketer_id"=>$item->id,
                 "sp_id"=>$sp_id ,
            ]
            );

    }

}
