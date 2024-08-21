<?php

use App\Http\Controllers\CreatedocController;
use App\Http\Controllers\DraftController;
use App\Http\Controllers\EofficeHistoryController;
use App\Http\Controllers\EofficeNumController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\GetMailNumController;
use App\Http\Controllers\UploadController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

Route::apiResource('files', FileController::class);
Route::apiResource('upload', UploadController::class);
Route::apiResource('create_doc', CreatedocController::class);
Route::apiResource('create_draft', DraftController::class);
Route::apiResource('get_mail_num', GetMailNumController::class);
Route::apiResource('eoffice_num', EofficeNumController::class);
Route::apiResource('eoffice_history', EofficeHistoryController::class);

Route::prefix('payments')->name('payments.')->group(function (){
    Route::controller(\App\Http\Controllers\PaymentGateway\BNIController::class)->prefix('bni')->name('bni.')->group(function () {
        Route::post('test', 'test')->name('test');
        Route::post('token2', 'token2')->name('token2');
        Route::post('wiwjqiekelajska', 'token')->name('token');
        Route::post('getbalance', 'getbalance')->name('getbalance');
        Route::post('getinhouseinquiry', 'getinhouseinquiry')->name('getinhouseinquiry');
        Route::post('dopayment', 'dopayment')->name('dopayment');
        Route::post('getpaymentstatus', 'getpaymentstatus')->name('getpaymentstatus');
        Route::post('getinterbankinquiry', 'getinterbankinquiry')->name('getinterbankinquiry');
        Route::post('getinterbankpayment', 'getinterbankpayment')->name('getinterbankpayment');
    });
});