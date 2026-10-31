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
use App\Http\Controllers\front\ReviewsController;
use App\Http\Controllers\front\TicketsController;
use App\Http\Controllers\front\CheckOutController;
use App\Http\Controllers\front\EmployeeController;
use App\Http\Controllers\front\FrontJobController;
use App\Http\Controllers\front\WithDrawController;

use App\Http\Controllers\front\ProperityController;
use App\Http\Controllers\Auth\SocialMediaController;
use App\Http\Controllers\front\ConversationController;
use App\Http\Controllers\front\ProductOrderController;
use App\Http\Controllers\front\OnlinePaymentController;
use App\Http\Controllers\front\ServicesPageController;
use App\Http\Controllers\front\SiteTestController;
use App\Http\Controllers\front\ChargeBalanceController;
use App\Http\Controllers\front\TicketMessageController;
use App\Http\Controllers\front\CourseRegisterController;
use App\Http\Controllers\front\FrontProperityController;
use App\Http\Controllers\front\AccountTypeController;
use App\Http\Controllers\front\SpecializationController;
use App\Http\Controllers\front\ServiceRequestController;
use App\Http\Controllers\front\FreelancerRequestController;
use App\Http\Controllers\front\OfferController;
use App\Http\Controllers\front\NotificationController;
use App\Http\Controllers\front\RequestPaymentController;
use App\Http\Controllers\front\ProperityMaintainController;
use App\Http\Controllers\front\UserProductPurchesController;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;
use App\Http\Controllers\front\PublicCourseRegisterController;
use App\Http\Controllers\front\FrontProperityMaintainController;
use App\Http\Controllers\front\User\PortfolioController;
use App\Http\Controllers\front\NafizhaPortfolioController;
use App\Http\Controllers\front\NewCourseController;

