<?php

namespace App\Http\Controllers\sp;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\users\serviceProvider;
use App\Models\order\eductional\reference;
use App\Models\order\eductional\university;
use App\Models\transactions\handovertransaction;
use App\Models\order\eductional\client_reference;
use App\Models\order\eductional\academicpermission;

class referenceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        $user = serviceProvider::find((Auth('sanctum')->user()->id));
        if ($user->category_id == 1) {
            $universities = university::all();
            $university_references = [];
            $data = [];
            $instructors = request()->instructor_id == null ?   $user->printer_instructors : collect([serviceProvider::find(request()->instructor_id)]);
            foreach ($universities as $university) {
                $out_university = new university();
                foreach ($university->references as $reference) {
                    if ($instructors->contains($reference->instructor)) {
                        array_push($university_references, $reference);
                    }
                }
                // return ;

                    $out_university->name = $university->name;
                    $out_university->id = $university->id;
                    $out_university->references = collect($university_references);
                    if($out_university->references->count()> 0){
                    array_push($data, $out_university);
                }

                //    var_dump($out_university);

            }

            return apiresponse(true, 200, 'success', $data);
        } elseif ($user->category_id == 10) {

            $global = (isset(request()->from_date) && isset(request()->to_date));
            $universities = university::all();
            $university_references = [];
            $data = [];
            foreach ($universities as $university) {
                $out_university = new university();
                $max = $global ? request()->to_date :  $university->references->max('created_at');
                $min = $global ? request()->from_date :  $university->references->min('created_at');
                $university->printer_references = $university->instructors_references()->where("created_at", '>=', $min)->where("created_at", '<=', $max)->orderBy('id' , 'desc')->get();
                $out_university->name = $university->name;
                $out_university->id = $university->id;
                $out_university->references = collect($university->printer_references);
                array_push($data, $out_university);
            }
            return apiresponse(true, 200, 'success', $data);
        }


        // return apiresponse(true, 200, 'success', $universities);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $user = serviceProvider::find((Auth('sanctum')->user()->id));
        $permession =  academicpermission::where('user_id', $user->id)->where('status', 1)->count();
        if ($user->category_id == 10 || $permession) {
            $reference = new reference();
            return  $reference->store();
        }
        return apiresponse(false, 401, __('auth.unAuthorized'));
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\order\eductional\reference  $reference
     * @return \Illuminate\Http\Response
     */
    public function show(reference $reference)
    {


        $user = serviceProvider::find((Auth('sanctum')->user()->id));

        if ($user->category_id == 1) {
            if ($user->printer_instructors->contains($reference->instructor)) {
                $count = $reference->downloads()->count();
                $clients = $reference->downloads;
                return apiresponse(true, 200, 'success', ['count' => $count, 'clients' => $clients]);
            }
        } else {
            if ($user->id == $reference->instructor->id) {
                $count = $reference->downloads()->count();
                $clients = $reference->downloads;
                return apiresponse(true, 200, 'success', ['count' => $count, 'clients' => $clients]);
            }
        }
        return  apiresponse(true, 401, __('auth.unAuthorized'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\order\eductional\reference  $reference
     * @return \Illuminate\Http\Response
     */
    public function edit(reference $reference)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\order\eductional\reference  $reference
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, reference $reference)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\order\eductional\reference  $reference
     * @return \Illuminate\Http\Response
     */
    public function destroy(reference $reference)
    {
        $user = serviceProvider::find((Auth('sanctum')->user()->id));

        if ($user->category_id == 1) {
            if ($user->printer_instructors->contains($reference->instructor)) {
                $reference->delete();
                return  apiresponse(true, 200, 'deleted successfully');
            }
        } else {
            if ($user->id == $reference->instructor->id) {
                $reference->delete();
                return  apiresponse(true, 200, 'deleted successfully');
            }
        }
        return  apiresponse(true, 401, __('auth.unAuthorized'));
    }

    public function academic_permission()
    {
        $user = serviceProvider::find((Auth('sanctum')->user()->id));
        if ($user->category_id == 1) {
         $permession =    $user->academicpermission()->create();
         $topic = set_user_topic($user->id);
         $payload = set_payload(__('notifiactions.academeic_permession_sent_title'), __('notifiactions.academeic_permession_sent_body'), 'academeic_permession_sent', '', $user->user_key);
         send_to_topic($topic, $payload);
            return apiresponse(true, 200, 'request sent' , academicpermission::find($permession->id));
        }

          // notify sp


        return apiresponse(false, 401, __('auth.unAuthorized'));
    }


    public function printer_instructors()
    {
        $user = serviceProvider::find((Auth('sanctum')->user()->id));

        if ($user->category_id == 1) {
            return  apiresponse(true, 200, 'success', $user->printer_instructors);
        }

        return apiresponse(false, 401, __('auth.unAuthorized'));
    }


    public function instructor()
    {
        if (Auth('sanctum')->user()->category_id == 10) {
            $user = serviceProvider::findorFail(Auth('sanctum')->user()->id);
            $in_come = $user->wallet()['avilable_balance'];
            $total_orders = client_reference::with('reference')->whereHas('reference', fn ($qry) => $qry->where('instructor_id', $user->id))->count();
            $today_orders = client_reference::with('reference')->whereHas('reference', fn ($qry) => $qry->where('instructor_id', $user->id))->whereDate('at', Carbon::today())->count();
            $pending_references = handovertransaction::with('purchase')->with('purchase.reference')->whereHas('purchase.reference', fn ($qry) => $qry->where('instructor_id', $user->id))->where('status', 0)->count();

            $pending_offers = handovertransaction::with('offer')->with('offer.order')->whereHas('offer', fn($qry) => $qry->where('user_id', $user->id))->where('status' , 0)->count();

            $pending_offers = $pending_offers + $pending_references;
            return apiresponse(true , 200 , 'success' , compact('in_come' , 'total_orders' , 'pending_offers'  , 'today_orders' ));
        }

    elseif((Auth('sanctum')->user()->category_id == 1)){
        $user = serviceProvider::find((Auth('sanctum')->user()->id));
        $instructors =  $user->printer_instructors;
        $in_come = 0;
        $today_orders = 0;
        $total_orders = 0;
        $pending_offers = 0;
        $pending_offers = 0;
        $pending_references = 0;
        foreach($instructors as $instructor){

        $in_come +=$user->wallet()['avilable_balance'];
        $today_orders += client_reference::with('reference')->whereHas('reference', fn ($qry) => $qry->where('instructor_id', $instructor->id))->whereDate('at', Carbon::today())->count();

        $today_orders = client_reference::with('reference')->whereHas('reference', fn ($qry) => $qry->where('instructor_id', $instructor->id))->whereDate('at', Carbon::today())->count();
        $total_orders += client_reference::with('reference')->whereHas('reference', fn ($qry) => $qry->where('instructor_id', $instructor->id))->count();

        $pending_references += handovertransaction::with('purchase')->with('purchase.reference')->whereHas('purchase.reference', fn ($qry) => $qry->where('instructor_id', $instructor->id))->where('status', 0)->count();

        $pending_offers += $pending_offers + $pending_references;
        return apiresponse(true , 200 , 'success' , compact('in_come' , 'total_orders' , 'pending_offers'  , 'today_orders' ));

        $pending_offers = $pending_offers + $pending_references;
        return apiresponse(true , 200 , 'success' , compact('in_come' , 'total_orders' , 'pending_offers'  , 'today_orders' ));

    }
        // $in_come = $user->wallet()['avilable_balance'];
        // $total_orders = client_reference::with('reference')->whereHas('reference', fn ($qry) => $qry->where('instructor_id', $user->id))->count();
        // $today_orders = client_reference::with('reference')->whereHas('reference', fn ($qry) => $qry->where('instructor_id', $user->id))->whereDate('at', Carbon::today())->count();

    }
}
}
