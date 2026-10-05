@extends('layout')

@section('title', 'تعديل القسم')

@section('content')
    <section class="category-edit-page">
        <h1>تعديل القسم</h1>
        <p class="category-edit-current-name">القسم الحالي: {{ $category->name }}</p>

        <form action="{{ url('/categories/' . $category->id . '/update') }}" method="POST" class="category-edit-form">
            @csrf
            <label for="category-name">اسم القسم</label>
            <input id="category-name" type="text" name="name" value="{{ old('name', $category->name) }}" required maxlength="255">
            @error('name')
                <small class="seller-field-error">{{ $message }}</small>
            @enderror

            <div class="category-edit-actions">
                <a href="{{ url('/categories') }}">إلغاء</a>
                <button type="submit">حفظ التعديل</button>
            </div>
        </form>
    </section>
@endsection
