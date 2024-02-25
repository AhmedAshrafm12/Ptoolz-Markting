<?php

use App\Models\data\tax;
use App\Models\data\city;
use App\Models\data\region;
use App\Models\offer\offer;
use App\Models\order\order;
use App\Models\data\country;
use Illuminate\Support\Str;;
use App\Models\data\category;
use App\Models\data\ordertype;
use App\Models\users\customer;
use App\Models\users\marketer;
use App\Models\order\print\map;
use App\Models\order\cameracart;
use App\Models\order\orderDelivery;
use App\Models\order\print\picture;
use App\Models\order\print\document;
use SebastianBergmann\Type\NullType;
use App\Models\users\serviceProvider;
use App\Models\order\photoraphy\photo;
use App\Models\users\data\notification;
use Illuminate\Support\Facades\Storage;
use App\Models\data\systemConfiguration;
use App\Http\Controllers\emailController;
use App\Models\order\eductional\reference;
use Stevebauman\Location\Facades\Location;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Laravel\Firebase\Facades\Firebase;
use App\Http\Controllers\registration\identity\coreApi;

function getLocation()
{
    $ip = request()->ip(); /* Static IP address */
    $currentUserInfo = Location::get($ip);
    $currentUserInfo->country_id = country::where("country_code", $currentUserInfo->countryCode)->first()->id ?? 1;
    $currentUserInfo->city_id = city::where("city_name", $currentUserInfo->cityName)->first()->id ?? 1;
    $currentUserInfo->region_id = region::where("region_name", $currentUserInfo->regionName)->first()->id ?? 1;
    return $currentUserInfo;
}

function getRequestLanguage()
{
    return request()->header('accept-language');
}

function get_platform()
{
    return request()->header('device-type');
}

function apiresponse($success, $code, $message, $data  = Null)
{
    return response()->json(['success' => $success, "code" => $code, "data" => $data, "message" => $message], $code);
}
function validationMessages()
{
    return  [
        'email.required' => __('auth.email_required'),
        'email.unique' => __('auth.email_unique'),
        'email.email' => __('auth.email_correct'),
        'username.required' => __('auth.username_required'),
        'username.unique' => __('auth.username_unique'),
        'full_name.required' => __('auth.full_name_required'),
        'mobile.required' => __('auth.mobile_required'),
        'password.required' => __('auth.password_required'),
        'password.same' => __('auth.password_same'),
        'mobile.unique' => __('auth.mobile_uinque'),
        'file.required' => __('order.file_required'),
        "paper_size.required" => __('order.paper_size'),
        "title.required" => __('order.title'),
        "description.required" => __('order.description'),
        "paper_type.required" => __('order.paper_type'),
        "delivery.required" => __('order.delivery'),
        "printing_color.required" => __('order.printing_color'),
        "printing_side.required" => __('order.printing_side'),
        "paper_binding.required" => __('order.paper_binding'),
        "number.required" => __('order.number'),
        "country.required_if" => __('order.country'),
        'city.required_if' => __('order.city'),
        'region.required_if' => __('order.region'),
        "country.required" => __('order.country'),
        'city.required' => __('order.city'),
        'region.required' => __('order.region'),
        'latitude.required_if' => __('order.latitude'),
        'longitude.required_if' => __('order.longitude'),
        'receiver_mobile.required' => __('order.receiver_mobile'),
        'receiver_name.required' => __('order.receiver_name'),
        'picture_size.required' => __('order.picture_size'),
        "occasion_id.required" => __('order.occasion'),
        "occcasion_name.required_if" => __('order.occasion'),
        "duration_days.required" => __('order.duration_days'),
        "duration_hours.required" => __('order.duration_hours'),
        "duration_minutes.required" => __('order.duration_minutes'),
        "occassion_date.required" => __('order.occassion_date'),
        "occassion_time.required" => __('order.occassion_time'),
    ];
}



function account_verification($email, $emailCode, $mobile = null, $mobileCode = 1234)
{
    $params = [
        'type' => 'email_verification',
        'title' => 'email verification',
        'body' => ' verificate your email with this code',
        'code' => $emailCode,
        'to' => $email
    ];
    $email = new emailController();
    $email->Send($params);


    // send mobile msg
    // send_sms_native('verificate your mobile with this code'.$mobileCode,$mobile);


    return apiresponse(true, 200, __('auth.verification_code'));
}


