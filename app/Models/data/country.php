<?php

namespace App\Models\data;

use App\Models\translation\countryTranslation;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class country extends Model
{
    use HasFactory;
    public $table = 'country';


    public static function boot() {
	    parent::boot();
	    static::retrieved(function($item) {
            if(getRequestLanguage()!='en'){
            $translation = $item->translation()->where('language_code',getRequestLanguage())->first();
            $item->country_name = $translation->country_name  ??  $item->country_name;
            $item->currency_name = $translation->currency_name ??  $item->currency_name;
     } });
    }


   public function cities(){
    return $this->hasMany(city::class , 'country_id');
   }

   public function translation(){
    return $this->hasMany(countryTranslation::class , 'master_id');
}
}

