<?php

use App\Http\Controllers\Api\RfidController;
use App\Http\Controllers\Api\ScannerController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/rfid/scan', [RfidController::class, 'scan']);
Route::post('/scanner/batch', [ScannerController::class, 'storeFromScanner']);
Route::get('/scanner/check/{barcode}', [ScannerController::class, 'checkBarcode']);
Route::get('/scanner/scan-only', [ScannerController::class, 'checkBarcode']);
