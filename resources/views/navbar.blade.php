<nav>
    <a href="{{ url('/') }}">الرئيسية</a> |

    @auth
        @if(Auth::user()->role === 'seller')
            <a href="{{ url('/categories/create') }}">إدارة الأقسام</a> |
        @else
            <a href="{{ url('/categories') }}">الأقسام</a> |
        @endif
    @else
        <a href="{{ url('/categories') }}">الأقسام</a> |
    @endauth

    {{-- حساب وإجمالي عدد المنتجات في السلة --}}
    @php
        $cartCount = 0;
        if(session('cart')) {
            foreach(session('cart') as $details) {
                $cartCount += $details['quantity'];
            }
        }
    @endphp

    <a href="{{ route('cart.index') }}">
        السلة <span id="cart-count" class="badge" style="background: red; color: white; padding: 2px 6px; border-radius: 50%;">{{ $cartCount }}</span>
    </a> |

    @auth
        <a href="{{ url('/addresses') }}">عناويني</a> |
        <a href="{{ url('/support') }}">الدعم</a> |
        <a href="{{ url('/profile') }}">حسابي</a> |
        <form action="{{ url('/logout') }}" method="POST" style="display: inline;">
            @csrf
            <button type="submit">تسجيل خروج</button>
        </form>
    @else
        <a href="{{ url('/support') }}">الدعم</a> |
        <a href="{{ url('/login') }}">تسجيل دخول</a> |
        <a href="{{ url('/register') }}">حساب جديد</a>
    @endauth
</nav>
<hr>
