<?php

namespace App\Models\data;

use App\Models\translation\cityTranslation;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class city extends Model
{
    use HasFactory;
    public $table = 'city';

    public static function boot() {
	    parent::boot();
	    static::retrieved(function($item) {
            if(getRequestLanguage()!='en'){
            $translation = $item->translation()->where('language_code',getRequestLanguage())->first();
            $item->city_name = $translation->city_name  ??  $item->city_name;
     } });
    }

    public function regions(){
        return $this->hasMany(region::class , 'city_id');
    }

    public function translation(){
        return $this->hasMany(cityTranslation::class , 'master_id');
    }

}
