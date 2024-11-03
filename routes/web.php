<?php

use App\Http\Controllers\front\CartController;
use App\Http\Controllers\front\CheckOutController;
use App\Http\Controllers\front\ConversationController;
use App\Http\Controllers\front\FrontController;
use App\Http\Controllers\front\serviceController;
use App\Http\Controllers\front\UserController;
use Illuminate\Support\Facades\Route;
use \App\Http\Controllers\front\ProductController;
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
    Route::get('search', 'search');
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
        Route::get('purches','purches');
        Route::get('orders','orders');
        Route::get('reviews', 'reviews');
        Route::match(['post', 'get'], 'update-account', 'update');
        Route::get('chat', 'chat');
        Route::get('balance', 'balance');
        Route::get('chat-main', \App\Livewire\Chat\Main::class);
    });

});
// Start User Service
Route::group(['middleware' => ['auth']], function () {
    Route::controller(serviceController::class)->group(function () {
        Route::get('service/index', 'index');
        Route::match(['post', 'get'], 'service/add', 'add');
        Route::match(['post', 'get'], 'service/update/{id}', 'update');
        Route::match(['post', 'get'], 'service/delete/{id}', 'delete');
        Route::post('/tmp-upload', 'tmpUpload');
        Route::delete('/tmp-delete', 'tmpDelete');
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

////////////////////////////////// Start Products //////////////////

Route::controller(ProductController::class)->group(function (){
    Route::get('products','index');
    Route::get('product/{slug}','product_details');
});

/////////////////////////////////// End Products ////////////////////

include 'admin.php';
