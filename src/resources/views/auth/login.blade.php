@extends('layouts.app')

@section('content')
<div class="auth-page">
    <h2 class="auth-title">ログイン</h2>
    <form action="/login" method="post" class="auth-form">
        @csrf
        
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

        <button type="submit" class="auth-form__btn">ログイン</button>
    </form>

    <a href="/register" class="auth-link">会員登録はこちら</a>
</div>
@endsection