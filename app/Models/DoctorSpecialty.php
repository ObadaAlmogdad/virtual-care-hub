<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DoctorSpecialty extends Model
{
    use HasFactory;

    protected $fillable = [
        'doctor_id',
        'medical_tag_id',
        'start_time',
        'end_time',
        'photo',
        'consultation_fee',
        'description',
        'is_active'
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'is_active' => 'boolean',
        'consultation_fee' => 'decimal:2'
    ];

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    public function medicalTag()
    {
        return $this->belongsTo(MedicalTag::class);
    }
} 