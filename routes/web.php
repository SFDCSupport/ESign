<?php

use Illuminate\Support\Facades\Route;
use NIIT\ESign\Http\Controllers\Controller;
use NIIT\ESign\Http\Controllers\DocumentController;
use NIIT\ESign\Http\Controllers\SignerController;
use NIIT\ESign\Http\Controllers\SigningController;
use NIIT\ESign\Http\Controllers\SubmissionController;
use NIIT\ESign\Http\Controllers\TemplateController;
use NIIT\ESign\Http\Middleware\SigningMiddleware;

Route::any('/upload/{type}', [Controller::class, 'upload']);

// ADMIN ROUTES
Route::middleware(['auth'])
    ->group(function () {
        Route::delete('templates/destroy', [DocumentController::class, 'bulkDestroy'])
            ->name('templates.bulk-destroy');
        Route::resource('templates', TemplateController::class);

        Route::delete('documents/{document}/signers/destroy', [SignerController::class, 'bulkDestroy'])
            ->name('documents.signers.bulk-destroy');
        Route::resource('documents.signers', SignerController::class)
            ->except(['create', 'edit', 'show']);

        Route::resource('documents.submissions', SubmissionController::class)
            ->only(['index', 'show', 'destroy']);

        Route::delete('documents/destroy', [DocumentController::class, 'bulkDestroy'])
            ->name('documents.bulk-destroy');
        Route::get('documents/{document}/copy', [DocumentController::class, 'copy'])
            ->name('documents.copy');
        Route::get('documents/{document}/send', [DocumentController::class, 'send'])
            ->name('documents.send');
        Route::resource('documents', DocumentController::class);

        Route::permanentRedirect('/', '/esign/documents');
    });

// SIGNING ROUTES
Route::get('signers/{signer}/mail', [SigningController::class, 'mailTrackingPixel'])
    ->name('signing.mail-pixel');

Route::name('signing.')
    ->middleware([
        SigningMiddleware::class,
    ])->group(function () {
        Route::get('/{signer}', [SigningController::class, 'index']);
        Route::post('/{signer}', [SigningController::class, 'store']);
    });