function orderModel($orderType)
{
    // $orderTypeClassName =  orderType::find($orderType)->tableName;
    if ($orderType == 1)
        return document::class;
    elseif ($orderType == 2)
        return map::class;
    elseif ($orderType == 3)
        return picture::class;
    //    elseif($orderType == 4)
    //    return orderImage::class;
    //    elseif($orderType == 5)
    //    return orderVideo::class;
    //    elseif($orderType == 6)
    //    return orderFilm::class;
    elseif ($orderType == 7)
        return photo::class;
    //    elseif($orderType == 8)
    //    return orderRentCamera::class;
    //    elseif($orderType == 9)
    //    return orderTranslate::class;
    //    elseif($orderType == 11)
    //    return orderGift::class;
}


function handleFile($extensions, $name)
{

    $fileContent = file_get_contents($_FILES[$name]['tmp_name']);

    $extension = explode('/', $_FILES[$name]['type'])[1];

    $exts = explode(',', $extensions);
    $valid = in_array($extension, $exts);

    $fileName = time() . '.' . $extension;
    return ['content' => $fileContent, 'extension' => $extension, 'valid' => $valid, 'fileName' => $fileName];
}


// function handleReference($extensions){

// // return $_FILES['file'];
//     $fileContent = file_get_contents($_FILES['file']['tmp_name']);
//   $extension = explode('/' , $_FILES['file']['type'])[1];
//  $stringLength = Str::length($fileContent);
//  $reveiwLength = $stringLength/100 *25;

//  $reveiw =  Str::substr($fileContent,0 ,$reveiwLength);
//   $reviwName = time().'.'.$extension;
//   $path =  Storage::disk('order')->put('/references/review/'.$reviwName , $fileContent);


//   // $exts = explode(',',$extensions);
//     // $valid = in_array($extension , $exts);

//     // $fileName = time().'.'.$extension;
//     // return ['content'=>$fileContent , 'extension'=>$extension , 'valid'=>$valid , 'fileName'=>$fileName];

// }


function offerCost(offer $offer)
{
    $cost = 0;
    $user = customer::find(Auth('sanctum')->user()->id);
    $tax =  tax::where('country', $user->Location->country)->first()->taxPercentage ?? 10;
    if ($offer->after_discount != 0)
        $cost = $offer->after_discount + $offer->deliveryCost;
    else
        $cost = $offer->offer_cost + $offer->delivery_cost;


    $clearCost = $cost  +  (($cost / 100) * $tax);
    return $clearCost;
}

function systemConfig($cost)
{
    $config =  systemConfiguration::latest('id')->first();

    $spShare = ($cost / 100) * $config->spAcceptPercentage;
    $adminshare = ($cost / 100) * $config->adminAcceptPercentage;
    $adminAcceptPercentage = $config->adminAcceptPercentage;
    $spAcceptPercentage = $config->spAcceptPercentage;

    return compact('spShare', 'adminshare', 'adminAcceptPercentage', 'spAcceptPercentage');
}


function merchant_helper()
{
    $data['shaString']  = null;
    $data['hash'] = '95KohPnRfsjEPbpKJX.C/P?#';
    $data['merchant_identifier'] = 'e4fcb656';
    $data['access_code'] = 'QQi6751LuIjYztnRGMWI';
    $data['currency'] = 'SAR';
    $data['language'] = 'ar';
    $data['return_url'] = 'https://ptoolzapi.ptoolz.com/api/payment/proccess';
    $data['redirectUrl'] = 'https://sbcheckout.PayFort.com/FortAPI/paymentPage';
    return $data;
}
function reference_merchant_helper()
{
    $data['shaString']  = null;
    $data['hash'] = '95KohPnRfsjEPbpKJX.C/P?#';
    $data['merchant_identifier'] = 'e4fcb656';
    $data['access_code'] = 'QQi6751LuIjYztnRGMWI';
    $data['currency'] = 'SAR';
    $data['language'] = 'ar';
    $data['return_url'] = 'https://ptoolzapi.ptoolz.com/api/payment/reference_proccess';
    $data['redirectUrl'] = 'https://sbcheckout.PayFort.com/FortAPI/paymentPage';
    return $data;
}
function charge_merchant_helper()
{
    $data['shaString']  = null;
    $data['hash'] = '95KohPnRfsjEPbpKJX.C/P?#';
    $data['merchant_identifier'] = 'e4fcb656';
    $data['access_code'] = 'QQi6751LuIjYztnRGMWI';
    $data['currency'] = 'SAR';
    $data['language'] = 'ar';
    $data['return_url'] = 'https://ptoolzapi.ptoolz.com/api/payment/charge_proccess';
    $data['redirectUrl'] = 'https://sbcheckout.PayFort.com/FortAPI/paymentPage';
    return $data;
}


