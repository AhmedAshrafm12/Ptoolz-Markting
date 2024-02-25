<?php

namespace App\Models\users\data;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class notification extends Model
{
    use HasFactory;
    public $timestamps = false;
    public $fillable = ["id", "title", "body", "data", "type", "user_key", "seen", "created_at"];


    static function store($payload_data){
        self::create(
            [
             "title"=>$payload_data['title'],
             "body"=>$payload_data['body'],
             "data"=>$payload_data['data'],
             "type"=>$payload_data['type'],
             "user_key"=>$payload_data['user_key'],
           ]
            );
    }
}
