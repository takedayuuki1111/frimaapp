<?php

namespace Tests\Feature;

use App\Models\Item;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DemoSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_seeder_creates_trade_and_rating_demo_data(): void
    {
        $this->seed();

        $this->assertDatabaseHas('users', [
            'email' => 'hanako@example.com',
        ]);

        $this->assertSame(10, Item::count());

        foreach (['腕時計', 'HDD', '玉ねぎ3束', '革靴', 'ノートPC', 'マイク', 'ショルダーバッグ', 'タンブラー', 'コーヒーミル', 'メイクセット'] as $itemName) {
            $this->assertSame(1, Item::where('name', $itemName)->count(), "{$itemName} should exist exactly once.");
        }

        $this->assertDatabaseHas('sold_items', [
            'status' => 'trading',
        ]);

        $this->assertDatabaseHas('sold_items', [
            'status' => 'completed',
            'seller_rating' => 5,
            'buyer_rating' => 4,
        ]);

        $this->assertDatabaseHas('items', [
            'name' => 'ショルダーバッグ',
        ]);

        $this->assertDatabaseHas('trade_messages', [
            'message' => '購入しました。よろしくお願いします。',
        ]);
    }

    public function test_database_seeder_can_be_run_twice_without_duplicating_spec_items(): void
    {
        $this->seed();
        $this->seed();

        $this->assertSame(10, Item::count());
        $this->assertSame(1, Item::where('name', 'ショルダーバッグ')->count());
        $this->assertSame(1, Item::where('name', 'ノートPC')->count());
    }
}
