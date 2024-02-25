<?php

use App\Http\Controllers\customer\orderDetailsController;
use Illuminate\Http\Request;
use App\Models\users\marketer;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\gloabalDetailsController;
use App\Http\Controllers\payment\paymentController;
use App\Http\Controllers\marketer\profileController;
use App\Http\Controllers\marketer\marketerController;
use App\Http\Controllers\registration\dataController;
use App\Http\Controllers\registration\userController;

use App\Http\Controllers\marketer\marketerleaderController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


// registration
Route::post('sign_up', [userController::class, 'signup']);
Route::post('login', [userController::class, 'login']);
Route::get('category', [dataController::class, 'catgeory']);
Route::post('send/{type}', [userController::class, 'sendCode']);
Route::post('check/{type}', [userController::class, 'checkCode']);
Route::post('change_password', [userController::class, 'change_password']);
Route::post('login_mobile/send', [userController::class, 'sendMobile']);
Route::post('login_mobile', [userController::class, 'mobileLogin']);

// before id verification
Route::middleware('auth:sanctum')->group(function () {
    Route::get('account_deactive' , [userController::class , 'deactive']);
    Route::get('logout', [userController::class, 'logout']);
    Route::post('identity_verification', [userController::class, 'verify_identity']);
    Route::post('withdrawl', [paymentController::class, 'withdrawl']);
    Route::get('wallet', [marketerController::class, 'wallet']);
    Route::get('current_balance', [marketerController::class, 'current_balance']);
    Route::get('notifications' , [userController::class ,'notifications']);

    Route::get('country', [orderDetailsController::class, 'country']);
    Route::get('city/{country}', [orderDetailsController::class, 'city']);
    Route::get('region/{city}', [orderDetailsController::class, 'region']);
// after id verification

Route::post('add_sp',[marketerController::class,'add_service_provider']);
Route::get('get_sp',[marketerController::class,'get_service_provider']);
Route::get('stats' , [marketerController::class , 'stats']);
Route::get('profile' , [profileController::class , 'index']);
Route::post('update_profile' , [profileController::class , 'edit']);
// team routes
Route::get('get_team',[marketerleaderController::class,'get_team']);
Route::post('accept_member/{marketer}',[marketerleaderController::class,'accept_member']);
Route::post('decline_member/{marketer}',[marketerleaderController::class,'decline_member']);

});

// after id verification


//global routes

Route::get('category', [dataController::class, 'catgeory']);
Route::get('informations' , [gloabalDetailsController::class , 'informations']);


Route::post('contact_us' , [gloabalDetailsController::class , 'contact_us']);
