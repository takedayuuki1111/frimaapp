<?php

namespace App\Http\Controllers;

use App\Http\Requests\TradeMessageRequest;
use App\Mail\TradeCompletedMail;
use App\Models\SoldItem;
use App\Models\TradeMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class TradeController extends Controller
{
    public function show(Request $request, SoldItem $soldItem)
    {
        $soldItem->load(['item.user', 'user', 'messages.user']);
        $this->ensureParticipant($soldItem);

        $currentUser = Auth::user();
        $soldItem->markAsReadBy($currentUser->id);

        $partner = (int) $soldItem->user_id === (int) $currentUser->id
            ? $soldItem->item->user
            : $soldItem->user;

        $ratingField = $this->ratingFieldForUser($soldItem, $currentUser->id);
        $hasRated = $ratingField ? !is_null($soldItem->{$ratingField}) : false;
        $bothRated = !is_null($soldItem->seller_rating) && !is_null($soldItem->buyer_rating);
        $otherTrades = $this->otherTradesForUser($currentUser->id, $soldItem->id);
        $editingMessageId = (int) $request->query('edit');

        return view('trade.show', compact(
            'soldItem',
            'currentUser',
            'partner',
            'hasRated',
            'bothRated',
            'otherTrades',
            'editingMessageId'
        ));
    }

    public function storeMessage(TradeMessageRequest $request, SoldItem $soldItem)
    {
        $soldItem->loadMissing('item.user');
        $this->ensureParticipant($soldItem);

        $payload = [
            'user_id' => Auth::id(),
            'message' => $request->validated()['message'],
        ];

        if ($request->hasFile('image')) {
            $payload['image_path'] = $request->file('image')->store('trade-messages', 'public');
        }

        $soldItem->messages()->create($payload);

        return redirect()->route('trade.show', $soldItem)->with('status', 'メッセージを送信しました。');
    }

    public function updateMessage(TradeMessageRequest $request, SoldItem $soldItem, TradeMessage $tradeMessage)
    {
        $soldItem->loadMissing('item.user');
        $this->ensureParticipant($soldItem);
        $this->ensureMessageBelongsToTrade($soldItem, $tradeMessage);

        abort_unless((int) $tradeMessage->user_id === (int) Auth::id(), 403);

        $payload = [
            'message' => $request->validated()['message'],
        ];

        if ($request->hasFile('image')) {
            $payload['image_path'] = $request->file('image')->store('trade-messages', 'public');
        }

        $tradeMessage->update($payload);

        return redirect()->route('trade.show', $soldItem)->with('status', 'メッセージを更新しました。');
    }

    public function destroyMessage(SoldItem $soldItem, TradeMessage $tradeMessage)
    {
        $soldItem->loadMissing('item.user');
        $this->ensureParticipant($soldItem);
        $this->ensureMessageBelongsToTrade($soldItem, $tradeMessage);

        abort_unless((int) $tradeMessage->user_id === (int) Auth::id(), 403);

        $tradeMessage->delete();

        return redirect()->route('trade.show', $soldItem)->with('status', 'メッセージを削除しました。');
    }

    public function complete(SoldItem $soldItem)
    {
        $soldItem->loadMissing(['item.user', 'buyer']);
        $this->ensureParticipant($soldItem);

        if ((int) $soldItem->user_id !== (int) Auth::id()) {
            return redirect()->route('trade.show', $soldItem)->with('status', '購入者のみ取引を完了できます。');
        }

        if ($soldItem->status !== 'completed') {
            $soldItem->update([
                'status' => 'completed',
                'completed_at' => now(),
            ]);

            if (!empty($soldItem->item->user->email)) {
                Mail::to($soldItem->item->user->email)->send(new TradeCompletedMail($soldItem));
            }

            return redirect()->route('trade.show', $soldItem)
                ->with('status', '取引が完了しました。出品者へ確認メールを送信しました。');
        }

        return redirect()->route('trade.show', $soldItem)->with('status', 'この取引はすでに完了しています。');
    }

    public function rate(Request $request, SoldItem $soldItem)
    {
        $soldItem->loadMissing('item');
        $this->ensureParticipant($soldItem);

        if ($soldItem->status !== 'completed') {
            return redirect()->route('trade.show', $soldItem)->with('status', '先に取引を完了してください。');
        }

        $validated = $request->validate([
            'score' => 'required|integer|between:1,5',
        ], [
            'score.required' => '評価を選択してください。',
        ]);

        $ratingField = $this->ratingFieldForUser($soldItem, Auth::id());

        if (!$ratingField) {
            abort(403);
        }

        if (is_null($soldItem->{$ratingField})) {
            $soldItem->update([
                $ratingField => $validated['score'],
            ]);
        }

        return redirect()->route('index')->with('status', '評価を送信しました。');
    }

    private function ensureParticipant(SoldItem $soldItem): void
    {
        $userId = Auth::id();

        abort_unless(
            (int) $soldItem->user_id === (int) $userId || (int) $soldItem->item->user_id === (int) $userId,
            403
        );
    }

    private function ensureMessageBelongsToTrade(SoldItem $soldItem, TradeMessage $tradeMessage): void
    {
        abort_unless((int) $tradeMessage->sold_item_id === (int) $soldItem->id, 404);
    }

    private function otherTradesForUser(int $userId, int $currentSoldItemId)
    {
        return SoldItem::with(['item', 'messages'])
            ->where('status', 'trading')
            ->where('id', '!=', $currentSoldItemId)
            ->where(function ($query) use ($userId) {
                $query->where('user_id', $userId)
                    ->orWhereHas('item', function ($itemQuery) use ($userId) {
                        $itemQuery->where('user_id', $userId);
                    });
            })
            ->get()
            ->sortByDesc(fn ($trade) => $trade->latestMessageTimestampForUser($userId))
            ->values();
    }

    private function ratingFieldForUser(SoldItem $soldItem, int $userId): ?string
    {
        if ((int) $soldItem->user_id === (int) $userId) {
            return 'seller_rating';
        }

        if ((int) $soldItem->item->user_id === (int) $userId) {
            return 'buyer_rating';
        }

        return null;
    }
}
