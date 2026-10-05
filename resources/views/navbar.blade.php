<nav class="site-nav" aria-label="التنقل الرئيسي">
    <a class="site-brand" href="{{ url('/') }}" aria-label="Epic Store - الصفحة الرئيسية">
        <img src="{{ asset('images/epic-store-logo.svg') }}" alt="Epic Store">
    </a>

    <div class="nav-links">
        <a href="{{ url('/') }}">الرئيسية</a>

        @auth
            @if(Auth::user()->role === 'seller')
                <a href="{{ url('/categories/create') }}">إدارة الأقسام</a>
            @else
                <a href="{{ url('/categories') }}">الأقسام</a>
            @endif
        @else
            <a href="{{ url('/categories') }}">الأقسام</a>
        @endauth

        @php
            $cartCount = 0;
            if (session('cart')) {
                foreach (session('cart') as $details) {
                    $cartCount += $details['quantity'];
                }
            }
        @endphp

        <a href="{{ route('cart.index') }}">
            السلة <span id="cart-count" class="badge">{{ $cartCount }}</span>
        </a>

        @auth
            <a href="{{ url('/addresses') }}">عناويني</a>
            <a href="{{ url('/support') }}">الدعم</a>
            <a href="{{ url('/profile') }}">حسابي</a>
            <form action="{{ url('/logout') }}" method="POST">
                @csrf
                <button type="submit">تسجيل خروج</button>
            </form>
        @else
            <a href="{{ url('/support') }}">الدعم</a>
            <a href="{{ url('/login') }}">تسجيل دخول</a>
            <a href="{{ url('/register') }}">حساب جديد</a>
        @endauth
    </div>
</nav>
