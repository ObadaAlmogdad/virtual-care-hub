<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Consultation extends Model
{
    use HasFactory;

    protected $guarded=['id'];

    protected $fillable = [
        'patient_id',
        'doctor_id',
        'medical_tag_id',
        'isSpecial',
        'problem',
        'media',
        'isAnonymous',
        'replayOfDoctor',
        'fee',
        'status',
        'scheduled_at',
        'reminder_before_minutes'
    ];

    protected $casts = [
        'isSpecial' => 'boolean',
        'isAnonymous' => 'boolean',
        'scheduled_at' => 'datetime',
        'fee' => 'double'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function medicalTag(): BelongsTo
    {
        return $this->belongsTo(MedicalTag::class);
    }

    public function rating()
    {
        return $this->hasOne(Rating::class);
    }

    public function consultationResults()
    {
        return $this->hasMany(ConsultationResult::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }
}
