@extends('layouts.app')

@section('content')
<div class="auth-page">
    <h2 class="auth-title">メール認証</h2>
    
    <div class="auth-form__group">
        <p>登録していただいたメールアドレスに認証リンクを送信しました。<br>
        メールを確認し、リンクをクリックして登録を完了してください。</p>
        
        <p>※メールが届かない場合は、以下のボタンから再送信してください。</p>
    </div>

    {{-- 認証メール再送信フォーム --}}
    <form method="POST" action="{{ route('verification.send') }}" class="auth-form">
        @csrf
        <button type="submit" class="auth-form__btn">認証メールを再送信する</button>
    </form>

    {{-- メッセージ表示（再送信完了時など） --}}
    @if (session('status') == 'verification-link-sent')
        <p class="success-message" style="color: green; margin-top: 10px;">
            新しい認証リンクを送信しました！
        </p>
    @endif
    
    {{-- ログアウト（認証せず抜ける場合） --}}
    {{-- 注意: まだ layouts/app.blade.php のログアウトが汎用化されていない場合は個別に書く --}}
    <div style="margin-top: 30px;">
        <a href="/logout" onclick="event.preventDefault(); document.getElementById('logout-form-verify').submit();" class="auth-link">
            ログアウトはこちら
        </a>
        <form id="logout-form-verify" action="/logout" method="POST" style="display: none;">
            @csrf
        </form>
    </div>
</div>
@endsection