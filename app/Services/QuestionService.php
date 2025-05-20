<?php

namespace App\Services;

use App\Repositories\Interfaces\QuestionRepositoryInterface;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class QuestionService
{
    protected $questionRepository;

    public function __construct(QuestionRepositoryInterface $questionRepository)
    {
        $this->questionRepository = $questionRepository;
    }

    public function getAllQuestions()
    {
        return $this->questionRepository->getAll();
    }

    public function getQuestion($id)
    {
        $question = $this->questionRepository->find($id);
        if (!$question) {
            throw new \Exception('Question not found', 404);
        }
        return $question;
    }

    public function createQuestion(array $data)
    {
        $validator = Validator::make($data, [
            'content' => 'required|string|max:1000',
            'isActive' => 'required|boolean',
            'medical_tag_ids' => 'required|array',
            'medical_tag_ids.*' => 'exists:medical_tags,id'
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $this->questionRepository->create($data);
    }

    public function updateQuestion($id, array $data)
    {
        $validator = Validator::make($data, [
            'content' => 'sometimes|required|string|max:1000',
            'isActive' => 'sometimes|required|boolean',
            'medical_tag_ids' => 'sometimes|required|array',
            'medical_tag_ids.*' => 'exists:medical_tags,id'
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $question = $this->questionRepository->update($id, $data);
        if (!$question) {
            throw new \Exception('Question not found', 404);
        }
        return $question;
    }

    public function deleteQuestion($id)
    {
        $result = $this->questionRepository->delete($id);
        if (!$result) {
            throw new \Exception('Question not found', 404);
        }
        return true;
    }

    public function getQuestionsByMedicalTag($medicalTagId)
    {
        return $this->questionRepository->getByMedicalTag($medicalTagId);
    }

    public function attachMedicalTags($questionId, array $medicalTagIds)
    {
        $validator = Validator::make(['medical_tag_ids' => $medicalTagIds], [
            'medical_tag_ids' => 'required|array',
            'medical_tag_ids.*' => 'exists:medical_tags,id'
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $result = $this->questionRepository->attachMedicalTags($questionId, $medicalTagIds);
        if (!$result) {
            throw new \Exception('Question not found', 404);
        }
        return true;
    }

    public function detachMedicalTags($questionId, array $medicalTagIds)
    {
        $validator = Validator::make(['medical_tag_ids' => $medicalTagIds], [
            'medical_tag_ids' => 'required|array',
            'medical_tag_ids.*' => 'exists:medical_tags,id'
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $result = $this->questionRepository->detachMedicalTags($questionId, $medicalTagIds);
        if (!$result) {
            throw new \Exception('Question not found', 404);
        }
        return true;
    }

    public function syncMedicalTags($questionId, array $medicalTagIds)
    {
        $validator = Validator::make(['medical_tag_ids' => $medicalTagIds], [
            'medical_tag_ids' => 'required|array',
            'medical_tag_ids.*' => 'exists:medical_tags,id'
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $result = $this->questionRepository->syncMedicalTags($questionId, $medicalTagIds);
        if (!$result) {
            throw new \Exception('Question not found', 404);
        }
        return true;
    }
} 