<?php

namespace App\Http\Controllers;

use App\Models\data\city;
use App\Models\data\region;
use App\Models\order\order;
use App\Models\data\country;
use Illuminate\Http\Request;
use App\Models\data\semesters;
use App\Models\users\customer;
use App\Models\data\departments;
use App\Models\data\information;
use App\Models\data\collegeyears;
use App\Models\notification_type;
use App\Models\users\serviceProvider;
use App\Models\order\photogrphyUpdates;
use App\Models\users\data\notification;
use Illuminate\Support\Facades\Storage;
use App\Models\data\systemConfiguration;
use App\Models\order\eductional\college;
use App\Models\users\data\users_contacts;
use Illuminate\Support\Facades\Validator;
use App\Models\order\eductional\reference;
use App\Models\order\eductional\university;
use App\Models\users\data\notification_type as DataNotification_type;
use App\Models\translation\informationtranslation;
class gloabalDetailsController extends Controller
{
    public function country()
    {
        return apiresponse(true, 200, 'success', country::all());
    }
    public function city(country $country)
    {

        return apiresponse(true, 200, 'success', $country->cities);
    }
    public function region(city $city)
    {
        return apiresponse(true, 200, 'success', $city->regions);
    }


    public function country_universities(country $country)
    {
        $universities =  university::where('country_id', $country->id)->get();

        return apiresponse(true, 200, 'success', $universities);
    }

    public function departments(college $college)
    {
        $departments = $college->departments;
        return apiresponse(true, 200, 'success', $departments);
    }
    public function semesters()
    {
        return apiresponse(true, 200, 'success', semesters::all());
    }

    public function colleges(university $university)
    {
        return apiresponse(true, 200, 'success', $university->colleges);
    }
    public function collegeyears()
    {
        return apiresponse(true, 200, 'success', collegeyears::all());
    }

    public function System_fees()
    {

        $config =  systemConfiguration::latest('id')->first()->adminAcceptPercentage;

        return apiresponse(true, 200, 'success', $config);
    }


    public function instructors_filter()
    {
        $referecnes = reference::where('university_id', request()->university_id)->where('college_id', request()->college_id)->where('department_id', request()->department_id)->where('year_id', request()->year_id)->where('semester_id', request()->semester_id)->with('instructor')->get()->unique('instructor');

        $instructors = [];
        foreach ($referecnes as $referecne)
            $instructors[] = $referecne['instructor'];

        return apiresponse(true, 200, 'success', $instructors);
    }



    public function references_filter()
    {
        $referecnes = reference::where('university_id', request()->university_id)->where('college_id', request()->college_id)->where('department_id', request()->department_id)->where('year_id', request()->year_id)->where('semester_id', request()->semester_id)->where('instructor_id', request()->instructor_id)->get();
        return apiresponse(true, 200, 'success', $referecnes);
    }

    public function user_profie()
    {

        $user = serviceProvider::where('user_key', request()->user_key)->first();

        if ($user != null) {

            $profile =  serviceProvider::with(['portfolio', 'experiences', 'location'])->where('user_key', request()->user_key)->first();
            $profile->location['country'] = country::find($user->location->country);
            $profile->location['city'] = city::find($user->location->city);
            $profile->location['region'] = region::find($user->location->region);

            return apiresponse(true, 200, 'success', $profile);
        } else {
            $profile = customer::with('location')->where('user_key', request()->user_key)->first();
            $profile->location['country'] = country::find($profile->location->country);
            $profile->location['city'] = city::find($profile->location->city);
            $profile->location['region'] = region::find($profile->location->region);
            return apiresponse(true, 200, 'success', $profile);
        }
    }

    public function informations()
    {


        $type = request()->type;
        
                $info =  information::first()->$type;

        if(getRequestLanguage()=='ar')
        $info = informationtranslation::where('language_code' , getRequestLanguage())->first()->$type;


        return apiresponse(true, 200, 'success', $info);
    }

    public function contact_us()
    {

        $user_contact = new users_contacts();
        return $user_contact->store();
    }


    public function test_upload()
    {
        $validator = Validator::make(
            request()->all(),

            [
                "file" => 'required|mimes:png,jpg,pdf',
            ],
            validationMessages()
        );

        if ($validator->fails())
            return apiresponse(false, 200, $validator->errors()->first());
        $file_name = time() . '.' . request()->file('file')->clientExtension();
        $path = request()->file('file')->storeAs('test',$file_name,'order');
                return apiresponse(true  , 200 , 'success' , $path);

    }



    public function charge(){

        $validator = Validator::make(
            request()->all(),

            [
                "value" => 'required',
            ],
            validationMessages()
        );

        if ($validator->fails())
            return apiresponse(false, 200, $validator->errors()->first());

        $value = request()->value;

        return apiresponse(true  , 200 , 'success' , env('CHARGE_URL').'?value='.$value);
    }


    public function notifications_types(){

        return apiresponse(true , 200 , 'success' , DataNotification_type::all());
    }

    public function ph_updates(){

        // $orders =  order::where('category_id' , 3)->get();

        // foreach($orders as $order)
        // photogrphyUpdates::store($order);
        return send_to_topic();
    }
}
