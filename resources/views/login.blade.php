<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>تسجيل الدخول</title>
<link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body>
    <div class="form-container">
        <h2>تسجيل الدخول</h2>

        @if($errors->any())
            <div style="color: red; margin-bottom: 15px; text-align: center;">
                {{ $errors->first() }}
            </div>
        @endif

        <form action="/login" method="POST">
            @csrf

            <div class="input-group">
                <label>البريد الإلكتروني أو رقم التليفون:</label>
                <input type="text" name="email_or_phone" required>
            </div>

            <div class="input-group">
                <label>كلمة المرور:</label>
                <input type="password" name="password" required>
            </div>

            <button type="submit" class="btn">دخول</button>
        </form>

        <div class="form-footer">
            ليس لديك حساب؟ <a href="/register">إنشاء حساب جديد</a>
        </div>
    </div>
</body>
</html>