Route::group(
    [
        'prefix' => LaravelLocalization::setLocale(),
        'middleware' => ['localeViewPath', 'track.visit']
    ],
    function () {

        Route::get('/', [FrontController::class, 'index'])->name('index');
        Route::get('go/{card}', [\App\Http\Controllers\front\HomeCardController::class, 'go'])->whereNumber('card')->name('home.card');
        Route::get('about', function () {
            return view('website.about');
        });
        Route::get('faq', function () {
            return view('website.faq');
        });
        Route::get('freelancers', [\App\Http\Controllers\front\FreelancerDirectoryController::class, 'index'])->name('freelancers.index');
        Route::get('freelancers/join', [\App\Http\Controllers\front\FreelancerJoinController::class, 'show'])->name('freelancers.join');
        // Journey analytics (client file «تعديلات», item 08): consent first, then anonymous events.
        Route::controller(\App\Http\Controllers\front\AnalyticsController::class)->group(function () {
            Route::post('analytics/consent', 'consent')->name('analytics.consent');
            Route::post('analytics/form-start', 'formStart')->middleware('throttle:60,1')->name('analytics.form-start');
            Route::post('analytics/abandon-reason', 'abandonReason')->middleware('throttle:10,1')->name('analytics.abandon-reason');
        });

        // Show prices with an approximate USD amount (display only; payments stay EGP).
        Route::get('currency/{code}', function (string $code) {
            return redirect()->back(302, [], url('/'))->withCookie(cookie()->forever(\App\Support\Money::COOKIE, $code));
        })->whereIn('code', ['EGP', 'USD'])->name('currency.switch');

        // Free site check (client file «اختبر موقعك مجاناً»): no login until the client asks to execute.
        Route::controller(SiteTestController::class)->group(function () {
            Route::get('site-test', 'index')->name('site-tests.index');
            Route::post('site-test', 'store')->name('site-tests.store');
            Route::get('site-test/{siteTest}', 'show')->name('site-tests.show');
            Route::get('site-test/{siteTest}/status', 'status')->name('site-tests.status');
            Route::post('site-test/{siteTest}/report', 'report')->middleware('throttle:10,60')->name('site-tests.report');
            Route::post('site-test/{siteTest}/choice/{choice}', 'choose')->whereIn('choice', ['yes', 'no'])->name('site-tests.choose');
            Route::middleware(['auth', 'account.type'])->group(function () {
                Route::get('site-test/{siteTest}/execute', 'execute')->name('site-tests.execute');
                Route::post('site-test/{siteTest}/execute', 'publish')->name('site-tests.publish');
            });
        });
        // Service 3: the public dispute policy (texts in the content admin).
        Route::view('dispute-policy', 'website.policy.disputes')->name('policy.disputes');
        // Landing pages built in the admin (client file «تعديلات», item 07).
        Route::get('lp/{slug}', [\App\Http\Controllers\front\LandingPageController::class, 'show'])->where('slug', '[a-z0-9-]+')->name('landing.show');
        // Services page (client brief «متطلبات تطوير قسم الخدمات»).
        Route::get('services', [ServicesPageController::class, 'index'])->name('services.index');
        Route::get('ready-services', [\App\Http\Controllers\front\ReadyServicesController::class, 'index'])->name('services.ready');
        // Homepage entry points (client file «المربعات الخمسة»): open to visitors; publishing needs a client account.
        Route::get('projects/diagnose', [\App\Http\Controllers\front\DiagnoseController::class, 'show'])->name('diagnose');
        Route::post('projects/diagnose', [\App\Http\Controllers\front\DiagnoseController::class, 'store'])->middleware('throttle:30,60')->name('diagnose.store');
        Route::get('projects/diagnose/result', [\App\Http\Controllers\front\DiagnoseController::class, 'result'])->name('diagnose.result');
        Route::get('projects/new', [\App\Http\Controllers\front\ProjectPostController::class, 'create'])->name('projects.new');
        Route::post('projects/new', [\App\Http\Controllers\front\ProjectPostController::class, 'store'])->middleware('throttle:10,60')->name('projects.store');
        // «نفّذها للأعمال | للشركات» (client file «قسم الشركات», phase 1): the free path is open to visitors.
        Route::controller(\App\Http\Controllers\front\BusinessController::class)->group(function () {
            Route::get('business', 'index')->name('business.index');
            Route::post('business/free', 'storeFree')->middleware('throttle:30,60')->name('business.free');
            Route::middleware(['auth', 'account.type'])->group(function () {
                Route::get('business/request', 'guided')->name('business.guided');
                Route::post('business/request', 'storeGuided')->middleware('throttle:30,60')->name('business.guided.store');
                Route::get('my/business', 'mine')->name('business.mine');
                Route::get('my/business/requests/{businessRequest}', 'showRequest')->where('businessRequest', 'BIZ-[0-9]+')->name('business.requests.show');
                Route::post('my/business/requests/{businessRequest}/decide', 'decide')->where('businessRequest', 'BIZ-[0-9]+')->name('business.requests.decide');
                Route::post('my/business/requests/{businessRequest}/pay', 'pay')->where('businessRequest', 'BIZ-[0-9]+')->name('business.requests.pay');
                Route::get('my/business/projects/{businessProject}', 'showProject')->where('businessProject', 'BPR-[0-9]+')->name('business.projects.show');
                Route::post('my/business/deliveries/{delivery}/review', 'review')->whereNumber('delivery')->name('business.deliveries.review');
                Route::get('business/files/request/{file}', 'requestFile')->whereNumber('file')->name('business.files.request');
                Route::get('business/files/delivery/{file}', 'deliveryFile')->whereNumber('file')->name('business.files.delivery');
            });
        });
        // «شاركنا اقتراحك» (client file «نظام الاقتراحات»): visitors may send too; flood limit here, 5 saved suggestions per hour in the controller.
        Route::get('suggestions', [\App\Http\Controllers\front\SuggestionController::class, 'create'])->name('suggestions.create');
        Route::post('suggestions', [\App\Http\Controllers\front\SuggestionController::class, 'store'])->middleware('throttle:30,60')->name('suggestions.store');
        Route::middleware('auth')->group(function () {
            Route::get('my/suggestions', [\App\Http\Controllers\front\SuggestionController::class, 'mine'])->name('suggestions.mine');
            Route::get('my/suggestions/{code}', [\App\Http\Controllers\front\SuggestionController::class, 'show'])->where('code', 'SUG-[0-9]+')->name('suggestions.show');
        });
        Route::middleware('auth')->group(function () {
            Route::get('services/help', [ServicesPageController::class, 'helpLogin'])->name('services.help.login');
            Route::post('services/help', [ServicesPageController::class, 'storeHelp'])->middleware('throttle:5,60')->name('services.help.store');
        });
        Route::controller(FrontController::class)->group(function () {
            Route::get('/', 'index')->name('home');
            Route::get('categories', 'categories');
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
        // Old unsigned activation links (base64 of the email) are no longer accepted.
        Route::get('user/confirm/{code}', [UserController::class, 'UserConfirm']);
        Route::post('user/verify/resend', [\App\Http\Controllers\front\EmailVerificationController::class, 'resend'])->middleware('throttle:3,60')->name('email.verify.resend');

        // Start User Dashboard
        Route::controller(UserController::class)->group(function () {
            Route::match(['get', 'post'], 'login', 'login')->name('user_login');
            Route::match(['get', 'post'], 'register', 'register');
            Route::match(['get', 'post'], 'logout', 'logout'); // GET kept for old links; the site's own menus POST
            Route::get('user/{username}', 'show_profile');
            Route::get('user/{username}/services', 'user_services');
            Route::group(['middleware' => ['auth']], function () {
                Route::get('dashboard', 'index')->name('dashboard');
                Route::get('purches', 'purches');
                Route::get('orders', 'orders');
                Route::match(['post', 'get'], 'update-account', 'update');
                Route::get('balance', 'balance')->name('user_balance');

                Route::get('chat-main/{conversation_id}', Main::class)->name('chat.main');
            });
        });
        // Account type (Google sign-ups / old accounts) and freelancer specializations
        Route::group(['middleware' => ['auth']], function () {
            Route::get('notifications/{id}', [NotificationController::class, 'open'])->name('notifications.open');
            Route::get('account-type', [AccountTypeController::class, 'edit'])->name('account.type');
            Route::post('account-type', [AccountTypeController::class, 'update'])->name('account.type.update');
            Route::group(['middleware' => ['account.type']], function () {
                Route::get('my/specializations', [SpecializationController::class, 'edit'])->name('specializations.edit');
                Route::post('my/specializations', [SpecializationController::class, 'update'])->name('specializations.update');
                // «ملفي كمستقل» was merged into «تعديل الحساب» (2026-10-20); old links and notifications land there.
                Route::get('my/freelancer-profile', fn () => redirect(url('update-account') . '#rf-freelancer', 301))->name('freelancer.profile.edit');

                // Service requests (client side)
                Route::controller(ServiceRequestController::class)->group(function () {
                    Route::get('request', 'create')->name('requests.create');
                    Route::get('staged-project', 'createStaged')->name('requests.staged');
                    Route::post('request', 'store')->name('requests.store');
                    Route::get('request/service/{subCategory}/fields', 'fields')->name('requests.fields');
                    Route::get('my/requests', 'index')->name('requests.index');
                    Route::get('my/requests/{serviceRequest}', 'show')->name('requests.show');
                    Route::post('my/requests/{serviceRequest}/publish', 'publish')->name('requests.publish');
                    Route::post('my/requests/{serviceRequest}/cancel', 'cancel')->name('requests.cancel');
                    Route::get('my/requests/{serviceRequest}/edit', 'edit')->name('requests.edit');
                    Route::get('projects/{serviceRequest}/edit', [\App\Http\Controllers\front\ProjectPostController::class, 'edit'])->name('projects.edit');
                    Route::post('projects/{serviceRequest}/edit', [\App\Http\Controllers\front\ProjectPostController::class, 'update'])->name('projects.update');
                    Route::post('my/requests/{serviceRequest}/edit', 'update')->name('requests.update');
                    Route::post('my/requests/{serviceRequest}/changes', 'proposeChange')->name('requests.changes.store');
                    Route::post('my/requests/{serviceRequest}/changes/{change}/{action}', 'changeAction')
                        ->whereIn('action', ['cancel', 'pay', 'pay-balance'])->name('requests.changes.action');
                    Route::post('my/requests/{serviceRequest}/messages', 'sendTeamMessage')->name('requests.messages.store');
                    Route::get('my/requests/{serviceRequest}/files/{file}', 'file')->name('requests.file');
                });

                // Service requests (freelancer side)
                Route::controller(FreelancerRequestController::class)->group(function () {
                    Route::get('client-requests', 'index')->name('freelancer.requests.index');
                    Route::get('client-requests/{serviceRequest}', 'show')->name('freelancer.requests.show');
                    Route::get('client-requests/{serviceRequest}/files/{file}', 'file')->name('freelancer.requests.file');
                    Route::post('client-requests/{serviceRequest}/offer', 'saveOffer')->name('freelancer.offers.save');
                    Route::post('client-requests/{serviceRequest}/changes/{change}', 'respondChange')->name('freelancer.changes.respond');
                    Route::get('my/offers', 'myOffers')->name('freelancer.offers.index');
                    Route::post('my/offers/{offer}/withdraw', 'withdraw')->name('freelancer.offers.withdraw');
                });

                // Offers: conversation and files for both parties, shortlist for the client
                Route::controller(OfferController::class)->group(function () {
                    Route::get('offers/{offer}/chat', 'chat')->name('offers.chat');
                    Route::post('offers/{offer}/messages', 'sendMessage')->name('offers.messages.store');
                    Route::post('offers/{offer}/save', 'toggleSave')->name('offers.save');
                    Route::get('offers/{offer}/files/{file}', 'file')->name('offers.file');
                });

                // Service 3 «مشروعك ما بيوقف»: contract page and agreement
                Route::controller(\App\Http\Controllers\front\ContractController::class)->group(function () {
                    Route::get('my/projects', 'index')->name('contracts.index');
                    Route::get('contracts/{contract}', 'show')->name('contracts.show');
                    Route::post('contracts/{contract}/agreement', 'propose')->name('contracts.agreement.propose');
                    Route::post('contracts/{contract}/agreement/{agreement}', 'respond')->name('contracts.agreement.respond');
                    Route::post('contracts/{contract}/fund', 'fund')->name('contracts.fund');
                    Route::post('contracts/{contract}/stages/{stage}/cancel-funding', 'cancelFunding')->name('contracts.fund.cancel');
                    Route::post('contracts/{contract}/stages/{stage}/deliver', 'deliver')->name('contracts.deliver');
                    Route::post('contracts/{contract}/deliveries/{delivery}', 'review')->name('contracts.review');
                    Route::get('contracts/{contract}/files/{file}', 'file')->name('contracts.file');
                    Route::post('contracts/{contract}/stages/{stage}/pause', 'pause')->name('contracts.pause');
                    Route::post('contracts/{contract}/pauses/{pause}/{action}', 'pauseAction')->whereIn('action', ['answer', 'object', 'resume'])->name('contracts.pause.action');
                    Route::post('contracts/{contract}/disputes', 'openDispute')->name('contracts.disputes.store');
                    Route::post('contracts/{contract}/disputes/{dispute}/{action}', 'disputeAction')->whereIn('action', ['respond', 'withdraw', 'decide', 'approve-proposal', 'reject-proposal'])->name('contracts.disputes.action');
                    Route::post('contracts/{contract}/change', 'requestChange')->name('contracts.change.request');
                    Route::post('contracts/{contract}/change/decide', 'decideChange')->name('contracts.change.decide');
                    Route::post('contracts/{contract}/change/{change}/proposal', 'settleChangeProposal')->name('contracts.change.proposal');
                    Route::post('contracts/{contract}/rating', 'rate')->name('contracts.rate');
                    Route::post('contracts/{contract}/addenda', 'proposeAddendum')->name('contracts.addenda.store');
                    Route::post('contracts/{contract}/addenda/{addendum}/{action}', 'addendumAction')->whereIn('action', ['approve', 'reject', 'cancel'])->name('contracts.addenda.action');
                    // Team tools without a dispute (admins only, checked in the controller + StaffPermissions)
                    Route::post('contracts/{contract}/stages/{stage}/extend', 'adminExtend')->name('contracts.admin.extend');
                    Route::post('contracts/{contract}/deliveries/{delivery}/admin-approve', 'adminApprove')->name('contracts.admin.approve');
                    Route::post('contracts/{contract}/pauses/{pause}/admin-end', 'adminEndPause')->name('contracts.admin.end-pause');
                    Route::post('contracts/{contract}/reply-requests', 'adminRequestReply')->name('contracts.admin.reply-request');
                    Route::post('contracts/{contract}/reply-requests/{reply}/answer', 'answerReply')->name('contracts.replies.answer');
                    Route::post('contracts/{contract}/reply-requests/{reply}/close', 'closeReply')->name('contracts.replies.close');
                });

                // Payment (escrow) and completion
                Route::controller(RequestPaymentController::class)->group(function () {
                    Route::post('offers/{offer}/accept', 'accept')->name('offers.accept');
                    Route::post('client-requests/{serviceRequest}/deliver', 'deliver')->name('freelancer.requests.deliver');
                    Route::post('my/requests/{serviceRequest}/complete', 'complete')->name('requests.complete');
                });
            });
        });
        // Start User Service
        Route::group(['middleware' => ['auth']], function () {
            Route::controller(\App\Http\Controllers\front\serviceController::class)->group(function () {
                Route::get('service/index', 'index');
                Route::match(['post', 'get'], 'service/add', 'add');
                Route::match(['post', 'get'], 'service/update/{id}', 'update');
                Route::post('service/delete/{id}', 'delete');
                Route::get('service/get-subcategories/{categoryId}', 'getSubCategories');
            });
        });

        ////////////// Cart Pages And Operations
        Route::controller(CartController::class)->group(function () {
            Route::get('cart', 'cart');
            Route::post('cart/add', 'AddToCart');
            Route::post('cart/delete', 'deleteCart');
        });
        Route::group(['middleware' => ['auth']], function () {
            Route::controller(CheckOutController::class)->group(function () {
                Route::get('checkout', 'index');
                Route::post('checkout/order', 'order');
                Route::post('create_order', 'create_order');
            });
        });

        ///////////////////////////// Start Conversation ////////////////////
        Route::controller(ConversationController::class)->middleware('auth')->group(function () {
            Route::get('chats', 'chats');
            Route::post('conversation/start', 'start_conversation');
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
            Route::post('product_order', 'store')->middleware('auth');
            Route::get('/download/product/{order}', 'downloadProduct')->name('download.product');
        });



        // The old projects/offers system was replaced by service requests.
        // Old links (pages, emails, notifications) redirect permanently.
        Route::permanentRedirect('projects', '/client-requests');
        Route::permanentRedirect('project/{project}', '/client-requests');
        Route::get('my/project/{path?}', fn () => redirect('/my/requests', 301))->where('path', '.*');

        #################### Start Tickets Controller ###########################
        ##################### Start User Courses ###################
        Route::controller(CourseController::class)->group(function () {
            Route::get('courses', 'index');
            Route::get('course/{id}-{slug}', 'course_details')->name('course_details');
        });

        ##################### Start New Courses System ###################
        Route::controller(NewCourseController::class)->group(function () {
            Route::get('new-courses', 'index')->name('courses.index');
            Route::get('courses/{slug}', 'show')->name('courses.show');
            Route::get('courses/{courseSlug}/topic/{topicId}', 'topic')->name('courses.topic');
            Route::get('courses/{courseSlug}/topic/{topicId}/lesson/{lessonId}', 'lesson')->name('courses.lesson');
        });

        // API route for video URL
        Route::get('api/courses/video/{lessonId}', [NewCourseController::class, 'getVideoUrl'])->middleware('video.protection');
        Route::get('courses/video-stream/{lessonId}', [NewCourseController::class, 'videoStream'])->middleware('video.protection')->name('courses.video-stream');

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
                Route::post('my/property/delete/{id}', 'delete');
                Route::post('delete-property-image/{id}', 'deletePropertyImage');
            });

            ################ End Properites #################

            ################# Start Properity Maintain #################
            Route::controller(ProperityMaintainController::class)->group(function () {
                Route::get('my/property/maintain/index', 'index');
                Route::match(['post', 'get'], 'my/property/maintain/add', 'store');
                Route::match(['post', 'get'], 'my/property/maintain/update/{id}', 'update');
                Route::post('my/property/maintain/delete/{id}', 'delete');
            });
            ################# End Properity Maintain #################

            ############## Start Jobs Controller ########################
            Route::controller(JobController::class)->group(function () {
                Route::get('my/jobs', 'index');
                Route::match(['post', 'get'], 'my/job/add', 'store');
                Route::match(['post', 'get'], 'my/job/update/{id}', 'update');
                Route::post('my/job/delete/{id}', 'delete');
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

            // Was 'categories', which clashed with the service categories page above.
            Route::get('blog', 'categories')->name('blogCategories');
            Route::get('category/{slug}', 'categoryDetails')->name('blogCategoryDetails');
            Route::get('blog/{slug}', 'blogDetails')->name('blogDetails');
        });
        #################### End Blog Routes ##########################

        // Back from EasyKash (every payment on the site); the token in the path stands in for the session.
        Route::get('payments/return/{onlinePayment}/{token}', [OnlinePaymentController::class, 'return'])->name('payments.return');


        Route::get('auth/{provider}/redirect', [SocialMediaController::class, 'redirect'])->whereIn('provider', ['google'])->name('auth.google.redirect');
        Route::get('auth/{provider}/callback', [SocialMediaController::class, 'callback'])->whereIn('provider', ['google'])->name('auth.google.callback');

        ############## Public Course Route
        Route::match(['post', 'get'], 'course/public/{url}', [PublicCourseRegisterController::class, 'RegisterCourse'])->name('registercourse');

        ######################### Start Package Controller #############################

        Route::controller(PackageController::class)->group(function () {
            Route::get('packages', 'index')->name('packages.index');
            Route::post('package/subscribe/{id}', 'subscribePlan')->name('subscribe.plan');
        });
        ######################### End Package Controller ################################

        ######################### Start Reviews Controller #############################

        Route::controller(ReviewsController::class)->group(function () {
            Route::get('reviews', 'index')->name('reviews.index');
            Route::post('reviews/post', 'store')->name('front.reviews.post');
        });
        ######################## End Reviews Controller ##################################

        ########################## Start User Portfolio Controller #######################
        Route::group(['middleware' => ['auth']], function () {
            Route::controller(PortfolioController::class)->prefix('portfolio/user')->group(function () {
                Route::get('index', 'index')->name('user.portfolio.index');
                Route::get('create', 'create')->name('user.portfolio.create');
                Route::post('store', 'store')->name('user.portfolio.store');
                Route::get('edit/{id}-{slug}', 'edit')->name('user.portfolio.edit');
                Route::post('update/{id}-{slug}', 'update')->name('user.portfolio.update');
                Route::post('delete/{id}', 'delete')->name('user.portfolio.delete');
            });
        });
        ######################### End User Portfolio Controller ##########################

        ####################### Start Public Portfolio ###################

        Route::controller(\App\Http\Controllers\front\PortfolioController::class)->group(function () {
            Route::get('portfolios', 'index')->name('portfolios.index');
            Route::get('portfolio/{id}-{slug}', 'details');
        });
        ####################### End Public Portfolio ###################
          ####################### Start Public Portfolio ###################

        Route::controller(NafizhaPortfolioController::class)->group(function () {
            Route::get('nafizha/portfolios', 'index');
            Route::get('nafizha/portfolio/{id}-{slug}', 'details');
        });
        ####################### End Public Portfolio ###################

        ####################### Start Testimonials ###################
        Route::get('testimonials', function() {
            return view('website.testimonials.index');
        })->name('testimonials.index');
        ####################### End Testimonials ###################
    }
);



Route::get('track/open/{token}', [App\Http\Controllers\api\EmailTrackingController::class, 'open'])->name('track.open');
Route::get('track/click/{token}', [App\Http\Controllers\api\EmailTrackingController::class, 'click'])->name('track.click');
Route::get('track/unsubscribe/{token}', [App\Http\Controllers\api\EmailTrackingController::class, 'unsubscribe'])->name('track.unsubscribe');

// EasyKash webhook for every payment on the site (no session, no CSRF, no locale prefix; HMAC signed).
// Signed email-confirmation link: outside the locale group so no redirect changes the signed URL.
Route::get('user/verify/{user}/{hash}', [\App\Http\Controllers\front\EmailVerificationController::class, 'verify'])
    ->middleware(['web', 'signed'])->name('email.verify');
Route::post('easykash/webhook', [OnlinePaymentController::class, 'webhook'])->name('easykash.webhook');

include 'admin.php';
