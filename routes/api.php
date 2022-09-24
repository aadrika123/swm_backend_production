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
    Route::post('createUser', 'CreateUser');                    // Route for create user
    Route::post('updateUser', 'UpdateUser');                    // Route for update user
    Route::post('getAlluser', 'getAllUser');                    // Route for get all user
    Route::post('userActiveDeactive', 'userActiveDeactive');    // Route for user activate and deactivate
    Route::post('getUserFormDate', 'getUserFormDate');          // Route for user form data
    Route::post('getTcList', 'getTcList');                      // Route for get tc list ulb wise
    
});

Route::group(['middleware' => ['json.response', 'apiauth:sanctum']], function () {
    // Your Protected Route is Here
    // Route::get('test', function () {
    //     return 'Success';
    // });
    




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
        Route::post('postEditConsumerDetail', 'UpdateConsumerDetails');
        Route::post('transactionModeChange', 'transactionModeChange');
        Route::post('postReminder', 'addConsumerReminder');
        Route::post('getReminder', 'getConsumerReminder');
        Route::post('apartmentPayment', 'ApartmentPayment');
        Route::post('apartmentDeactivate', 'ApartmentDeactivate');
        Route::post('getCashVerificationList', 'getCashVerificationList');
        Route::post('getCashVerificationFullDetails', 'getCashVerificationFullDetails');
        Route::post('postCashVerification', 'CashVerification');
        
    });

    Route::controller(MasterController::class)->group(function () {
        Route::get('getConsumerAddFormData', 'GetConsumerAddFormData');
        Route::get('getApartmentListByWardNo/{wardNo}', 'GetApartmentListData');
        Route::get('getConsumerTypeByCategory/{id}', 'GetConsumerTypeByCategoryId');
        
        Route::put('updateApartment', 'updateApartment');
        Route::post('addApartment', 'addApartment');
        Route::get('getApartList', 'GetApartmentListData');
        
        Route::get('getConsumerCategoryList', 'getConsumerCategoryList');
        Route::post('postConsumerCategoryAdd', 'ConsumerCategoryAdd');
        Route::put('postConsumerCategoryUpdate', 'ConsumerCategoryUpdate');
        Route::post('getonsumerCategoryById', 'ConsumerCategoryById');
        
        Route::post('getConsumerTypeList', 'ConsumerTypeList');
        Route::post('postConsumerTypeAdd', 'ConsumerTypeAdd');
        Route::put('postConsumerTypeUpdate', 'ConsumerTypeUpdate');
        Route::post('getConsumerTypeById', 'ConsumerTypeById');
        
        Route::post('getUlbList', 'UlbList');
        Route::post('postUlbAdd', 'UlbAdd');
        Route::put('postUlbUpdate', 'UlbUpdate');
        Route::post('deactivateToggleUlb', 'UlbActiveDeactive');
        Route::post('getUlbById', 'UlbById');
        
        Route::post('getWardList', 'WardList');
        Route::post('postWardAdd', 'WardAdd');
        Route::put('postWardUpdate', 'WardUpdate');
        Route::put('getWardListById', 'WardById');
    });

});






// Route::controller(ApartmentController::class)->group(function () {
//     Route::get('getApartmentList', 'GetApartmentList');
// });