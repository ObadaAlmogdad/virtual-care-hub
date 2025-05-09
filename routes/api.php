<?php


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\DocumentController;
use App\Http\Controllers\API\BankAccountController;
use App\Http\Controllers\API\ActivationRequestController;



Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// User Routes
Route::apiResource('users', UserController::class);

Route::post('/register', [UserController::class, 'register']);
Route::post('/login', [UserController::class, 'login']);
Route::get('/user/{id}', [UserController::class, 'getUserById']);

Route::group(["middleware" => ["auth:sanctum"]], function () {

    Route::get("profile", [UserController::class, "profile"]);
    Route::get("logout", [UserController::class, "logout"]);
});

Route::post('/users/{user}/documents', [DocumentController::class, 'upload']);
Route::post('/users/{user}/bank-account', [BankAccountController::class, 'link']);
Route::post('/users/{user}/activation-request', [ActivationRequestController::class, 'send']);
Route::post('/admin/activation-requests/{activationRequest}/approve', [ActivationRequestController::class, 'approve']);
Route::get('/users/{user}/activation-status', [ActivationRequestController::class, 'status']);


/***********************/ 

Route::post('/register-ductor', [UserController::class, 'registerDuctor']);

/***********************/ 

Route::post('/register-admin', [UserController::class, 'registerAdmin']);