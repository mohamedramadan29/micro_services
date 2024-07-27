<?php

use Illuminate\Support\Facades\Route;
use \App\Http\Controllers\serviceController;
use \App\Http\Controllers\UserController;
use  \App\Http\Controllers\FrontController;
use \App\Http\Controllers\CartController;
use \App\Http\Controllers\CheckOutController;
use \App\Http\Controllers\ConversationController;

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
    Route::get('category/{slug}', 'sub_categories');
    Route::get('services/{slug}', 'category_services');
    Route::match(['post', 'get'], 'forget-password', 'forget_password');
    Route::match(['post', 'get'], 'user/change-forget-password/{code}', 'change_forget_password');
    Route::post('user/update_forget_password', 'update_forget_password');
    Route::get('terms', 'terms');
    Route::get('privacy-policy', 'privacy_policy');
    Route::get('search','search');
});
// Confirm User Email
Route::get('user/confirm/{code}', [UserController::class, 'UserConfirm']);

// Start User Dashboard
Route::controller(UserController::class)->group(function () {
    Route::match(['get', 'post'], 'login', 'login');
    Route::match(['get', 'post'], 'register', 'register');
    Route::get('logout', 'logout');
    Route::get('user/{username}', 'show_profile');
    Route::get('user/{username}/services', 'user_services');
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

////////////// Cart Pages And Operations
Route::controller(CartController::class)->group(function () {
    Route::get('cart', 'cart');
    Route::post('cart/add', 'AddToCart');
    Route::post('cart/update', 'update');
    Route::post('cart/delete', 'deleteCart');
});
Route::group(['middleware' => ['auth']], function () {
    Route::controller(CheckOutController::class)->group(function () {
        Route::get('checkout', 'index');
        Route::post('checkout/order', 'order');
    });
});

///////////////////////////// Start Conversation ////////////////////
Route::controller(ConversationController::class)->group(function () {
    Route::post('conversation/start', 'start_conversation');
});
//////////////////////// Start Chats /////////////////
///
include 'admin.php';
