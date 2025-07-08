<?php

namespace App\Repositories\Interfaces;

interface QuestionRepositoryInterface
{
    public function getAll();
    public function find($id);
    public function create(array $data);
    public function update($id, array $data);
    public function delete($id);
    public function getByMedicalTag($medicalTagId);
    public function attachMedicalTags($questionId, array $medicalTagIds);
    public function detachMedicalTags($questionId, array $medicalTagIds);
    public function syncMedicalTags($questionId, array $medicalTagIds);
} 