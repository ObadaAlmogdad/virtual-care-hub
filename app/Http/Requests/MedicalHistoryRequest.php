<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MedicalHistoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check(); // التحقق من تسجيل الدخول فقط
    }

    public function rules(): array
    {
        return [
            // الحقول الإجبارية
            'chronic_diseases' => 'required|array|max:10',
            'chronic_diseases.*' => 'string|max:100',
            'allergies' => 'required|string|max:255',
            
            // الحقول الاختيارية
            'general_diseases' => 'nullable|array',
            'general_diseases.*' => 'string|max:80',
            'surgeries' => 'nullable|string|max:500',
            'permanent_medications' => 'nullable|string|max:500',
            
            // حقل الملفات
            'medical_documents' => 'sometimes|file|mimes:pdf,jpg,png|max:5120',
            
            // حقل المسار (إذا كان يُرسل كرابط)
            'medical_documents_path' => 'nullable|url|max:2048'
        ];
    }

    public function messages(): array
    {
        return [
            'chronic_diseases.required' => 'يجب إدخال الأمراض المزمنة',
            'chronic_diseases.*.max' => 'كل مرض يجب ألا يتجاوز 100 حرف',
            'medical_documents.max' => 'حجم الملف يجب أن لا يتجاوز 5 ميجابايت',
            'general_diseases.*.max' => 'كل مرض عام يجب ألا يتجاوز 80 حرف',
            'medical_documents_path.url' => 'يجب إدخال رابط صحيح للمستندات'
        ];
    }

    public function attributes(): array
    {
        return [
            'chronic_diseases' => 'الأمراض المزمنة',
            'allergies' => 'الحساسيات',
            'general_diseases' => 'الأمراض العامة',
            'surgeries' => 'العمليات الجراحية',
            'permanent_medications' => 'الأدوية الدائمة',
            'medical_documents' => 'المستندات الطبية',
            'medical_documents_path' => 'رابط المستندات'
        ];
    }
}