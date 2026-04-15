@extends('layouts.app')

@section('content')
<div class="trade-page">
    <div class="trade-layout">
        <aside class="trade-sidebar">
            <p class="trade-sidebar-label">その他の取引</p>
            <a href="{{ route('mypage.index', ['page' => 'trade']) }}" class="trade-sidebar-link">取引中の商品一覧へ</a>

            <div class="trade-sidebar-list">
                @forelse ($otherTrades as $otherTrade)
                    <a href="{{ route('trade.show', $otherTrade) }}" class="trade-sidebar-link">
                        {{ $otherTrade->item->name }}
                        @if ($otherTrade->unreadCountForUser($currentUser->id) > 0)
                            <span class="trade-sidebar-notice">新着{{ $otherTrade->unreadCountForUser($currentUser->id) }}件</span>
                        @endif
                    </a>
                @empty
                    <p class="trade-empty-message">他の取引はありません。</p>
                @endforelse
            </div>
        </aside>

        <section class="trade-main">
            @php
                $shouldShowRatingModal = $soldItem->status === 'completed' && !$hasRated && !$bothRated;
            @endphp

            <div class="trade-header">
                <h2 class="trade-title">「{{ $partner->name }}」さんとの取引画面</h2>
                @if ($soldItem->status !== 'completed')
                    <form action="{{ route('trade.complete', $soldItem) }}" method="post">
                        @csrf
                        <button type="submit" class="trade-complete-button">取引を完了する</button>
                    </form>
                @else
                    <span class="trade-status-badge is-completed">取引完了</span>
                @endif
            </div>

            @if (session('status'))
                <div class="trade-flash-message">{{ session('status') }}</div>
            @endif

            <div class="trade-item-card">
                <div class="trade-item-image">
                    @if (str_starts_with($soldItem->item->img_url, 'http'))
                        <img src="{{ $soldItem->item->img_url }}" alt="{{ $soldItem->item->name }}">
                    @else
                        <img src="{{ asset('storage/' . $soldItem->item->img_url) }}" alt="{{ $soldItem->item->name }}">
                    @endif
                </div>
                <div>
                    <p class="trade-item-name">{{ $soldItem->item->name }}</p>
                    <p class="trade-item-price">¥{{ number_format($soldItem->item->price) }}</p>
                </div>
            </div>

            <div class="trade-messages">
                @forelse ($soldItem->messages as $message)
                    <div class="trade-message {{ $message->user_id === $currentUser->id ? 'is-mine' : '' }}">
                        <p class="trade-message-user">{{ $message->user->name }}</p>

                        @if ($editingMessageId === $message->id && $message->user_id === $currentUser->id)
                            <form action="{{ route('trade.message.update', [$soldItem, $message]) }}" method="post" enctype="multipart/form-data" class="trade-message-form trade-message-form-inline">
                                @csrf
                                @method('PATCH')
                                <input type="text" name="message" class="trade-message-input" value="{{ old('message', $message->message) }}">
                                <div class="trade-file-picker">
                                    <label class="trade-file-button">
                                        画像を追加
                                        <input type="file" name="image" class="trade-message-image-input" data-file-input>
                                    </label>
                                    <span class="trade-file-name" data-file-name>ファイル未選択</span>
                                </div>
                                <button type="submit" class="trade-send-button">更新する</button>
                            </form>
                        @else
                            <p class="trade-message-body">{{ $message->message }}</p>
                            @if ($message->image_path)
                                <p><img src="{{ asset('storage/' . $message->image_path) }}" alt="取引メッセージ画像" class="trade-message-image-preview"></p>
                            @endif
                        @endif

                        @if ($message->user_id === $currentUser->id)
                            <div class="trade-message-actions">
                                <a href="{{ route('trade.show', ['soldItem' => $soldItem, 'edit' => $message->id]) }}" class="btn-trade-edit">編集</a>
                                <form action="{{ route('trade.message.destroy', [$soldItem, $message]) }}" method="post">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="trade-send-button trade-delete-button">削除</button>
                                </form>
                            </div>
                        @endif
                    </div>
                @empty
                    <p class="trade-empty-message">まだメッセージはありません。</p>
                @endforelse

                @if ($soldItem->status === 'completed')
                    @if ($bothRated)
                        <div class="trade-inline-rating">
                            <p class="trade-rating-done">双方の評価が完了しました。ありがとうございました。</p>
                        </div>
                    @elseif ($hasRated)
                        <div class="trade-inline-rating">
                            <p class="trade-rating-done">評価を送信済みです。相手の評価をお待ちください。</p>
                        </div>
                    @else
                        {{-- 評価モーダル --}}
                        <div class="trade-rating-modal {{ $shouldShowRatingModal || $errors->has('score') ? 'is-open' : '' }}" data-rating-modal aria-hidden="{{ $shouldShowRatingModal || $errors->has('score') ? 'false' : 'true' }}">
                            <div class="trade-rating-dialog" role="dialog" aria-modal="true" aria-labelledby="trade-rating-modal-title">
                                <p class="trade-rating-window-label">取引完了</p>
                                <h3 class="trade-rating-window-title" id="trade-rating-modal-title">「{{ $partner->name }}」さんはどうでしたか？</h3>

                                <form action="{{ route('trade.rate', $soldItem) }}" method="post" class="trade-rating-form">
                                    @csrf
                                    <input type="hidden" name="score" value="{{ old('score') }}" data-rating-input>

                                    <div class="trade-rating-stars-row" data-rating-stars>
                                        @for ($score = 1; $score <= 5; $score++)
                                            <button
                                                type="button"
                                                class="trade-rating-star {{ old('score') && (int) old('score') >= $score ? 'is-active' : '' }}"
                                                data-score="{{ $score }}"
                                                aria-label="{{ $score }}つ星を選択"
                                            >
                                                <span class="trade-rating-star-icon">★</span>
                                            </button>
                                        @endfor
                                    </div>

                                    @error('score')
                                        <p class="error-message">{{ $message }}</p>
                                    @enderror

                                    <button type="submit" class="trade-rate-submit">送信する</button>
                                </form>
                            </div>
                        </div>
                    @endif
                @endif
            </div>

            @if ($errors->has('message') || $errors->has('image'))
                <div class="trade-form-errors">
                    @error('message')
                        <p class="error-message">{{ $message }}</p>
                    @enderror
                    @error('image')
                        <p class="error-message">{{ $message }}</p>
                    @enderror
                </div>
            @endif

            <form action="{{ route('trade.message.store', $soldItem) }}" method="post" enctype="multipart/form-data" class="trade-message-form">
                @csrf
                <input
                    type="text"
                    id="trade-message-input"
                    name="message"
                    class="trade-message-input"
                    placeholder="取引メッセージを入力してください"
                    value="{{ old('message') }}"
                    data-trade-draft-input
                    data-draft-key="trade-message-draft-{{ $soldItem->id }}"
                >
                <div class="trade-file-picker">
                    <label class="trade-file-button">
                        画像を追加
                        <input type="file" name="image" class="trade-message-image-input" data-file-input>
                    </label>
                    <span class="trade-file-name" data-file-name>ファイル未選択</span>
                </div>
                <button type="submit" class="trade-send-button trade-send-icon-button" aria-label="送信">↑</button>
            </form>

        </section>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const input = document.querySelector('[data-trade-draft-input]');

        if (input) {
            const storageKey = input.dataset.draftKey;
            const shouldClear = @json(session('status') === 'メッセージを送信しました。');

            if (shouldClear) {
                localStorage.removeItem(storageKey);
            } else if (!input.value) {
                const savedMessage = localStorage.getItem(storageKey);
                if (savedMessage) {
                    input.value = savedMessage;
                }
            }

            input.addEventListener('input', function () {
                localStorage.setItem(storageKey, input.value);
            });
        }

        document.querySelectorAll('[data-file-input]').forEach(function (fileInput) {
            const picker = fileInput.closest('.trade-file-picker');
            const fileName = picker ? picker.querySelector('[data-file-name]') : null;

            fileInput.addEventListener('change', function () {
                if (!fileName) {
                    return;
                }

                fileName.textContent = this.files && this.files[0] ? this.files[0].name : 'ファイル未選択';
            });
        });

        const ratingInput = document.querySelector('[data-rating-input]');
        const ratingStars = document.querySelectorAll('.trade-rating-star');
        const selectedText = document.querySelector('[data-rating-selected-text]');
        const modal = document.querySelector('[data-rating-modal]');
        const shouldOpenModal = @json($shouldShowRatingModal || $errors->has('score'));

        function openRatingModal() {
            if (!modal) return;
            modal.classList.add('is-open');
            modal.setAttribute('aria-hidden', 'false');
        }

        function updateSelectedStars(score) {
            ratingStars.forEach(function (star) {
                const starScore = Number(star.dataset.score);
                star.classList.toggle('is-active', starScore <= score);
            });

            if (selectedText) {
                selectedText.textContent = score ? score + ' / 5 を選択中' : '星を押して評価を選択してください';
            }
        }

        ratingStars.forEach(function (star) {
            star.addEventListener('click', function () {
                const score = Number(this.dataset.score);

                if (ratingInput) {
                    ratingInput.value = score;
                }

                updateSelectedStars(score);
            });
        });

        if (ratingInput && ratingInput.value) {
            updateSelectedStars(Number(ratingInput.value));
        }

        if (shouldOpenModal) {
            openRatingModal();
        }
    });
</script>
@endsection
