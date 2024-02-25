<?php

namespace App\Http\Controllers\sp;

use App\Models\offer\offer;
use App\Models\order\update;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use App\Http\Controllers\Controller;
use App\Models\users\serviceProvider;
use App\Models\order\photogrphyUpdates;

class updatesController extends Controller
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
        $offer = offer::find(request()->offer_id);
        $user =serviceProvider::find(Auth('sanctum')->user()->id);
        if ($user->cannot('showUpdates', $offer)) {
            return apiresponse(false, 200, "forbidden action");
        }

        return apiresponse(true , 200 , 'success' , $offer->updates);


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

        $user =serviceProvider::find(Auth('sanctum')->user()->id);
        $offer = offer::find(request()->offer_id);

        // if ($user->cannot('sendUpdate', $offer)) {
        //     return apiresponse(false, 200, "forbidden action");
        // }

       $update = new update();
      return $update->store();
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
        //
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
        //
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

    public function confirm_arrival(offer $offer)
    {
         $code = request()->qr_code;
         $user =serviceProvider::find(Auth('sanctum')->user()->id);
         $order = $offer->order;
         $photo_updates = photogrphyUpdates::where('order_id' , $order->id)->first();
         $photo_updates->sp_arrival =  1;
         $photo_updates->sp_arrival_at = now();

         if ($user->cannot('sendUpdate', $offer)) {
            return apiresponse(false, 200, "forbidden action");
        }


         if ($code == $photo_updates->qr_code) {
            $photo_updates->sp_arrival = 1 ;
            return apiresponse(true, 200, 'handoverd_successfully');
        }
        return apiresponse(false, 200, "forbidden action");
    }
}
