<?php

use App\Http\Controllers\API\AdminController;
use App\Http\Controllers\API\AppointmentController;

use App\Http\Controllers\API\ChatController;
use App\Http\Controllers\API\ComplaintController;
use App\Http\Controllers\API\ConsultationController;
use App\Http\Controllers\API\ConsultationResultController;
use App\Http\Controllers\API\DoctorController;
use App\Http\Controllers\Api\DoctorRatingController;
use App\Http\Controllers\Api\MedicalBannerController;
use App\Http\Controllers\API\MedicalHistoryController;
use App\Http\Controllers\API\MedicalSpecialtyController;
use App\Http\Controllers\API\WalletTopupController;
use App\Http\Controllers\API\StripeWebhookController;
use App\Http\Controllers\API\StripeConnectController;
use App\Http\Controllers\API\PaymentController;
use App\Http\Controllers\API\MessageController;
use App\Http\Controllers\API\PublicDoctorController;
use App\Http\Controllers\API\MedicalArticleController;
use App\Http\Controllers\API\QuestionController;
use App\Http\Controllers\API\UserController;
use Illuminate\Broadcasting\Broadcast;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/register', [UserController::class, 'register']);
Route::post('/login', [UserController::class, 'login']);
Route::get('/user/{id}', [UserController::class, 'getUserById']);

