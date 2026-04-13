<?php

namespace Tests\Feature;

use App\Mail\TradeCompletedMail;
use App\Models\Item;
use App\Models\SoldItem;
use App\Models\TradeMessage;
use App\Models\User;
use Database\Seeders\CategorySeeder;
use Database\Seeders\ConditionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class TradeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(ConditionSeeder::class);
        $this->seed(CategorySeeder::class);
    }

    public function test_mypage_displays_trading_items_tab_with_count(): void
    {
        $seller = User::factory()->create();
        $buyer = User::factory()->create();
        $item = Item::factory()->create([
            'user_id' => $seller->id,
            'name' => '取引中のバッグ',
        ]);

        SoldItem::create([
            'user_id' => $buyer->id,
            'item_id' => $item->id,
            'status' => 'trading',
        ]);

        $response = $this->actingAs($buyer)->get('/mypage?page=trade');

        $response->assertOk();
        $response->assertSee('取引中の商品 (1)');
        $response->assertSee('取引中のバッグ');
        $response->assertSee('取引画面を開く');
    }

    public function test_trade_screen_supports_messages_between_buyer_and_seller(): void
    {
        $seller = User::factory()->create();
        $buyer = User::factory()->create();
        $item = Item::factory()->create(['user_id' => $seller->id]);

        $soldItem = SoldItem::create([
            'user_id' => $buyer->id,
            'item_id' => $item->id,
            'status' => 'trading',
        ]);

        $response = $this->actingAs($buyer)->post(route('trade.message.store', $soldItem), [
            'message' => 'よろしくお願いします。',
        ]);

        $response->assertRedirect(route('trade.show', $soldItem));

        $this->assertDatabaseHas('trade_messages', [
            'sold_item_id' => $soldItem->id,
            'user_id' => $buyer->id,
            'message' => 'よろしくお願いします。',
        ]);

        $screen = $this->actingAs($seller)->get(route('trade.show', $soldItem));
        $screen->assertOk();
        $screen->assertSee('取引チャット');
        $screen->assertSee('よろしくお願いします。');
    }

    public function test_mutual_ratings_are_reflected_on_profiles(): void
    {
        $seller = User::factory()->create();
        $buyer = User::factory()->create();
        $item = Item::factory()->create(['user_id' => $seller->id]);

        $soldItem = SoldItem::create([
            'user_id' => $buyer->id,
            'item_id' => $item->id,
            'status' => 'completed',
        ]);

        $this->actingAs($buyer)
            ->post(route('trade.rate', $soldItem), ['score' => 5])
            ->assertRedirect(route('index'));

        $this->actingAs($seller)
            ->post(route('trade.rate', $soldItem), ['score' => 4])
            ->assertRedirect(route('index'));

        $sellerPage = $this->actingAs($seller)->get(route('mypage.index'));
        $sellerPage->assertSee('評価 5.0 / 5.0');
        $sellerPage->assertSee('評価件数 1件');

        $buyerPage = $this->actingAs($buyer)->get(route('mypage.index'));
        $buyerPage->assertSee('評価 4.0 / 5.0');
        $buyerPage->assertSee('評価件数 1件');
    }

    public function test_user_without_ratings_does_not_see_rating_summary(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('mypage.index'));

        $response->assertOk();
        $response->assertDontSee('評価件数');
        $response->assertDontSee('/ 5.0');
    }

    public function test_trade_message_validation_follows_excel_requirements(): void
    {
        $seller = User::factory()->create();
        $buyer = User::factory()->create();
        $item = Item::factory()->create(['user_id' => $seller->id]);

        $soldItem = SoldItem::create([
            'user_id' => $buyer->id,
            'item_id' => $item->id,
            'status' => 'trading',
        ]);

        $response = $this->from(route('trade.show', $soldItem))
            ->actingAs($buyer)
            ->post(route('trade.message.store', $soldItem), [
                'message' => str_repeat('あ', 401),
                'image' => UploadedFile::fake()->create('invalid.gif', 10, 'image/gif'),
            ]);

        $response->assertRedirect(route('trade.show', $soldItem));

        $screen = $this->actingAs($buyer)->get(route('trade.show', $soldItem));
        $screen->assertSee('本文は400文字以内で入力してください');
        $screen->assertSee('「.png」または「.jpeg」形式でアップロードしてください');
        $screen->assertSee(str_repeat('あ', 20));
    }

    public function test_user_can_edit_and_delete_own_trade_message(): void
    {
        $seller = User::factory()->create();
        $buyer = User::factory()->create();
        $item = Item::factory()->create(['user_id' => $seller->id]);

        $soldItem = SoldItem::create([
            'user_id' => $buyer->id,
            'item_id' => $item->id,
            'status' => 'trading',
        ]);

        $tradeMessage = TradeMessage::create([
            'sold_item_id' => $soldItem->id,
            'user_id' => $buyer->id,
            'message' => '編集前のメッセージ',
        ]);

        $this->actingAs($buyer)
            ->patch(route('trade.message.update', [$soldItem, $tradeMessage]), [
                'message' => '編集後のメッセージ',
            ])
            ->assertRedirect(route('trade.show', $soldItem));

        $this->assertDatabaseHas('trade_messages', [
            'id' => $tradeMessage->id,
            'message' => '編集後のメッセージ',
        ]);

        $this->actingAs($buyer)
            ->delete(route('trade.message.destroy', [$soldItem, $tradeMessage]))
            ->assertRedirect(route('trade.show', $soldItem));

        $this->assertDatabaseMissing('trade_messages', [
            'id' => $tradeMessage->id,
        ]);
    }

    public function test_mypage_shows_unread_counts_and_prioritizes_latest_trade_messages(): void
    {
        $seller = User::factory()->create();
        $buyer = User::factory()->create();
        $anotherBuyer = User::factory()->create();

        $priorityItem = Item::factory()->create([
            'user_id' => $seller->id,
            'name' => '新着の取引商品',
        ]);
        $otherItem = Item::factory()->create([
            'user_id' => $seller->id,
            'name' => '古い取引商品',
        ]);

        $priorityTrade = SoldItem::create([
            'user_id' => $buyer->id,
            'item_id' => $priorityItem->id,
            'status' => 'trading',
            'seller_last_read_at' => now()->subHour(),
        ]);
        $otherTrade = SoldItem::create([
            'user_id' => $anotherBuyer->id,
            'item_id' => $otherItem->id,
            'status' => 'trading',
            'seller_last_read_at' => now(),
        ]);

        $priorityTrade->messages()->create([
            'user_id' => $buyer->id,
            'message' => '最新の未読メッセージ',
            'created_at' => now()->subMinutes(5),
            'updated_at' => now()->subMinutes(5),
        ]);
        $priorityTrade->messages()->create([
            'user_id' => $buyer->id,
            'message' => 'もう1件の未読メッセージ',
            'created_at' => now()->subMinutes(2),
            'updated_at' => now()->subMinutes(2),
        ]);
        $otherTrade->messages()->create([
            'user_id' => $anotherBuyer->id,
            'message' => '既読のメッセージ',
            'created_at' => now()->subDay(),
            'updated_at' => now()->subDay(),
        ]);

        $response = $this->actingAs($seller)->get('/mypage?page=trade');

        $response->assertOk();
        $response->assertSee('新着2件');
        $response->assertSee('trade-card-alert-dot', false);

        $html = $response->getContent();
        $this->assertTrue(
            strpos($html, '新着の取引商品') < strpos($html, '古い取引商品'),
            'Unread trade should appear before the older trade.'
        );
    }

    public function test_trade_completion_sends_mail_to_seller(): void
    {
        Mail::fake();

        $seller = User::factory()->create(['email' => 'seller@example.com']);
        $buyer = User::factory()->create(['email' => 'buyer@example.com']);
        $item = Item::factory()->create([
            'user_id' => $seller->id,
            'name' => 'メール通知テスト商品',
        ]);

        $soldItem = SoldItem::create([
            'user_id' => $buyer->id,
            'item_id' => $item->id,
            'status' => 'trading',
        ]);

        $this->actingAs($buyer)
            ->post(route('trade.complete', $soldItem))
            ->assertRedirect(route('trade.show', $soldItem));

        Mail::assertSent(TradeCompletedMail::class, function (TradeCompletedMail $mail) use ($seller) {
            return $mail->hasTo($seller->email);
        });
    }

    public function test_completed_trade_waiting_for_seller_rating_is_shown_in_trade_tab(): void
    {
        $seller = User::factory()->create();
        $buyer = User::factory()->create();
        $item = Item::factory()->create([
            'user_id' => $seller->id,
            'name' => '完了済みになった商品',
        ]);

        $soldItem = SoldItem::create([
            'user_id' => $buyer->id,
            'item_id' => $item->id,
            'status' => 'trading',
        ]);

        $this->actingAs($buyer)
            ->post(route('trade.complete', $soldItem))
            ->assertRedirect(route('trade.show', $soldItem));

        $response = $this->actingAs($seller)->get('/mypage?page=trade');

        $response->assertOk();
        $response->assertSee('完了済みになった商品');
        $response->assertSee('取引画面を開く');
        $response->assertSee('trade-card-alert-dot', false);
    }

    public function test_completed_trade_disappears_from_trade_tab_after_seller_rates(): void
    {
        $seller = User::factory()->create();
        $buyer = User::factory()->create();
        $item = Item::factory()->create([
            'user_id' => $seller->id,
            'name' => '評価後に消える商品',
        ]);

        $soldItem = SoldItem::create([
            'user_id' => $buyer->id,
            'item_id' => $item->id,
            'status' => 'completed',
            'seller_rating' => 5,
            'buyer_rating' => null,
        ]);

        $this->actingAs($seller)
            ->post(route('trade.rate', $soldItem), ['score' => 4])
            ->assertRedirect(route('index'));

        $response = $this->actingAs($seller)->get('/mypage?page=trade');

        $response->assertOk();
        $response->assertDontSee('評価後に消える商品');
    }

    public function test_completed_trade_screen_shows_rating_modal_trigger_with_five_stars(): void
    {
        $seller = User::factory()->create();
        $buyer = User::factory()->create();
        $item = Item::factory()->create(['user_id' => $seller->id]);

        $soldItem = SoldItem::create([
            'user_id' => $buyer->id,
            'item_id' => $item->id,
            'status' => 'completed',
        ]);

        $response = $this->actingAs($buyer)->get(route('trade.show', $soldItem));

        $response->assertOk();
        $response->assertSee('評価する');
        $response->assertSee('trade-rating-modal');
        $response->assertSee('☆1');
        $response->assertSee('☆5');
    }
}
