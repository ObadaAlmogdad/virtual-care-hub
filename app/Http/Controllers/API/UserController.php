<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;
use App\Services\ConsultationService;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    protected $userService;
    protected $consultationService;

    public function __construct(UserService $userService, ConsultationService $consultationService)
    {
        $this->userService = $userService;
        $this->consultationService = $consultationService;
    }

    public function index()
    {
        $users = $this->userService->getAllUsers();
        return response()->json(['users' => $users], 200);
    }

    public function register(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'fullName' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:8',
                'phoneNumber' => 'required|string',
                'photo' => 'required|image|mimes:jpeg,png,jpg|max:2048',
                'address' => 'required|string',
                'birthday' => 'required|date',
                'gender' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $user = $this->userService->register($request->all(), "Patient");
            return response()->json(['user' => $user], 201);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['message' => 'An error occurred while registering the user'], 500);
        }
    }

    public function registerDuctor(Request $request)
{
    try {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'certificate_images' => 'required|array',
            'certificate_images.*' => 'image|mimes:jpeg,png,jpg|max:2048',
            'medical_tag_id' => 'required|exists:medical_tags,id',
            'start_time' => 'required|date_format:Y-m-d H:i:s',
            'end_time' => 'required|date_format:Y-m-d H:i:s|after:start_time',
            'yearOfExper' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // $data = $request->all();
        $data = $request->except('certificate_images');
        $data['certificate_images'] = $request->file('certificate_images');
        $data['role'] = 'Ductor';

        $user = $this->userService->registerDuctorMinimal($data);


        return response()->json(['user' => $user], 201);
    } catch (ValidationException $e) {
        return response()->json(['errors' => $e->errors()], 422);
    } catch (\Exception $e) {
        return response()->json(['message' => 'An error occurred while registering the doctor'], 500);
    }
}


    // public function registerDuctor(Request $request)
    // {
    //     try {
    //         $validator = Validator::make($request->all(), [
    //             'fullName' => 'required|string|max:255',
    //             'email' => 'required|string|email|max:255|unique:users',
    //             'password' => 'required|string|min:8',
    //             'phoneNumber' => 'required|string',
    //             'photo' => 'required|image|mimes:jpeg,png,jpg|max:2048',
    //             'address' => 'required|string',
    //             'birthday' => 'required|date',
    //             'gender' => 'required|string',
    //             'bio' => 'required|string',
    //             'yearOfExper' => 'required|string',
    //         ]);

    //         if ($validator->fails()) {
    //             return response()->json(['errors' => $validator->errors()], 422);
    //         }

    //         $user = $this->userService->register($request->all(), "Ductor");
    //         return response()->json(['user' => $user], 201);
    //     } catch (ValidationException $e) {
    //         return response()->json(['errors' => $e->errors()], 422);
    //     } catch (\Exception $e) {
    //         return response()->json(['message' => 'An error occurred while registering the doctor'], 500);
    //     }
    // }

    public function registerAdmin(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'fullName' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:8',
                'phoneNumber' => 'required|string',
                'photo' => 'required|image|mimes:jpeg,png,jpg|max:2048',
                'address' => 'required|string',
                'birthday' => 'required|date',
                'gender' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $user = $this->userService->register($request->all(), "Admin");
            return response()->json(['user' => $user], 201);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['message' => 'An error occurred while registering the admin'], 500);
        }
    }

    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $token = $this->userService->login($validated);

        return response()->json([
            'token' => $token,
        ], 200);
    }

    public function profile()
    {
        return response()->json([
            "status" => 1,
            "message" => "User Profile information",
            "data" => auth()->user()
        ]);
    }

    public function logout()
    {
        //auth()->user()->tokens()->delete();

        return response()->json([
            "status" => 1,
            "message" => "you are logged out successfully"
        ]);
    }

    public function getUserById($id)
    {
        $user = $this->userService->getUserById($id);
        return response()->json(['user' => $user], 200);
    }

    public function update(Request $request, $id)
    {
        $result = $this->userService->updateUser($id, $request->all());

        if (!$result['success']) {
            return response()->json(['errors' => $result['errors']], 422);
        }

        return response()->json(['user' => $result['user']], 200);
    }

    public function destroy($id)
    {
        $this->userService->deleteUser($id);
        return response()->json(null, 204);
    }

    public function createConsultation(Request $request)
    {
        try {
            $data = $request->all();
            $data['user_id'] = auth()->id();

            // Handle media files
            if ($request->hasFile('media')) {
                $data['media'] = $request->file('media');
            }

            $consultation = $this->consultationService->createConsultation($data);

            // Format the response to include media URLs
            if ($consultation->media) {
                $mediaPaths = explode(',', $consultation->media);
                $mediaUrls = array_map(function($path) {
                    return Storage::url($path);
                }, $mediaPaths);
                $consultation->media_urls = $mediaUrls;
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Consultation created successfully',
                'data' => $consultation
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while creating the consultation',
                'errors' => $e->getMessage()
            ], 500);
        }
    }

    public function getUserConsultations()
    {
        try {
            $consultations = $this->consultationService->getUserConsultations(auth()->id());
            return response()->json([
                'status' => 'success',
                'data' => $consultations
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while fetching consultations',
                'errors' => $e->getMessage()
            ], 500);
        }
    }

    public function getUserConsultationsByStatus(Request $request)
    {
        try {
            $status = $request->query('status');
            $consultations = $this->consultationService->getUserConsultationsByStatus(auth()->id(), $status);
            return response()->json([
                'status' => 'success',
                'data' => $consultations
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while fetching consultations',
                'errors' => $e->getMessage()
            ], 500);
        }
    }
}
