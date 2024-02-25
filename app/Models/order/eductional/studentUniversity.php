<?php

namespace App\Models\order\eductional;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class studentUniversity extends Model
{
    use HasFactory;
    public $table  ='studentuniversity';
    public  $timestamps  = false;
    protected $appends = ['university'];
    public $fillable = ["id", "university_id", "university_code", "created_at"];
    public function university(){
        return $this->belongsTo(university::class , 'university_id');
    }



     public function getUniversityAttribute($value){
        return university::find($this->university_id)->name;
      }

}
