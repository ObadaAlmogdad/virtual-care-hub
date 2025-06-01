<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Document;
use App\Models\BankAccount;
use App\Models\ActivationRequest;

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

    public function documents()
    {
        return $this->hasMany(Document::class);
    }

    public function bankAccount()
    {
        return $this->belongsTo(BankAccount::class, 'bank_account_id');
    }

    public function activationRequests()
    {
        return $this->hasMany(ActivationRequest::class);
    }

    public function medicalHistory()
    {
        return $this->hasOne(MedicalHistory::class);
    }

    public function consultations()
    {
        return $this->hasMany(Consultation::class);
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
}
