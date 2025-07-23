<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MedicalHistory extends Model
{
    use HasFactory,SoftDeletes;

    protected $guarded=['id'];
    protected $casts = [
        'general_diseases' => 'array',
        'chronic_diseases' => 'array',
        'medical_documents_path' => 'array',
    ];

    public function patient()
{
    return $this->belongsTo(Patient::class);
}

    public function consultationResultHistory()
    {
        return $this->hasOne(ConsultationResultHistory::class);
    }
}
