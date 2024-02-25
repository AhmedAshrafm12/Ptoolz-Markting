<?php

namespace App\Models\data;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class paperSize extends Model
{
    use HasFactory;
    public $table = 'papersize';
    public function paperBinding(){
   return $this->hasMany(paperBinding::class , 'forSize');
    }
}
