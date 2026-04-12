@extends('layouts.app')

@section('content')
<div class="item-detail-page">
    <div class="item-detail-container">
        
       <div class="item-detail-img-box">
            @if (str_starts_with($item->img_url, 'http'))
                <img src="{{ $item->img_url }}" alt="{{ $item->name }}" class="item-detail-img">
            @else
                <img src="{{ asset('storage/' . $item->img_url) }}" alt="{{ $item->name }}" class="item-detail-img">
            @endif
            
            @if ($item->isSold())
                <div class="sold-badge-lg">SOLD</div>
            @endif
        </div>

        <div class="item-detail-info-box">
            <h2 class="item-detail-name">{{ $item->name }}</h2>
            <p class="item-detail-brand">{{ $item->brand_name ?? 'ブランドなし' }}</p>
            <p class="item-detail-price">¥{{ number_format($item->price) }} <span class="tax-in">(税込)</span></p>

            <div class="item-detail-actions">
                <div class="action-item">
                    <form action="{{ route('item.like', $item->id) }}" method="post" class="like-form">
                        @csrf
                        @if (Auth::check() && Auth::user()->likes->contains('item_id', $item->id))
                            @method('DELETE')
                            <button type="submit" class="btn-icon liked">★</button>
                        @else
                            <button type="submit" class="btn-icon">☆</button>
                        @endif
                    </form>
                    <span class="action-count">{{ $item->likes->count() }}</span>
                </div>
                <div class="action-item">
                    <span class="icon-comment">💬</span>
                    <span class="action-count">{{ $item->comments->count() }}</span>
                </div>
            </div>

            @if ($item->isSold())
                @if (!empty($canOpenTrade))
                    <a href="{{ route('trade.show', $trade) }}" class="btn-purchase">取引画面を開く</a>
                @else
                    <button class="btn-purchase disabled" disabled>売り切れ</button>
                @endif
            @else
                <a href="{{ route('purchase.create', $item->id) }}" class="btn-purchase">購入手続きへ</a>
            @endif

            <div class="item-description-section">
                <h3>商品説明</h3>
                <p class="item-description-text">{{ $item->description }}</p>
            </div>

            <div class="item-info-section">
                <h3>商品の情報</h3>
                <div class="info-row">
                    <span class="info-label">カテゴリー</span>
                    <div class="info-tags">
                        @foreach ($categories as $category)
                            <span class="category-tag">{{ $category->content }}</span>
                        @endforeach
                    </div>
                </div>
                <div class="info-row">
                    <span class="info-label">商品の状態</span>
                    <span class="info-value">{{ $item->condition->condition }}</span>
                </div>
            </div>

            <div class="comment-section">
                <h3>コメント ({{ $item->comments->count() }})</h3>
                
                <div class="comment-list">
                    @foreach ($item->comments as $comment)
                        <div class="comment-item">
                            <div class="comment-user">
                                <div class="user-avatar">
                                    @if($comment->user->avatar_image)
                                        <img src="{{ asset('storage/' . $comment->user->avatar_image) }}" alt="">
                                    @else
                                        <div class="no-avatar"></div>
                                    @endif
                                </div>
                                <span class="user-name">{{ $comment->user->name }}</span>
                            </div>
                            <p class="comment-text">{{ $comment->comment }}</p>
                        </div>
                    @endforeach
                </div>

                <div class="comment-form-container">
                    <p class="comment-note">商品へのコメント</p>
                    @auth
                        <form action="{{ route('item.comment.store', $item->id) }}" method="post">
                            @csrf
                            <textarea name="comment" class="comment-input" rows="4"></textarea>
                            <button type="submit" class="btn-comment-submit">コメントを送信する</button>
                        </form>
                    @else
                        <p class="login-alert">コメントを投稿するには<a href="/login">ログイン</a>してください。</p>
                    @endauth
                </div>
            </div>

        </div>
    </div>
</div>
@endsection