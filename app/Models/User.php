<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;


    protected $guarded = ['id'];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function patient()
    {
        return $this->hasOne(Patient::class);
    }

    public function doctor()
    {
        return $this->hasOne(Doctor::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function doctorPayments()
    {
        return $this->hasMany(Payment::class, 'doctor_id');
    }

    public function isAdmin()
    {
        return $this->role === 'Admin';
    }

    public function isDoctor()
    {
        return $this->role === 'Doctor';
    }

    public function isPatient()
    {
        return $this->role === 'Patient';
    }

    public function doctorChats()
    {
        return $this->hasMany(Chat::class, 'doctor_id');
    }

    public function patientChats()
    {
        return $this->hasMany(Chat::class, 'patient_id');
    }
}
