<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\admin\JobController;
use App\Http\Controllers\admin\BlogController;
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
use App\Http\Controllers\admin\MessagesController;
use \App\Http\Controllers\admin\CategoryController;
use App\Http\Controllers\admin\ProperityController;
use \App\Http\Controllers\admin\SubCategoryController;
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

Route::get('/admin', [AdminController::class, 'index'])->name('login');
Route::group(['prefix' => 'admin'], function () {
    Route::post('admin_login', [AdminController::class, 'admin_login']);
    // Start Register Page
    Route::match(['post', 'get'], 'register', [AdminController::class, 'register']);
    Route::group(['middleware' => ['auth']], function () {
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
            Route::get('service/get-subcategories/{categoryId}', 'getSubCategories');
        });
        Route::controller(UserController::class)->group(function () {
            Route::get('users', 'index');
            Route::post('block_status/{id}', 'blockStatus');
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
            Route::get('products', 'index')->name('admin.products.index');
            Route::match(['post', 'get'], 'product/add', 'store');
            Route::match(['post', 'get'], 'product/update/{slug}', 'update')->name('product.update');
            Route::post('product/delete/{id}', 'delete');
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

        #################### Project Controller ############################

        Route::controller(ProjectController::class)->group(function () {
            Route::get('projects', 'index');
            Route::match(['post', 'get'], 'project/update/{id}', 'update');
            Route::post('project/delete/{id}', 'delete');
            Route::post('project/update_status/{id}', 'update_status');
        });

        ################### End Project Controller ########################

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
            Route::match(['post', 'get'], 'properit/active/{id}', 'ActiveStatus');
        });
        ############## End Properiteis ###################
        ############## Start Properity Maintain ############
        Route::controller(ProperityMaintainController::class)->group(function () {
            Route::get('properity-maintain', 'index');
            Route::match(['post', 'get'], 'properity-maintain/update/{id}', 'update');
            Route::post('properity-maintain/delete/{id}', 'delete');
            Route::match(['post', 'get'], 'properity-maintain/active/{id}', 'ActiveStatus');
        });
        ############## End Properity Maintain #############

        ############### Start Jobs Controller  ################
        Route::controller(JobController::class)->group(function () {
            Route::get('jobs', 'index');
            Route::match(['post', 'get'], 'job/update/{id}', 'update');
            Route::post('job/delete/{id}', 'delete');
            Route::match(['post', 'get'], 'job/active/{id}', 'ActiveStatus');
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
            Route::get('nafizha-portfolio/delete/{id}', 'delete');
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
            Route::get('email/campaigns/{emailCampaign}/send', 'send')->name('admin.email.campaigns.send');
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

    });
});
