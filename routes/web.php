<?php

use Illuminate\Support\Facades\Route;
use NIIT\ESign\Http\Controllers\Controller;
use NIIT\ESign\Http\Controllers\DocumentController;
use NIIT\ESign\Http\Controllers\SignerController;
use NIIT\ESign\Http\Controllers\SigningController;
use NIIT\ESign\Http\Controllers\SubmissionController;
use NIIT\ESign\Http\Controllers\TemplateController;
use NIIT\ESign\Http\Middleware\SigningMiddleware;

Route::prefix('attachment')
    ->name('attachment.')
    ->group(function () {
        Route::any('upload/{type}', [Controller::class, 'upload'])->name('upload');
        Route::any('remove/{attachment}', [Controller::class, 'remove'])
            ->name('remove');
    });

// ADMIN ROUTES
Route::middleware('auth')
    ->group(function () {
        Route::resource('templates', TemplateController::class)
            ->except(['edit', 'create']);

        Route::resource('documents.signers', SignerController::class)
            ->except(['create', 'edit', 'show']);

        Route::resource('documents.submissions', SubmissionController::class)
            ->only(['index', 'show', 'destroy']);

        Route::get('documents/{document}/copy', [DocumentController::class, 'copy'])
            ->name('documents.copy');
        Route::post('documents/{document}/sendMail/{signer?}', [DocumentController::class, 'sendMail'])
            ->name('documents.sendMail');
        Route::resource('documents', DocumentController::class)
            ->except(['create', 'edit']);

        Route::permanentRedirect('/', '/esign/documents');
        Route::permanentRedirect('document', '/esign/documents');
    });

// SIGNING ROUTES
Route::get('signers/{signer}/mail', [SigningController::class, 'mailTrackingPixel'])
    ->name('signing.mail-pixel');

Route::name('signing.')
    ->middleware([
        SigningMiddleware::class,
    ])->group(function () {
        Route::get('/{signing_url}/show', [SigningController::class, 'show'])->name('show');
        Route::get('/{signing_url}', [SigningController::class, 'index'])->name('index');
        Route::post('/{signing_url}', [SigningController::class, 'store']);
    });
