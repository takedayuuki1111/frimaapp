<?php

namespace Database\Seeders;

use App\Models\Item;
use App\Models\SoldItem;
use App\Models\TradeMessage;
use App\Models\User;
use Illuminate\Database\Seeder;

class TradeDemoSeeder extends Seeder
{
    public function run()
    {
        $buyer = User::where('email', 'test@example.com')->firstOrFail();
        $seller = User::where('email', 'taro@example.com')->firstOrFail();

        $tradingItem = Item::where('user_id', $seller->id)
            ->where('name', 'ショルダーバッグ')
            ->firstOrFail();

        $completedItem = Item::where('user_id', $seller->id)
            ->where('name', '革靴')
            ->firstOrFail();

        $soldByBuyerItem = Item::where('user_id', $buyer->id)
            ->where('name', 'ノートPC')
            ->firstOrFail();

        $tradingSoldItem = SoldItem::updateOrCreate(
            ['item_id' => $tradingItem->id],
            [
                'user_id' => $buyer->id,
                'status' => 'trading',
                'seller_rating' => null,
                'buyer_rating' => null,
                'completed_at' => null,
            ]
        );

        $completedSoldItem = SoldItem::updateOrCreate(
            ['item_id' => $completedItem->id],
            [
                'user_id' => $buyer->id,
                'status' => 'completed',
                'seller_rating' => 5,
                'buyer_rating' => 4,
                'completed_at' => now()->subDays(2),
            ]
        );

        $reverseCompletedSoldItem = SoldItem::updateOrCreate(
            ['item_id' => $soldByBuyerItem->id],
            [
                'user_id' => $seller->id,
                'status' => 'completed',
                'seller_rating' => 4,
                'buyer_rating' => 5,
                'completed_at' => now()->subDay(),
            ]
        );

        $this->seedMessages($tradingSoldItem, [
            [$buyer->id, '購入しました。よろしくお願いします。'],
            [$seller->id, 'ありがとうございます。明日発送予定です。'],
        ]);

        $this->seedMessages($completedSoldItem, [
            [$buyer->id, '商品を受け取りました。ありがとうございました。'],
            [$seller->id, '無事届いて安心しました。'],
        ]);

        $this->seedMessages($reverseCompletedSoldItem, [
            [$seller->id, '購入させていただきました。'],
            [$buyer->id, 'ご購入ありがとうございます。'],
        ]);
    }

    private function seedMessages(SoldItem $soldItem, array $messages): void
    {
        foreach ($messages as [$userId, $message]) {
            TradeMessage::firstOrCreate([
                'sold_item_id' => $soldItem->id,
                'user_id' => $userId,
                'message' => $message,
            ]);
        }
    }
}
