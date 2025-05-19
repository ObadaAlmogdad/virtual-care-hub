<?php

namespace App\Services;

use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Repositories\FileRepository;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class UserService
{
    protected UserRepositoryInterface $userRepository;
    protected $doctorService;
    protected $fileRepository;

    public function __construct(
        UserRepositoryInterface $userRepository, 
        DoctorService $doctorService,
        FileRepository $fileRepository
    ) {
        $this->userRepository = $userRepository;
        $this->doctorService = $doctorService;
        $this->fileRepository = $fileRepository;
    }

    public function getAllUsers()
    {
        return $this->userRepository->getAll();
    }

    public function getUserById($id)
    {
        return $this->userRepository->findById($id);
    }

    public function register(array $data, string $role)
    {
        $validator = Validator::make($data, [
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
            throw new ValidationException($validator);
        }

        try {
            DB::beginTransaction();

            // Store user photo
            $photoData = $this->fileRepository->storeFile($data['photo'], 'profiles');
            $fileRecord = $this->fileRepository->create($photoData);

            $data['password'] = Hash::make($data['password']);
            $data['role'] = $role;
            $data['isVerified'] = false;
            $data['photoPath'] = $fileRecord->path;

            $user = $this->userRepository->create($data);

            if ($role === 'Ductor') {
                $doctorData = [
                    'user_id' => $user->id,
                    'bio' => $data['bio'] ?? '',
                    'yearOfExper' => $data['yearOfExper'] ?? '0',
                    'activatePoint' => '0'
                ];
                
                $this->doctorService->createDoctor($doctorData);
            }

            DB::commit();

            // Add photo URL to the response
            $user->photo_url = $this->fileRepository->getFileUrl($fileRecord->path);
            
            return $user;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function updateUser($id, array $data)
    {
        $validator = Validator::make($data, [
            'fullName' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|string|email|max:255|unique:users,email,' . $id,
            'password' => 'sometimes|required|string|min:6',
            'phoneNumber' => 'sometimes|required|string|min:6',
            'photoPath' => 'sometimes|required|string',
            'address' => 'sometimes|required'
        ]);

        if ($validator->fails()) {
            return ['success' => false, 'errors' => $validator->errors()];
        }

        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        $user = $this->userRepository->update($id, $data);
        return ['success' => true, 'user' => $user];
    }

    public function deleteUser($id)
    {
        return $this->userRepository->delete($id);
    }

    public function getCurrentUser()
    {
        return Auth::user();
    }

    public function login(array $data)
    {
        $user = $this->userRepository->findByEmail($data['email']);

        if (!$user || !Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $token = $user->createToken('auth_token')->plainTextToken;
        return [
            'token' => $token,
            'user' => $user,
        ];
    }

    public function findByEmail(string $email): ?User
    {
        return $this->userRepository->findByEmail($email);
    }

    public function findById($id): ?User
    {
        return $this->userRepository->findById($id);
    }
}
