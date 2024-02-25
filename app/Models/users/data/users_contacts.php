<?php

namespace App\Models\users\data;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;

class users_contacts extends Model
{
    use HasFactory;
    protected $table = 'users_contacts';
    public $timestamps = false;
    protected $fillable =[
        "full_name", "email", "mobile", "message", "created_at"
    ];


    public function store(){

        $validator =  Validator::make(request()->all() ,
        [
            "full_name"=>'required',
             "email"=>'required|email',
             "mobile"=>'required',
             "message"=>'required'
        ]);


        if ($validator->fails())
        return apiresponse(false, 200, $validator->errors()->first());

        self::create(
            [
                "full_name"=>request()->full_name,
                "email"=>request()->email,
                "mobile"=>request()->mobile,
                "message"=>request()->message
            ]
            );

           return apiresponse(true , 200 , 'success');

    }


}
