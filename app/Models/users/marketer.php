<?php

namespace App\Models\users;

use App\Models\transactions\chargetransaction;
use App\Models\transactions\markettransactions;
use App\Models\transactions\withdrawaltransaction;
use App\Models\users\data\emailCode;
use App\Models\users\data\location;
use App\Models\users\data\marketerleader;
use App\Models\users\data\mobileCode;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Sanctum\HasApiTokens;

class marketer extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'marketer';
    public $timestamps = false;
    protected $fillable = [
        "id", "username", "email", "mobile", "full_name", "checked", "avatar", "password", "user_key", "activity", "approval", "id_verification", "datebirth", "account_type_id", "created_at", "role", "join_code",
    ];

    protected $hidden = ['password'];

    public static function boot()
    {

        parent::boot();
        static::created(function ($item) {

            // set user key
            $user_key = 'marketer' . $item->id;
            $item->user_key = $user_key;

            // generate leader code
            if ($item->role == 2) {
                $item->join_code = uniqid('le');
            }

            $item->save();

            // add marketer to leader
            if ($item->role == 1) {
                marketerleader::store($item);
            }

            // store user location
            location::store($item);

            // store user  verifications codes
            $emailCode = random_int(10, 99) . rand(10, 99);
            $mobileCode = 1234;

            emailCode::store($item, $emailCode, '1');
            mobileCode::store($item, $mobileCode, '1');

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
                'email' => 'required|email|unique:serviceprovider,email|unique:customer,email|unique:marketer,email',
                'full_name' => 'required',
                'username' => 'required|unique:serviceprovider,username|unique:customer,username|unique:marketer,username',
                'mobile' => 'required|unique:serviceprovider,mobile|unique:customer,mobile|unique:marketer,mobile',
                'password' => 'required',
                'role' => 'required',
                'join_code' => 'required_if:role,1',
            ],
            validationMessages()
        );

        // return errors
        if ($validator->fails()) {
            return apiresponse(false, 200, $validator->errors()->first());
        }

        // check join code
        if (request()->role == 1) {
            if (check_join_code(request()->join_code) == 0) {
                return apiresponse(false, 200, 'join code not correct');
            }

        }

        // create user
        $user = $this->create([
            'email' => request()->email,
            'full_name' => request()->full_name,
            'username' => request()->username,
            'mobile' => request()->mobile,
            'password' => Hash::make(request()->password),
            'user_key' => "marketer" . $this->id,
            "role" => request()->role,
        ]);

        return apiresponse(true, 200, __('auth.verification_code'));
    }

    public function edit()
    {
        $user = marketer::find(auth_user()->id);
        // return $user;
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

        $file = $user->attributes['avatar'];
        if (isset(request()->avatar)) {

            $validator = Validator::make(request()->all(), ["avatar" => "mimes:" . env('IMAGE_VALID_EXTENSIONS')]);

            if ($validator->fails()) {
                return apiresponse(false, 200, $validator->errors()->first());
            }

            $file = upload_public("avatar", '../../assets/users/profile_img/');

            $user->avatar = $file;
            $user->save();
        }

        $user->Location()->update(
            [
                'country' => request()->country,
                'city' => request()->city,
                'region' => request()->region,
            ]
        );

        $user->update(
            [
                'full_name' => request()->full_name,
                'datebirth' => request()->datebirth,
                'avatar' => $file,
            ]
        );

        return apiresponse(true, 200, "success", $user->myprofile());
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

    public function deactive()
    {

        $this->activity = 0;
        $this->save();
    }

    public function serviceproviders()
    {
        return $this->belongsToMany(serviceProvider::class, 'marketerserviceprovider', 'marketer_id', "sp_id");

    }

    public function team()
    {
        return $this->belongsToMany(marketer::class, 'marketerleader', 'leader_id', "marketer_id");
    }

    public function transactions()
    {
        return $this->hasMany(markettransactions::class, 'marketer_id');
    }

    public function sp_stats($from, $to)
    {

        // sps stats

        // total transations

        $transactions = $this->transactions()->where("created_at", '>=', $from)->where("created_at", '<=', $to);

        // total income
        $over_all_income = round($transactions->sum('marketer_share'));

        // total sps

        $sp_count = $this->serviceproviders->count();

        $services_providers = $this->serviceproviders;

        foreach ($services_providers as $sp) {
            $sp_transactions = $sp->market_transactions()->where("created_at", '>=', $from)->where("created_at", '<=', $to);
            $sp->total_income = round($sp_transactions->sum('marketer_share'));
            $sp->total_offers = $sp_transactions->count();
        }

        return compact('services_providers', 'over_all_income', 'sp_count');

    }

    public function team_stats($from, $to)
    {
        $team = $this->team;
        $over_all_income = 0;
        $team_count = $this->team->count();
        foreach ($team as $member) {
            $member_sp_stats = $member->sp_stats($from, $to);

            $member_transactions = $member->transactions()->where("created_at", '>=', $from)->where("created_at", '<=', $to);

            $member->total_income = round($member_transactions->sum('leader_share'));
            $over_all_income += $member->total_income;
            $member->total_offers = $member_transactions->count();
            $member->sp_count = $member_sp_stats['sp_count'];

            unset($member->serviceproviders);
        }

        return compact('team', 'over_all_income', 'team_count');

    }

    public function myprofile()
    {
        return $this->with(['location', 'location.country', 'location.city', 'location.region'])->find($this->id);

    }

    public function getAvatarAttribute($value)
    {
        return env('PROFILE_IMG_URL') . $value;
    }

    public function chargeTransactions()
    {
        return $this->hasMany(chargetransaction::class, 'user_id')->where('account_type_id', 3);
    }

    public function withdrawaltransaction()
    {
        return $this->hasMany(withdrawaltransaction::class, 'user_id')->where('account_type_id', 3);
    }

    public function currentBalance()
    {
        // charge transactions
        $charge = $this->chargeTransactions->sum('value');

        // withdral transactions
        $withdral = $this->withdrawaltransaction->sum('value');

        // income transactions
        $accounttransactions = $this->transactions->sum('marketer_share');

        // team income
        $team_income = $this->team_stats('1990-1-01', now())['over_all_income'] ?? 0;

        $in_come = floor($accounttransactions + $team_income);
        $out_come = floor($withdral);

        return $in_come - $out_come;
    }

    public function orders_history()
    {

        // chareg transactions
        $charge_transactions = $this->chargeTransactions->toArray();

        // withdrawl transactions
        $withdral_transactions = $this->withdrawaltransaction->toArray();

        //  account_transactions

        $in_come_transactions = $this->transactions->toArray();

        // team income
        $team_income = [];
        foreach ($this->team as $member) {
            foreach ($member->transactions as $transaction) {
                $team_income[] = $transaction;
            }
        }

        $history = collect(array_merge($in_come_transactions, $team_income, $charge_transactions, $withdral_transactions))->sortByDesc('created_at')->values();

        return $history;

    }

    public function wallet()
    {

        // charge transactions
        $charge = $this->chargeTransactions->sum('value');

        // withdral transactions
        $withdral = $this->withdrawaltransaction->sum('value');

        // income transactions
        $accounttransactions = $this->transactions->sum('marketer_share');

        // team income
        $team_income = $this->team_stats('1990-1-01', now())['over_all_income'] ?? 0;

        $in_come = floor($accounttransactions + $team_income);
        $out_come = floor($withdral);

        //  history
        $history = $this->orders_history();

        $current_balance = $this->currentBalance();

        return compact('in_come', 'out_come', 'current_balance', 'history');
    }

}
