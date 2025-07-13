<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\DocumentController;
use App\Http\Controllers\API\BankAccountController;
use App\Http\Controllers\API\ActivationRequestController;
use App\Http\Controllers\API\AdminController;
use App\Http\Controllers\API\AppointmentController;
use App\Http\Controllers\API\MedicalHistoryController;
use App\Http\Controllers\API\DoctorController;
use App\Http\Controllers\API\QuestionController;
use App\Http\Controllers\API\ConsultationController;

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

Route::post('/register-ductor', [UserController::class, 'registerDuctor']);

// Doctor Routes
Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('doctor')->group(function () {
        Route::get('/profile', [DoctorController::class, 'getProfile']);
        Route::post('/profile', [DoctorController::class, 'updateProfile']);
        Route::post('/license', [DoctorController::class, 'uploadLicense']);
        Route::delete('/license/{fileId}', [DoctorController::class, 'deleteLicense']);
    });
});

// Doctor Specialties Routes
Route::middleware(['auth:sanctum'])->prefix('doctor/specialties')->group(function () {
    Route::get('/', [DoctorController::class, 'getSpecialties']);
    Route::post('/', [DoctorController::class, 'addSpecialty']);
    Route::put('/{specialtyId}', [DoctorController::class, 'updateSpecialty']);
    Route::delete('/{specialtyId}', [DoctorController::class, 'deleteSpecialty']);
});
Route::get('doctor/{doctor_id}/specialties', [DoctorController::class, 'getDoctorSpecialties']);

// Medical Tags Routes
Route::middleware(['auth:sanctum'])->prefix('admin/medical-tags')->group(function () {
    Route::get('/', [AdminController::class, 'getMedicalTags']);
    Route::post('/', [AdminController::class, 'addMedicalTag']);
    Route::put('/{id}', [AdminController::class, 'updateMedicalTag']);
    Route::delete('/{id}', [AdminController::class, 'deleteMedicalTag']);
});

// Question Routes
Route::middleware(['auth:sanctum'])->prefix('questions')->group(function () {
    Route::get('/', [QuestionController::class, 'index']);
    Route::post('/', [QuestionController::class, 'store']);
    Route::get('/{id}', [QuestionController::class, 'show']);
    Route::put('/{id}', [QuestionController::class, 'update']);
    Route::delete('/{id}', [QuestionController::class, 'destroy']);
    Route::get('/medical-tag/{medicalTagId}', [QuestionController::class, 'getByMedicalTag']);
    Route::post('/{id}/attach-tags', [QuestionController::class, 'attachMedicalTags']);
    Route::post('/{id}/detach-tags', [QuestionController::class, 'detachMedicalTags']);
    Route::post('/{id}/sync-tags', [QuestionController::class, 'syncMedicalTags']);
});


//admin api
Route::post('/register-admin', [UserController::class, 'registerAdmin']);
Route::middleware(['auth:sanctum'])->prefix('admin')->group(function () {

    Route::get('/users/count-by-role', [AdminController::class, 'countUsersByRole']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::post('payments/create-intent', [\App\Http\Controllers\Api\PaymentController::class, 'createIntent']);
    Route::post('payments/{payment}/refund', [\App\Http\Controllers\Api\PaymentController::class, 'refund']);
    Route::get('payments/status/{payment}', [\App\Http\Controllers\Api\PaymentController::class, 'getPaymentStatus']);
    Route::post('wallet/topup', [\App\Http\Controllers\Api\WalletTopupController::class, 'topup']);
    Route::post('/wallet/confirm', [\App\Http\Controllers\Api\WalletTopupController::class, 'confirmTopup']);
});

Route::post('stripe/webhook', [\App\Http\Controllers\Api\StripeWebhookController::class, 'handle']);

Route::get('/stripe/onboard/{doctor_id}', [\App\Http\Controllers\Api\StripeConnectController::class, 'createOnboardingLink']);
Route::get('/stripe/onboard/refresh', fn () => response()->json(['message' => 'Please try again.']));
Route::get('/stripe/onboard/return', fn () => response()->json(['message' => 'Onboarding completed successfully.']));


// Route::middleware(['auth:sanctum', 'verified'])->group(function () {
//     Route::prefix('medicalHistory/')->group(function () {
//         Route::post('store', [MedicalHistoryController::class, 'store']);
//         Route::put('update', [MedicalHistoryController::class, 'update']);
//     });
// });


Route::group(["middleware" => ["auth:sanctum"]], function () {

    Route::prefix('admin/')->group(function () {
        Route::patch("verification_account/{id}", [AdminController::class, "verficat"]);
    });
});

// User Consultation Routes
Route::middleware('auth:sanctum')->group(function () {
    // User routes
    Route::post('/consultations', [ConsultationController::class, 'store']);
    Route::get('/consultations', [UserController::class, 'getUserConsultations']);
    Route::get('/consultations/filter', [UserController::class, 'getUserConsultationsByStatus']);

    // Doctor routes
    Route::get('/doctor/consultations/pending', [DoctorController::class, 'getPendingConsultations']);
    Route::get('/doctor/consultations/filter', [DoctorController::class, 'getConsultationsByStatus']);
    Route::patch('/doctor/consultations/{consultationId}/status', [DoctorController::class, 'updateConsultationStatus']);
    Route::post('/doctor/consultations/{consultationId}/schedule', [DoctorController::class, 'scheduleConsultation']);
});


//Booking Appointment
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/doctors/{doctor}/available-days', [AppointmentController::class, 'availableDays']);
    Route::get('/doctors/{doctor}/available-slots', [AppointmentController::class, 'availableSlots']);
    Route::post('/doctors/{doctor}/book', [AppointmentController::class, 'book']);
});
