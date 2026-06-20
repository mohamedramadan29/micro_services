<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إلغاء الاشتراك</title>
    <style>
        body { font-family: 'Tahoma', Arial, sans-serif; background: #f5f5f5; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; direction: rtl; }
        .container { background: #fff; padding: 40px; border-radius: 12px; box-shadow: 0 2px 20px rgba(0,0,0,0.1); text-align: center; max-width: 500px; width: 90%; }
        h1 { color: #333; margin-bottom: 20px; }
        p { color: #666; line-height: 1.8; }
        .success-icon { color: #28a745; font-size: 64px; margin-bottom: 20px; }
        .error-icon { color: #dc3545; font-size: 64px; margin-bottom: 20px; }
        .btn { display: inline-block; padding: 12px 30px; background: #007bff; color: #fff; text-decoration: none; border-radius: 6px; margin-top: 20px; }
        .btn:hover { background: #0056b3; }
    </style>
</head>
<body>
    <div class="container">
        @if($success)
            <div class="success-icon">✓</div>
            <h1>تم إلغاء الاشتراك بنجاح</h1>
            <p>تم إلغاء اشتراك <strong>{{ $email }}</strong> بنجاح. لن تستلم أي إيميلات مستقبلية من هذه القائمة.</p>
        @else
            <div class="error-icon">✗</div>
            <h1>رابط غير صالح</h1>
            <p>عذراً، رابط إلغاء الاشتراك غير صالح أو منتهي الصلاحية.</p>
        @endif
    </div>
</body>
</html>
