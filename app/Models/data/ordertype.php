<?php

namespace App\Models\data;

use App\Models\translation\ordertypetranslation;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ordertype extends Model
{
    use HasFactory;
    public $table = 'ordertype';

    public static function boot() {
	    parent::boot();
	    static::retrieved(function($item) {
            if(getRequestLanguage()!='en'){
            $translation = $item->translation()->where('language_code',getRequestLanguage())->first();
            $item->name = $translation->name;
     } });
    }


    public function translation(){
        return $this->hasMany(ordertypetranslation::class , 'master_id');
    }
}
