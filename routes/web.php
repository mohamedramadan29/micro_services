<?php

use App\Livewire\Chat\Main;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\front\JobController;
use App\Http\Controllers\front\BlogController;
use App\Http\Controllers\front\CartController;
use App\Http\Controllers\front\UserController;
use App\Http\Controllers\front\FrontController;
use App\Http\Controllers\front\CourseController;
use App\Http\Controllers\front\ChatgptController;
use App\Http\Controllers\front\PackageController;
use App\Http\Controllers\front\ProductController;
use App\Http\Controllers\front\ProjectController;
use App\Http\Controllers\front\serviceController;
use App\Http\Controllers\front\TicketsController;
use App\Http\Controllers\front\CheckOutController;
use App\Http\Controllers\front\EmployeeController;
use App\Http\Controllers\front\FrontJobController;
use App\Http\Controllers\front\JobOfferController;
use App\Http\Controllers\front\WithDrawController;
use App\Http\Controllers\front\ProperityController;
use App\Http\Controllers\Auth\SocialMediaController;
use App\Http\Controllers\front\ConversationController;
use App\Http\Controllers\front\ProductOrderController;
use App\Http\Controllers\front\ProjectOfferController;
use App\Http\Controllers\front\ChargeBalanceController;
use App\Http\Controllers\front\TicketMessageController;
use App\Http\Controllers\front\CourseRegisterController;
use App\Http\Controllers\front\FrontProperityController;
use App\Http\Controllers\front\ProperityMaintainController;
use App\Http\Controllers\front\UserProductPurchesController;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;
use App\Http\Controllers\front\PublicCourseRegisterController;
use App\Http\Controllers\front\FrontProperityMaintainController;
use App\Http\Controllers\front\ReviewsController;

