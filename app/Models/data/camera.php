<?php

namespace App\Models\data;

use App\Models\order\cameracart;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class camera extends Model
{
    use HasFactory;
    public $table = 'cameralist';

    public function accessories(){
        return $this->belongsToMany(cameraaccessory::class , "cameraaccessory" ,"camera_id" , "accessory_id" );
    }
    public function cartaccessories(){
        return $this->hasMany(cameracart::class ,"parent_id");
    }

}
