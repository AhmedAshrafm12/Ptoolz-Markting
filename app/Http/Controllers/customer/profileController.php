<?php

namespace App\Http\Controllers\customer;

use App\Models\data\city;
use App\Models\data\region;
use App\Models\data\country;
use Illuminate\Http\Request;
use App\Models\users\customer;
use App\Http\Controllers\Controller;

class profileController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = customer::with(['location' ,'location.country', 'location.city', 'location.region'])->findOrfail(Auth('sanctum')->user()->id);
        // $profile  =  $user;
        //  $profile->location['country'] = country::find($user->location->country);
        //  $profile->location['city'] = city::find($user->location->city);
        //  $profile->location['region'] = region::find($user->location->region);
        return apiresponse(true , 200 , 'success' , $user);
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
     * @param  \App\Models\users\customer  $customer
     * @return \Illuminate\Http\Response
     */
    public function show(customer $customer)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\users\customer  $customer
     * @return \Illuminate\Http\Response
     */
    public function edit(customer $customer)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\users\customer  $customer
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, customer $customer)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\users\customer  $customer
     * @return \Illuminate\Http\Response
     */
    public function destroy(customer $customer)
    {
        //
    }

    public function update_profile(){
        $user  =customer::findOrfail(Auth('sanctum')->user()->id);
        return $user->edit($user);

       }
}