Route::group(["middleware" => ["auth:sanctum"]], function () {

    Route::prefix('users/')->group(function () {

        Route::get("profile", [UserController::class, "profile"]);
        Route::post('update-profile', [UserController::class, 'updateProfile']);
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

    Route::post('/', [DoctorController::class, 'addSpecialty']);
    Route::put('/{specialtyId}', [DoctorController::class, 'updateSpecialty']);
    Route::delete('/{specialtyId}', [DoctorController::class, 'deleteSpecialty']);
});
Route::get('doctor/specialties', [DoctorController::class, 'getSpecialties']);
Route::get('doctor/{doctor_id}/specialties', [DoctorController::class, 'getDoctorSpecialties']);

// Public APIs without authentication
Route::prefix('public')->group(function () {
    // Doctor APIs
    Route::get('doctors', [PublicDoctorController::class, 'index']);
    Route::get('doctors/{id}', [PublicDoctorController::class, 'show']);
    Route::get('doctors/specialty/{specialtyId}', [PublicDoctorController::class, 'getBySpecialty']);
    Route::get('doctors/search', [PublicDoctorController::class, 'search']);

    // Medical Specialties APIs
    Route::get('medical-specialties', [MedicalSpecialtyController::class, 'index']);
    Route::get('medical-specialties/{id}', [MedicalSpecialtyController::class, 'show']);
    Route::get('medical-specialties/{id}/doctors', [MedicalSpecialtyController::class, 'getDoctorsBySpecialty']);

    // Consultation API
    Route::get('/patient/{patientId}/consultations/{consultationId}/reply', [ConsultationResultController::class, 'getMyDoctorReply']);
});

Route::prefix('all')->group(function () {
    Route::get('/consultations/general', [ConsultationController::class, 'getGeneralConsultations']);
    Route::get('/web/consultations/general', [ConsultationController::class, 'getwebGeneralConsultations']);
});

// Medical Tags Routes
Route::get('admin/medical-tags/', [AdminController::class, 'getMedicalTags']);
Route::middleware(['auth:sanctum'])->prefix('admin/medical-tags')->group(function () {

    Route::post('/', [AdminController::class, 'addMedicalTag']);
    Route::put('/{id}', [AdminController::class, 'updateMedicalTag']);
    Route::delete('/{id}', [AdminController::class, 'deleteMedicalTag']);
});
Route::get('admin/medical-tags', [AdminController::class, 'getMedicalTags']);

// Question Routes
Route::middleware(['auth:sanctum'])->prefix('questions')->group(function () {
    Route::get('/', [QuestionController::class, 'index']);
    Route::post('/', [QuestionController::class, 'store']);
    Route::get('/{id}', [QuestionController::class, 'show']);
    Route::put('/{id}', [QuestionController::class, 'update']);
    Route::delete('/{id}', [QuestionController::class, 'destroy']);
    Route::post('/{id}/attach-tags', [QuestionController::class, 'attachMedicalTags']);
    Route::post('/{id}/detach-tags', [QuestionController::class, 'detachMedicalTags']);
    Route::post('/{id}/sync-tags', [QuestionController::class, 'syncMedicalTags']);
});
Route::get('questions/medical-tag/{medicalTagId}', [QuestionController::class, 'getByMedicalTag']);

// Medical Tag Questions Routes
Route::middleware(['auth:sanctum'])->prefix('medical-tags')->group(function () {
    Route::post('/{medicalTagId}/attach-questions', [QuestionController::class, 'attachQuestionsToMedicalTag']);
});

//admin api
Route::post('/register-admin', [UserController::class, 'registerAdmin']);
Route::middleware(['auth:sanctum'])->prefix('admin')->group(function () {

    Route::get('/users/count-by-role', [AdminController::class, 'countUsersByRole']);
    Route::get('/doctors', [AdminController::class, 'getAllDoctors']);
    Route::get('/patients', [AdminController::class, 'getAllPatients']);
    Route::get('/consultations/general/count', [ConsultationController::class, 'GeneralConsultationsCount']);
    Route::get('/consultations/special/count', [ConsultationController::class, 'SpecialConsultationsCount']);
    Route::get('/complaintsByType', [ComplaintController::class, 'complaintsByType']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::post('payments/create-intent', [PaymentController::class, 'createIntent']);
    Route::post('payments/{payment}/refund', [PaymentController::class, 'refund']);
    Route::get('payments/status/{payment}', [PaymentController::class, 'getPaymentStatus']);
    Route::post('wallet/topup', [WalletTopupController::class, 'topup']);
    Route::post('/wallet/confirm', [WalletTopupController::class, 'confirmTopup']);
});

Route::post('stripe/webhook', [StripeWebhookController::class, 'handle']);

Route::get('/stripe/onboard/{doctor_id}', [StripeConnectController::class, 'createOnboardingLink']);
Route::get('/stripe/onboard/refresh', fn () => response()->json(['message' => 'Please try again.']));
Route::get('/stripe/onboard/return', fn () => response()->json(['message' => 'Onboarding completed successfully.']));

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
    Route::get('/doctors/by-specialty/{medical_tag_id}', [DoctorController::class, 'getBySpecialty']);

    // Doctor routes
    Route::get('/doctor/consultations/pending', [DoctorController::class, 'getPendingConsultations']);
    Route::get('/doctor/consultations/filter', [DoctorController::class, 'getConsultationsByStatus']);
    Route::patch('/doctor/consultations/{consultationId}/status', [DoctorController::class, 'updateConsultationStatus']);
    Route::post('/doctor/consultations/{consultationId}/schedule', [DoctorController::class, 'scheduleConsultation']);
    Route::post('/doctor/consultations/{consultationId}/reply', [DoctorController::class, 'replyToAnswer']);

    // Medical Articles (Doctor only for write operations)
    Route::middleware(['ensure.role:Doctor'])->group(function () {
        Route::post('/articles', [MedicalArticleController::class, 'store']);
        Route::put('/articles/{id}', [MedicalArticleController::class, 'update']);
        Route::delete('/articles/{id}', [MedicalArticleController::class, 'destroy']);
        Route::patch('/articles/{id}/toggle-publish', [MedicalArticleController::class, 'togglePublish']);
    });
});

//Booking Appointment
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/doctors/{doctor}/book', [AppointmentController::class, 'book']);
    Route::get('/doctors/{doctor}/available-days', [AppointmentController::class, 'availableDays']);
    Route::get('/doctors/{doctor}/available-slots', [AppointmentController::class, 'availableSlots']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user/appointments/getAll', [AppointmentController::class, 'getPatientAppointments']);
    Route::get('/user/appointments/filter', [AppointmentController::class, 'filterPatientAppointments']);
    Route::get('/doctor/appointments', [AppointmentController::class, 'getDoctorAppointments']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/complaints', [ComplaintController::class, 'index']);
    Route::post('/complaints', [ComplaintController::class, 'store']);
    Route::get('/complaints/count', [ComplaintController::class, 'count']);
    Route::get('/complaints/{complaint}', [ComplaintController::class, 'show']);
    Route::post('/complaints/{complaint}', [ComplaintController::class, 'update']);
    Route::delete('/complaints/{complaint}', [ComplaintController::class, 'destroy']);
});

// Broadcasting Auth Route
Route::post('/broadcasting/auth', function (Request $request) {
    return Broadcast::auth($request);
})->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->group(function () {
    // Chat Routes
    Route::post('/chats', [ChatController::class, 'createChat']);
    Route::get('/chats', [ChatController::class, 'myChats']);
    Route::get('/chats/{chat_id}', [ChatController::class, 'getChat']);
    Route::get('/my_chats', [ChatController::class, 'getmyChatId']);

    // Message Routes
    Route::get('/chats/{chat_id}/messages', [MessageController::class, 'getMessages']);
    Route::post('/chats/{chat_id}/messages', [MessageController::class, 'sendMessage']);
    Route::delete('/chats/{chat_id}/messages/{message_id}', [MessageController::class, 'deleteMessage']);
});

//banner
Route::apiResource('medical-banners', MedicalBannerController::class);
Route::patch('medical-banners/{id}/toggle-active', [MedicalBannerController::class, 'toggleActive']);
// end //banner

//rationg
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/doctor-ratings', [DoctorRatingController::class, 'store']);
});
Route::get('/doctors/{doctorId}/ratings', [DoctorRatingController::class, 'getDoctorRatings']);
Route::get('/doctors/top-rated', [DoctorRatingController::class, 'topRatedDoctors']);
//end rating

// Public Articles
Route::get('/articles', [MedicalArticleController::class, 'index']);
Route::get('/articles/{id}', [MedicalArticleController::class, 'show']);
