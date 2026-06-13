<?php

use App\Http\Controllers\ActivationPageController;
use App\Http\Controllers\DownloadController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\LegalController;
use App\Http\Controllers\ResellerDirectoryController;
use App\Http\Controllers\StripeCheckoutController;
use App\Http\Controllers\UploadController;
use Illuminate\Support\Facades\Route;

Route::get('/', LandingController::class);

Route::get('/download/app', [DownloadController::class, 'app'])->name('download.app');

Route::get('/upload', [UploadController::class, 'show'])->name('upload');
Route::post('/upload', [UploadController::class, 'store'])->name('upload.store');

Route::get('/activation', [ActivationPageController::class, 'show'])->name('activation');
Route::post('/activation/lookup', [ActivationPageController::class, 'lookup'])->name('activation.lookup');
Route::post('/activation/checkout', [ActivationPageController::class, 'checkout'])->name('activation.checkout');
Route::get('/activation/success', [StripeCheckoutController::class, 'activationSuccess'])->name('activation.success');

Route::get('/resellers', [ResellerDirectoryController::class, 'index'])->name('resellers.index');
Route::get('/resellers/{slug}', [ResellerDirectoryController::class, 'show'])->name('resellers.show');

Route::get('/legal/terms', [LegalController::class, 'terms'])->name('legal.terms');
Route::get('/legal/privacy', [LegalController::class, 'privacy'])->name('legal.privacy');
Route::get('/legal/refund', [LegalController::class, 'refund'])->name('legal.refund');
Route::get('/legal/activation', [LegalController::class, 'activation'])->name('legal.activation');
Route::get('/legal/acceptable-use', [LegalController::class, 'acceptableUse'])->name('legal.acceptable-use');
Route::get('/legal/cookies', [LegalController::class, 'cookies'])->name('legal.cookies');

Route::get('/reseller/credits/success', [StripeCheckoutController::class, 'success'])
    ->middleware('auth:reseller')
    ->name('reseller.credits.success');