function processMerchantPageResponse()
{
    $fortParams = array_merge($_GET, $_POST);
    $statusWEB = 0;
    $status = 0;
    $response_code = 0;
    $amount = 0;
    $debugMsg = 0;
    $success = 0;
    $merchantReference = 0;

    if (empty($fortParams)) {
        $success = false;
        $reason = "Invalid Response Parameters";
        $debugMsg = $reason;
    } else {
        $params = $fortParams;
        $amount     = $fortParams['amount'];
        $responseSignature     = $fortParams['signature'];
        $merchantReference = $params['merchant_reference'];
        unset($params['r']);
        unset($params['signature']);
        unset($params['integration_type']);
        $response_code    = $params['response_code'];
        $response_message = $params['response_message'];
        $status           = $params['status'];
        if (substr($response_code, 2) != '000') {
            $success = false;
            $reason  = $response_message;
        }
        $debugMsg = $response_message;

        if (!$success) {
            $statusWEB = 100;
        } else {
            $statusWEB = 200;
        }
    }
    return  array(
        'merchantReference' => $merchantReference,
        'message' => $debugMsg,
        'amount' => $amount,
        'response_code' => $response_code,
        'statusAPI' => $status,
        'statusWEB' => $statusWEB
    );
}


function set_order_additional_data(order $item)
{
    $item->category_name = category::find($item->category_id)->department_name;
    $item->order_type_name = ordertype::find($item->order_type_id)->name;
}


function cart_item_selected($item_id, $order_id, $type)
{
    if (cameracart::where('item_id', $item_id)->where('order_id', $order_id)->where('type', $type)->count() > 0) {
        $pivot = cameracart::where('item_id', $item_id)->where('order_id', $order_id)->where('type', $type)->first();

        $selected = true;
    } else {
        $selected = false;
        $pivot = Null;
    }

    return [
        'selected' => $selected,
        'pivot' => $pivot
    ];
}


function create_auto_delivery_order(orderDelivery $delivery)
{

    $new_delivery = $delivery->replicate();
    $new_delivery->save();

    $order =  order::create([
        "category_id" => 6,
        "order_type_id" => 10,
        "client_id" => auth("sanctum")->user()->id,
        "title" => request()->title,
        "description" => request()->description,
        "status" => 1,
        "order_id" => $new_delivery->id,
        'code' => random_int(0, 1000)
    ]);

    $new_delivery->order_id = $order->id;
    $new_delivery->save();
}


function reference_cost(reference $reference, $user_id = null)
{
    $cost = $reference->price;
    if ($user_id == null)
        $user = customer::find(Auth('sanctum')->user()->id);
    else
        $user = customer::find($user_id);

    $tax =  tax::where('country', $user->Location->country)->first()->taxPercentage ?? 10;
    $clearCost = $cost  +  (($cost / 100) * $tax);
    return $clearCost;
}
function offer_submitted(order $order)
{


    return $order->offers()->where('user_id', Auth('sanctum')->user()->id)->count();
}


function download($user_id, $reference_id)
{
}


/// sebd notifiaction to topic

// function send_to_topic($topic, $payload_data)
// {

//     $messaging = app('firebase.messaging');
//     if (get_platform() == 'android') {
//         try{
//         $message = CloudMessage::fromArray([
//             'topic' => $topic,
//             'data' => $payload_data, // optional
//         ]);
//     }catch(Throwable $e){
//     }
//     } else {
//         $message = CloudMessage::fromArray([
//             'topic' => $topic,
//             'notification' => $payload_data, // optional
//             'data' => $payload_data, // optional
//         ]);
//     }


//     if ($messaging->send($message)) {
//         notification::store($payload_data);
//     }
// }


