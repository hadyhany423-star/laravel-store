@extends('layout')

@section('title', 'تعديل صور ' . $product->name)

@section('content')
    <section class="seller-edit-page">
        <header class="seller-edit-header">
            <div>
                <p class="seller-edit-eyebrow">إدارة المنتجات</p>
                <h1>تعديل صور المنتج</h1>
                <p class="seller-edit-product-name">{{ $product->name }}</p>
            </div>
            <a class="seller-edit-back" href="{{ url('/products') }}">العودة للمنتجات</a>
        </header>

        <form class="seller-product-form" action="{{ url('/products/' . $product->id . '/images') }}" method="POST" enctype="multipart/form-data">
            @csrf

            @if($product->image)
                <div class="seller-current-image">
                    <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}">
                    <span>الصورة الحالية</span>
                </div>
            @endif

            <div class="seller-product-field seller-product-image-field">
                <label for="product-image">اختر الصورة الجديدة</label>
                <input id="product-image" type="file" name="image" accept="image/*" required>
                <small>الحد الأقصى لحجم الصورة 2 ميجابايت.</small>
                @error('image') <small class="seller-field-error">{{ $message }}</small> @enderror
            </div>

            <div class="seller-product-actions">
                <a href="{{ url('/products') }}">إلغاء</a>
                <button type="submit">حفظ الصورة</button>
            </div>
        </form>
    </section>
@endsection
