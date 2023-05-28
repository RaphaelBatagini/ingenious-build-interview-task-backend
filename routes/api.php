<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Modules\Invoices\Api\InvoiceController;
use App\Modules\Invoices\Api\InvoiceApprovalController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/invoices/{id}', [InvoiceController::class, 'show']);
Route::put('/invoices/approve/{id}', [InvoiceApprovalController::class, 'approve']);
Route::put('/invoices/reject/{id}', [InvoiceApprovalController::class, 'reject']);
