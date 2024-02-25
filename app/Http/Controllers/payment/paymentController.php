<?php

namespace App\Http\Controllers\payment;

use App\Models\offer\offer;
use Illuminate\Http\Request;
use App\Models\users\customer;
use Illuminate\Support\Facades\App;
use App\Http\Controllers\Controller;
use App\Models\users\serviceProvider;
use App\Models\order\eductional\reference;
use App\Models\transactions\chargetransaction;
use App\Models\transactions\withdrawaltransaction;
use App\Http\Controllers\customer\referenceController;

class paymentController extends Controller
{

    function __construct()
    {
        if (getRequestLanguage()  != 'en')
            App::setLocale(getRequestLanguage());
    }


    public  function index(offer $offer)
    {

        $user = customer::find(Auth('sanctum')->user()->id);
        $totalCost = offerCost($offer);
        $userBalance  =  $user->currentBalance();

        if ($userBalance >= $totalCost) {
            //    $acceptQutation =  acceptQutation($quotationId);
            $status = 200;
        } else {
            $amount =  $totalCost - $userBalance;
            $status = 100;
            $amount =  abs($amount) ;
            $amount = ($amount + 1) * 100;
            //  echo  $amount = floatval( $amount);
            // int $amount;
            // $amount = (float) $amount;


        }
        if ($status == 100) {
            $data = merchant_helper();
            $merchant_reference = '2e-' . $amount . '-pid-' . $offer->id . '-recharge-' . $user->id;
            $requestParams = array(
                'command' => 'PURCHASE',
                'access_code' => $data['access_code'],
                'merchant_identifier' => $data['merchant_identifier'],
                'merchant_reference' => $merchant_reference,
                'amount' => $amount,
                'currency' => $data['currency'],
                'language' => $data['language'],
                'customer_email' => $user->email,
                'return_url' => $data['return_url'],
            );
            ksort($requestParams);
            $shaString = '';
            foreach ($requestParams as $key => $value) {
                $shaString .= "$key=$value";
            }
            $shaString = $data['hash'] . $shaString . $data['hash'];
            $signature = hash("sha256", $shaString);

            return view('payment.index', ['data' => $data, 'user' => $user, 'amount' => $amount, 'shaString' => $shaString, 'signature' => $signature, 'merchant_reference' => $merchant_reference]);
        }


        // echo $status;

    }

    public function proccess()
    {
        $status = 0;
        //   return "test";
        $processMerchantPageResponse =  processMerchantPageResponse();
        $message = $processMerchantPageResponse['message'];
        $response_code = $processMerchantPageResponse['response_code'];
        $statusAPI = $processMerchantPageResponse['statusAPI'];
        $statusWEB = $processMerchantPageResponse['statusWEB'];
        $amount = $processMerchantPageResponse['amount'];
        $merchantReference = $processMerchantPageResponse['merchantReference'];
        $userAccountId =  strstr($merchantReference, "-recharge-");
        $pid =  strstr($merchantReference, "-pid-");
        $pid =  str_replace('-pid-', '', $pid);
        $pid = explode("-recharge-", $pid);
        $pid =  $pid[0];
        //    $getSptIdOfQuotation =  getSptIdOfQuotation($pid);
        $offer = offer::find($pid);
        $user = $offer->order->user;
        $amount = $amount/100;
        //    $orderId = $getSptIdOfQuotation['orderId'];
        //    $userAccountId =  getUserAccountIdOfOrder($orderId);
        //    $userAccount = getInfoAccount('customerAccount', $userAccountId);
        $customer_email = $user->email;
        $mobile = $user->mobile;
        $fullName = $user->full_name;
        $userName = $user->userName;

        $url_send = 'https://ptoolz.com/Controller/transactions/_AcceptAndHoldTransaction.php';
        $data = array(
            'userId' => $user->id,
            'quotationId' => $offer->id,
        );
        // $res =  httpPost($url_send, $data) ;


        $search = '-recharge-';
        $userAccountId = str_replace($search, '', $user->id);
        if ($message == 'عملية ناجحة') {
            chargetransaction::create([
                "user_id" => $user->id,
                "value" => $amount,
            ]);

            $status = 1;

            $topic = set_user_topic($user->id);
            $payload = set_payload(__('notifiactions.balance_charge_title'), __('notifiactions.balance_charge_body'), 'balance_charge', '', $user->user_key);
            send_to_topic($topic, $payload);
        }
        return view('payment.proccess', compact('message', 'status', 'fullName', 'amount'));
    }



