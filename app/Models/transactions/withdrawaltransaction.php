<?php

namespace App\Models\transactions;

use App\Models\users\serviceProvider;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;

class withdrawaltransaction extends Model
{
    use HasFactory;
    public $table = 'withdrawaltransaction';
    public  $timestamps  = false;
    public $fillable = [
        "id",
        "user_id",
        "account_type_id",
        "currency",
        "value",
        "status",
        "comment",
        "checked",
        "full_name", "account_number", "iban", "bank_name", "created_at"
    ];


    protected $appends = ['title', 'total_cost', 'type'];
    public function getTitleAttribute($value)
    {
        $value =  getRequestLanguage() == 'ar' ? 'عملية سحب' : "withdrawl";
        return $value;
    }
    public function getTotalCostAttribute($value)
    {
        return $this->value;;
    }
    public function getTypeAttribute($value)
    {
        return 0;
    }


    public function store()
    {

        $validator = Validator::make(
            request()->all(),
            [
                "value" => 'required',
                "full_name" => "required",
                "account_number" => "required",
                "iban" => "required",
                "bank_name" => "required",
            ],
            validationMessages()
        );

        if ($validator->fails())
            return apiresponse(false, 200, $validator->errors()->first());

        self::create(
            [
                "user_id" => Auth("sanctum")->user()->id,
                "account_type_id" => Auth("sanctum")->user()->account_type_id,
                "value" => request()->value,
                "full_name" => request()->full_name,
                "account_number" => request()->account_number,
                "iban" => request()->iban,
                "bank_name" => request()->bank_name,
            ]
        );

        // $user = serviceProvider::find(Auth("sanctum")->user()->id);
        // $topic = set_user_topic($user->id);
        // $payload = set_payload(__('notifiactions.balance_withdrawl_title'), __('notifiactions.balance_withdrawl_body'), 'balance_withdrawl', '', $user->user_key);
        // send_to_topic($topic, $payload);


        return apiresponse(true, 200, 'success');
    }
}
