<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Doctor extends Model
{
    use HasFactory;
    protected $guarded=['id'];
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
