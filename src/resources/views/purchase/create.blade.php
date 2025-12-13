@extends('layouts.app')

@section('content')
<div class="purchase-page">
    <form action="{{ route('purchase.store', $item->id) }}" method="post" class="purchase-container">
        @csrf
        <input type="hidden" name="item_id" value="{{ $item->id }}">

        <div class="purchase-left">
            <div class="purchase-item-info">
                <div class="purchase-item-img-box">
                    @if (str_starts_with($item->img_url, 'http'))
                        <img src="{{ $item->img_url }}" alt="{{ $item->name }}">
                    @else
                        <img src="{{ asset('storage/' . $item->img_url) }}" alt="{{ $item->name }}">
                    @endif
                </div>
                <div class="purchase-item-detail">
                    <h2 class="purchase-item-name">{{ $item->name }}</h2>
                    <p class="purchase-item-price">¥{{ number_format($item->price) }}</p>
                </div>
            </div>

            <div class="divider"></div>

            <div class="purchase-section">
                <h3>支払い方法</h3>
                <div class="select-box">
                    <select name="payment_method" class="payment-select">
                        <option value="" selected hidden>選択してください</option>
                        <option value="konbini">コンビニ払い</option>
                        <option value="card">カード支払い</option>
                    </select>
                </div>
                @error('payment_method')
                    <p class="error-message">{{ $message }}</p>
                @enderror
            </div>

            <div class="divider"></div>

            <div class="purchase-section">
                <div class="section-header">
                    <h3>配送先</h3>
                    <a href="{{ route('purchase.address.edit', $item->id) }}" class="change-link">変更する</a>
                </div>
                <div class="address-info">
                    <p>〒 {{ substr($user->postal_code, 0, 3) }}-{{ substr($user->postal_code, 3) }}</p>
                    <p>{{ $user->address }} {{ $user->building_name }}</p>
                </div>
            </div>
        </div>

        <div class="purchase-right">
            <div class="summary-box">
                <div class="summary-row">
                    <span>商品代金</span>
                    <span>¥{{ number_format($item->price) }}</span>
                </div>
                <div class="summary-row total">
                    <span>支払い金額</span>
                    <span>¥{{ number_format($item->price) }}</span>
                </div>
                <button type="submit" class="btn-purchase-submit">購入する</button>
            </div>
        </div>

    </form>
</div>
@endsection