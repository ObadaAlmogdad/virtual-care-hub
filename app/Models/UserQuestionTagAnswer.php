<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class UserQuestionTagAnswer extends Model
{
    use HasFactory;
    protected $guarded=['id'];
    public function questions()
    {
        return $this->belongsTo(Question::class);
    }

    public function consultationResult()
    {
        return $this->hasOne(ConsultationResult::class);
    }
}
