@extends('layout')

@section('title', 'تعديل ' . $product->name)

@section('content')
    <section class="seller-edit-page">
        <header class="seller-edit-header">
            <div>
                <p class="seller-edit-eyebrow">إدارة المنتجات</p>
                <h1>تعديل المنتج</h1>
                <p class="seller-edit-product-name">{{ $product->name }}</p>
            </div>
            <a class="seller-edit-back" href="{{ url('/products') }}">العودة للمنتجات</a>
        </header>

        <form class="seller-product-form" action="{{ url('/products/' . $product->id . '/update') }}" method="POST" enctype="multipart/form-data">
            @csrf

            @if($errors->any())
                <div class="seller-form-errors" role="alert">
                    راجع البيانات المدخلة وحاول مرة أخرى.
                </div>
            @endif

            <div class="seller-product-fields">
                <div class="seller-product-field">
                    <label for="product-name">اسم المنتج</label>
                    <input id="product-name" type="text" name="name" value="{{ old('name', $product->name) }}" required>
                    @error('name') <small class="seller-field-error">{{ $message }}</small> @enderror
                </div>

                <div class="seller-product-field">
                    <label for="product-category">القسم</label>
                    <select id="product-category" name="category_id" required>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" @selected(old('category_id', $product->category_id) == $category->id)>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('category_id') <small class="seller-field-error">{{ $message }}</small> @enderror
                </div>

                <div class="seller-product-field seller-product-description">
                    <label for="product-description">الوصف</label>
                    <textarea id="product-description" name="description" rows="5">{{ old('description', $product->description) }}</textarea>
                    @error('description') <small class="seller-field-error">{{ $message }}</small> @enderror
                </div>

                <div class="seller-product-field">
                    <label for="product-price">السعر بالجنيه</label>
                    <input id="product-price" type="number" name="price" min="0.01" step="0.01" value="{{ old('price', $product->price) }}" required>
                    @error('price') <small class="seller-field-error">{{ $message }}</small> @enderror
                </div>

                <div class="seller-product-field">
                    <label for="product-stock">الكمية المتاحة</label>
                    <input id="product-stock" type="number" name="stock" min="0" step="1" value="{{ old('stock', $product->stock) }}" required>
                    @error('stock') <small class="seller-field-error">{{ $message }}</small> @enderror
                </div>

                <div class="seller-product-field seller-product-image-field">
                    <label for="product-image">صورة جديدة <span>اختياري</span></label>
                    @if($product->image)
                        <div class="seller-current-image">
                            <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}">
                            <span>الصورة الحالية</span>
                        </div>
                    @endif
                    <div class="seller-image-controls">
                        <input id="product-image" type="file" name="image" accept="image/*">
                        <a class="seller-image-edit-button" href="{{ url('/products/' . $product->id . '/images/edit') }}">تعديل الصور</a>
                    </div>
                    <small>سيتم الاحتفاظ بالصورة الحالية إذا لم تختر صورة جديدة.</small>
                    @error('image') <small class="seller-field-error">{{ $message }}</small> @enderror
                </div>
            </div>

            <div class="seller-product-actions">
                <a href="{{ url('/products') }}">إلغاء</a>
                <button type="submit">حفظ التعديلات</button>
            </div>
        </form>
    </section>
@endsection
