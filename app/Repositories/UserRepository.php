<?php

namespace App\Repositories;

use App\Models\User;
use App\Repositories\Interfaces\UserRepositoryInterface;

use Illuminate\Support\Facades\Cache;

class UserRepository implements UserRepositoryInterface
{
    protected $model;

    public function __construct(User $model)
    {
        $this->model = $model;
    }

    public function getAll()
    {
        return $this->model->all();
    }

    public function findById($id)
    {
        return $this->model->findOrFail($id);
    }

    public function findByEmail($email)
    {
        return Cache::remember("user:{$email}", 3600, function () use ($email) {
            return User::where('email', $email)->first();
        });
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function update($id, array $data)
    {
        $user = $this->findById($id);
        $user->update($data);
        return $user;
    }

    public function delete($id)
    {
        $user = $this->findById($id);
        $user->delete();
        return true;
    }

    public function createUser(array $data): User
    {
        return $this->model->create($data);
    }
}
