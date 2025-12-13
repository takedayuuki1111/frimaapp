<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Coachtechフリマ</title>
    <link rel="stylesheet" href="https://unpkg.com/ress/dist/ress.min.css">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body>
    <header class="header">
        <div class="header__inner">
            <h1 class="header__logo">
                 <h1 class="header__logo">
                    <a href="/">COACHTECH</a>
                 </h1>
            
            <form action="/" method="get" class="header__search">
                @csrf
                <input type="text" name="keyword" value="{{ request('keyword') }}" placeholder="なにをお探しですか？" class="header__search-input">
            </form>

            <nav class="header__nav">
                <ul class="header__nav-list">
                    @if (Auth::check())

                        <li class="header__nav-item">
                            <form action="/logout" method="post">
                                @csrf
                                <button type="submit" class="header__nav-btn btn-logout">ログアウト</button>
                            </form>
                        </li>
                        <li class="header__nav-item"><a href="/mypage" class="header__nav-link">マイページ</a></li>
                        <li class="header__nav-item"><a href="/sell" class="header__nav-btn btn-sell">出品</a></li>
                    @else

                        <li class="header__nav-item"><a href="/login" class="header__nav-link">ログイン</a></li>
                        <li class="header__nav-item"><a href="/register" class="header__nav-link">会員登録</a></li>
                        <li class="header__nav-item"><a href="/sell" class="header__nav-btn btn-sell">出品</a></li>
                    @endif
                </ul>
            </nav>
        </div>
    </header>

    <main class="main">
        @yield('content')
    </main>

</body>
</html>