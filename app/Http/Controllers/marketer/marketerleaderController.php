<?php

namespace App\Http\Controllers\marketer;

use Illuminate\Http\Request;
use App\Models\users\marketer;
use App\Http\Controllers\Controller;
use App\Models\users\data\marketerleader;

class marketerleaderController extends Controller
{
    public function get_team(){

        $user = marketer::find(auth_user()->id);

        if($user->role == 2)
        return apiresponse(true , 200 , 'success' , ['team'=>$user->team , 'team_count'=>$user->team->count()]);


    }

    public function accept_member(marketer $marketer){

        $leader = marketer::find(auth_user()->id);

        if(!$leader->team->contains($marketer))
        return apiresponse(false, 200, __('auth.unAuthorized'));

        $marketer->approval = 1;
        $marketer->checked = 1;

        $marketer->save();

        return apiresponse(true, 200,"success");


   }

    public function decline_member(marketer $marketer){

        $leader = marketer::find(auth_user()->id);

        if(!$leader->team->contains($marketer))
        return apiresponse(false, 200, __('auth.unAuthorized'));

        $marketer->approval = 0;
        $marketer->checked = 1;

        $marketer->save();

        return apiresponse(true, 200,"success");


   }
}
