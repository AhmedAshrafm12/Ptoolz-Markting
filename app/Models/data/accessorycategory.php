<?php

namespace App\Models\data;

use App\Models\translation\accessorycategorytranslation;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class accessorycategory extends Model
{
    use HasFactory;
    public $table = 'accessorycategory';



    public static function boot() {
	    parent::boot();
	    static::retrieved(function($item) {
            if(getRequestLanguage()!='en'){
            $translation = $item->translation()->where('language_code',getRequestLanguage())->first();
            if($translation)
            $item->name = $translation->name;
     } });
    }


    public function translation(){
        return $this->hasMany(accessorycategorytranslation::class , 'master_id');
    }

    public function accessories(){
        return $this->hasMany(cameraaccessory::class , 'category_id');
    }

}
