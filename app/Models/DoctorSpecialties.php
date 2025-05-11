<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DoctorSpecialties extends Model
{
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
