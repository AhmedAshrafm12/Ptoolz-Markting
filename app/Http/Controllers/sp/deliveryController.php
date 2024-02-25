<?php

namespace App\Http\Controllers\sp;

use App\Models\offer\offer;
use App\Models\order\order;
use Illuminate\Http\Request;
use App\Models\order\orderDelivery;
use App\Models\users\data\location;
use Illuminate\Support\Facades\App;
use App\Http\Controllers\Controller;
use App\Models\users\serviceProvider;
use App\Models\order\photogrphyUpdates;
use App\Http\Controllers\customer\orderTransactionsController;

class deliveryController extends Controller
{
    function __construct()
    {
        if (getRequestLanguage()  != 'en')
            App::setLocale(getRequestLanguage());
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {


        $offer_id = request()->offer_id;
        $offer = offer::with('user')->find($offer_id);
        if ($offer->user->id == Auth('sanctum')->user()->id) {
            $offer = offer::with('user')->find($offer_id);
            $order = $offer->order;

            if ($order->category_id == 3) {
                $location = $offer->user->location;
                $sp_location = location::with('country')->with('city')->with('region')->find($location->id);
                $sp_name = $offer->user->full_name;
                $photography_updates =  $order->photogarphy_updates;
                return apiresponse(true, 200, 'success',  compact('sp_location', 'sp_name', 'photography_updates'));
            }

            $orderDelivery = $order->delivery;
            $location = location::with('country')->with('city')->with('region')->find($orderDelivery->from_location);
            $orderDelivery->from_location = $location;
            $orderDelivery->to_location = orderDelivery::with('country')->with('city')->with('region')->where('id', $orderDelivery->id)->first();
            //    $orderDelivery->to_location = $orderDelivery->get();
            //   $orderDelivery->sp = serviceProvider::where( 'user_key' , $location->user_key)->first();
            return apiresponse(true, 200, 'success', compact('orderDelivery', 'offer'));
        }
        return apiresponse(false, 200, __('auth.unAuthorized'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\order\orderDelivery  $orderDelivery
     * @return \Illuminate\Http\Response
     */
    public function show(orderDelivery $orderDelivery)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\order\orderDelivery  $orderDelivery
     * @return \Illuminate\Http\Response
     */
    public function edit(orderDelivery $orderDelivery)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\order\orderDelivery  $orderDelivery
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, orderDelivery $orderDelivery)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\order\orderDelivery  $orderDelivery
     * @return \Illuminate\Http\Response
     */
    public function destroy(orderDelivery $orderDelivery)
    {
        //
    }
    public function confirm_recieve(offer $offer)
    {
        $user = serviceProvider::find(Auth('sanctum')->user()->id);
        $order = $offer->order;
        $orderDelivery = $order->delivery;

        if ($user->cannot('sendUpdate', $offer)) {
            return apiresponse(false, 200, "forbidden action");
        }
        $orderDelivery->sp_received = 1;
        $orderDelivery->sp_received_at = now();
        $orderDelivery->save();

        return apiresponse(true, 200, 'success');
    }



    public function update_in_way(offer $offer)
    {
        $user = serviceProvider::find(Auth('sanctum')->user()->id);
        if ($user->cannot('sendUpdate', $offer)) {
            return apiresponse(false, 200, "forbidden action");
        }

        $order = $offer->order;
        if ($offer->order->category_id == 1) {
            $order = $offer->order;
            $orderDelivery = $order->delivery;
            $orderDelevery = orderDelivery::find($orderDelivery->id);
            $orderDelevery->in_way_at = now();
            $orderDelevery->in_way     = 1;
            $orderDelevery->save();
        } elseif ($offer->order->category_id == 3) {

            $photo_updates = photogrphyUpdates::where('order_id', $offer->order->id)->first();
            $photo_updates->in_way =  1;
            $photo_updates->in_way_at = now();
            $photo_updates->save();
        }

        // notify customer
        $topic = set_user_topic($order->client_id);
        $payload = set_payload(__('notifiactions.sp_in_way_title'), __('notifiactions.sp_in_way_body'), 'sp_in_way', $order->id, $order->user->user_key);
        send_to_topic($topic, $payload);

        return apiresponse(true, 200, 'success');
    }


    public function hand_over(offer $offer)
    {
        $code = request()->qr_code;
        $user = serviceProvider::find(Auth('sanctum')->user()->id);
        $order = $offer->order;
        $orderDelivery = $order->delivery;

        if ($user->cannot('sendUpdate', $offer)) {
            return apiresponse(false, 200, "forbidden action");
        }


        if ($code == $orderDelivery->qr_code) {
            $offer = $orderDelivery->order->offers->where('status', 2)->first();
            $transactionController = new orderTransactionsController();
            $transactionController->handover_transactions($offer);
            $orderDelevery = orderDelivery::find($orderDelivery->id);
            $orderDelevery->activity = 0;
            $orderDelevery->customer_received_at = now();
            $orderDelevery->customer_received = 1;
            $orderDelevery->save();

            // notify customer
            $topic = set_user_topic($order->client_id);
            $payload = set_payload(__('notifiactions.order_handoverd_title'), __('notifiactions.order_handoverd_body'), 'order_handoverd', '', $order->user->user_key);
            send_to_topic($topic, $payload);
            // notify sp
            $topic = set_user_topic($offer->user_id);
            $payload = set_payload(__('notifiactions.offer_handoverd_title'), __('notifiactions.offer_handoverd_body'), 'offer_handoverd','', $offer->user->user_key);
            send_to_topic($topic, $payload);


            return apiresponse(true, 200, 'handoverd_successfully');
        }
        return apiresponse(false, 200, "forbidden action");
    }




    public function confirm_arrival(offer $offer)
    {
        $code = request()->qr_code;
        $user = serviceProvider::find(Auth('sanctum')->user()->id);
        $order = $offer->order;
        $photo_updates = photogrphyUpdates::where('order_id', $order->id)->first();

        if ($user->cannot('sendUpdate', $offer)) {
            return apiresponse(false, 200, "forbidden action");
        }


        if ($code == $photo_updates->qr_code) {
            $photo_updates->sp_arrival =  1;
            $photo_updates->sp_arrival_at = now();
            $photo_updates->save();
            return apiresponse(true, 200, 'success');
        }
        return apiresponse(false, 200, "forbidden action");
    }
}
