# إعداد البريد الإلكتروني لتأكيد الحساب

## الخطوات المطلوبة:

### 1. إعداد متغيرات البيئة (.env)
أضف هذه الإعدادات إلى ملف `.env`:

```env
# إعدادات البريد الإلكتروني
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your-email@gmail.com
MAIL_FROM_NAME="${APP_NAME}"
```

### 2. إعداد Gmail (مثال)
إذا كنت تستخدم Gmail:
1. فعّل المصادقة الثنائية
2. أنشئ كلمة مرور للتطبيق
3. استخدم كلمة المرور هذه في `MAIL_PASSWORD`

### 3. اختبار الإرسال
يمكنك اختبار إرسال البريد الإلكتروني باستخدام:

```bash
php artisan tinker
```

ثم في Tinker:
```php
Mail::raw('Test email', function($message) {
    $message->to('test@example.com')
            ->subject('Test');
});
```

### 4. تشغيل الطوابير (إذا كنت تستخدم الطوابير)
```bash
php artisan queue:work
```

## ملاحظات:
- تأكد من أن المستخدم لديه عنوان بريد إلكتروني صحيح
- يمكنك تخصيص محتوى الرسالة في `AccountVerificationNotification.php`
- الرسالة ستُرسل تلقائياً عند تأكيد الحساب من لوحة الإدارة 