<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\QuestionService;
use Illuminate\Http\Request;

class QuestionController extends Controller
{
    protected $questionService;

    public function __construct(QuestionService $questionService)
    {
        $this->questionService = $questionService;
    }

    protected function handleError(\Exception $e)
    {
        $statusCode = $e->getCode() >= 100 && $e->getCode() < 600 ? $e->getCode() : 500;
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage()
        ], $statusCode);
    }

    protected function formatQuestionResponse($question)
    {
        if (!$question) return null;
        
        $formattedQuestion = $question->toArray();
        
        // Map question_text to content for API consistency
        $formattedQuestion['content'] = $formattedQuestion['question_text'] ?? '';
        unset($formattedQuestion['question_text']);
        
        if (isset($formattedQuestion['medical_tags'])) {
            // Remove duplicate medical tags
            $uniqueTags = collect($formattedQuestion['medical_tags'])->unique('id')->values();
            $formattedQuestion['medical_tags'] = $uniqueTags->map(function ($tag) {
                return [
                    'id' => $tag['id'],
                    'name' => $tag['name'],
                    'name_ar' => $tag['name_ar'],
                    'is_active' => $tag['is_active'],
                    'pivot' => $tag['pivot']
                ];
            })->toArray();
        }
        
        return $formattedQuestion;
    }

    public function index()
    {
        try {
            $questions = $this->questionService->getAllQuestions();
            return response()->json([
                'status' => 'success',
                'data' => $questions->map(function($question) {
                    return $this->formatQuestionResponse($question);
                })
            ]);
        } catch (\Exception $e) {
            return $this->handleError($e);
        }
    }

    public function show($id)
    {
        try {
            $question = $this->questionService->getQuestion($id);
            return response()->json([
                'status' => 'success',
                'data' => $this->formatQuestionResponse($question)
            ]);
        } catch (\Exception $e) {
            return $this->handleError($e);
        }
    }

    public function store(Request $request)
    {
        try {
            $question = $this->questionService->createQuestion($request->all());
            return response()->json([
                'status' => 'success',
                'message' => 'Question created successfully',
                'data' => $this->formatQuestionResponse($question)
            ], 201);
        } catch (\Exception $e) {
            return $this->handleError($e);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $question = $this->questionService->updateQuestion($id, $request->all());
            return response()->json([
                'status' => 'success',
                'message' => 'Question updated successfully',
                'data' => $this->formatQuestionResponse($question)
            ]);
        } catch (\Exception $e) {
            return $this->handleError($e);
        }
    }

    public function destroy($id)
    {
        try {
            $this->questionService->deleteQuestion($id);
            return response()->json([
                'status' => 'success',
                'message' => 'Question deleted successfully'
            ]);
        } catch (\Exception $e) {
            return $this->handleError($e);
        }
    }

    public function getByMedicalTag($medicalTagId)
    {
        try {
            $questions = $this->questionService->getQuestionsByMedicalTag($medicalTagId);
            return response()->json([
                'status' => 'success',
                'data' => $questions->map(function($question) {
                    return $this->formatQuestionResponse($question);
                })
            ]);
        } catch (\Exception $e) {
            return $this->handleError($e);
        }
    }

    public function attachMedicalTags(Request $request, $id)
    {
        try {
            // Remove duplicates from the input
            $uniqueTagIds = array_unique($request->medical_tag_ids);
            $this->questionService->attachMedicalTags($id, $uniqueTagIds);
            return response()->json([
                'status' => 'success',
                'message' => 'Medical tags attached successfully'
            ]);
        } catch (\Exception $e) {
            return $this->handleError($e);
        }
    }

    public function detachMedicalTags(Request $request, $id)
    {
        try {
            $this->questionService->detachMedicalTags($id, $request->medical_tag_ids);
            return response()->json([
                'status' => 'success',
                'message' => 'Medical tags detached successfully'
            ]);
        } catch (\Exception $e) {
            return $this->handleError($e);
        }
    }

    public function syncMedicalTags(Request $request, $id)
    {
        try {
            // Remove duplicates from the input
            $uniqueTagIds = array_unique($request->medical_tag_ids);
            $this->questionService->syncMedicalTags($id, $uniqueTagIds);
            return response()->json([
                'status' => 'success',
                'message' => 'Medical tags synced successfully'
            ]);
        } catch (\Exception $e) {
            return $this->handleError($e);
        }
    }

    public function attachQuestionsToMedicalTag(Request $request, $medicalTagId)
    {
        try {
            $request->validate([
                'question_ids' => 'required|array',
                'question_ids.*' => 'exists:questions,id'
            ]);

            // Remove duplicates from the input
            $uniqueQuestionIds = array_unique($request->question_ids);
            $this->questionService->attachQuestionsToMedicalTag($medicalTagId, $uniqueQuestionIds);
            
            return response()->json([
                'status' => 'success',
                'message' => 'Questions attached to medical tag successfully',
                'data' => [
                    'medical_tag_id' => $medicalTagId,
                    'attached_questions_count' => count($uniqueQuestionIds)
                ]
            ]);
        } catch (\Exception $e) {
            return $this->handleError($e);
        }
    }
} 