    public function wallet()
    {
        if (Auth('sanctum')->user()->account_type_id == 1) {
            $user = customer::find(Auth('sanctum')->user()->id);

            return   apiresponse(true, 200, 'success', $user->wallet());
        } elseif (Auth('sanctum')->user()->account_type_id == 2) {
            $user = serviceProvider::find(Auth('sanctum')->user()->id);

            return   apiresponse(true, 200, 'success', $user->wallet());
        }
    }
    public function balance()
    {
        if (Auth('sanctum')->user()->account_type_id == 1) {
            $user = customer::find(Auth('sanctum')->user()->id);

            return   apiresponse(true, 200, 'success', round($user->currentBalance()));
        } elseif (Auth('sanctum')->user()->account_type_id == 2) {
            $user = serviceProvider::find(Auth('sanctum')->user()->id);

            return   apiresponse(true, 200, 'success', round($user->currentBalance()));
        }
    }



    public function reference_index(reference $reference){

        $user = customer::find(Auth('sanctum')->user()->id);
        $totalCost = reference_cost($reference);

        $userBalance  =  $user->currentBalance();

        if ($userBalance >= $totalCost) {
            //    $acceptQutation =  acceptQutation($quotationId);
            $status = 200;
        } else {
            $amount =  $totalCost - $userBalance;
            $status = 100;
            $amount =  abs($amount) ;
            $amount = ($amount + 1) * 100;
            //  echo  $amount = floatval( $amount);
            // int $amount;
            // $amount = (float) $amount;


        }
        if ($status == 100) {
            $data = reference_merchant_helper();
            $merchant_reference = '2e-' . $totalCost . '-ref-' . $reference->id . '-recharge-' . $user->id;
            $requestParams = array(
                'command' => 'PURCHASE',
                'access_code' => $data['access_code'],
                'merchant_identifier' => $data['merchant_identifier'],
                'merchant_reference' => $merchant_reference,
                'amount' => $amount,
                'currency' => $data['currency'],
                'language' => $data['language'],
                'customer_email' => $user->email,
                'return_url' => $data['return_url'],
            );
            ksort($requestParams);
            $shaString = '';
            foreach ($requestParams as $key => $value) {
                $shaString .= "$key=$value";
            }
            $shaString = $data['hash'] . $shaString . $data['hash'];
            $signature = hash("sha256", $shaString);

            return view('payment.index', ['data' => $data, 'user' => $user, 'amount' => $amount, 'shaString' => $shaString, 'signature' => $signature, 'merchant_reference' => $merchant_reference]);
        }



    }


    public function reference_proccess()
    {
        $status = 0;
        //   return "test";
        $processMerchantPageResponse =  processMerchantPageResponse();
        $message = $processMerchantPageResponse['message'];
        $response_code = $processMerchantPageResponse['response_code'];
        $statusAPI = $processMerchantPageResponse['statusAPI'];
        $statusWEB = $processMerchantPageResponse['statusWEB'];
        $amount = $processMerchantPageResponse['amount'];
        $merchantReference = $processMerchantPageResponse['merchantReference'];
        $userAccountId =  strstr($merchantReference, "-recharge-");
        $pid =  strstr($merchantReference, "-ref-");
        $pid =  str_replace('-ref-', '', $pid);
        $ids = explode("-recharge-", $pid);
        // return $pid;
        $pid =  $ids[0];
        $userAccountId  = $ids[1];
        //    $getSptIdOfQuotation =  getSptIdOfQuotation($pid);
        $reference = reference::find($pid);
        $user = customer::find($userAccountId);
        //    $orderId = $getSptIdOfQuotation['orderId'];
        //    $userAccountId =  getUserAccountIdOfOrder($orderId);
        //    $userAccount = getInfoAccount('customerAccount', $userAccountId);
        $customer_email = $user->email;
        $mobile = $user->mobile;
        $fullName = $user->full_name;
        $userName = $user->userName;
        $amount = $amount/100;


        // $url_send = 'https://ptoolz.com/Controller/transactions/_AcceptAndHoldTransaction.php';
        // $data = array(
        //     'userId' => $user->id,
        //     'quotationId' => $offer->id,
        // );
        // $res =  httpPost($url_send, $data) ;


        $search = '-recharge-';

        $userAccountId = str_replace($search, '', $user->id);
        if ($message == 'عملية ناجحة') {
            // chargetransaction::create([
            //     "user_id" => $user->id,
            //     "value" => $amount,
            // ]);

            // $referenceController = new referenceController();
            //  $referenceController->handover_reference($reference , $userAccountId);
            chargetransaction::create([
                "user_id" => $user->id,
                "value" => $amount,
            ]);
            $status = 1;

            $topic = set_user_topic($user->id);
            $payload = set_payload(__('notifiactions.balance_charge_title'), __('notifiactions.balance_charge_body'), 'balance_charge', '', $user->user_key);
            send_to_topic($topic, $payload);
        }
        return view('payment.proccess', compact('message', 'status', 'fullName', 'amount'));
    }






