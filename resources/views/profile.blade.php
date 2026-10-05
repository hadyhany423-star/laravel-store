@extends('layout')

@section('title', 'حسابي')

@section('content')
    <h2>حسابي الشخصي</h2>
    <p>أهلاً بك في صفحة الحساب الخاصة بك.</p>

    <!-- البيانات الأساسية -->
    <div>
        <h3>البيانات الأساسية</h3>
        <ul>
            <li>الاسم: {{ $user->name }}</li>
            <li>البريد الإلكتروني: {{ $user->email }}</li>
            <li>العمر: {{ $user->age ?? 'غير متوفر' }}</li>
            <li>الجنس: {{ $user->gender == 'male' ? 'ذكر' : ($user->gender == 'female' ? 'أنثى' : 'غير متوفر') }}</li>
        </ul>
    </div>

    <hr>

    <!-- عناوين التوصيل   -->
    <div>
        <h3>عناوين التوصيل الخاصة بي</h3>
        @if($addresses->count() > 0)
            <ul>
                @foreach($addresses as $addr)
                    <li>{{ $addr->address }} - {{ $addr->city }}</li>
                @endforeach
            </ul>
        @else
            <p>لا توجد عناوين مسجلة حتى الآن.</p>
        @endif

        <br>
        <a href="{{ url('/addresses') }}">إدارة وإضافة عناوين جديدة</a>
    </div>

    <hr>

    <!-- سجل الطلبات  -->
    <div>
        <h3>سجل المشتريات والطلبات</h3>

        @if(isset($orders) && count($orders) > 0)
            <ul>
                @foreach($orders as $order)
                    <li>
                        <strong>رقم الطلب: #{{ $order->id }}</strong><br>
                        الإجمالي: {{ $order->total_price }} جنيه<br>
                        الحالة: {{ $order->status }}<br>
                        عنوان التوصيل: {{ $order->address }}<br>
                        تاريخ الطلب: {{ $order->created_at }}<br>

                        <strong>المنتجات المطلوبة:</strong>
                        <ul>
                            @foreach($order->items as $item)
                                <li>
                                    {{ $item->product->name ?? 'منتج محذوف' }} -
                                    الكمية: {{ $item->quantity }} -
                                    السعر: {{ $item->price }} جنيه
                                </li>
                            @endforeach
                        </ul>

                        <!-- زر إلغاء الطلب -->
                        @if($order->status === 'قيد المعالجة' || $order->status === 'pending')
                            <form action="/orders/{{ $order->id }}/cancel" method="POST" style="margin-top: 10px;">
                                @csrf
                                <button type="submit">إلغاء الطلب</button>
                            </form>
                        @endif
                    </li>
                    <hr>
                @endforeach
            </ul>
        @else
            <p>ليس لديك أي طلبات سابقة حتى الآن.</p>
        @endif
    </div>

    <br>
    <a href="/products">الرجوع إلى قائمة المنتجات</a>
@endsection
