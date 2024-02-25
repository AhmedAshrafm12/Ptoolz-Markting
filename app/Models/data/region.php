<?php

namespace App\Models\data;

use App\Models\translation\regionTranslation;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class region extends Model
{
    use HasFactory;
    public $table = 'region';
    public static function boot() {
	    parent::boot();
	    static::retrieved(function($item) {
            if(getRequestLanguage()!='en'){
            $translation = $item->translation()->where('language_code',getRequestLanguage())->first();
            $item->region_name = $translation->region_name  ??  $item->region_name;
     } });
    }


    public function translation(){
        return $this->hasMany(regionTranslation::class , 'master_id');
    }

}
