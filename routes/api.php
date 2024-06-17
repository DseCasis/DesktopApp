<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use \App\Http\Controllers\RemoteDesktopController;
use \App\Http\Controllers\FileUploadController;

Route::post('/ssh/execute', [RemoteDesktopController::class, 'executeSSHCommand'])->name('ssh.execute');
Route::post('/receive-file', [FileUploadController::class, 'uploadFile']);


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