Route::group(
    [
        'prefix' => LaravelLocalization::setLocale(),
        'middleware' => ['localeViewPath']
    ],
    function () {

        Route::get('/', [FrontController::class, 'index'])->name('index');
        Route::get('about', function () {
            return view('website.about');
        });
        Route::get('faq', function () {
            return view('website.faq');
        });
        Route::controller(FrontController::class)->group(function () {
            Route::get('/', 'index')->name('home');
            Route::get('categories', 'categories');
            Route::get('services', 'services');
            Route::get('service/{id}-{slug}', 'service_details');
            Route::get('category/service/{slug}', 'sub_categories');
            Route::get('services/{slug}', 'category_services');
            Route::match(['post', 'get'], 'forget-password', 'forget_password');
            Route::match(['post', 'get'], 'user/change-forget-password/{code}', 'change_forget_password');
            Route::post('user/update_forget_password', 'update_forget_password');
            Route::get('terms', 'terms');
            Route::get('privacy-policy', 'privacy_policy');
            Route::get('search', 'search');
            Route::get('consultants', 'getConsultantsByCategory');
        });
        // Confirm User Email
        Route::get('user/confirm/{code}', [UserController::class, 'UserConfirm']);

        // Start User Dashboard
        Route::controller(UserController::class)->group(function () {
            Route::match(['get', 'post'], 'login', 'login')->name('user_login');
            Route::match(['get', 'post'], 'register', 'register');
            Route::get('logout', 'logout');
            Route::get('user/{username}', 'show_profile');
            Route::get('user/{username}/services', 'user_services');
            Route::group(['middleware' => ['auth']], function () {
                Route::get('dashboard', 'index')->name('dashboard');
                Route::get('purches', 'purches');
                Route::get('orders', 'orders');
                Route::get('reviews', 'reviews');
                Route::match(['post', 'get'], 'update-account', 'update');
                Route::get('chat', 'chat');
                Route::get('balance', 'balance')->name('user_balance');

                Route::get('chat-main/{conversation_id}', \App\Livewire\Chat\Main::class)->name('chat.main');
            });
        });
        // Start User Service
        Route::group(['middleware' => ['auth']], function () {
            Route::controller(serviceController::class)->group(function () {
                Route::get('service/index', 'index');
                Route::match(['post', 'get'], 'service/add', 'add');
                Route::match(['post', 'get'], 'service/update/{id}', 'update');
                Route::match(['post', 'get'], 'service/delete/{id}', 'delete');
                Route::get('service/get-subcategories/{categoryId}', 'getSubCategories');
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
                Route::post('create_order', 'create_order');
                Route::get('service/payment/success', 'paymentSuccess')->name('service.payment.success');
                Route::get('service/payment/cancel', 'paymentCancel')->name('service.payment.cancel');
            });
        });

        ///////////////////////////// Start Conversation ////////////////////
        Route::controller(ConversationController::class)->group(function () {
            Route::get('chats', 'chats');
            Route::post('conversation/start', 'start_conversation');
            Route::post('conversation/start/project', 'project_start_conversation');
            Route::post('consult_conversation/start', 'consult_start_conversation');
        });
        //////////////////////// Start Chats /////////////////
        ///

        ////////////////////////////////// Start Products //////////////////

        Route::controller(ProductController::class)->group(function () {
            Route::get('products', 'index');
            Route::get('product/{slug}', 'product_details');
        });

        /////////////////////////////////// End Products ////////////////////
        ///
        ////////////// Start Product ORder /////////////
        ///
        Route::controller(ProductOrderController::class)->group(function () {
            Route::post('product_order', 'store');
            Route::get('/product/payment/success', 'paymentSuccess')->name('product.order.success');
            Route::get('/product/payment/cancel', 'PaymentCancel')->name('product.order.cancel');
            Route::get('/download/product/{order}', 'downloadProduct')->name('download.product');
        });



        ////////////////// Start Projects Controller
        ///
        ///
        Route::group(['middleware' => ['auth']], function () {
            Route::controller(ProjectController::class)->group(function () {
                Route::get('my/project/index', 'index');
                Route::match(['post', 'get'], 'my/project/add', 'store');
                Route::match(['post', 'get'], 'my/project/update/{id}', 'update');
                Route::match(['post', 'get'], 'my/project/delete/{id}', 'delete');
            });
        });

        ///////////////////////////////// Start Projects IN Front
        Route::controller(ProjectController::class)->group(function () {
            Route::get('projects', 'website_project');
            Route::get('project/{id}-{slug}', 'ProjectDetails');
        });

        Route::group(['middleware' => ['auth']], function () {
            Route::controller(ProjectOfferController::class)->group(function () {
                Route::post('project/add-offer', 'store');
                Route::post('project/update-offer', 'update');
                Route::post('accept_offer/{offer_id}', 'accept_offer');
                Route::post('accept_project/{id}', 'accept_project');
            });
        });

        #################### Start Tickets Controller ###########################
        ##################### Start User Courses ###################
        Route::controller(CourseController::class)->group(function () {
            Route::get('courses', 'index');
            Route::get('course/{id}-{slug}', 'course_details')->name('course_details');
        });

        Route::group(['middleware' => ['auth']], function () {
            Route::controller(TicketsController::class)->group(function () {
                Route::get('tickets', 'index')->name('tickets');
                Route::match(['post', 'get'], 'ticket/create', 'store');
            });
            #################### Start Ticket Messages ########################
            Route::controller(TicketMessageController::class)->group(function () {
                Route::get('ticket/{id}', 'index');
                Route::post('message/create/{ticket_id}', 'store');
            });
            ##################### Start User Courses ###################
            Route::controller(CourseController::class)->group(function () {

                ////////////////  User Course Route  //////////////////////////////
                Route::get('my/courses', action: 'user_courses');
                Route::match(['post', 'get'], 'my/course/add', 'store');
                Route::match(['post', 'get'], 'my/course/update/{id}', 'update');
                Route::get('my/course/subscriptions/{id}', 'subscriptions');
            });
            //////////////// Course Register ////////////////////////////////

            Route::controller(CourseRegisterController::class)->group(function () {
                Route::post('course_regitser/{id}', 'course_register');
            });
            ######################### Start Charge Balance ##########################
            Route::controller(ChargeBalanceController::class)->group(function () {
                Route::post('charge_balance', 'charge_balance');
            });
            ###################### Start WithDraw Balance ############################
            Route::controller(WithDrawController::class)->group(function () {
                Route::post('withdraw_balance', 'WithDraw');
            });
            ################# Start Properites ################


            Route::controller(ProperityController::class)->group(function () {
                Route::get('my/properties/index', 'index');
                Route::match(['post', 'get'], 'my/property/add', 'store');
                Route::match(['post', 'get'], 'my/property/update/{id}', 'update');
                Route::match(['post', 'get'], 'my/property/delete/{id}', 'delete');
                Route::post('my/property/upload-temp', 'uploadTemp');
                Route::post('my/property/remove-temp', 'removeTemp');
                Route::post('delete-property-image/{id}', 'deletePropertyImage');
            });

            ################ End Properites #################

            ################# Start Properity Maintain #################
            Route::controller(ProperityMaintainController::class)->group(function () {
                Route::get('my/property/maintain/index', 'index');
                Route::match(['post', 'get'], 'my/property/maintain/add', 'store');
                Route::match(['post', 'get'], 'my/property/maintain/update/{id}', 'update');
                Route::match(['post', 'get'], 'my/property/maintain/delete/{id}', 'delete');
            });
            ################# End Properity Maintain #################

            ############## Start Jobs Controller ########################
            Route::controller(JobController::class)->group(function () {
                Route::get('my/jobs', 'index');
                Route::match(['post', 'get'], 'my/job/add', 'store');
                Route::match(['post', 'get'], 'my/job/update/{id}', 'update');
                Route::post('my/job/delete/{id}', 'delete');
                Route::match(['get', 'post'], 'my/job/delete/{id}', 'delete');
                Route::get('my/job/subscriptions/{id}', 'subscriptions');
            });
            ############# End Jobs Controller ####################
            ############ Start User Product Purches ##############
            Route::controller(UserProductPurchesController::class)->group(function () {
                Route::get('my/products/purches', 'index')->name('user.products.purches');
            });
            ############ End User Product Purches ################
        });
        ############### Staty Front Properity Controller ##########
        Route::controller(FrontProperityController::class)->group(function () {
            Route::get('properties', 'index');
            Route::get('property/{id}-{slug}', 'propertyDetails');
        });
        ############### End Front Properity Controller ##########

        ################# Start Front Properity Maintain Controller ##########
        Route::controller(FrontProperityMaintainController::class)->group(function () {
            Route::get('properties/maintain', 'index');
            Route::get('properties/maintain/{id}-{slug}', 'propertyDetails');
        });
        ################# End Front Properity Maintain Controller ##########

        ################# Start Front Jobs Controller ##########
        Route::controller(FrontJobController::class)->group(function () {
            Route::get('jobs', 'index');
            Route::get('job/{id}-{slug}', 'jobDetails');
            Route::post('job/offer/store/{id}', 'OfferStore');
        });
        ################# End Front Jobs Controller ##########

        ################# Start Employees Controller ##########
        Route::controller(EmployeeController::class)->group(function () {
            Route::get('employees', 'index');
            Route::get('employee/{username}', 'show');
        });
        ################# End Employees Controller ##########

        ######################## Start ChatGpt ##################
        Route::controller(ChatgptController::class)->group(function () {

            Route::get('chatgpt', 'index');
            Route::post('chatgpt', 'chatgpt');
        });

        #################### Start Blog Routes ########################

        Route::controller(BlogController::class)->group(function () {

            Route::get('categories', 'categories')->name('blogCategories');
            Route::get('category/{slug}', 'categoryDetails')->name('blogCategoryDetails');
            Route::get('blog/{slug}', 'blogDetails')->name('blogDetails');
        });
        #################### End Blog Routes ##########################

        Route::group(['middleware' => ['auth']], function () {
            Route::get('/course/payment/success', [CourseRegisterController::class, 'payment_success'])->name('course.payment.success');
            Route::get('/course/payment/cancel', [CourseRegisterController::class, 'payment_cancel'])->name('course.payment.cancel');
        });


        Route::get('auth/{provider}/redirect', [SocialMediaController::class, 'redirect'])->name('auth.google.redirect');
        Route::get('auth/{provider}/callback', [SocialMediaController::class, 'callback'])->name('auth.google.callback');

        ############## Public Course Route
        Route::match(['post', 'get'], 'course/public/{url}', [PublicCourseRegisterController::class, 'RegisterCourse'])->name('registercourse');

        ######################### Start Package Controller #############################

        Route::controller(PackageController::class)->group(function () {
            Route::get('packages', 'index')->name('packages.index');
            Route::post('package/subscribe/{id}', 'subscribePlan')->name('subscribe.plan');
            Route::get('/package/payment/success', 'paymentSuccess')->name('package.order.success');
            Route::get('/package/payment/cancel', 'PaymentCancel')->name('package.order.cancel');
        });
        ######################### End Package Controller ################################

        ######################### Start Reviews Controller #############################

        Route::controller(ReviewsController::class)->group(function () {
            Route::get('reviews', 'index')->name('reviews.index');
            Route::post('reviews/post', 'store')->name('front.reviews.post');
        });
        ######################## End Reviews Controller ##################################
    }
);



include 'admin.php';
