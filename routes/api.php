<?php

use App\Http\Controllers\ReportAnalysisController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\ListingController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\UserManageController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\UserInterfaceController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// todo: Public Routes

Route::post('/register',[UserController::class,'register']);
Route::post('/login',[UserController::class,'login']);

// Route::controller(ListingController::class)->group(function(){
//     Route::get('/listings','index');
// });

// todo: Protected Routes for User
Route::middleware(['auth:sanctum','user'])->group(function(){

    Route::controller(UserController::class)->group(function(){
        Route::get('/user','index');
        Route::get('/user/edit','edit');
        Route::post('/user/update','update');
        Route::get('/password/reset','passwordReset');
        Route::post('/password/update','passwordUpdate');
        Route::post('/logout','logout');
    });

    Route::controller(UserInterfaceController::class)->group(function(){
        Route::get('/listings','listings');
        Route::get('/listings/{id}','singleListing');
        Route::post('/listings/search','search');
        Route::post('/listings/filter','filter');

        Route::post('/bookings','bookings');
        Route::get('/bookings/{id}','specificBookingsGet');

        Route::get('/notifications/all','notifications');
        Route::get('/notifications/read','notificationsRead');
        Route::get('/notifications/unread','notificationsUnread');

        Route::get('reviews','reviewsAll');
        Route::get('/reviews/{id}','specificReviews');
        Route::post('/reviews','reviews');
        Route::get('/reviews/edit/{id}','reviewsEdit');
        Route::put('/reviews','reviewsUpdate');
        Route::delete('/reviews/{id}','reviewsDelete');

    });

});


// todo: Protected Routes for Admin
Route::middleware(['auth:sanctum','admin'])->group(function(){

    Route::prefix('admin')->group(function(){

        Route::controller(UserManageController::class)->group(function(){
            Route::get('/users','index');
            Route::get('/users/active','activeUsers');
            Route::get('/users/unactive','unactiveUsers');
            Route::get('/users/{id}','show');
            Route::get('/users/edit/{id}','edit');
            Route::post('/users/update','update');
            Route::delete('/users/delete/{id}','destroy');
        });

        Route::controller(ListingController::class)->group(callback: function(){
            Route::get('/listings','index');
            Route::get('/listings/{id}','show');
            Route::post('/listings/store','store');
            Route::get('/listings/edit/{id}','edit');
            Route::put('/listings/update','update');
            Route::delete('/listings/delete/{id}','destroy');
        });

        Route::controller(BookingController::class)->group(function(){

            Route::get('/bookings','bookings');
            Route::get('/bookings/{id}','specificBookingsGet');
            Route::get('/bookings/edit/{id}','edit');
            Route::put('/bookings/update','update');

            Route::get('/bookings/cancelled','bookingCancelled');
            Route::get('/bookings/pending','bookingPending');
            Route::get('/bookings/confirmed','bookingConfirmed');
            Route::get('/bookings/completed','bookingCompleted');

        });

        Route::controller(PaymentController::class)->group(function(){

            Route::get('/payments','index');
            Route::get('/payment/{id}','paymentById');
            Route::get('/payment/edit/{id}','edit');
            Route::put('/payment/update','update');
            Route::delete('/payment/delete/{id}','destroy');

        });

        Route::controller(ReportAnalysisController::class)->group(function(){

            Route::get('total-users','totalUsers');
            Route::get('total-listing','totalListing');
            Route::get('total-bookings','totalBookings');
            Route::get('total-revenue','totalRevenue');
            Route::get('total-monthly-revenue','totalMonthlyRevenue');

        });

    });

});

// Route::put('/test-put', function (Request $request) {
//     return response()->json(["data" => $request->all()]);
// });
