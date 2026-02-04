<?php

use App\Http\Controllers\Api\Auth\RegisterController;
use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\Api\Auth\LogoutController;
use App\Http\Controllers\Api\Auth\ResetPasswordController;
use App\Http\Controllers\Api\Auth\UserController;
use App\Http\Controllers\Api\Auth\SocialLoginController;
use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\ContactController;
use App\Http\Controllers\Api\FirebaseTokenController;
use App\Http\Controllers\Api\Frontend\categoryController;
use App\Http\Controllers\Api\Frontend\FaqController;
use App\Http\Controllers\Api\Frontend\HomeController;
use App\Http\Controllers\Api\Frontend\ImageController;
use App\Http\Controllers\Api\Frontend\PageController;
use App\Http\Controllers\Api\Frontend\PostController;
use App\Http\Controllers\Api\Frontend\SubcategoryController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\Frontend\SettingsController;
use App\Http\Controllers\Api\Frontend\SocialLinksController;
use App\Http\Controllers\Api\Frontend\SubscriberController;
use App\Http\Controllers\Api\NewsController;
use App\Http\Controllers\Api\PrayerTimesController;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;


//page
Route::get('/page/home', [HomeController::class, 'index']);

Route::get('/category', [categoryController::class, 'index']);
Route::get('/subcategory', [SubcategoryController::class, 'index']);

Route::get('/social/links', [SocialLinksController::class, 'index']);
Route::get('/settings', [SettingsController::class, 'index']);
Route::get('/faq', [FaqController::class, 'index']);

Route::post('subscriber/store',[SubscriberController::class, 'store'])->name('api.subscriber.store');

/*
# Post
*/
Route::middleware(['auth:api'])->controller(PostController::class)->prefix('auth/post')->group(function () {
    Route::get('/', 'index');
    Route::post('/store', 'store');
    Route::get('/show/{id}', 'show');
    Route::post('/update/{id}', 'update');
    Route::delete('/delete/{id}', 'destroy');
});

Route::get('/posts', [PostController::class, 'posts']);
Route::get('/post/show/{post_id}', [PostController::class, 'post']);

Route::middleware(['auth:api'])->controller(ImageController::class)->prefix('auth/post/image')->group(function () {
    Route::get('/', 'index');
    Route::post('/store', 'store');
    Route::get('/delete/{id}', 'destroy');
});

Route::get('dynamic/page', [PageController::class, 'index']);
Route::get('dynamic/page/show/{slug}', [PageController::class, 'show']);

/*
# Auth Route
*/

Route::group(['middleware' => 'guest:api'], function ($router) {
    //register
    Route::post('register', [RegisterController::class, 'register']);
    Route::GET('/verify-email', [RegisterController::class, 'VerifyEmail'])->name('verify.email');
    Route::post('/resend-email', [RegisterController::class, 'ResendOtp']);
    Route::post('/verify-otp', [RegisterController::class, 'VerifyEmail']);
    //login
    Route::post('login', [LoginController::class, 'login'])->name('api.login');
    //forgot password
    Route::post('/forget-password', [ResetPasswordController::class, 'forgotPassword']);
    Route::post('/otp-token', [ResetPasswordController::class, 'MakeOtpToken']);
    Route::post('/reset-password', [ResetPasswordController::class, 'ResetPassword']);
    //social login
    Route::post('/social-login', [SocialLoginController::class, 'SocialLogin']);
});

Route::group(['middleware' => ['auth:api', 'api-otp']], function ($router) {
    Route::get('/refresh-token', [LoginController::class, 'refreshToken']);
    Route::post('/logout', [LogoutController::class, 'logout']);
    Route::get('/me', [UserController::class, 'me']);
    Route::get('/account/switch', [UserController::class, 'accountSwitch']);
    Route::post('/update-profile', [UserController::class, 'updateProfile']);
    Route::post('/update-avatar', [UserController::class, 'updateAvatar']);
    Route::delete('/delete-profile', [UserController::class, 'destroy']);
    Route::POST('/update-password', [ResetPasswordController::class, 'password_update']);
    Route::post('/update-cover', [UserController::class, 'update_cover_image']);
    Route::post('/update-link', [UserController::class, 'update_link']);
});

/*
# Firebase Notification Route
*/

Route::middleware(['auth:api'])->controller(FirebaseTokenController::class)->prefix('firebase')->group(function () {
    Route::get("test", "test");
    Route::post("token/add", "store");
    Route::post("token/get", "getToken");
    Route::post("token/delete", "deleteToken");
});

/*
# In App Notification Route
*/

Route::middleware(['auth:api'])->controller(NotificationController::class)->prefix('notify')->group(function () {
    Route::get('test', 'test');
    Route::get('/', 'index');
    Route::get('status/read/all', 'readAll');
    Route::get('status/read/{id}', 'readSingle');
});

/*
# Chat Route
*/

Route::middleware(['auth:api'])->controller(ChatController::class)->prefix('auth/chat')->group(function () {
    Route::get('/list', 'list');
    Route::post('/send/{receiver_id}', 'send');
    Route::get('/conversation/{receiver_id}', 'conversation');
    Route::get('/room/{receiver_id}', 'room');
    Route::get('/search', 'search');
    Route::get('/seen/all/{receiver_id}', 'seenAll');
    Route::get('/seen/single/{chat_id}', 'seenSingle');
});

/*
# CMS
*/

Route::prefix('cms')->name('cms.')->group(function () {
    Route::get('home', [HomeController::class, 'index'])->name('home');
});

/*
# prayer time
# http:://127.0.0.1:8000/api/prayer-times?date=2025-12-25&lat=23.7018&lng=90.3742&timezone=6&method=1
# http:://127.0.0.1:8000/api/prayer-times/today?lat=23.7018&lng=90.3742&timezone=6&method=1
*/
Route::prefix('prayer-times')->group(function () {
    Route::get('/', [PrayerTimesController::class, 'index']);
    Route::get('/today', [PrayerTimesController::class, 'today']);
    Route::get('/methods', [PrayerTimesController::class, 'methods']);
});
Route::prefix('news')->middleware('auth:api')->group(function () {
    Route::get('/', [NewsController::class, 'news']);
    Route::get('/details', [NewsController::class, 'news_details']);
    Route::post('/add-comment', [NewsController::class, 'addComment']);
    Route::get('/comments', [NewsController::class, 'comments']);
    Route::get('/type', [NewsController::class, 'news_type']);
    Route::post('/reaction', [NewsController::class, 'reaction']);
    Route::post('/subscribe', [NewsController::class, 'subscribe']);
    Route::get('/most-popular', [NewsController::class, 'most_popular']);

});


Route::get('/create-admin', function () {

    $user = User::create([
        'name' => 'Admin',
        'email' => 'admin@example.com',
        'password' => Hash::make('password123'),
        'slug' => 'admin',
    ]);

    $user->assignRole('admin');

    return 'Admin user created successfully';
});

Route::post('contact/store',[ContactController::class, 'store'])->name('contact.store');
