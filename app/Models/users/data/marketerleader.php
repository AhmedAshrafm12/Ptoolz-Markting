<?php

namespace App\Models\users\data;

use App\Models\users\marketer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class marketerleader extends Model
{
    use HasFactory;
    public $table = 'marketerleader';
    public $timestamps = false;

   public $fillable = [
     "marketer_id", "leader_id"
   ];
    static function store($item){
        $leader = marketer::where('join_code' , request()->join_code)->first();

        self::create(
            [
                "marketer_id"=>$item->id,
                 "leader_id"=>$leader->id
            ]
            );

    }
}
