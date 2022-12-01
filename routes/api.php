<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::prefix('v1')->group(function () {

    //Route::middleware('recaptcha')->group(function () {
    Route::post('/token', 'App\Http\Controllers\UserController@token');
    Route::post('/login', 'App\Http\Controllers\UserController@login');
    //});

    Route::middleware('auth:sanctum')->group(function () {

        // CRUD for Roles
        Route::get('/roles', 'App\Http\Controllers\RoleController@index');
        Route::post('/role', 'App\Http\Controllers\RoleController@create');
        Route::get('/role/{id}', 'App\Http\Controllers\RoleController@show');
        Route::put('/role/{id}', 'App\Http\Controllers\RoleController@update');
        Route::delete('/role/{id}', 'App\Http\Controllers\RoleController@delete');

        // R for Modules
        Route::get('/modules', 'App\Http\Controllers\ModuleController@index');

        // CRUD for Users
        Route::get('/users', 'App\Http\Controllers\UserController@index');
        Route::post('/user', 'App\Http\Controllers\UserController@create');
        Route::get('/user', 'App\Http\Controllers\UserController@show');
        Route::get('/user/{id}', 'App\Http\Controllers\UserController@show');
        Route::put('/user/{id}', 'App\Http\Controllers\UserController@update');
        Route::delete('/user/{id}', 'App\Http\Controllers\UserController@delete');

        // CRUD for Sectors
        Route::get('/sectors', 'App\Http\Controllers\SectorController@index');
        Route::post('/sector', 'App\Http\Controllers\SectorController@create');
        Route::get('/sector/{id}', 'App\Http\Controllers\SectorController@show');
        Route::put('/sector/{id}', 'App\Http\Controllers\SectorController@update');
        Route::delete('/sector/{id}', 'App\Http\Controllers\SectorController@delete');

        // CRUD for Categories
        Route::get('/categories', 'App\Http\Controllers\CategoryController@index');
        Route::post('/category', 'App\Http\Controllers\CategoryController@create');
        Route::get('/category/{id}', 'App\Http\Controllers\CategoryController@show');
        Route::put('/category/{id}', 'App\Http\Controllers\CategoryController@update');
        Route::delete('/category/{id}', 'App\Http\Controllers\CategoryController@delete');

        // CRUD for Institutions
        Route::get('/institutions', 'App\Http\Controllers\InstitutionController@index');
        Route::post('/institution', 'App\Http\Controllers\InstitutionController@create');
        Route::get('/institution/{id}', 'App\Http\Controllers\InstitutionController@show');
        Route::put('/institution/{id}', 'App\Http\Controllers\InstitutionController@update');
        Route::delete('/institution/{id}', 'App\Http\Controllers\InstitutionController@delete');

        // CRUD for Services
        Route::get('/services', 'App\Http\Controllers\ServiceController@index');
        Route::post('/service', 'App\Http\Controllers\ServiceController@create');
        Route::get('/service/{id}', 'App\Http\Controllers\ServiceController@show');
        Route::put('/service/{id}', 'App\Http\Controllers\ServiceController@update');
        Route::delete('/service/{id}', 'App\Http\Controllers\ServiceController@delete');

        // CRUD for News
        Route::get('/news', 'App\Http\Controllers\NewsController@index');
        Route::post('/news', 'App\Http\Controllers\NewsController@create');
        Route::get('/news/{id}', 'App\Http\Controllers\NewsController@show');
        Route::put('/news/{id}', 'App\Http\Controllers\NewsController@update');
        Route::delete('/news/{id}', 'App\Http\Controllers\NewsController@delete');

        // CRUD for Pages
        Route::get('/pages', 'App\Http\Controllers\PageController@index');
        Route::post('/page', 'App\Http\Controllers\PageController@create');
        Route::get('/page/{id}', 'App\Http\Controllers\PageController@show');
        Route::put('/page/{id}', 'App\Http\Controllers\PageController@update');
        Route::delete('/page/{id}', 'App\Http\Controllers\PageController@delete');

        // CRUD for PageCategory
        Route::get('/page_categories', 'App\Http\Controllers\PageCategoryController@index');
        Route::post('/page_category', 'App\Http\Controllers\PageCategoryController@create');
        Route::get('/page_category/{id}', 'App\Http\Controllers\PageCategoryController@show');
        Route::put('/page_category/{id}', 'App\Http\Controllers\PageCategoryController@update');
        Route::delete('/page_category/{id}', 'App\Http\Controllers\PageCategoryController@delete');

        // CRUD for Tags
        Route::get('/tags', 'App\Http\Controllers\TagController@index');
        Route::post('/tag', 'App\Http\Controllers\TagController@create');
        Route::get('/tag/{id}', 'App\Http\Controllers\TagController@show');
        Route::put('/tag/{id}', 'App\Http\Controllers\TagController@update');
        Route::delete('/tag/{id}', 'App\Http\Controllers\TagController@delete');

        //CRUD for Entry
        Route::get('/entries', 'App\Http\Controllers\EntryController@index');
        Route::post('/entry', 'App\Http\Controllers\EntryController@create');
        Route::get('/entry/{id}', 'App\Http\Controllers\EntryController@show');
        Route::put('/entry/{id}', 'App\Http\Controllers\EntryController@update');

        //CRUD for EntryReview
        Route::get('/entry_reviews', 'App\Http\Controllers\EntryReviewController@index');
        Route::post('/entry_review', 'App\Http\Controllers\EntryReviewController@create');
        Route::get('/entry_review/{id}', 'App\Http\Controllers\EntryReviewController@show');
        Route::put('/entry_review/{id}', 'App\Http\Controllers\EntryReviewController@update');
        Route::delete('/entry_review/{id}', 'App\Http\Controllers\EntryReviewController@delete');

        // CD for EntryFile
        Route::post('/file', 'App\Http\Controllers\EntryFileController@create');
        Route::delete('/file/{id}', 'App\Http\Controllers\EntryFileController@delete');

        // CRUD for Report
        Route::get('/reports', 'App\Http\Controllers\ReportController@index');
        Route::post('/report', 'App\Http\Controllers\ReportController@create');
        Route::get('/report/{id}', 'App\Http\Controllers\ReportController@show');
        Route::put('/report/{id}', 'App\Http\Controllers\ReportController@update');
        Route::delete('/report/{id}', 'App\Http\Controllers\ReportController@delete');

        // CRUD for LinkCategory
        Route::get('/link_categories', 'App\Http\Controllers\LinkCategoryController@index');
        Route::post('/link_category', 'App\Http\Controllers\LinkCategoryController@create');
        Route::get('/link_category/{id}', 'App\Http\Controllers\LinkCategoryController@show');
        Route::put('/link_category/{id}', 'App\Http\Controllers\LinkCategoryController@update');
        Route::delete('/link_category/{id}', 'App\Http\Controllers\LinkCategoryController@delete');

        // CRUD for Link
        Route::get('/links', 'App\Http\Controllers\LinkController@index');
        Route::post('/link', 'App\Http\Controllers\LinkController@create');
        Route::get('/link/{id}', 'App\Http\Controllers\LinkController@show');
        Route::put('/link/{id}', 'App\Http\Controllers\LinkController@update');
        Route::delete('/link/{id}', 'App\Http\Controllers\LinkController@delete');

        // CRUD for News Categories
        Route::get('/news_categories', 'App\Http\Controllers\NewsCategoryController@index');
        Route::post('/news_category', 'App\Http\Controllers\NewsCategoryController@create');
        Route::get('/news_category/{id}', 'App\Http\Controllers\NewsCategoryController@show');
        Route::put('/news_category/{id}', 'App\Http\Controllers\NewsCategoryController@update');
        Route::delete('/news_category/{id}', 'App\Http\Controllers\NewsCategoryController@delete');

        // CRUD for LawCategory
        Route::get('/law_categories', 'App\Http\Controllers\LawCategoryController@index');
        Route::post('/law_category', 'App\Http\Controllers\LawCategoryController@create');
        Route::get('/law_category/{id}', 'App\Http\Controllers\LawCategoryController@show');
        Route::put('/law_category/{id}', 'App\Http\Controllers\LawCategoryController@update');
        Route::delete('/law_category/{id}', 'App\Http\Controllers\LawCategoryController@delete');

        // CRUD for Law
        Route::get('/laws', 'App\Http\Controllers\LawController@index');
        Route::post('/law', 'App\Http\Controllers\LawController@create');
        Route::get('/law/{id}', 'App\Http\Controllers\LawController@show');
        Route::put('/law/{id}', 'App\Http\Controllers\LawController@update');
        Route::delete('/law/{id}', 'App\Http\Controllers\LawController@delete');

        // CRUD for Feedbacks
        Route::get('/feedbacks', 'App\Http\Controllers\FeedBackController@index');
        Route::get('/feedback/{id}', 'App\Http\Controllers\FeedBackController@show');
        Route::put('/feedback/{id}', 'App\Http\Controllers\FeedBackController@update');

        // CRUD for Queues
        Route::get('/queues', 'App\Http\Controllers\QueueController@index');
        Route::post('/queue', 'App\Http\Controllers\QueueController@create');
        Route::get('/queue/{id}', 'App\Http\Controllers\QueueController@show');
        Route::put('/queue/{id}', 'App\Http\Controllers\QueueController@update');
        Route::delete('/queue/{id}', 'App\Http\Controllers\QueueController@delete');

        // CRUD for Queue Slots
        /*        Route::get('/queue/{id}/slots', 'App\Http\Controllers\QueueSlotController@index');
        Route::post('/queue/{id}/slot', 'App\Http\Controllers\QueueSlotController@create');
        Route::get('/queue/slot/{id}', 'App\Http\Controllers\QueueSlotController@show');
        Route::post('/queue/slot/{id}', 'App\Http\Controllers\QueueSlotController@update');
        Route::delete('/queue/slot/{id}', 'App\Http\Controllers\QueueSlotController@delete');*/
    });

    Route::prefix('/web')->group(function () {
        // R for Sectors
        Route::get('/sectors', 'App\Http\Controllers\SectorController@index');
        Route::get('/sector/{uri}', 'App\Http\Controllers\Web\SectorController@show');

        // R for Categories
        Route::get('/categories', 'App\Http\Controllers\CategoryController@index');
        Route::get('/category/{id}', 'App\Http\Controllers\CategoryController@show');

        // R for Institutions
        Route::get('/institutions', 'App\Http\Controllers\InstitutionController@index');
        Route::get('/institution/{uri}', 'App\Http\Controllers\Web\InstitutionController@show');

        // R for Services
        Route::get('/services', 'App\Http\Controllers\ServiceController@index');
        Route::get('/service/{uri}', 'App\Http\Controllers\Web\ServiceController@show');

        // R for News
        Route::get('/news', 'App\Http\Controllers\NewsController@index');
        Route::get('/news/{uri}', 'App\Http\Controllers\Web\NewsController@show');

        // R for Pages
        Route::get('/pages', 'App\Http\Controllers\PageController@index');
        Route::get('/page/{uri}', 'App\Http\Controllers\Web\PageController@show');

        // R for PageCategory
        Route::get('/page_categories', 'App\Http\Controllers\PageCategoryController@index');
        Route::get('/page_category/{id}', 'App\Http\Controllers\PageCategoryController@show');

        // R for Tags
        Route::get('/tags', 'App\Http\Controllers\TagController@index');
        Route::get('/tag/{id}', 'App\Http\Controllers\TagController@show');

        // R for Report
        Route::get('/reports', 'App\Http\Controllers\ReportController@index');
        Route::get('/report/{id}', 'App\Http\Controllers\ReportController@show');

        // R for LinkCategory
        Route::get('/link_categories', 'App\Http\Controllers\LinkCategoryController@index');
        Route::get('/link_category/{id}', 'App\Http\Controllers\LinkCategoryController@show');

        // R for Link
        Route::get('/links', 'App\Http\Controllers\LinkController@index');
        Route::get('/link/{id}', 'App\Http\Controllers\LinkController@show');

        // R for News Categories
        Route::get('/news_categories', 'App\Http\Controllers\NewsCategoryController@index');
        Route::get('/news_category/{id}', 'App\Http\Controllers\NewsCategoryController@show');

        // R for LawCategory
        Route::get('/law_categories', 'App\Http\Controllers\LawCategoryController@index');
        Route::get('/law_category/{id}', 'App\Http\Controllers\LawCategoryController@show');

        // R for Law
        Route::get('/laws', 'App\Http\Controllers\LawController@index');
        Route::get('/law/{id}', 'App\Http\Controllers\LawController@show');

        // R for Feedbacks
        Route::get('/feedbacks', 'App\Http\Controllers\FeedBackController@index');
        Route::post('/feedback', 'App\Http\Controllers\FeedBackController@create');
        Route::get('/feedback/{id}', 'App\Http\Controllers\FeedBackController@show');

        // global search
        Route::get('/search', 'App\Http\Controllers\SearchController@search');

        // R for preview
        Route::get('/service/preview/{id}', 'App\Http\Controllers\ServiceController@show');
        Route::get('/news/preview/{id}', 'App\Http\Controllers\NewsController@show');
        Route::get('/page/preview/{id}', 'App\Http\Controllers\PageController@show');

        // R for queues
        Route::get('/queues', 'App\Http\Controllers\QueueController@index');
        Route::get('/queue/{id}', 'App\Http\Controllers\QueueController@show');

        // CRUD for Users
        Route::get('/user', 'App\Http\Controllers\Web\UserController@show');
        Route::put('/user', 'App\Http\Controllers\Web\UserController@update');
    });

    Route::prefix('/cabinet')->group(function () {
        Route::middleware('auth:sanctum')->group(function () {
            // CRU for Entry
            Route::get('/entries', 'App\Http\Controllers\EntryController@index');
            Route::post('/entry', 'App\Http\Controllers\EntryController@create');
            Route::get('/entry/{id}', 'App\Http\Controllers\EntryController@show');
            Route::put('/entry/{id}', 'App\Http\Controllers\EntryController@update');

            //CRUD for EntryReview
            Route::get('/entry_reviews', 'App\Http\Controllers\EntryReviewController@index');
            Route::post('/entry_review', 'App\Http\Controllers\EntryReviewController@create');
            Route::get('/entry_review/{id}', 'App\Http\Controllers\EntryReviewController@show');

            // C for EntryFile
            Route::post('/file', 'App\Http\Controllers\EntryFileController@create');

            Route::get('/queues', 'App\Http\Controllers\QueueController@index');
            Route::get('/queue/{id}', 'App\Http\Controllers\QueueController@show');
        });
    });
});