    public  function charge_index(offer $offer)
    {

        $user = customer::find(Auth('sanctum')->user()->id);
         $amount = request()->value;
         $amount = ($amount) * 100;
        $amount = (float) $amount;
        $amount = $amount  ;
        //     //  echo  $amount = floatval( $amount);
        // $userBalance  =  $user->currentBalance();

        // if ($userBalance >= $totalCost) {
        //     //    $acceptQutation =  acceptQutation($quotationId);
        //     $status = 200;
        // } else {
        //     $amount =  $userBalance - $totalCost;
        //     $status = 100;
        //     $amount =  abs($amount);
        //     $amount = $amount * 100;
        //     //  echo  $amount = floatval( $amount);
        //     // int $amount;
        //     // $amount = (float) $amount;


        // }
        // if ($status == 100) {
            $data = charge_merchant_helper();
            $merchant_reference = '2e-' . $amount . '-user-' . $user->id . '-recharge-' .rand(0,100);
            $requestParams = array(
                'command' => 'PURCHASE',
                'access_code' => $data['access_code'],
                'merchant_identifier' => $data['merchant_identifier'],
                'merchant_reference' => $merchant_reference,
                'amount' => $amount,
                'currency' => $data['currency'],
                'language' => $data['language'],
                'customer_email' => $user->email,
                'return_url' => $data['return_url'],
            );
            ksort($requestParams);
            $shaString = '';
            foreach ($requestParams as $key => $value) {
                $shaString .= "$key=$value";
            }
            $shaString = $data['hash'] . $shaString . $data['hash'];
            $signature = hash("sha256", $shaString);

            return view('payment.index', ['data' => $data, 'user' => $user, 'amount' => $amount, 'shaString' => $shaString, 'signature' => $signature, 'merchant_reference' => $merchant_reference]);
        // }


        // echo $status;

    }



    public function charge_proccess()
    {
        $status = 0;
        $processMerchantPageResponse =  processMerchantPageResponse();
        $message = $processMerchantPageResponse['message'];
        $response_code = $processMerchantPageResponse['response_code'];
        $statusAPI = $processMerchantPageResponse['statusAPI'];
        $statusWEB = $processMerchantPageResponse['statusWEB'];
        $amount = $processMerchantPageResponse['amount'];
        $merchantReference = $processMerchantPageResponse['merchantReference'];
        $merch_array =  explode( '-' ,$merchantReference);
        $user_id =  $merch_array[3];
        $amount = $amount/100;
        // return $user_id;
        // $pid =  strstr($merchantReference, "-user-");
        // $pid =  str_replace('-user-', '', $pid);
        // $ids = explode("-recharge-", $pid);
        // $pid =  $ids[0];
        // $userAccountId  = $ids[1];
        // $reference = reference::find($pid);
        // $user = customer::find($userAccountId);
         $user = customer::find($user_id);
        $customer_email = $user->email;
        $mobile = $user->mobile;
        $fullName = $user->full_name;
        $userName = $user->userName;



        $search = '-recharge-';

        // $userAccountId = str_replace($search, '', $user->id);
        if ($message == 'عملية ناجحة') {
            chargetransaction::create([
                "user_id" => $user_id,
                "value" => $amount,
            ]);

        //     $referenceController = new referenceController();
        //      $referenceController->handover_reference($reference , $userAccountId);

         $status = 1;

         $topic = set_user_topic($user->id);
         $payload = set_payload(__('notifiactions.balance_charge_title'), __('notifiactions.balance_charge_body'), 'balance_charge', '', $user->user_key);
         send_to_topic($topic, $payload);
        }
        return view('payment.proccess', compact('message', 'status', 'fullName', 'amount'));
    }

    public function withdrawl(){

         $withdrawl = new withdrawaltransaction();
        return  $withdrawl->store();

    }
}
