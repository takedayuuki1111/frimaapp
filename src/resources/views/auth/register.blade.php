@extends('layouts.app')

@section('content')
<div class="auth-page">
    <h2 class="auth-title">会員登録</h2>
    <form action="/register" method="post" class="auth-form">
        @csrf
        
        <div class="auth-form__group">
            <label class="auth-form__label">ユーザー名</label>
            <input type="text" name="name" class="auth-form__input" value="{{ old('name') }}">
            @error('name')
                <p class="error-message">{{ $message }}</p>
            @enderror
        </div>

        <div class="auth-form__group">
            <label class="auth-form__label">メールアドレス</label>
            <input type="email" name="email" class="auth-form__input" value="{{ old('email') }}">
            @error('email')
                <p class="error-message">{{ $message }}</p>
            @enderror
        </div>

        <div class="auth-form__group">
            <label class="auth-form__label">パスワード</label>
            <input type="password" name="password" class="auth-form__input">
            @error('password')
                <p class="error-message">{{ $message }}</p>
            @enderror
        </div>

        <div class="auth-form__group">
            <label class="auth-form__label">確認用パスワード</label>
            <input type="password" name="password_confirmation" class="auth-form__input">
        </div>

        <button type="submit" class="auth-form__btn">会員登録</button>
    </form>

    <a href="/login" class="auth-link">ログインはこちら</a>
</div>
@endsection