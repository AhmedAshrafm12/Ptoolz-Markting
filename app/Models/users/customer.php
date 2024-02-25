<?php

namespace App\Models\users;

use App\Models\order\eductional\studentUniversity;
use App\Models\order\order;
use App\Models\transactions\chargetransaction;
use App\Models\transactions\clientaccounttransaction;
use App\Models\transactions\handovertransaction;
use App\Models\transactions\withdrawaltransaction;
use App\Models\users\data\emailCode;
use App\Models\users\data\location;
use App\Models\users\data\mobileCode;
use App\Models\users\data\notification;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Sanctum\HasApiTokens;

class customer extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    public $timestamps = false;
    public $table = 'customer';

    public $fillable =
        [
        "id",
        "username",
        "email",
        "mobile",
        "password",
        "full_name",
        "user_key",
        "activity",
        "student",
        "account_type_id",
        "created_at",
        "datebirth",
    ];

    protected $hidden = ['password'];
    protected $appends = ['student_at'];

    public static function boot()
    {

        parent::boot();
        static::created(function ($item) {

            // set user key
            $user_key = 'customer' . $item->id;
            $item->user_key = $user_key;
            $item->save();

            // store user location
            location::store($item);

            // store user  verifications codes
            $emailCode = random_int(10, 99) . rand(10, 99);
            $mobileCode = 1234;

            emailcode::store($item, $emailCode, '1');
            mobilecode::store($item, $mobileCode, '1');

            // send verifications codes
            account_verification($item->email, $emailCode, $item->mobile, $mobileCode);

        });

    }

    public function store()
    {

         // validation
        $validator = Validator::make(
            request()->all(),
            [
                'email' => 'required|email|unique:serviceprovider,email|unique:customer,email',
                'full_name' => 'required',
                'username' => 'required|unique:serviceprovider,username|unique:customer,username',
                'mobile' => 'required|unique:serviceprovider,mobile|unique:customer,mobile',
                'password' => 'required',
            ],
            validationMessages()
        );

        // return errors
        if ($validator->fails())
            return apiresponse(false, 200, $validator->errors()->first());


    // create user
        $user = $this->create([
            'email' => request()->email,
            'full_name' => request()->full_name,
            'username' => request()->username,
            'mobile' => request()->mobile,
            'password' => Hash::make(request()->password),
            'user_key' => "sp" . $this->id,
        ]);

        return apiresponse(true, 200, __('auth.verification_code'));
    }




    public function edit(customer $user)
    {
        $validator = Validator::make(
            request()->all(),
            [
                'full_name' => 'required',
                'datebirth' => 'required',
                'country' => 'required',
                'city' => 'required',
                'region' => 'required',
                'student' => 'required',
            ],
            validationMessages()
        );
        if ($validator->fails()) {
            return apiresponse(false, 200, $validator->errors()->first());
        }

        $user->update(
            [
                'full_name' => request()->full_name,
                'datebirth' => request()->datebirth,
                'student' => request()->student,
            ]
        );

        $user->Location()->update(
            [
                'country' => request()->country,
                'city' => request()->city,
                'region' => request()->region,
            ]
        );

        if (isset(request()->avatar)) {
            $file = handleFile(env('IMAGE_VALID_EXTENSIONS'), 'avatar');

            if (!$file['valid']) {
                return apiresponse(false, 200, "select a valid file");
            }

            $file_path = $file['fileName'];
            file_put_contents(public_path('../../assets/users/profile_img/') . $file['fileName'], $file['content']);

            $user->avatar = $file_path;
            $user->save();
        }

        if (request()->student == 1) {

            $validator = Validator::make(
                request()->all(),
                [
                    'university' => 'required',
                    'university_code' => 'required',
                ],
                validationMessages()
            );
            if ($validator->fails()) {
                return apiresponse(false, 200, $validator->errors()->first());
            }

            if ($user->student_at()->count() == 0) {
                $user->student_at()->create(
                    [
                        'university_id' => request()->university,
                        'university_code' => request()->university_code,

                    ]
                );
            } else {
                $user->student_at()->update(
                    [
                        'university_id' => request()->university,
                        'university_code' => request()->university_code,

                    ]
                );
            }
        }
        //         if($user->student == 0)
        // {
        //     if ($user->student_at()->count() > 0) {
        //         $user->student_at()->delete();
        //     }
        // }
        return apiresponse(true, 200, "success", customer::with(['location', 'location.country', 'location.city', 'location.region'])->find($this->id));
    }



    public function Location()
    {
        return $this->hasOne(location::class, 'user_key', 'user_key');
    }

    public function notifications()
    {
        return $this->hasMany(notification::class, 'user_key', 'user_key');
    }

    public function emailcode()
    {
        return $this->hasMany(emailCode::class, 'user_key', 'user_key');
    }
    public function mobilecode()
    {
        return $this->hasMany(mobileCode::class, 'user_key', 'user_key');
    }

    public function orders()
    {
        return $this->hasMany(order::class, 'client_id')->orderBy('id', 'DESC');
    }

    public function chargeTransactions()
    {
        return $this->hasMany(chargetransaction::class, 'user_id')->where('account_type_id', 1);
    }

    public function withdrawaltransaction()
    {
        return $this->hasMany(withdrawaltransaction::class, 'user_id')->where('account_type_id', 1);
    }

    public function clientaccounttransaction()
    {
        return $this->hasMany(clientaccounttransaction::class, 'user_id');
    }

    public function holdtransaction()
    {
        return $this->hasMany(holdtransaction::class, 'user_id');
    }
    public function student_at()
    {
        return $this->hasOne(studentUniversity::class, 'user_id');
    }

    public function orders_history()
    {

        // chareg transactions
        $charge_transactions = $this->chargeTransactions->toArray();

        // withdrawl transactions
        $withdral_transactions = $this->withdrawaltransaction->toArray();

        //  account_transactions

        $orders_transactions = clientaccounttransaction::where('user_id', $this->id)->where('type_id', 1)->get()->toArray();

        // holded transactions

        $holded_transactions = clientaccounttransaction::where('user_id', $this->id)->where('type_id', 2)->get()->toArray();

        // foreach($orders_transactions as $order)
        // {
        //     if($order->offer){
        //         $order->title =$order->offer->order->title;
        //     }
        //     elseif($order->purchase)
        //     {
        //         $order->title =$order->offer->purchase->reference->title;
        //     }
        // }

        // $references_transactions = clientaccounttransaction::with('purchase')->with('purchase.reference')->whereHas('purchase', fn ($qry) => $qry->where('client_id', $this->id))->where('type_id', 1)->get()->toArray();

        $history = collect(array_merge($orders_transactions, $charge_transactions, $withdral_transactions, $holded_transactions))->sortByDesc('created_at')->values();

        return $history;

    }

    public function wallet()
    {

        // charge transactions
        $charge = $this->chargeTransactions->sum('value');

        // withdral transactions
        $withdral = $this->withdrawaltransaction->sum('value');

        // income transactions
        $incomeAccountTransactions = $this->clientaccounttransaction->where('type_id', 4)->sum('value') + $this->clientaccounttransaction->where('type_id', 3)->sum('value');

        // outCome transactions
        $outComeAccountTransactions = $this->clientaccounttransaction->where('type_id', 1)->sum('value') + $this->clientaccounttransaction->where('type_id', 2)->sum('value');

        $handovers = handovertransaction::with('offer')->with('offer.order')->whereHas('offer.order', fn($qry) => $qry->where('client_id', $this->id));

        $out_come = round($outComeAccountTransactions + $withdral);
        $in_come = round($incomeAccountTransactions + $charge);
        $history = $this->orders_history();
        $current_balance = $this->currentBalance();
        // $charge  = $this->chargeTransactions->toArray();
        // foreach ($handovers->get() as $handover) {
        //     $handover->offer->order->total_cost = round($handover->hold_value);
        //     $handover->offer->order->type = 0;
        //     $orders_history[] = $handover->offer->order;
        // }

        // $history = collect(array_merge($charge, $orders_history))->sortBy('created_at')->values();

        return compact('in_come', 'out_come', 'current_balance', 'history');
    }



    public function currentBalance()
    {

        // charge transactions
        $charge = $this->chargeTransactions->sum('value');

        // withdral transactions
        $withdral = $this->withdrawaltransaction->sum('value');

        // income transactions
        $incomeAccountTransactions = $this->clientaccounttransaction->where('type_id', 4)->sum('value') + $this->clientaccounttransaction->where('type_id', 3)->sum('value');

        // outCome transactions
        $outComeAccountTransactions = $this->clientaccounttransaction->where('type_id', 1)->sum('value') + $this->clientaccounttransaction->where('type_id', 2)->sum('value');

        // total balance
        $balance = ($charge + $incomeAccountTransactions) - ($withdral + $outComeAccountTransactions);
        return floor($balance);
    }

    public function getAvatarAttribute($value)
    {
        return env('PROFILE_IMG_URL') . $value;
    }

    public function getStudentAtAttribute($value)
    {
        $value = $this->student == 1 ? $this->student_at()->first() : null;
        return $value;
    }


    public function deactive(){

        $this->activity =0 ;
        $this->save();
    }
}
