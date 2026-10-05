@extends('layout')

@section('title', 'منتجات قسم ' . $category->name)

@section('content')
    <section class="category-products-page">
        <header class="category-products-header">
            <div>
                <p class="category-products-eyebrow">تسوق حسب القسم</p>
                <h1>{{ $category->name }}</h1>
                <p class="category-products-count">{{ $category->products->count() }} منتجات</p>
            </div>
            <a class="category-back-link" href="{{ url('/categories') }}">كل الأقسام</a>
        </header>

        @if($category->products->isNotEmpty())
            <ul class="category-products-grid">
                @foreach($category->products as $product)
                    <li class="category-product-card">
                        <a class="category-product-image-link" href="{{ url('/products/' . $product->id) }}">
                            @if($product->image)
                                <img
                                    class="category-product-image"
                                    src="{{ asset('storage/' . $product->image) }}"
                                    alt="{{ $product->name }}"
                                    loading="lazy"
                                >
                            @else
                                <span class="category-product-image-empty">الصورة غير متاحة</span>
                            @endif
                        </a>

                        <div class="category-product-info">
                            <h2>{{ $product->name }}</h2>
                            <p class="category-product-price">{{ number_format($product->price, 2) }} جنيه</p>
                            <a class="category-product-action" href="{{ url('/products/' . $product->id) }}">
                                تفاصيل المنتج
                            </a>
                        </div>
                    </li>
                @endforeach
            </ul>
        @else
            <div class="category-products-empty">
                <h2>لا توجد منتجات في هذا القسم حاليًا</h2>
                <a href="{{ url('/products') }}">استعرض كل المنتجات</a>
            </div>
        @endif
    </section>
@endsection
