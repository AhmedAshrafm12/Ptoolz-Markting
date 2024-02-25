<?php

namespace App\Http\Controllers\marketer;

use App\Http\Controllers\Controller;
use App\Models\users\marketer;
use Illuminate\Http\Request;

class profileController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $marketer = marketer::findorfail(auth_user()->id);
        return apiresponse(true ,200 ,'success' , $marketer->myprofile());
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
     * @param  \App\Models\users\marketer  $marketer
     * @return \Illuminate\Http\Response
     */
    public function show(marketer $marketer)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\users\marketer  $marketer
     * @return \Illuminate\Http\Response
     */
    public function edit(marketer $marketer)
    {
      return  $marketer->edit();
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\users\marketer  $marketer
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, marketer $marketer)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\users\marketer  $marketer
     * @return \Illuminate\Http\Response
     */
    public function destroy(marketer $marketer)
    {
        //
    }
}
