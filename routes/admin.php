<?php

use Illuminate\Support\Facades\Route;
use \App\Http\Controllers\admin\UserController;
use App\Http\Controllers\admin\AdminController;
use App\Http\Controllers\admin\OrderController;
use App\Http\Controllers\admin\CourseController;
use \App\Http\Controllers\admin\TicketController;
use App\Http\Controllers\admin\ConsultController;
use App\Http\Controllers\admin\ProductController;
use App\Http\Controllers\admin\ProjectController;
use \App\Http\Controllers\admin\ServiceController;
use \App\Http\Controllers\admin\SettingController;
use \App\Http\Controllers\admin\CategoryController;
use \App\Http\Controllers\admin\SubCategoryController;
Route::get('/admin', [AdminController::class, 'index'])->name('login');
Route::group(['prefix' => 'admin'], function () {
    Route::post('admin_login', [AdminController::class, 'admin_login']);
    // Start Register Page
    Route::match(['post', 'get'], 'register', [AdminController::class, 'register']);
    Route::group(['middleware' => ['auth']], function () {
        Route::controller(AdminController::class)->group(function () {
            Route::get('dashboard', 'dashboard');
            Route::post('logout', 'logout')->name('logout');
            // Start Update Admin Details
            Route::match(['post', 'get'], 'update_admin_password', 'update_admin_password');
            Route::match(['post', 'get'], 'update_admin_details', 'update_admin_details');
            // Start Update Admin Password
            // update admin password
            Route::match(['post', 'get'], 'update_admin_password', 'update_admin_password');
            // check Admin Password
            Route::post('check_admin_password', 'check_admin_password');
        });
        Route::controller(ServiceController::class)->group(function () {
            Route::get('services', 'index');
            Route::match(['get', 'post'], 'service/update/{id}', 'update');
            Route::match(['post', 'get'], 'service/add', 'store');
            Route::post('service/destroy/{id}', 'destroy');
            Route::get('service/get-subcategories/{categoryId}', 'getSubCategories');
        });
        Route::controller(UserController::class)->group(function () {
            Route::get('users', 'index');
        });

        Route::controller(CategoryController::class)->group(function () {
            Route::get('categories', 'index');
            Route::post('category/store', 'store');
            Route::post('category/update/{id}', 'update');
            Route::post('category/destroy/{id}', 'destroy');

        });
        Route::controller(SubCategoryController::class)->group(function () {
            Route::get('sub-categories', 'index');
            Route::post('sub-category/store', 'store');
            Route::post('sub-category/update/{id}', 'update');
            Route::post('sub-category/destroy/{id}', 'destroy');
        });

        Route::controller(SettingController::class)->group(function () {
            Route::get('public_settings', 'index');
            Route::post('public_settings/update', 'update');
        });
        ////////////////////// Start Products ///////////////////////////////
        Route::controller(ProductController::class)->group(function () {
            Route::get('products', 'index');
            Route::match(['post', 'get'], 'product/add', 'store');
            Route::match(['post', 'get'], 'product/update/{slug}', 'update')->name('product.update');
            Route::post('product/delete/{id}', 'delete');
            Route::get('/get-attribute-values/{attributeId}', 'getAttributeValues');
            Route::get('/get-subcategories', 'getSubCategories')->name('get.subcategories');
            Route::post('product/gallary/delete/{id}', 'delete_image_gallary');
        });
        ################ Start Ticket Controller #############

        Route::controller(TicketController::class)->group(function () {
            Route::get('tickets', 'index');
            Route::get('ticket/details/{id}', 'details');
            Route::post('message/create/{id}', 'create');
        });

        ####################### Start Consultants ############################

        Route::controller(ConsultController::class)->group(function () {
            Route::get('consultants', 'index');
            Route::match(['post', 'get'], 'consultant/add', 'store');
            Route::match(['post', 'get'], 'consultant/update/{id}', 'update');
            Route::post('consultant/delete/{id}', 'delete');
        });
        ###################### End Consultants ##############################

        #################### Project Controller ############################

        Route::controller(ProjectController::class)->group(function () {
            Route::get('projects', 'index');
            Route::match(['post', 'get'], 'project/update/{id}', 'update');
            Route::post('project/delete/{id}', 'delete');
            Route::post('project/update_status/{id}','update_status');
        });

        ################### End Project Controller ########################

        ################ Start Courses ###################
        Route::controller(CourseController::class)->group(function () {
            Route::get('courses', 'index');
            Route::post('course/update_status/{id}', 'update_status');
            Route::post('course/delete/{id}', 'delete');
        });
        ################# End Courses ####################
        ################## Start Order Controller ################
        Route::controller(OrderController::class)->group(function () {
            Route::get('orders', 'index');
            Route::match(['post', 'get'], 'order/update/{id}', 'update');
            Route::post('order/delete/{id}', 'delete');
        });
    });
});
