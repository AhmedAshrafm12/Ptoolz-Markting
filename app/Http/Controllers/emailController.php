<?php

namespace App\Http\Controllers;

use App\Mail\ptoolzMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class emailController extends Controller
{
public function send($params ){


    $details = [
        'type'=>$params['type'],
        'title'=>$params['title'],
        'body'=>$params['body'],
        'code'=>$params['code']
    ];

    Mail::to($params['to'])->send(new ptoolzMail($details));
   return "email sent";
}
}
