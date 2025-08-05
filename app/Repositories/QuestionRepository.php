<?php

namespace App\Repositories;

use App\Models\Question;
use App\Repositories\Interfaces\QuestionRepositoryInterface;

class QuestionRepository implements QuestionRepositoryInterface
{
    protected $model;

    public function __construct(Question $model)
    {
        $this->model = $model;
    }

    public function getAll()
    {
        return $this->model->with('medicalTags')->get();
    }

    public function find($id)
    {
        return $this->model->with('medicalTags')->find($id);
    }

    public function create(array $data)
    {
        // Extract medical_tag_ids before creating the question
        $medicalTagIds = $data['medical_tag_ids'] ?? [];
        unset($data['medical_tag_ids']);
        
        $question = $this->model->create($data);
        
        if (!empty($medicalTagIds)) {
            $this->attachMedicalTags($question->id, $medicalTagIds);
        }
        
        return $question->load('medicalTags');
    }

    public function update($id, array $data)
    {
        $question = $this->model->find($id);
        if ($question) {
            // Extract medical_tag_ids before updating the question
            $medicalTagIds = $data['medical_tag_ids'] ?? [];
            unset($data['medical_tag_ids']);
            
            $question->update($data);
            
            if (!empty($medicalTagIds)) {
                $this->syncMedicalTags($question->id, $medicalTagIds);
            }
            
            return $question->load('medicalTags');
        }
        return null;
    }

    public function delete($id)
    {
        $question = $this->model->find($id);
        if ($question) {
            $question->medicalTags()->detach();
            return $question->delete();
        }
        return false;
    }

    public function getByMedicalTag($medicalTagId)
    {
        return $this->model->whereHas('medicalTags', function ($query) use ($medicalTagId) {
            $query->where('medical_tags.id', $medicalTagId);
        })->with('medicalTags')->get();
    }

    public function attachMedicalTags($questionId, array $medicalTagIds)
    {
        $question = $this->model->find($questionId);
        if ($question) {
            $question->medicalTags()->attach($medicalTagIds);
            return true;
        }
        return false;
    }

    public function detachMedicalTags($questionId, array $medicalTagIds)
    {
        $question = $this->model->find($questionId);
        if ($question) {
            $question->medicalTags()->detach($medicalTagIds);
            return true;
        }
        return false;
    }

    public function syncMedicalTags($questionId, array $medicalTagIds)
    {
        $question = $this->model->find($questionId);
        if ($question) {
            $question->medicalTags()->sync($medicalTagIds);
            return true;
        }
        return false;
    }
} 