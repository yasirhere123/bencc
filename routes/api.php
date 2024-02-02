<?php

use App\Http\Controllers\eventcontroller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\loginController;
use App\Http\Controllers\citiesController;







/*
|---------------------------------------------------------------------	-----
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });
Route::middleware('cors')->group(function(){
Route::any('/clear-cache', function() {
    Artisan::call('cache:clear');
    Artisan::call('config:clear');
    Artisan::call('view:clear');
    Artisan::call('route:clear');
    return "Cache is cleared";
});




Route::any('/cities', [citiesController::class, 'cities']);
Route::any('/register', [loginController::class, 'signup']);
Route::any('/submitevent', [eventcontroller::class, 'submitevent']);
Route::any('/eventlist', [eventcontroller::class, 'eventlist']);
Route::any('/eventdetail', [eventcontroller::class, 'eventdetail']);
Route::any('/searchevent', [eventcontroller::class, 'searchevent']);
Route::any('/sendemail', [eventcontroller::class, 'sendemail']);
Route::any('/eventbystatus', [eventcontroller::class, 'eventbystatus']);
Route::any('/approvedeclineevent', [eventcontroller::class, 'approvedeclineevent']);
Route::any('/eventbymonthyear', [eventcontroller::class, 'eventbymonthyear']);
Route::any('/eventbydate', [eventcontroller::class, 'eventbydate']);
Route::any('/createevent', [eventcontroller::class, 'createevent']);




Route::any('/login', [loginController::class, 'login']);
Route::group(['middleware' => ['jwt.verify']],   function() {
Route::any('/logout', [loginController::class, 'logout']);
Route::any('/getUser', [loginController::class, 'getUser']);


});

});



