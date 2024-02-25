<?php

namespace App\Http\Controllers\registration;

use App\Http\Controllers\Controller;
use App\Http\Controllers\emailController;
use App\Http\Controllers\registration\identity\coreApi;
use App\Models\order\eductional\academicpermission;
use App\Models\users\customer;
use App\Models\users\data\emailCode;
use App\Models\users\data\mobileCode;
use App\Models\users\data\notification;
use App\Models\users\marketer;
use App\Models\users\serviceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use PharIo\Manifest\Email;

class userController extends Controller
{
    public function __construct()
    {
        if (getRequestLanguage() != 'en') {
            App::setLocale(getRequestLanguage());
        }

    }

    public function signup()
    {
      $marketer = new marketer();
      return  $marketer->store();
    }

    public function login(Request $request)
    {
        $validator = Validator::make(
            request()->all(),
            [
                'email' => 'required|email',
                'password' => 'required',
            ],
            validationMessages()
        );

        // if has error
        if ($validator->fails()) {
            return apiresponse(false, 200, $validator->errors()->first());
        }

        //  login
        if (Auth::guard('marketer')->attempt(['email' => $request->email, 'password' => $request->password])) {
            $user = Auth('marketer')->user();
        }
       else {
            return apiresponse(false, 200, __('auth.failed_login'));
        }

        // return success data
        $token = $user->createToken('api-token')->plainTextToken;

        return apiresponse(true, 200, __('auth.success_proccess'), ['user' => $user->with(['location', 'location.country', 'location.city', 'location.region'])->find($user->id),
            'token' => $token,
        ]);

    }

    public function sendCode($type)
    {

        if ($type == 'change_password') {

            // email validation
            $validator = Validator::make(request()->all(), ['email' => 'required|email'], validationMessages());

            // validation error
            if ($validator->fails()){
                return apiresponse(false, 200, $validator->errors()->first()); }


            // email exist

            // marketer email
            if (marketer::where("email", request()->email)->count() > 0){ // auth user after register {
            $user = marketer::where("email", request()->email)->first(); }

            // not found
            else
            return apiResponse(false, 200, __('auth.email_not_found'));


        // generate  code
        emailCode::where('user_key', $user->user_key)->where('type', 2)->delete();

        $emailCode = random_int(10, 99) . rand(10, 99);

        emailCode::store($user, $emailCode, '2');

        // send code

        send_email_with_default_params('password_change', 'change password', 'change your password with this code', $emailCode, $user->email);

        return apiresponse(true, 200, __('auth.code_sent_email'));

    }

    elseif ($type == 'account_verification') {
        // email send
        if (isset(request()->email)) {
            // marketer
            if (marketer::where("email", request()->email)->count() > 0){
                $user = marketer::where("email", request()->email)->first(); }
            // not found
            else
             return apiResponse(false, 200, __('auth.email_not_found'));


            // generate  code
            emailCode::where('user_key', $user->user_key)->where('type', 1)->delete();

            $emailCode = random_int(10, 99) . rand(10, 99);

            emailCode::store($user, $emailCode, '1');

            // send code

            send_email_with_default_params('email_verification', 'email verification', 'verificate your email with this code', $emailCode, $user->email);

        }

        if (isset(request()->mobile)) {

            // marketer
            if (marketer::where("mobile", request()->mobile)->count() > 0){
            $user = marketer::where("mobile", request()->mobile)->first(); }
          // not found
            else
            return apiresponse(false, 200, __('auth.mobile_not_found'));


            mobileCode::where('user_key', $user->user_key)->where('type', 1)->delete();

            $mobileCode = 1234;

            mobileCode::store($user, $mobileCode, '1');


        // send sms
        //    send_sms_native('verificate your mobile with this code'.$userMobileCode->code,$user->mobile);

    }

    // send mobile msg
    return apiresponse(true, 200, __('auth.verification_code'));

}
}




    function checkCode($type)
{


// password change
    if ($type == 'change_password') {

        $email = request()->email;

        $validator = Validator::make(request()->all(), ['email' => 'required|email'], validationMessages());


        if ($validator->fails()){
            return apiresponse(false, 200, $validator->errors()->first());}

      //marketer
        if (marketer::where("email", request()->email)->count() > 0){
        $user = marketer::where("email", request()->email)->first(); }

      // no found
        else
        return apiResponse(false, 200, __('auth.email_not_found'));

        $userCode = $user->emailcode()->where('type', 2)->first();

        // check code
        if ($userCode->code == request()->code) {
            return apiresponse(true, 200, __('auth.correct_code'), null);
        }

        return apiresponse(false, 200, __('auth.incorrect_code'), null);
    }

    // account verification
    elseif ($type == 'account_verification') {

        $mobile = request()->mobile;

        $validator = Validator::make(request()->all(), ['mobile' => 'required'], validationMessages());


        if ($validator->fails()){
            return apiresponse(false, 200, $validator->errors()->first());}

      //marketer
        if (marketer::where("mobile", request()->mobile)->count() > 0){
        $user = marketer::where("mobile", request()->mobile)->first(); }

      // not found
        else
        return apiResponse(false, 200, __('auth.mobile_not_found'));
        // not the same mobile
        // if ($user->mobile != request()->mobile) {
        //     return apiresponse(false, 200, __('auth.incorrect_code'));
        // }

        // user codes
        $useremailCode = $user->emailcode()->where('type', 1)->first();
        $usermobileCode = $user->mobilecode()->where('type', 1)->first();

        if ($usermobileCode->code == request()->mobile_code) {

            // update activity

            $user->activity = 1;
            $user->save();

            // return user
            $token = $user->createToken('api-token')->plainTextToken;
            return apiresponse(true, 200, __('auth.account_verification_true'), [
                'user' => $user->with(['location', 'location.country', 'location.city', 'location.region'])->find($user->id),
                'token' => $token,
            ]);
        }
        // not correct
        return apiresponse(false, 200, __('auth.incorrect_code'), null);
    }

}


function change_password()        // perform passowrd change
{
        $email = request()->email;
        $password = request()->password;
        $confirm_password = request()->confirm_password;
        $code = request()->code;

        $validator = Validator::make(request()->all(),
            [
                'email' => 'required|email',
                'password' => 'required|same:confirm_password',
                'confirm_password' => 'required|same:password',
            ],
            validationMessages()
        );

        if ($validator->fails()) {
            return apiresponse(false, 200, $validator->errors()->first());
        }

        $email = request()->email;

        // marketer
        if (marketer::where("email", request()->email)->count() > 0){ // auth user after register {
         $user = marketer::where("email", request()->email)->first();
        }
      // not found
    else {
        return apiresponse(false, 200, "unAuthorized", null);
    }

    // get user code
    $userCode = $user->emailcode()->where('type', 2)->first();

    // check code
    if ($userCode->code == request()->code) {
        $user->password = Hash::make($password);
        $user->save();
        return apiresponse(true, 200, __('auth.password_changed'), null);
    }

    return apiresponse(false, 200, __('auth.unAuthorized'), null);

}

