<?php

namespace App\Http\Controllers\customer;

use App\Models\data\tax;
use Illuminate\Http\Request;
use App\Models\users\customer;
use Illuminate\Support\Facades\App;
use App\Http\Controllers\Controller;
use App\Models\order\print\document;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Models\order\eductional\reference;
use App\Models\transactions\handovertransaction;
use App\Models\order\eductional\client_reference;
use App\Models\transactions\spaccounttransaction;
use App\Models\transactions\clientaccounttransaction;
use App\Models\users\serviceProvider;

class referenceController extends Controller
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
        //
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
     * @param  \App\Models\order\eductional\reference  $reference
     * @return \Illuminate\Http\Response
     */
    public function show(reference $reference)
    {
        //
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
        //
    }

    public function download(reference $reference)
    {


        $user = customer::find(Auth('sanctum')->user()->id);

        // return $user->currentBalance();
        if (reference_cost($reference) > $user->currentBalance()) {
            return apiresponse(false, 200, 'no enough balance', env('PAYMENT_URL_REFERENCE') . $reference->id);
        }
    }


    public function invoice(reference $reference)
    {
        $user = customer::find(Auth('sanctum')->user()->id);
        $reference->total_cost =  round(reference_cost($reference));
        $tax =  tax::where('country', $user->Location->country)->first()->taxPercentage ?? 10;
        $reference->tax = $tax;
        return apiresponse(true, 200, 'success', $reference);
    }



    public function handover_reference(reference $reference)
    {

        $user = customer::find(Auth('sanctum')->user()->id);

        // return $user->currentBalance();
        if (reference_cost($reference) > $user->currentBalance()) {
            return apiresponse(false, 200, 'no enough balance', env('PAYMENT_URL_REFERENCE') . $reference->id);
        }


        else{
        $systemConfig = systemConfig($reference->price);
        $client_reference = new client_reference();
        $client_reference->reference_id = $reference->id;
        $client_reference->client_id = $user->id;
        $client_reference->save();
        ///   submit transactions

        handovertransaction::create(
            [
                'purchase_id' => $client_reference->id,
                "sp_percentage" => $systemConfig['spAcceptPercentage'],
                "admin_percentage" => $systemConfig['adminAcceptPercentage'],
                "sp_share" => $systemConfig['spShare'],
                'hold_value' => reference_cost($reference, $user->id),
                "admin_share" => $systemConfig['adminshare'],
            ]
        );

        $seller_id =  $reference->instructor_id;


        if($reference->instructor->category_id == 10){

      $printer_check = check_printer_instructor($reference->instructor);
      $seller_id = $printer_check['count'] > 0 ? $printer_check['printer']->id  :  Auth('sanctum')->user()->id ;


    }

        clientaccounttransaction::create([
            'purchase_id' => $client_reference->id,
            "user_id" =>  Auth('sanctum')->user()->id,
            "value" => reference_cost($reference, $user->id),
            "type_id" => 1,
        ]);


        spaccounttransaction::create([
            'purchase_id' => $client_reference->id,
            'user_id' => $seller_id,
            "value" => reference_cost($reference, $user->id),
            'type_id' => 1,
        ]);

        $sp = serviceProvider::find($seller_id);
  // notify customer
  $topic = set_user_topic($user->id);
  $payload = set_payload(__('notifiactions.reference_handoverd_title'), __('notifiactions.reference_handoverd_body'), 'order_handoverd', '', $user->user_key);
  send_to_topic($topic, $payload);

  // notify sp
  $topic = set_user_topic($seller_id);
  $payload = set_payload(__('notifiactions.reference_handoverd_title'), __('notifiactions.reference_handoverd_body'), 'offer_handoverd','',         $sp->user_key);
  send_to_topic($topic, $payload);

    }
        return apiresponse(true, 200, 'success');
    }


    public function print_reference(reference $reference)
    {

        $user = customer::find(Auth('sanctum')->user()->id);
        if(!($reference->downloads->contains($user)))
        return apiresponse(false , 200  , __('auth.unAuthorized'));

        $file_name = time() . '.pdf';
        Storage::disk('order')->copy('references/' . $reference->file_name, 'document/' . time() . '.pdf');
        $validator = Validator::make(
            request()->all(),

            [
                "paper_size" => 'required',
                "title" => 'required',
                "description" => 'required',
                "paper_type" => 'required',
                "delivery" => 'required',
                "printing_color" => 'required',
                "printing_side" => 'required',
                "paper_binding" => 'required',
                "number" => 'required',
                "country" => "required_if:delivery,1",
                'city' => 'required_if:delivery,1',
                'region' => 'required_if:delivery,1',
                'latitude' => 'required_if:delivery,1',
                'longitude' => 'required_if:delivery,1',
                'address' => 'required_if:delivery,1'
            ],
            validationMessages()
        );

        if ($validator->fails())
            return apiresponse(false, 200, $validator->errors()->first());




        $linguistic_correction = isset(request()->linguistic_correction)   ? request()->linguistic_correction : 0;
        $delivery = isset(request()->delivery) ? request()->delivery : 0;
        $document = document::create([
            "file" => $file_name,
            "paper_size" => request()->paper_size,
            "paper_type" => request()->paper_type,
            "delivery" => $delivery,
            "printing_color" => request()->printing_color,
            "printing_side" => request()->printing_side,
            "paper_binding" => request()->paper_binding,
            "number" => request()->number,
            "paper_type" => request()->paper_type,
            "linguistic_correction" => $linguistic_correction

        ]);

  // notify customer
  $topic = set_user_topic($user->id);
  $payload = set_payload(__('notifiactions.print_reference_title'), __('notifiactions.print_reference_body'), 'print_reference', '', $user->user_key);
  send_to_topic($topic, $payload);

        return apiresponse(true, 200, __("order.order_stored"));
    }
}
