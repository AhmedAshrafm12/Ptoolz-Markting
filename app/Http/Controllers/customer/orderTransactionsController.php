<?php

namespace App\Http\Controllers\customer;

use App\Models\offer\offer;
use Illuminate\Http\Request;
use App\Models\users\customer;
use Illuminate\Support\Facades\App;
use App\Http\Controllers\Controller;
use App\Models\transactions\holdtransaction;
use App\Models\transactions\adminTransaction;
use App\Models\transactions\chargetransaction;
use App\Models\transactions\acceptedtransaction;
use App\Models\transactions\handovertransaction;
use App\Models\transactions\spaccounttransaction;
use App\Models\transactions\clientaccounttransaction;

class orderTransactionsController extends Controller
{



    function __construct()
    {
        if (getRequestLanguage()  != 'en')
            App::setLocale(getRequestLanguage());
    }


    public function accept_and_hold(offer $offer)
    {

        $user  = customer::find(Auth('sanctum')->user()->id);
        // return request()->user();
        if ($user->cannot('acceptAndHold', $offer)) {
            return apiresponse(false, 401, __('auth.unAuthorized'));
        }


        $userBalance  =  $user->currentBalance();
        $cost = offerCost($offer);
        if ($cost > $userBalance)
            return apiresponse(false, 200, "no enough balance" ,env('PAYMENT_URL'));

        // delivery check
        //......
        $order = $offer->order;


        // update order status

        $order->status = 2;

        // update offer status

        $offer->status = 2;
        $offer->save();


        $systemConfig = systemConfig($cost);

        // set activation time


        if ($order->categoryId == 3)
            $order->active_time = $order->occasionTime;
        else
            $order->active_time = date('Y-m-d H:i:s');




        $order->save();


        // add hold transaction

        holdtransaction::create([
            "offer_id" => $offer->id,
            "value" => $cost,
            "holded" => 1,
        ]);

        acceptedtransaction::create([
            "offer_id" => $offer->id,
            "sp_percentage" => $systemConfig['spAcceptPercentage'],
            "admin_percentage" => $systemConfig['adminAcceptPercentage'],
            "sp_share" => $systemConfig['spShare'],
            "admin_share" => $systemConfig['adminshare'],

        ]);

        clientaccounttransaction::create([
            "offer_id" => $offer->id,
            "user_id" => Auth('sanctum')->user()->id,
            "value" => $cost,
            "type_id" => 2,
        ]);

// notify sp

$topic = set_user_topic($offer->user_id);
$payload = set_payload(__('notifiactions.offer_accepted_title'), __('notifiactions.offer_accepted_body'), 'offer_accepted', $offer->id, $offer->user->user_key);
send_to_topic($topic, $payload);


// notify customer

$topic = set_user_topic($order->client_id);
$payload = set_payload(__('notifiactions.cost_holded_title'), __('notifiactions.cost_holded_body'), 'cost_holded', '', $order->user->user_key);
send_to_topic($topic, $payload);



        return apiResponse(true, 200, 'success');
    }


    public function charge()
    {

        chargetransaction::create([
            "userAccountId" => Auth('sanctum')->user()->id,
            "value" => request()->value,
        ]);

        return apiresponse(true, 200, 'successful chareg');
    }


    public function handover(offer $offer)
    {
        if ($offer->status == 3)
            return apiResponse('0', 'forbidden action', '200');


        if (isset(request()->previewId)) {
            $preview = preview::find(request()->previewId);
            $preview->status = 1;
            $preview->accept = 1;
            $preview->save();
        }
        $order = $offer->order;
        $model =  getOrderTypeModel($order->orderTypeId);
        $orderModel  = new $model();

        if ($orderModel->delivery  ==  1) {         /// order with delivery

            if ($offer->delivery  == 1) {      ///  sp will deliver
                $orderDelivery  = orderDelivery::where("orderId", $order->id);
                $orderDelivery->status = 2;
                $orderDelivery->spReceived = 1;
                $orderDelivery->save();
            } else {                            /// sp will not deliver

                $orderDelivery  = orderDelivery::where("orderId", $order->id);
                $orderDelivery->status = 1;
                $orderDelivery->spReceived = 0;
                $orderDelivery->save();

                $offer->satatus = 3;
                $offer->save();
            }
        } else {                                   /// order has no delivery
            // perform transactions
            $this->handoverTransactions($offer);

            return apiResponse('1', 'success', '200');
        }
    }


    public function handover_transactions(offer $offer)
    {

        $order = $offer->order;

        $acceptedTransaction = acceptedtransaction::where("offer_id", $offer->id)->first();
        $holdtransaction = holdtransaction::where("offer_id", $offer->id)->first();
        ///   submit transactions

        handovertransaction::create(
            [
                'offer_id' => $offer->id,
                'sp_percentage' => $acceptedTransaction->sp_percentage,
                'admin_percentage' => $acceptedTransaction->admin_percentage,
                'sp_share' => $acceptedTransaction->sp_share,
                'admin_share' => $acceptedTransaction->admin_share,
                'hold_value' => $holdtransaction->value
            ]
        );

        adminTransaction::create([
            'offer_id' => $offer->id,
            'value' => $acceptedTransaction->admin_share,
            'type_id' => 1
        ]);

        spaccounttransaction::create([
            'offer_id' => $offer->id,
            'user_id' => $offer->user_id,
            'value' => $acceptedTransaction->sp_share,
            'type_id' => 1,
        ]);



        // updates
        $clientTransaction = clientaccounttransaction::where("offer_id", $offer->id)->update(
            ['type_id' => 1]
        );
        // $clientTransaction = clientaccounttransaction::where("offerId", $offer->id)->first();
        // $clientTransaction->transactionTypeId = 1;
        // $clientTransaction->save();


        $holdTransaction = holdtransaction::where("offer_id", $offer->id)->update(
            ['holded' => 0]
        );
        // $holdTransaction->holded = 0;
        // $holdTransaction->save();


        $order->status = 3;
        $order->save();


        $offer->status = 3;
        $offer->save();

    // notify customer
    $topic = set_user_topic($order->client_id);
    $payload = set_payload(__('notifiactions.order_handoverd_title'), __('notifiactions.order_handoverd_body'), 'order_handoverd', '', $order->user->user_key);
    send_to_topic($topic, $payload);

    // notify sp
    $topic = set_user_topic($offer->user_id);
    $payload = set_payload(__('notifiactions.offer_handoverd_title'), __('notifiactions.offer_handoverd_body'), 'offer_handoverd','', $offer->user->user_key);
    send_to_topic($topic, $payload);

    }

    public function handoverDelivery(offer  $offer)
    {

        /// check if the sp is delivery sp
        // update orderdelivery
        // update order
        // update offer





        /// if sp is not delivery
        // update order
        // update delivery
        // hand over delivery and




        // hand over offer

    }


}
