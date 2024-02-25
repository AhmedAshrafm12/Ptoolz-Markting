<?php

namespace App\Models\users\data;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class experiences extends Model
{
    use HasFactory;
    public $table  = 'work_experiences';
    public $timestamps = false;
    public static function boot() {

	    parent::boot();
        static::retrieved(function($item) {
            $item->certification = env('EXP_URL').$item->certification;
       } );

	}
    public $fillable =
    [
        "id", "company_name", "job_title", "description", "certification", "user_id", "created_at"
    ];
    public function store()
    {

        $validator = Validator::make(
            request()->all(),
            [
                "company_name" => 'required',
                "job_title" => 'required',
                "description" => 'required',
                "certification" => 'required',
            ],
            validationMessages()
        );
        if ($validator->fails())
            return apiresponse(false, 200, $validator->errors()->first());



        $certification = handleFile(env('file_VALID_EXTENSIONS'), 'certification');

        if (!$certification['valid'])
            return apiresponse(false, 200, env('file_VALID_EXTENSIONS'));
        $certification_path = $certification['fileName'];
        file_put_contents(public_path('../../assets/users/certifications/') . $certification['fileName'], $certification['content']);



        $certification = '';
        $user =  $this->create([
            "company_name" => request()->company_name,
            "job_title" => request()->job_title,
            "description" => request()->description,
            "certification" => $certification_path,
            "user_id" => Auth('sanctum')->user()->id,
        ]);
        return apiresponse(true, 200,"item successfully added");
    }
}
