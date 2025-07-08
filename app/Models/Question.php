<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Question extends Model
{
    use HasFactory;
    protected $guarded=['id'];
    
    public function medicalTags(): BelongsToMany
    {
        return $this->belongsToMany(MedicalTag::class, 'question_medical_tags');
    }

    public function user_questions_tag_answers(): BelongsToMany
    {
        return $this->belongsToMany(UserQuestionTagAnswer::class);
    }
}
