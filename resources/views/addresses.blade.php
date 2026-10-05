@extends('layout')

@section('title', 'عناويني')

@section('content')
    <h2>إدارة العناوين</h2>

    @if(session('success'))
        <p>{{ session('success') }}</p>
    @endif

    <!-- فورم إضافة عنوان جديد -->
    <div>
        <h3>إضافة عنوان جديد</h3>
        <form action="{{ url('/addresses') }}" method="POST">
            @csrf
            <div>
                <label>العنوان بالتفصيل:</label><br>
                <input type="text" name="address" required>
            </div>
            <div>
                <label>المدينة / المحافظة:</label><br>
                <input type="text" name="city" required>
            </div>
            <br>
            <button type="submit">حفظ العنوان</button>
        </form>
    </div>

    <hr>

    <!-- جدول عرض العناوين الحالية -->
    <h3>عناوينك المسجلة</h3>
    <table border="1">
        <thead>
            <tr>
                <th>العنوان</th>
                <th>المدينة</th>
                <th>الإجراءات</th>
            </tr>
        </thead>
        <tbody>
            @forelse($addresses as $addr)
                <tr>
                    <td>{{ $addr->address }}</td>
                    <td>{{ $addr->city }}</td>
                    <td>
                        <form action="{{ url('/addresses/' . $addr->id . '/delete') }}" method="POST">
                            @csrf
                            <button type="submit">حذف</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="3">لا توجد عناوين مسجلة حتى الآن.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
@endsection
