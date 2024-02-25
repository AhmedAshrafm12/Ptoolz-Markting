<?php

namespace App\Models\data;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class paperBinding extends Model
{
    use HasFactory;
    public $table ='paperbinding';
    public static function boot() {
	    parent::boot();
	    static::retrieved(function($item) {
            $item->image = env('BINDING_IMG_URL').$item->image;
 });
    }
}
