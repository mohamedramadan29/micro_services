<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\admin\JobController;
use App\Http\Controllers\admin\BlogController;
use App\Http\Controllers\admin\UserController;
use App\Http\Controllers\admin\AdminController;
use App\Http\Controllers\admin\OrderController;
use App\Http\Controllers\admin\CourseController;
use App\Http\Controllers\admin\TicketController;
use App\Http\Controllers\admin\ConsultController;
use App\Http\Controllers\admin\ProductController;
use App\Http\Controllers\admin\ServiceController;
use App\Http\Controllers\admin\SettingController;
use App\Http\Controllers\admin\MessagesController;
use App\Http\Controllers\admin\CategoryController;
use App\Http\Controllers\admin\ProperityController;
use App\Http\Controllers\admin\SubCategoryController;
use App\Http\Controllers\admin\BlogCategoryController;
use App\Http\Controllers\admin\NafizhaPortfolioController;
use App\Http\Controllers\admin\PackageController;
use App\Http\Controllers\admin\PackagetitleController;
use App\Http\Controllers\admin\WithdrawRequestController;
use App\Http\Controllers\admin\ProperityMaintainController;
use App\Http\Controllers\admin\PublicCoursesPageController;
use App\Http\Controllers\admin\ReviewsController;
use App\Http\Controllers\admin\ServiceOrdersController;
use App\Http\Controllers\admin\NewCourseController;
use App\Http\Controllers\admin\NewCourseTopicController;
use App\Http\Controllers\admin\NewCourseLessonController;
use App\Http\Controllers\admin\ReviewController;
use App\Http\Controllers\admin\SocialMediaController;
use App\Http\Controllers\admin\SocialPostController;
use App\Http\Controllers\admin\EmailListController;
use App\Http\Controllers\admin\EmailTemplateController;
use App\Http\Controllers\admin\EmailCampaignController;
use App\Http\Controllers\admin\EmailAnalyticsController;
use App\Http\Controllers\admin\EmailFollowUpController;
use App\Http\Controllers\admin\EmailAIController;
use App\Http\Controllers\admin\ServiceFormController;
use App\Http\Controllers\admin\ServiceFormSectionController;
use App\Http\Controllers\admin\ServiceFormQuestionController;
use App\Http\Controllers\admin\ServiceRequestAdminController;

