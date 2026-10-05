@extends('layout')

@section('title', 'قائمة الأقسام')

@section('content')
    <h1>الأقسام المتاحة</h1>

    @auth
        @if(Auth::user()->role === 'seller')
            <p><a href="{{ url('/categories/create') }}">+ إضافة قسم جديد</a></p>
        @endif
    @endauth

    <ul>
        @foreach($categories as $category)
            <li>
                <a href="{{ url('/categories/' . $category->id) }}">{{ $category->name }}</a>

                @auth
                    @if(Auth::user()->role === 'seller')
                        <div class="category-actions">
                            <a class="category-edit-button" href="{{ url('/categories/' . $category->id . '/edit') }}">تعديل</a>
                            <form action="{{ url('/categories/' . $category->id . '/delete') }}" method="POST">
                                @csrf
                                <button class="btn-danger" type="submit">حذف</button>
                            </form>
                        </div>
                    @endif
                @endauth
            </li>
        @endforeach
    </ul>
@endsection
