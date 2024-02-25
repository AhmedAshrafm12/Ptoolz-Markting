<?php

namespace App\Models\order\eductional;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class university extends Model
{
    use HasFactory;
    public $table = 'university';

    public function colleges(){
        return $this->hasMany(college::class);
    }
    public function instructors_references(){
    return $this->hasMany(reference::class)->where('instructor_id' , Auth('sanctum')->user()->id);
    }

    public function references(){
        return $this->hasMany(reference::class);
    }

    // public function instructors(){
    //     $refernces =  $this->references();
    //     $instructors = [];
    //     foreach($refernces as $refernce){
    //         array_push($instructors,$refernce);
    //     }
    //     return $refernces;
    // }
}
