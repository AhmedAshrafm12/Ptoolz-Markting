<?php

namespace App\Models\order\eductional;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class client_reference extends Model
{
    use HasFactory;
    public $table = 'client_reference';
    public  $timestamps  = false;
    public $fillable = ["id", "reference_id", "client_id", "at"];

    public function reference(){
        return $this->belongsTo(reference::class , 'reference_id');
    }
}
