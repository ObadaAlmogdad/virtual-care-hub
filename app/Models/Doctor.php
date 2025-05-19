<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Doctor extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'bio',
        'yearOfExper',
        'activatePoint'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function files(): BelongsToMany
    {
        return $this->belongsToMany(File::class, 'doctor_file')
            ->withPivot('type')
            ->withTimestamps();
    }

    public function licenses()
    {
        return $this->belongsToMany(File::class, 'doctor_file')
            ->wherePivot('type', 'license')
            ->withTimestamps();
    }

    public function specialties(): BelongsToMany
    {
        return $this->belongsToMany(MedicalTag::class, 'doctor_specialties')
                    ->withPivot(['time', 'photo', 'consultationFee'])
                    ->withTimestamps();
    }

    public function consultations()
    {
        return $this->hasMany(Consultation::class);
    }
}
