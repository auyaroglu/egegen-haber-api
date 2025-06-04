<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\LogRequestMiddleware;
use App\Http\Middleware\BearerTokenMiddleware;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// News API Routes - Bearer token korunmalı ve loglanmalı
// YENİ MANTIK: BearerTokenMiddleware içinde hem token hem blacklist kontrolü
Route::middleware([
    LogRequestMiddleware::class,   // 1. Request'i logla
    BearerTokenMiddleware::class   // 2. Token kontrol et + blacklist kontrolü
])->group(function () {
    // Arama endpoint'i - GET params ile (apiResource'dan ÖNCE tanımlanmalı)
    Route::get('news/search', [\App\Http\Controllers\Api\NewsController::class, 'search'])->name('news.search');

    // News CRUD operations - RESTful API
    Route::apiResource('news', \App\Http\Controllers\Api\NewsController::class);
});
