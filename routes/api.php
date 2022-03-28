<?php

use App\Http\Controllers\JobTitleController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\RouteStatusController;
use App\Http\Controllers\BookingStatusController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\DriverRouteController;
use App\Http\Controllers\LocationPointController;
use App\Http\Controllers\PassengerBookingController;
use App\Http\Controllers\UserCarController;
use Illuminate\Http\Request;
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

Route::middleware(['auth:api'])->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/register', [UserController::class, 'register']);

Route::group(['middleware' => ['auth:api,cors']], function () {
    Route::get('/logout', [UserController::class, 'logout']);

    Route::get('/driver-routes/user', [DriverRouteController::class, 'user_routes']);
    Route::get('/driver-routes/user/past', [DriverRouteController::class, 'user_routes_past']);
    Route::get('/driver-routes/search', [DriverRouteController::class, 'search_routes']);
    Route::get('/driver-routes/{id}/location-points', [DriverRouteController::class, 'driver_location_points']);

    Route::get('/passenger-bookings/user', [PassengerBookingController::class, 'user_bookings']);
    Route::get('/passenger-bookings/user/past', [PassengerBookingController::class, 'user_bookings_past']);
    Route::get('/passenger-bookings/search', [PassengerBookingController::class, 'search_bookings']);
    Route::post('/passenger-bookings/driver/{id}', [PassengerBookingController::class, 'driver_bookings']);

    Route::get('/notifications/user', [NotificationController::class, 'user_notifications']);

    Route::get('/user/profile', [UserController::class, 'user_profile']);

    Route::post('/user/photo', [UserController::class, 'user_photo']);

    Route::get('/cars/user', [UserCarController::class, 'user_car']);

    Route::apiResources([
        'route-statuses' => RouteStatusController::class,
        'booking-statuses' => BookingStatusController::class,
        'users' => UserController::class,
        'notifications' => NotificationController::class,
        'driver-routes' => DriverRouteController::class,
        'location-points' => LocationPointController::class,
        'passenger-bookings' => PassengerBookingController::class,
        'cars' => UserCarController::class,
    ]);
});

//Route::group(['middleware' => ['cors']], function () {

    Route::apiResources([
        'job-titles' => JobTitleController::class,
        'departments' => DepartmentController::class,
    ]);
//});
