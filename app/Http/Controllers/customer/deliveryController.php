<?php

namespace App\Http\Controllers\customer;

use App\Models\order\order;
use Illuminate\Http\Request;
use App\Models\users\customer;
use App\Models\order\orderDelivery;
use App\Models\users\data\location;
use App\Http\Controllers\Controller;

class deliveryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $order_id = request()->order_id;
        $orderDelivery = orderDelivery::where('order_id', $order_id)->first();
        $order  = order::find($order_id);
        $offer  = $order->offers()->with('user')->where('status','!=' , 1)->first();
        if($order->client_id == Auth('sanctum')->user()->id){


        if($order->category_id == 3)
        {
            $location = $offer->user->location;
            $sp_location = location::with('country')->with('city')->with('region')->find($location->id);
            $sp_name = $offer->user->full_name;
            $photography_updates =  $order->photogarphy_updates;
            return apiresponse(true , 200 , 'success' ,  compact('sp_location' , 'sp_name' , 'photography_updates') );
        }

        $offer  = $order->offers()->with('user')->where('status','!=' , 1)->first();

    //    $sp = $offer->user;
    $location = location::with('country')->with('city')->with('region')->find($orderDelivery->from_location);
    $orderDelivery->from_location = $location;
    $orderDelivery->to_location = orderDelivery::with('country')->with('city')->with('region')->where('id',$orderDelivery->id)->first();
     return apiresponse(true, 200, 'success', compact('orderDelivery'  ,'offer'));

    }
    return apiresponse(false, 200 , 'forbidden');

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

    public function hand_over(orderDelivery $orderDelivery)
    {
         $code = request()->qr_code;
         $user = customer::find(Auth('sanctum')->user()->id);

         if ($user->cannot('perform_updates', $orderDelivery->order)) {
            return apiresponse(false, 200, "forbidden action");
         }
         if ($code == $orderDelivery->qr_code) {
            $offer = $orderDelivery->order->offers->where('status', 2)->first();
            $transactionController = new orderTransactionsController();
            $transactionController->handover_transactions($offer);
            return apiresponse(true, 200, 'handoverd_successfully');
        }
        return apiresponse(false, 200, "forbidden action");
    }
}
