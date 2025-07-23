<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DoctorSpecialties extends Model
{
    use HasFactory;
    protected $guarded=['id'];
    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    public function medicalTag()
    {
        return $this->belongsTo(MedicalTag::class);
    }
}
