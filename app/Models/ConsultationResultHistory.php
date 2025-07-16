<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ConsultationResultHistory extends Model
{
    use HasFactory;
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
