<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <title>إضافة منتج جديد</title>
</head>
<body>
    <h1>إضافة منتج جديد</h1>

    <form action="/products" method="POST" enctype="multipart/form-data">
        @csrf

        <p>
            <label>اسم المنتج:</label><br>
            <input type="text" name="name" required>
        </p>

        <p>
            <label>القسم:</label><br>
            <select name="category_id" required>
                <option value="">اختر القسم</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                @endforeach
            </select>
            <br>
            <!-- رابط إضافة قسم جديد  -->
            <a href="{{ url('/categories/create') }}" target="_blank" style="font-size: 13px; color: #007bff; text-decoration: underline;">
                + إضافة قسم جديد
            </a>
        </p>

        <p>
            <label>الوصف:</label><br>
            <textarea name="description"></textarea>
        </p>

        <p>
            <label>السعر:</label><br>
            <input type="number" step="0.01" name="price" required>
        </p>

        <p>
            <label>الكمية المتاحة:</label><br>
            <input type="number" name="stock" required>
        </p>

        <p>
            <label>صورة المنتج:</label><br>
            <input type="file" name="image" accept="image/*">
        </p>

        <button type="submit">حفظ المنتج</button>
    </form>

    <br>
    <a href="/products">الرجوع للمنتجات</a>
</body>
</html>
