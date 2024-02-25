<?php

namespace App\Models\data;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class cameraaccessory extends Model
{
    use HasFactory;
    public $table = 'accessorieslist';

    public function cameras(){
        return $this->belongsToMany(camera::class , "cameraaccessory" , "accessory_id" , "camera_id");
    }


}
