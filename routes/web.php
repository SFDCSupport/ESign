<?php

use Illuminate\Support\Facades\Route;
use NIIT\ESign\Http\Controllers\Controller;
use NIIT\ESign\Http\Controllers\DocumentController;
use NIIT\ESign\Http\Controllers\SignerController;
use NIIT\ESign\Http\Controllers\SigningController;

Route::prefix('attachment')
    ->name('attachment.')
    ->group(function () {
        Route::any('upload/{type}', [Controller::class, 'upload'])->name('upload');
        Route::any('remove/{attachment}', [Controller::class, 'remove'])
            ->name('remove');
    });

// ADMIN ROUTES
Route::middleware([
    'auth',
    \NIIT\ESign\Http\Middleware\Heartbeat::class,
])->group(function () {
    Route::post('heartbeat', \NIIT\ESign\Http\Controllers\HeartbeatController::class)->name('heartbeat');
    Route::resource('templates', \NIIT\ESign\Http\Controllers\TemplateController::class)
        ->except(['edit', 'create']);

    Route::get('documents/{document}/signers/{signer}/send-mail', [SignerController::class, 'sendMail'])
        ->name('documents.signers.sendMail');
    Route::resource('documents.signers', SignerController::class)
        ->except(['create', 'edit', 'show']);

    Route::get('documents/{document}/submissions', [\NIIT\ESign\Http\Controllers\SubmissionController::class, 'index'])
        ->name('documents.submissions.index');
    Route::get('documents/{document}/submissions/{signer}', [\NIIT\ESign\Http\Controllers\SubmissionController::class, 'show'])
        ->name('documents.submissions.show');

    Route::get('documents/{document}/copy', [DocumentController::class, 'copy'])
        ->name('documents.copy');
    Route::post('documents/{document}/sendMail/{signer?}', [DocumentController::class, 'sendMail'])
        ->name('documents.sendMail');
    Route::resource('documents', DocumentController::class)
        ->except(['create', 'edit']);

    Route::permanentRedirect('/', '/esign/documents');
    Route::permanentRedirect('document', '/esign/documents');
});

Route::post('audit/{document}', \NIIT\ESign\Http\Controllers\AuditController::class)->name('audit-log');

// SIGNING ROUTES
Route::get('signers/{signer}/mail', [SigningController::class, 'mailTrackingPixel'])
    ->name('signing.mail-pixel');

Route::name('signing.')
    ->middleware([
        \NIIT\ESign\Http\Middleware\SigningMiddleware::class,
    ])->group(function () {
        Route::get('{signing_url}/show', [SigningController::class, 'show'])->name('show');
        Route::post('{signing_url}/send-copy', [SigningController::class, 'sendCopy'])->name('send-copy');
        Route::get('{signing_url}', [SigningController::class, 'index'])->name('index');
        Route::post('{signing_url}', [SigningController::class, 'store']);
    });
