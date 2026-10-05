<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <title>{{ $product->name }}</title>
<link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body>
    <h1>{{ $product->name }}</h1>
    <p><strong>الوصف:</strong> {{ $product->description }}</p>
    <p><strong>السعر:</strong> {{ $product->price }} جنيه</p>
    <p><strong>الكمية المتاحة:</strong> {{ $product->stock }}</p>
    <p><strong>القسم:</strong> {{ $product->category->name }}</p>

    @if($product->image)
        <p>
            <label>صورة المنتج:</label><br>
            <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" width="200">
        </p>
    @endif

    <!-- زر إضافة إلى السلة -->
    <form action="/cart/add/{{ $product->id }}" method="POST">
        @csrf
        <button type="submit">أضف إلى السلة</button>
    </form>

    <br>
    <a href="/products">الرجوع لكل المنتجات</a>
    <br><br>

    <!-- أزرار البائع -->
    @if(Auth::check() && Auth::user()->role === 'seller')
        <a href="/products/{{ $product->id }}/edit">تعديل هذا المنتج</a>
        <br><br>
        <form action="/products/{{ $product->id }}/delete" method="POST">
            @csrf
            <button type="submit">حذف هذا المنتج</button>
        </form>
    @endif
</body>
</html>
