@extends('layouts.app')

@section('content')
<div class="sell-page">
    <h2 class="page-title">商品の出品</h2>
    
    <form action="{{ route('sell.store') }}" method="post" enctype="multipart/form-data" class="sell-form">
        @csrf

        <div class="form-section">
            <h3 class="section-title">商品画像</h3>
            <div class="image-upload-area">
                <label for="item_image" class="upload-label">
                    画像を選択する
                    <input type="file" name="item_image" id="item_image" class="file-input" accept="image/jpeg, image/png">
                </label>
                <div id="preview-box" class="preview-box"></div>
            </div>
            @error('item_image')
                <p class="error-message">{{ $message }}</p>
            @enderror
        </div>

        <h3 class="section-title">商品の詳細</h3>

        <div class="form-group">
            <label class="form-label">カテゴリー</label>
            <div class="category-checkboxes">
                @foreach ($categories as $category)
                    <label class="category-check">
                        <input type="checkbox" name="categories[]" value="{{ $category->id }}"
                            {{ is_array(old('categories')) && in_array($category->id, old('categories')) ? 'checked' : '' }}>
                        <span>{{ $category->content }}</span>
                    </label>
                @endforeach
            </div>
            @error('categories')
                <p class="error-message">{{ $message }}</p>
            @enderror
        </div>

        <div class="form-group">
            <label class="form-label">商品の状態</label>
            <select name="condition_id" class="form-select">
                <option value="" selected hidden>選択してください</option>
                @foreach ($conditions as $condition)
                    <option value="{{ $condition->id }}" {{ old('condition_id') == $condition->id ? 'selected' : '' }}>
                        {{ $condition->condition }}
                    </option>
                @endforeach
            </select>
            @error('condition_id')
                <p class="error-message">{{ $message }}</p>
            @enderror
        </div>

        <h3 class="section-title">商品名と説明</h3>

        <div class="form-group">
            <label class="form-label">商品名</label>
            <input type="text" name="name" class="form-input" value="{{ old('name') }}">
            @error('name')
                <p class="error-message">{{ $message }}</p>
            @enderror
        </div>
        
        <div class="form-group">
            <label class="form-label">ブランド名（任意）</label>
            <input type="text" name="brand_name" class="form-input" value="{{ old('brand_name') }}">
        </div>

        <div class="form-group">
            <label class="form-label">商品の説明</label>
            <textarea name="description" class="form-textarea" rows="5">{{ old('description') }}</textarea>
            @error('description')
                <p class="error-message">{{ $message }}</p>
            @enderror
        </div>

        <h3 class="section-title">販売価格</h3>

        <div class="form-group">
            <label class="form-label">販売価格</label>
            <div class="price-input-wrap">
                <span class="currency">¥</span>
                <input type="number" name="price" class="form-input price-input" value="{{ old('price') }}">
            </div>
            @error('price')
                <p class="error-message">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit" class="btn-sell-submit">出品する</button>

    </form>
</div>

<script>
    document.getElementById('item_image').addEventListener('change', function(e) {
        const file = e.target.files[0];
        const previewBox = document.getElementById('preview-box');
        previewBox.innerHTML = '';
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const img = document.createElement('img');
                img.src = e.target.result;
                img.classList.add('preview-img');
                previewBox.appendChild(img);
            }
            reader.readAsDataURL(file);
        }
    });
</script>
@endsection