<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SoldItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'item_id',
        'status',
        'seller_rating',
        'buyer_rating',
        'completed_at',
        'seller_last_read_at',
        'buyer_last_read_at',
    ];

    protected $casts = [
        'completed_at' => 'datetime',
        'seller_last_read_at' => 'datetime',
        'buyer_last_read_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function buyer()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function messages()
    {
        return $this->hasMany(TradeMessage::class)->orderBy('created_at');
    }

    public function isParticipant($userId): bool
    {
        $this->loadMissing('item');

        return (int) $this->user_id === (int) $userId || (int) $this->item->user_id === (int) $userId;
    }

    public function unreadCountForUser(int $userId): int
    {
        $this->loadMissing(['item', 'messages']);

        $lastReadField = $this->lastReadFieldForUser($userId);

        if (!$lastReadField) {
            return 0;
        }

        $lastReadAt = $this->{$lastReadField};

        return $this->messages
            ->filter(function ($message) use ($userId, $lastReadAt) {
                if ((int) $message->user_id === (int) $userId) {
                    return false;
                }

                return is_null($lastReadAt) || $message->created_at->gt($lastReadAt);
            })
            ->count();
    }

    public function markAsReadBy(int $userId): void
    {
        $lastReadField = $this->lastReadFieldForUser($userId);

        if (!$lastReadField) {
            return;
        }

        $this->forceFill([
            $lastReadField => now(),
        ])->save();
    }

    public function latestMessageTimestampForUser(int $userId): int
    {
        $this->loadMissing('messages');

        $latestMessage = $this->messages
            ->filter(fn ($message) => (int) $message->user_id !== (int) $userId)
            ->sortByDesc('created_at')
            ->first();

        return optional($latestMessage?->created_at ?? $this->created_at)->timestamp ?? 0;
    }

    private function lastReadFieldForUser(int $userId): ?string
    {
        $this->loadMissing('item');

        if ((int) $this->user_id === (int) $userId) {
            return 'buyer_last_read_at';
        }

        if ((int) $this->item->user_id === (int) $userId) {
            return 'seller_last_read_at';
        }

        return null;
    }
}