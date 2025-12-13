@extends('layouts.app')

@section('content')
<div class="profile-edit-page">
    <h2 class="page-title">プロフィール設定</h2>

    <form action="{{ route('mypage.update') }}" method="post" enctype="multipart/form-data" class="profile-form">
        @csrf

        {{-- アイコン画像 --}}
        <div class="profile-icon-section">
            <div class="icon-preview" id="icon-preview">
                @if($user->avatar_image)
                    <img src="{{ asset('storage/' . $user->avatar_image) }}" alt="" class="current-icon">
                @else
                    <div class="no-avatar-lg"></div>
                @endif
            </div>
            <label for="avatar_image" class="btn-select-image">
                画像を選択する
                <input type="file" name="avatar_image" id="avatar_image" class="file-input" accept="image/jpeg, image/png">
            </label>
        </div>
        @error('avatar_image')
            <p class="error-message">{{ $message }}</p>
        @enderror

        {{-- ユーザー名 --}}
        <div class="form-group">
            <label class="form-label">ユーザー名</label>
            <input type="text" name="name" class="form-input" value="{{ old('name', $user->name) }}">
            @error('name')
                <p class="error-message">{{ $message }}</p>
            @enderror
        </div>

        {{-- 郵便番号 --}}
        <div class="form-group">
            <label class="form-label">郵便番号</label>
            <input type="text" name="postal_code" class="form-input" value="{{ old('postal_code', $user->postal_code) }}">
            @error('postal_code')
                <p class="error-message">{{ $message }}</p>
            @enderror
        </div>

        {{-- 住所 --}}
        <div class="form-group">
            <label class="form-label">住所</label>
            <input type="text" name="address" class="form-input" value="{{ old('address', $user->address) }}">
            @error('address')
                <p class="error-message">{{ $message }}</p>
            @enderror
        </div>

        {{-- 建物名 --}}
        <div class="form-group">
            <label class="form-label">建物名</label>
            <input type="text" name="building_name" class="form-input" value="{{ old('building_name', $user->building_name) }}">
        </div>

        <button type="submit" class="btn-profile-submit">更新する</button>
    </form>
</div>

<script>
    // アイコンプレビュー
    document.getElementById('avatar_image').addEventListener('change', function(e) {
        const file = e.target.files[0];
        const previewBox = document.getElementById('icon-preview');
        previewBox.innerHTML = '';
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const img = document.createElement('img');
                img.src = e.target.result;
                img.classList.add('current-icon');
                previewBox.appendChild(img);
            }
            reader.readAsDataURL(file);
        }
    });
</script>
@endsection