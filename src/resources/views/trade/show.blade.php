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
            <div class="trade-header">
                <div>
                    <p class="trade-partner-label">取引チャット</p>
                    <h2 class="trade-title">「{{ $partner->name }}」さんとの取引画面</h2>
                </div>
                <span class="trade-status-badge {{ $soldItem->status === 'completed' ? 'is-completed' : '' }}">
                    {{ $soldItem->status === 'completed' ? '取引完了' : '取引中' }}
                </span>
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
                            <form action="{{ route('trade.message.update', [$soldItem, $message]) }}" method="post" enctype="multipart/form-data" class="trade-message-form">
                                @csrf
                                @method('PATCH')
                                <input type="text" name="message" class="trade-message-input" value="{{ old('message', $message->message) }}">
                                <input type="file" name="image" accept=".png,.jpeg" class="trade-message-image-input">
                                <button type="submit" class="trade-send-button">更新する</button>
                            </form>
                        @else
                            <p class="trade-message-body">{{ $message->message }}</p>
                            @if ($message->image_path)
                                <p><img src="{{ asset('storage/' . $message->image_path) }}" alt="取引メッセージ画像" style="max-width: 220px;"></p>
                            @endif
                        @endif

                        @if ($message->user_id === $currentUser->id)
                            <div class="trade-message-actions">
                                <a href="{{ route('trade.show', ['soldItem' => $soldItem, 'edit' => $message->id]) }}" class="btn-trade-edit">編集</a>
                                <form action="{{ route('trade.message.destroy', [$soldItem, $message]) }}" method="post">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="trade-send-button">削除</button>
                                </form>
                            </div>
                        @endif
                    </div>
                @empty
                    <p class="trade-empty-message">まだメッセージはありません。</p>
                @endforelse
            </div>

            @if ($errors->has('message') || $errors->has('image'))
                <div>
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
                <input type="file" name="image" accept=".png,.jpeg" class="trade-message-image-input">
                <button type="submit" class="trade-send-button">送信</button>
            </form>

            <div class="trade-actions-panel">
                @if ($soldItem->status !== 'completed')
                    <form action="{{ route('trade.complete', $soldItem) }}" method="post">
                        @csrf
                        <button type="submit" class="trade-complete-button">取引を完了する</button>
                    </form>
                @else
                    <div class="trade-rating-box">
                        <p class="trade-rating-title">取引が完了しました。</p>

                        @if ($bothRated)
                            <p class="trade-rating-done">双方の評価が完了しました。ありがとうございました。</p>
                        @elseif ($hasRated)
                            <p class="trade-rating-done">評価を送信済みです。相手の評価をお待ちください。</p>
                        @else
                            <p class="trade-rating-text">評価は新しいウィンドウで行えます。</p>
                            <button type="button" class="trade-open-rating-modal" data-open-rating-modal>評価する</button>

                            <div class="trade-rating-modal" data-rating-modal aria-hidden="true">
                                <div class="trade-rating-dialog" role="dialog" aria-modal="true" aria-labelledby="trade-rating-modal-title">
                                    <button type="button" class="trade-rating-close" data-close-rating-modal aria-label="閉じる">×</button>
                                    <p class="trade-rating-window-label">取引完了</p>
                                    <h3 class="trade-rating-window-title" id="trade-rating-modal-title">取引相手を評価してください</h3>
                                    <p class="trade-rating-window-text">左から ☆1 ～ ☆5 を押して評価できます。</p>

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

                                        <p class="trade-rating-selected" data-rating-selected-text>
                                            {{ old('score') ? old('score') . ' / 5 を選択中' : '星を押して評価を選択してください' }}
                                        </p>

                                        <button type="submit" class="trade-rate-submit">送信する</button>
                                    </form>

                                    @error('score')
                                        <p class="error-message">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        @endif
                    </div>
                @endif
            </div>
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

        const modal = document.querySelector('[data-rating-modal]');
        const openModalButton = document.querySelector('[data-open-rating-modal]');
        const closeModalButton = document.querySelector('[data-close-rating-modal]');
        const ratingInput = document.querySelector('[data-rating-input]');
        const ratingStars = document.querySelectorAll('.trade-rating-star');
        const selectedText = document.querySelector('[data-rating-selected-text]');
        const shouldOpenModal = @json(($soldItem->status === 'completed' && !$hasRated && !$bothRated && session('status') && str_contains(session('status'), '取引が完了しました')) || $errors->has('score'));

        function updateSelectedStars(score) {
            ratingStars.forEach(function (star) {
                const starScore = Number(star.dataset.score);
                star.classList.toggle('is-active', starScore <= score);
            });

            if (selectedText) {
                selectedText.textContent = score ? score + ' / 5 を選択中' : '星を押して評価を選択してください';
            }
        }

        function openRatingModal() {
            if (!modal) {
                return;
            }

            modal.classList.add('is-open');
            modal.setAttribute('aria-hidden', 'false');
        }

        function closeRatingModal() {
            if (!modal) {
                return;
            }

            modal.classList.remove('is-open');
            modal.setAttribute('aria-hidden', 'true');
        }

        if (openModalButton) {
            openModalButton.addEventListener('click', openRatingModal);
        }

        if (closeModalButton) {
            closeModalButton.addEventListener('click', closeRatingModal);
        }

        if (modal) {
            modal.addEventListener('click', function (event) {
                if (event.target === modal) {
                    closeRatingModal();
                }
            });
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
