<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\DocumentController;
use App\Http\Controllers\API\BankAccountController;
use App\Http\Controllers\API\ActivationRequestController;
use App\Http\Controllers\API\AdminController;
use App\Http\Controllers\API\MedicalHistoryController;


Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


Route::post('/register', [UserController::class, 'register']);
Route::post('/login', [UserController::class, 'login']);
Route::get('/user/{id}', [UserController::class, 'getUserById']);

Route::group(["middleware" => ["auth:sanctum"]], function () {

    Route::prefix('users/')->group(function () {

        Route::get("profile", [UserController::class, "profile"]);
        Route::get("logout", [UserController::class, "logout"]);
        Route::post('complete-registration', [MedicalHistoryController::class, 'store']);
    });
});

Route::middleware(['auth:sanctum', 'verified'])->group(function () {
    Route::prefix('medicalHistory/')->group(function () {
        Route::post('store', [MedicalHistoryController::class, 'store']);
        Route::put('update', [MedicalHistoryController::class, 'update']);
    });
});
Route::post('/users/{user}/documents', [DocumentController::class, 'upload']);
Route::post('/users/{user}/bank-account', [BankAccountController::class, 'link']);
Route::post('/users/{user}/activation-request', [ActivationRequestController::class, 'send']);
Route::post('/admin/activation-requests/{activationRequest}/approve', [ActivationRequestController::class, 'approve']);
Route::get('/users/{user}/activation-status', [ActivationRequestController::class, 'status']);




Route::post('/register-ductor', [UserController::class, 'registerDuctor']);


Route::post('/register-admin', [UserController::class, 'registerAdmin']);

Route::group(["middleware" => ["auth:sanctum"]], function () {

    Route::prefix('admin/')->group(function () {
        Route::patch("verification_account/{id}", [AdminController::class, "verficat"]);
    });
});
