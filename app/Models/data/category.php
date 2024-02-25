<?php

namespace App\Models\data;

use App\Models\order\order;
use App\Models\translation\categoryTranslation;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class category extends Model
{
    use HasFactory;
    public $table = 'category';
    public static function boot() {
	    parent::boot();
	    static::retrieved(function($item) {
            $item->department_icon = env('DEPARTMENTS_ICON_URL').$item->department_icon;
            $item->department_img= env('DEPARTMENTS_IMG_URL').$item->department_img;
            if(getRequestLanguage()!='en'){
            $translation = $item->translation()->where('language_code',getRequestLanguage())->first();
            if($translation){
                $item->department_name = $translation->department_name;
                $item->department_description= $translation->department_description;
            }

     } });
    }


    public function translation(){
        return $this->hasMany(categoryTranslation::class , 'master_id');
    }



    public function orders(){
        return $this->hasMany(order::class , 'category_id');
    }




}
