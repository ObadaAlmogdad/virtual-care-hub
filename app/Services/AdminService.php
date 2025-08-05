<?php

namespace App\Services;

use App\Repositories\Interfaces\AdminRepositoryInterface;
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Notifications\AccountVerificationNotification;


class AdminService
{

    protected $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

      public function verficatAccount($id)
    {
        // جلب المستخدم
        $user = $this->userRepository->findById($id);
        
        if (!$user) {
            throw new \Exception('المستخدم غير موجود');
        }

        $this->userRepository->update($user, ['isVerified' => true]);
        
        // إرسال إشعار تأكيد الحساب
        $user->notify(new AccountVerificationNotification($user));
    }
}
