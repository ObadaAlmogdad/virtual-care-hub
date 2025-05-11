<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConsultationResult extends Model
{
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
