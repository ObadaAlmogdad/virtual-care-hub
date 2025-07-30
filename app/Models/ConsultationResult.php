<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConsultationResult extends Model
{
    use HasFactory;

    protected $fillable = [
    'consultation_id',
    'user_question_tag_answer_id',
    'replayOfDoctor',
    'accepted',
];

    public function consultation()
    {
        return $this->belongsTo(Consultation::class);
    }
    public function user_question_tag_answer()
    {
        return $this->belongsTo(UserQuestionTagAnswer::class);
    }

    public function consultationResultHistory()
    {
        return $this->hasOne(ConsultationResultHistory::class);
    }
}