function send_to_topic($topic_name,$payload_data)
{

      if (get_platform() == 'android') {
    $data = json_encode([
        "to" => '/topics/'.$topic_name,
        "data" => $payload_data,
    ]);
}
else{
    $data = json_encode([
        "to" => '/topics/'.$topic_name,
        "notification" => $payload_data,
        "data" => $payload_data
    ]);

}
    //FCM API end-point
    $url = 'https://fcm.googleapis.com/fcm/send';
    //api_key in Firebase Console -> Project Settings -> CLOUD MESSAGING -> Server key
    $server_key = 'AAAAYN4l32I:APA91bGVJ46QUD58d5jkUq_3vFYMlOTL8jLHvQCKRBPyURSQiWf9XvfUJwgmNMfLIkO39lIlvnpU_90sGzuuBftNyJsXpJcKuqy5kTJUZtfdYgew6qrpSi3J_YmDz5D7IGaxsNvarSHN';

    //header with content_type api key
    $headers = array(
        'Content-Type:application/json',
        'Authorization:key='.$server_key
    );
    //CURL request to route notification to FCM connection server (provided by Google)
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    $result = curl_exec($ch);
    if ($result === FALSE) {
        \Illuminate\Support\Facades\Log::info('Oops! FCM Send Error: ' . curl_error($ch));
//        Log('Oops! FCM Send Error: ' . curl_error($ch));
    }
    \Illuminate\Support\Facades\Log::info($result);
    curl_close($ch);
}



function test_notifi($topic, $payload_data)
{

    $messaging = app('firebase.messaging');
    if (get_platform() == 'android') {

        $message = CloudMessage::fromArray([
            'topic' => $topic,
            'data' => $payload_data, // optional
        ]);

    } else {
        $message = CloudMessage::fromArray([
            'topic' => $topic,
            'notification' => $payload_data, // optional
            'data' => $payload_data, // optional
        ]);
    }


return   $messaging->send($message);

}

// function customer_notifications_types(){
// return ["new_order"=>'new_order','new_offer'=>'new_offer'];
// }
// function sp_notifications_types(){
// return ["add_offer"=>'add_offer','offer_accepted'=>'offer_accepted'];
// }


// create the notification payload

function set_payload($title, $body, $type, $data = null, $user_key = null)
{
    return  [
        "title" => $title,
        "body" => $body,
        "data" => $data,
        "type" => $type,
        "user_key" => $user_key,
    ];
};

function set_user_topic($user_id)
{
    return 'user_' . $user_id;
}



function genereate_sms_token(){

    $key = env('SMS_KEY');
    $secret = env('SMS_SECRET');

    $token =  base64_encode($key.':'.$secret) ;


    $curl = curl_init();

    curl_setopt_array($curl, array(
      CURLOPT_URL => "https://auth.routee.net/oauth/token",
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => "",
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 30,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => "POST",
      CURLOPT_POSTFIELDS => "grant_type=client_credentials",
      CURLOPT_HTTPHEADER => array(
        "authorization: Basic $token",
        "content-type: application/x-www-form-urlencoded"
      ),
    ));

    $response = curl_exec($curl);
    $err = curl_error($curl);

    curl_close($curl);

    if ($err) {
      return "cURL Error #:" . $err;
    } else {
      return $response;
    }

}


function send_sms_native($body , $to ){

    $from = env('APP_NAME');
    $curl = curl_init();
    // key:sec =>base64 = token

 $token =    json_decode(genereate_sms_token())->access_token;


    curl_setopt_array($curl, array(
      CURLOPT_URL => "https://connect.routee.net/sms",
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => "",
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 30,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => "POST",
      CURLOPT_POSTFIELDS =>  "{ \"body\": \"$body\",\"to\" : \"$to\",\"from\": \"$from\",\"callback\": { \"strategy\": \"OnCompletion\", \"url\": \"http://www.ptoolz.com\"}}",
      CURLOPT_HTTPHEADER => array(
        "authorization: Bearer $token",
        "content-type: application/json"
      ),
    ));

    $response = curl_exec($curl);
    $err = curl_error($curl);

    curl_close($curl);

    if ($err) {
      return  "cURL Error #:" . $err;
    } else {
      return 1;
    }


}


