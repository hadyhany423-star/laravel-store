@extends('layout')

@section('title', 'سلة التسوق')

@section('content')
<div class="cart-container">
    <h1>سلة التسوق الخاصة بك</h1>

    @if(session('success'))
        <p class="alert-success">{{ session('success') }}</p>
    @endif

    @if(session('cart') && count(session('cart')) > 0)
        @php $total = 0; @endphp

        <div class="cart-items-list">
            @foreach(session('cart') as $id => $details)
                @php
                    $subtotal = $details['price'] * $details['quantity'];
                    $total += $subtotal;
                @endphp

                <div class="cart-item-card">
                    @if(isset($details['image']))
                        <div class="cart-item-img">
                            <img src="{{ asset('storage/' . $details['image']) }}" alt="{{ $details['name'] }}">
                        </div>
                    @endif

                    <div class="cart-item-info">
                        <h3>{{ $details['name'] }}</h3>
                        <p class="item-price">السعر: جنيه {{ $details['price'] }}</p>

                        <div class="cart-item-actions">
                            <!-- فورم تحديث الكمية -->
                            <form action="/cart/update/{{ $id }}" method="POST" class="update-form">
                                @csrf
                                <label>الكمية:</label>
                                <input type="number" name="quantity" value="{{ $details['quantity'] }}" min="1">
                                <button type="submit" class="btn-update">تحديث</button>
                            </form>

                            <!-- زر الحذف -->
                            <form action="/cart/remove/{{ $id }}" method="POST" style="margin: 0; padding: 0; background: transparent; box-shadow: none; width: auto;">
                                @csrf
                                <button type="submit" class="btn-danger">حذف</button>
                            </form>
                        </div>

                        <p class="subtotal">الإجمالي الفرعي: <span>جنيه {{ $subtotal }}</span></p>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="cart-summary-box">
            <h3>الإجمالي الكلي: جنيه {{ $total }}</h3>

            <form action="{{ route('checkout.store') }}" method="POST" class="checkout-form">
                @csrf
                <div style="margin-bottom: 15px; text-align: right;">
                    <label>عنوان التوصيل:</label>
                    <input type="text" name="address" required placeholder="اكتب عنوانك بالتفصيل هنا">
                </div>

                <button type="submit" class="btn-checkout">تأكيد وإتمام الشراء</button>
            </form>

            <!-- زر إفراغ السلة -->
            <form action="/cart/clear" method="POST" class="clear-form">
                @csrf
                <button type="submit" class="btn-clear">إفراغ السلة بالكامل</button>
            </form>
        </div>

    @else
        <div class="empty-cart">
            <p>السلة فارغة حالياً.</p>
        </div>
    @endif

    <div class="back-link">
        <a href="/products">الرجوع إلى قائمة المنتجات</a>
    </div>
</div>
@endsection
