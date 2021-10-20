<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\userController;
use App\Http\Controllers\EmailVerificationContollers;
use App\Http\Controllers\ComplateProfile;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
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

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

// Register And Login Api
Route::post('register',[userController::class, 'Register', 'verify' => true]);
Route::post('login',[userController::class, 'login']);


// Dashboard API
Route::post('dashboard',[userController::class, 'userpostImage']);
Route::post('get-favorite-user',[userController::class, 'getFavoriteUser']);
Route::post('remove-favorite',[userController::class, 'removeFavorite']);




// User complate profile
// Route::post('complete-profile',[userController::class, 'complete_profile']);
// Route::post('get-customer-profile-info',[userController::class, 'get_customer_profile_info']);
// Route::post('update-profile/{id}',[ComplateProfile::class, 'updateProfile']);

// User Account Setting Api



// Forgot Password Api
Route::post('forgot-password',[userController::class, 'forgot_password']);

    Route::post('complete-profile',[userController::class, 'complete_profile']);
    Route::post('get-user-profile-info',[userController::class, 'get_user_profile_info']);
    Route::post('user-account-setting',[userController::class, 'user_accound_settings']);
    Route::post('get-account-setting',[userController::class, 'get_account_setting']);
    

// Authenticate Api Section

Route::group(['middleware' => ['jwt.verify']], function() {
    Route::post('logout',[userController::class,'logout']);
    Route::post('favorite-image',[userController::class, 'favoriteImage']);
    
});

// Route::middleware('auth:sanctum')->group(function () {
//     Route::post('logout',[userController::class,'logout']);
//     Route::group(['middleware' => ['jwt.verify']], function()
// });


// Email Verification Routes
// Route::post('emali/verification',[EmailVerificationContollers::class, 'sendVerificationEmail'])->middleware('auth:sanctum');
// Route::get('/email/verify/{id}/{hash}',[EmailVerificationContollers::class, 'verify'])->name('verification.verify')->middleware('auth:sanctum');



// Email Verification Routes
Route::get('/email/verify', function () {
    return view('auth.verify-email');
})->middleware('auth')->name('verification.notice');

Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();

    return redirect('/home');
})->middleware(['auth', 'signed'])->name('verification.verify');


