<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConsultationAnswer extends Model
{
    use HasFactory;

    protected $fillable = [
        'consultation_id',
        'question_id',
        'answer_text',
    ];

    public function consultation()
{
    return $this->belongsTo(Consultation::class);
}

public function question()
{
    return $this->belongsTo(Question::class);
}

}
