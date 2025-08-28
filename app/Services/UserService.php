<?php

namespace App\Services;

use App\Models\MedicalHistory;
use App\Models\Patient;
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

        try {
            DB::beginTransaction();
            // Store user photo
            if (isset($data['photo']) && $data['photo'] instanceof \Illuminate\Http\UploadedFile)
                // dd('asd;las');
            $photoData = $this->fileRepository->storeFile($data['photo'], 'profiles');
            $fileRecord = $this->fileRepository->create($photoData);

            $data['password'] = Hash::make($data['password']);
            $data['role'] = $role;
            $data['isVerified'] = false;
            $data['photoPath'] = $fileRecord->path;

            $user = $this->userRepository->create($data);

            $patient=Patient::create([
            'user_id'=>$user->id,
            'fakeName'=>'',
            'height'=>0.0,
            'weight'=>0.0
            ]);

            MedicalHistory::create([
            'patient_id' => $patient->id,
            ]);

            $token = $user->createToken('auth_token')->plainTextToken;
            $user['token']=$token;

            DB::commit();

            // Add photo URL to the response
            $user->photo_url = $this->fileRepository->getFileUrl($fileRecord->path);

            return $user;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    public function registerDuctorMinimal(array $data)
{
    try {
        DB::beginTransaction();

        $user = $this->userRepository->create([
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => $data['role'],
            'isVerified' => false,
            'fullName' => 'Not Set',
            'phoneNumber' => 'Not Set',
            'photoPath' => '',
            'address' => 'Not Set',
            'birthday' => now(),
            'gender' => 'Not Set',
        ]);
        $doctor = $this->doctorService->create([
            'user_id' => $user->id,
            'bio' => '',
            'activatePoint' => '0',
        ]);

        $certificatePaths = [];
        foreach ($data['certificate_images'] as $image) {
            $certificatePaths[] = $image->store('certificates', 'public');
        }
        // dd('asdasd');

        $doctor->update([
            'certificate_images' => json_encode($certificatePaths)
        ]);

        $this->doctorService->addSpecialtyBySystem($doctor->id, [
            'medical_tag_id' => $data['medical_tag_id'],
            'start_time' => $data['start_time'],
            'end_time' => $data['end_time'],
            'consultation_fee' => 0,
            'is_active' => false,
            'yearOfExper' => $data['yearOfExper'] ?? '0',
        ]);

        DB::commit();
         $token = $user->createToken('auth_token')->plainTextToken;
         $user['token']=$token;
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

   public function updateProfile($userId, $request)
{
    $user = $this->userRepository->findById($userId);

    $userData = $request->only(['fullName', 'phoneNumber', 'address', 'birthday', 'gender']);
    $patientData = $request->only(['fakeName', 'height', 'weight']);
    $medicalData = $request->only([
        'general_diseases',
        'chronic_diseases',
        'surgeries',
        'allergies',
        'permanent_medications',
    ]);

    if ($request->hasFile('photoPath')) {
        $photoData = $this->fileRepository->storeFile($request->file('photoPath'), 'profiles');
        $fileRecord = $this->fileRepository->create($photoData);
        $userData['photoPath'] = $fileRecord->path;
    }

    if ($request->hasFile('medical_documents_path')) {
        $paths = [];
        foreach ($request->file('medical_documents_path') as $file) {
            $docData = $this->fileRepository->storeFile($file, 'medical_documents');
            $paths[] = $docData['path'];
        }
        $medicalData['medical_documents_path'] = json_encode($paths);
        // dd($medicalData);
    }

    return $this->userRepository->updateProfile($userId, $userData, $patientData, $medicalData);
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
