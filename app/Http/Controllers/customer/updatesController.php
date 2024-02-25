<?php

namespace App\Http\Controllers\customer;

use App\Models\offer\offer;
use App\Models\order\order;
use App\Models\order\update;
use Illuminate\Http\Request;
use App\Models\users\customer;
use App\Models\order\orderDelivery;
use Illuminate\Support\Facades\App;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class updatesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    // function __construct() {
    //     $user =customer::find(Auth('sanctum')->user()->id);


    // }

    function __construct()
    {
        if (getRequestLanguage()  != 'en')
            App::setLocale(getRequestLanguage());
    }


    public function index()
    {

        $order = order::findorfail(request()->order_id);
        $user = customer::find(Auth('sanctum')->user()->id);

        if ($order->status != 2 && $order->status  != 3)
            return apiresponse(false, 401, __('auth.unAuthorized'));
        $offer = $order->offers()->where(function ($query) {
            $query->where('status', '=', 2)
                ->orWhere('status', '=', 3);
        })->first();

        $user = customer::find(Auth('sanctum')->user()->id);
        if ($user->cannot('show_updates', $order)) {
            return apiresponse(false, 200, __("auth.unAuthorized"));
        }
        foreach ($offer->updates as $update) {
            $update->file_name  = $update->file;
            $update->file = env('UPDATES_STRORAGE_LINK') . '/' . $update->file;
        }
        return  apiresponse(true, 200, 'success', $offer->updates);
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
     * @param  \App\Models\order\update  $update
     * @return \Illuminate\Http\Response
     */
    public function show(update $update)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\order\update  $update
     * @return \Illuminate\Http\Response
     */
    public function edit(update $update)
    {
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\order\update  $update
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, update $update)
    {
        $offer = $update->offer;
        $user = customer::find(Auth('sanctum')->user()->id);
        if ($user->cannot('perform_updates', $offer->order)) {
            return apiresponse(false, 401, __('auth.unAuthorized'));
        }

        return  $update->decline();
    }


    public function accept_update(update $update)
    {



        $user = customer::find(Auth('sanctum')->user()->id);
        $offer = $update->offer;

        if ($user->cannot('perform_updates', $offer->order)) {
            return apiresponse(false, 401, __('auth.unAuthorized'));
        }


        $offer = $update->offer;
        $order = $offer->order;


        $transactionController = new orderTransactionsController();




        if ($order->category_id == 1) {
            if ($offer->delivery  ==  1) {

                $update->accept();

                $delivery = $offer->order->delivery;
                $orderDelevery = orderDelivery::find($delivery->id);
                $orderDelevery->activity = 1;
                $orderDelevery->sp_received_at = now();
                $orderDelevery->sp_received = 1;
                $orderDelevery->from_location = $offer->user->location->id;
                $orderDelevery->save();

             // notify sp

            $topic = set_user_topic($offer->user_id);
            $payload = set_payload(__('notifiactions.accept_update_title'), __('notifiactions.accept_update_body'), 'accept_update',$offer->id, $offer->user->user_key);
            send_to_topic($topic, $payload);

            return apiresponse(true, 200, 'success');

            }
            elseif ($offer->delivery == 0) {
                //  perform handover
                $update->accept();

                $validator = Validator::make(
                    request()->all(),
                    [
                        'title' => 'required',
                        'description' => 'required'
                    ],
                    validationMessages()
                );

                if ($validator->fails())
                    return apiresponse(false, 200, $validator->errors()->first());


                $delivery = $offer->order->delivery;

                create_auto_delivery_order($delivery);

                $transactionController->handover_transactions($update->offer);
            }

        } elseif ($order->category_id ==  3) {

            $update->accept();
            $transactionController->handover_transactions($offer);

        }



        return apiresponse(true, 200, 'handoverd successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\order\update  $update
     * @return \Illuminate\Http\Response
     */
    public function destroy(update $update)
    {
        //
    }
}
