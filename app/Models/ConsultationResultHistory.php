<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConsultationResultHistory extends Model
{
    protected $fillable = [
    'consultation_result_id',
    'medical_history_id',
];

    public function consultationResult()
    {
        return $this->belongsTo(ConsultationResult::class);
    }

    public function medicalHistory()
    {
        return $this->belongsTo(MedicalHistory::class);
    }
}
