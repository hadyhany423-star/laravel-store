@extends('layout')

@section('title', 'قائمة المنتجات')

@section('content')
    <h1>المنتجات المتاحة</h1>

    <ul>
        @foreach($products as $product)
            <li>
                @if($product->image)
                    <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" loading="lazy">
                @endif
                <a href="/products/{{ $product->id }}">{{ $product->name }}</a> - السعر: {{ $product->price }} جنيه

                {{-- زر إضافة إلى السلة --}}
                <form action="/cart/add/{{ $product->id }}" method="POST">
                    @csrf
                    <button type="submit">أضف إلى السلة</button>
                </form>

                {{-- أزرار التحكم تظهر للبائعين فقط --}}
                @if(Auth::check() && Auth::user()->role === 'seller')
                    <a href="/products/{{ $product->id }}/edit">[تعديل]</a>

                    <form action="/products/{{ $product->id }}/delete" method="POST" style="display: inline;">
                        @csrf
                        <button type="submit">[حذف]</button>
                    </form>
                @endif
            </li>
        @endforeach
    </ul>

    @auth
        @if(Auth::user()->role === 'seller')
            <div>
                <a href="/products/create">+ إضافة منتج جديد</a>
            </div>
        @endif
    @endauth
@endsection
