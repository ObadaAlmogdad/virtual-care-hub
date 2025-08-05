<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Question extends Model
{
    use HasFactory;
    protected $guarded=['id'];

    protected $fillable = [
        'question_text',
        'isActive',
        'specialty_id',
        'parent_question_id',
        'parent_answer_value'
    ];

    public function medicalTags(): BelongsToMany
    {
        return $this->belongsToMany(MedicalTag::class, 'question_medical_tags');
    }

    public function specialty(): BelongsTo
    {
        return $this->belongsTo(MedicalTag::class, 'specialty_id');
    }

    public function user_questions_tag_answers()
    {
        return $this->hasMany(UserQuestionTagAnswer::class);
    }
}
