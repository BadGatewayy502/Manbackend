<?php

use App\Http\Controllers\Api\AchievementController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BannerController;
use App\Http\Controllers\Api\ComplaintCategoryController;
use App\Http\Controllers\Api\ComplaintController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\FaqController;
use App\Http\Controllers\Api\FeaturedProgramController;
use App\Http\Controllers\Api\InnovationProgramController;
use App\Http\Controllers\Api\GalleryController;
use App\Http\Controllers\Api\NewsCategoryController;
use App\Http\Controllers\Api\NewsController;
use App\Http\Controllers\Api\PopupBannerController;
use App\Http\Controllers\Api\ProfilePageController;
use App\Http\Controllers\Api\PublicComplaintController;
use App\Http\Controllers\Api\PublicController;
use App\Http\Controllers\Api\ServiceController;
use App\Http\Controllers\Api\SettingController;
use App\Http\Controllers\Api\TestimonialController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('public')->group(function () {
    Route::get('/home', [PublicController::class, 'home']);
    Route::get('/galleries', [PublicController::class, 'galleries']);
    Route::get('/news', [PublicController::class, 'news']);
    Route::get('/news/{slug}', [PublicController::class, 'newsShow']);
    Route::get('/featured-programs', [PublicController::class, 'featuredPrograms']);
    Route::get('/innovation-programs', [PublicController::class, 'innovationPrograms']);
    Route::get('/profile-pages/{slug}', [PublicController::class, 'profilePage'])
        ->whereIn('slug', ['sejarah', 'visi-misi']);
    Route::get('/featured-programs/{slug}', [PublicController::class, 'featuredProgramShow']);
    Route::get('/innovation-programs/{slug}', [PublicController::class, 'innovationProgramShow']);

    Route::get('/complaint-categories', [PublicComplaintController::class, 'categories']);
    Route::post('/complaints', [PublicComplaintController::class, 'store'])
        ->middleware('throttle:complaint-submit');
    Route::post('/complaints/track', [PublicComplaintController::class, 'track'])
        ->middleware('throttle:complaint-track');
    Route::get('/complaints/{ticket}/status', [PublicComplaintController::class, 'status'])
        ->middleware('throttle:complaint-track');
    Route::post('/complaints/{ticket}/messages', [PublicComplaintController::class, 'message'])
        ->middleware('throttle:complaint-message');
    Route::get('/complaints/{ticket}/attachments/{attachment}', [PublicComplaintController::class, 'attachment'])
        ->middleware('throttle:complaint-track');
});
Route::get('/popup-banners/active', [PopupBannerController::class, 'active'])
    ->middleware('throttle:60,1');
Route::get('/banners/active', [BannerController::class, 'active'])
    ->middleware('throttle:60,1');

Route::prefix('auth')->group(function () {
    Route::get('/captcha', [AuthController::class, 'captcha']);
    Route::post('/login', [AuthController::class, 'login']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
    });

    Route::get('/dashboard', [DashboardController::class, 'index']);

    Route::get('/news/categories', [NewsCategoryController::class, 'index']);
    Route::post('/news/categories', [NewsCategoryController::class, 'store']);
    Route::put('/news/categories/{id}', [NewsCategoryController::class, 'update']);
    Route::delete('/news/categories/{id}', [NewsCategoryController::class, 'destroy']);

    Route::apiResource('news', NewsController::class);

    Route::delete('/gallery/{gallery}/images/{image}', [GalleryController::class, 'destroyImage']);
    Route::apiResource('gallery', GalleryController::class);

    Route::apiResource('services', ServiceController::class);
    Route::apiResource('achievements', AchievementController::class);
    Route::apiResource('faqs', FaqController::class);
    Route::apiResource('featured-programs', FeaturedProgramController::class);
    Route::apiResource('innovation-programs', InnovationProgramController::class);
    Route::get('/profile-pages/{slug}', [ProfilePageController::class, 'show'])
        ->whereIn('slug', ['sejarah', 'visi-misi']);
    Route::put('/profile-pages/{slug}', [ProfilePageController::class, 'update'])
        ->whereIn('slug', ['sejarah', 'visi-misi']);
    Route::apiResource('testimonials', TestimonialController::class);
    Route::patch('/popup-banners/{id}/status', [PopupBannerController::class, 'updateStatus']);
    Route::apiResource('popup-banners', PopupBannerController::class);

    Route::patch('/banners/reorder', [BannerController::class, 'reorder']);
    Route::put('/banners/reorder', [BannerController::class, 'reorder']);
    Route::patch('/banners/{id}/status', [BannerController::class, 'updateStatus']);
    Route::put('/banners/{id}/status', [BannerController::class, 'updateStatus']);
    Route::apiResource('banners', BannerController::class)->only(['index', 'store', 'destroy']);


    Route::get('/settings', [SettingController::class, 'index']);
    Route::put('/settings', [SettingController::class, 'update']);
    Route::post('/settings/logo', [SettingController::class, 'uploadLogo']);
    Route::delete('/settings/logo', [SettingController::class, 'deleteLogo']);
    Route::post('/settings/hero-image', [SettingController::class, 'uploadHeroImage']);
    Route::delete('/settings/hero-image', [SettingController::class, 'deleteHeroImage']);
    Route::post('/settings/principal-photo', [SettingController::class, 'uploadPrincipalPhoto']);
    Route::delete('/settings/principal-photo', [SettingController::class, 'deletePrincipalPhoto']);
    Route::post('/settings/integrity-image', [SettingController::class, 'uploadIntegrityImage']);
    Route::delete('/settings/integrity-image', [SettingController::class, 'deleteIntegrityImage']);
    Route::post('/settings/org-chart-image', [SettingController::class, 'uploadOrgChartImage']);
    Route::delete('/settings/org-chart-image', [SettingController::class, 'deleteOrgChartImage']);

    Route::get('/users/roles', [UserController::class, 'roles']);
    Route::apiResource('users', UserController::class);

    Route::get('/complaints/dashboard', [ComplaintController::class, 'dashboard']);
    Route::get('/complaints/export', [ComplaintController::class, 'export']);
    Route::get('/complaints', [ComplaintController::class, 'index']);
    Route::get('/complaints/{id}', [ComplaintController::class, 'show']);
    Route::patch('/complaints/{id}/status', [ComplaintController::class, 'updateStatus']);
    Route::post('/complaints/{id}/assign', [ComplaintController::class, 'assign']);
    Route::post('/complaints/{id}/messages', [ComplaintController::class, 'message']);
    Route::get('/complaints/{id}/attachments/{attachmentId}', [ComplaintController::class, 'attachment']);

    Route::get('/complaint-categories', [ComplaintCategoryController::class, 'index']);
    Route::post('/complaint-categories', [ComplaintCategoryController::class, 'store']);
    Route::put('/complaint-categories/{id}', [ComplaintCategoryController::class, 'update']);
    Route::delete('/complaint-categories/{id}', [ComplaintCategoryController::class, 'destroy']);
});