function upload_file($key , $path , $disk){
    // return request()->file($key);
    $org_file_name = request()->file($key)->getClientOriginalName();
    $file_name = time() . '.' .pathinfo($org_file_name, PATHINFO_EXTENSION);
    $path = request()->file($key)->storeAs($path,$file_name,$disk);
    return $file_name;
}

function check_printer_instructor(serviceProvider $sp){

    return ['count'=>$sp->for_printer()->count() , 'printer'=>$sp->for_printer->first()];


}

function getCurrency(){
  $location = getLocation();
 $currency = country::find($location->country_id)->currency;

 return $currency;
}



function send_email_with_default_params($type , $title , $body , $code , $to)
{
    $params = [
        'type' => $type,
        'title' => $title,
        'body' => $body,
        'code' => $code,
        'to' => $to
    ];
    $email = new emailController();
    $email->Send($params);
}


function verify_identity($identity_img , $personal_img){
    $coreapi = new coreApi(env("IDENTITY_VERIFY_KEY"), "US");
    $coreapi->enableAuthentication(true, 'quick');
    $result = $coreapi->scan ($identity_img,'',$personal_img,);



    // $data_result = $result['result'];
    // $authentication_result = $result['authentication'];
    $face_result = $result['face'] ?? null;


    $success = 0;
    $msg = '';
    // return $face_result;
    // Print result

    // // Parse document authentication results
    // if($authentication_result){
    //     if($authentication_result['score'] > 0.5) {
    //         echo("The document uploaded is authentic<br>");
    //     }else if($authentication_result['score'] > 0.3){
    //         echo("The document uploaded looks little bit suspicious<br>");
    //     }else{
    //         echo("The document uploaded is fake<br>");
    //     }
    // }
    // Parse face verification results
    if($result['error']){
               $success = 0;
             $msg = $result['error']['message'];
    }else{
         if($face_result){
        if(isset($face_result['error'])){
            // View complete error codes under API reference: https://developer.idanalyzer.com/coreapi.html
            // echo("Face verification failed! Code: {$face_result['error']}, Reason: {$face_result['error_message']}<br>");
            $msg = $face_result['error_message'];
        }else{
            if($face_result['isIdentical'] === true){
              $success = 1;
              $msg = 'identical';
            }else{
                $success = 0;
              $msg = 'not identical';
            }
            // echo("Similarity score: {$face_result['confidence']}<br>");
        }
    }
    }

    return ['success'=>$success ,'msg'=>$msg];
}

function check_join_code($code){

    return marketer::where('join_code' , $code)->count();

}
function check_sp_code($code){

    return serviceProvider::where('sp_code' , $code)->count();

}

function check_sp_category($category_id , $code){
    return serviceProvider::where('sp_code' , $code)->where('category_id'  , $category_id)->count();
}

function auth_user($guard = 'sanctum'){
    return Auth($guard)->user();
}

// get marketer total for handovers
function get_market_total($handovers = []){
$market_percentage =  systemConfiguration::latest('id')->first()->salesAcceptPercentage;
$total = 0;

$leader_percentage = 0;

if(auth_user()->role == 1)
$leader_percentage = systemConfiguration::latest('id')->first()->salesLeaderAcceptPercentage;

foreach ($handovers as $handover) {
$total += ($handover['admin_share']/100)*$market_percentage;
}
$marketer_total = ($total) - (($total/100)*$leader_percentage);
$leader_total =  (($total/100)*$leader_percentage);

return  [ 'marketer_total'=>floor($marketer_total) , 'leader_total'=>$leader_total];

}

// get marketer stats for his sps in spicefic period
function get_marketer_sps_stats(marketer $marketer , $at = null){

    $sps = $marketer->serviceproviders;
    $response['sp_count'] = $sps->count();
    $response['sp_total_income'] = 0;
    foreach($sps as $sp)
    {
        $sp->total_income = get_market_total($sp->handovers($at))['marketer_total'];
        $sp->total_offers = count($sp->handovers($at));
        $response['sp_total_income'] +=$sp->total_income;
    }

    $response['services_providers'] = $sps->values();

    return $response;
}


function upload_public($key , $path){
    $file = request()->file($key);
    $org_file_name = $file->getClientOriginalName();
    $file_name = time() . '.' .pathinfo($org_file_name, PATHINFO_EXTENSION);
    $file->move(public_path($path),$file_name);
    return $file_name;
}
