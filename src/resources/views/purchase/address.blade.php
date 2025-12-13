@extends('layouts.app')

@section('content')
<div class="address-page">
    <h2 class="page-title">住所の変更</h2>

    <form action="{{ route('purchase.address.update', $item_id) }}" method="post" class="address-form">
        @csrf

        <div class="form-group">
            <label class="form-label">郵便番号</label>
            <input type="text" name="postal_code" class="form-input" value="{{ old('postal_code', Auth::user()->postal_code) }}">
            @error('postal_code')
                <p class="error-message">{{ $message }}</p>
            @enderror
        </div>

        <div class="form-group">
            <label class="form-label">住所</label>
            <input type="text" name="address" class="form-input" value="{{ old('address', Auth::user()->address) }}">
            @error('address')
                <p class="error-message">{{ $message }}</p>
            @enderror
        </div>

        <div class="form-group">
            <label class="form-label">建物名</label>
            <input type="text" name="building_name" class="form-input" value="{{ old('building_name', Auth::user()->building_name) }}">
        </div>

        <button type="submit" class="btn-update-address">更新する</button>
    </form>
</div>
@endsection