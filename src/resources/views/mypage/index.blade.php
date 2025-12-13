@extends('layouts.app')

@section('content')
<div class="mypage-container">

    <div class="user-profile-header">
        <div class="user-profile-icon">
            @if($user->avatar_image)
                <img src="{{ asset('storage/' . $user->avatar_image) }}" alt="{{ $user->name }}">
            @else
                <div class="no-avatar-lg"></div>
            @endif
        </div>
        <h2 class="user-profile-name">{{ $user->name }}</h2>
        <a href="{{ route('mypage.edit') }}" class="btn-profile-edit">プロフィールを編集</a>
    </div>

    <div class="tab-menu">
        <a href="/mypage?page=sell" class="tab-item {{ $page === 'sell' ? 'active' : '' }}">出品した商品</a>
        <a href="/mypage?page=buy" class="tab-item {{ $page === 'buy' ? 'active' : '' }}">購入した商品</a>
    </div>

    <div class="item-grid">
        @foreach ($items as $item)
            <div class="item-card">
                <a href="{{ route('item.show', ['item_id' => $item->id]) }}" class="item-link">
                    <div class="item-img-wrapper">
                        @if (str_starts_with($item->img_url, 'http'))
                            <img src="{{ $item->img_url }}" alt="{{ $item->name }}" class="item-img">
                        @else
                            <img src="{{ asset('storage/' . $item->img_url) }}" alt="{{ $item->name }}" class="item-img">
                        @endif

                        @if ($item->isSold())
                            <div class="sold-badge">SOLD</div>
                        @endif
                    </div>
                    <div class="item-info">
                        <p class="item-name">{{ $item->name }}</p>
                        <p class="item-price">¥{{ number_format($item->price) }}</p>
                    </div>
                </a>
            </div>
        @endforeach
    </div>
</div>
@endsection