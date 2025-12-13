@extends('layouts.app')

@section('content')
<div class="item-list-page">

    <div class="tab-menu">
        <a href="/?tab=recommend" class="tab-item {{ $tab === 'recommend' ? 'active' : '' }}">おすすめ</a>
        <a href="/?tab=mylist" class="tab-item {{ $tab === 'mylist' ? 'active' : '' }}">マイリスト</a>
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