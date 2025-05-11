<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConsultationResultHistory extends Model
{
    public function consultationResult()
    {
        return $this->belongsTo(ConsultationResult::class);
    }

    public function medicalHistory()
    {
        return $this->belongsTo(MedicalHistory::class);
    }
}
