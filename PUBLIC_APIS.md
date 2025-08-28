# Public APIs Documentation

هذا الملف يحتوي على توثيق للـ APIs العامة التي لا تحتاج إلى authentication.

## Base URL
```
http://your-domain.com/api/public
```

## Doctor APIs

### 1. Get All Doctors
```
GET /public/doctors
```

**Response:**
```json
{
    "status": "success",
    "data": [
        {
            "id": 1,
            "user_id": 1,
            "bio": "دكتور متخصص في أمراض القلب",
            "rating": 4.5,
            "user": {
                "id": 1,
                "fullName": "د. أحمد محمد",
                "email": "ahmed@example.com",
                "phoneNumber": "+966501234567"
            },
            "specialties": [
                {
                    "id": 1,
                    "medical_tag_id": 1,
                    "consultation_fee": 200.00,
                    "medicalTag": {
                        "id": 1,
                        "name": "Cardiology",
                        "name_ar": "أمراض القلب"
                    }
                }
            ]
        }
    ]
}
```

### 2. Get Doctor by ID
```
GET /public/doctors/{id}
```

**Parameters:**
- `id` (required): Doctor ID

**Response:**
```json
{
    "status": "success",
    "data": {
        "id": 1,
        "user_id": 1,
        "bio": "دكتور متخصص في أمراض القلب",
        "rating": 4.5,
        "user": {
            "id": 1,
            "fullName": "د. أحمد محمد",
            "email": "ahmed@example.com",
            "phoneNumber": "+966501234567"
        },
        "specialties": [
            {
                "id": 1,
                "medical_tag_id": 1,
                "consultation_fee": 200.00,
                "medicalTag": {
                    "id": 1,
                    "name": "Cardiology",
                    "name_ar": "أمراض القلب"
                }
            }
        ]
    }
}
```

### 3. Get Doctors by Specialty
```
GET /public/doctors/specialty/{specialtyId}
```

**Parameters:**
- `specialtyId` (required): Medical specialty ID

**Response:**
```json
{
    "status": "success",
    "data": [
        {
            "id": 1,
            "user_id": 1,
            "bio": "دكتور متخصص في أمراض القلب",
            "rating": 4.5,
            "user": {
                "id": 1,
                "fullName": "د. أحمد محمد",
                "email": "ahmed@example.com",
                "phoneNumber": "+966501234567"
            },
            "specialties": [
                {
                    "id": 1,
                    "medical_tag_id": 1,
                    "consultation_fee": 200.00,
                    "medicalTag": {
                        "id": 1,
                        "name": "Cardiology",
                        "name_ar": "أمراض القلب"
                    }
                }
            ]
        }
    ]
}
```

### 4. Search Doctors
```
GET /public/doctors/search?name=أحمد&specialty=قلب
```

**Query Parameters:**
- `name` (optional): Doctor name to search for
- `specialty` (optional): Medical specialty to search for

**Response:**
```json
{
    "status": "success",
    "data": [
        {
            "id": 1,
            "user_id": 1,
            "bio": "دكتور متخصص في أمراض القلب",
            "rating": 4.5,
            "user": {
                "id": 1,
                "fullName": "د. أحمد محمد",
                "email": "ahmed@example.com",
                "phoneNumber": "+966501234567"
            },
            "specialties": [
                {
                    "id": 1,
                    "medical_tag_id": 1,
                    "consultation_fee": 200.00,
                    "medicalTag": {
                        "id": 1,
                        "name": "Cardiology",
                        "name_ar": "أمراض القلب"
                    }
                }
            ]
        }
    ]
}
```

## Medical Specialties APIs

### 1. Get All Medical Specialties
```
GET /public/medical-specialties
```

**Response:**
```json
{
    "status": "success",
    "data": [
        {
            "id": 1,
            "name": "Cardiology",
            "name_ar": "أمراض القلب",
            "description": "تخصص في أمراض القلب والأوعية الدموية",
            "icon": "heart-icon.png",
            "is_active": true,
            "order": 1
        },
        {
            "id": 2,
            "name": "Dermatology",
            "name_ar": "الأمراض الجلدية",
            "description": "تخصص في أمراض الجلد",
            "icon": "skin-icon.png",
            "is_active": true,
            "order": 2
        }
    ]
}
```

### 2. Get Medical Specialty by ID
```
GET /public/medical-specialties/{id}
```

**Parameters:**
- `id` (required): Medical specialty ID

**Response:**
```json
{
    "status": "success",
    "data": {
        "id": 1,
        "name": "Cardiology",
        "name_ar": "أمراض القلب",
        "description": "تخصص في أمراض القلب والأوعية الدموية",
        "icon": "heart-icon.png",
        "is_active": true,
        "order": 1,
        "doctor_specialties": [
            {
                "id": 1,
                "doctor_id": 1,
                "consultation_fee": 200.00,
                "doctor": {
                    "id": 1,
                    "user_id": 1,
                    "bio": "دكتور متخصص في أمراض القلب",
                    "rating": 4.5,
                    "user": {
                        "id": 1,
                        "fullName": "د. أحمد محمد",
                        "email": "ahmed@example.com",
                        "phoneNumber": "+966501234567"
                    }
                }
            }
        ]
    }
}
```

### 3. Get Doctors by Medical Specialty
```
GET /public/medical-specialties/{id}/doctors
```

**Parameters:**
- `id` (required): Medical specialty ID

**Response:**
```json
{
    "status": "success",
    "data": [
        {
            "id": 1,
            "user_id": 1,
            "bio": "دكتور متخصص في أمراض القلب",
            "rating": 4.5,
            "user": {
                "id": 1,
                "fullName": "د. أحمد محمد",
                "email": "ahmed@example.com",
                "phoneNumber": "+966501234567"
            },
            "specialties": [
                {
                    "id": 1,
                    "medical_tag_id": 1,
                    "consultation_fee": 200.00,
                    "medicalTag": {
                        "id": 1,
                        "name": "Cardiology",
                        "name_ar": "أمراض القلب"
                    }
                }
            ]
        }
    ]
}
```

## Error Responses

### 404 Not Found
```json
{
    "status": "error",
    "message": "Doctor not found"
}
```

### 500 Internal Server Error
```json
{
    "status": "error",
    "message": "An error occurred while fetching doctor information"
}
```

## Notes

- جميع هذه الـ APIs لا تحتاج إلى authentication
- يتم إرجاع الأطباء النشطين فقط (is_active = true)
- يتم إرجاع التخصصات النشطة فقط (is_active = true)
- جميع البيانات مرتبة حسب الترتيب المحدد في قاعدة البيانات 