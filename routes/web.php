<?php

use Illuminate\Support\Facades\Route;
use NIIT\ESign\Http\Controllers\DocumentController;
use NIIT\ESign\Http\Controllers\SignerController;
use NIIT\ESign\Http\Controllers\SigningController;
use NIIT\ESign\Http\Controllers\TemplateController;
use NIIT\ESign\Http\Middleware\SigningMiddleware;

// ADMIN ROUTES
Route::middleware(['auth'])
    ->group(function () {
        Route::delete('template/destroy', [DocumentController::class, 'bulkDestroy'])
            ->name('templates.bulk-destroy');
        Route::resource('template', TemplateController::class);

        Route::delete('document/{document}/signer/destroy', [SignerController::class, 'bulkDestroy'])
            ->name('documents.signers.bulk-destroy');
        Route::resource('document.signer', SignerController::class)
            ->only(['index', 'store', 'destroy']);

        Route::delete('document/destroy', [DocumentController::class, 'bulkDestroy'])
            ->name('documents.bulk-destroy');
        Route::get('document/copy', [DocumentController::class, 'copy'])
            ->name('documents.copy');
        Route::get('document/send', [DocumentController::class, 'send'])
            ->name('documents.send');
        Route::resource('document', DocumentController::class);
    });

// SIGNING ROUTES
Route::prefix('signing')
    ->name('signing.')
    ->middleware([
        SigningMiddleware::class,
    ])->group(function () {
        Route::get('/signing/{document}', [SigningController::class, 'index']);
        Route::post('/signing/{document}', [SigningController::class, 'store']);
    });
