<?php

namespace App\Models\order\eductional;

use App\Models\data\departments;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class instructorUniversity extends Model
{
    use HasFactory;
    public $table  ='instructoruniversity';
    public  $timestamps  = false;
    protected $appends = ['university' , 'college' , 'department'];
    public $fillable = ["id", "university_id", "college_id", "department_id", "user_id", "created_at"];
    public function university(){
        return $this->belongsTo(university::class , 'university_id');
    }
    public function college(){
        return $this->belongsTo(university::class , 'college_id');
    }
    public function department(){
        return $this->belongsTo(university::class , 'department_id');
    }


     public function getUniversityAttribute($value){
        return university::find($this->university_id)->name;
      }
     public function getCollegeAttribute($value){
        return college::find($this->university_id)->name;
      }
     public function getDepartmentAttribute($value){
        return departments::find($this->university_id)->name;
      }
}
