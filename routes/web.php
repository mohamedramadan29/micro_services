<?php

use Illuminate\Support\Facades\Route;
use \App\Http\Controllers\serviceController;
use \App\Http\Controllers\UserController;
use  \App\Http\Controllers\FrontController;

Route::get('/', function () {
    return view('website.index');
});
Route::get('/index', [FrontController::class, 'index'])->name('index');
Route::get('about', function () {
    return view('website.about');
});
Route::get('faq', function () {
    return view('website.faq');
});

Route::controller(FrontController::class)->group(function () {
    Route::get('/', 'index');
    Route::get('categories', 'categories');
    Route::get('services', 'services');
    Route::get('service/{id}-{slug}', 'service_details');
    Route::get('category/{slug}','sub_categories');
    Route::get('services/{slug}','category_services');

});

// Start User Dashboard
Route::controller(UserController::class)->group(function () {
    Route::match(['get', 'post'], 'login', 'login');
    Route::match(['get', 'post'], 'register', 'register');
    Route::get('logout', 'logout');
    Route::get('user/{username}','show_profile');
    Route::get('user/{username}/services','user_services');
    Route::group(['middleware' => ['auth']], function () {
        Route::get('dashboard', 'index');
        Route::get('user/reviews', 'reviews');
        Route::match(['post', 'get'], 'user/update', 'update');
        Route::get('user/chat', 'chat');
        Route::get('user/balance', 'balance');
    });
});
// Start User Service
Route::group(['middleware' => ['auth']], function () {
    Route::controller(serviceController::class)->group(function () {
        Route::get('service/index', 'index');
        Route::match(['post', 'get'], 'service/add', 'add');
        Route::match(['post', 'get'], 'service/update/{id}', 'update');
        Route::match(['post', 'get'], 'service/delete/{id}', 'delete');
    });
});

// Confirm User Email
Route::get('user/confirm/{code}', [UserController::class, 'UserConfirm']);

include 'admin.php';
