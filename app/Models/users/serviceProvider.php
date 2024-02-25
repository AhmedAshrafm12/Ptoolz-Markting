<?php

namespace App\Models\users;

use App\Models\offer\offer;
use App\Models\order\eductional\academicpermission;
use App\Models\order\eductional\instructorUniversity;
use App\Models\order\eductional\reference;
use App\Models\transactions\chargetransaction;
use App\Models\transactions\handovertransaction;
use App\Models\transactions\markettransactions;
use App\Models\transactions\spaccounttransaction;
use App\Models\transactions\withdrawaltransaction;
use App\Models\users\data\emailCode;
use App\Models\users\data\experiences;
use App\Models\users\data\location;
use App\Models\users\data\mobileCode;
use App\Models\users\data\portfolio;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Sanctum\HasApiTokens;

class serviceProvider extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
    public $table = 'serviceprovider';
    public $timestamps = false;
    public $fillable =
        [
        "id",
        "username",
        "email",
        "full_name",
        "mobile",
        "password",
        "user_key",
        "activity",
        "account_type_id",
        "category_id",
        "datebirth",
    ];
    protected $hidden = ['password'];
    protected $appends = ['instructor_at', 'academic_request'];

    public static function boot()
    {

        parent::boot();
        static::created(function ($item) {
            // set user key
            $user_key = 'sp' . $item->id;
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

        // valiadtion

        $validator = Validator::make(
            request()->all(),
            [
                'email' => 'required|email|unique:serviceprovider,email|unique:customer,email',
                'full_name' => 'required',
                'username' => 'required|unique:serviceprovider,username|unique:customer,username',
                'mobile' => 'required|unique:serviceprovider,mobile|unique:customer,mobile',
                'password' => 'required',
                'category_id' => 'required',
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
            'category_id' => request()->category_id,
            'user_key' => "sp" . $this->id,
        ]);

        return apiresponse(true, 200, __('auth.verification_code'));
    }

    public function edit(serviceProvider $user)
    {
        $validator = Validator::make(
            request()->all(),
            [
                'full_name' => 'required',
                'datebirth' => 'required',
                'country' => 'required',
                'city' => 'required',
                'region' => 'required',
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

        if ($user->category_id == 10) {
            if (isset(request()->university)) {
                $university = request()->university;
                $validator = Validator::make(
                    request()->all(),
                    [
                        'college' => [Rule::requiredIf($university != null)],
                        'department' => [Rule::requiredIf($university != null)],
                    ],
                    validationMessages()
                );
                if ($validator->fails()) {
                    return apiresponse(false, 200, $validator->errors()->first());
                }

                $user->instructor_at()->update(
                    [
                        'university_id' => request()->university,
                        'college_id' => request()->college,
                        'department_id' => request()->department,

                    ]
                );
            }
        }

        return apiresponse(true, 200, "success", serviceProvider::with(['portfolio', 'experiences', 'location', 'location.country', 'location.city', 'location.region'])->find($this->id));
    }

    public function Location()
    {
        return $this->hasOne(location::class, 'user_key', 'user_key');
    }

    public function emailcode()
    {
        return $this->hasMany(emailCode::class, 'user_key', 'user_key');
    }
    public function notifications()
    {
        return $this->hasMany(notification::class, 'user_key', 'user_key');
    }
    public function mobilecode()
    {
        return $this->hasMany(mobileCode::class, 'user_key', 'user_key');
    }

    public function references()
    {
        return $this->hasMany(reference::class, 'instructor_id');
    }

    public function academicpermission()
    {
        return $this->hasMany(academicpermission::class, 'user_id');
    }

    public function printer_instructors()
    {
        return $this->belongsToMany(serviceProvider::class, "printerinstructor", 'printer_id', "instructor_id");
    }
    public function for_printer()
    {
        return $this->belongsToMany(serviceProvider::class, "printerinstructor", "instructor_id", 'printer_id');
    }

    public function offers()
    {
        return $this->hasMany(offer::class, 'user_id');
    }

    public function portfolio()
    {
        return $this->hasMany(portfolio::class, 'user_id');
    }
    public function experiences()
    {
        return $this->hasMany(experiences::class, 'user_id');
    }

    public function instructor_at()
    {
        return $this->hasOne(instructorUniversity::class, 'user_id');
    }

    public function chargeTransactions()
    {
        return $this->hasMany(chargetransaction::class, 'user_id')->where('account_type_id', 2);
    }

    public function withdrawaltransaction()
    {
        return $this->hasMany(withdrawaltransaction::class, 'user_id')->where('account_type_id', 2);
    }

    public function spaccounttransaction()
    {
        return $this->hasMany(spaccounttransaction::class, 'user_id');
    }

    public function orders_history()
    {

        // chareg transactions
        $charge_transactions = $this->chargeTransactions->toArray();

        // withdrawl transactions
        $withdral_transactions = $this->withdrawaltransaction->toArray();

        //  account_transactions

        $orders_transactions = spaccounttransaction::where('user_id', $this->id)->where('type_id', 1)->get()->toArray();

        // $references_transactions = clientaccounttransaction::with('purchase')->with('purchase.reference')->whereHas('purchase', fn ($qry) => $qry->where('client_id', $this->id))->where('type_id', 1)->get()->toArray();

        $history = collect(array_merge($orders_transactions, $charge_transactions, $withdral_transactions))->sortByDesc('created_at')->values();

        return $history;

    }


    public function handovers($at = null)
    {
        $offers_handovers = handovertransaction::with('offer')->with('offer.order')->whereHas('offer', fn($qry) => $qry->where('user_id', $this->id))->get()->toArray();
        $references_handovers = handovertransaction::with('purchase')->with('purchase.reference')->whereHas('purchase.reference', fn($qry) => $qry->where('instructor_id', $this->id))->get()->toArray();

        if($at != null){
            $offers_handovers = handovertransaction::with('offer')->with('offer.order')->whereHas('offer', fn($qry) => $qry->where('user_id', $this->id))->get()->toArray();
            $references_handovers = handovertransaction::with('purchase')->with('purchase.reference')->whereHas('purchase.reference', fn($qry) => $qry->where('instructor_id', $this->id))->get()->toArray();
        }



        return array_merge($offers_handovers , $references_handovers);


    }
    public function wallet()
    {

        $handovers = handovertransaction::with('offer')->with('offer.order')->whereHas('offer', fn($qry) => $qry->where('user_id', $this->id));
        $references_handovers = handovertransaction::with('purchase')->with('purchase.reference')->whereHas('purchase.reference', fn($qry) => $qry->where('instructor_id', $this->id));
        // return $handovers->get();
        $avilable_balance = $this->currentBalance();
        $pending_balance = round($this->offers()->where('status', 2)->sum('after_fees'));
        $history = $this->orders_history();
        $current_balance = $this->currentBalance();

        // foreach ($handovers->get() as $handover) {
        //     $handover->offer->order->total_cost = round($handover->sp_share);
        //     $handover->offer->order->type = 1;
        //     $orders_history[] = $handover->offer->order;
        // }
        // foreach ($references_handovers->get() as $reference) {
        //     $reference->purchase->reference->total_cost = round($reference->sp_share);
        //     $reference->purchase->reference->type = 1;
        //     $reference_histroy[] = $reference->purchase->reference;
        // }
        // $history = collect(array_merge($reference_histroy, $orders_history))->sortBy('created_at')->values();

        return compact('avilable_balance', 'pending_balance', 'current_balance', 'history');
    }

    public function currentBalance()
    {

        // charge transactions
        $charge = $this->chargeTransactions->sum('value');

        // withdral transactions
        $withdral = $this->withdrawaltransaction->sum('value');

        // out come transactions
        // cancellation
        // $incomeAccountTransactions  =  $this->clientaccounttransaction->where('type_id', 4)->sum('value') +  $this->clientaccounttransaction->where('type_id', 3)->sum('value');

        // in_Come transactions
        $inComeAccountTransactions = $this->spaccounttransaction->where('type_id', 1)->sum('value');

        // return $handovers->get();
        $avilable_balance = ($inComeAccountTransactions + $charge) - $withdral;

        return floor($avilable_balance);
    }

    public function getAvatarAttribute($value)
    {
        return env('PROFILE_IMG_URL') . $value;
    }
    public function getInstructorAtAttribute($value)
    {
        $value = $this->category_id == 10 ? $this->instructor_at()->first() : null;
        return $value;
    }
    public function getAcademicRequestAttribute($value)
    {
        return academicpermission::where('user_id', $this->id)->first() ?? null;
    }

    public function market_transactions(){
        return $this->hasMany(markettransactions::class , 'sp_id');
    }

    public function deactive(){

        $this->activity =0 ;
        $this->save();

     }
}
