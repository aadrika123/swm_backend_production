<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ConsumerController;
use App\Http\Controllers\MasterController;
//use App\Http\Controllers\ApartmentController;
use Illuminate\Support\Facades\Route;

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

Route::controller(AuthController::class)->group(function () {
    Route::post('login', 'login');                              // Route for user Login
    Route::get('getHomePageData/{userId}', 'GetHomePageData');  // Route for get last Login Details
    Route::post('postChangePassword', 'ChangePassword');        // Route for Change Password
    Route::post('logOut', 'Logout');                            // Route for user Logout
});

Route::group(['middleware' => ['json.response', 'apiauth:sanctum']], function () {
    // Your Protected Route is Here
    // Route::get('test', function () {
    //     return 'Success';
    // });
    




    

});

Route::controller(ConsumerController::class)->group(function () {
    Route::get('getConsumerList', 'GetConsumerList', function(){
        return 'Success';
    });
    Route::get('getConsumerDetailsById/{id}', 'GetConsumerList');
    Route::get('getApartmentList', 'GetApartmentList');
    Route::get('getApartmentDetailsById/{id}', 'GetApartmentDetailsById');
    Route::post('postConsumerAdd', 'postConsumerAdd');
    Route::get('getRenterFormData/{consumerId}', 'GetRenterFormData');
    Route::get('getEditConsumerDetailsbyId/{id}', 'getEditConsumerDetailsById');
    Route::post('postDeactivateConsumer', 'postDeactivateConsumer');
    Route::post('getPaymentData', 'getPaymentData');
    Route::post('postPayment', 'MakePayment');
    Route::post('getCalculatedAmount', 'getCalculatedAmount');
    Route::post('getDashboardData', 'getDashboardData');
    Route::get('searchTransaction/{transactionNo}', 'searchTransaction');
    Route::post('transactionDeactivate', 'transactionDeactivate');
    Route::post('postRenterForn', 'RenterForm');
    Route::get('getGeoLocation/{consumerId}', 'GetGeoLocation');
    Route::post('getAllTransaction', 'GetAllTransaction');
    Route::post('getCollectionSummary', 'AllCollectionSummary');
});

Route::controller(MasterController::class)->group(function () {
    Route::get('getConsumerAddFormData', 'GetConsumerAddFormData');
    Route::get('getApartmentListByWardNo/{wardNo}', 'GetApartmentListData');
    Route::get('getConsumerTypeByCategory/{id}', 'GetConsumerType');
});


// Route::controller(ApartmentController::class)->group(function () {
//     Route::get('getApartmentList', 'GetApartmentList');
// });