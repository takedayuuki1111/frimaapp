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

        <div class="user-profile-meta">
            <h2 class="user-profile-name">{{ $user->name }}</h2>

            @if (!is_null($averageRating))
                <div class="user-rating-stars" aria-label="user-rating-stars">
                    @php
                        $roundedRating = (int) round($averageRating);
                    @endphp
                    @for ($i = 1; $i <= 5; $i++)
                        <span class="{{ $i <= $roundedRating ? 'filled' : 'empty' }}">★</span>
                    @endfor
                </div>
                <p class="user-rating-summary">評価 {{ number_format($averageRating, 1) }} / 5.0</p>
                <p class="user-rating-count">評価件数 {{ $ratingCount }}件</p>
            @endif
        </div>

        <a href="{{ route('mypage.edit') }}" class="btn-profile-edit">プロフィールを編集</a>
    </div>

    <div class="tab-menu">
        <a href="/mypage?page=sell" class="tab-item {{ $page === 'sell' ? 'active' : '' }}">出品した商品 ({{ $sellCount }})</a>
        <a href="/mypage?page=buy" class="tab-item {{ $page === 'buy' ? 'active' : '' }}">購入した商品 ({{ $buyCount }})</a>
        <a href="/mypage?page=trade" class="tab-item {{ $page === 'trade' ? 'active' : '' }}">取引中の商品 ({{ $tradingCount }})</a>
    </div>

    <div class="item-grid">
        @forelse ($items as $item)
            @php
                $trade = $tradesByItemId->get($item->id);
                $unreadCount = $trade ? ($unreadCountsByItemId->get($item->id) ?? 0) : 0;
                $hasPendingRating = false;

                if ($trade) {
                    $hasPendingRating = $trade->status === 'completed'
                        && (
                            ((int) $trade->user_id === (int) $user->id && is_null($trade->seller_rating))
                            || ((int) $trade->item->user_id === (int) $user->id && is_null($trade->buyer_rating))
                        );
                }

                $showTradeAlert = $trade && ($unreadCount > 0 || $hasPendingRating);
            @endphp
            <div class="item-card">
                <a href="{{ $page === 'trade' && $trade ? route('trade.show', $trade) : route('item.show', ['item_id' => $item->id]) }}" class="item-link">
                    <div class="item-img-wrapper">
                        @if ($page === 'trade' && $showTradeAlert)
                            <span class="trade-card-alert-dot" aria-label="要確認の取引"></span>
                        @endif

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

                @if ($page === 'trade' && $trade)
                    <div class="trade-card-footer">
                        <p class="trade-card-role">{{ (int) $trade->user_id === (int) $user->id ? '購入者として取引中' : '出品者として取引中' }}</p>
                        @if ($unreadCount > 0)
                            <p class="trade-card-notice">新着{{ $unreadCount }}件</p>
                        @endif
                        <a href="{{ route('trade.show', $trade) }}" class="btn-trade-open">取引画面を開く</a>
                    </div>
                @endif
            </div>
        @empty
            <p class="empty-state-message">表示できる商品はありません。</p>
        @endforelse
    </div>
</div>
@endsection