Route::get('/admin', [AdminController::class, 'index'])->name('login');
Route::group(['prefix' => 'admin'], function () {
    Route::post('admin_login', [AdminController::class, 'admin_login']);
    Route::group(['middleware' => ['auth', 'admin']], function () {
        Route::controller(AdminController::class)->group(function () {
            Route::get('dashboard', 'dashboard')->name('admin.dashboard');
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
            Route::post('service/{id}/publish', 'publish')->name('admin.services.publish');
            Route::post('service/{id}/reject', 'reject')->name('admin.services.reject');
            Route::get('service/get-subcategories/{categoryId}', 'getSubCategories');
        });
        Route::controller(UserController::class)->group(function () {
            Route::get('users', 'index');
            Route::post('block_status/{id}', 'blockStatus');
        });

        Route::controller(CategoryController::class)->group(function () {
            Route::get('categories', fn () => redirect()->route('admin.catalog.groups'));
            Route::post('category/store', 'store');
            Route::post('category/update/{id}', 'update');
            Route::post('category/destroy/{id}', 'destroy');
        });
        Route::controller(SubCategoryController::class)->group(function () {
            Route::get('sub-categories', fn () => redirect()->route('admin.catalog.services'));
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
            Route::get('products', 'index')->name('admin.products.index');
            Route::match(['post', 'get'], 'product/add', 'store');
            Route::match(['post', 'get'], 'product/update/{slug}', 'update')->name('product.update');
            Route::post('product/delete/{id}', 'delete');
            Route::get('product/{id}/digital-file', 'digitalFile')->name('admin.products.digital-file');
            Route::get('/get-attribute-values/{attributeId}', 'getAttributeValues');
            Route::get('/get-subcategories', 'getSubCategories')->name('get.subcategories');
            Route::post('product/gallary/delete/{id}', 'delete_image_gallary');
            Route::post('/upload-chunk/{id}', 'uploadChunk');
            Route::post('/merge-chunks/{id}', 'mergeChunks');
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

        // Old projects admin page → the new service requests pages.
        Route::permanentRedirect('projects', '/admin/requests');

        ################ Start Courses ###################
        Route::controller(CourseController::class)->group(function () {
            Route::get('courses', 'index');
            Route::post('course/update_status/{id}', 'update_status');
            Route::post('course/delete/{id}', 'delete');
            Route::match(['post', 'get'], 'course/update/{id}', 'update');
            Route::get('course/user_register/{id}', 'register_users')->name('course.register_users');
        });
        ################# End Courses ####################
        ################## Start Order Controller ################
        Route::controller(OrderController::class)->group(function () {
            Route::get('orders', 'index');
            Route::match(['post', 'get'], 'order/update/{id}', 'update');
            Route::post('order/delete/{id}', 'delete');
        });
        ############### Start Properities ################
        Route::controller(ProperityController::class)->group(function () {
            Route::get('properities', 'index');
            Route::match(['post', 'get'], 'properity/update/{id}', 'update');
            Route::post('properity/delete/{id}', 'delete');
            Route::post('properit/active/{id}', 'ActiveStatus');
        });
        ############## End Properiteis ###################
        ############## Start Properity Maintain ############
        Route::controller(ProperityMaintainController::class)->group(function () {
            Route::get('properity-maintain', 'index');
            Route::match(['post', 'get'], 'properity-maintain/update/{id}', 'update');
            Route::post('properity-maintain/delete/{id}', 'delete');
            Route::post('properity-maintain/active/{id}', 'ActiveStatus');
        });
        ############## End Properity Maintain #############

        ############### Start Jobs Controller  ################
        Route::controller(JobController::class)->group(function () {
            Route::get('jobs', 'index');
            Route::match(['post', 'get'], 'job/update/{id}', 'update');
            Route::post('job/delete/{id}', 'delete');
            Route::post('job/active/{id}', 'ActiveStatus');
        });
        ############## End Jobs Controller  ###################

        ############# Start User Chat Controller ##############

        Route::controller(MessagesController::class)->group(function () {
            Route::get('chats', 'index');
            Route::get('chat/details/{id}', 'chatDetails');
        });
        ############## End User Chat Controller

        ############# Start WithDraw Controller ############
        Route::controller(WithdrawRequestController::class)->group(function () {
            Route::get('withdraws', 'index');
            Route::match(['post', 'get'], 'withdraw/update/{id}', 'update');
            Route::post('withdraw/delete/{id}', 'delete');
        });
        ############# End Withdraw Controller ###############

        ///////////////////  Start Blog Category //////////////////
        ///

        Route::controller(BlogCategoryController::class)->group(function () {
            Route::get('blog_category', 'index');
            Route::match(['post', 'get'], 'blog_category/add', 'store');
            Route::match(['post', 'get'], 'blog_category/update/{id}', 'update');
            Route::post('blog_category/delete/{id}', 'delete');
        });
        ///////////////////  Start Blog //////////////////
        ///
        Route::controller(BlogController::class)->group(function () {
            Route::get('blogs', 'index');
            Route::match(['post', 'get'], 'blog/add', 'store');
            Route::match(['post', 'get'], 'blog/update/{id}', 'update');
            Route::post('blog/delete/{id}', 'delete');
            Route::get('blog/schedule', 'schedule');
            Route::get('blog/archived', 'archived');
        });


        ################ Start Public Courses ###############

        Route::controller(PublicCoursesPageController::class)->group(function () {
            Route::get('public-courses/index', 'index');
            Route::match(['post', 'get'], 'public-courses/add', 'store')->name('public-courses.add');
            Route::match(['post', 'get'], 'public-courses/update/{id}', 'update')->name('public-courses.update');
            Route::post('public-courses/delete/{id}', 'delete');
            Route::get('public-courses/registers/{id}', 'registers');
        });

        ################### End Public Courses ##################

        ################### Start Package Controller ################
        Route::controller(PackageController::class)->group(function () {
            Route::get('packages', 'index');
            Route::get('package/add', 'create');
            Route::post('package/store', 'store');
            Route::get('package/edit/{id}', 'edit');
            Route::post('package/update/{id}', 'update');
            Route::post('package/delete/{id}', 'delete');
            Route::get('package/subscribes/{id}', 'showSubscribe');
        });
        ################## End Package Controller ###################

        ################## Start PackageTitle Controller ############
        Route::controller(PackagetitleController::class)->group(function () {
            Route::get('package-titles', 'index');
            Route::match(['post', 'get'], 'package-title/add', 'create');
            Route::match(['post', 'get'], 'package-title/edit/{id}', 'update');
            Route::post('package-title/delete/{id}', 'delete');
        });
        ################# End PackageTitle Controller ################

        ################# Start Review Controller ###################

        Route::controller(ReviewsController::class)->group(function () {
            Route::get('reviews', 'index');
            Route::get('review/{id}', 'details');
        });
        ################# End Review Controller #####################

        ################### Start Service Orders Controller #########

        Route::controller(ServiceOrdersController::class)->group(function () {
            Route::get('service-orders/index', 'index');
            Route::get('service-order/{id}', 'details');
        });
        ################### End Service Orders Controller ############

        #################### Start Nafizha Portfolio ################

        Route::controller(NafizhaPortfolioController::class)->group(function () {
            Route::get('nafizha-portfolio', 'index');
            Route::get('nafizha-portfolio/add', 'create');
            Route::post('nafizha-portfolio/store', 'store')->name('nafizha-portfolio.store');
            Route::get('nafizha-portfolio/edit/{id}', 'edit');
            Route::post('nafizha-portfolio/update/{id}', 'update');
            Route::post('nafizha-portfolio/delete/{id}', 'delete');
        });
        #################### End Nafizha Portfolio ###################

        #################### Start New Course System ###################
        Route::controller(NewCourseController::class)->group(function () {
            Route::get('new-courses', 'index')->name('admin.new-courses.index');
            Route::get('new-course/create', 'create')->name('admin.new-courses.create');
            Route::post('new-course/store', 'store')->name('admin.new-courses.store');
            Route::get('new-course/{newCourse}', 'show')->name('admin.new-courses.show');
            Route::get('new-course/{newCourse}/edit', 'edit')->name('admin.new-courses.edit');
            Route::post('new-course/{newCourse}/update', 'update')->name('admin.new-courses.update');
            Route::post('new-course/{newCourse}/delete', 'destroy')->name('admin.new-courses.destroy');
            Route::post('new-course/{newCourse}/toggle-status', 'toggleStatus')->name('admin.new-courses.toggle-status');
        });

        Route::controller(NewCourseTopicController::class)->group(function () {
            Route::get('new-course/{course}/topics', 'index')->name('admin.new-courses.topics.index');
            Route::get('new-course/{course}/topic/create', 'create')->name('admin.new-courses.topics.create');
            Route::post('new-course/{course}/topic/store', 'store')->name('admin.new-courses.topics.store');
            Route::get('new-course/{course}/topic/{topic}', 'show')->name('admin.new-courses.topics.show');
            Route::get('new-course/{course}/topic/{topic}/edit', 'edit')->name('admin.new-courses.topics.edit');
            Route::post('new-course/{course}/topic/{topic}/update', 'update')->name('admin.new-courses.topics.update');
            Route::post('new-course/{course}/topic/{topic}/delete', 'destroy')->name('admin.new-courses.topics.destroy');
        });

        Route::controller(NewCourseLessonController::class)->group(function () {
            Route::get('new-course/{course}/topic/{topic}/lessons', 'index')->name('admin.new-courses.lessons.index');
            Route::get('new-course/{course}/topic/{topic}/lesson/create', 'create')->name('admin.new-courses.lessons.create');
            Route::post('new-course/{course}/topic/{topic}/lesson/store', 'store')->name('admin.new-courses.lessons.store');
            Route::get('new-course/{course}/topic/{topic}/lesson/{lesson}', 'show')->name('admin.new-courses.lessons.show');
            Route::get('new-course/{course}/topic/{topic}/lesson/{lesson}/edit', 'edit')->name('admin.new-courses.lessons.edit');
            Route::post('new-course/{course}/topic/{topic}/lesson/{lesson}/update', 'update')->name('admin.new-courses.lessons.update');
            Route::post('new-course/{course}/topic/{topic}/lesson/{lesson}/delete', 'destroy')->name('admin.new-courses.lessons.destroy');
            Route::post('new-course/{course}/topic/{topic}/lesson/{lesson}/toggle-free', 'toggleFree')->name('admin.new-courses.lessons.toggle-free');
        });
        ################## End New Course System ####################

        #################### Start Reviews/Testimonials ###################
        Route::controller(ReviewController::class)->group(function () {
            Route::get('reviews', 'index')->name('admin.reviews.index');
            Route::get('reviews/create', 'create')->name('admin.reviews.create');
            Route::post('reviews/store', 'store')->name('admin.reviews.store');
            Route::get('reviews/{review}', 'show')->name('admin.reviews.show');
            Route::get('reviews/{review}/edit', 'edit')->name('admin.reviews.edit');
            Route::post('reviews/{review}/update', 'update')->name('admin.reviews.update');
            Route::post('reviews/{review}/delete', 'destroy')->name('admin.reviews.destroy');
            Route::post('reviews/{review}/toggle-status', 'toggleStatus')->name('admin.reviews.toggle-status');
            Route::post('reviews/{review}/toggle-approved', 'toggleApproved')->name('admin.reviews.toggle-approved');
            Route::post('reviews/{review}/toggle-featured', 'toggleFeatured')->name('admin.reviews.toggle-featured');
        });
        #################### End Reviews/Testimonials ####################

        #################### Start Social Media Manager ####################
        // لوحة تحكم السوشيال ميديا
        Route::controller(SocialMediaController::class)->group(function () {
            Route::get('social-media', 'index')->name('admin.social.index');
            Route::get('social-media/accounts', 'accounts')->name('admin.social.accounts');
            Route::post('social-media/account/{account}/toggle', 'toggleAccount')->name('admin.social.toggle');
            Route::post('social-media/account/{account}/delete', 'deleteAccount')->name('admin.social.delete');

            // OAuth - Facebook
            Route::get('social-media/connect/facebook', 'connectFacebook')->name('admin.social.facebook.connect');
            Route::get('social-media/callback/facebook', 'facebookCallback')->name('admin.social.facebook.callback');

            // OAuth - Instagram
            Route::get('social-media/connect/instagram', 'connectInstagram')->name('admin.social.instagram.connect');
            Route::get('social-media/callback/instagram', 'instagramCallback')->name('admin.social.instagram.callback');

            // OAuth - TikTok
            Route::get('social-media/connect/tiktok', 'connectTikTok')->name('admin.social.tiktok.connect');
            Route::get('social-media/callback/tiktok', 'tikTokCallback')->name('admin.social.tiktok.callback');

            // OAuth - YouTube
            Route::get('social-media/connect/youtube', 'connectYouTube')->name('admin.social.youtube.connect');
            Route::get('social-media/callback/youtube', 'youTubeCallback')->name('admin.social.youtube.callback');

            // OAuth - LinkedIn
            Route::get('social-media/connect/linkedin', 'connectLinkedIn')->name('admin.social.linkedin.connect');
            Route::get('social-media/callback/linkedin', 'linkedInCallback')->name('admin.social.linkedin.callback');

            // OAuth - Twitter
            Route::get('social-media/connect/twitter', 'connectTwitter')->name('admin.social.twitter.connect');
            Route::get('social-media/callback/twitter', 'twitterCallback')->name('admin.social.twitter.callback');
        });

        // إدارة البوستات
        Route::controller(SocialPostController::class)->group(function () {
            Route::get('social-media/post/create', 'create')->name('admin.social.post.create');
            Route::post('social-media/post/store', 'store')->name('admin.social.post.store');
            Route::get('social-media/posts/scheduled', 'scheduled')->name('admin.social.scheduled');
            Route::get('social-media/posts/published', 'published')->name('admin.social.published');
            Route::get('social-media/post/{post}', 'show')->name('admin.social.post.show');
            Route::post('social-media/post/{post}/retry', 'retry')->name('admin.social.post.retry');
            Route::post('social-media/post/{post}/delete', 'destroy')->name('admin.social.post.delete');
        });
        #################### End Social Media Manager ####################

        #################### Start Email Campaigns ####################
        Route::controller(EmailListController::class)->group(function () {
            Route::get('email/lists', 'index')->name('admin.email.lists.index');
            Route::get('email/lists/create', 'create')->name('admin.email.lists.create');
            Route::post('email/lists/store', 'store')->name('admin.email.lists.store');
            Route::get('email/lists/{emailList}', 'show')->name('admin.email.lists.show');
            Route::get('email/lists/{emailList}/edit', 'edit')->name('admin.email.lists.edit');
            Route::post('email/lists/{emailList}/update', 'update')->name('admin.email.lists.update');
            Route::post('email/lists/{emailList}/delete', 'destroy')->name('admin.email.lists.destroy');
            Route::post('email/lists/{emailList}/toggle-status', 'toggleStatus')->name('admin.email.lists.toggle-status');
            Route::get('email/lists/{emailList}/import', 'importForm')->name('admin.email.lists.import');
            Route::post('email/lists/{emailList}/import/store', 'importStore')->name('admin.email.lists.import.store');
            Route::post('email/lists/{emailList}/contacts/store', 'contactStore')->name('admin.email.lists.contact.store');
            Route::post('email/lists/{emailList}/contacts/{contact}/delete', 'contactDestroy')->name('admin.email.lists.contact.destroy');
            Route::post('email/lists/{emailList}/import-users', 'importUsers')->name('admin.email.lists.import.users');
        });

        Route::controller(EmailTemplateController::class)->group(function () {
            Route::get('email/templates', 'index')->name('admin.email.templates.index');
            Route::get('email/templates/create', 'create')->name('admin.email.templates.create');
            Route::post('email/templates/store', 'store')->name('admin.email.templates.store');
            Route::get('email/templates/{emailTemplate}', 'show')->name('admin.email.templates.show');
            Route::get('email/templates/{emailTemplate}/edit', 'edit')->name('admin.email.templates.edit');
            Route::post('email/templates/{emailTemplate}/update', 'update')->name('admin.email.templates.update');
            Route::post('email/templates/{emailTemplate}/delete', 'destroy')->name('admin.email.templates.destroy');
            Route::post('email/templates/{emailTemplate}/toggle-status', 'toggleStatus')->name('admin.email.templates.toggle-status');
        });

        Route::controller(EmailCampaignController::class)->group(function () {
            Route::get('email/campaigns', 'index')->name('admin.email.campaigns.index');
            Route::get('email/campaigns/create', 'create')->name('admin.email.campaigns.create');
            Route::post('email/campaigns/store', 'store')->name('admin.email.campaigns.store');
            Route::get('email/campaigns/{emailCampaign}', 'show')->name('admin.email.campaigns.show');
            Route::get('email/campaigns/{emailCampaign}/edit', 'edit')->name('admin.email.campaigns.edit');
            Route::post('email/campaigns/{emailCampaign}/update', 'update')->name('admin.email.campaigns.update');
            Route::post('email/campaigns/{emailCampaign}/delete', 'destroy')->name('admin.email.campaigns.destroy');
            Route::post('email/campaigns/{emailCampaign}/toggle-status', 'toggleStatus')->name('admin.email.campaigns.toggle-status');
            Route::post('email/campaigns/{emailCampaign}/send', 'send')->name('admin.email.campaigns.send');
            Route::post('email/campaigns/{emailCampaign}/duplicate', 'duplicate')->name('admin.email.campaigns.duplicate');
        });

        Route::controller(EmailAnalyticsController::class)->group(function () {
            Route::get('email/analytics', 'index')->name('admin.email.analytics');
        });

        Route::controller(EmailFollowUpController::class)->group(function () {
            Route::get('email/campaigns/{emailCampaign}/follow-ups/create', 'create')->name('admin.email.follow-ups.create');
            Route::post('email/campaigns/{emailCampaign}/follow-ups/store', 'store')->name('admin.email.follow-ups.store');
            Route::get('email/campaigns/{emailCampaign}/follow-ups/{emailFollowUp}/edit', 'edit')->name('admin.email.follow-ups.edit');
            Route::post('email/campaigns/{emailCampaign}/follow-ups/{emailFollowUp}/update', 'update')->name('admin.email.follow-ups.update');
            Route::post('email/campaigns/{emailCampaign}/follow-ups/{emailFollowUp}/delete', 'destroy')->name('admin.email.follow-ups.destroy');
            Route::post('email/campaigns/{emailCampaign}/follow-ups/{emailFollowUp}/toggle', 'toggle')->name('admin.email.follow-ups.toggle');
        });

        Route::controller(EmailAIController::class)->group(function () {
            Route::post('email/ai/generate', 'generate')->name('admin.email.ai.generate');
            Route::post('email/ai/embed-video', 'embedVideo')->name('admin.email.ai.embed-video');
        });

        Route::controller(SettingController::class)->group(function () {
            Route::get('email/gmail/settings', 'gmailSettings')->name('admin.email.gmail.settings');
            Route::post('email/gmail/update', 'gmailUpdate')->name('admin.email.gmail.update');
            Route::get('email/gmail/callback', 'gmailCallback')->name('admin.email.gmail.callback');
            Route::post('email/gmail/disconnect', 'gmailDisconnect')->name('admin.email.gmail.disconnect');
            Route::get('email/sheets/auth', 'sheetsAuth')->name('admin.email.sheets.auth');
            Route::get('email/sheets/callback', 'sheetsCallback')->name('admin.email.sheets.callback');
        });

        Route::controller(EmailListController::class)->group(function () {
            Route::post('email/lists/{emailList}/import-sheets', 'importSheets')->name('admin.email.lists.import.sheets');
        });
        #################### End Email Campaigns ####################

        #################### Service Request Forms ####################
        Route::controller(ServiceFormController::class)->group(function () {
            Route::get('request-forms', 'index')->name('admin.request-forms.index');
            Route::get('request-forms/service/{subCategory}', 'show')->name('admin.request-forms.show');
            Route::post('request-forms/service/{subCategory}', 'store')->name('admin.request-forms.store');
            Route::post('request-forms/{form}/status', 'updateStatus')->name('admin.request-forms.status');
            Route::post('request-forms/{form}/reorder', 'reorder')->name('admin.request-forms.reorder');
            Route::get('request-forms/{form}/preview', 'preview')->name('admin.request-forms.preview');
            Route::get('request-forms/{form}/builder', 'builder')->name('admin.request-forms.builder');
            Route::post('request-forms/{form}/revision', 'createRevision')->name('admin.request-forms.revision');
            Route::post('request-forms/{form}/revision/publish', 'publishRevision')->name('admin.request-forms.revision.publish');
            Route::post('request-forms/{form}/revision/discard', 'discardRevision')->name('admin.request-forms.revision.discard');
        });
        Route::controller(ServiceFormSectionController::class)->group(function () {
            Route::post('request-forms/{form}/sections', 'store')->name('admin.request-forms.sections.store');
            Route::post('request-forms/sections/{section}/update', 'update')->name('admin.request-forms.sections.update');
            Route::post('request-forms/sections/{section}/duplicate', 'duplicate')->name('admin.request-forms.sections.duplicate');
            Route::post('request-forms/sections/{section}/delete', 'destroy')->name('admin.request-forms.sections.destroy');
        });
        Route::controller(ServiceFormQuestionController::class)->group(function () {
            Route::get('request-forms/{form}/questions/create', 'create')->name('admin.request-forms.questions.create');
            Route::post('request-forms/{form}/questions', 'store')->name('admin.request-forms.questions.store');
            Route::get('request-forms/questions/{question}/edit', 'edit')->name('admin.request-forms.questions.edit');
            Route::post('request-forms/questions/{question}/update', 'update')->name('admin.request-forms.questions.update');
            Route::post('request-forms/questions/{question}/duplicate', 'duplicate')->name('admin.request-forms.questions.duplicate');
            Route::post('request-forms/questions/{question}/delete', 'destroy')->name('admin.request-forms.questions.destroy');
        });
        #################### End Service Request Forms ####################

        #################### Service Requests (lists, follow-up, results) ####################
        Route::controller(ServiceRequestAdminController::class)->group(function () {
            Route::view('requests-guide', 'admin.service-requests.guide')->name('admin.requests.guide');
            Route::get('requests', 'index')->name('admin.requests.index');
            Route::get('requests/{serviceRequest}', 'show')->name('admin.requests.show');
            Route::post('requests/{serviceRequest}/followup', 'followup')->name('admin.requests.followup');
            Route::post('requests/{serviceRequest}/messages', 'sendMessage')->name('admin.requests.messages.store');
            Route::post('requests/{serviceRequest}/close', 'close')->name('admin.requests.close');
            Route::post('requests/{serviceRequest}/publish', 'publish')->name('admin.requests.publish');
            Route::post('requests/{serviceRequest}/complete', 'complete')->name('admin.requests.complete');
            Route::post('requests/{serviceRequest}/invite/{freelancer}', 'invite')->name('admin.requests.invite');
            Route::post('request-payments/{payment}/refund', 'refund')->name('admin.requests.refund');
            Route::get('request-offers/{offer}/files/{file}', 'offerFile')->name('admin.requests.offer-file');
            Route::post('notifications/read-all', 'readAllNotifications')->name('admin.notifications.read-all');
        });
        Route::controller(\App\Http\Controllers\admin\ClientController::class)->group(function () {
            Route::get('clients', 'index')->name('admin.clients.index');
            Route::get('clients/{user}', 'show')->name('admin.clients.show');
        });

        #################### Services page: groups, services, help requests ####################
        Route::controller(\App\Http\Controllers\admin\CatalogController::class)->group(function () {
            Route::get('catalog/groups', 'groups')->name('admin.catalog.groups');
            Route::get('catalog/groups/create', 'editGroup')->name('admin.catalog.groups.create');
            Route::post('catalog/groups', 'saveGroup')->name('admin.catalog.groups.store');
            Route::get('catalog/groups/{group}/edit', 'editGroup')->name('admin.catalog.groups.edit');
            Route::post('catalog/groups/{group}', 'saveGroup')->name('admin.catalog.groups.update');
            Route::post('catalog/groups/{group}/delete', 'deleteGroup')->name('admin.catalog.groups.destroy');
            Route::get('catalog/services', 'services')->name('admin.catalog.services');
            Route::get('catalog/services/create', 'editService')->name('admin.catalog.services.create');
            Route::post('catalog/services', 'saveService')->name('admin.catalog.services.store');
            Route::get('catalog/services/{service}/edit', 'editService')->name('admin.catalog.services.edit');
            Route::post('catalog/services/{service}', 'saveService')->name('admin.catalog.services.update');
            Route::post('catalog/services/{service}/delete', 'deleteService')->name('admin.catalog.services.destroy');
        });
        Route::controller(\App\Http\Controllers\admin\HelpRequestAdminController::class)->group(function () {
            Route::get('help-requests', 'index')->name('admin.help-requests.index');
            Route::get('help-requests/{helpRequest}', 'show')->name('admin.help-requests.show');
            Route::post('help-requests/{helpRequest}', 'update')->name('admin.help-requests.update');
            Route::post('help-requests/{helpRequest}/notes', 'addNote')->name('admin.help-requests.notes');
        });
        // «نفّذها للأعمال» (client file «قسم الشركات», phase 1).
        Route::controller(\App\Http\Controllers\admin\BusinessAdminController::class)->prefix('business')->group(function () {
            Route::get('requests', 'requests')->name('admin.business.requests');
            Route::get('requests/{businessRequest}', 'showRequest')->where('businessRequest', 'BIZ-[0-9]+')->name('admin.business.requests.show');
            Route::post('requests/{businessRequest}', 'updateRequest')->where('businessRequest', 'BIZ-[0-9]+')->name('admin.business.requests.update');
            Route::post('requests/{businessRequest}/note', 'note')->where('businessRequest', 'BIZ-[0-9]+')->name('admin.business.requests.note');
            Route::post('requests/{businessRequest}/quote', 'quote')->where('businessRequest', 'BIZ-[0-9]+')->name('admin.business.requests.quote');
            Route::post('requests/{businessRequest}/decision', 'decideForClient')->where('businessRequest', 'BIZ-[0-9]+')->name('admin.business.requests.decision');
            Route::post('requests/{businessRequest}/payment', 'payment')->where('businessRequest', 'BIZ-[0-9]+')->name('admin.business.requests.payment');
            Route::post('requests/{businessRequest}/account', 'linkAccount')->where('businessRequest', 'BIZ-[0-9]+')->name('admin.business.requests.account');
            Route::post('requests/{businessRequest}/convert', 'convert')->where('businessRequest', 'BIZ-[0-9]+')->name('admin.business.requests.convert');
            Route::get('projects', 'projects')->name('admin.business.projects');
            Route::get('projects/{businessProject}', 'showProject')->where('businessProject', 'BPR-[0-9]+')->name('admin.business.projects.show');
            Route::post('projects/{businessProject}', 'updateProject')->where('businessProject', 'BPR-[0-9]+')->name('admin.business.projects.update');
            Route::post('projects/{businessProject}/note', 'projectNote')->where('businessProject', 'BPR-[0-9]+')->name('admin.business.projects.note');
            Route::post('projects/{businessProject}/tasks', 'saveTask')->where('businessProject', 'BPR-[0-9]+')->name('admin.business.tasks.store');
            Route::post('projects/{businessProject}/tasks/{task}', 'saveTask')->where('businessProject', 'BPR-[0-9]+')->whereNumber('task')->name('admin.business.tasks.update');
            Route::post('projects/{businessProject}/tasks/{task}/move', 'moveTask')->where('businessProject', 'BPR-[0-9]+')->whereNumber('task')->name('admin.business.tasks.move');
            Route::post('projects/{businessProject}/tasks/{task}/delete', 'deleteTask')->where('businessProject', 'BPR-[0-9]+')->whereNumber('task')->name('admin.business.tasks.destroy');
            Route::post('projects/{businessProject}/deliveries', 'deliver')->where('businessProject', 'BPR-[0-9]+')->name('admin.business.deliveries.store');
            Route::get('settings', 'settings')->name('admin.business.settings');
        });
        // Suggestions (client file «نظام الاقتراحات»); abilities checked in the controller.
        Route::controller(\App\Http\Controllers\admin\SuggestionAdminController::class)->group(function () {
            Route::get('suggestions', 'index')->name('admin.suggestions.index');
            Route::get('suggestions/export', 'export')->name('admin.suggestions.export');
            Route::get('suggestions/settings', 'settings')->name('admin.suggestions.settings');
            Route::post('suggestions/settings/categories', 'saveCategory')->name('admin.suggestions.categories.store');
            Route::post('suggestions/settings/categories/{category}', 'saveCategory')->name('admin.suggestions.categories.update');
            Route::post('suggestions/settings/categories/{category}/delete', 'deleteCategory')->name('admin.suggestions.categories.destroy');
            Route::get('suggestions/{suggestion}', 'show')->where('suggestion', 'SUG-[0-9]+')->name('admin.suggestions.show');
            Route::post('suggestions/{suggestion}', 'update')->where('suggestion', 'SUG-[0-9]+')->name('admin.suggestions.update');
            Route::post('suggestions/{suggestion}/notes', 'addNote')->where('suggestion', 'SUG-[0-9]+')->name('admin.suggestions.notes');
            Route::post('suggestions/{suggestion}/duplicate', 'linkDuplicate')->where('suggestion', 'SUG-[0-9]+')->name('admin.suggestions.duplicate');
        });

        #################### Freelancer applications and directory ####################
        Route::controller(\App\Http\Controllers\admin\FreelancerAdminController::class)->group(function () {
            Route::get('freelancers', 'index')->name('admin.freelancers.index');
            Route::get('freelancers/{freelancer}', 'show')->name('admin.freelancers.show');
            Route::post('freelancers/{freelancer}/verify', 'verify')->name('admin.freelancers.verify');
            Route::post('freelancers/{freelancer}/verify-email', 'resendVerification')->name('admin.freelancers.verify-email');
            Route::post('freelancers/{freelancer}/photo', 'photo')->name('admin.freelancers.photo');
            Route::post('freelancers/{freelancer}/decide', 'decide')->name('admin.freelancers.decide');
            Route::post('freelancers/{freelancer}/feature', 'feature')->name('admin.freelancers.feature');
            Route::post('freelancers/{freelancer}/delete', 'destroy')->name('admin.freelancers.destroy');
        });
        Route::controller(\App\Http\Controllers\admin\FreelancerWorkController::class)->group(function () {
            Route::get('freelancer-works', 'index')->name('admin.freelancer-works.index');
            Route::post('freelancer-works/{work}/publish', 'publish')->name('admin.freelancer-works.publish');
            Route::post('freelancer-works/{work}/reject', 'reject')->name('admin.freelancer-works.reject');
        });

        #################### SEO keywords ####################
        Route::controller(\App\Http\Controllers\admin\SeoKeywordController::class)->group(function () {
            Route::get('seo', 'index')->name('admin.seo.index');
            Route::get('seo/queries', 'queries')->name('admin.seo.queries');
            Route::post('seo/keywords', 'store')->name('admin.seo.store');
            Route::post('seo/keywords/delete', 'destroy')->name('admin.seo.destroy');
            Route::post('seo/keywords/{keyword}', 'update')->name('admin.seo.update');
            Route::post('seo/track', 'track')->name('admin.seo.track');
            Route::get('seo/export', 'export')->name('admin.seo.export');
            Route::post('seo/import', 'import')->name('admin.seo.import');
            Route::post('seo/sync', 'sync')->name('admin.seo.sync');
        });

        #################### Service 3: contracts, disputes, freelancer changes ####################
        Route::get('contracts', [\App\Http\Controllers\admin\ContractAdminController::class, 'index'])->name('admin.contracts.index');
        Route::get('contract-policy', [\App\Http\Controllers\admin\ContractPolicyController::class, 'index'])->name('admin.contract-policy.index');
        Route::post('contract-policy', [\App\Http\Controllers\admin\ContractPolicyController::class, 'save'])->name('admin.contract-policy.save');
        Route::get('staff-roles', [\App\Http\Controllers\admin\StaffRoleController::class, 'index'])->name('admin.staff.index');
        Route::post('staff-roles', [\App\Http\Controllers\admin\StaffRoleController::class, 'update'])->name('admin.staff.update');

        #################### Landing pages ####################
        Route::controller(\App\Http\Controllers\admin\LandingPageController::class)->group(function () {
            Route::get('landing-pages', 'index')->name('admin.landing.index');
            Route::post('landing-pages', 'store')->name('admin.landing.store');
            Route::get('landing-pages/{page}', 'edit')->name('admin.landing.edit');
            Route::post('landing-pages/{page}/settings', 'settings')->name('admin.landing.settings');
            Route::post('landing-pages/{page}/sections', 'addSection')->name('admin.landing.sections.store');
            Route::post('landing-pages/{page}/sections/{section}', 'updateSection')->name('admin.landing.sections.update');
            Route::post('landing-pages/{page}/sections/{section}/{action}', 'sectionAction')
                ->whereIn('action', ['up', 'down', 'toggle', 'duplicate', 'delete'])->name('admin.landing.sections.action');
            Route::post('landing-pages/{page}/publish', 'publish')->name('admin.landing.publish');
            Route::post('landing-pages/{page}/discard', 'discard')->name('admin.landing.discard');
            Route::post('landing-pages/{page}/unpublish', 'unpublish')->name('admin.landing.unpublish');
            Route::post('landing-pages/{page}/delete', 'destroy')->name('admin.landing.destroy');
        });

        #################### Journey analytics ####################
        Route::controller(\App\Http\Controllers\admin\AnalyticsAdminController::class)->group(function () {
            Route::get('analytics', 'index')->name('admin.analytics.index');
            Route::get('analytics/visits/{visit}', 'show')->name('admin.analytics.show');
        });

        #################### Free site check ####################
        Route::controller(\App\Http\Controllers\admin\SiteTestAdminController::class)->group(function () {
            Route::get('site-tests', 'index')->name('admin.site-tests.index');
            Route::get('site-tests/settings', 'settings')->name('admin.site-tests.settings');
            Route::post('site-tests/settings', 'saveSettings')->name('admin.site-tests.settings.save');
            Route::post('site-tests/questions', 'saveQuestions')->name('admin.site-tests.questions.save');
            Route::post('site-tests/rules', 'saveRule')->name('admin.site-tests.rules.save');
            Route::post('site-tests/rules/{rule}/delete', 'deleteRule')->name('admin.site-tests.rules.destroy');
            Route::get('site-tests/{siteTest}', 'show')->name('admin.site-tests.show');
            Route::post('site-tests/{siteTest}/review', 'review')->name('admin.site-tests.review');
            Route::post('site-tests/{siteTest}/send', 'send')->name('admin.site-tests.send');
            Route::post('site-tests/{siteTest}/issues', 'addIssue')->name('admin.site-tests.issues.store');
            Route::post('site-tests/{siteTest}/issues/{issue}/toggle', 'toggleIssue')->name('admin.site-tests.issues.toggle');
            Route::get('site-tests/{siteTest}/raw/{result}', 'raw')->name('admin.site-tests.raw');
        });

        Route::get('notification-texts', [\App\Http\Controllers\admin\NotificationTextController::class, 'index'])->name('admin.notification-texts.index');
        Route::post('notification-texts', [\App\Http\Controllers\admin\NotificationTextController::class, 'save'])->name('admin.notification-texts.save');
        Route::get('notification-log', [\App\Http\Controllers\admin\NotificationLogController::class, 'index'])->name('admin.notification-log.index');
        Route::get('home-cards', [\App\Http\Controllers\admin\HomeCardAdminController::class, 'index'])->name('admin.home-cards.index');

        #################### Site content (texts, links, images) ####################
        Route::controller(\App\Http\Controllers\admin\ContentController::class)->group(function () {
            Route::get('content', 'index')->name('admin.content.index');
            Route::post('content/blocks/{block}', 'update')->name('admin.content.update');
            Route::post('content/areas/{area}/add', 'store')->name('admin.content.store');
            Route::post('content/blocks/{block}/delete', 'destroy')->name('admin.content.destroy');
            Route::post('content/blocks/{block}/toggle', 'toggle')->name('admin.content.toggle');
            Route::post('content/blocks/{block}/move/{direction}', 'move')->whereIn('direction', ['up', 'down'])->name('admin.content.move');
            Route::post('content/{page}/publish', 'publish')->name('admin.content.publish');
            Route::post('content/{page}/discard', 'discard')->name('admin.content.discard');
            Route::get('content/{page}/preview', 'preview')->name('admin.content.preview');
            Route::get('content/preview/stop', 'stopPreview')->name('admin.content.preview.stop');
            Route::post('content/revisions/{revision}/rollback', 'rollback')->name('admin.content.rollback');
        });
        Route::controller(\App\Http\Controllers\admin\ContentMediaController::class)->group(function () {
            Route::get('media', 'index')->name('admin.media.index');
            Route::post('media', 'store')->name('admin.media.store');
            Route::post('media/{medium}', 'update')->name('admin.media.update');
            Route::post('media/{medium}/delete', 'destroy')->name('admin.media.destroy');
        });

    });
});
