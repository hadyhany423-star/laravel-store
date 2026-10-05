@extends('layout')

@section('title', 'إدارة الأقسام')

@section('content')
    <h2>إدارة الأقسام</h2>

    @if(session('success'))
        <p>{{ session('success') }}</p>
    @endif

    <!-- فورم إضافة قسم جديد -->
    <div>
        <h3>إضافة قسم جديد</h3>
        <form action="{{ url('/categories') }}" method="POST">
            @csrf
            <div>
                <label>اسم القسم:</label><br>
                <input type="text" name="name" required>
            </div>
            <button type="submit">حفظ القسم</button>
        </form>
    </div>

    <hr>

    <!-- جدول عرض الأقسام والحذف -->
    <h3>الأقسام الحالية</h3>
    <table border="1">
        <thead>
            <tr>
                <th>اسم القسم</th>
                <th>الرابط</th>
                <th>الإجراءات</th>
            </tr>
        </thead>
        <tbody>
            @forelse($categories as $category)
                <tr>
                    <td>{{ $category->name }}</td>
                    <td>{{ $category->slug }}</td>
                    <td>
                        <div class="category-actions">
                            <a class="category-edit-button" href="{{ url('/categories/' . $category->id . '/edit') }}">تعديل</a>
                            <form action="{{ url('/categories/' . $category->id . '/delete') }}" method="POST">
                                @csrf
                                <button class="btn-danger" type="submit">حذف</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="3">لا توجد أقسام مضافة حتى الآن.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
@endsection
