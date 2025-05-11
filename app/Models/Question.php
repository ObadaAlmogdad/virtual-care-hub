<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Question extends Model
{
    use HasFactory,SoftDeletes;
    protected $guarded=['id'];
    public function specialties(): BelongsToMany
    {
        return $this->belongsToMany(MedicalTag::class);
    }

    public function user_questions_tag_answers(): BelongsToMany
    {
        return $this->belongsToMany(UserQuestionTagAnswer::class);
    }
}
