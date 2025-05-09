<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function index()
    {
        $users = $this->userService->getAllUsers();
        return response()->json(['users' => $users], 200);
    }

    public function register(Request $request)
    {
        try {
            $user = $this->userService->register($request->all(), "Patient");
            return response()->json(['user' => $user], 201);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        }
    }

    public function registerDuctor(Request $request)
    {
        try {
            $user = $this->userService->register($request->all(), "Ductor");
            return response()->json(['user' => $user], 201);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        }
    }

    public function registerAdmin(Request $request)
    {
        try {
            $user = $this->userService->register($request->all(), "Admin");
            return response()->json(['user' => $user], 201);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
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
            'user' => Auth::user()
        ], 200);
    }

    public function logout()
    {
        //auth()->user()->tokens()->delete();

        return response()->json([
            "status" => 1,
            "message" => "you are logged out successfully"
        ]);
    }

    /**
     * Display the specified user.
     */
    public function getUserById($id)
    {
        $user = $this->userService->getUserById($id);
        return response()->json(['user' => $user], 200);
    }

    /**
     * Update the specified user in storage.
     */
    public function update(Request $request, $id)
    {
        $result = $this->userService->updateUser($id, $request->all());

        if (!$result['success']) {
            return response()->json(['errors' => $result['errors']], 422);
        }

        return response()->json(['user' => $result['user']], 200);
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy($id)
    {
        $this->userService->deleteUser($id);
        return response()->json(null, 204);
    }
}
