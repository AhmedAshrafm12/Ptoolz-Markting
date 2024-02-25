<?php

namespace App\Models\order\eductional;

use App\Models\data\departments;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class college extends Model
{
    use HasFactory;
    public $table = 'college';
  public function departments(){
    return $this->hasMany(departments::class);
  }

}
