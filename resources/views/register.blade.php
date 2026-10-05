<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>إنشاء حساب جديد</title>
<link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body>
    <div class="form-container">
        <h2>إنشاء حساب جديد</h2>

        <form action="/register" method="POST">
            @csrf

            <div class="input-register">
                <label>الاسم:</label>
                <input type="text" name="name" required>
            </div>
            <div>
    <label>العمر:</label><br>
    <input type="number" name="age">
</div>
<div>
    <label>الجنس:</label><br>
    <select name="gender">
        <option value="male">ذكر</option>
        <option value="female">أنثى</option>
    </select>
</div>

            <div class="input-register">
                <label>البريد الإلكتروني:</label>
                <input type="email" name="email_or_phone" required>
            </div>

            <div class="input-register">
                <label>كلمة المرور:</label>
                <input type="password" name="password" required>
            </div>

            <button type="submit" class="btn">تسجيل حساب</button>
        </form>

        <div class="form-footer">
            لديك حساب بالفعل؟ <a href="/login">تسجيل الدخول</a>
        </div>
    </div>
</body>
</html>
