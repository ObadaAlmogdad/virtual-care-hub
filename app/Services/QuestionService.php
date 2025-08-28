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
            'specialty_ids' => 'required|array',
            'specialty_ids.*' => 'exists:medical_tags,id'
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        // Transform the data to match the database schema
        $transformedData = [
            'question_text' => $data['content'],
            'isActive' => $data['isActive'],
            'specialty_id' => $data['specialty_ids'][0], // Use the first specialty as the main one
            'medical_tag_ids' => $data['specialty_ids'] // Store all specialty IDs for the many-to-many relationship
        ];

        return $this->questionRepository->create($transformedData);
    }

    public function updateQuestion($id, array $data)
    {
        $validator = Validator::make($data, [
            'content' => 'sometimes|required|string|max:1000',
            'isActive' => 'sometimes|required|boolean',
            'specialty_ids' => 'sometimes|required|array',
            'specialty_ids.*' => 'exists:medical_tags,id'
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        // Transform the data to match the database schema
        $transformedData = [];
        
        if (isset($data['content'])) {
            $transformedData['question_text'] = $data['content'];
        }
        
        if (isset($data['isActive'])) {
            $transformedData['isActive'] = $data['isActive'];
        }
        
        if (isset($data['specialty_ids'])) {
            $transformedData['specialty_id'] = $data['specialty_ids'][0]; // Use the first specialty as the main one
            $transformedData['medical_tag_ids'] = $data['specialty_ids']; // Store all specialty IDs for the many-to-many relationship
        }

        $question = $this->questionRepository->update($id, $transformedData);
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

    public function attachQuestionsToMedicalTag($medicalTagId, array $questionIds)
    {
        $validator = Validator::make([
            'medical_tag_id' => $medicalTagId,
            'question_ids' => $questionIds
        ], [
            'medical_tag_id' => 'required|exists:medical_tags,id',
            'question_ids' => 'required|array',
            'question_ids.*' => 'exists:questions,id'
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $result = $this->questionRepository->attachQuestionsToMedicalTag($medicalTagId, $questionIds);
        if (!$result) {
            throw new \Exception('Medical tag not found', 404);
        }
        return true;
    }
}
