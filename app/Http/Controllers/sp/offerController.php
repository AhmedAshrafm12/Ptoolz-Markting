<?php

namespace App\Http\Controllers\sp;

use App\Models\offer\offer;
use App\Models\order\order;
use Illuminate\Http\Request;
use App\Models\data\category;
use App\Models\data\ordertype;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\users\serviceProvider;

class offerController extends Controller
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

        $user = serviceProvider::find(Auth('sanctum')->user()->id);
        $category = category::find($user->category_id);
        $orders =  $category->orders()->with('offers')->where('status', 1)->withcount('offers')->orderBy('id' , 'desc');

        if(request()->order_type_id )
        $orders =  $category->orders()->with('offers')->where('order_type_id' ,request()->order_type_id )->where('status', 1)->withcount('offers')->orderBy('id' , 'desc');

        $order_output = [];

         foreach($orders->get() as $order)
         {
            if($order->submitted == 0)
            $order_output[] = $order;
         }


          $collection = (new Collection($order_output));

          $collection->filter(function($item){
            // return $item->value();
            return $item->id != 42;
          });
        //   return $collection;
        //   $collection =  $collection->values();
        return apiresponse(true, 200 , 'success' , $collection->paginate(10));
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
        // $user = serviceProvider::find(Auth('sanctum')->user()->id);
        // $order = order::find(request()->order_id)

        // if ($user->cannot('acceptAndHold', $offer)) {
        //     return apiresponse(false, 401, __('auth.unAuthorized'));
        // }

        $offer = new offer();
        return $offer->store();
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\offer\offer  $offer
     * @return \Illuminate\Http\Response
     */
    public function show(offer $offer)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\offer\offer  $offer
     * @return \Illuminate\Http\Response
     */
    public function edit(offer $offer)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\offer\offer  $offer
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, offer $offer)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\offer\offer  $offer
     * @return \Illuminate\Http\Response
     */
    public function destroy(offer $offer)
    {
        //
    }

  public function order_details(order $order){

    $user = serviceProvider::find(Auth('sanctum')->user()->id);
    if($user->category_id == $order->category_id){
        $model = orderModel($order->order_type_id);
        $orderModel =   $model::find($order->order_id);
     

        $data = $orderModel->get($order);
         
         // web edit
        if(isset(request()->platform_web))
        $data->offer = $order->offers()->where('user_id' , $user->id)->first();

        return apiresponse(true , 200 , 'success' , $data);
    }
    else{
        return apiresponse(false , 401 , __('auth.unAuthorized'));
    }

  }


  public function my_offers(){
    $user = serviceProvider::find(Auth('sanctum')->user()->id);

    if(isset(request()->order_type_id)){
        $offers =  $user->offers()->with(['order'=>function($query){
            $query->where('status' ,  request()->status)->where('order_type_id' ,  request()->order_type_id);
           }])->get();
    }else{
        $offers =  $user->offers()->with(['order'=>function($query){
            $query->where('status' ,  request()->status);
           }])->get();
    }

   $data = [];

if(isset(request()->platform_web))
{
       return apiresponse(true , 200 ,'success' , $offers);

}
   foreach($offers as $offer)
   {

    if($offer->order)
    {
        $offer->order->offer_id = $offer->id;
        $data[] = $offer->order;
    }

   }

   return apiresponse(true , 200 ,'success' , $data);
  }






}
