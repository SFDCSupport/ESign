<?php

use Illuminate\Support\Facades\Route;
use NIIT\ESign\Http\Controllers\DocumentController;
use NIIT\ESign\Http\Controllers\DocumentSignerController;

Route::get('/image2svg', function () {
    $response = \Illuminate\Support\Facades\Process::run('potrace --help');
    dd($response->successful() ? $response->output() : $response->errorOutput());
});

Route::delete('document/destroy', [DocumentController::class, 'bulkDestroy'])
    ->name('document.bulk-destroy');
Route::resource('document', DocumentController::class);

Route::delete('document/{document}/signer/destroy', [DocumentSignerController::class, 'bulkDestroy'])
    ->name('document.signer.bulk-destroy');
Route::resource('document.signer', DocumentSignerController::class)
    ->only(['index', 'store', 'destroy']);

Route::get('/{hash?}', function ($hash = null) {
    return view('esign::index')
        ->with([
            'hash' => $hash,
            'maxSize' => min([convertPHPSizeToBytes(ini_get('post_max_size')), convertPHPSizeToBytes(ini_get('upload_max_filesize'))]),
            'maxPage' => ini_get('max_file_uploads') - 1,
        ]);
});