    function mobileLogin(Request $request)
{
    $validator = Validator::make(
        request()->all(),
        [
            'mobile' => 'required',
            'password' => 'required',
        ],
        validationMessages()
    );

    // if has error
    if ($validator->fails()) {
        return apiresponse(false, 200, $validator->errors()->first());
    }

    // marketer login
    if (Auth::guard('marketer')->attempt(['mobile' => $request->mobile, 'password' => $request->password])) {
        $user = Auth('marketer')->user();
    }
    else {
        return apiresponse(false, 200, __('auth.failed_login'));
    }

    // return success data
    $token = $user->createToken('api-token')->plainTextToken;

    return apiresponse(true, 200, __('auth.success_proccess'), ['user' => $user->with(['location', 'location.country', 'location.city', 'location.region'])->find($user->id),
        'token' => $token,
    ]);

}

    // public function mobileLogin(){
    //     $mobile = request()->mobile;

    //     if (customer::where("mobile", request()->mobile)->count() > 0)   // auth user after register
    //         $user =  customer::where("mobile", request()->mobile)->first();

    //     elseif (serviceProvider::where("mobile", $mobile)->count() > 0)
    //         $user  = serviceProvider::where("mobile", $mobile)->first();

    //     else
    //     return apiresponse(false, 200, __('auth.mobile_not_found'), null);

    //     $userMobileCode = $user->mobileCode()->where('type', 1)->first();

    //     if(request()->code == $userMobileCode->code)
    //     {
    //         $token = $user->createToken('api-token')->plainTextToken;
    //           return apiresponse(true, 200, __('auth.success_proccess'), [
    //             'user' => $user,
    //             'token' => $token
    //         ]);
    //     }

    //  return apiresponse(false, 200, __('auth.incorrect_code'), null);

    // }



function verify_identity(){

    $user = marketer::find(Auth('sanctum')->user()->id);

    $validator = Validator::make(
        request()->all(),
        [
            'identity_img' => "required|mimes:" . env('IMAGE_VALID_EXTENSIONS'),
            'personal_img' => "required|mimes:" . env('IMAGE_VALID_EXTENSIONS'),
        ],
        validationMessages()
    );



    // if has error
    if ($validator->fails()) {
        return apiresponse(false, 200, $validator->errors()->first());
    }

    // upload imgs
    $identity_img = upload_file('identity_img' , 'identity_img'  , 'users');
    $personal_img = upload_file('personal_img' , 'personal_img'  , 'users');

    $scan =  verify_identity(env('IDENTITY_IMGS_URL').$identity_img , env('PERSONAL_IMGS_URL').$personal_img);

   if($scan['success'] == 1)
   {
    $user->id_verification = 1;
    $user->save();
   return apiresponse(true , 200 , 'success' , marketer::with(['location', 'location.country', 'location.city', 'location.region'])->find($user->id));
 }
 else
 return apiresponse(false , 200 , $scan['msg']);

}


    function account_verification($user)
{

        $params = [
            'type' => 'email_verification',
            'title' => 'email verification',
            'body' => ' verificate your email with this code',
            'code' => $user->useremailCode->code,
            'to' => $user->email,
        ];
        $email = new emailController();
        $email->Send($params);

        // send mobile msg

        return apiresponse(true, 200, __('auth.verification_code'));

    }

    function logout()
{
        $user = Auth('sanctum')->user();
        $tokenId = $user->currentAccessToken()->id;
        $user->tokens()->where('id', $tokenId)->delete();
        return apiresponse(true, 200, "logged out");
//
    }

    function notifications()
{

        $user_key = Auth('sanctum')->user()->user_key;

        $notifications = notification::where('user_key', $user_key);
        $notifications->update(["seen" => 1]);
        $notifications = notification::where('user_key', $user_key)->orderby('id', 'desc')->get();

        return apiresponse(true, 200, 'successs', $notifications);

    }

    function deactive()
{

        $user = Auth('sanctum')->user();

        // marketer
            $user = marketer::find($user->id);

        // deactive
        $user->deactive();

        // logout
        $this->logout();

        return apiresponse(true, 200, "success");

    }

}
