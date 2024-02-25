<?php

namespace App\Models\users\data;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class portfolio extends Model
{
    use HasFactory;
    public $table = 'portfolio';
    public $timestamps = false;
    public $fillable = ["id", "title", "file", "created_at" , 'user_id'];
    public static function boot() {

	    parent::boot();
        static::retrieved(function($item) {
            $item->file = env('PORTOFOLIO_URL').$item->file;
       } );

	}
   public function store(){

        $validator = Validator::make(request()->all(),
            [
                "title"=>'required',
                 "file"=>'required'
            ],
            validationMessages()
            );
            if($validator->fails())
            return apiresponse(false,200,$validator->errors()->first());


            $portfolio = handleFile(env('IMAGE_VALID_EXTENSIONS'), 'file');

            if (!$portfolio['valid'])
                return apiresponse(false, 200, env("IMAGE_VALID_EXTENSIONS"));
            $portfolio_path = $portfolio['fileName'];
            file_put_contents(public_path('../../assets/users/portfolio/') . $portfolio['fileName'], $portfolio['content']);
        $user =  $this->create([
            "title"=>request()->title,
            "file"=>$portfolio_path,
            "user_id"=>Auth('sanctum')->user()->id,
           ]);
           return apiresponse(true, 200,"item added");}
}
