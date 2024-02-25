<?php
namespace App\Http\Controllers\marketer;



use App\Http\Controllers\Controller;
use App\Models\data\category;
use App\Models\markting\marketerserviceprovider;
use App\Models\users\marketer;
use App\Models\users\serviceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class marketerController extends Controller
{
    public function add_service_provider(){                      // add new service provider to the marketer

        $validator  = Validator::make(request()->all() ,
        [
            "sp_code"=>'required' ,
            'category_id'=>'required|exists:category,id'
        ]);


     // return errors
            if ($validator->fails())
            return apiresponse(false, 200, $validator->errors()->first());


    // // check sp code
    //  if(check_sp_code(request()->sp_code,request()->sp_id) == 0)
    //    return apiresponse(false , 200 , 'sp code is not correct');


    // check sp category
    if(check_sp_category(request()->category_id,request()->sp_code) == 0)
    return apiresponse(false , 200 , 'sp code is not correct');

    $sp = serviceProvider::where('sp_code' , request()->sp_code)->where('category_id'  , request()->category_id)->first();


    // return $sp;
    // check if can add
    $marketer = marketer::find(auth_user()->id);


    // check if sp exist
    if ($marketer->serviceproviders->contains($sp))
        return apiresponse(false, 200, "sp already exist");


    // add sp
        marketerserviceprovider::store(auth_user() , $sp->id);

        return apiresponse(true , 200 , 'success' );

    }


    public function get_service_provider(){

        $category_id = request()->category_id;

        $sps =  marketer::find(auth_user()->id)->serviceproviders()->where('category_id' , $category_id)->get();

        $response['category_name'] = category::find($category_id)->department_name;
        $response['sp_count'] = $sps->count();
        foreach($sps as $sp)
        {
            $sp->total_income = get_market_total($sp->handovers())['marketer_total'];
            $sp->total_offers = count($sp->handovers());
            $sp->added_at = marketerserviceprovider::where('marketer_id' , (auth_user()->id))->where('sp_id' , $sp->id)->first()->created_at;
        }
        $response['services_providers'] = $sps->values();

        return apiresponse(true , 200 , 'success' , $response);
    }


    public function stats(){

         $marketer = marketer::find(auth_user()->id);

         $from = request()->from ?? '1990-1-01';
         $to = request()->to ?? now();
         $at = null;

         $response['service_providers'] = $marketer->sp_stats($from , $to) ;

         $response['team'] = null;
         if($marketer->role == 2)
         $response['team']  = $marketer->team_stats($from , $to);

         return apiresponse(true , 200 , 'success' , $response);


    }



    public function wallet(){

        $marketer = marketer::find(auth_user()->id);
        return   apiresponse(true, 200, 'success', $marketer->wallet());
    }

public function current_balance(){
    $marketer = marketer::find(auth_user()->id);
    return   apiresponse(true, 200, 'success', round($marketer->currentBalance()));

}




}
